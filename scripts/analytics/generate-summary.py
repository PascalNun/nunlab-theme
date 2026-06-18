#!/usr/bin/env python3
"""Generate a small privacy-preserving analytics summary from nginx logs."""

from __future__ import annotations

import argparse
import gzip
import grp
import ipaddress
import json
import os
import pwd
import re
import shutil
import subprocess
import tempfile
from collections import Counter, defaultdict
from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Iterable
from urllib.parse import urlsplit


LOG_RE = re.compile(
    r'^(?P<ip>\S+) \S+ \S+ \[(?P<time>[^\]]+)\] "(?P<request>[^"]*)" '
    r'(?P<status>\d{3}) (?P<size>\S+) "(?P<referrer>[^"]*)" "(?P<agent>[^"]*)"'
)

IGNORED_PREFIXES = (
    "/wp-admin",
    "/wp-content",
    "/wp-includes",
    "/wp-json",
    "/wp-login.php",
    "/wp/",
    "/xmlrpc.php",
)

SCANNER_PATH_TERMS = (
    "/actuator",
    "/backup",
    "/boaform",
    "/cgi-bin",
    "/composer",
    "/config",
    "/debug",
    "/phpinfo",
    "/phpmyadmin",
    "/pma",
    "/server-status",
    "/shell",
    "/solr",
    "/vendor",
)

BOT_TERMS = (
    "ahrefs",
    "bot",
    "bytespider",
    "censys",
    "crawler",
    "crawl",
    "curl",
    "discordbot",
    "dotbot",
    "facebookexternalhit",
    "go-http-client",
    "headless",
    "httpclient",
    "internetmeasurement",
    "lighthouse",
    "mj12",
    "masscan",
    "monitor",
    "nikto",
    "pagespeed",
    "petalbot",
    "python-requests",
    "scan",
    "semrush",
    "slurp",
    "spider",
    "telegrambot",
    "wget",
    "whatsapp",
    "wpscan",
    "yandex",
    "zgrab",
)

SEARCH_REFERRERS = (
    "bing.",
    "duckduckgo.",
    "ecosia.",
    "google.",
    "search.yahoo.",
    "yandex.",
)

SOCIAL_REFERRERS = (
    "facebook.",
    "instagram.",
    "linkedin.",
    "lnkd.in",
    "threads.",
    "twitter.",
    "x.com",
)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--log-dir", default="/var/log/nginx", help="Directory containing nginx access logs.")
    parser.add_argument("--output", default="/var/lib/nunlab-analytics/summary.json", help="JSON output path.")
    parser.add_argument("--days", type=int, default=30, help="Number of recent days to summarize.")
    parser.add_argument("--site", action="append", default=[], help="Own domain for referrer filtering.")
    parser.add_argument("--owner-user", default="", help="Optional output owner user.")
    parser.add_argument("--owner-group", default="", help="Optional output owner group.")
    parser.add_argument("--no-geo", action="store_true", help="Disable local GeoIP country lookup.")
    return parser.parse_args()


def log_files(log_dir: Path) -> list[Path]:
    files = [path for path in log_dir.glob("access.log*") if path.is_file()]
    return sorted(files, key=lambda path: path.stat().st_mtime, reverse=True)


def open_log(path: Path):
    if path.suffix == ".gz":
        return gzip.open(path, mode="rt", encoding="utf-8", errors="replace")

    return path.open(mode="r", encoding="utf-8", errors="replace")


def parse_time(value: str) -> datetime | None:
    try:
        return datetime.strptime(value, "%d/%b/%Y:%H:%M:%S %z")
    except ValueError:
        return None


def request_path(request: str) -> str | None:
    parts = request.split()

    if len(parts) < 2 or parts[0] not in {"GET", "HEAD"}:
        return None

    target = parts[1]
    parsed = urlsplit(target)
    path = parsed.path or "/"

    if not path.startswith("/"):
        path = "/" + path

    return normalize_path(path)


def normalize_path(path: str) -> str:
    if len(path) > 1:
        path = path.rstrip("/")

    return path or "/"


def is_public_page(path: str) -> bool:
    lower_path = path.lower()
    segments = [segment for segment in lower_path.split("/") if segment]

    if any(lower_path.startswith(prefix) for prefix in IGNORED_PREFIXES):
        return False

    if any(segment.startswith(".") for segment in segments):
        return False

    if any(term in lower_path for term in SCANNER_PATH_TERMS):
        return False

    if lower_path in {"/favicon.ico", "/robots.txt"}:
        return False

    if lower_path.startswith("/sitemap") or lower_path.endswith("/feed"):
        return False

    suffix = Path(lower_path).suffix

    if suffix:
        return False

    return True


def is_bot(agent: str) -> bool:
    if not agent or agent == "-":
        return True

    lower_agent = agent.lower()
    return any(term in lower_agent for term in BOT_TERMS)


def is_public_ip(ip: str) -> bool:
    try:
        parsed = ipaddress.ip_address(ip)
    except ValueError:
        return False

    return not (
        parsed.is_private
        or parsed.is_loopback
        or parsed.is_link_local
        or parsed.is_multicast
        or parsed.is_reserved
        or parsed.is_unspecified
    )


def own_domains(values: Iterable[str]) -> set[str]:
    domains = {"pascalnun.eu", "www.pascalnun.eu", "nun.archi", "www.nun.archi"}

    for value in values:
        value = value.strip().lower()

        if value:
            domains.add(value.removeprefix("www."))
            domains.add(value)

    return domains


def referrer_domain(referrer: str, sites: set[str]) -> str | None:
    if not referrer or referrer == "-":
        return None

    parsed = urlsplit(referrer)
    domain = parsed.netloc.lower()

    if not domain:
        return None

    domain = domain.split("@")[-1].split(":")[0]
    comparable = domain.removeprefix("www.")
    site_names = {site.removeprefix("www.") for site in sites}

    if comparable in site_names or any(comparable.endswith("." + site) for site in site_names):
        return None

    return comparable


def is_content_path(path: str) -> bool:
    """Treat non-homepage page views as stronger editorial/content signals."""
    return path != "/"


def referrer_group(domain: str | None) -> str:
    if not domain:
        return "Direct"

    if any(term in domain for term in SEARCH_REFERRERS):
        return "Search"

    if any(term in domain for term in SOCIAL_REFERRERS):
        return "Social"

    return "Referral"


def device_family(agent: str) -> str:
    lower_agent = agent.lower()

    if "ipad" in lower_agent or "tablet" in lower_agent:
        return "Tablet"

    if "mobile" in lower_agent or "iphone" in lower_agent or "android" in lower_agent:
        return "Mobile"

    return "Desktop"


def browser_family(agent: str) -> str:
    lower_agent = agent.lower()

    if "edg/" in lower_agent or "edge/" in lower_agent:
        return "Edge"

    if "firefox/" in lower_agent or "fxios/" in lower_agent:
        return "Firefox"

    if "crios/" in lower_agent or (
        "chrome/" in lower_agent
        and "chromium" not in lower_agent
        and "edg/" not in lower_agent
        and "opr/" not in lower_agent
    ):
        return "Chrome"

    if "safari/" in lower_agent and "version/" in lower_agent:
        return "Safari"

    return "Other"


def country_lookup_enabled(no_geo: bool) -> bool:
    return not no_geo and shutil.which("geoiplookup") is not None


def lookup_country(ip: str, enabled: bool, cache: dict[str, tuple[str, str]]) -> tuple[str, str]:
    if ip in cache:
        return cache[ip]

    if not enabled or not is_public_ip(ip):
        cache[ip] = ("ZZ", "Unknown")
        return cache[ip]

    try:
        result = subprocess.run(
            ["geoiplookup", ip],
            check=False,
            capture_output=True,
            text=True,
            timeout=2,
        )
    except (OSError, subprocess.TimeoutExpired):
        cache[ip] = ("ZZ", "Unknown")
        return cache[ip]

    output = result.stdout.strip()
    match = re.search(r":\s*([A-Z]{2}),\s*(.+)$", output)

    if not match or "IP Address not found" in output:
        cache[ip] = ("ZZ", "Unknown")
        return cache[ip]

    code = match.group(1)
    label = match.group(2).strip()
    cache[ip] = (code, label)
    return cache[ip]


def serialise_counter(counter: Counter, label_key: str, value_key: str, limit: int = 25) -> list[dict[str, int | str]]:
    return [
        {label_key: label, value_key: count}
        for label, count in counter.most_common(limit)
    ]


def write_json(path: Path, data: dict, owner_user: str, owner_group: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)

    with tempfile.NamedTemporaryFile("w", encoding="utf-8", dir=path.parent, delete=False) as handle:
        json.dump(data, handle, indent=2, sort_keys=True)
        handle.write("\n")
        temp_name = handle.name

    os.chmod(temp_name, 0o640)

    if owner_user or owner_group:
        uid = pwd.getpwnam(owner_user).pw_uid if owner_user else -1
        gid = grp.getgrnam(owner_group).gr_gid if owner_group else -1
        os.chown(temp_name, uid, gid)

    os.replace(temp_name, path)


def main() -> int:
    args = parse_args()
    now = datetime.now(timezone.utc)
    start = now - timedelta(days=args.days)
    today = now.astimezone().date().isoformat()
    sites = own_domains(args.site)
    geo_enabled = country_lookup_enabled(args.no_geo)
    country_cache: dict[str, tuple[str, str]] = {}

    events: list[dict[str, str]] = []
    not_found = 0
    parsed_requests = 0
    bot_requests = 0
    filtered_requests = 0
    daily_filtered_requests: Counter[str] = Counter()

    for path in log_files(Path(args.log_dir)):
        with open_log(path) as log:
            for line in log:
                match = LOG_RE.match(line)

                if not match:
                    continue

                timestamp = parse_time(match.group("time"))

                if timestamp is None or timestamp.astimezone(timezone.utc) < start:
                    continue

                parsed_requests += 1
                status = int(match.group("status"))
                path_value = request_path(match.group("request"))
                agent = match.group("agent")
                day = timestamp.date().isoformat()

                if path_value is None or not is_public_page(path_value):
                    filtered_requests += 1
                    daily_filtered_requests[day] += 1
                    continue

                if is_bot(agent):
                    bot_requests += 1
                    filtered_requests += 1
                    daily_filtered_requests[day] += 1
                    continue

                ip = match.group("ip")

                if status == 404:
                    not_found += 1

                if status < 200 or status >= 400:
                    filtered_requests += 1
                    daily_filtered_requests[day] += 1
                    continue

                referrer = referrer_domain(match.group("referrer"), sites)
                country_code, country_label = lookup_country(ip, geo_enabled, country_cache)

                events.append(
                    {
                        "ip": ip,
                        "day": day,
                        "path": path_value,
                        "agent": agent,
                        "referrer": referrer or "",
                        "country_code": country_code,
                        "country_label": country_label,
                    }
                )

    ip_counts: Counter[str] = Counter(event["ip"] for event in events)
    ip_day_counts: Counter[tuple[str, str]] = Counter((event["ip"], event["day"]) for event in events)
    ip_has_signal: defaultdict[str, bool] = defaultdict(bool)

    for event in events:
        if is_content_path(event["path"]) or event["referrer"]:
            ip_has_signal[event["ip"]] = True

    visitor_ips: set[str] = set()
    likely_ips: set[str] = set()
    today_ips: set[str] = set()
    today_likely_ips: set[str] = set()
    page_counter: Counter[str] = Counter()
    page_likely_counter: Counter[str] = Counter()
    referrer_counter: Counter[str] = Counter()
    source_counter: Counter[str] = Counter()
    country_view_counter: Counter[str] = Counter()
    country_likely_counter: Counter[str] = Counter()
    country_code_by_label: dict[str, str] = {}
    country_visitors: defaultdict[str, set[str]] = defaultdict(set)
    country_likely_visitors: defaultdict[str, set[str]] = defaultdict(set)
    device_counter: Counter[str] = Counter()
    browser_counter: Counter[str] = Counter()
    daily_views: Counter[str] = Counter()
    daily_likely_views: Counter[str] = Counter()
    daily_content_views: Counter[str] = Counter()
    daily_visitors: defaultdict[str, set[str]] = defaultdict(set)
    daily_likely_visitors: defaultdict[str, set[str]] = defaultdict(set)
    likely_views = 0
    content_views = 0
    direct_homepage_views = 0

    for event in events:
        ip = event["ip"]
        day = event["day"]
        path_value = event["path"]
        referrer = event["referrer"]
        country_label = event["country_label"]
        country_code = event["country_code"]
        automated_pressure = ip_counts[ip] > 25 or ip_day_counts[(ip, day)] > 12
        likely = (not automated_pressure) and (
            is_content_path(path_value) or bool(referrer) or ip_has_signal[ip]
        )

        visitor_ips.add(ip)
        daily_visitors[day].add(ip)
        daily_views[day] += 1
        page_counter[path_value] += 1
        country_code_by_label[country_label] = country_code
        country_view_counter[country_label] += 1
        country_visitors[country_label].add(ip)

        if day == today:
            today_ips.add(ip)

        if is_content_path(path_value):
            content_views += 1
            daily_content_views[day] += 1

        if not referrer and path_value == "/":
            direct_homepage_views += 1

        if likely:
            likely_views += 1
            likely_ips.add(ip)
            daily_likely_views[day] += 1
            daily_likely_visitors[day].add(ip)
            page_likely_counter[path_value] += 1
            country_likely_counter[country_label] += 1
            country_likely_visitors[country_label].add(ip)
            device_counter[device_family(event["agent"])] += 1
            browser_counter[browser_family(event["agent"])] += 1
            source_counter[referrer_group(referrer or None)] += 1

            if referrer:
                referrer_counter[referrer] += 1

            if day == today:
                today_likely_ips.add(ip)

    countries = []

    for label, views in country_view_counter.most_common(25):
        countries.append(
            {
                "code": country_code_by_label.get(label, "ZZ"),
                "label": label,
                "likely_views": country_likely_counter[label],
                "likely_visitors": len(country_likely_visitors[label]),
                "views": views,
                "visitors": len(country_visitors[label]),
            }
        )

    days = []
    start_date = start.date()

    for offset in range(args.days + 1):
        day = (start_date + timedelta(days=offset)).isoformat()
        days.append(
            {
                "content_views": daily_content_views[day],
                "date": day,
                "filtered_requests": daily_filtered_requests[day],
                "likely_views": daily_likely_views[day],
                "likely_visitors": len(daily_likely_visitors[day]),
                "views": daily_views[day],
                "visitors": len(daily_visitors[day]),
            }
        )

    pages = []

    for page, views in page_counter.most_common(25):
        pages.append(
            {
                "content": is_content_path(page),
                "path": page,
                "likely_views": page_likely_counter[page],
                "views": views,
            }
        )

    output = {
        "schema_version": 2,
        "generated_at": now.isoformat().replace("+00:00", "Z"),
        "period": {
            "days": args.days,
            "start": start.date().isoformat(),
            "end": now.date().isoformat(),
        },
        "privacy": {
            "source": "nginx access logs",
            "visitor_script": False,
            "stores_ip_addresses": False,
            "geoip": "local geoiplookup" if geo_enabled else "disabled",
        },
        "totals": {
            "bot_requests": bot_requests,
            "content_views": content_views,
            "direct_homepage_views": direct_homepage_views,
            "filtered_requests": filtered_requests,
            "likely_views": likely_views,
            "likely_visitors": len(likely_ips),
            "parsed_requests": parsed_requests,
            "page_views": len(events),
            "not_found": not_found,
            "visitors": len(visitor_ips),
        },
        "today": {
            "content_views": daily_content_views[today],
            "likely_views": daily_likely_views[today],
            "likely_visitors": len(today_likely_ips),
            "page_views": daily_views[today],
            "visitors": len(today_ips),
        },
        "browsers": serialise_counter(browser_counter, "label", "views"),
        "countries": countries,
        "days": days,
        "devices": serialise_counter(device_counter, "label", "views"),
        "pages": pages,
        "referrers": serialise_counter(referrer_counter, "label", "views"),
        "sources": serialise_counter(source_counter, "label", "views"),
    }

    write_json(Path(args.output), output, args.owner_user, args.owner_group)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

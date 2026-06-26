// Homepage-only interactions: hero scrubbing, section nav, and work cards.

(function () {
	'use strict';

	var bindHorizontalSwipe = function (element, onPrev, onNext, shouldHandle) {
		var startX = 0;
		var startY = 0;
		var isTracking = false;

		if (!element) {
			return;
		}

		element.addEventListener(
			'touchstart',
			function (event) {
				if ((shouldHandle && !shouldHandle()) || !event.touches || 1 !== event.touches.length) {
					return;
				}

				startX = event.touches[0].clientX;
				startY = event.touches[0].clientY;
				isTracking = true;
			},
			{ passive: true }
		);

		element.addEventListener(
			'touchend',
			function (event) {
				var deltaX;
				var deltaY;

				if (!isTracking || !event.changedTouches || !event.changedTouches.length) {
					return;
				}

				deltaX = event.changedTouches[0].clientX - startX;
				deltaY = event.changedTouches[0].clientY - startY;
				isTracking = false;

				if (Math.abs(deltaX) < 56 || Math.abs(deltaX) < Math.abs(deltaY) * 1.2) {
					return;
				}

				if (deltaX < 0) {
					onNext();
					return;
				}

				onPrev();
			},
			{ passive: true }
		);

		element.addEventListener(
			'touchcancel',
			function () {
				isTracking = false;
			},
			{ passive: true }
		);
	};

	var escapeAttribute = function (value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	};

	var buildVideoIframe = function (url, title) {
		if (!url) {
			return '';
		}

		return (
			'<iframe src="' +
			escapeAttribute(url) +
			'" title="' +
			escapeAttribute(title || 'Project video') +
			'" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'
		);
	};

	var buildNativeVideo = function (url, mimeType, posterUrl) {
		if (!url) {
			return '';
		}

		return (
			'<video class="project-gallery__native-video" controls preload="metadata" playsinline data-nunlab-player' +
			(posterUrl ? ' poster="' + escapeAttribute(posterUrl) + '"' : '') +
			'>' +
			'<source src="' +
			escapeAttribute(url) +
			'"' +
			(mimeType ? ' type="' + escapeAttribute(mimeType) + '"' : '') +
			' />' +
			'</video>'
		);
	};

	var initNativeVideoFrame = function (frame) {
		var video = frame ? frame.querySelector('video[data-nunlab-player]') : null;

		if (video && window.nunlabInitNativeVideoPlayer) {
			window.nunlabInitNativeVideoPlayer(video);
		}
	};

	var clearVideoFrame = function (frame) {
		if (!frame) {
			return;
		}

		frame.querySelectorAll('video').forEach(function (video) {
			video.pause();
			video.removeAttribute('src');
			video.load();
		});
		frame.innerHTML = '';
		frame.hidden = true;
	};

	var clearMediaAspectRatio = function (container) {
		if (!container) {
			return;
		}

		container.style.removeProperty('aspect-ratio');
	};

	var syncMediaAspectRatio = function (container, image, fallbackRatio) {
		if (!container) {
			return;
		}

		var applyRatio = function () {
			if (!image || !image.naturalWidth || !image.naturalHeight) {
				if (fallbackRatio) {
					container.style.aspectRatio = fallbackRatio;
					return;
				}

				clearMediaAspectRatio(container);
				return;
			}

			container.style.aspectRatio = String(image.naturalWidth) + ' / ' + String(image.naturalHeight);
		};

		if (!image || image.hidden) {
			if (fallbackRatio) {
				container.style.aspectRatio = fallbackRatio;
				return;
			}

			clearMediaAspectRatio(container);
			return;
		}

		if (image.complete && image.naturalWidth && image.naturalHeight) {
			applyRatio();
			return;
		}

		if (fallbackRatio) {
			container.style.aspectRatio = fallbackRatio;
		}

		image.addEventListener('load', applyRatio, { once: true });
	};

	var animateScrollTo = function (targetY, duration) {
		var root = document.documentElement;
		var startY = window.scrollY;
		var delta = targetY - startY;
		var startTime = null;
		var previousScrollBehavior = root.style.scrollBehavior;

		if (Math.abs(delta) < 2) {
			return;
		}

		var easeInOutCubic = function (progress) {
			return progress < 0.5
				? 4 * progress * progress * progress
				: 1 - Math.pow(-2 * progress + 2, 3) / 2;
		};

		root.style.scrollBehavior = 'auto';

		var step = function (timestamp) {
			var progress;

			if (null === startTime) {
				startTime = timestamp;
			}

			progress = Math.min((timestamp - startTime) / duration, 1);
			window.scrollTo(0, startY + delta * easeInOutCubic(progress));

			if (progress < 1) {
				window.requestAnimationFrame(step);
				return;
			}

			root.style.scrollBehavior = previousScrollBehavior;
		};

		window.requestAnimationFrame(step);
	};

	var scrollWindowTo = function (targetY, behavior) {
		var top = Math.max(0, targetY);

		if ('smooth' === behavior && window.requestAnimationFrame) {
			animateScrollTo(top, 520);
			return;
		}

		try {
			window.scrollTo({
				top: top,
				behavior: behavior || 'auto',
			});
		} catch (error) {
			window.scrollTo(0, top);
		}
	};

	var isTypingContext = function () {
		var activeElement = document.activeElement;
		var tagName = activeElement ? activeElement.tagName : '';

		return !!(
			activeElement &&
			(activeElement.isContentEditable ||
				'INPUT' === tagName ||
				'TEXTAREA' === tagName ||
				'SELECT' === tagName)
		);
	};

	// The hero video is scrubbed by scroll instead of autoplaying freely.
	var initHeroStage = function () {
		var stage = document.querySelector('[data-hero-stage]');
		var video = document.querySelector('[data-hero-video]');
		var sequenceFrame = document.querySelector('[data-hero-sequence-frame]');
		var content = document.querySelector('[data-hero-content]');
		var header = document.querySelector('.site-header');
		var workSection = document.getElementById('work');
		var ticking = false;
		var duration = 0;
		var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var userAgent = window.navigator.userAgent || '';
		var isIOS =
			/iPad|iPhone|iPod/.test(userAgent) ||
			(window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);
		var isFirefox = /firefox/i.test(userAgent);
		var supportsWebP = (function () {
			var canvas;

			try {
				canvas = document.createElement('canvas');

				if (!canvas.getContext || !canvas.getContext('2d')) {
					return false;
				}

				return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
			} catch (error) {
				return false;
			}
		})();
		var sequenceCount = parseInt(stage ? stage.getAttribute('data-hero-sequence-count') || '0' : '0', 10);
		var sequenceBaseJpg = stage ? stage.getAttribute('data-hero-sequence-base-jpg') || '' : '';
		var sequenceBaseWebp = stage ? stage.getAttribute('data-hero-sequence-base-webp') || '' : '';
		var sequenceExt = supportsWebP && sequenceBaseWebp && !isFirefox ? 'webp' : 'jpg';
		var sequenceBase = 'webp' === sequenceExt ? sequenceBaseWebp : sequenceBaseJpg;
		var sequenceDigits = parseInt(stage ? stage.getAttribute('data-hero-sequence-digits') || '3' : '3', 10);
		var useSequenceFallback =
			!!sequenceFrame &&
			sequenceCount > 0 &&
			(isIOS || isFirefox || prefersReducedMotion);
		var useScrollScrub = !!video && !prefersReducedMotion && !useSequenceFallback;
		var currentSequenceIndex = -1;
		var hasSequenceFormatFallback = false;

		if (!stage || !header) {
			return;
		}

		var clamp = function (value, min, max) {
			return Math.min(Math.max(value, min), max);
		};

		var buildSequenceUrl = function (index) {
			var frameNumber = String(index + 1);

			while (frameNumber.length < sequenceDigits) {
				frameNumber = '0' + frameNumber;
			}

			return (
				sequenceBase +
				frameNumber +
				'.' +
				sequenceExt
			);
		};

		var fallBackToJpgSequence = function () {
			if (hasSequenceFormatFallback || 'webp' !== sequenceExt || !sequenceBaseJpg) {
				return;
			}

			hasSequenceFormatFallback = true;
			sequenceExt = 'jpg';
			sequenceBase = sequenceBaseJpg;
			currentSequenceIndex = -1;
			syncSequenceFrame(clamp((-stage.getBoundingClientRect().top / Math.max(stage.offsetHeight - window.innerHeight, 1)), 0, 1));
		};

		var preloadSequenceFrames = function () {
			var index = 0;

			if (!useSequenceFallback) {
				return;
			}

			var loadNext = function () {
				var image;

				if (index >= sequenceCount) {
					return;
				}

				image = new Image();
				image.decoding = 'async';
				image.src = buildSequenceUrl(index);
				index += 1;
				window.setTimeout(loadNext, 24);
			};

			loadNext();
		};

		var syncSequenceFrame = function (progress) {
			var nextIndex;

			if (!useSequenceFallback || !sequenceFrame) {
				return;
			}

			nextIndex = Math.min(sequenceCount - 1, Math.round(progress * (sequenceCount - 1)));

			if (nextIndex === currentSequenceIndex) {
				return;
			}

			currentSequenceIndex = nextIndex;
			sequenceFrame.src = buildSequenceUrl(nextIndex);
		};

		var setHeroState = function () {
			var rect = stage.getBoundingClientRect();
			var scrollRange = Math.max(stage.offsetHeight - window.innerHeight, 1);
			var progress = clamp((-rect.top / scrollRange), 0, 1);
			var workTop = workSection ? workSection.getBoundingClientRect().top : Number.POSITIVE_INFINITY;
			var heroExitThreshold = header.offsetHeight + (window.innerWidth <= 768 ? 18 : 28);
			var onHero =
				rect.top <= header.offsetHeight &&
				rect.bottom > header.offsetHeight + 32 &&
				workTop > heroExitThreshold;

			stage.style.setProperty('--hero-progress', progress.toFixed(4));

			if (content) {
				content.style.setProperty('--hero-progress', progress.toFixed(4));
			}

			header.classList.toggle('site-header--on-hero', onHero);
			header.classList.toggle('site-header--surface', !onHero);

			if (useSequenceFallback) {
				syncSequenceFrame(progress);
			}

			if (video && duration && useScrollScrub) {
				video.pause();
				var targetTime = duration * progress;

				if (Math.abs(video.currentTime - targetTime) > 0.03) {
					video.currentTime = targetTime;
				}

				video.pause();
			}

			ticking = false;
		};

		var requestSync = function () {
			if (ticking) {
				return;
			}

			ticking = true;
			window.requestAnimationFrame(setHeroState);
		};

		if (video) {
			stage.classList.toggle('hero-stage--sequence', useSequenceFallback);
			video.autoplay = false;
			video.loop = false;
			video.removeAttribute('autoplay');
			video.removeAttribute('loop');
			video.addEventListener('play', function () {
				video.pause();
			});

			if (useSequenceFallback) {
				if (sequenceFrame) {
					sequenceFrame.addEventListener('error', fallBackToJpgSequence);
					sequenceFrame.hidden = false;
					sequenceFrame.src = buildSequenceUrl(0);
				}

				video.pause();
				preloadSequenceFrames();
			} else {
				video.pause();
				video.defaultMuted = true;
				video.muted = true;
				video.addEventListener('canplay', function () {
					video.pause();
				});
				video.addEventListener('seeking', function () {
					video.pause();
				});
				video.addEventListener('seeked', function () {
					video.pause();
				});
				video.addEventListener('playing', function () {
					video.pause();
				});
				video.addEventListener('timeupdate', function () {
					video.pause();
				});
				document.addEventListener('visibilitychange', function () {
					if (document.hidden) {
						video.pause();
					}
				});

				var syncDuration = function () {
					if (!Number.isFinite(video.duration) || video.duration <= 0) {
						return;
					}

					duration = Math.max(video.duration - Math.min(0.12, video.duration * 0.04), 0);
					requestSync();
				};

				if (video.readyState >= 1) {
					syncDuration();
				}

				video.addEventListener('loadedmetadata', syncDuration);
				video.addEventListener('loadeddata', syncDuration);
			}
		}

		setHeroState();
		window.addEventListener('scroll', requestSync, { passive: true });
		window.addEventListener('resize', requestSync);
	};

	// The homepage nav should highlight the section currently in view.
	var initSectionAwareNav = function () {
		var header = document.querySelector('.site-header');
		var navigation = document.querySelector('.site-navigation');
		var heroStage = document.querySelector('[data-hero-stage]');
		var links;
		var sectionLinks = [];
		var trackedLinks = [];
		var homeLink = null;
		var ticking = false;

		if (!document.body.classList.contains('is-homepage') || !header || !navigation) {
			return;
		}

		var normalizePath = function (path) {
			return path.replace(/\/+$/, '') || '/';
		};

		var getHeaderOffset = function () {
			return header.offsetHeight + 24;
		};

		var getSectionTargetY = function (section) {
			var styles = window.getComputedStyle(section);
			var scrollMarginTop = parseFloat(styles.scrollMarginTop || '0');
			var paddingTop = parseFloat(styles.paddingTop || '0');
			var sectionTop = section.getBoundingClientRect().top + window.scrollY;

			if ('work' === section.id) {
				return sectionTop - header.offsetHeight + 12;
			}

			var landingInset = Math.max(24, Math.min(paddingTop - 24, 96));

			if (scrollMarginTop) {
				return sectionTop + landingInset - scrollMarginTop;
			}

			return sectionTop + landingInset - getHeaderOffset();
		};

		var setActiveLink = function (activeLink) {
			trackedLinks.forEach(function (link) {
				var item = link.closest('li');

				if (item) {
					item.classList.remove('is-section-active');
				}

				if ('location' === link.getAttribute('aria-current')) {
					link.removeAttribute('aria-current');
				}
			});

			navigation.classList.toggle('has-section-current', !!activeLink);

			if (!activeLink) {
				return;
			}

			var activeItem = activeLink.closest('li');

			if (activeItem) {
				activeItem.classList.add('is-section-active');
			}

			activeLink.setAttribute('aria-current', 'location');
		};

		var syncActiveLink = function () {
			var marker = window.scrollY + getHeaderOffset() + window.innerHeight * 0.18;
			var activeLink = homeLink;

			sectionLinks.forEach(function (entry) {
				if (marker >= entry.section.offsetTop) {
					activeLink = entry.link;
				}
			});

			setActiveLink(activeLink);
			ticking = false;
		};

		links = Array.prototype.slice.call(navigation.querySelectorAll('a'));

		links.forEach(function (link) {
			var itemUrl;
			var sectionId;
			var section;

			try {
				itemUrl = new URL(link.getAttribute('href'), window.location.href);
			} catch (error) {
				return;
			}

			if (normalizePath(itemUrl.pathname) !== normalizePath(window.location.pathname)) {
				return;
			}

			if (!itemUrl.hash) {
				if (!homeLink) {
					homeLink = link;
					trackedLinks.push(link);
				}

				return;
			}

			sectionId = decodeURIComponent(itemUrl.hash.slice(1));
			section = document.getElementById(sectionId);

			if (!section) {
				return;
			}

			sectionLinks.push({
				link: link,
				section: section,
			});
			trackedLinks.push(link);
		});

		if (!trackedLinks.length) {
			return;
		}

		sectionLinks.sort(function (left, right) {
			return left.section.offsetTop - right.section.offsetTop;
		});

		trackedLinks.forEach(function (link) {
			link.addEventListener('click', function (event) {
				var itemUrl;
				var targetId;
				var targetSection;
				var heroActive;
				var targetY;

				setActiveLink(link);

				try {
					itemUrl = new URL(link.getAttribute('href'), window.location.href);
				} catch (error) {
					return;
				}

				if (!itemUrl.hash) {
					if (normalizePath(itemUrl.pathname) === normalizePath(window.location.pathname)) {
						event.preventDefault();
						animateScrollTo(0, window.scrollY > window.innerHeight * 0.6 ? 900 : 650);
					}

					return;
				}

				targetId = decodeURIComponent(itemUrl.hash.slice(1));
				targetSection = document.getElementById(targetId);

				if (!targetSection) {
					return;
				}

				heroActive =
					heroStage &&
					heroStage.getBoundingClientRect().bottom > window.innerHeight * 0.45;

				if (!heroActive) {
					return;
				}

				event.preventDefault();
				targetY = getSectionTargetY(targetSection);
				animateScrollTo(targetY, 1100);
			});
		});

		var requestSync = function () {
			if (ticking) {
				return;
			}

			ticking = true;
			window.requestAnimationFrame(syncActiveLink);
		};

		syncActiveLink();
		window.addEventListener('scroll', requestSync, { passive: true });
		window.addEventListener('resize', requestSync);
		window.addEventListener('hashchange', requestSync);
	};

	// Home page work cards reuse one interactive pattern across all sections.
	var initWorkListings = function () {
		var groups = document.querySelectorAll('[data-work-group]');

		if (!groups.length) {
			return;
		}

		var animateItem = function (item, mutate) {
			var first = item.getBoundingClientRect();

			mutate();

			if (!item.animate) {
				return;
			}

			var last = item.getBoundingClientRect();
			var deltaX = first.left - last.left;
			var deltaY = first.top - last.top;
			var scaleX = first.width / last.width;
			var scaleY = first.height / last.height;

			item.animate(
				[
					{
						transformOrigin: 'top left',
						transform: 'translate(' + deltaX + 'px, ' + deltaY + 'px) scale(' + scaleX + ', ' + scaleY + ')',
					},
					{
						transformOrigin: 'top left',
						transform: 'translate(0, 0) scale(1, 1)',
					},
				],
				{
					duration: 260,
					easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
				}
			);
		};

		var scrollToWorkItem = function (item) {
			var header = document.querySelector('.site-header');
			var headerOffset = header ? header.getBoundingClientRect().height : 0;
			var top = item.getBoundingClientRect().top + window.scrollY - headerOffset - 24;

			scrollWindowTo(top, 'smooth');
		};

		var scrollToWorkMedia = function (item) {
			var media = item.querySelector('.project-card__media');
			var header = document.querySelector('.site-header');
			var headerOffset = header ? header.getBoundingClientRect().height : 0;
			var target = media || item;
			var rect = target.getBoundingClientRect();
			var topBoundary = headerOffset + 18;
			var lowerBoundary = window.innerHeight * 0.42;
			var top;

			if (rect.top >= topBoundary && rect.top <= lowerBoundary) {
				return;
			}

			top = rect.top + window.scrollY - headerOffset - 18;
			scrollWindowTo(top, 'smooth');
		};

		groups.forEach(function (group) {
			var items = Array.prototype.slice.call(group.querySelectorAll('[data-work-card-item]'));

			if (!items.length) {
				return;
			}

			items.forEach(function (item, index) {
				item.dataset.initialOrder = String(index);
			});

			var getItemsPerRow = function (activeItem) {
				var templateColumns = window.getComputedStyle(group).gridTemplateColumns;

				if (!templateColumns || 'none' === templateColumns) {
					return 1;
				}

				if (activeItem && group.clientWidth && activeItem.offsetWidth >= group.clientWidth - 4) {
					return templateColumns.split(' ').length || 1;
				}

				return templateColumns.split(' ').length || 1;
			};

			var resetWorkVideo = function (item) {
				var frame = item.querySelector('[data-work-video-frame]');
				var playButton = item.querySelector('[data-work-video-play]');

				clearVideoFrame(frame);

				if (playButton) {
					playButton.hidden = true;
				}
			};

			var setExpandedImage = function (item, image, media, src, alt) {
				var token;
				var loader;
				var commitImage = function (naturalWidth, naturalHeight) {
					if (src !== image.getAttribute('src')) {
						image.removeAttribute('srcset');
						image.removeAttribute('sizes');
						image.removeAttribute('width');
						image.removeAttribute('height');
						image.src = src;
					}

					image.alt = alt;
					image.hidden = false;

					if (media && naturalWidth && naturalHeight) {
						media.style.aspectRatio = String(naturalWidth) + ' / ' + String(naturalHeight);
						return;
					}

					syncMediaAspectRatio(media, image, '4 / 5');
				};

				if (!image || !src) {
					return 'empty';
				}

				if (src === image.getAttribute('src') || src === image.currentSrc) {
					commitImage(image.naturalWidth, image.naturalHeight);
					return 'synced';
				}

				token = (item._mediaLoadToken || 0) + 1;
				item._mediaLoadToken = token;
				loader = new Image();

				loader.onload = function () {
					var finish = function () {
						if (item._mediaLoadToken !== token) {
							return;
						}

						commitImage(loader.naturalWidth, loader.naturalHeight);
					};

					if (loader.decode) {
						loader.decode().then(finish).catch(finish);
						return;
					}

					finish();
				};

				loader.onerror = function () {
					if (item._mediaLoadToken !== token) {
						return;
					}

					commitImage(0, 0);
				};

				loader.src = src;
				return 'pending';
			};

			var updateExpandedMedia = function (item) {
				var button = item.querySelector('[data-work-card]');
				var image = item.querySelector('[data-work-image-current]');
				var placeholder = item.querySelector('[data-work-placeholder-current]');
				var media = item.querySelector('.project-card__media');
				var frame = item.querySelector('[data-work-video-frame]');
				var nav = item.querySelector('[data-work-nav]');
				var counter = item.querySelector('[data-work-counter]');
				var caption = item.querySelector('[data-work-caption]');
				var credit = item.querySelector('[data-work-credit]');
				var playButton = item.querySelector('[data-work-video-play]');
				var prev = item.querySelector('[data-work-prev]');
				var next = item.querySelector('[data-work-next]');
				var currentMedia;
				var mediaSrc;
				var mediaState = 'empty';

				if (!button || (!image && !placeholder)) {
					return;
				}

				item._media = item._media || [];
				item._slideIndex = item._slideIndex || 0;
				currentMedia = item._media[item._slideIndex];

				resetWorkVideo(item);

				if (currentMedia) {
					if ('video' === currentMedia.type && currentMedia.videoUrl) {
						item._mediaLoadToken = (item._mediaLoadToken || 0) + 1;

						if (image) {
							image.hidden = true;
						}

						if (placeholder) {
							placeholder.hidden = true;
						}

						if (media) {
							media.style.aspectRatio = '16 / 9';
						}

						if (frame) {
							frame.innerHTML = buildNativeVideo(
								currentMedia.videoUrl,
								currentMedia.mimeType || '',
								currentMedia.posterUrl || ''
							);
							frame.hidden = false;
							initNativeVideoFrame(frame);
						}

						mediaState = 'synced';
					} else {
						mediaSrc = currentMedia.posterUrl || currentMedia.url;

						if (image && mediaSrc) {
							mediaState = setExpandedImage(
								item,
								image,
								media,
								mediaSrc,
								currentMedia.alt || button.dataset.workImageAlt || button.dataset.workTitle || ''
							);
						} else if (image) {
							item._mediaLoadToken = (item._mediaLoadToken || 0) + 1;
							image.hidden = true;
						}

						if (placeholder) {
							placeholder.hidden = !!mediaSrc;
						}

						if (playButton && 'youtube' === currentMedia.type && currentMedia.autoplayEmbedUrl) {
							playButton.hidden = false;
						}
					}
				} else {
					item._mediaLoadToken = (item._mediaLoadToken || 0) + 1;

					if (image) {
						image.hidden = true;
					}

					if (placeholder) {
						placeholder.hidden = false;
					}
				}

				if (media && 'synced' !== mediaState && 'pending' !== mediaState) {
					syncMediaAspectRatio(media, image, currentMedia ? '4 / 5' : '');
				}

				if (nav) {
					nav.hidden = false;
				}

				if (prev) {
					prev.hidden = item._media.length <= 1;
				}

				if (next) {
					next.hidden = item._media.length <= 1;
				}

				if (counter) {
					counter.hidden = item._media.length <= 1;
					counter.textContent = item._media.length > 1 ? String(item._slideIndex + 1) + ' / ' + String(item._media.length) : '';
				}

				if (caption) {
					caption.textContent = currentMedia && currentMedia.caption ? currentMedia.caption : '';
					caption.hidden = '' === caption.textContent;
				}

				if (credit) {
					credit.textContent = currentMedia && currentMedia.credit ? currentMedia.credit : '';
					credit.hidden = '' === credit.textContent;
				}
			};

			var playWorkVideo = function (item) {
				var frame = item.querySelector('[data-work-video-frame]');
				var playButton = item.querySelector('[data-work-video-play]');
				var button = item.querySelector('[data-work-card]');
				var currentMedia = item._media && item._media[item._slideIndex] ? item._media[item._slideIndex] : null;

				if (
					!frame ||
					!playButton ||
					!currentMedia ||
					'youtube' !== currentMedia.type ||
					!currentMedia.autoplayEmbedUrl
				) {
					return;
				}

				frame.innerHTML = buildVideoIframe(currentMedia.autoplayEmbedUrl, button ? button.dataset.workTitle : '');
				frame.hidden = false;
				playButton.hidden = true;
			};

			var restoreItem = function (item) {
				var initialOrder = parseInt(item.dataset.initialOrder || '0', 10);
				var siblings = Array.prototype.slice
					.call(group.querySelectorAll('[data-work-card-item]'))
					.filter(function (otherItem) {
						return otherItem !== item;
					});
				var nextSibling = siblings.find(function (otherItem) {
					return parseInt(otherItem.dataset.initialOrder || '0', 10) > initialOrder;
				});

				group.insertBefore(item, nextSibling || null);
			};

			var closeItem = function (item, shouldScroll) {
				var button = item.querySelector('[data-work-card]');
				var expand = item.querySelector('[data-work-expand]');
				var media = item.querySelector('.project-card__media');
				var nav = item.querySelector('[data-work-nav]');
				var counter = item.querySelector('[data-work-counter]');
				var caption = item.querySelector('[data-work-caption]');
				var credit = item.querySelector('[data-work-credit]');

				shouldScroll = false !== shouldScroll;

				animateItem(item, function () {
					if (button) {
						button.setAttribute('aria-expanded', 'false');
					}

					item.classList.remove('is-active');

					if (expand) {
						expand.hidden = true;
					}

					if (nav) {
						nav.hidden = true;
					}

					if (counter) {
						counter.textContent = '';
					}

					if (caption) {
						caption.textContent = '';
						caption.hidden = true;
					}

					if (credit) {
						credit.textContent = '';
						credit.hidden = true;
					}

					resetWorkVideo(item);
					clearMediaAspectRatio(media);

					restoreItem(item);
				});

				if (shouldScroll) {
					window.setTimeout(function () {
						scrollToWorkItem(item);
					}, 120);
				}
			};

			var isWorkCardInternalControl = function (event) {
				var target = event.target;

				return !!(
					target &&
					'function' === typeof target.closest &&
					target.closest(
						'.nunlab-player, [data-work-video-frame], [data-work-video-play], [data-work-nav], [data-work-expand], button, a, input, textarea, select, [contenteditable="true"]'
					)
				);
			};

			items.forEach(function (item) {
				var button = item.querySelector('[data-work-card]');
				var expand = item.querySelector('[data-work-expand]');
				var prev = item.querySelector('[data-work-prev]');
				var next = item.querySelector('[data-work-next]');
				var close = item.querySelector('[data-work-close]');
				var media = item.querySelector('.project-card__media');
				var videoFrame = item.querySelector('[data-work-video-frame]');
				var playButton = item.querySelector('[data-work-video-play]');

				if (!button || !expand) {
					return;
				}

				item._media = [];
				item._slideIndex = 0;

				if (button.dataset.workMedia) {
					try {
						item._media = JSON.parse(button.dataset.workMedia) || [];
					} catch (error) {
						item._media = [];
					}
				}

				if (!item._media.length && button.dataset.workImage) {
					item._media = [
						{
							type: 'image',
							url: button.dataset.workImage,
							posterUrl: button.dataset.workImage,
							alt: button.dataset.workImageAlt || button.dataset.workTitle || '',
							credit: button.dataset.workImageCredit || '',
							embedUrl: '',
							autoplayEmbedUrl: '',
							videoUrl: '',
							mimeType: '',
						},
					];
				}

				item.classList.toggle('has-multiple-media', item._media.length > 1);

				var goToPreviousSlide = function () {
					if (!item._media.length) {
						return;
					}

					item._slideIndex = (item._slideIndex - 1 + item._media.length) % item._media.length;
					updateExpandedMedia(item);
					window.requestAnimationFrame(function () {
						scrollToWorkMedia(item);
					});
				};

				var goToNextSlide = function () {
					if (!item._media.length) {
						return;
					}

					item._slideIndex = (item._slideIndex + 1) % item._media.length;
					updateExpandedMedia(item);
					window.requestAnimationFrame(function () {
						scrollToWorkMedia(item);
					});
				};

				if (prev) {
					prev.addEventListener('click', function (event) {
						event.stopPropagation();
						goToPreviousSlide();
					});
				}

				if (next) {
					next.addEventListener('click', function (event) {
						event.stopPropagation();
						goToNextSlide();
					});
				}

				if (close) {
					close.addEventListener('click', function (event) {
						event.stopPropagation();
						closeItem(item);
					});
				}

				bindHorizontalSwipe(
					media,
					goToPreviousSlide,
					goToNextSlide,
					function () {
						return item.classList.contains('is-active') && item._media.length > 1;
					}
				);

				if (playButton) {
					playButton.addEventListener('click', function (event) {
						event.stopPropagation();
						playWorkVideo(item);
					});
				}

				if (videoFrame) {
					videoFrame.addEventListener('click', function (event) {
						event.stopPropagation();
					});
					videoFrame.addEventListener('keydown', function (event) {
						event.stopPropagation();
					});
				}

				var activateItem = function () {
					var activeItem = group.querySelector('.project-card--directory.is-active');
					var workingItems;
					var index;
					var itemsPerRow;
					var rowStartIndex;
					var rowAnchor;

					if (activeItem && activeItem !== item) {
						closeItem(activeItem, false);
					}

					if (item.classList.contains('is-active')) {
						closeItem(item);
						return;
					}

					workingItems = Array.prototype.slice.call(group.querySelectorAll('[data-work-card-item]'));
					index = workingItems.indexOf(item);
					itemsPerRow = getItemsPerRow(item);
					rowStartIndex = Math.max(0, Math.floor(index / itemsPerRow) * itemsPerRow);
					rowAnchor = workingItems[rowStartIndex];

					item._slideIndex = 0;

					animateItem(item, function () {
						if (rowAnchor && rowAnchor !== item) {
							group.insertBefore(item, rowAnchor);
						}

						button.setAttribute('aria-expanded', 'true');
						item.classList.add('is-active');
						expand.hidden = false;
						updateExpandedMedia(item);
					});

					window.setTimeout(function () {
						scrollToWorkItem(item);
					}, 120);
				};

				button.addEventListener('click', function (event) {
					var rect;

					if (isWorkCardInternalControl(event)) {
						return;
					}

					if (!item.classList.contains('is-active')) {
						activateItem();
						return;
					}

					if (!item._media || item._media.length <= 1 || !media) {
						return;
					}

					rect = media.getBoundingClientRect();

					if (event.clientX < rect.left + rect.width / 2) {
						goToPreviousSlide();
						return;
					}

					goToNextSlide();
				});
				button.addEventListener('keydown', function (event) {
					if (isWorkCardInternalControl(event)) {
						return;
					}

					if ('Enter' !== event.key && ' ' !== event.key) {
						return;
					}

					event.preventDefault();
					activateItem();
				});
			});

			document.addEventListener('keydown', function (event) {
				var activeItem = group.querySelector('.project-card--directory.is-active');

				if (!activeItem || isTypingContext()) {
					return;
				}

				if ('Escape' === event.key) {
					event.preventDefault();
					closeItem(activeItem);
					return;
				}

				if ('ArrowLeft' === event.key && activeItem._media && activeItem._media.length > 1) {
					event.preventDefault();
					activeItem._slideIndex = (activeItem._slideIndex - 1 + activeItem._media.length) % activeItem._media.length;
					updateExpandedMedia(activeItem);
					window.requestAnimationFrame(function () {
						scrollToWorkMedia(activeItem);
					});
					return;
				}

				if ('ArrowRight' === event.key && activeItem._media && activeItem._media.length > 1) {
					event.preventDefault();
					activeItem._slideIndex = (activeItem._slideIndex + 1) % activeItem._media.length;
					updateExpandedMedia(activeItem);
					window.requestAnimationFrame(function () {
						scrollToWorkMedia(activeItem);
					});
				}
			});

			document.addEventListener('click', function (event) {
				var activeItem = group.querySelector('.project-card--directory.is-active');

				if (!activeItem || !event.target || activeItem.contains(event.target)) {
					return;
				}

				closeItem(activeItem, false);
			});
		});
	};

	document.addEventListener('DOMContentLoaded', function () {
		[
			initHeroStage,
			initSectionAwareNav,
			initWorkListings,
		].forEach(function (init) {
			try {
				init();
			} catch (error) {
				if (window.console && window.console.error) {
					window.console.error('N:UN front-page init failed:', error);
				}
			}
		});
	});
})();

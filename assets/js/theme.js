// Shared theme interactions loaded on every page.

(function () {
	'use strict';

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

	// Overlay search stays separate so it can close the mobile menu cleanly.
	var initSearchOverlay = function () {
		var body = document.body;
		var header = document.querySelector('.site-header');
		var searchLayer = document.querySelector('[data-search-layer]');
		var searchToggle = document.querySelector('[data-search-toggle]');
		var searchClose = document.querySelector('[data-search-close]');
		var searchInput = document.querySelector('[data-search-input]');

		if (!searchLayer || !searchToggle || !searchInput) {
			return;
		}

		var setExpanded = function (value) {
			searchToggle.setAttribute('aria-expanded', value ? 'true' : 'false');
		};

		var openSearch = function () {
			var menuToggle = document.querySelector('[data-menu-toggle]');

			if (header) {
				header.classList.remove('site-header--menu-open');
			}

			body.classList.remove('has-mobile-menu');

			if (menuToggle) {
				menuToggle.setAttribute('aria-expanded', 'false');
			}

			searchLayer.hidden = false;
			window.requestAnimationFrame(function () {
				searchLayer.classList.add('is-visible');
			});
			body.classList.add('has-search-overlay');
			setExpanded(true);
			window.setTimeout(function () {
				searchInput.focus();
				searchInput.select();
			}, 80);
		};

		var closeSearch = function () {
			searchLayer.classList.remove('is-visible');
			body.classList.remove('has-search-overlay');
			setExpanded(false);
			window.setTimeout(function () {
				searchLayer.hidden = true;
			}, 220);
		};

		searchToggle.addEventListener('click', function () {
			if (searchLayer.hidden) {
				openSearch();
				return;
			}

			closeSearch();
		});

		if (searchClose) {
			searchClose.addEventListener('click', closeSearch);
		}

		searchLayer.addEventListener('click', function (event) {
			if (event.target === searchLayer) {
				closeSearch();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && !searchLayer.hidden) {
				closeSearch();
			}

			if (
				event.key === '/' &&
				searchLayer.hidden &&
				!event.metaKey &&
				!event.ctrlKey &&
				!event.altKey &&
				!isTypingContext()
			) {
				event.preventDefault();
				openSearch();
			}
		});
	};

	// The header collapses into its compact navigation state before mobile.
	var initMobileMenu = function () {
		var body = document.body;
		var header = document.querySelector('.site-header');
		var menuToggle = document.querySelector('[data-menu-toggle]');
		var menuPanel = document.querySelector('[data-menu-panel]');

		if (!header || !menuToggle || !menuPanel) {
			return;
		}

		var setOpenState = function (isOpen) {
			header.classList.toggle('site-header--menu-open', isOpen);
			body.classList.toggle('has-mobile-menu', isOpen);
			menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		};

		menuToggle.addEventListener('click', function () {
			setOpenState(!header.classList.contains('site-header--menu-open'));
		});

		menuPanel.querySelectorAll('a').forEach(function (link) {
			link.addEventListener('click', function () {
				setOpenState(false);
			});
		});

		document.addEventListener('click', function (event) {
			if (
				header.classList.contains('site-header--menu-open') &&
				!header.contains(event.target)
			) {
				setOpenState(false);
			}
		});

		document.addEventListener('keydown', function (event) {
			if ('Escape' === event.key && header.classList.contains('site-header--menu-open')) {
				setOpenState(false);
			}
		});

		var desktopQuery = window.matchMedia('(min-width: 1101px)');

		desktopQuery.addEventListener('change', function (event) {
			if (event.matches) {
				setOpenState(false);
			}
		});
	};

	// Small scroll compression keeps the sticky header calm.
	var initStickyHeader = function () {
		var header = document.querySelector('.site-header');

		if (!header) {
			return;
		}

		var syncHeaderState = function () {
			header.classList.toggle('site-header--scrolled', window.scrollY > 18);
		};

		syncHeaderState();
		window.addEventListener('scroll', syncHeaderState, { passive: true });
	};

	var createVideoPlayerButton = function (modifier, label) {
		var button = document.createElement('button');

		button.type = 'button';
		button.className = 'nunlab-player__button nunlab-player__button--' + modifier;
		button.setAttribute('aria-label', label);

		return button;
	};

	var createVideoPlayerPosterButton = function () {
		var button = document.createElement('button');

		button.type = 'button';
		button.className = 'nunlab-player__poster-play';
		button.setAttribute('aria-label', 'Play video');

		return button;
	};

	var createVideoPlayerTimeline = function () {
		var timeline = document.createElement('button');
		var track = document.createElement('span');
		var tooltip = document.createElement('span');

		timeline.type = 'button';
		timeline.className = 'nunlab-player__timeline';
		timeline.setAttribute('aria-label', 'Seek video');

		track.className = 'nunlab-player__timeline-track';
		tooltip.className = 'nunlab-player__chapter-tooltip';
		tooltip.setAttribute('aria-hidden', 'true');
		tooltip.hidden = true;

		timeline.appendChild(track);
		timeline.appendChild(tooltip);

		return timeline;
	};

	var createVideoPlayerTool = function (button, modifier) {
		var tool = document.createElement('span');

		tool.className =
			'nunlab-player__tool' + (modifier ? ' nunlab-player__tool--' + modifier : '');
		tool.appendChild(button);

		return tool;
	};

	var formatVideoTime = function (seconds) {
		var safeSeconds = Number.isFinite(seconds) && seconds > 0 ? Math.floor(seconds) : 0;
		var minutes = Math.floor(safeSeconds / 60);
		var remainingSeconds = safeSeconds % 60;

		return String(minutes) + ':' + String(remainingSeconds).padStart(2, '0');
	};

	var parseVideoTimecode = function (value) {
		var parts = value.trim().replace(',', '.').split(':').map(Number);

		if (
			!parts.length ||
			parts.some(function (part) {
				return !Number.isFinite(part);
			})
		) {
			return null;
		}

		if (3 === parts.length) {
			return parts[0] * 3600 + parts[1] * 60 + parts[2];
		}

		if (2 === parts.length) {
			return parts[0] * 60 + parts[1];
		}

		return null;
	};

	var parseVideoChapters = function (text) {
		var chapters = [];
		var lines = text.split(/\r?\n/);

		lines.forEach(function (line, index) {
			var chapterLine = line.trim();
			var timeParts = [];
			var start = null;
			var titleLines = [];
			var titleIndex = index + 1;

			if (-1 === chapterLine.indexOf('-->')) {
				return;
			}

			timeParts = chapterLine.split('-->');
			start = parseVideoTimecode(timeParts[0]);

			while (titleIndex < lines.length && lines[titleIndex].trim()) {
				titleLines.push(lines[titleIndex].trim());
				titleIndex += 1;
			}

			if (Number.isFinite(start)) {
				chapters.push({
					start: start,
					title: titleLines.join(' '),
				});
			}
		});

		return chapters.sort(function (first, second) {
			return first.start - second.start;
		});
	};

	var getVideoTrackElements = function (video, kinds) {
		return Array.prototype.filter.call(video.querySelectorAll('track'), function (track) {
			return -1 !== kinds.indexOf((track.kind || '').toLowerCase());
		});
	};

	var renderVideoTimelineSegments = function (timeline, video, chapters) {
		var track = timeline.querySelector('.nunlab-player__timeline-track');
		var duration = Number.isFinite(video.duration) && video.duration > 0 ? video.duration : 0;
		var starts = [0];
		var labels = {};

		if (!track) {
			return;
		}

		if (duration && chapters.length) {
			chapters.forEach(function (chapter) {
				var start = chapter.start <= 0.05 ? 0 : chapter.start;
				var key = '';

				if (start < 0 || start >= duration) {
					return;
				}

				key = start.toFixed(3);
				labels[key] = chapter.title;

				if (
					!starts.some(function (existingStart) {
						return Math.abs(existingStart - start) < 0.05;
					})
				) {
					starts.push(start);
				}
			});
		}

		starts = starts
			.filter(function (start, index, list) {
				return (
					start >= 0 &&
					(!duration || start < duration) &&
					list.findIndex(function (candidate) {
						return Math.abs(candidate - start) < 0.05;
					}) === index
				);
			})
			.sort(function (first, second) {
				return first - second;
			});

		if (!starts.length) {
			starts = [0];
		}

		track.replaceChildren();
		timeline.classList.toggle('has-chapters', starts.length > 1);

		starts.forEach(function (start, index) {
			var segment = document.createElement('span');
			var end = duration && starts[index + 1] ? starts[index + 1] : duration || 1;
			var label = labels[start.toFixed(3)] || '';

				segment.className = 'nunlab-player__timeline-segment';
				segment.dataset.start = String(start);
				segment.dataset.end = String(end);
				segment.style.flexGrow = String(Math.max(end - start, 0.15));

				if (label) {
					segment.dataset.title = label;
				}

			track.appendChild(segment);
		});
	};

	var initNativeVideoPlayer = function (video) {
		var wrapper = null;
		var chrome = null;
		var timelineGroup = null;
		var tools = null;
		var soundTool = null;
		var speedTool = null;
		var captionOverlay = null;
		var playButton = null;
		var soundButton = null;
		var subtitlesButton = null;
		var speedButton = null;
		var fullscreenButton = null;
		var posterButton = null;
		var volumePanel = null;
		var volumeRange = null;
		var speedMenu = null;
		var timeline = null;
		var timeDisplay = null;
		var chapterCues = [];
		var captionTracks = getVideoTrackElements(video, ['captions', 'subtitles']);
		var chapterTracks = getVideoTrackElements(video, ['chapters']);
		var isPointerInsidePlayer = false;

		if (
			video.dataset.nunlabPlayerReady ||
			video.closest('.nunlab-player, .hero-stage, .project-gallery, .project-card') ||
			video.classList.contains('wp-video-shortcode')
		) {
			return;
		}

		video.dataset.nunlabPlayerReady = 'true';
		video.controls = false;
		video.playsInline = true;
		video.setAttribute('playsinline', '');
		video.classList.add('nunlab-player__video');

		wrapper = document.createElement('div');
		wrapper.className = 'nunlab-player nunlab-player--native';
		wrapper.tabIndex = 0;
		wrapper.setAttribute('aria-label', 'Video player');
		video.parentNode.insertBefore(wrapper, video);
		wrapper.appendChild(video);

		captionOverlay = document.createElement('div');
		captionOverlay.className = 'nunlab-player__captions';
		captionOverlay.setAttribute('aria-hidden', 'true');
		wrapper.appendChild(captionOverlay);

		posterButton = createVideoPlayerPosterButton();
		wrapper.appendChild(posterButton);

		chrome = document.createElement('div');
		chrome.className = 'nunlab-player__chrome';
		chrome.setAttribute('aria-label', 'Video controls');

		playButton = createVideoPlayerButton('play', 'Play video');
		timelineGroup = document.createElement('span');
		timelineGroup.className = 'nunlab-player__timeline-group';
		timeline = createVideoPlayerTimeline();
		timeDisplay = document.createElement('span');
		timeDisplay.className = 'nunlab-player__time';
		timeDisplay.textContent = '0:00 / 0:00';
		tools = document.createElement('div');
		tools.className = 'nunlab-player__tools';

		soundButton = createVideoPlayerButton('sound', 'Mute video');
		subtitlesButton = createVideoPlayerButton('subtitles', 'Toggle captions');
		speedButton = createVideoPlayerButton('speed', 'Playback speed');
		fullscreenButton = createVideoPlayerButton('fullscreen', 'Enter fullscreen');
		volumePanel = document.createElement('div');
		volumeRange = document.createElement('input');
		speedMenu = document.createElement('div');

		volumePanel.className = 'nunlab-player__volume-panel';
		volumePanel.hidden = true;
		volumeRange.type = 'range';
		volumeRange.min = '0';
		volumeRange.max = '1';
		volumeRange.step = '0.01';
		volumeRange.value = String(video.volume);
		volumeRange.setAttribute('aria-label', 'Volume');
		volumePanel.appendChild(volumeRange);
		soundButton.setAttribute('aria-expanded', 'false');

		speedMenu.className = 'nunlab-player__speed-menu';
		speedMenu.hidden = true;
		speedButton.setAttribute('aria-expanded', 'false');

		[2, 1.5, 1.25, 1].forEach(function (rate) {
			var rateButton = document.createElement('button');

			rateButton.type = 'button';
			rateButton.textContent = 'x ' + rate.toFixed(2);
			rateButton.dataset.nunlabVideoRate = String(rate);
			speedMenu.appendChild(rateButton);
		});

		soundTool = createVideoPlayerTool(soundButton, 'sound');
		speedTool = createVideoPlayerTool(speedButton, 'speed');
		soundTool.appendChild(volumePanel);
		speedTool.appendChild(speedMenu);

		tools.appendChild(soundTool);
		tools.appendChild(subtitlesButton);
		tools.appendChild(speedTool);
		tools.appendChild(fullscreenButton);
		timelineGroup.appendChild(timeline);
		timelineGroup.appendChild(timeDisplay);
		chrome.appendChild(playButton);
		chrome.appendChild(timelineGroup);
		chrome.appendChild(tools);
		wrapper.appendChild(chrome);

		var closeVolumePanel = function () {
			volumePanel.hidden = true;
			soundButton.setAttribute('aria-expanded', 'false');
			wrapper.classList.remove('has-volume-menu-open');
		};

			var closeSpeedMenu = function () {
				speedMenu.hidden = true;
				speedButton.setAttribute('aria-expanded', 'false');
				wrapper.classList.remove('has-speed-menu-open');
			};

			var hideChapterTooltip = function () {
				var tooltip = timeline.querySelector('.nunlab-player__chapter-tooltip');

				if (!tooltip) {
					return;
				}

				tooltip.hidden = true;
				timeline.classList.remove('has-chapter-tooltip');
			};

			var showChapterTooltip = function (segment) {
				var tooltip = timeline.querySelector('.nunlab-player__chapter-tooltip');
				var label = segment ? segment.dataset.title : '';
				var timelineBounds = null;
				var segmentBounds = null;
				var x = 50;

				if (!tooltip || !label) {
					hideChapterTooltip();
					return;
				}

				timelineBounds = timeline.getBoundingClientRect();
				segmentBounds = segment.getBoundingClientRect();

				if (timelineBounds.width) {
					x =
						((segmentBounds.left + segmentBounds.width / 2 - timelineBounds.left) /
							timelineBounds.width) *
						100;
				}

				timeline.style.setProperty(
					'--nunlab-chapter-tooltip-x',
					Math.min(Math.max(x, 6), 94).toFixed(3) + '%'
				);
				tooltip.textContent = label;
				tooltip.hidden = false;
				timeline.classList.add('has-chapter-tooltip');
			};

			var updateChapterTooltipFromEvent = function (event) {
				var segment =
					event.target && 'function' === typeof event.target.closest
						? event.target.closest('.nunlab-player__timeline-segment')
						: null;

				if (!segment || !timeline.contains(segment)) {
					hideChapterTooltip();
					return;
				}

				showChapterTooltip(segment);
			};

		var syncAspectRatio = function () {
			if (video.videoWidth && video.videoHeight) {
				wrapper.style.setProperty(
					'--nunlab-player-ratio',
					video.videoWidth + ' / ' + video.videoHeight
				);
			}
		};

		var syncPlayState = function () {
			var isPlaying = !video.paused && !video.ended;

			wrapper.classList.toggle('is-playing', isPlaying);
			wrapper.classList.toggle('has-started', wrapper.classList.contains('has-started') || isPlaying);
			playButton.setAttribute('aria-label', isPlaying ? 'Pause video' : 'Play video');
		};

		var syncSoundState = function () {
			var isMuted = video.muted || 0 === video.volume;
			var volume = isMuted ? 0 : video.volume;

			wrapper.classList.toggle('is-muted', isMuted);
			wrapper.style.setProperty('--nunlab-player-volume', (volume * 100).toFixed(1) + '%');
			volumeRange.value = String(volume);
			soundButton.setAttribute('aria-label', 'Open volume controls');
		};

		var syncProgress = function () {
			var segments = timeline.querySelectorAll('.nunlab-player__timeline-segment');

			segments.forEach(function (segment) {
				var segmentStart = Number(segment.dataset.start) || 0;
				var segmentEnd = Number(segment.dataset.end) || video.duration || 1;
				var segmentProgress = (video.currentTime - segmentStart) / (segmentEnd - segmentStart);

				segment.style.setProperty(
					'--nunlab-segment-progress',
					(Math.min(Math.max(segmentProgress, 0), 1) * 100).toFixed(3) + '%'
				);
			});
		};

		var syncTime = function () {
			timeDisplay.textContent =
				formatVideoTime(video.currentTime) + ' / ' + formatVideoTime(video.duration);
		};

		var syncTimelineSegments = function () {
			renderVideoTimelineSegments(timeline, video, chapterCues);
		};

		var loadChapterTrack = function () {
			var chapterTrack = chapterTracks[0];

			syncTimelineSegments();

			if (!chapterTrack || !chapterTrack.src || !window.fetch) {
				return;
			}

			window
				.fetch(chapterTrack.src, { credentials: 'same-origin' })
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Chapter track could not be loaded.');
					}

					return response.text();
				})
				.then(function (text) {
					chapterCues = parseVideoChapters(text);
					syncTimelineSegments();
					syncProgress();
				})
				.catch(function () {
					chapterCues = [];
					syncTimelineSegments();
					syncProgress();
				});
		};

		var syncFullscreen = function () {
			var isFullscreen = document.fullscreenElement === wrapper;

			wrapper.classList.toggle('is-fullscreen', isFullscreen);
			fullscreenButton.setAttribute(
				'aria-label',
				isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'
			);
		};

		var togglePlay = function () {
			var playPromise = null;

			if (video.paused || video.ended) {
				playPromise = video.play();

				if (playPromise && 'function' === typeof playPromise.catch) {
					playPromise.catch(function () {
						syncPlayState();
					});
				}

				return;
			}

			video.pause();
		};

		var seekFromEvent = function (event) {
			var bounds = timeline.getBoundingClientRect();
			var ratio = 0;

			if (!video.duration || !bounds.width) {
				return;
			}

			ratio = Math.min(Math.max((event.clientX - bounds.left) / bounds.width, 0), 1);
			video.currentTime = video.duration * ratio;
		};

		var nudgeSeek = function (seconds) {
			if (!video.duration) {
				return;
			}

			video.currentTime = Math.min(Math.max(video.currentTime + seconds, 0), video.duration);

			syncProgress();
			syncTime();
		};

		var updateCaptionOverlay = function () {
			var captionLines = [];

			captionTracks.forEach(function (trackElement) {
				var textTrack = trackElement.track;

				if (!textTrack || 'disabled' === textTrack.mode || !textTrack.activeCues) {
					return;
				}

				Array.prototype.forEach.call(textTrack.activeCues, function (cue) {
					if (cue.text) {
						captionLines = captionLines.concat(cue.text.split(/\n+/));
					}
				});
			});

			captionOverlay.replaceChildren();

			captionLines
				.map(function (line) {
					return line.trim();
				})
				.filter(Boolean)
				.forEach(function (line) {
					var captionLine = document.createElement('span');

					captionLine.className = 'nunlab-player__caption-line';
					captionLine.textContent = line;
					captionOverlay.appendChild(captionLine);
				});

			wrapper.classList.toggle('has-captions-visible', 0 < captionOverlay.childElementCount);
		};

		var toggleCaptions = function () {
			var shouldShow = !wrapper.classList.contains('has-captions-on');

			captionTracks.forEach(function (trackElement) {
				if (!trackElement.track) {
					return;
				}

				trackElement.track.mode = shouldShow ? 'hidden' : 'disabled';
			});

			wrapper.classList.toggle('has-captions-on', shouldShow);
			subtitlesButton.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
			updateCaptionOverlay();
		};

		var shouldUsePlayerShortcut = function (event) {
			var target = event.target;
			var targetTag = target && target.tagName ? target.tagName.toLowerCase() : '';

			if (event.defaultPrevented || event.altKey || event.ctrlKey || event.metaKey) {
				return false;
			}

			if (!isPointerInsidePlayer && !wrapper.contains(document.activeElement)) {
				return false;
			}

			if (
				target &&
				(('function' === typeof target.closest &&
					target.closest('input, textarea, select, [contenteditable="true"]')) ||
					('button' === targetTag &&
						(' ' === event.key || 'Enter' === event.key || 'Spacebar' === event.key)))
			) {
				return false;
			}

			return true;
		};

		var handlePlayerShortcut = function (event) {
			var key = event.key.toLowerCase();

			if (!shouldUsePlayerShortcut(event)) {
				return;
			}

			if (' ' === event.key || 'Spacebar' === event.key) {
				if (event.repeat) {
					return;
				}

				event.preventDefault();
				togglePlay();
				return;
			}

			if ('arrowleft' === key) {
				event.preventDefault();
				nudgeSeek(-5);
				updateCaptionOverlay();
				return;
			}

			if ('arrowright' === key) {
				event.preventDefault();
				nudgeSeek(5);
				updateCaptionOverlay();
				return;
			}

			if (('c' === key || 's' === key) && captionTracks.length) {
				if (event.repeat) {
					return;
				}

				event.preventDefault();
				toggleCaptions();
			}
		};

		var updateSpeedButtons = function () {
			Array.prototype.forEach.call(speedMenu.querySelectorAll('button'), function (button) {
				button.classList.toggle(
					'is-active',
					Number(button.dataset.nunlabVideoRate) === video.playbackRate
				);
			});
		};

			playButton.addEventListener('click', togglePlay);
			posterButton.addEventListener('click', togglePlay);
			video.addEventListener('click', function () {
				wrapper.focus({ preventScroll: true });
				togglePlay();
			});
			wrapper.addEventListener('pointerenter', function () {
				isPointerInsidePlayer = true;
			});
			wrapper.addEventListener('pointerleave', function () {
				isPointerInsidePlayer = false;
			});
		video.addEventListener('play', syncPlayState);
		video.addEventListener('pause', syncPlayState);
		video.addEventListener('ended', syncPlayState);
		video.addEventListener('volumechange', syncSoundState);
		video.addEventListener('timeupdate', function () {
			syncProgress();
			syncTime();
			updateCaptionOverlay();
		});
		video.addEventListener('seeked', updateCaptionOverlay);
		video.addEventListener('loadedmetadata', function () {
			syncAspectRatio();
			syncTimelineSegments();
			syncProgress();
			syncTime();
			updateCaptionOverlay();
		});
		video.addEventListener('durationchange', function () {
			syncTimelineSegments();
			syncProgress();
			syncTime();
		});

		soundButton.addEventListener('click', function () {
			var shouldOpen = volumePanel.hidden;

			closeSpeedMenu();
			volumePanel.hidden = !shouldOpen;
			soundButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
			wrapper.classList.toggle('has-volume-menu-open', shouldOpen);
		});

		volumeRange.addEventListener('input', function () {
			video.volume = Number(volumeRange.value);
			video.muted = 0 === video.volume;
			syncSoundState();
		});

			timeline.addEventListener('click', seekFromEvent);
			timeline.addEventListener('pointermove', updateChapterTooltipFromEvent);
			timeline.addEventListener('pointerleave', hideChapterTooltip);
			timeline.addEventListener('blur', hideChapterTooltip);
			timeline.addEventListener('keydown', function (event) {
				if ('ArrowLeft' === event.key) {
					event.preventDefault();
					nudgeSeek(-5);
				}

				if ('ArrowRight' === event.key) {
					event.preventDefault();
					nudgeSeek(5);
				}
			});

		if (captionTracks.length) {
			subtitlesButton.setAttribute('aria-pressed', 'false');
			subtitlesButton.addEventListener('click', toggleCaptions);
			captionTracks.forEach(function (trackElement) {
				if (trackElement.track) {
					trackElement.track.mode = 'disabled';
					trackElement.track.addEventListener('cuechange', updateCaptionOverlay);
				}

				trackElement.addEventListener('load', updateCaptionOverlay);
			});
		} else {
			subtitlesButton.disabled = true;
		}

		speedButton.addEventListener('click', function () {
			var shouldOpen = speedMenu.hidden;

			closeVolumePanel();
			speedMenu.hidden = !speedMenu.hidden;
			speedButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
			wrapper.classList.toggle('has-speed-menu-open', shouldOpen);
		});

		speedMenu.addEventListener('click', function (event) {
			var rateButton = event.target.closest('[data-nunlab-video-rate]');

			if (!rateButton) {
				return;
			}

			video.playbackRate = Number(rateButton.dataset.nunlabVideoRate);
			updateSpeedButtons();
			closeSpeedMenu();
		});

		fullscreenButton.addEventListener('click', function () {
			if (document.fullscreenElement === wrapper) {
				if (document.exitFullscreen) {
					document.exitFullscreen();
				} else if (document.webkitExitFullscreen) {
					document.webkitExitFullscreen();
				}

				return;
			}

			if (wrapper.requestFullscreen) {
				wrapper.requestFullscreen();
			} else if (wrapper.webkitRequestFullscreen) {
				wrapper.webkitRequestFullscreen();
			} else if (video.webkitEnterFullscreen) {
				video.webkitEnterFullscreen();
			}
		});

		document.addEventListener('fullscreenchange', syncFullscreen);
		document.addEventListener('webkitfullscreenchange', syncFullscreen);
		document.addEventListener('click', function (event) {
			if (!wrapper.contains(event.target)) {
				closeVolumePanel();
				closeSpeedMenu();
			}
		});
			document.addEventListener('keydown', function (event) {
				handlePlayerShortcut(event);

				if ('Escape' === event.key) {
					closeVolumePanel();
					closeSpeedMenu();
			}
		});

		syncAspectRatio();
		syncPlayState();
		syncSoundState();
		loadChapterTrack();
		syncProgress();
		syncTime();
		updateSpeedButtons();
	};

	// Uploaded content videos get the same N:UN player language as walkthrough posters.
	var initNativeVideoPlayers = function () {
		var videos = document.querySelectorAll(
			'.entry-content .wp-block-video > video[controls], .entry-content > video[controls]'
		);

		videos.forEach(initNativeVideoPlayer);
	};

	document.addEventListener('DOMContentLoaded', function () {
		[
			initSearchOverlay,
			initMobileMenu,
			initStickyHeader,
			initNativeVideoPlayers,
		].forEach(function (init) {
			try {
				init();
			} catch (error) {
				if (window.console && window.console.error) {
					window.console.error('N:UN theme init failed:', error);
				}
			}
		});
	});
})();

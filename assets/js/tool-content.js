// Tool-page interactions for chapter navigation and screenshots.

(function () {
	'use strict';

	var viewer = null;
	var viewerImage = null;
	var viewerCaption = null;
	var closeButton = null;
	var activeTrigger = null;
	var closeTimer = 0;
	var highlightTimer = 0;
	var arrivalFrame = 0;
	var currentChapterHash = '';

	var isOpen = function () {
		return viewer && !viewer.hidden;
	};

	var closeViewer = function () {
		if (!isOpen()) {
			return;
		}

		window.clearTimeout(closeTimer);
		viewer.classList.remove('is-visible');
		document.body.classList.remove('has-tool-image-viewer');

		closeTimer = window.setTimeout(function () {
			viewer.hidden = true;
			viewerImage.removeAttribute('src');

			if (activeTrigger) {
				activeTrigger.focus();
			}
		}, 180);
	};

	var createViewer = function () {
		if (viewer) {
			return;
		}

		viewer = document.createElement('div');
		viewer.className = 'tool-image-viewer';
		viewer.hidden = true;
		viewer.setAttribute('role', 'dialog');
		viewer.setAttribute('aria-modal', 'true');
		viewer.setAttribute('aria-label', 'Screenshot preview');

		closeButton = document.createElement('button');
		closeButton.className = 'tool-image-viewer__close';
		closeButton.type = 'button';
		closeButton.setAttribute('aria-label', 'Close preview');

		var frame = document.createElement('figure');
		frame.className = 'tool-image-viewer__frame';

		viewerImage = document.createElement('img');
		viewerImage.className = 'tool-image-viewer__image';
		viewerImage.alt = '';

		viewerCaption = document.createElement('figcaption');
		viewerCaption.className = 'tool-image-viewer__caption';

		frame.appendChild(viewerImage);
		frame.appendChild(viewerCaption);
		viewer.appendChild(closeButton);
		viewer.appendChild(frame);
		document.body.appendChild(viewer);

		closeButton.addEventListener('click', closeViewer);
		viewer.addEventListener('click', function (event) {
			if (event.target === viewer) {
				closeViewer();
			}
		});

		viewerImage.addEventListener('click', closeViewer);
	};

	var getLargeImageUrl = function (link, image) {
		if (link && link.href) {
			return link.href;
		}

		return image.currentSrc || image.src;
	};

	var openViewer = function (trigger, link, image, caption) {
		var captionText = caption ? caption.textContent.trim() : '';

		createViewer();
		window.clearTimeout(closeTimer);

		activeTrigger = trigger;
		viewerImage.src = getLargeImageUrl(link, image);
		viewerImage.alt = image.alt || '';
		viewerCaption.textContent = captionText;
		viewerCaption.hidden = '' === captionText;
		viewer.hidden = false;
		document.body.classList.add('has-tool-image-viewer');

		window.requestAnimationFrame(function () {
			viewer.classList.add('is-visible');
			closeButton.focus();
		});
	};

	var initToolChapterImages = function () {
		var figures = document.querySelectorAll('.tool-chapter__body figure.wp-block-image');

		if (!figures.length) {
			return;
		}

		figures.forEach(function (figure) {
			var link = figure.querySelector('a[href]');
			var image = figure.querySelector('img');
			var caption = figure.querySelector('figcaption');
			var trigger = link || image;

			if (!trigger || !image) {
				return;
			}

			trigger.classList.add('tool-chapter__image-link');
			trigger.setAttribute('aria-haspopup', 'dialog');

			if (!link) {
				trigger.setAttribute('role', 'button');
				trigger.setAttribute('tabindex', '0');
				trigger.addEventListener('keydown', function (event) {
					if ('Enter' === event.key || ' ' === event.key) {
						event.preventDefault();
						openViewer(trigger, link, image, caption);
					}
				});
			}

			trigger.addEventListener('click', function (event) {
				event.preventDefault();
				openViewer(trigger, link, image, caption);
			});
		});

		document.addEventListener('keydown', function (event) {
			if ('Escape' === event.key && isOpen()) {
				event.preventDefault();
				closeViewer();
			}
		});
	};

	var getChapterFromHash = function (hash) {
		var id = '';
		var target = null;

		if (!hash || '#' === hash) {
			return null;
		}

		try {
			id = window.decodeURIComponent(hash.slice(1));
		} catch (error) {
			id = hash.slice(1);
		}

		target = document.getElementById(id);

		if (!target || !target.classList.contains('tool-chapter')) {
			return null;
		}

		return target;
	};

	var highlightChapter = function (chapter) {
		if (!chapter) {
			return;
		}

		window.clearTimeout(highlightTimer);
		window.cancelAnimationFrame(arrivalFrame);
		document.querySelectorAll('.tool-chapter.is-highlighted').forEach(function (activeChapter) {
			activeChapter.classList.remove('is-highlighted');
		});

		// Restart the CSS animation when the same chapter is triggered again.
		chapter.offsetWidth; // eslint-disable-line no-unused-expressions
		chapter.classList.add('is-highlighted');

		highlightTimer = window.setTimeout(function () {
			chapter.classList.remove('is-highlighted');
		}, 3200);
	};

	var highlightChapterOnArrival = function (chapter) {
		var startedAt = Date.now();

		if (!chapter) {
			return;
		}

		window.cancelAnimationFrame(arrivalFrame);

		var checkArrival = function () {
			var rect = chapter.getBoundingClientRect();
			var isNearReadingPosition = rect.top >= -24 && rect.top <= Math.min(280, window.innerHeight * 0.36);
			var isVisibleAfterWaiting = Date.now() - startedAt > 2400 && rect.bottom > 0 && rect.top < window.innerHeight;
			var hasWaitedLongEnough = Date.now() - startedAt > 3600;

			if (isNearReadingPosition || isVisibleAfterWaiting || hasWaitedLongEnough) {
				highlightChapter(chapter);
				return;
			}

			arrivalFrame = window.requestAnimationFrame(checkArrival);
		};

		checkArrival();
	};

	var getHeaderOffset = function () {
		var header = document.querySelector('.site-header');
		var rect = header ? header.getBoundingClientRect() : null;

		return (rect ? rect.height : 0) + 18;
	};

	var scrollToChapter = function (chapter, reducedMotionQuery) {
		var top = Math.max(0, chapter.getBoundingClientRect().top + window.scrollY - getHeaderOffset());

		window.scrollTo({
			top: top,
			behavior: reducedMotionQuery.matches ? 'auto' : 'smooth',
		});
	};

	var youtubeApiPromise = null;

	var formatVideoTime = function (seconds) {
		var safeSeconds = Number.isFinite(seconds) && seconds > 0 ? Math.floor(seconds) : 0;
		var minutes = Math.floor(safeSeconds / 60);
		var remainingSeconds = safeSeconds % 60;

		return String(minutes) + ':' + String(remainingSeconds).padStart(2, '0');
	};

	var getYoutubeApiSrc = function (src, customControls) {
		var url = null;

		try {
			url = new URL(src, window.location.href);
			url.searchParams.set('autoplay', '1');

			if (customControls) {
				url.searchParams.set('controls', '0');
				url.searchParams.set('enablejsapi', '1');
				url.searchParams.set('origin', window.location.origin);
			}

			return url.toString();
		} catch (error) {
			return (
				src +
				(src.indexOf('?') === -1 ? '?' : '&') +
				'autoplay=1' +
				(customControls
					? '&controls=0&enablejsapi=1&origin=' + window.encodeURIComponent(window.location.origin)
					: '')
			);
		}
	};

	var ensureYoutubeApi = function () {
		if (window.YT && window.YT.Player) {
			return Promise.resolve(window.YT);
		}

		if (youtubeApiPromise) {
			return youtubeApiPromise;
		}

		youtubeApiPromise = new Promise(function (resolve, reject) {
			var previousReady = window.onYouTubeIframeAPIReady;
			var script = document.createElement('script');

			window.onYouTubeIframeAPIReady = function () {
				if ('function' === typeof previousReady) {
					previousReady();
				}

				resolve(window.YT);
			};

			script.src = 'https://www.youtube.com/iframe_api';
			script.async = true;
			script.onerror = reject;
			document.head.appendChild(script);
		});

		return youtubeApiPromise;
	};

	var createPlayerButton = function (modifier, label) {
		var button = document.createElement('button');

		button.type = 'button';
		button.className = 'nunlab-player__button nunlab-player__button--' + modifier;
		button.setAttribute('aria-label', label);

		return button;
	};

	var createPlayerTool = function (button, modifier) {
		var tool = document.createElement('span');

		tool.className =
			'nunlab-player__tool' + (modifier ? ' nunlab-player__tool--' + modifier : '');
		tool.appendChild(button);

		return tool;
	};

	var createPlayerTimeline = function () {
		var timeline = document.createElement('button');
		var track = document.createElement('span');
		var segment = document.createElement('span');
		var tooltip = document.createElement('span');

		timeline.type = 'button';
		timeline.className = 'nunlab-player__timeline';
		timeline.setAttribute('aria-label', 'Seek video');
		track.className = 'nunlab-player__timeline-track';
		segment.className = 'nunlab-player__timeline-segment';
		segment.dataset.start = '0';
		segment.dataset.end = '1';
		tooltip.className = 'nunlab-player__chapter-tooltip';
		tooltip.hidden = true;

		track.appendChild(segment);
		timeline.appendChild(track);
		timeline.appendChild(tooltip);

		return timeline;
	};

	var createCustomYoutubeControls = function () {
		var controls = {};

		controls.chrome = document.createElement('div');
		controls.timelineGroup = document.createElement('span');
		controls.timeDisplay = document.createElement('span');
		controls.tools = document.createElement('div');
		controls.captionOverlay = document.createElement('div');
		controls.volumePanel = document.createElement('div');
		controls.volumeRange = document.createElement('input');
		controls.speedMenu = document.createElement('div');
		controls.playButton = createPlayerButton('play', 'Play video');
		controls.soundButton = createPlayerButton('sound', 'Mute video');
		controls.subtitlesButton = createPlayerButton('subtitles', 'Toggle captions');
		controls.speedButton = createPlayerButton('speed', 'Playback speed');
		controls.fullscreenButton = createPlayerButton('fullscreen', 'Enter fullscreen');
		controls.timeline = createPlayerTimeline();
		controls.soundTool = createPlayerTool(controls.soundButton, 'sound');
		controls.speedTool = createPlayerTool(controls.speedButton, 'speed');

		controls.chrome.className = 'nunlab-player__chrome';
		controls.chrome.setAttribute('aria-label', 'Video controls');
		controls.timelineGroup.className = 'nunlab-player__timeline-group';
		controls.timeDisplay.className = 'nunlab-player__time';
		controls.timeDisplay.textContent = '0:00 / 0:00';
		controls.tools.className = 'nunlab-player__tools';
		controls.captionOverlay.className = 'nunlab-player__captions';
		controls.captionOverlay.setAttribute('aria-hidden', 'true');
		controls.volumePanel.className = 'nunlab-player__volume-panel';
		controls.volumeRange.type = 'range';
		controls.volumeRange.min = '0';
		controls.volumeRange.max = '1';
		controls.volumeRange.step = '0.01';
		controls.volumeRange.value = '1';
		controls.volumeRange.setAttribute('aria-label', 'Volume');
		controls.speedMenu.className = 'nunlab-player__speed-menu';
		controls.speedMenu.hidden = true;
		controls.subtitlesButton.setAttribute('aria-pressed', 'false');
		controls.speedButton.setAttribute('aria-expanded', 'false');

		[2, 1.5, 1.25, 1].forEach(function (rate) {
			var rateButton = document.createElement('button');

			rateButton.type = 'button';
			rateButton.textContent = 'x ' + rate.toFixed(2);
			rateButton.dataset.nunlabVideoRate = String(rate);
			controls.speedMenu.appendChild(rateButton);
		});

		controls.volumePanel.appendChild(controls.volumeRange);
		controls.soundTool.appendChild(controls.volumePanel);
		controls.speedTool.appendChild(controls.speedMenu);
		controls.tools.appendChild(controls.soundTool);
		controls.tools.appendChild(controls.subtitlesButton);
		controls.tools.appendChild(controls.speedTool);
		controls.tools.appendChild(controls.fullscreenButton);
		controls.timelineGroup.appendChild(controls.timeline);
		controls.timelineGroup.appendChild(controls.timeDisplay);
		controls.chrome.appendChild(controls.playButton);
		controls.chrome.appendChild(controls.timelineGroup);
		controls.chrome.appendChild(controls.tools);

		return controls;
	};

	var createYoutubeIframe = function (src, title) {
		var iframe = document.createElement('iframe');

		iframe.src = src;
		iframe.title = title;
		iframe.loading = 'lazy';
		iframe.allow =
			'accelerometer; autoplay; clipboard-write; encrypted-media; fullscreen; gyroscope; picture-in-picture; web-share';
		iframe.allowFullscreen = true;

		return iframe;
	};

	var createYoutubeHitArea = function () {
		var hitArea = document.createElement('button');

		hitArea.type = 'button';
		hitArea.className = 'nunlab-player__video-hit';
		hitArea.tabIndex = -1;
		hitArea.setAttribute('aria-label', 'Toggle walkthrough video');

		return hitArea;
	};

	var getYoutubeChapters = function (frame) {
		var chapterData = frame.querySelector('[data-tool-youtube-chapters]');
		var chapters = [];

		if (!chapterData || !chapterData.textContent.trim()) {
			return chapters;
		}

		try {
			chapters = JSON.parse(chapterData.textContent);
		} catch (error) {
			return [];
		}

		if (!Array.isArray(chapters)) {
			return [];
		}

		return chapters
			.map(function (chapter) {
				return {
					start: Number(chapter.start),
					title: chapter.title ? String(chapter.title).trim() : '',
				};
			})
			.filter(function (chapter) {
				return Number.isFinite(chapter.start) && chapter.start >= 0 && '' !== chapter.title;
			})
			.sort(function (first, second) {
				return first.start - second.start;
			});
	};

	var getYoutubeCaptions = function (frame) {
		var captionData = frame.querySelector('[data-tool-youtube-captions]');
		var captions = [];

		if (!captionData || !captionData.textContent.trim()) {
			return captions;
		}

		try {
			captions = JSON.parse(captionData.textContent);
		} catch (error) {
			return [];
		}

		if (!Array.isArray(captions)) {
			return [];
		}

		return captions
			.map(function (cue) {
				return {
					start: Number(cue.start),
					end: Number(cue.end),
					text: cue.text ? String(cue.text).trim() : '',
				};
			})
			.filter(function (cue) {
				return (
					Number.isFinite(cue.start) &&
					Number.isFinite(cue.end) &&
					cue.end > cue.start &&
					'' !== cue.text
				);
			})
			.sort(function (first, second) {
				return first.start - second.start;
			});
	};

	var renderYoutubeTimelineSegments = function (timeline, duration, chapters) {
		var track = timeline.querySelector('.nunlab-player__timeline-track');
		var safeDuration = Number.isFinite(duration) && duration > 0 ? duration : 0;
		var starts = [0];
		var labels = {};

		if (!track) {
			return;
		}

		if (safeDuration && chapters.length) {
			chapters.forEach(function (chapter) {
				var start = chapter.start <= 0.05 ? 0 : chapter.start;
				var key = '';

				if (start < 0 || start >= safeDuration) {
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
					(!safeDuration || start < safeDuration) &&
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
			var end = safeDuration && starts[index + 1] ? starts[index + 1] : safeDuration || 1;
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

	var syncYoutubeTimelineProgress = function (timeline, currentTime, duration) {
		var segments = timeline.querySelectorAll('.nunlab-player__timeline-segment');

		segments.forEach(function (segment) {
			var segmentStart = Number(segment.dataset.start) || 0;
			var segmentEnd = Number(segment.dataset.end) || duration || 1;
			var segmentProgress = (currentTime - segmentStart) / (segmentEnd - segmentStart);

			segment.style.setProperty(
				'--nunlab-segment-progress',
				(Math.min(Math.max(segmentProgress, 0), 1) * 100).toFixed(3) + '%'
			);
		});
	};

	var setYoutubeEmbedFallback = function (frame, src, title) {
		var iframe = createYoutubeIframe(getYoutubeApiSrc(src, false), title);

		frame.classList.remove(
			'nunlab-player--youtube-custom',
			'has-started',
			'is-playing',
			'has-speed-menu-open',
			'has-volume-menu-open',
			'has-captions-on',
			'has-captions-visible',
			'is-fullscreen',
			'is-pseudo-fullscreen'
		);
		document.body.classList.remove('has-nunlab-player-expanded');
		frame.classList.add('nunlab-player--loaded');
		frame.replaceChildren(iframe);
		iframe.focus();
	};

	var loadStandardYoutubeEmbed = function (frame) {
		var src = frame.getAttribute('data-tool-youtube-src');
		var title = frame.getAttribute('data-tool-youtube-title') || 'Walkthrough video';
		var iframe = null;

		if (!src) {
			return;
		}

		iframe = createYoutubeIframe(getYoutubeApiSrc(src, false), title);

		frame.classList.add('nunlab-player--loaded');
		frame.replaceChildren(iframe);
		iframe.focus();
	};

	var loadCustomYoutubeEmbed = function (frame) {
		var src = frame.getAttribute('data-tool-youtube-src');
		var title = frame.getAttribute('data-tool-youtube-title') || 'Walkthrough video';
		var iframe = null;
		var controls = null;
		var hitArea = null;
		var player = null;
		var ytApi = null;
		var syncTimer = 0;
		var renderedDuration = 0;
		var chapterCues = getYoutubeChapters(frame);
		var captionCues = getYoutubeCaptions(frame);
		var captionsEnabled = false;
		var activeCaptionText = '';
		var isPseudoFullscreen = false;
		var fullscreenFallbackTimer = 0;
		var initialPlayPending = true;
		var lastVolume = 100;
		var isPlayerReady = false;
		var isPointerInsideFrame = false;

		if (!src || 'true' === frame.dataset.toolYoutubeLoaded) {
			return;
		}

		frame.dataset.toolYoutubeLoaded = 'true';
		iframe = createYoutubeIframe(getYoutubeApiSrc(src, true), title);
		iframe.id = 'nunlab-youtube-player-' + Math.random().toString(36).slice(2);
		iframe.loading = 'eager';
		controls = createCustomYoutubeControls();
		hitArea = createYoutubeHitArea();

		var closeSpeedMenu = function () {
			controls.speedMenu.hidden = true;
			controls.speedButton.setAttribute('aria-expanded', 'false');
			frame.classList.remove('has-speed-menu-open');
		};

		var closeVolumePanel = function () {
			frame.classList.remove('has-volume-menu-open');
		};

		var closeControlPanels = function () {
			closeSpeedMenu();
			closeVolumePanel();
		};

		var getPlayerDuration = function () {
			return player && 'function' === typeof player.getDuration ? player.getDuration() || 0 : 0;
		};

		var getPlayerCurrentTime = function () {
			return player && 'function' === typeof player.getCurrentTime ? player.getCurrentTime() || 0 : 0;
		};

		var syncTime = function () {
			controls.timeDisplay.textContent =
				formatVideoTime(getPlayerCurrentTime()) + ' / ' + formatVideoTime(getPlayerDuration());
		};

		var syncProgress = function () {
			syncYoutubeTimelineProgress(
				controls.timeline,
				getPlayerCurrentTime(),
				getPlayerDuration()
			);
		};

		var updateCaptionOverlay = function () {
			var currentTime = getPlayerCurrentTime();
			var activeLines = [];
			var nextCaptionText = '';

			if (captionsEnabled && captionCues.length) {
				captionCues.forEach(function (cue) {
					if (currentTime >= cue.start && currentTime < cue.end) {
						activeLines = activeLines.concat(cue.text.split(/\n+/));
					}
				});
			}

			nextCaptionText = activeLines
				.map(function (line) {
					return line.trim();
				})
				.filter(Boolean)
				.join('\n');

			if (nextCaptionText === activeCaptionText) {
				frame.classList.toggle('has-captions-visible', '' !== nextCaptionText);
				return;
			}

			activeCaptionText = nextCaptionText;
			controls.captionOverlay.replaceChildren();

			if ('' === nextCaptionText) {
				frame.classList.remove('has-captions-visible');
				return;
			}

			nextCaptionText
				.split(/\n+/)
				.forEach(function (line) {
					var captionLine = document.createElement('span');

					captionLine.className = 'nunlab-player__caption-line';
					captionLine.textContent = line;
					controls.captionOverlay.appendChild(captionLine);
				});

			frame.classList.add('has-captions-visible');
		};

		var setCaptionsEnabled = function (shouldEnable) {
			captionsEnabled = Boolean(shouldEnable && captionCues.length);
			frame.classList.toggle('has-captions-on', captionsEnabled);
			controls.subtitlesButton.setAttribute('aria-pressed', captionsEnabled ? 'true' : 'false');
			updateCaptionOverlay();
		};

		var toggleCaptions = function () {
			setCaptionsEnabled(!captionsEnabled);
		};

		var syncTimelineSegments = function () {
			var duration = getPlayerDuration();

			if (!duration || Math.abs(duration - renderedDuration) < 0.05) {
				return;
			}

			renderedDuration = duration;
			renderYoutubeTimelineSegments(controls.timeline, duration, chapterCues);
		};

		var syncVolumeState = function () {
			var isMuted = player && 'function' === typeof player.isMuted && player.isMuted();
			var volume =
				player && 'function' === typeof player.getVolume
					? Math.min(Math.max(player.getVolume() / 100, 0), 1)
					: Number(controls.volumeRange.value) || 0;

			if (isMuted) {
				volume = 0;
			} else if (volume > 0) {
				lastVolume = Math.round(volume * 100);
			}

			frame.classList.toggle('is-muted', Boolean(isMuted));
			frame.style.setProperty('--nunlab-player-volume', (volume * 100).toFixed(1) + '%');
			controls.volumeRange.value = String(volume);
			controls.soundButton.setAttribute('aria-label', isMuted ? 'Unmute video' : 'Mute video');
		};

		var toggleMute = function () {
			var isMuted = player && 'function' === typeof player.isMuted && player.isMuted();
			var volume =
				player && 'function' === typeof player.getVolume
					? Math.min(Math.max(Math.round(player.getVolume()), 0), 100)
					: lastVolume;

			closeSpeedMenu();

			if (!player) {
				return;
			}

			if (isMuted || 0 === volume) {
				if (0 === volume && 'function' === typeof player.setVolume) {
					player.setVolume(lastVolume || 100);
				}

				if ('function' === typeof player.unMute) {
					player.unMute();
				}
			} else if ('function' === typeof player.mute) {
				lastVolume = volume;
				player.mute();
			}

			syncVolumeState();
		};

		var syncSpeedButtons = function () {
			var playbackRate =
				player && 'function' === typeof player.getPlaybackRate ? player.getPlaybackRate() : 1;

			Array.prototype.forEach.call(
				controls.speedMenu.querySelectorAll('button'),
				function (button) {
					button.classList.toggle(
						'is-active',
						Number(button.dataset.nunlabVideoRate) === playbackRate
					);
				}
			);
		};

		var syncPlayState = function (state) {
			var playingState = ytApi && ytApi.PlayerState ? ytApi.PlayerState.PLAYING : 1;
			var isPlaying = state === playingState;

			frame.classList.toggle('is-playing', isPlaying);
			frame.classList.add('has-started');
			controls.playButton.setAttribute('aria-label', isPlaying ? 'Pause video' : 'Play video');
			hitArea.setAttribute('aria-label', isPlaying ? 'Pause walkthrough video' : 'Play walkthrough video');
		};

		var syncAll = function () {
			syncTimelineSegments();
			syncTime();
			syncProgress();
			updateCaptionOverlay();
			syncVolumeState();
			syncSpeedButtons();
		};

		var togglePlay = function () {
			var state = null;
			var playingState = ytApi && ytApi.PlayerState ? ytApi.PlayerState.PLAYING : 1;

			if (!isPlayerReady || !player) {
				initialPlayPending = true;
				return;
			}

			state = 'function' === typeof player.getPlayerState ? player.getPlayerState() : null;

			if (state === playingState) {
				player.pauseVideo();
				return;
			}

			player.playVideo();
		};

		var playInitialVideo = function () {
			var playerState = ytApi && ytApi.PlayerState ? ytApi.PlayerState : null;
			var state = player && 'function' === typeof player.getPlayerState ? player.getPlayerState() : null;
			var isNearStart = getPlayerCurrentTime() < 0.35;

			if (!isPlayerReady || !player || !isNearStart) {
				return;
			}

			if (
				!playerState ||
				-1 === state ||
				state === playerState.UNSTARTED ||
				state === playerState.CUED ||
				state === playerState.PAUSED
			) {
				player.playVideo();
			}
		};

		var seekFromEvent = function (event) {
			var bounds = controls.timeline.getBoundingClientRect();
			var duration = getPlayerDuration();
			var ratio = 0;

			if (!duration || !bounds.width || !player) {
				return;
			}

			ratio = Math.min(Math.max((event.clientX - bounds.left) / bounds.width, 0), 1);
			player.seekTo(duration * ratio, true);
			syncProgress();
			syncTime();
			updateCaptionOverlay();
		};

		var nudgeSeek = function (seconds) {
			var duration = getPlayerDuration();
			var nextTime = 0;

			if (!duration || !player) {
				return;
			}

			nextTime = Math.min(Math.max(getPlayerCurrentTime() + seconds, 0), duration);
			player.seekTo(nextTime, true);
			syncProgress();
			syncTime();
			updateCaptionOverlay();
		};

		var hideChapterTooltip = function () {
			var tooltip = controls.timeline.querySelector('.nunlab-player__chapter-tooltip');

			if (tooltip) {
				tooltip.hidden = true;
			}

			controls.timeline.classList.remove('has-chapter-tooltip');
		};

		var showChapterTooltip = function (segment) {
			var tooltip = controls.timeline.querySelector('.nunlab-player__chapter-tooltip');
			var label = segment ? segment.dataset.title : '';
			var timelineBounds = controls.timeline.getBoundingClientRect();
			var segmentBounds = segment ? segment.getBoundingClientRect() : null;
			var x = 50;

			if (!tooltip || !segment || !label) {
				hideChapterTooltip();
				return;
			}

			if (timelineBounds.width && segmentBounds) {
				x =
					((segmentBounds.left + segmentBounds.width / 2 - timelineBounds.left) /
						timelineBounds.width) *
					100;
			}

			controls.timeline.style.setProperty(
				'--nunlab-chapter-tooltip-x',
				Math.min(Math.max(x, 6), 94).toFixed(3) + '%'
			);
			tooltip.textContent = label;
			tooltip.hidden = false;
			controls.timeline.classList.add('has-chapter-tooltip');
		};

		var updateChapterTooltipFromEvent = function (event) {
			var segment =
				event.target && 'function' === typeof event.target.closest
					? event.target.closest('.nunlab-player__timeline-segment')
					: null;

			if (!segment || !controls.timeline.contains(segment)) {
				hideChapterTooltip();
				return;
			}

			showChapterTooltip(segment);
		};

		var getFullscreenElement = function () {
			return document.fullscreenElement || document.webkitFullscreenElement || null;
		};

		var syncFullscreenState = function () {
			var isFullscreen = getFullscreenElement() === frame || isPseudoFullscreen;

			frame.classList.toggle('is-fullscreen', isFullscreen);
			controls.fullscreenButton.setAttribute(
				'aria-label',
				isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'
			);
		};

		var enterPseudoFullscreen = function () {
			isPseudoFullscreen = true;
			document.body.classList.add('has-nunlab-player-expanded');
			frame.classList.add('is-pseudo-fullscreen');
			syncFullscreenState();
		};

		var exitPseudoFullscreen = function () {
			window.clearTimeout(fullscreenFallbackTimer);
			isPseudoFullscreen = false;
			document.body.classList.remove('has-nunlab-player-expanded');
			frame.classList.remove('is-pseudo-fullscreen');
			syncFullscreenState();
		};

		var confirmFullscreenRequest = function () {
			window.clearTimeout(fullscreenFallbackTimer);
			fullscreenFallbackTimer = window.setTimeout(function () {
				if (!getFullscreenElement() && !isPseudoFullscreen) {
					enterPseudoFullscreen();
				}
			}, 450);
		};

		var exitRealFullscreen = function () {
			if (document.exitFullscreen) {
				document.exitFullscreen();
				return true;
			}

			if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
				return true;
			}

			return false;
		};

		var requestRealFullscreen = function () {
			var request = frame.requestFullscreen || frame.webkitRequestFullscreen;
			var requestResult = null;

			if (!request) {
				return false;
			}

			try {
				requestResult = request.call(frame);
			} catch (error) {
				return false;
			}

			confirmFullscreenRequest();

			if (requestResult && 'function' === typeof requestResult.catch) {
				requestResult.catch(enterPseudoFullscreen);
			}

			return true;
		};

		var requestFrameFullscreen = function () {
			var shouldUsePseudoFullscreen =
				window.matchMedia &&
				window.matchMedia('(hover: none), (pointer: coarse)').matches;

			if (getFullscreenElement() === frame) {
				window.clearTimeout(fullscreenFallbackTimer);
				exitRealFullscreen();
				return;
			}

			if (isPseudoFullscreen) {
				exitPseudoFullscreen();
				return;
			}

			if (shouldUsePseudoFullscreen || !requestRealFullscreen()) {
				enterPseudoFullscreen();
			}

			playInitialVideo();
		};

		var shouldUsePlayerShortcut = function (event) {
			var target = event.target;
			var targetTag = target && target.tagName ? target.tagName.toLowerCase() : '';

			if (event.defaultPrevented || event.altKey || event.ctrlKey || event.metaKey) {
				return false;
			}

			if (!isPointerInsideFrame && !frame.contains(document.activeElement)) {
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

			if ('escape' === key && isPseudoFullscreen) {
				event.preventDefault();
				exitPseudoFullscreen();
				return;
			}

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
				return;
			}

			if ('arrowright' === key) {
				event.preventDefault();
				nudgeSeek(5);
				return;
			}

			if (('c' === key || 's' === key) && captionCues.length) {
				if (event.repeat) {
					return;
				}

				event.preventDefault();
				toggleCaptions();
			}
		};

		// YouTube owns the iframe; this layer mirrors the small control set the site needs.
		frame.classList.add('nunlab-player--loaded', 'has-started');
		frame.replaceChildren(iframe, hitArea, controls.captionOverlay, controls.chrome);
		controls.playButton.focus({ preventScroll: true });

		controls.playButton.addEventListener('click', togglePlay);
		hitArea.addEventListener('click', togglePlay);
		if (captionCues.length) {
			controls.subtitlesButton.addEventListener('click', toggleCaptions);
		} else {
			controls.subtitlesButton.disabled = true;
		}
		controls.timeline.addEventListener('click', seekFromEvent);
		controls.timeline.addEventListener('keydown', function (event) {
			if ('ArrowLeft' === event.key) {
				event.preventDefault();
				nudgeSeek(-5);
			}

			if ('ArrowRight' === event.key) {
				event.preventDefault();
				nudgeSeek(5);
			}
		});
		controls.timeline.addEventListener('pointermove', updateChapterTooltipFromEvent);
		controls.timeline.addEventListener('pointerleave', hideChapterTooltip);
		controls.timeline.addEventListener('blur', hideChapterTooltip);

		controls.soundButton.addEventListener('click', function () {
			toggleMute();
		});

		controls.volumeRange.addEventListener('input', function () {
			var volume = Math.round(Number(controls.volumeRange.value) * 100);

			if (!player) {
				return;
			}

			player.setVolume(volume);

			if (0 === volume) {
				player.mute();
			} else {
				lastVolume = volume;
				player.unMute();
			}

			syncVolumeState();
		});

		controls.speedButton.addEventListener('click', function () {
			var shouldOpen = controls.speedMenu.hidden;

			closeVolumePanel();
			controls.speedMenu.hidden = !shouldOpen;
			controls.speedButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
			frame.classList.toggle('has-speed-menu-open', shouldOpen);
		});

		controls.speedMenu.addEventListener('click', function (event) {
			var button =
				event.target && 'function' === typeof event.target.closest
					? event.target.closest('button[data-nunlab-video-rate]')
					: null;
			var rate = button ? Number(button.dataset.nunlabVideoRate) : 0;

			if (!button || !player || !rate) {
				return;
			}

			player.setPlaybackRate(rate);
			closeSpeedMenu();
			syncSpeedButtons();
		});

		controls.fullscreenButton.addEventListener('click', requestFrameFullscreen);
		document.addEventListener('fullscreenchange', syncFullscreenState);
		document.addEventListener('webkitfullscreenchange', syncFullscreenState);
		document.addEventListener('keydown', handlePlayerShortcut);
		window.addEventListener('pagehide', exitPseudoFullscreen);
		document.addEventListener('click', function (event) {
			if (!frame.contains(event.target)) {
				closeControlPanels();
			}
		});
		frame.addEventListener('pointerenter', function () {
			isPointerInsideFrame = true;
		});
		frame.addEventListener('pointerleave', function () {
			isPointerInsideFrame = false;
		});

		ensureYoutubeApi()
			.then(function (yt) {
				ytApi = yt;
				player = new yt.Player(iframe.id, {
					events: {
						onReady: function (event) {
							isPlayerReady = true;
							player = event.target;
							syncAll();
							syncPlayState(player.getPlayerState());
							if (initialPlayPending) {
								initialPlayPending = false;
								playInitialVideo();
							}
							syncTimer = window.setInterval(syncAll, 250);
						},
						onStateChange: function (event) {
							syncPlayState(event.data);
							if (
								ytApi &&
								ytApi.PlayerState &&
								event.data === ytApi.PlayerState.PLAYING
							) {
								initialPlayPending = false;
							}
							syncAll();
						},
						onPlaybackRateChange: syncSpeedButtons,
						onError: function () {
							window.clearInterval(syncTimer);
							setYoutubeEmbedFallback(frame, src, title);
						},
					},
				});
			})
			.catch(function () {
				window.clearInterval(syncTimer);
				setYoutubeEmbedFallback(frame, src, title);
			});
	};

	var initToolChapterNavigation = function () {
		var overviewLinks = document.querySelectorAll('.tool-overview__list a[href^="#chapter-"]');
		var reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
		var highlightCurrentHash = function (force) {
			var hash = window.location.hash;
			var chapter = getChapterFromHash(hash);

			if (!chapter) {
				return;
			}

			if (!force && hash === currentChapterHash) {
				return;
			}

			currentChapterHash = hash;
			highlightChapterOnArrival(chapter);
		};

		overviewLinks.forEach(function (link) {
			if ('true' === link.dataset.toolChapterBound) {
				return;
			}

			link.dataset.toolChapterBound = 'true';

			link.addEventListener('click', function (event) {
				var chapter = getChapterFromHash(link.hash);

				if (
					event.defaultPrevented ||
					('number' === typeof event.button && 0 !== event.button) ||
					event.metaKey ||
					event.ctrlKey ||
					event.shiftKey ||
					event.altKey ||
					!chapter
				) {
					return;
				}

				event.preventDefault();
				scrollToChapter(chapter, reducedMotionQuery);
				window.history.pushState(null, '', link.hash);
				currentChapterHash = link.hash;

				if (reducedMotionQuery.matches) {
					highlightChapter(chapter);
					return;
				}

				highlightChapterOnArrival(chapter);
			});
		});

		window.addEventListener('hashchange', function () {
			highlightCurrentHash(true);
		});

		window.addEventListener('popstate', function () {
			highlightCurrentHash(true);
		});

		window.setTimeout(function () {
			highlightCurrentHash(true);
		}, 480);

		window.setInterval(function () {
			highlightCurrentHash(false);
		}, 250);
	};

	var initToolWalkthroughEmbeds = function () {
		var frames = document.querySelectorAll('[data-tool-youtube-frame]');

		frames.forEach(function (frame) {
			var playButtons = frame.querySelectorAll('[data-tool-youtube-play]');
			var loadVideo = function () {
				if ('true' === frame.getAttribute('data-tool-youtube-custom-controls')) {
					loadCustomYoutubeEmbed(frame);
					return;
				}

				loadStandardYoutubeEmbed(frame);
			};

			playButtons.forEach(function (button) {
				button.addEventListener('click', function () {
					loadVideo();
				});
			});
		});
	};

	var initToolContent = function () {
		try {
			initToolWalkthroughEmbeds();
			initToolChapterNavigation();
			initToolChapterImages();
		} catch (error) {
			if (window.console && window.console.error) {
				window.console.error('N:UN tool content init failed:', error);
			}
		}
	};

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', initToolContent);
	} else {
		initToolContent();
	}
})();

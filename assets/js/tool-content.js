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
		var triggers = document.querySelectorAll('[data-tool-youtube-src]');

		triggers.forEach(function (trigger) {
			trigger.addEventListener('click', function () {
				var src = trigger.getAttribute('data-tool-youtube-src');
				var title = trigger.getAttribute('data-tool-youtube-title') || 'Walkthrough video';
				var frame = trigger.closest('.tool-walkthrough__frame');
				var iframe = null;

				if (!src || !frame) {
					return;
				}

				iframe = document.createElement('iframe');
				iframe.src = src;
				iframe.title = title;
				iframe.loading = 'lazy';
				iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
				iframe.allowFullscreen = true;

				frame.replaceChildren(iframe);
				iframe.focus();
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

// Single-project gallery interactions.

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

	var buildVideoIframe = function (url, title) {
		if (!url) {
			return '';
		}

		return (
			'<iframe src="' +
			url +
			'" title="' +
			String(title || 'Project video').replace(/"/g, '&quot;') +
			'" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'
		);
	};

	var clearVideoFrame = function (frame) {
		if (!frame) {
			return;
		}

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

	var initProjectGalleries = function () {
		var galleries = document.querySelectorAll('[data-project-gallery]');

		if (!galleries.length) {
			return;
		}

		galleries.forEach(function (gallery) {
			var slides = Array.prototype.slice.call(gallery.querySelectorAll('[data-project-gallery-slide]'));
			var viewport = gallery.querySelector('[data-project-gallery-viewport]');
			var prev = gallery.querySelector('[data-project-gallery-prev]');
			var next = gallery.querySelector('[data-project-gallery-next]');
			var counter = gallery.querySelector('[data-project-gallery-counter]');
			var index = 0;

			if (!slides.length) {
				return;
			}

			var clearAllVideos = function () {
				slides.forEach(function (slide) {
					var frame = slide.querySelector('[data-project-gallery-video-frame]');
					var playButton = slide.querySelector('[data-project-gallery-video-play]');

					clearVideoFrame(frame);

					if (playButton) {
						playButton.hidden = false;
					}
				});
			};

			var playVideo = function (slide) {
				var frame = slide.querySelector('[data-project-gallery-video-frame]');
				var playButton = slide.querySelector('[data-project-gallery-video-play]');
				var autoplayUrl = playButton ? playButton.dataset.autoplayEmbedUrl : '';

				if (!frame || !playButton || !autoplayUrl) {
					return;
				}

				clearAllVideos();
				frame.innerHTML = buildVideoIframe(autoplayUrl, slide.dataset.projectGalleryTitle || 'Project video');
				frame.hidden = false;
				playButton.hidden = true;
			};

			var updateSlides = function () {
				var activeSlide;
				var activeImage;

				clearAllVideos();

				slides.forEach(function (slide, slideIndex) {
					slide.hidden = slideIndex !== index;
				});

				activeSlide = slides[index];
				activeImage = activeSlide ? activeSlide.querySelector('.project-gallery__image') : null;

				syncMediaAspectRatio(viewport, activeImage, '16 / 10');

				if (counter) {
					counter.textContent = String(index + 1) + ' / ' + String(slides.length);
				}
			};

			var goToPreviousSlide = function () {
				index = (index - 1 + slides.length) % slides.length;
				updateSlides();
			};

			var goToNextSlide = function () {
				index = (index + 1) % slides.length;
				updateSlides();
			};

			slides.forEach(function (slide) {
				var playButton = slide.querySelector('[data-project-gallery-video-play]');

				if (!playButton) {
					return;
				}

				playButton.addEventListener('click', function () {
					playVideo(slide);
				});
			});

			if (prev) {
				prev.addEventListener('click', goToPreviousSlide);
			}

			if (next) {
				next.addEventListener('click', goToNextSlide);
			}

			bindHorizontalSwipe(
				viewport,
				goToPreviousSlide,
				goToNextSlide,
				function () {
					return slides.length > 1;
				}
			);

			document.addEventListener('keydown', function (event) {
				var rect;
				var inViewport;

				if (slides.length <= 1 || isTypingContext()) {
					return;
				}

				rect = gallery.getBoundingClientRect();
				inViewport = rect.bottom > window.innerHeight * 0.2 && rect.top < window.innerHeight * 0.8;

				if (!inViewport && !gallery.contains(document.activeElement)) {
					return;
				}

				if ('ArrowLeft' === event.key) {
					event.preventDefault();
					goToPreviousSlide();
					return;
				}

				if ('ArrowRight' === event.key) {
					event.preventDefault();
					goToNextSlide();
				}
			});

			updateSlides();
		});
	};

	document.addEventListener('DOMContentLoaded', function () {
		try {
			initProjectGalleries();
		} catch (error) {
			if (window.console && window.console.error) {
				window.console.error('N:UN project gallery init failed:', error);
			}
		}
	});
})();

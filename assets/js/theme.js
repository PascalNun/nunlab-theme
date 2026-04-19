/**
 * Theme JavaScript
 * 
 * @package nunlab-theme
 */

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
			var tagName = document.activeElement ? document.activeElement.tagName : '';
			var isTypingContext =
				document.activeElement &&
				(document.activeElement.isContentEditable ||
					tagName === 'INPUT' ||
					tagName === 'TEXTAREA' ||
					tagName === 'SELECT');

			if (event.key === 'Escape' && !searchLayer.hidden) {
				closeSearch();
			}

			if (
				event.key === '/' &&
				searchLayer.hidden &&
				!event.metaKey &&
				!event.ctrlKey &&
				!event.altKey &&
				!isTypingContext
			) {
				event.preventDefault();
				openSearch();
			}
		});
	};

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

		if (window.matchMedia) {
			var desktopQuery = window.matchMedia('(min-width: 769px)');

			var syncMenuState = function (event) {
				if (event.matches) {
					setOpenState(false);
				}
			};

			if (desktopQuery.addEventListener) {
				desktopQuery.addEventListener('change', syncMenuState);
			} else if (desktopQuery.addListener) {
				desktopQuery.addListener(syncMenuState);
			}
		}
	};

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

	var initWorkListings = function () {
		var groups = document.querySelectorAll('[data-work-group]');

		if (!groups.length) {
			return;
		}

		var animateItem = function (item, mutate) {
			var first = item.getBoundingClientRect();

			mutate();

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

		var scrollToActiveItem = function (item) {
			var header = document.querySelector('.site-header');
			var headerOffset = header ? header.getBoundingClientRect().height : 0;
			var top = item.getBoundingClientRect().top + window.scrollY - headerOffset - 24;

			window.scrollTo({
				top: Math.max(0, top),
				behavior: 'smooth',
			});
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

			var updateExpandedMedia = function (item) {
				var button = item.querySelector('[data-work-card]');
				var image = item.querySelector('[data-work-image-current]');
				var placeholder = item.querySelector('[data-work-placeholder-current]');
				var nav = item.querySelector('[data-work-nav]');
				var counter = item.querySelector('[data-work-counter]');
				var playButton = item.querySelector('[data-work-video-play]');
				var currentMedia;

				if (!button || (!image && !placeholder)) {
					return;
				}

				item._media = item._media || [];
				item._slideIndex = item._slideIndex || 0;
				currentMedia = item._media[item._slideIndex];

				resetWorkVideo(item);

				if (currentMedia) {
					if (image && (currentMedia.posterUrl || currentMedia.url)) {
						image.src = currentMedia.posterUrl || currentMedia.url;
						image.alt = currentMedia.alt || button.dataset.workImageAlt || button.dataset.workTitle || '';
						image.hidden = false;
					} else if (image) {
						image.hidden = true;
					}

					if (placeholder) {
						placeholder.hidden = !!currentMedia.posterUrl;
					}

					if (playButton && 'youtube' === currentMedia.type && currentMedia.autoplayEmbedUrl) {
						playButton.hidden = false;
					}
				} else {
					if (image) {
						image.hidden = true;
					}

					if (placeholder) {
						placeholder.hidden = false;
					}
				}

				if (nav && counter) {
					if (item._media.length > 1) {
						nav.hidden = false;
						counter.textContent = String(item._slideIndex + 1) + ' / ' + String(item._media.length);
					} else {
						nav.hidden = true;
						counter.textContent = '';
					}
				}
			};

			var playWorkVideo = function (item) {
				var frame = item.querySelector('[data-work-video-frame]');
				var playButton = item.querySelector('[data-work-video-play]');
				var button = item.querySelector('[data-work-card]');
				var currentMedia = item._media && item._media[item._slideIndex] ? item._media[item._slideIndex] : null;

				if (!frame || !playButton || !currentMedia || 'youtube' !== currentMedia.type || !currentMedia.autoplayEmbedUrl) {
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

			var closeItem = function (item) {
				var button = item.querySelector('[data-work-card]');
				var expand = item.querySelector('[data-work-expand]');
				var nav = item.querySelector('[data-work-nav]');
				var counter = item.querySelector('[data-work-counter]');

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

					resetWorkVideo(item);

					restoreItem(item);
				});
			};

			items.forEach(function (item) {
				var button = item.querySelector('[data-work-card]');
				var expand = item.querySelector('[data-work-expand]');
				var prev = item.querySelector('[data-work-prev]');
				var next = item.querySelector('[data-work-next]');
				var media = item.querySelector('.project-card__media');
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
							embedUrl: '',
							autoplayEmbedUrl: '',
						},
					];
				}

				var goToPreviousSlide = function () {
					if (!item._media.length) {
						return;
					}

					item._slideIndex = (item._slideIndex - 1 + item._media.length) % item._media.length;
					updateExpandedMedia(item);
				};

				var goToNextSlide = function () {
					if (!item._media.length) {
						return;
					}

					item._slideIndex = (item._slideIndex + 1) % item._media.length;
					updateExpandedMedia(item);
				};

				if (prev) {
					prev.addEventListener('click', goToPreviousSlide);
				}

				if (next) {
					next.addEventListener('click', goToNextSlide);
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

				var activateItem = function () {
					var activeItem = group.querySelector('.project-card--directory.is-active');
					var workingItems;
					var index;
					var itemsPerRow;
					var rowStartIndex;
					var rowAnchor;

					if (activeItem && activeItem !== item) {
						closeItem(activeItem);
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
					updateExpandedMedia(item);

					animateItem(item, function () {
						if (rowAnchor && rowAnchor !== item) {
							group.insertBefore(item, rowAnchor);
						}

						button.setAttribute('aria-expanded', 'true');
						item.classList.add('is-active');
						expand.hidden = false;
					});

					window.setTimeout(function () {
						scrollToActiveItem(item);
					}, 120);
				};

				button.addEventListener('click', activateItem);
				button.addEventListener('keydown', function (event) {
					if ('Enter' !== event.key && ' ' !== event.key) {
						return;
					}

					event.preventDefault();
					activateItem();
				});
			});
		});
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
				clearAllVideos();

				slides.forEach(function (slide, slideIndex) {
					slide.hidden = slideIndex !== index;
				});

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

			updateSlides();
		});
	};

	document.addEventListener('DOMContentLoaded', function () {
		initSearchOverlay();
		initMobileMenu();
		initStickyHeader();
		initWorkListings();
		initProjectGalleries();
	});
})();

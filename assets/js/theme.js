// Theme interactions.

(function () {
	'use strict';

	// Keep touch navigation light and dependency-free.
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
		var sequenceExt = supportsWebP && sequenceBaseWebp ? 'webp' : 'jpg';
		var sequenceBase = 'webp' === sequenceExt ? sequenceBaseWebp : sequenceBaseJpg;
		var sequenceDigits = parseInt(stage ? stage.getAttribute('data-hero-sequence-digits') || '3' : '3', 10);
		var useSequenceFallback =
			!!sequenceFrame &&
			!prefersReducedMotion &&
			sequenceCount > 0 &&
			(isIOS || isFirefox);
		var useScrollScrub = !!video && !prefersReducedMotion && !useSequenceFallback;
		var currentSequenceIndex = -1;

		if (!stage || !header) {
			return;
		}

		var clamp = function (value, min, max) {
			return Math.min(Math.max(value, min), max);
		};

		var buildSequenceUrl = function (index) {
			return (
				sequenceBase +
				String(index + 1).padStart(sequenceDigits, '0') +
				'.' +
				sequenceExt
			);
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
			var heroExitThreshold = window.innerWidth <= 768 ? -10 : -18;
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
				var targetTime = duration * progress;

				if (Math.abs(video.currentTime - targetTime) > 0.03) {
					video.currentTime = targetTime;
				}
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

			if (useSequenceFallback) {
				if (sequenceFrame) {
					sequenceFrame.hidden = false;
					sequenceFrame.src = buildSequenceUrl(0);
				}

				video.pause();
				preloadSequenceFrames();
			} else {
				video.pause();

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

	// Single project pages use the same lightweight gallery logic.
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
		initHeroStage();
		initSectionAwareNav();
		initWorkListings();
		initProjectGalleries();
	});
})();

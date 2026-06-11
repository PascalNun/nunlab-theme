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

	document.addEventListener('DOMContentLoaded', function () {
		[
			initSearchOverlay,
			initMobileMenu,
			initStickyHeader,
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

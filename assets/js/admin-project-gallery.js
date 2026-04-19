/**
 * Project media editor for the WordPress admin.
 *
 * @package nunlab-theme
 */

(function () {
	'use strict';

	var escapeHtml = function (value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	};

	var parseYoutubeId = function (url) {
		var match;

		if (!url) {
			return '';
		}

		match = String(url).match(/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);

		return match ? match[1] : '';
	};

	var getYoutubePoster = function (url) {
		var videoId = parseYoutubeId(url);
		return videoId ? 'https://i.ytimg.com/vi/' + videoId + '/hqdefault.jpg' : '';
	};

	var getAttachmentPreview = function (attachment) {
		var sizes = attachment.sizes || {};
		var thumb = sizes.thumbnail || sizes.medium || null;
		return thumb ? thumb.url : attachment.url || '';
	};

	var serializeItems = function (items) {
		return items.map(function (item) {
			if ('youtube' === item.type) {
				return {
					type: 'youtube',
					youtube_url: item.youtubeUrl || '',
					poster_id: item.posterId || 0,
				};
			}

			return {
				type: 'image',
				image_id: item.imageId || 0,
				image_url: item.imageUrl || '',
			};
		});
	};

	var createEmptyMarkup = function () {
		return '<p class="description">' + escapeHtml(nunlabProjectMedia.emptyLabel) + '</p>';
	};

	var createPreviewMarkup = function (item) {
		var previewUrl = item.previewUrl || '';
		var previewLabel = item.label || item.posterLabel || '';

		if (!previewUrl) {
			return '<div class="nunlab-admin-media__placeholder"></div>';
		}

		return (
			'<img src="' +
			escapeHtml(previewUrl) +
			'" alt="" />' +
			(previewLabel
				? '<span class="nunlab-admin-media__caption">' + escapeHtml(previewLabel) + '</span>'
				: '')
		);
	};

	var createItemMarkup = function (item, index) {
		var actions =
			'<div class="nunlab-admin-media__item-actions">' +
			'<button type="button" class="button-link" data-media-action="move-up" data-index="' +
			index +
			'">' +
			escapeHtml(nunlabProjectMedia.moveUpLabel) +
			'</button>' +
			'<button type="button" class="button-link" data-media-action="move-down" data-index="' +
			index +
			'">' +
			escapeHtml(nunlabProjectMedia.moveDownLabel) +
			'</button>' +
			'<button type="button" class="button-link button-link-delete" data-media-action="remove" data-index="' +
			index +
			'">' +
			escapeHtml(nunlabProjectMedia.removeLabel) +
			'</button>' +
			'</div>';

		if ('youtube' === item.type) {
			return (
				'<article class="nunlab-admin-media__item">' +
				'<div class="nunlab-admin-media__preview">' +
				createPreviewMarkup(item) +
				'</div>' +
				'<div class="nunlab-admin-media__item-body">' +
				'<p class="nunlab-admin-media__item-type">' +
				escapeHtml(nunlabProjectMedia.youtubeTypeLabel) +
				'</p>' +
				'<label class="nunlab-admin-media__field">' +
				'<span class="nunlab-admin-media__field-label">YouTube URL</span>' +
				'<input class="widefat" type="url" value="' +
				escapeHtml(item.youtubeUrl || '') +
				'" placeholder="' +
				escapeHtml(nunlabProjectMedia.youtubePlaceholder) +
				'" data-media-field="youtube-url" data-index="' +
				index +
				'" />' +
				'</label>' +
				'<div class="nunlab-admin-media__poster-actions">' +
				'<button type="button" class="button button-secondary" data-media-action="choose-poster" data-index="' +
				index +
				'">' +
				escapeHtml(nunlabProjectMedia.choosePosterLabel) +
				'</button>' +
				'<button type="button" class="button-link" data-media-action="clear-poster" data-index="' +
				index +
				'">' +
				escapeHtml(nunlabProjectMedia.clearPosterLabel) +
				'</button>' +
				'</div>' +
				'<p class="description">' +
				escapeHtml(item.posterId ? item.posterLabel || '' : nunlabProjectMedia.generatedPosterLabel) +
				'</p>' +
				actions +
				'</div>' +
				'</article>'
			);
		}

		return (
			'<article class="nunlab-admin-media__item">' +
			'<div class="nunlab-admin-media__preview">' +
			createPreviewMarkup(item) +
			'</div>' +
			'<div class="nunlab-admin-media__item-body">' +
			'<p class="nunlab-admin-media__item-type">' +
			escapeHtml(nunlabProjectMedia.imageTypeLabel) +
			'</p>' +
			'<p class="nunlab-admin-media__item-title">' +
			escapeHtml(item.label || nunlabProjectMedia.externalImageLabel) +
			'</p>' +
			'<div class="nunlab-admin-media__poster-actions">' +
			'<button type="button" class="button button-secondary" data-media-action="replace-image" data-index="' +
			index +
			'">' +
			escapeHtml(item.imageId || item.imageUrl ? nunlabProjectMedia.replaceImageLabel : nunlabProjectMedia.chooseImageLabel) +
			'</button>' +
			'</div>' +
			actions +
			'</div>' +
			'</article>'
		);
	};

	document.addEventListener('DOMContentLoaded', function () {
		var mediaBox = document.querySelector('[data-project-media-box]');

		if (!mediaBox || typeof wp === 'undefined' || !wp.media) {
			return;
		}

		var input = mediaBox.querySelector('[data-media-input]');
		var stateElement = mediaBox.querySelector('[data-media-state]');
		var list = mediaBox.querySelector('[data-media-list]');
		var addImageButton = mediaBox.querySelector('[data-media-add-image]');
		var addYoutubeButton = mediaBox.querySelector('[data-media-add-youtube]');
		var clearButton = mediaBox.querySelector('[data-media-clear]');
		var items = [];

		if (!input || !stateElement || !list || !addImageButton || !addYoutubeButton || !clearButton) {
			return;
		}

		try {
			items = JSON.parse(stateElement.textContent || '[]') || [];
		} catch (error) {
			items = [];
		}

		var syncInput = function () {
			input.value = JSON.stringify(serializeItems(items));
		};

		var render = function () {
			if (!items.length) {
				list.innerHTML = createEmptyMarkup();
				syncInput();
				return;
			}

			list.innerHTML = items
				.map(function (item, index) {
					return createItemMarkup(item, index);
				})
				.join('');

			syncInput();
		};

		var openImageFrame = function (options) {
			var frame = wp.media({
				title: options.title,
				button: {
					text: options.buttonText,
				},
				library: {
					type: 'image',
				},
				multiple: options.multiple,
			});

			frame.on('select', function () {
				var selection = frame.state().get('selection').toJSON();
				options.onSelect(selection);
			});

			frame.open();
		};

		addImageButton.addEventListener('click', function () {
			openImageFrame({
				title: nunlabProjectMedia.addImageTitle,
				buttonText: nunlabProjectMedia.addImageButton,
				multiple: true,
				onSelect: function (selection) {
					selection.forEach(function (attachment) {
						items.push({
							type: 'image',
							imageId: attachment.id,
							imageUrl: '',
							previewUrl: getAttachmentPreview(attachment),
							label: attachment.title || nunlabProjectMedia.imageTypeLabel,
						});
					});

					render();
				},
			});
		});

		addYoutubeButton.addEventListener('click', function () {
			items.push({
				type: 'youtube',
				youtubeUrl: '',
				posterId: 0,
				previewUrl: '',
				posterPreviewUrl: '',
				posterLabel: '',
			});
			render();
		});

		clearButton.addEventListener('click', function () {
			items = [];
			render();
		});

		list.addEventListener('click', function (event) {
			var action = event.target.dataset.mediaAction;
			var index = parseInt(event.target.dataset.index || '-1', 10);
			var item;

			if (!action || Number.isNaN(index) || !items[index]) {
				return;
			}

			item = items[index];

			if ('move-up' === action && index > 0) {
				items.splice(index - 1, 0, items.splice(index, 1)[0]);
				render();
			}

			if ('move-down' === action && index < items.length - 1) {
				items.splice(index + 1, 0, items.splice(index, 1)[0]);
				render();
			}

			if ('remove' === action) {
				items.splice(index, 1);
				render();
			}

			if ('replace-image' === action) {
				openImageFrame({
					title: nunlabProjectMedia.replaceImageTitle,
					buttonText: nunlabProjectMedia.replaceImageButton,
					multiple: false,
					onSelect: function (selection) {
						var attachment = selection[0];

						if (!attachment) {
							return;
						}

						items[index] = {
							type: 'image',
							imageId: attachment.id,
							imageUrl: '',
							previewUrl: getAttachmentPreview(attachment),
							label: attachment.title || nunlabProjectMedia.imageTypeLabel,
						};

						render();
					},
				});
			}

			if ('choose-poster' === action) {
				openImageFrame({
					title: nunlabProjectMedia.selectPosterTitle,
					buttonText: nunlabProjectMedia.selectPosterButton,
					multiple: false,
					onSelect: function (selection) {
						var attachment = selection[0];

						if (!attachment) {
							return;
						}

						items[index].posterId = attachment.id;
						items[index].posterPreviewUrl = getAttachmentPreview(attachment);
						items[index].previewUrl = items[index].posterPreviewUrl;
						items[index].posterLabel = attachment.title || '';
						render();
					},
				});
			}

			if ('clear-poster' === action) {
				items[index].posterId = 0;
				items[index].posterPreviewUrl = '';
				items[index].posterLabel = '';
				items[index].previewUrl = getYoutubePoster(items[index].youtubeUrl);
				render();
			}
		});

		list.addEventListener('input', function (event) {
			var field = event.target.dataset.mediaField;
			var index = parseInt(event.target.dataset.index || '-1', 10);

			if ('youtube-url' !== field || Number.isNaN(index) || !items[index]) {
				return;
			}

			items[index].youtubeUrl = event.target.value.trim();

			if (!items[index].posterId) {
				items[index].previewUrl = getYoutubePoster(items[index].youtubeUrl);
			}

			syncInput();
		});

		render();
	});
})();

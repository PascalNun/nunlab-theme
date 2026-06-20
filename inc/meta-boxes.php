<?php
/**
 * Theme Meta Boxes and Editable Content Fields
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize a gallery field into an array of attachment IDs.
 *
 * Kept for legacy fallback support.
 *
 * @param mixed $value Raw submitted value.
 * @return int[]
 */
function nunlab_sanitize_gallery_ids( $value ) {
	if ( is_string( $value ) ) {
		$value = array_filter( array_map( 'absint', explode( ',', $value ) ) );
	}

	if ( ! is_array( $value ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'absint', $value ) ) );
}

/**
 * Sanitize mixed project media items.
 *
 * @param mixed $value Raw submitted value.
 * @return array<int, array<string, mixed>>
 */
function nunlab_sanitize_project_media_items( $value ) {
	if ( is_string( $value ) ) {
		$decoded = json_decode( wp_unslash( $value ), true );
		$value   = JSON_ERROR_NONE === json_last_error() ? $decoded : array();
	}

	if ( ! is_array( $value ) ) {
		return array();
	}

	$items = array();

	foreach ( $value as $item ) {
		if ( ! is_array( $item ) || empty( $item['type'] ) ) {
			continue;
		}

		if ( 'image' === $item['type'] ) {
			$image_id  = isset( $item['image_id'] ) ? absint( $item['image_id'] ) : 0;
			$image_url = isset( $item['image_url'] ) ? esc_url_raw( (string) $item['image_url'] ) : '';

			if ( ! $image_id && '' === $image_url ) {
				continue;
			}

			$items[] = array(
				'type'      => 'image',
				'image_id'  => $image_id,
				'image_url' => $image_url,
			);
		}

		if ( 'youtube' === $item['type'] ) {
			$youtube_url = isset( $item['youtube_url'] ) ? esc_url_raw( (string) $item['youtube_url'] ) : '';

			if ( '' === $youtube_url ) {
				continue;
			}

			$items[] = array(
				'type'        => 'youtube',
				'youtube_url' => $youtube_url,
				'poster_id'   => isset( $item['poster_id'] ) ? absint( $item['poster_id'] ) : 0,
			);
		}
	}

	return array_values( $items );
}

/**
 * Sanitize the flexible project metadata rows.
 *
 * @param mixed $value Raw submitted value.
 * @return array<int, array{label:string,value:string}>
 */
function nunlab_sanitize_project_meta_items( $value ) {
	if ( is_string( $value ) ) {
		$decoded = json_decode( wp_unslash( $value ), true );
		$value   = JSON_ERROR_NONE === json_last_error() ? $decoded : array();
	}

	if ( ! is_array( $value ) ) {
		return array();
	}

	$items = array();

	foreach ( $value as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$label = isset( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '';
		$text  = isset( $item['value'] ) ? sanitize_textarea_field( (string) $item['value'] ) : '';

		if ( '' === trim( $label ) || '' === trim( $text ) ) {
			continue;
		}

		$items[] = array(
			'label' => $label,
			'value' => $text,
		);

		if ( count( $items ) >= 10 ) {
			break;
		}
	}

	return $items;
}

/**
 * Return enriched media items for the admin editor UI.
 *
 * @param int $post_id Project post ID.
 * @return array<int, array<string, mixed>>
 */
function nunlab_get_project_media_editor_items( $post_id ) {
	$items        = nunlab_get_project_media_data( $post_id );
	$editor_items = array();

	foreach ( $items as $item ) {
		if ( 'image' === $item['type'] ) {
			$image_id   = isset( $item['image_id'] ) ? absint( $item['image_id'] ) : 0;
			$image_url  = isset( $item['image_url'] ) ? esc_url_raw( (string) $item['image_url'] ) : '';
			$preview_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : $image_url;

			$editor_items[] = array(
				'type'       => 'image',
				'imageId'    => $image_id,
				'imageUrl'   => $image_url,
				'previewUrl' => $preview_url,
				'label'      => $image_id ? get_the_title( $image_id ) : __( 'External image', 'nunlab-theme' ),
			);
		}

		if ( 'youtube' === $item['type'] ) {
			$youtube_url = isset( $item['youtube_url'] ) ? esc_url_raw( (string) $item['youtube_url'] ) : '';
			$poster_id   = isset( $item['poster_id'] ) ? absint( $item['poster_id'] ) : 0;
			$video_id    = nunlab_get_youtube_video_id( $youtube_url );

			$editor_items[] = array(
				'type'             => 'youtube',
				'youtubeUrl'       => $youtube_url,
				'posterId'         => $poster_id,
				'previewUrl'       => $poster_id ? wp_get_attachment_image_url( $poster_id, 'thumbnail' ) : ( $video_id ? nunlab_get_youtube_poster_url( $video_id ) : '' ),
				'posterPreviewUrl' => $poster_id ? wp_get_attachment_image_url( $poster_id, 'thumbnail' ) : '',
				'posterLabel'      => $poster_id ? get_the_title( $poster_id ) : '',
			);
		}
	}

	return $editor_items;
}

/**
 * Return the editable fields for plugin/tool entries.
 *
 * @return array<string, array<string, string>>
 */
function nunlab_get_tool_detail_fields() {
	return array(
		'nunlab_tool_status'          => array(
			'label'       => __( 'Status', 'nunlab-theme' ),
			'description' => __( 'Short public status label, for example Alpha, Beta, or Stable.', 'nunlab-theme' ),
			'type'        => 'text',
		),
		'nunlab_tool_version'         => array(
			'label'       => __( 'Current Version', 'nunlab-theme' ),
			'description' => __( 'Optional release/version label shown near the plugin title.', 'nunlab-theme' ),
			'type'        => 'text',
		),
		'nunlab_tool_icon_url'        => array(
			'label'       => __( 'Plugin Icon URL', 'nunlab-theme' ),
			'description' => __( 'Optional small SVG or image icon shown beside the plugin title.', 'nunlab-theme' ),
			'type'        => 'url',
		),
		'nunlab_tool_walkthrough_url' => array(
			'label'       => __( 'YouTube Walkthrough URL', 'nunlab-theme' ),
			'description' => __( 'A YouTube URL for the walkthrough video displayed near the top of the plugin page.', 'nunlab-theme' ),
			'type'        => 'url',
		),
		'nunlab_tool_walkthrough_chapters' => array(
			'label'       => __( 'Walkthrough Chapters', 'nunlab-theme' ),
			'description' => __( 'Optional timestamps for the N:UN player timeline. Use the same format as YouTube chapters, for example: 0:00 Overview.', 'nunlab-theme' ),
			'type'        => 'textarea',
		),
		'nunlab_tool_github_url'      => array(
			'label'       => __( 'GitHub URL', 'nunlab-theme' ),
			'description' => __( 'Repository or source-code link.', 'nunlab-theme' ),
			'type'        => 'url',
		),
		'nunlab_tool_food4rhino_url'  => array(
			'label'       => __( 'Food4Rhino URL', 'nunlab-theme' ),
			'description' => __( 'Optional marketplace/listing link.', 'nunlab-theme' ),
			'type'        => 'url',
		),
		'nunlab_tool_docs_url'        => array(
			'label'       => __( 'Documentation URL', 'nunlab-theme' ),
			'description' => __( 'Optional documentation or guide link.', 'nunlab-theme' ),
			'type'        => 'url',
		),
		'nunlab_tool_release_url'     => array(
			'label'       => __( 'Release / Download URL', 'nunlab-theme' ),
			'description' => __( 'Optional latest release, installer, or download link.', 'nunlab-theme' ),
			'type'        => 'url',
		),
	);
}

/**
 * Register meta keys used by the theme.
 */
function nunlab_register_theme_meta() {
	register_post_meta(
		'project',
		'nunlab_project_media_items',
		array(
			'type'              => 'array',
			'single'            => true,
			'sanitize_callback' => 'nunlab_sanitize_project_media_items',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'type'        => array( 'type' => 'string' ),
							'image_id'    => array( 'type' => 'integer' ),
							'image_url'   => array( 'type' => 'string' ),
							'youtube_url' => array( 'type' => 'string' ),
							'poster_id'   => array( 'type' => 'integer' ),
						),
					),
				),
			),
		)
	);

	register_post_meta(
		'project',
		'nunlab_project_gallery_ids',
		array(
			'type'              => 'array',
			'single'            => true,
			'sanitize_callback' => 'nunlab_sanitize_gallery_ids',
			'show_in_rest'      => false,
		)
	);

	register_post_meta(
		'project',
		'nunlab_project_meta_items',
		array(
			'type'              => 'array',
			'single'            => true,
			'sanitize_callback' => 'nunlab_sanitize_project_meta_items',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'label' => array( 'type' => 'string' ),
							'value' => array( 'type' => 'string' ),
						),
					),
				),
			),
		)
	);

	foreach ( nunlab_get_tool_detail_fields() as $meta_key => $field ) {
		$sanitize_callback = 'sanitize_text_field';

		if ( 'url' === $field['type'] ) {
			$sanitize_callback = 'esc_url_raw';
		}

		if ( 'textarea' === $field['type'] ) {
			$sanitize_callback = 'sanitize_textarea_field';
		}

		register_post_meta(
			'tool',
			$meta_key,
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => $sanitize_callback,
				'show_in_rest'      => true,
			)
		);
	}

	register_post_meta(
		'tool',
		'nunlab_tool_youtube_custom_controls',
		array(
			'type'              => 'boolean',
			'single'            => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
		)
	);

	foreach (
		array(
			'nunlab_project_title_line_one' => 'string',
			'nunlab_project_title_line_two' => 'string',
			'nunlab_hero_title'   => 'string',
			'nunlab_hero_intro'   => 'string',
			'nunlab_work_eyebrow' => 'string',
			'nunlab_work_heading' => 'string',
			'nunlab_about_page_id' => 'integer',
			'nunlab_manifesto_page_id' => 'integer',
		) as $meta_key => $meta_type
	) {
		register_post_meta(
			'page',
			$meta_key,
			array(
				'type'              => $meta_type,
				'single'            => true,
				'sanitize_callback' => 'integer' === $meta_type ? 'absint' : 'sanitize_textarea_field',
				'show_in_rest'      => true,
			)
		);
	}
}
add_action( 'init', 'nunlab_register_theme_meta' );

/**
 * Add the project media meta box.
 */
function nunlab_add_project_meta_boxes() {
	add_meta_box(
		'nunlab-project-presentation',
		esc_html__( 'Project Presentation', 'nunlab-theme' ),
		'nunlab_render_project_presentation_meta_box',
		'project',
		'side',
		'default'
	);

	add_meta_box(
		'nunlab-project-meta',
		esc_html__( 'Project Metadata', 'nunlab-theme' ),
		'nunlab_render_project_meta_box',
		'project',
		'normal',
		'default'
	);

	add_meta_box(
		'nunlab-project-media',
		esc_html__( 'Project Media', 'nunlab-theme' ),
		'nunlab_render_project_media_meta_box',
		'project',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_project', 'nunlab_add_project_meta_boxes' );

/**
 * Add the plugin/tool detail meta box.
 */
function nunlab_add_tool_meta_boxes() {
	add_meta_box(
		'nunlab-tool-details',
		esc_html__( 'Plugin Details', 'nunlab-theme' ),
		'nunlab_render_tool_details_meta_box',
		'tool',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes_tool', 'nunlab_add_tool_meta_boxes' );

/**
 * Render the project presentation meta box.
 *
 * @param WP_Post $post Current project.
 */
function nunlab_render_project_presentation_meta_box( $post ) {
	$title_line_one = (string) get_post_meta( $post->ID, 'nunlab_project_title_line_one', true );
	$title_line_two = (string) get_post_meta( $post->ID, 'nunlab_project_title_line_two', true );

	wp_nonce_field( 'nunlab_save_project_presentation', 'nunlab_project_presentation_nonce' );
	?>
	<div class="nunlab-admin-fields">
		<p class="nunlab-admin-fields__field">
			<label class="nunlab-admin-fields__label" for="nunlab_project_title_line_one">
				<?php esc_html_e( 'Expanded Title Line 1', 'nunlab-theme' ); ?>
			</label>
			<input
				id="nunlab_project_title_line_one"
				name="nunlab_project_title_line_one"
				type="text"
				class="widefat"
				value="<?php echo esc_attr( $title_line_one ); ?>"
			/>
			<span class="description"><?php esc_html_e( 'Optional lighter first line for the opened project card headline.', 'nunlab-theme' ); ?></span>
		</p>

		<p class="nunlab-admin-fields__field">
			<label class="nunlab-admin-fields__label" for="nunlab_project_title_line_two">
				<?php esc_html_e( 'Expanded Title Line 2', 'nunlab-theme' ); ?>
			</label>
			<input
				id="nunlab_project_title_line_two"
				name="nunlab_project_title_line_two"
				type="text"
				class="widefat"
				value="<?php echo esc_attr( $title_line_two ); ?>"
			/>
			<span class="description"><?php esc_html_e( 'Optional stronger second line for the opened project card headline.', 'nunlab-theme' ); ?></span>
		</p>

		<p class="description">
			<?php esc_html_e( 'The project excerpt is used as the expanded subheadline below the title.', 'nunlab-theme' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Render the flexible project metadata meta box.
 *
 * @param WP_Post $post Current project.
 */
function nunlab_render_project_meta_box( $post ) {
	$items = nunlab_get_project_meta_items( $post->ID );

	wp_nonce_field( 'nunlab_save_project_meta', 'nunlab_project_meta_nonce' );
	?>
	<div class="nunlab-admin-fields">
		<p class="description">
			<?php esc_html_e( 'Add up to ten short facts such as Type, Program, Location, Year, Role, or Scope. Empty rows are ignored.', 'nunlab-theme' ); ?>
		</p>

		<div class="nunlab-admin-meta-list">
			<?php for ( $index = 0; $index < 10; $index++ ) : ?>
				<?php
				$item  = isset( $items[ $index ] ) ? $items[ $index ] : array( 'label' => '', 'value' => '' );
				$label = isset( $item['label'] ) ? (string) $item['label'] : '';
				$value = isset( $item['value'] ) ? (string) $item['value'] : '';
				?>
				<div class="nunlab-admin-meta-row">
					<label class="nunlab-admin-media__field">
						<span class="nunlab-admin-media__field-label"><?php esc_html_e( 'Label', 'nunlab-theme' ); ?></span>
						<input
							name="nunlab_project_meta_labels[]"
							type="text"
							class="widefat"
							value="<?php echo esc_attr( $label ); ?>"
							placeholder="<?php esc_attr_e( 'Type', 'nunlab-theme' ); ?>"
						/>
					</label>
					<label class="nunlab-admin-media__field">
						<span class="nunlab-admin-media__field-label"><?php esc_html_e( 'Value', 'nunlab-theme' ); ?></span>
						<textarea
							name="nunlab_project_meta_values[]"
							class="widefat"
							rows="2"
							placeholder="<?php esc_attr_e( 'Concept study', 'nunlab-theme' ); ?>"
						><?php echo esc_textarea( $value ); ?></textarea>
					</label>
				</div>
			<?php endfor; ?>
		</div>
	</div>
	<?php
}

/**
 * Render the plugin/tool detail meta box.
 *
 * @param WP_Post $post Current tool.
 */
function nunlab_render_tool_details_meta_box( $post ) {
	$use_custom_youtube_controls = (bool) get_post_meta( $post->ID, 'nunlab_tool_youtube_custom_controls', true );

	wp_nonce_field( 'nunlab_save_tool_details', 'nunlab_tool_details_nonce' );
	?>
	<div class="nunlab-admin-fields">
		<?php foreach ( nunlab_get_tool_detail_fields() as $meta_key => $field ) : ?>
			<?php $value = (string) get_post_meta( $post->ID, $meta_key, true ); ?>
			<p class="nunlab-admin-fields__field">
				<label class="nunlab-admin-fields__label" for="<?php echo esc_attr( $meta_key ); ?>">
					<?php echo esc_html( $field['label'] ); ?>
				</label>
				<?php if ( 'textarea' === $field['type'] ) : ?>
					<textarea
						id="<?php echo esc_attr( $meta_key ); ?>"
						name="<?php echo esc_attr( $meta_key ); ?>"
						class="widefat"
						rows="6"
					><?php echo esc_textarea( $value ); ?></textarea>
				<?php else : ?>
					<input
						id="<?php echo esc_attr( $meta_key ); ?>"
						name="<?php echo esc_attr( $meta_key ); ?>"
						type="<?php echo esc_attr( $field['type'] ); ?>"
						class="widefat"
						value="<?php echo esc_attr( $value ); ?>"
					/>
				<?php endif; ?>
				<span class="description"><?php echo esc_html( $field['description'] ); ?></span>
			</p>
		<?php endforeach; ?>

		<p class="nunlab-admin-fields__field">
			<label>
				<input
					type="checkbox"
					name="nunlab_tool_youtube_custom_controls"
					value="1"
					<?php checked( $use_custom_youtube_controls ); ?>
				/>
				<?php esc_html_e( 'Use N:UN controls for the YouTube walkthrough', 'nunlab-theme' ); ?>
			</label>
			<span class="description">
				<?php esc_html_e( 'Optional. Keeps the designed poster and overlays the N:UN controls after play. YouTube still owns the embedded video and some browser/device behavior.', 'nunlab-theme' ); ?>
			</span>
		</p>

		<p class="description">
			<?php esc_html_e( 'Use the featured image for the plugin card/poster. Use the main editor for chapter-style sections: start each chapter with a Heading block, then add text and screenshots below it.', 'nunlab-theme' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Add the front-page content fields meta box.
 *
 * @param WP_Post $post Current page post.
 */
function nunlab_add_front_page_meta_box( $post ) {
	$front_page_id = (int) get_option( 'page_on_front' );

	if ( $front_page_id !== (int) $post->ID ) {
		return;
	}

	add_meta_box(
		'nunlab-front-page-fields',
		esc_html__( 'Front Page Content', 'nunlab-theme' ),
		'nunlab_render_front_page_meta_box',
		'page',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_page', 'nunlab_add_front_page_meta_box' );

/**
 * Render the project media meta box.
 *
 * @param WP_Post $post Current project.
 */
function nunlab_render_project_media_meta_box( $post ) {
	$stored_items = nunlab_get_project_media_data( $post->ID );
	$editor_items = nunlab_get_project_media_editor_items( $post->ID );
	$legacy_urls  = get_post_meta( $post->ID, 'nunlab_demo_gallery', true );
	$legacy_urls  = is_array( $legacy_urls ) ? array_values( array_filter( $legacy_urls ) ) : array();

	wp_nonce_field( 'nunlab_save_project_media', 'nunlab_project_media_nonce' );
	?>
	<div class="nunlab-admin-media" data-project-media-box>
		<p class="description">
			<?php esc_html_e( 'Build an ordered sequence of images and YouTube videos for the homepage expansion and the full project page.', 'nunlab-theme' ); ?>
		</p>

		<input type="hidden" name="nunlab_project_media_items" value="<?php echo esc_attr( wp_json_encode( $stored_items ) ); ?>" data-media-input />
		<script type="application/json" data-media-state><?php echo wp_json_encode( $editor_items ); ?></script>

		<div class="nunlab-admin-media__actions">
			<button type="button" class="button button-secondary" data-media-add-image>
				<?php esc_html_e( 'Add image slide', 'nunlab-theme' ); ?>
			</button>
			<button type="button" class="button button-secondary" data-media-add-youtube>
				<?php esc_html_e( 'Add YouTube slide', 'nunlab-theme' ); ?>
			</button>
			<button type="button" class="button-link button-link-delete" data-media-clear>
				<?php esc_html_e( 'Clear all', 'nunlab-theme' ); ?>
			</button>
		</div>

		<div class="nunlab-admin-media__list" data-media-list></div>

		<?php if ( ! $stored_items && $legacy_urls ) : ?>
			<p class="description">
				<?php esc_html_e( 'Local demo images are still being used on the front end until you add real project media here.', 'nunlab-theme' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render the front-page content fields meta box.
 *
 * @param WP_Post $post Current front page.
 */
function nunlab_render_front_page_meta_box( $post ) {
	$fields = array(
		'nunlab_hero_title'   => array(
			'label'       => __( 'Hero Title', 'nunlab-theme' ),
			'description' => __( 'Main opening line at the top of the homepage.', 'nunlab-theme' ),
		),
		'nunlab_hero_intro'   => array(
			'label'       => __( 'Hero Intro', 'nunlab-theme' ),
			'description' => __( 'Short supporting paragraph below the hero title.', 'nunlab-theme' ),
		),
		'nunlab_work_eyebrow' => array(
			'label'       => __( 'Portfolio Section Label', 'nunlab-theme' ),
			'description' => __( 'Small label above the project index. You can use Work, Portfolio, Selected Work, Projects, or a custom phrase.', 'nunlab-theme' ),
		),
		'nunlab_work_heading' => array(
			'label'       => __( 'Portfolio Section Heading', 'nunlab-theme' ),
			'description' => __( 'Main heading above the expandable project index on the front page.', 'nunlab-theme' ),
		),
	);
	$about_page_id = (int) get_post_meta( $post->ID, 'nunlab_about_page_id', true );
	$manifesto_page_id = (int) get_post_meta( $post->ID, 'nunlab_manifesto_page_id', true );
	$source_pages      = get_pages(
		array(
			'sort_column' => 'menu_order,post_title',
			'exclude'     => array( (int) $post->ID ),
		)
	);

	wp_nonce_field( 'nunlab_save_front_page_fields', 'nunlab_front_page_fields_nonce' );
	?>
	<div class="nunlab-admin-fields">
		<?php foreach ( $fields as $meta_key => $field ) : ?>
			<?php $value = (string) get_post_meta( $post->ID, $meta_key, true ); ?>
			<p class="nunlab-admin-fields__field">
				<label class="nunlab-admin-fields__label" for="<?php echo esc_attr( $meta_key ); ?>">
					<?php echo esc_html( $field['label'] ); ?>
				</label>
				<textarea
					id="<?php echo esc_attr( $meta_key ); ?>"
					name="<?php echo esc_attr( $meta_key ); ?>"
					class="widefat"
					rows="3"
				><?php echo esc_textarea( $value ); ?></textarea>
				<span class="description"><?php echo esc_html( $field['description'] ); ?></span>
			</p>
		<?php endforeach; ?>

		<p class="nunlab-admin-fields__field">
			<label class="nunlab-admin-fields__label" for="nunlab_about_page_id">
				<?php esc_html_e( 'About Section Source Page', 'nunlab-theme' ); ?>
			</label>
			<select id="nunlab_about_page_id" name="nunlab_about_page_id" class="widefat">
				<option value="0"><?php esc_html_e( 'Select a page', 'nunlab-theme' ); ?></option>
				<?php foreach ( $source_pages as $page ) : ?>
					<option value="<?php echo esc_attr( $page->ID ); ?>"<?php selected( $about_page_id, (int) $page->ID ); ?>>
						<?php echo esc_html( $page->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<span class="description"><?php esc_html_e( 'This page is rendered as the About section below the portfolio/project section on the front page.', 'nunlab-theme' ); ?></span>
		</p>

		<p class="nunlab-admin-fields__field">
			<label class="nunlab-admin-fields__label" for="nunlab_manifesto_page_id">
				<?php esc_html_e( 'Manifesto Section Source Page', 'nunlab-theme' ); ?>
			</label>
			<select id="nunlab_manifesto_page_id" name="nunlab_manifesto_page_id" class="widefat">
				<option value="0"><?php esc_html_e( 'Select a page', 'nunlab-theme' ); ?></option>
				<?php foreach ( $source_pages as $page ) : ?>
					<option value="<?php echo esc_attr( $page->ID ); ?>"<?php selected( $manifesto_page_id, (int) $page->ID ); ?>>
						<?php echo esc_html( $page->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<span class="description"><?php esc_html_e( 'This page is rendered as the Manifesto section further down the front page.', 'nunlab-theme' ); ?></span>
		</p>
	</div>
	<?php
}

/**
 * Save project media meta.
 *
 * @param int $post_id Post ID.
 */
function nunlab_save_project_media_meta( $post_id ) {
	if ( ! isset( $_POST['nunlab_project_media_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nunlab_project_media_nonce'] ) ), 'nunlab_save_project_media' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$media_items = isset( $_POST['nunlab_project_media_items'] ) ? nunlab_sanitize_project_media_items( wp_unslash( $_POST['nunlab_project_media_items'] ) ) : array();

	if ( $media_items ) {
		update_post_meta( $post_id, 'nunlab_project_media_items', $media_items );
	} else {
		delete_post_meta( $post_id, 'nunlab_project_media_items' );
	}

	$legacy_image_ids = array();

	foreach ( $media_items as $item ) {
		if ( isset( $item['type'], $item['image_id'] ) && 'image' === $item['type'] && absint( $item['image_id'] ) ) {
			$legacy_image_ids[] = absint( $item['image_id'] );
		}
	}

	update_post_meta( $post_id, 'nunlab_project_gallery_ids', $legacy_image_ids );
}
add_action( 'save_post_project', 'nunlab_save_project_media_meta' );

/**
 * Save project presentation meta.
 *
 * @param int $post_id Post ID.
 */
function nunlab_save_project_presentation_meta( $post_id ) {
	if ( ! isset( $_POST['nunlab_project_presentation_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nunlab_project_presentation_nonce'] ) ), 'nunlab_save_project_presentation' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array( 'nunlab_project_title_line_one', 'nunlab_project_title_line_two' ) as $meta_key ) {
		if ( ! isset( $_POST[ $meta_key ] ) ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}

		$value = sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) );

		if ( '' === trim( $value ) ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}

		update_post_meta( $post_id, $meta_key, $value );
	}
}
add_action( 'save_post_project', 'nunlab_save_project_presentation_meta' );

/**
 * Save project metadata rows.
 *
 * @param int $post_id Post ID.
 */
function nunlab_save_project_meta_items_meta( $post_id ) {
	if ( ! isset( $_POST['nunlab_project_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nunlab_project_meta_nonce'] ) ), 'nunlab_save_project_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$labels = isset( $_POST['nunlab_project_meta_labels'] ) ? wp_unslash( $_POST['nunlab_project_meta_labels'] ) : array();
	$values = isset( $_POST['nunlab_project_meta_values'] ) ? wp_unslash( $_POST['nunlab_project_meta_values'] ) : array();

	if ( ! is_array( $labels ) || ! is_array( $values ) ) {
		delete_post_meta( $post_id, 'nunlab_project_meta_items' );
		return;
	}

	$raw_items = array();

	for ( $index = 0; $index < 10; $index++ ) {
		$raw_items[] = array(
			'label' => isset( $labels[ $index ] ) ? (string) $labels[ $index ] : '',
			'value' => isset( $values[ $index ] ) ? (string) $values[ $index ] : '',
		);
	}

	$items = nunlab_sanitize_project_meta_items( $raw_items );

	if ( $items ) {
		update_post_meta( $post_id, 'nunlab_project_meta_items', $items );
	} else {
		delete_post_meta( $post_id, 'nunlab_project_meta_items' );
	}
}
add_action( 'save_post_project', 'nunlab_save_project_meta_items_meta' );

/**
 * Save plugin/tool detail meta.
 *
 * @param int $post_id Post ID.
 */
function nunlab_save_tool_details_meta( $post_id ) {
	if ( ! isset( $_POST['nunlab_tool_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nunlab_tool_details_nonce'] ) ), 'nunlab_save_tool_details' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( nunlab_get_tool_detail_fields() as $meta_key => $field ) {
		if ( ! isset( $_POST[ $meta_key ] ) ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}

		$value = sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) );

		if ( 'url' === $field['type'] ) {
			$value = esc_url_raw( wp_unslash( $_POST[ $meta_key ] ) );
		}

		if ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( wp_unslash( $_POST[ $meta_key ] ) );
		}

		if ( '' === trim( $value ) ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}

		update_post_meta( $post_id, $meta_key, $value );
	}

	update_post_meta(
		$post_id,
		'nunlab_tool_youtube_custom_controls',
		isset( $_POST['nunlab_tool_youtube_custom_controls'] ) ? 1 : 0
	);
}
add_action( 'save_post_tool', 'nunlab_save_tool_details_meta' );

/**
 * Save front-page content fields.
 *
 * @param int $post_id Post ID.
 */
function nunlab_save_front_page_fields( $post_id ) {
	if ( ! isset( $_POST['nunlab_front_page_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nunlab_front_page_fields_nonce'] ) ), 'nunlab_save_front_page_fields' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach (
		array(
			'nunlab_hero_title',
			'nunlab_hero_intro',
			'nunlab_work_eyebrow',
			'nunlab_work_heading',
		) as $meta_key
	) {
		if ( ! isset( $_POST[ $meta_key ] ) ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}

		$value = sanitize_textarea_field( wp_unslash( $_POST[ $meta_key ] ) );

		if ( '' === trim( $value ) ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}

		update_post_meta( $post_id, $meta_key, $value );
	}

	if ( isset( $_POST['nunlab_about_page_id'] ) ) {
		$about_page_id = absint( wp_unslash( $_POST['nunlab_about_page_id'] ) );

		if ( $about_page_id ) {
			update_post_meta( $post_id, 'nunlab_about_page_id', $about_page_id );
		} else {
			delete_post_meta( $post_id, 'nunlab_about_page_id' );
		}
	}

	if ( isset( $_POST['nunlab_manifesto_page_id'] ) ) {
		$manifesto_page_id = absint( wp_unslash( $_POST['nunlab_manifesto_page_id'] ) );

		if ( $manifesto_page_id ) {
			update_post_meta( $post_id, 'nunlab_manifesto_page_id', $manifesto_page_id );
		} else {
			delete_post_meta( $post_id, 'nunlab_manifesto_page_id' );
		}
	}
}
add_action( 'save_post_page', 'nunlab_save_front_page_fields' );

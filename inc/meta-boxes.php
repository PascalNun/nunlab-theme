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

	foreach (
		array(
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
			'label'       => __( 'Work Eyebrow', 'nunlab-theme' ),
			'description' => __( 'Small section label above the work heading.', 'nunlab-theme' ),
		),
		'nunlab_work_heading' => array(
			'label'       => __( 'Work Heading', 'nunlab-theme' ),
			'description' => __( 'Main heading above the expandable work index.', 'nunlab-theme' ),
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
			<span class="description"><?php esc_html_e( 'This page is rendered as the About section below Work on the front page.', 'nunlab-theme' ); ?></span>
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

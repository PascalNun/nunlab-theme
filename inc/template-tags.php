<?php
/**
 * Theme Template Tags
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print a post date for notebook entries.
 */
function nunlab_posted_on() {
	if ( 'post' !== get_post_type() ) {
		return;
	}
	?>
	<time class="entry-date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
		<?php echo esc_html( get_the_date() ); ?>
	</time>
	<?php
}

/**
 * Return project archive sections in the preferred display order.
 *
 * @param array $args Section query options.
 * @return WP_Term[]
 */
function nunlab_get_project_archive_sections( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'preferred_order'    => array( 'concept', 'work', 'research', 'build' ),
			'hide_empty'         => true,
			'include_additional' => true,
		)
	);

	$sections        = array();
	$preferred_order = (array) $args['preferred_order'];
	$hide_empty      = (bool) $args['hide_empty'];
	$excluded_ids    = array();

	foreach ( $preferred_order as $slug ) {
		$term = get_term_by( 'slug', $slug, 'project_type' );

		if ( $term instanceof WP_Term && ( ! $hide_empty || $term->count > 0 ) ) {
			$sections[]     = $term;
			$excluded_ids[] = (int) $term->term_id;
		}
	}

	if ( ! $args['include_additional'] ) {
		return $sections;
	}

	$additional_terms = get_terms(
		array(
			'taxonomy'   => 'project_type',
			'hide_empty' => $hide_empty,
			'exclude'    => $excluded_ids,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $additional_terms ) ) {
		return $sections;
	}

	return array_merge( $sections, $additional_terms );
}

/**
 * Return a front-page meta field with a fallback value.
 *
 * @param string $meta_key Meta key.
 * @param string $default  Fallback text.
 * @return string
 */
function nunlab_get_front_page_field( $meta_key, $default = '' ) {
	$front_page_id = (int) get_option( 'page_on_front' );

	if ( ! $front_page_id ) {
		return $default;
	}

	$value = get_post_meta( $front_page_id, $meta_key, true );

	return is_string( $value ) && '' !== trim( $value ) ? $value : $default;
}

/**
 * Return a published page chosen as a front-page section source.
 *
 * @param string $meta_key      Front-page meta key that stores the page ID.
 * @param string $fallback_slug Optional fallback page slug.
 * @return WP_Post|null
 */
function nunlab_get_front_page_source_page( $meta_key, $fallback_slug = '' ) {
	$front_page_id = (int) get_option( 'page_on_front' );

	if ( ! $front_page_id ) {
		return null;
	}

	$page_id = absint( get_post_meta( $front_page_id, $meta_key, true ) );
	$page    = $page_id ? get_post( $page_id ) : null;

	if ( ! $page instanceof WP_Post && '' !== $fallback_slug ) {
		$page = get_page_by_path( $fallback_slug );
	}

	if ( ! $page instanceof WP_Post || 'page' !== $page->post_type || 'publish' !== $page->post_status ) {
		return null;
	}

	return $page;
}

/**
 * Return a theme asset URI when the file exists.
 *
 * @param string $relative_path Relative path from theme root.
 * @return string
 */
function nunlab_get_theme_asset_uri( $relative_path ) {
	$relative_path = ltrim( (string) $relative_path, '/' );

	if ( '' === $relative_path ) {
		return '';
	}

	$asset_path = NUNLAB_THEME_DIR . '/' . $relative_path;

	if ( ! file_exists( $asset_path ) ) {
		return '';
	}

	return NUNLAB_THEME_URI . '/' . $relative_path;
}

/**
 * Return stored legacy project gallery attachment IDs.
 *
 * @param int $post_id Project post ID.
 * @return int[]
 */
function nunlab_get_project_gallery_ids( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$value   = get_post_meta( $post_id, 'nunlab_project_gallery_ids', true );

	if ( is_string( $value ) ) {
		$value = array_filter( array_map( 'absint', explode( ',', $value ) ) );
	}

	if ( ! is_array( $value ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'absint', $value ) ) );
}

/**
 * Extract a YouTube video ID from a supported URL.
 *
 * @param string $url YouTube URL.
 * @return string
 */
function nunlab_get_youtube_video_id( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	if ( preg_match( '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches ) ) {
		return $matches[1];
	}

	$parsed_url = wp_parse_url( $url );

	if ( empty( $parsed_url['query'] ) ) {
		return '';
	}

	parse_str( $parsed_url['query'], $query_args );

	return isset( $query_args['v'] ) ? sanitize_text_field( $query_args['v'] ) : '';
}

/**
 * Return the privacy-friendly YouTube embed URL.
 *
 * @param string $video_id YouTube video ID.
 * @param bool   $autoplay Whether to autoplay.
 * @return string
 */
function nunlab_get_youtube_embed_url( $video_id, $autoplay = false ) {
	$query_args = array(
		'playsinline'    => 1,
		'rel'            => 0,
		'modestbranding' => 1,
	);

	if ( $autoplay ) {
		$query_args['autoplay'] = 1;
	}

	return add_query_arg( $query_args, 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $video_id ) );
}

/**
 * Return a fallback YouTube poster image URL.
 *
 * @param string $video_id YouTube video ID.
 * @return string
 */
function nunlab_get_youtube_poster_url( $video_id ) {
	return 'https://i.ytimg.com/vi/' . rawurlencode( $video_id ) . '/hqdefault.jpg';
}

/**
 * Return stored raw project media items.
 *
 * Falls back to the legacy gallery fields until real media items are added.
 *
 * @param int $post_id Project post ID.
 * @return array<int, array<string, mixed>>
 */
function nunlab_get_project_media_data( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$value   = get_post_meta( $post_id, 'nunlab_project_media_items', true );
	$items   = array();

	if ( is_string( $value ) ) {
		$decoded = json_decode( $value, true );
		$value   = JSON_ERROR_NONE === json_last_error() ? $decoded : array();
	}

	if ( is_array( $value ) ) {
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
					'type'     => 'image',
					'image_id' => $image_id,
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
	}

	if ( $items ) {
		return $items;
	}

	$gallery_ids = nunlab_get_project_gallery_ids( $post_id );

	if ( $gallery_ids ) {
		foreach ( $gallery_ids as $attachment_id ) {
			$items[] = array(
				'type'     => 'image',
				'image_id' => $attachment_id,
				'image_url' => '',
			);
		}

		return $items;
	}

	$demo_gallery = get_post_meta( $post_id, 'nunlab_demo_gallery', true );
	$demo_gallery = is_array( $demo_gallery ) ? array_values( array_filter( $demo_gallery ) ) : array();

	foreach ( $demo_gallery as $image_url ) {
		$items[] = array(
			'type'     => 'image',
			'image_id' => 0,
			'image_url' => esc_url_raw( (string) $image_url ),
		);
	}

	return $items;
}

/**
 * Return normalized project media items for the front end.
 *
 * @param int    $post_id Project post ID.
 * @param string $size    Requested image size.
 * @return array<int, array<string, string|int>>
 */
function nunlab_get_project_media_items( $post_id = 0, $size = 'large' ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$items   = nunlab_get_project_media_data( $post_id );
	$media   = array();

	foreach ( $items as $index => $item ) {
		if ( 'image' === $item['type'] ) {
			$image_id  = isset( $item['image_id'] ) ? absint( $item['image_id'] ) : 0;
			$image_url = $image_id ? wp_get_attachment_image_url( $image_id, $size ) : ( isset( $item['image_url'] ) ? esc_url_raw( (string) $item['image_url'] ) : '' );
			$image_alt = $image_id ? (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '';

			if ( ! $image_url ) {
				continue;
			}

			$media[] = array(
				'type'              => 'image',
				'id'                => $image_id,
				'url'               => $image_url,
				'poster_url'        => $image_url,
				'alt'               => $image_alt ? $image_alt : sprintf(
					/* translators: %d is the image position. */
					__( 'Project media image %d', 'nunlab-theme' ),
					$index + 1
				),
				'embed_url'         => '',
				'autoplay_embed_url' => '',
			);

			continue;
		}

		if ( 'youtube' !== $item['type'] ) {
			continue;
		}

		$youtube_url = isset( $item['youtube_url'] ) ? esc_url_raw( (string) $item['youtube_url'] ) : '';
		$video_id    = nunlab_get_youtube_video_id( $youtube_url );

		if ( '' === $video_id ) {
			continue;
		}

		$poster_id  = isset( $item['poster_id'] ) ? absint( $item['poster_id'] ) : 0;
		$poster_url = $poster_id ? wp_get_attachment_image_url( $poster_id, $size ) : nunlab_get_youtube_poster_url( $video_id );
		$poster_alt = $poster_id ? (string) get_post_meta( $poster_id, '_wp_attachment_image_alt', true ) : get_the_title( $post_id );

		$media[] = array(
			'type'               => 'youtube',
			'id'                 => $poster_id,
			'url'                => $poster_url,
			'poster_url'         => $poster_url,
			'alt'                => $poster_alt,
			'embed_url'          => nunlab_get_youtube_embed_url( $video_id, false ),
			'autoplay_embed_url' => nunlab_get_youtube_embed_url( $video_id, true ),
		);
	}

	return $media;
}

/**
 * Return the primary project image for cards and previews.
 *
 * @param int    $post_id Project post ID.
 * @param string $size    Requested image size.
 * @return array<string, string|int>
 */
function nunlab_get_project_primary_image( $post_id = 0, $size = 'large' ) {
	$post_id    = $post_id ? (int) $post_id : get_the_ID();
	$media_items = nunlab_get_project_media_items( $post_id, $size );

	if ( $media_items ) {
		return array(
			'id'  => isset( $media_items[0]['id'] ) ? (int) $media_items[0]['id'] : 0,
			'url' => isset( $media_items[0]['poster_url'] ) ? (string) $media_items[0]['poster_url'] : '',
			'alt' => isset( $media_items[0]['alt'] ) ? (string) $media_items[0]['alt'] : '',
		);
	}

	$image_id = get_post_thumbnail_id( $post_id );

	if ( $image_id ) {
		return array(
			'id'  => $image_id,
			'url' => (string) wp_get_attachment_image_url( $image_id, $size ),
			'alt' => (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
		);
	}

	return array(
		'id'  => 0,
		'url' => '',
		'alt' => '',
	);
}

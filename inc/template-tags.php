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
 * Determine whether rendered block markup still contains visible non-text media.
 *
 * @param string $html Rendered block markup.
 * @return bool
 */
function nunlab_block_has_visible_media_markup( $html ) {
	$html = (string) $html;

	if ( '' === trim( $html ) ) {
		return false;
	}

	return 1 === preg_match( '/<(img|figure|video|iframe|svg|canvas|audio|picture)\b/i', $html );
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
 * Return the optional two-line project title used by project presentation views.
 *
 * @param int $post_id Project post ID.
 * @return array{line_one:string,line_two:string,fallback:string,text:string}
 */
function nunlab_get_project_presentation_title_parts( $post_id = 0 ) {
	$post_id   = $post_id ? (int) $post_id : get_the_ID();
	$line_one  = trim( (string) get_post_meta( $post_id, 'nunlab_project_title_line_one', true ) );
	$line_two  = trim( (string) get_post_meta( $post_id, 'nunlab_project_title_line_two', true ) );
	$fallback  = get_the_title( $post_id );
	$plain     = trim( $line_one . ' ' . $line_two );

	return array(
		'line_one' => $line_one,
		'line_two' => $line_two,
		'fallback' => $fallback,
		'text'     => '' !== $plain ? $plain : $fallback,
	);
}

/**
 * Return escaped title-line markup for a project presentation title.
 *
 * @param array  $title_parts Title parts from nunlab_get_project_presentation_title_parts().
 * @param string $base_class  Base class for the generated line spans.
 * @return string
 */
function nunlab_get_project_presentation_title_markup( $title_parts, $base_class ) {
	$line_one = isset( $title_parts['line_one'] ) ? (string) $title_parts['line_one'] : '';
	$line_two = isset( $title_parts['line_two'] ) ? (string) $title_parts['line_two'] : '';
	$fallback = isset( $title_parts['fallback'] ) ? (string) $title_parts['fallback'] : '';

	if ( '' !== $line_one && '' !== $line_two ) {
		return sprintf(
			'<span class="%1$s">%2$s</span><span class="%3$s">%4$s</span>',
			esc_attr( $base_class . ' ' . $base_class . '--primary' ),
			esc_html( $line_one ),
			esc_attr( $base_class . ' ' . $base_class . '--emphasis' ),
			esc_html( $line_two )
		);
	}

	return sprintf(
		'<span class="%1$s">%2$s</span>',
		esc_attr( $base_class . ' ' . $base_class . '--emphasis' ),
		esc_html( '' !== $line_one ? $line_one : ( '' !== $line_two ? $line_two : $fallback ) )
	);
}

/**
 * Return compact public metadata for plugin/tool cards and pages.
 *
 * @param int  $post_id      Tool post ID.
 * @param bool $include_type Whether to include the "Plugin" type label.
 * @return string[]
 */
function nunlab_get_tool_meta_parts( $post_id = 0, $include_type = true ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$parts   = array();

	if ( $include_type ) {
		$parts[] = __( 'Plugin', 'nunlab-theme' );
	}

	$parts[] = trim( (string) get_post_meta( $post_id, 'nunlab_tool_status', true ) );
	$parts[] = trim( (string) get_post_meta( $post_id, 'nunlab_tool_version', true ) );

	return array_values( array_filter( $parts ) );
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
 * Split block content into editorial sections.
 *
 * Each heading starts a new section. Non-heading blocks are collected under the
 * current section so front-page editorial layouts can restart their columns
 * cleanly at each heading.
 *
 * @param string $content Raw block content.
 * @return array<int, array{heading:string, blocks:array<int, string>}>
 */
function nunlab_get_editorial_sections( $content ) {
	$content  = (string) $content;
	$blocks   = parse_blocks( $content );
	$sections = array();
	$current  = array(
		'heading' => '',
		'blocks'  => array(),
	);

	if ( empty( $blocks ) ) {
		return array();
	}

	foreach ( $blocks as $block ) {
		$rendered = trim( render_block( $block ) );

		if ( '' === $rendered ) {
			continue;
		}

		if ( '' === trim( wp_strip_all_tags( $rendered ) ) && ! nunlab_block_has_visible_media_markup( $rendered ) ) {
			continue;
		}

		if ( 'core/heading' === $block['blockName'] ) {
			if ( '' !== $current['heading'] || ! empty( $current['blocks'] ) ) {
				$sections[] = $current;
			}

			$current = array(
				'heading' => $rendered,
				'blocks'  => array(),
			);
			continue;
		}

		$current['blocks'][] = $rendered;
	}

	if ( '' !== $current['heading'] || ! empty( $current['blocks'] ) ) {
		$sections[] = $current;
	}

	return $sections;
}

/**
 * Render editorial sections for front-page text areas.
 *
 * @param string $content Raw block content.
 * @param string $layout  Rendering mode. Accepts 'stacked', 'flow', or 'continuous'.
 * @return string
 */
function nunlab_render_editorial_sections( $content, $layout = 'stacked' ) {
	$sections = nunlab_get_editorial_sections( $content );

	if ( empty( $sections ) ) {
		return '';
	}

	ob_start();
	?>
	<?php if ( 'continuous' === $layout ) : ?>
		<div class="editorial-flow editorial-flow--continuous">
			<?php foreach ( $sections as $section ) : ?>
				<?php if ( '' !== $section['heading'] ) : ?>
					<div class="editorial-flow__block editorial-flow__block--heading">
						<?php echo $section['heading']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>

				<?php foreach ( $section['blocks'] as $block_html ) : ?>
					<div class="editorial-flow__block editorial-flow__block--body">
						<?php echo $block_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="editorial-flow<?php echo 'flow' === $layout ? ' editorial-flow--sequential' : ''; ?>">
			<?php foreach ( $sections as $section ) : ?>
				<section class="editorial-section">
					<?php if ( '' !== $section['heading'] ) : ?>
						<div class="editorial-section__heading<?php echo 'flow' === $layout ? ' editorial-section__heading--continuous' : ''; ?>">
							<?php echo $section['heading']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $section['blocks'] ) ) : ?>
						<?php if ( 'flow' === $layout ) : ?>
							<div class="editorial-section__columns editorial-section__columns--flow" data-editorial-columns>
								<?php foreach ( $section['blocks'] as $block_html ) : ?>
									<?php echo $block_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<div class="editorial-section__columns">
								<?php foreach ( $section['blocks'] as $block_html ) : ?>
									<div class="editorial-section__block">
										<?php echo $block_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php

	return trim( (string) ob_get_clean() );
}

/**
 * Render one continuous editorial flow for project bodies.
 *
 * This keeps the whole body content inside a single text stream so it can be
 * laid out like an article instead of a set of separate content groups.
 *
 * @param string $content Raw block content.
 * @return string
 */
function nunlab_render_project_editorial_content( $content ) {
	$content = trim( (string) $content );

	if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
		return '';
	}

	return nunlab_render_editorial_sections( $content, 'continuous' );
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

	return add_query_arg( 'ver', (string) filemtime( $asset_path ), NUNLAB_THEME_URI . '/' . $relative_path );
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
 * Return public external links for a plugin/tool entry.
 *
 * @param int $post_id Tool post ID.
 * @return array<int, array{label:string, url:string}>
 */
function nunlab_get_tool_links( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$fields  = array(
		'nunlab_tool_github_url'     => __( 'GitHub', 'nunlab-theme' ),
		'nunlab_tool_food4rhino_url' => __( 'Food4Rhino', 'nunlab-theme' ),
		'nunlab_tool_docs_url'       => __( 'Documentation', 'nunlab-theme' ),
		'nunlab_tool_release_url'    => __( 'Latest Release', 'nunlab-theme' ),
	);
	$links   = array();

	foreach ( $fields as $meta_key => $label ) {
		$url = esc_url_raw( (string) get_post_meta( $post_id, $meta_key, true ) );

		if ( '' === $url ) {
			continue;
		}

		$links[] = array(
			'label' => $label,
			'url'   => $url,
		);
	}

	return $links;
}

/**
 * Render chapter-style content for plugin/tool pages.
 *
 * Heading blocks start chapters. Text, screenshots, and other blocks that
 * follow the heading become that chapter's body.
 *
 * @param string $content Raw block content.
 * @return string
 */
function nunlab_render_tool_chapters( $content ) {
	$sections = nunlab_get_editorial_sections( $content );

	if ( empty( $sections ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="tool-chapters">
		<?php foreach ( $sections as $section ) : ?>
			<section class="tool-chapter">
				<?php if ( '' !== $section['heading'] ) : ?>
					<div class="tool-chapter__heading">
						<?php echo $section['heading']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $section['blocks'] ) ) : ?>
					<div class="tool-chapter__body">
						<?php foreach ( $section['blocks'] as $block_html ) : ?>
							<?php echo $block_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>
	<?php

	return trim( (string) ob_get_clean() );
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

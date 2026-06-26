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
 * Return public project metadata rows.
 *
 * @param int $post_id Project post ID.
 * @return array<int, array{label:string,value:string}>
 */
function nunlab_get_project_meta_items( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$value   = get_post_meta( $post_id, 'nunlab_project_meta_items', true );

	if ( ! is_array( $value ) ) {
		return array();
	}

	$items = array();

	foreach ( $value as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$label = isset( $item['label'] ) ? trim( (string) $item['label'] ) : '';
		$text  = isset( $item['value'] ) ? trim( (string) $item['value'] ) : '';

		if ( '' === $label || '' === $text ) {
			continue;
		}

		$items[] = array(
			'label' => $label,
			'value' => $text,
		);
	}

	return $items;
}

/**
 * Render project metadata as a compact definition list.
 *
 * @param int    $post_id     Project post ID.
 * @param string $extra_class Optional extra class for context-specific spacing.
 * @return string
 */
function nunlab_render_project_meta_list( $post_id = 0, $extra_class = '' ) {
	$items = nunlab_get_project_meta_items( $post_id );

	if ( empty( $items ) ) {
		return '';
	}

	$classes = trim( 'project-meta ' . (string) $extra_class );

	ob_start();
	?>
	<dl class="<?php echo esc_attr( $classes ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<div class="project-meta__item">
				<dt class="project-meta__label"><?php echo esc_html( $item['label'] ); ?></dt>
				<dd class="project-meta__value"><?php echo nl2br( esc_html( $item['value'] ) ); ?></dd>
			</div>
		<?php endforeach; ?>
	</dl>
	<?php

	return trim( (string) ob_get_clean() );
}

/**
 * Return an attachment media credit for project overlays.
 *
 * @param int $attachment_id Attachment ID.
 * @return string
 */
function nunlab_get_attachment_media_credit( $attachment_id ) {
	$attachment_id = absint( $attachment_id );

	if ( ! $attachment_id ) {
		return '';
	}

	return trim( wp_strip_all_tags( (string) get_post_meta( $attachment_id, 'nunlab_media_credit', true ) ) );
}

/**
 * Return compact public metadata for tool cards and pages.
 *
 * @param int  $post_id      Tool post ID.
 * @param bool $include_type Whether to include the "Tool" type label.
 * @return string[]
 */
function nunlab_get_tool_meta_parts( $post_id = 0, $include_type = true ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$parts   = array();

	if ( $include_type ) {
		$parts[] = __( 'Tool', 'nunlab-theme' );
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
			<?php $heading_count = 0; ?>
			<?php foreach ( $sections as $section ) : ?>
				<?php
				$heading               = isset( $section['heading'] ) ? (string) $section['heading'] : '';
				$blocks                = isset( $section['blocks'] ) && is_array( $section['blocks'] ) ? $section['blocks'] : array();
				$first_block           = null;
				$heading_tier          = '';
				$heading_group_classes = 'editorial-flow__group editorial-flow__group--heading';
				$heading_block_classes = 'editorial-flow__block editorial-flow__block--heading';

				if ( '' !== $heading ) {
					++$heading_count;
					$heading_tier          = $heading_count <= 2 ? 'lead' : 'compact';
					$heading_group_classes = trim( $heading_group_classes . ' editorial-flow__group--' . $heading_tier );
					$heading_block_classes = trim( $heading_block_classes . ' editorial-flow__block--heading-' . $heading_tier );
				}

				if ( '' !== $heading && ! empty( $blocks ) ) {
					$first_block = array_shift( $blocks );
				}
				?>
				<?php if ( '' !== $heading && null !== $first_block ) : ?>
					<div class="<?php echo esc_attr( $heading_group_classes ); ?>">
						<div class="<?php echo esc_attr( $heading_block_classes ); ?>">
							<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="editorial-flow__block editorial-flow__block--body">
							<?php echo $first_block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				<?php elseif ( '' !== $heading ) : ?>
					<div class="<?php echo esc_attr( $heading_block_classes ); ?>">
						<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>

				<?php foreach ( $blocks as $block_html ) : ?>
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
 * Parse a video timecode into seconds.
 *
 * @param string $timecode Timecode such as 0:16, 01:02:03.400, or 01:02:03,400.
 * @return float|null
 */
function nunlab_parse_video_timecode( $timecode ) {
	$timecode = trim( str_replace( ',', '.', (string) $timecode ) );
	$parts    = array_map( 'floatval', explode( ':', $timecode ) );

	if ( empty( $parts ) ) {
		return null;
	}

	foreach ( $parts as $part ) {
		if ( ! is_finite( $part ) ) {
			return null;
		}
	}

	if ( 3 === count( $parts ) ) {
		return ( $parts[0] * 3600 ) + ( $parts[1] * 60 ) + $parts[2];
	}

	if ( 2 === count( $parts ) ) {
		return ( $parts[0] * 60 ) + $parts[1];
	}

	return null;
}

/**
 * Parse timestamp/title lines into player chapter data.
 *
 * Supports both line-separated and compact YouTube-style chapter text, e.g.
 * "0:00 Introduction 0:16 Installation".
 *
 * @param string $text Raw chapter text.
 * @return array<int, array{start:float,title:string}>
 */
function nunlab_parse_video_chapters( $text ) {
	$text = trim( (string) $text );

	if ( '' === $text ) {
		return array();
	}

	if ( ! preg_match_all( '/(?<!\d)(\d{1,2}:(?:\d{2}:)?\d{2})(?![\d,.])/', $text, $matches, PREG_OFFSET_CAPTURE ) ) {
		return array();
	}

	$chapters = array();
	$count    = count( $matches[1] );

	for ( $index = 0; $index < $count; $index++ ) {
		$timecode         = $matches[1][ $index ][0];
		$title_start      = $matches[1][ $index ][1] + strlen( $timecode );
		$next_start       = isset( $matches[1][ $index + 1 ] ) ? $matches[1][ $index + 1 ][1] : strlen( $text );
		$title            = substr( $text, $title_start, $next_start - $title_start );
		$title            = trim( (string) preg_replace( '/^[\s\-–—|:]+/', '', $title ) );
		$timecode_seconds = nunlab_parse_video_timecode( $timecode );

		if ( '' === $title || null === $timecode_seconds ) {
			continue;
		}

		$chapters[] = array(
			'start' => $timecode_seconds,
			'title' => sanitize_text_field( $title ),
		);
	}

	usort(
		$chapters,
		static function ( $first, $second ) {
			return $first['start'] <=> $second['start'];
		}
	);

	return $chapters;
}

/**
 * Parse SRT or WebVTT caption text into player cue data.
 *
 * @param string $text Raw SRT/WebVTT text.
 * @return array<int, array{start:float,end:float,text:string}>
 */
function nunlab_parse_video_caption_cues( $text ) {
	$text = trim( str_replace( array( "\r\n", "\r" ), "\n", (string) $text ) );

	if ( '' === $text ) {
		return array();
	}

	$lines = explode( "\n", $text );
	$cues  = array();
	$index = 0;
	$count = count( $lines );

	while ( $index < $count ) {
		$line = trim( $lines[ $index ] );

		if ( '' === $line || 'WEBVTT' === strtoupper( $line ) || preg_match( '/^\d+$/', $line ) ) {
			++$index;
			continue;
		}

		if ( false === strpos( $line, '-->' ) ) {
			++$index;
			continue;
		}

		$time_parts = explode( '-->', $line, 2 );
		$start      = nunlab_parse_video_timecode( $time_parts[0] );
		$end_text   = trim( preg_split( '/\s+/', trim( $time_parts[1] ) )[0] ?? '' );
		$end        = nunlab_parse_video_timecode( $end_text );
		$cue_lines  = array();

		++$index;

		while ( $index < $count && '' !== trim( $lines[ $index ] ) ) {
			$cue_lines[] = sanitize_text_field( $lines[ $index ] );
			++$index;
		}

		if ( null === $start || null === $end || $end <= $start || empty( $cue_lines ) ) {
			continue;
		}

		$cues[] = array(
			'start' => $start,
			'end'   => $end,
			'text'  => implode( "\n", $cue_lines ),
		);
	}

	return $cues;
}

/**
 * Return public external links for a tool entry.
 *
 * @param int $post_id Tool post ID.
 * @return array<int, array{label:string, url:string}>
 */
function nunlab_get_tool_links( $post_id = 0 ) {
	$post_id         = $post_id ? (int) $post_id : get_the_ID();
	$walkthrough_url = esc_url_raw( (string) get_post_meta( $post_id, 'nunlab_tool_walkthrough_url', true ) );
	$fields          = array(
		'nunlab_tool_github_url'     => __( 'GitHub', 'nunlab-theme' ),
		'nunlab_tool_food4rhino_url' => __( 'Food4Rhino', 'nunlab-theme' ),
		'nunlab_tool_docs_url'       => __( 'Documentation', 'nunlab-theme' ),
		'nunlab_tool_release_url'    => __( 'Latest Release', 'nunlab-theme' ),
	);
	$links           = array();

	if ( '' !== $walkthrough_url ) {
		$links[] = array(
			'label' => __( 'YouTube', 'nunlab-theme' ),
			'url'   => $walkthrough_url,
		);
	}

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
 * Return overview blocks and chapter sections for tool pages.
 *
 * Authoring contract: blocks before the first Heading become the overview
 * intro; each Heading after that starts a jumpable chapter.
 *
 * @param string $content Raw block content.
 * @return array{overview_blocks:array<int, string>, chapters:array<int, array{id:string,title:string,nav_label:string,heading:string,blocks:array<int, string>}>}
 */
function nunlab_get_tool_content_sections( $content ) {
	$sections = nunlab_get_editorial_sections( $content );
	$result   = array(
		'overview_blocks' => array(),
		'chapters'        => array(),
	);

	if ( empty( $sections ) ) {
		return $result;
	}

	$used_ids = array();

	foreach ( $sections as $section ) {
		$heading = isset( $section['heading'] ) ? (string) $section['heading'] : '';
		$blocks  = isset( $section['blocks'] ) && is_array( $section['blocks'] ) ? $section['blocks'] : array();

		if ( '' === trim( $heading ) ) {
			$result['overview_blocks'] = array_merge( $result['overview_blocks'], $blocks );
			continue;
		}

		$title     = trim( wp_strip_all_tags( $heading ) );
		$nav_label = trim( (string) preg_replace( '/^\d+[\).\s-]+/', '', $title ) );
		$nav_label = '' !== $nav_label ? $nav_label : $title;
		$base_id   = sanitize_title( '' !== $nav_label ? $nav_label : (string) ( count( $result['chapters'] ) + 1 ) );
		$base_id   = 'chapter-' . ( '' !== $base_id ? $base_id : ( count( $result['chapters'] ) + 1 ) );
		$id        = $base_id;
		$suffix    = 2;

		while ( in_array( $id, $used_ids, true ) ) {
			$id = $base_id . '-' . $suffix;
			++$suffix;
		}

		$used_ids[] = $id;

		$result['chapters'][] = array(
			'id'        => $id,
			'title'     => $title,
			'nav_label' => $nav_label,
			'heading'   => $heading,
			'blocks'    => $blocks,
		);
	}

	return $result;
}

/**
 * Render the generated overview card for a tool page.
 *
 * @param array $tool_sections Sections from nunlab_get_tool_content_sections().
 * @return string
 */
function nunlab_render_tool_overview( $tool_sections ) {
	$overview_blocks = isset( $tool_sections['overview_blocks'] ) && is_array( $tool_sections['overview_blocks'] ) ? $tool_sections['overview_blocks'] : array();
	$chapters        = isset( $tool_sections['chapters'] ) && is_array( $tool_sections['chapters'] ) ? $tool_sections['chapters'] : array();

	if ( empty( $overview_blocks ) && empty( $chapters ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="tool-overview" aria-labelledby="tool-overview-title">
		<div class="tool-overview__index">
			<h2 id="tool-overview-title" class="tool-overview__title"><?php esc_html_e( 'Overview', 'nunlab-theme' ); ?></h2>

			<?php if ( $chapters ) : ?>
				<ol class="tool-overview__list">
					<?php foreach ( $chapters as $chapter ) : ?>
						<li>
							<a href="#<?php echo esc_attr( $chapter['id'] ); ?>">
								<?php echo esc_html( $chapter['nav_label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</div>

		<?php if ( $overview_blocks ) : ?>
			<div class="tool-overview__body">
				<?php foreach ( $overview_blocks as $block_html ) : ?>
					<?php echo $block_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php

	return trim( (string) ob_get_clean() );
}

/**
 * Render chapter-style content for tool pages.
 *
 * @param array $tool_sections Sections from nunlab_get_tool_content_sections().
 * @return string
 */
function nunlab_render_tool_chapters( $tool_sections ) {
	$chapters = isset( $tool_sections['chapters'] ) && is_array( $tool_sections['chapters'] ) ? $tool_sections['chapters'] : array();

	if ( empty( $chapters ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="tool-chapters">
		<?php foreach ( $chapters as $chapter ) : ?>
			<section id="<?php echo esc_attr( $chapter['id'] ); ?>" class="tool-chapter">
				<div class="tool-chapter__heading">
					<?php echo $chapter['heading']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<?php if ( ! empty( $chapter['blocks'] ) ) : ?>
					<div class="tool-chapter__body">
						<?php foreach ( $chapter['blocks'] as $block_html ) : ?>
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

				continue;
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

				continue;
			}

			if ( 'video' === $item['type'] ) {
				$video_id = isset( $item['video_id'] ) ? absint( $item['video_id'] ) : 0;

				if ( ! $video_id ) {
					continue;
				}

				$items[] = array(
					'type'      => 'video',
					'video_id'  => $video_id,
					'poster_id' => isset( $item['poster_id'] ) ? absint( $item['poster_id'] ) : 0,
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
			$caption   = $image_id ? wp_strip_all_tags( (string) wp_get_attachment_caption( $image_id ) ) : '';
			$credit    = $image_id ? nunlab_get_attachment_media_credit( $image_id ) : '';

			if ( ! $image_url ) {
				continue;
			}

			$media[] = array(
				'type'               => 'image',
				'id'                 => $image_id,
				'url'                => $image_url,
				'poster_url'         => $image_url,
				'caption'            => $caption,
				'credit'             => $credit,
				'alt'                => $image_alt ? $image_alt : sprintf(
					/* translators: %d is the image position. */
					__( 'Project media image %d', 'nunlab-theme' ),
					$index + 1
				),
				'embed_url'         => '',
				'autoplay_embed_url' => '',
				'video_url'         => '',
				'mime_type'         => '',
			);

			continue;
		}

		if ( 'video' === $item['type'] ) {
			$video_id  = isset( $item['video_id'] ) ? absint( $item['video_id'] ) : 0;
			$video_url = $video_id ? wp_get_attachment_url( $video_id ) : '';
			$caption   = $video_id ? wp_strip_all_tags( (string) wp_get_attachment_caption( $video_id ) ) : '';
			$credit    = $video_id ? nunlab_get_attachment_media_credit( $video_id ) : '';

			if ( ! $video_url ) {
				continue;
			}

			$poster_id  = isset( $item['poster_id'] ) ? absint( $item['poster_id'] ) : 0;
			$poster_url = $poster_id ? wp_get_attachment_image_url( $poster_id, $size ) : '';
			$poster_alt = $poster_id ? (string) get_post_meta( $poster_id, '_wp_attachment_image_alt', true ) : get_the_title( $video_id );
			$credit     = '' !== $credit || ! $poster_id ? $credit : nunlab_get_attachment_media_credit( $poster_id );

			$media[] = array(
				'type'               => 'video',
				'id'                 => $video_id,
				'url'                => $poster_url,
				'poster_url'         => $poster_url,
				'caption'            => $caption,
				'credit'             => $credit,
				'alt'                => $poster_alt ? $poster_alt : get_the_title( $post_id ),
				'embed_url'          => '',
				'autoplay_embed_url' => '',
				'video_url'          => $video_url,
				'mime_type'          => (string) get_post_mime_type( $video_id ),
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
		$caption    = $poster_id ? wp_strip_all_tags( (string) wp_get_attachment_caption( $poster_id ) ) : '';
		$credit     = $poster_id ? nunlab_get_attachment_media_credit( $poster_id ) : '';

		$media[] = array(
			'type'               => 'youtube',
			'id'                 => $poster_id,
			'url'                => $poster_url,
			'poster_url'         => $poster_url,
			'caption'            => $caption,
			'credit'             => $credit,
			'alt'                => $poster_alt,
			'embed_url'          => nunlab_get_youtube_embed_url( $video_id, false ),
			'autoplay_embed_url' => nunlab_get_youtube_embed_url( $video_id, true ),
			'video_url'          => '',
			'mime_type'          => '',
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
	$post_id  = $post_id ? (int) $post_id : get_the_ID();
	$image_id = get_post_thumbnail_id( $post_id );

	if ( $image_id ) {
		return array(
			'id'     => $image_id,
			'url'    => (string) wp_get_attachment_image_url( $image_id, $size ),
			'alt'    => (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
			'credit' => nunlab_get_attachment_media_credit( $image_id ),
		);
	}

	$media_items = nunlab_get_project_media_items( $post_id, $size );

	if ( $media_items ) {
		return array(
			'id'     => isset( $media_items[0]['id'] ) ? (int) $media_items[0]['id'] : 0,
			'url'    => isset( $media_items[0]['poster_url'] ) ? (string) $media_items[0]['poster_url'] : '',
			'alt'    => isset( $media_items[0]['alt'] ) ? (string) $media_items[0]['alt'] : '',
			'credit' => isset( $media_items[0]['credit'] ) ? (string) $media_items[0]['credit'] : '',
		);
	}

	return array(
		'id'     => 0,
		'url'    => '',
		'alt'    => '',
		'credit' => '',
	);
}

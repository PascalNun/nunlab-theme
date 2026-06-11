<?php
/**
 * Theme Hooks and Filters
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom body classes.
 */
function nunlab_body_classes( $classes ) {
	// Only the actual front page should trigger the homepage hero/header behavior.
	if ( is_front_page() ) {
		$classes[] = 'is-homepage';
	}

	if ( is_home() && ! is_front_page() ) {
		$classes[] = 'is-posts-index';
	}

	// Add class for single posts/pages.
	if ( is_singular() ) {
		$classes[] = 'is-singular';
	}

	return $classes;
}
add_filter( 'body_class', 'nunlab_body_classes' );

/**
 * Point selected primary menu items to homepage anchors.
 *
 * @param WP_Post[] $items Menu items.
 * @param stdClass  $args  Menu arguments.
 * @return WP_Post[]
 */
function nunlab_primary_menu_section_anchors( $items, $args ) {
	if ( ! isset( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $items;
	}

	$about_page     = nunlab_get_front_page_source_page( 'nunlab_about_page_id', 'about' );
	$manifesto_page = nunlab_get_front_page_source_page( 'nunlab_manifesto_page_id', 'manifesto' );
	$anchors        = array(
		'work'  => array(
			'url'          => get_post_type_archive_link( 'project' ),
			'anchor_front' => '#work',
			'anchor_site'  => home_url( '/#work' ),
		),
		'about' => array(
			'url'          => ( $about_page instanceof WP_Post ) ? get_permalink( $about_page ) : '',
			'anchor_front' => '#about',
			'anchor_site'  => home_url( '/#about' ),
		),
		'manifesto' => array(
			'url'          => ( $manifesto_page instanceof WP_Post ) ? get_permalink( $manifesto_page ) : '',
			'anchor_front' => '#manifesto',
			'anchor_site'  => home_url( '/#manifesto' ),
		),
	);

	foreach ( $items as $item ) {
		$item_title = strtolower( trim( wp_strip_all_tags( $item->title ) ) );

		foreach ( $anchors as $slug => $anchor ) {
			$matches_anchor = $slug === $item_title;
			$matches_url    = ! empty( $anchor['url'] ) && untrailingslashit( $item->url ) === untrailingslashit( $anchor['url'] );

			if ( ! $matches_anchor && ! $matches_url ) {
				continue;
			}

			$item->url = is_front_page() ? $anchor['anchor_front'] : $anchor['anchor_site'];
			break;
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'nunlab_primary_menu_section_anchors', 10, 2 );

/**
 * Keep search focused on the main public content types.
 *
 * @param WP_Query $query Main query instance.
 */
function nunlab_tune_search_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 'post_type', array( 'post', 'page', 'project', 'tool' ) );
}
add_action( 'pre_get_posts', 'nunlab_tune_search_queries' );

/**
 * Use native emoji rendering and skip WordPress' visitor-side emoji helper.
 */
function nunlab_disable_front_end_emoji_assets() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'nunlab_disable_front_end_emoji_assets' );

/**
 * Raise WordPress' "big image" threshold so uploads are not scaled down as
 * aggressively at 2560px.
 *
 * @return int
 */
function nunlab_big_image_size_threshold() {
	return 4096;
}
add_filter( 'big_image_size_threshold', 'nunlab_big_image_size_threshold' );

/**
 * Keep generated image derivatives high enough quality for project media.
 *
 * Existing uploads need regenerated thumbnails before this affects their
 * already-created intermediate sizes.
 *
 * @return int
 */
function nunlab_image_editor_quality() {
	return 92;
}
add_filter( 'jpeg_quality', 'nunlab_image_editor_quality' );
add_filter( 'wp_editor_set_quality', 'nunlab_image_editor_quality' );

/**
 * Output theme favicon assets when no WordPress site icon is set.
 *
 * SVG is the preferred browser icon. PNG stays as the fallback for browsers
 * and contexts that still expect a raster icon.
 */
function nunlab_output_theme_favicons() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$favicon_svg_path = NUNLAB_THEME_DIR . '/assets/images/brand/favicon.svg';
	$favicon_svg_uri  = NUNLAB_THEME_URI . '/assets/images/brand/favicon.svg';
	$favicon_png_path = NUNLAB_THEME_DIR . '/assets/images/brand/favicon-512.png';
	$favicon_png_uri  = NUNLAB_THEME_URI . '/assets/images/brand/favicon-512.png';

	$has_svg = file_exists( $favicon_svg_path );
	$has_png = file_exists( $favicon_png_path );

	if ( ! $has_svg && ! $has_png ) {
		return;
	}

	if ( $has_svg ) :
		?>
		<link rel="icon" href="<?php echo esc_url( $favicon_svg_uri ); ?>" type="image/svg+xml" sizes="any" />
		<?php
	endif;

	if ( $has_png ) :
		?>
		<link rel="alternate icon" href="<?php echo esc_url( $favicon_png_uri ); ?>" type="image/png" sizes="512x512" />
		<link rel="apple-touch-icon" href="<?php echo esc_url( $favicon_png_uri ); ?>" />
		<?php
	endif;
}
add_action( 'wp_head', 'nunlab_output_theme_favicons', 1 );

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
	// Add class if using homepage template.
	if ( is_home() || is_front_page() ) {
		$classes[] = 'is-homepage';
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

	$front_page_id = (int) get_option( 'page_on_front' );
	$about_page_id = $front_page_id ? (int) get_post_meta( $front_page_id, 'nunlab_about_page_id', true ) : 0;
	$about_page    = $about_page_id ? get_post( $about_page_id ) : get_page_by_path( 'about' );
	$anchors       = array(
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

	$query->set( 'post_type', array( 'post', 'page', 'project' ) );
}
add_action( 'pre_get_posts', 'nunlab_tune_search_queries' );

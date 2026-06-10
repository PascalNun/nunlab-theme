<?php
declare(strict_types=1);

require getcwd() . '/wp-load.php';

if ( ! function_exists( 'switch_theme' ) ) {
	require ABSPATH . 'wp-includes/theme.php';
}

if ( ! function_exists( 'wp_create_nav_menu' ) ) {
	require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
}

$theme_stylesheet = 'nunlab-theme';
$theme            = wp_get_theme( $theme_stylesheet );

if ( ! $theme->exists() ) {
	fwrite( STDERR, "Theme 'nunlab-theme' is not available.\n" );
	exit( 1 );
}

switch_theme( $theme_stylesheet );
update_option( 'blogname', 'N:UN' );

/**
 * Ensure a published page exists for the requested slug.
 *
 * @param string $title Page title.
 * @param string $slug  Page slug.
 * @return int
 */
function nunlab_ensure_page( string $title, string $slug ): int {
	$page = get_page_by_path( $slug );

	if ( $page instanceof WP_Post ) {
		if ( 'publish' !== $page->post_status ) {
			wp_update_post(
				array(
					'ID'          => $page->ID,
					'post_status' => 'publish',
				)
			);
		}

		return (int) $page->ID;
	}

	return (int) wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => '',
			'post_excerpt' => '',
		)
	);
}

/**
 * Trash default sample content when it still exists.
 */
function nunlab_trash_default_content(): void {
	$hello_world = get_page_by_path( 'hello-world', OBJECT, 'post' );
	$sample_page = get_page_by_path( 'sample-page', OBJECT, 'page' );

	if ( $hello_world instanceof WP_Post ) {
		wp_trash_post( $hello_world->ID );
	}

	if ( $sample_page instanceof WP_Post ) {
		wp_trash_post( $sample_page->ID );
	}
}

/**
 * Ensure a menu item exists on the target menu.
 *
 * @param int   $menu_id   Menu ID.
 * @param array $item_args Menu item args.
 */
function nunlab_ensure_menu_item( int $menu_id, array $item_args ): void {
	$existing_items = wp_get_nav_menu_items( $menu_id );
	$object_id      = isset( $item_args['menu-item-object-id'] ) ? (string) $item_args['menu-item-object-id'] : '';
	$title          = isset( $item_args['menu-item-title'] ) ? (string) $item_args['menu-item-title'] : '';
	$url            = isset( $item_args['menu-item-url'] ) ? (string) $item_args['menu-item-url'] : '';

	if ( is_array( $existing_items ) ) {
		foreach ( $existing_items as $existing_item ) {
			$matches_object = $object_id && (string) $existing_item->object_id === $object_id;
			$matches_custom = 'custom' === $existing_item->type && $title === $existing_item->title && $url === $existing_item->url;

			if ( $matches_object || $matches_custom ) {
				return;
			}
		}
	}

	wp_update_nav_menu_item( $menu_id, 0, $item_args );
}

$page_ids = array(
	'home'      => nunlab_ensure_page( 'Home', 'home' ),
	'about'     => nunlab_ensure_page( 'About', 'about' ),
	'manifesto' => nunlab_ensure_page( 'Manifesto', 'manifesto' ),
	'notebook'  => nunlab_ensure_page( 'Notebook', 'notebook' ),
	'plugins'   => nunlab_ensure_page( 'Plugins', 'plugins' ),
	'contact'   => nunlab_ensure_page( 'Contact', 'contact' ),
	'legal'     => nunlab_ensure_page( 'Legal Notice', 'legal-notice' ),
);

nunlab_trash_default_content();

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $page_ids['home'] );
update_option( 'page_for_posts', $page_ids['notebook'] );

update_post_meta( $page_ids['home'], 'nunlab_about_page_id', $page_ids['about'] );
update_post_meta( $page_ids['home'], 'nunlab_hero_title', 'Architecture, design, research, tools, and making.' );
update_post_meta( $page_ids['home'], 'nunlab_hero_intro', 'A curated body of work, ideas, and spatial thinking shaped through architecture, design, research, and making.' );
update_post_meta( $page_ids['home'], 'nunlab_work_eyebrow', 'Work' );
update_post_meta( $page_ids['home'], 'nunlab_work_heading', 'A structured index of selected work, research, and ideas.' );

$menu_name  = 'Primary Navigation';
$menu       = wp_get_nav_menu_object( $menu_name );
$menu_id    = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );
$legal_name = 'Legal Footer Navigation';
$legal_menu = wp_get_nav_menu_object( $legal_name );
$legal_id   = $legal_menu ? (int) $legal_menu->term_id : (int) wp_create_nav_menu( $legal_name );

nunlab_ensure_menu_item(
	$menu_id,
	array(
		'menu-item-title'     => 'Home',
		'menu-item-object-id' => $page_ids['home'],
		'menu-item-object'    => 'page',
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
	)
);

nunlab_ensure_menu_item(
	$menu_id,
	array(
		'menu-item-title'  => 'Work',
		'menu-item-url'    => home_url( '/#work' ),
		'menu-item-type'   => 'custom',
		'menu-item-status' => 'publish',
	)
);

foreach ( array( 'about', 'notebook', 'plugins', 'contact' ) as $slug ) {
	nunlab_ensure_menu_item(
		$menu_id,
		array(
			'menu-item-title'     => ucfirst( $slug ),
			'menu-item-object-id' => $page_ids[ $slug ],
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
}

$locations            = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = $menu_id;
$locations['legal']   = $legal_id;
set_theme_mod( 'nav_menu_locations', $locations );

nunlab_ensure_menu_item(
	$legal_id,
	array(
		'menu-item-title'     => 'Legal Notice',
		'menu-item-object-id' => $page_ids['legal'],
		'menu-item-object'    => 'page',
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
	)
);

echo wp_json_encode(
	array(
		'theme'        => $theme_stylesheet,
		'front_page'   => $page_ids['home'],
		'posts_page'   => $page_ids['notebook'],
		'about_page'   => $page_ids['about'],
		'manifesto'    => $page_ids['manifesto'],
		'plugins_page' => $page_ids['plugins'],
		'contact_page' => $page_ids['contact'],
		'menu_id'      => $menu_id,
		'legal_menu_id' => $legal_id,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . PHP_EOL;

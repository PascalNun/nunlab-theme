<?php
/**
 * Theme Setup and Registration
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set up theme defaults and register support for various WordPress features.
 */
function nunlab_setup() {
	// Enable theme support for title tag.
	add_theme_support( 'title-tag' );

	// Enable theme support for post thumbnails.
	add_theme_support( 'post-thumbnails' );

	// Enable theme support for a custom logo.
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Enable HTML5 support for various features.
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Register navigation menus.
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'nunlab-theme' ),
			'footer'  => esc_html__( 'Footer Menu', 'nunlab-theme' ),
		)
	);

	// Load text domain for translations.
	load_theme_textdomain( 'nunlab-theme', NUNLAB_THEME_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'nunlab_setup' );

/**
 * Set content width.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1200;
}

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
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/admin-editor.css' );

	// Larger generated size for project galleries and expanded project cards.
	add_image_size( 'nunlab-project-large', 2400, 0, false );

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

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

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'nunlab-theme' ),
			'legal'   => esc_html__( 'Legal Footer Menu', 'nunlab-theme' ),
		)
	);

	load_theme_textdomain( 'nunlab-theme', NUNLAB_THEME_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'nunlab_setup' );

/**
 * Set content width.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1200;
}

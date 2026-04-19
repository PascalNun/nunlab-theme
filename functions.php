<?php
/**
 * N:UN Lab Theme Functions
 *
 * @package nunlab-theme
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define theme constants.
define( 'NUNLAB_THEME_DIR', get_template_directory() );
define( 'NUNLAB_THEME_URI', get_template_directory_uri() );
define( 'NUNLAB_THEME_VERSION', wp_get_theme()->get( 'Version' ) );

// Load theme files.
require_once NUNLAB_THEME_DIR . '/inc/theme-setup.php';
require_once NUNLAB_THEME_DIR . '/inc/enqueue.php';
require_once NUNLAB_THEME_DIR . '/inc/post-types.php';
require_once NUNLAB_THEME_DIR . '/inc/template-tags.php';
require_once NUNLAB_THEME_DIR . '/inc/meta-boxes.php';
require_once NUNLAB_THEME_DIR . '/inc/hooks.php';

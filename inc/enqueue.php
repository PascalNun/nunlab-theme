<?php
/**
 * Theme Assets Enqueuing
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue theme stylesheets and scripts.
 */
function nunlab_enqueue_assets() {
	$style_path          = NUNLAB_THEME_DIR . '/assets/css/style.css';
	$script_path         = NUNLAB_THEME_DIR . '/assets/js/theme.js';
	$style_version       = file_exists( $style_path ) ? (string) filemtime( $style_path ) : NUNLAB_THEME_VERSION;
	$script_version      = file_exists( $script_path ) ? (string) filemtime( $script_path ) : NUNLAB_THEME_VERSION;
	$front_script_path   = NUNLAB_THEME_DIR . '/assets/js/front-page.js';
	$project_script_path = NUNLAB_THEME_DIR . '/assets/js/project-gallery.js';
	$tool_script_path    = NUNLAB_THEME_DIR . '/assets/js/tool-content.js';

	// Main theme stylesheet.
	wp_enqueue_style(
		'nunlab-theme-style',
		NUNLAB_THEME_URI . '/assets/css/style.css',
		array(),
		$style_version,
		'all'
	);

	// Core interactions shared by every visitor-facing page.
	wp_enqueue_script(
		'nunlab-theme-script',
		NUNLAB_THEME_URI . '/assets/js/theme.js',
		array(),
		$script_version,
		true
	);

	if ( is_front_page() && file_exists( $front_script_path ) ) {
		wp_enqueue_script(
			'nunlab-front-page-script',
			NUNLAB_THEME_URI . '/assets/js/front-page.js',
			array( 'nunlab-theme-script' ),
			(string) filemtime( $front_script_path ),
			true
		);
	}

	if ( is_singular( 'project' ) && file_exists( $project_script_path ) ) {
		wp_enqueue_script(
			'nunlab-project-gallery-script',
			NUNLAB_THEME_URI . '/assets/js/project-gallery.js',
			array( 'nunlab-theme-script' ),
			(string) filemtime( $project_script_path ),
			true
		);
	}

	if ( is_singular( 'tool' ) && file_exists( $tool_script_path ) ) {
		wp_enqueue_script(
			'nunlab-tool-content-script',
			NUNLAB_THEME_URI . '/assets/js/tool-content.js',
			array( 'nunlab-theme-script' ),
			(string) filemtime( $tool_script_path ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'nunlab_enqueue_assets' );

/**
 * Enqueue admin assets for theme editing workflows.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function nunlab_enqueue_admin_assets( $hook_suffix ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$style_path    = NUNLAB_THEME_DIR . '/assets/css/admin-editor.css';
	$style_version = file_exists( $style_path ) ? (string) filemtime( $style_path ) : NUNLAB_THEME_VERSION;

	if ( in_array( $screen->post_type, array( 'project', 'page', 'tool' ), true ) ) {
		wp_enqueue_style(
			'nunlab-admin-editor-style',
			NUNLAB_THEME_URI . '/assets/css/admin-editor.css',
			array(),
			$style_version
		);
	}

	if ( 'project' !== $screen->post_type ) {
		return;
	}

	$script_path    = NUNLAB_THEME_DIR . '/assets/js/admin-project-gallery.js';
	$script_version = file_exists( $script_path ) ? (string) filemtime( $script_path ) : NUNLAB_THEME_VERSION;

	wp_enqueue_media();
	wp_enqueue_script(
		'nunlab-admin-project-gallery',
		NUNLAB_THEME_URI . '/assets/js/admin-project-gallery.js',
		array( 'media-editor' ),
		$script_version,
		true
	);

	wp_localize_script(
		'nunlab-admin-project-gallery',
		'nunlabProjectMedia',
		array(
			'addImageTitle'        => __( 'Select project images', 'nunlab-theme' ),
			'addImageButton'       => __( 'Use selected images', 'nunlab-theme' ),
			'replaceImageTitle'    => __( 'Select an image', 'nunlab-theme' ),
			'replaceImageButton'   => __( 'Use this image', 'nunlab-theme' ),
			'selectPosterTitle'    => __( 'Select a poster image', 'nunlab-theme' ),
			'selectPosterButton'   => __( 'Use poster image', 'nunlab-theme' ),
			'emptyLabel'           => __( 'No media items added yet.', 'nunlab-theme' ),
			'imageTypeLabel'       => __( 'Image slide', 'nunlab-theme' ),
			'youtubeTypeLabel'     => __( 'YouTube slide', 'nunlab-theme' ),
			'youtubePlaceholder'   => __( 'Paste a YouTube URL', 'nunlab-theme' ),
			'chooseImageLabel'     => __( 'Choose image', 'nunlab-theme' ),
			'replaceImageLabel'    => __( 'Replace image', 'nunlab-theme' ),
			'choosePosterLabel'    => __( 'Choose poster', 'nunlab-theme' ),
			'clearPosterLabel'     => __( 'Clear poster', 'nunlab-theme' ),
			'moveUpLabel'          => __( 'Move up', 'nunlab-theme' ),
			'moveDownLabel'        => __( 'Move down', 'nunlab-theme' ),
			'removeLabel'          => __( 'Remove', 'nunlab-theme' ),
			'externalImageLabel'   => __( 'External image', 'nunlab-theme' ),
			'generatedPosterLabel' => __( 'Using YouTube thumbnail', 'nunlab-theme' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'nunlab_enqueue_admin_assets' );

<?php
/**
 * Custom Post Type Registration
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom post types used by the theme.
 */
function nunlab_register_post_types() {
	register_post_type(
		'project',
		array(
			'labels'       => array(
				'name'               => esc_html__( 'Projects', 'nunlab-theme' ),
				'singular_name'      => esc_html__( 'Project', 'nunlab-theme' ),
				'add_new'            => esc_html__( 'Add Project', 'nunlab-theme' ),
				'add_new_item'       => esc_html__( 'Add New Project', 'nunlab-theme' ),
				'edit_item'          => esc_html__( 'Edit Project', 'nunlab-theme' ),
				'new_item'           => esc_html__( 'New Project', 'nunlab-theme' ),
				'view_item'          => esc_html__( 'View Project', 'nunlab-theme' ),
				'view_items'         => esc_html__( 'View Projects', 'nunlab-theme' ),
				'search_items'       => esc_html__( 'Search Projects', 'nunlab-theme' ),
				'not_found'          => esc_html__( 'No projects found.', 'nunlab-theme' ),
				'not_found_in_trash' => esc_html__( 'No projects found in Trash.', 'nunlab-theme' ),
				'all_items'          => esc_html__( 'Projects', 'nunlab-theme' ),
				'archives'           => esc_html__( 'Project Archives', 'nunlab-theme' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'menu_icon'    => 'dashicons-portfolio',
			'rewrite'      => array(
				'slug'       => 'projects',
				'with_front' => false,
			),
			'show_in_rest' => true,
			'supports'     => array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'page-attributes',
				'revisions',
			),
		)
	);

	register_post_type(
		'tool',
		array(
			'labels'       => array(
				'name'               => esc_html__( 'Plugins', 'nunlab-theme' ),
				'singular_name'      => esc_html__( 'Plugin', 'nunlab-theme' ),
				'add_new'            => esc_html__( 'Add Plugin', 'nunlab-theme' ),
				'add_new_item'       => esc_html__( 'Add New Plugin', 'nunlab-theme' ),
				'edit_item'          => esc_html__( 'Edit Plugin', 'nunlab-theme' ),
				'new_item'           => esc_html__( 'New Plugin', 'nunlab-theme' ),
				'view_item'          => esc_html__( 'View Plugin', 'nunlab-theme' ),
				'view_items'         => esc_html__( 'View Plugins', 'nunlab-theme' ),
				'search_items'       => esc_html__( 'Search Plugins', 'nunlab-theme' ),
				'not_found'          => esc_html__( 'No plugins found.', 'nunlab-theme' ),
				'not_found_in_trash' => esc_html__( 'No plugins found in Trash.', 'nunlab-theme' ),
				'all_items'          => esc_html__( 'Plugins', 'nunlab-theme' ),
				'archives'           => esc_html__( 'Plugin Archives', 'nunlab-theme' ),
				'menu_name'          => esc_html__( 'Site Plugins', 'nunlab-theme' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-admin-plugins',
			'rewrite'      => array(
				'slug'       => 'plugins',
				'with_front' => false,
			),
			'show_in_rest' => true,
			'supports'     => array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'page-attributes',
				'revisions',
			),
		)
	);

	register_taxonomy(
		'project_type',
		array( 'project' ),
		array(
			'labels'            => array(
				'name'          => esc_html__( 'Project Types', 'nunlab-theme' ),
				'singular_name' => esc_html__( 'Project Type', 'nunlab-theme' ),
				'search_items'  => esc_html__( 'Search Project Types', 'nunlab-theme' ),
				'all_items'     => esc_html__( 'All Project Types', 'nunlab-theme' ),
				'edit_item'     => esc_html__( 'Edit Project Type', 'nunlab-theme' ),
				'update_item'   => esc_html__( 'Update Project Type', 'nunlab-theme' ),
				'add_new_item'  => esc_html__( 'Add New Project Type', 'nunlab-theme' ),
				'new_item_name' => esc_html__( 'New Project Type Name', 'nunlab-theme' ),
				'menu_name'     => esc_html__( 'Project Types', 'nunlab-theme' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'work-type',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'nunlab_register_post_types' );

/**
 * Seed the default project archive section terms.
 */
function nunlab_seed_project_type_terms() {
	$project_types = array(
		'concept' => esc_html__( 'Concept', 'nunlab-theme' ),
		'work'    => esc_html__( 'Work', 'nunlab-theme' ),
		'research' => esc_html__( 'Research', 'nunlab-theme' ),
		'build'   => esc_html__( 'Build', 'nunlab-theme' ),
	);

	foreach ( $project_types as $slug => $name ) {
		if ( ! term_exists( $slug, 'project_type' ) ) {
			wp_insert_term(
				$name,
				'project_type',
				array(
					'slug' => $slug,
				)
			);
		}
	}
}
add_action( 'init', 'nunlab_seed_project_type_terms', 20 );

/**
 * Flush rewrite rules once when the theme route schema changes.
 */
function nunlab_maybe_flush_rewrite_rules() {
	$rewrite_schema_version = '2026-06-10-tools';

	if ( get_option( 'nunlab_rewrite_schema_version' ) === $rewrite_schema_version ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'nunlab_rewrite_schema_version', $rewrite_schema_version );
}
add_action( 'init', 'nunlab_maybe_flush_rewrite_rules', 99 );

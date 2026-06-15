<?php
/**
 * Seed local example projects for the N:UN theme.
 *
 * Run with:
 * wp --path=.wp-local eval-file scripts/seed-local-projects.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$projects = array(
	array(
		'slug'         => 'urban-hubs',
		'title'        => 'Urban Hubs',
		'project_type' => 'concept',
		'menu_order'   => 10,
		'excerpt'      => 'A speculative academic proposal for lightweight mobility hubs along Stuttgart\'s river edge, combining modular textile structures, public infrastructure, and adaptable urban programs.',
		'content'      => '<p>Urban Hubs explores how temporary architecture can support new forms of urban mobility without relying on heavy permanent interventions. The proposal combines modular structures, lightweight envelopes, and public interfaces that can grow or contract with seasonal use.</p><p>The case study follows the project from initial spatial research through material testing, site-based scenarios, and a broader reflection on adaptable infrastructure in transitional urban contexts.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/urban-hubs-1/1000/1400',
			'https://picsum.photos/seed/urban-hubs-2/1000/1400',
			'https://picsum.photos/seed/urban-hubs-3/1000/1400',
		),
		'media'        => array(
			array(
				'type'      => 'image',
				'image_url' => 'https://picsum.photos/seed/urban-hubs-1/1400/1800',
			),
			array(
				'type'        => 'youtube',
				'youtube_url' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE',
			),
			array(
				'type'      => 'image',
				'image_url' => 'https://picsum.photos/seed/urban-hubs-3/1400/1800',
			),
		),
	),
	array(
		'slug'         => 'river-campus-commons',
		'title'        => 'River Campus Commons',
		'project_type' => 'concept',
		'menu_order'   => 20,
		'excerpt'      => 'An academic framework for shared learning spaces that sit between campus, city, and landscape, with a focus on porous thresholds, public use, and seasonal flexibility.',
		'content'      => '<p>River Campus Commons studies the edge condition between institutional space and the public city. The proposal develops a sequence of open structures, shaded platforms, and semi-enclosed rooms that can host teaching, gathering, and public events.</p><p>Rather than a single building, the project is organized as a family of connected spatial situations that can be phased over time.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/river-campus-commons-1/1000/1400',
			'https://picsum.photos/seed/river-campus-commons-2/1000/1400',
			'https://picsum.photos/seed/river-campus-commons-3/1000/1400',
		),
	),
	array(
		'slug'         => 'climate-corridor',
		'title'        => 'Climate Corridor',
		'project_type' => 'concept',
		'menu_order'   => 25,
		'excerpt'      => 'A design concept for linking shaded pedestrian routes, small climate infrastructures, and public pause points into one continuous urban sequence.',
		'content'      => '<p>Climate Corridor imagines a connected public route that responds to heat, exposure, and interrupted walkability. The project combines micro-infrastructure, planting systems, and repeated spatial markers that make movement legible and comfortable.</p><p>The proposal is less about one object and more about a distributed framework that could be implemented in phases.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/climate-corridor-1/1000/1400',
			'https://picsum.photos/seed/climate-corridor-2/1000/1400',
			'https://picsum.photos/seed/climate-corridor-3/1000/1400',
		),
	),
	array(
		'slug'         => 'material-atlas-studio',
		'title'        => 'Material Atlas Studio',
		'project_type' => 'concept',
		'menu_order'   => 28,
		'excerpt'      => 'A speculative studio project testing how digital material mapping can inform spatial atmospheres, assembly logic, and adaptive reuse decisions.',
		'content'      => '<p>Material Atlas Studio explores the overlap between cataloging and making. The work assembles visual samples, assembly tests, and spatial scenarios into one study of how material information can shape form.</p><p>It functions as both a design project and a methodological experiment.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/material-atlas-studio-1/1000/1400',
			'https://picsum.photos/seed/material-atlas-studio-2/1000/1400',
			'https://picsum.photos/seed/material-atlas-studio-3/1000/1400',
		),
	),
	array(
		'slug'         => 'courtyard-retrofit',
		'title'        => 'Courtyard Retrofit',
		'project_type' => 'work',
		'menu_order'   => 30,
		'excerpt'      => 'A built-environment study for reworking an underused courtyard into a calm social interior through shade, seating, planting, and a clearer spatial rhythm.',
		'content'      => '<p>Courtyard Retrofit focuses on a small but high-impact transformation of an overlooked urban interior. The work considers material restraint, circulation, and the role of atmosphere in everyday use.</p><p>The project balances practical interventions with a quieter spatial strategy that lets the courtyard feel more coherent and generous without over-designing it.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/courtyard-retrofit-1/1000/1400',
			'https://picsum.photos/seed/courtyard-retrofit-2/1000/1400',
			'https://picsum.photos/seed/courtyard-retrofit-3/1000/1400',
		),
	),
	array(
		'slug'         => 'signal-house-identity',
		'title'        => 'Signal House Identity',
		'project_type' => 'work',
		'menu_order'   => 40,
		'excerpt'      => 'A cross-disciplinary project connecting architectural tone, visual identity, and digital presentation into one authored system rather than separate outputs.',
		'content'      => '<p>Signal House Identity brings together naming, visual language, layout logic, and spatial references into a single authored framework. The aim is to let the identity behave like an architectural system: ordered, precise, and adaptable.</p><p>The project demonstrates how branding and built work can share the same structural logic when both are treated as part of one design process.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/signal-house-identity-1/1000/1400',
			'https://picsum.photos/seed/signal-house-identity-2/1000/1400',
			'https://picsum.photos/seed/signal-house-identity-3/1000/1400',
		),
	),
	array(
		'slug'         => 'studio-website-system',
		'title'        => 'Studio Website System',
		'project_type' => 'work',
		'menu_order'   => 45,
		'excerpt'      => 'A modular web design and content system for presenting selected work, long-form writing, and tools without turning the site into a generic portfolio.',
		'content'      => '<p>Studio Website System treats the website as an authored publishing platform rather than a static showcase. The work combines hierarchy, component logic, editorial rhythm, and strong restraint.</p><p>Its central question is how a digital system can remain open-ended without becoming visually noisy.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/studio-website-system-1/1000/1400',
			'https://picsum.photos/seed/studio-website-system-2/1000/1400',
			'https://picsum.photos/seed/studio-website-system-3/1000/1400',
		),
		'media'        => array(
			array(
				'type'      => 'image',
				'image_url' => 'https://picsum.photos/seed/studio-website-system-1/1400/1800',
			),
			array(
				'type'        => 'youtube',
				'youtube_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
			),
			array(
				'type'      => 'image',
				'image_url' => 'https://picsum.photos/seed/studio-website-system-3/1400/1800',
			),
		),
	),
	array(
		'slug'         => 'harbor-wayfinding',
		'title'        => 'Harbor Wayfinding',
		'project_type' => 'work',
		'menu_order'   => 48,
		'excerpt'      => 'A spatial communication project using route markers, material cues, and interface logic to connect waterfront movement with a clearer public experience.',
		'content'      => '<p>Harbor Wayfinding combines spatial signage, pacing, and digital orientation into one system. Rather than relying on isolated signs, the project treats movement as a sequence of cues embedded in the environment.</p><p>The design language aims to be calm, legible, and consistent across different scales.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/harbor-wayfinding-1/1000/1400',
			'https://picsum.photos/seed/harbor-wayfinding-2/1000/1400',
			'https://picsum.photos/seed/harbor-wayfinding-3/1000/1400',
		),
	),
	array(
		'slug'         => 'mobility-atlas',
		'title'        => 'Mobility Atlas',
		'project_type' => 'research',
		'menu_order'   => 50,
		'excerpt'      => 'An ongoing research project mapping how infrastructure, movement, and public space intersect across urban corridors, using diagrams, observations, and typological comparisons.',
		'content'      => '<p>Mobility Atlas is a long-term research thread that documents the overlap of circulation, infrastructure, and public life. It collects field notes, diagrammatic studies, and visual comparisons to identify recurring spatial conditions.</p><p>Rather than proposing one solution, the project builds a reference system that can support later design decisions.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/mobility-atlas-1/1000/1400',
			'https://picsum.photos/seed/mobility-atlas-2/1000/1400',
			'https://picsum.photos/seed/mobility-atlas-3/1000/1400',
		),
	),
	array(
		'slug'         => 'temporary-city-toolkit',
		'title'        => 'Temporary City Toolkit',
		'project_type' => 'research',
		'menu_order'   => 60,
		'excerpt'      => 'A research-based toolkit for temporary urban interventions, focusing on small systems, repeatable details, and low-threshold ways of testing public space ideas.',
		'content'      => '<p>Temporary City Toolkit gathers recurring design elements that can be deployed quickly in public space: markers, seating, shading, surface adjustments, and lightweight structures.</p><p>The goal is to turn research into a practical framework that helps test spatial ideas before they become fixed or expensive.</p>',
		'gallery'      => array(
			'https://picsum.photos/seed/temporary-city-toolkit-1/1000/1400',
			'https://picsum.photos/seed/temporary-city-toolkit-2/1000/1400',
			'https://picsum.photos/seed/temporary-city-toolkit-3/1000/1400',
		),
	),
);

foreach ( $projects as $project ) {
	$existing = get_page_by_path( $project['slug'], OBJECT, 'project' );

	$post_data = array(
		'post_type'    => 'project',
		'post_status'  => 'publish',
		'post_title'   => $project['title'],
		'post_name'    => $project['slug'],
		'post_excerpt' => $project['excerpt'],
		'post_content' => $project['content'],
		'menu_order'   => $project['menu_order'],
	);

	if ( $existing instanceof WP_Post ) {
		$post_data['ID'] = $existing->ID;
		$post_id         = wp_update_post( $post_data, true );
	} else {
		$post_id = wp_insert_post( $post_data, true );
	}

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( sprintf( 'Skipping %s: %s', $project['title'], $post_id->get_error_message() ) );
		continue;
	}

	wp_set_object_terms( $post_id, array( $project['project_type'] ), 'project_type', false );
	update_post_meta( $post_id, 'nunlab_demo_gallery', isset( $project['gallery'] ) ? $project['gallery'] : array() );
	update_post_meta( $post_id, 'nunlab_project_media_items', isset( $project['media'] ) ? $project['media'] : array() );
	update_post_meta( $post_id, 'nunlab_project_gallery_ids', array() );
	WP_CLI::log( sprintf( 'Seeded %s (%s).', $project['title'], $project['project_type'] ) );
}

<?php
/**
 * Import professional work projects and their media.
 *
 * Run with: wp eval-file /tmp/nunlab-work-import/import-work-projects.php
 */

$base_dir = '/tmp/nunlab-work-import';

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function nunlab_import_find_attachment_by_filename( $filename ) {
	global $wpdb;

	$attachment_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
			'%' . $wpdb->esc_like( $filename )
		)
	);

	return $attachment_id ? (int) $attachment_id : 0;
}

function nunlab_import_attachment( $project_id, $file_path, $title, $caption, $alt, $credit ) {
	if ( ! file_exists( $file_path ) ) {
		throw new RuntimeException( 'Missing media file: ' . $file_path );
	}

	$filename      = basename( $file_path );
	$attachment_id = nunlab_import_find_attachment_by_filename( $filename );

	if ( ! $attachment_id ) {
		$temp_file = wp_tempnam( $filename );

		if ( ! $temp_file || ! copy( $file_path, $temp_file ) ) {
			throw new RuntimeException( 'Could not prepare media file: ' . $file_path );
		}

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $temp_file,
		);

		$attachment_id = media_handle_sideload( $file_array, $project_id, $title );

		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $temp_file );
			throw new RuntimeException( $attachment_id->get_error_message() );
		}
	}

	wp_update_post(
		array(
			'ID'           => $attachment_id,
			'post_parent'  => $project_id,
			'post_title'   => $title,
			'post_excerpt' => $caption,
			'post_content' => '',
		)
	);

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );

	if ( '' !== $credit ) {
		update_post_meta( $attachment_id, 'nunlab_media_credit', $credit );
	} else {
		delete_post_meta( $attachment_id, 'nunlab_media_credit' );
	}

	return $attachment_id;
}

$projects = array(
	array(
		'slug'       => 'canyon',
		'title'      => 'CANYON',
		'title_one'  => '',
		'title_two'  => 'CANYON',
		'menu_order' => 100,
		'excerpt'    => 'A planted mixed-use office building in Frankfurt\'s banking district, developed through plan coordination, facade and lobby studies, 3D model work, visualization handoff, and tenant-planning iterations.',
		'content'    => $base_dir . '/canyon/Website_Project_Content.wp.html',
		'meta'       => array(
			array( 'label' => 'Type', 'value' => 'Professional project' ),
			array( 'label' => 'Programme', 'value' => 'Mixed-use office / retail / gastronomy' ),
			array( 'label' => 'Location', 'value' => 'Frankfurt am Main, Germany' ),
			array( 'label' => 'Phase', 'value' => 'LPH 2-4 + tenant planning' ),
			array( 'label' => 'Role', 'value' => 'Project team, design development + coordination' ),
			array( 'label' => 'Scope', 'value' => 'Plans, facade / lobby studies, 3D model, visualization handoff' ),
		),
		'poster'     => array(
			'file'    => 'canyon/website/canyon-card-poster.jpg',
			'title'   => 'CANYON card poster',
			'caption' => '',
			'alt'     => 'CANYON tower with planted facade in Frankfurt',
			'credit'  => 'CV Real Estate AG / bloomimages',
		),
		'media'      => array(
			array( 'file' => 'canyon/website/canyon-01-street-perspective.jpg', 'title' => 'Street perspective', 'caption' => 'Street perspective', 'alt' => 'CANYON street perspective with planted facade and public ground floor', 'credit' => 'CV Real Estate AG / bloomimages' ),
			array( 'file' => 'canyon/website/canyon-02-aerial-terraces.jpg', 'title' => 'Aerial view with planted terraces', 'caption' => 'Aerial view with planted terraces', 'alt' => 'Aerial view of CANYON with planted terraces in Frankfurt', 'credit' => 'CV Real Estate AG / bloomimages' ),
			array( 'file' => 'canyon/website/canyon-03-public-lobby.jpg', 'title' => 'Public lobby perspective', 'caption' => 'Public lobby perspective', 'alt' => 'CANYON public lobby perspective with stair and double-height space', 'credit' => 'CV Real Estate AG / bloomimages' ),
			array( 'file' => 'canyon/website/canyon-04-in-house-lobby-study.jpg', 'title' => 'In-house lobby study', 'caption' => 'In-house lobby study', 'alt' => 'In-house lobby study for CANYON with ceiling and material concept', 'credit' => 'Study: Pascal Nuenninghoff / KSP ENGEL' ),
			array( 'file' => 'canyon/website/canyon-05-lobby-plan-study.jpg', 'title' => 'Lobby plan study', 'caption' => 'Lobby plan study', 'alt' => 'CANYON lobby plan study with public ground-floor layout', 'credit' => 'Study: Pascal Nuenninghoff / KSP ENGEL' ),
			array( 'file' => 'canyon/website/canyon-06-facade-lobby-elevation-study.jpg', 'title' => 'Facade and lobby elevation study', 'caption' => 'Facade and lobby elevation study', 'alt' => 'CANYON facade and lobby elevation study', 'credit' => 'Study: Pascal Nuenninghoff / KSP ENGEL' ),
		),
	),
	array(
		'slug'       => 'mauritius-hoefe',
		'title'      => 'Mauritius Höfe',
		'title_one'  => '',
		'title_two'  => 'Mauritius Höfe',
		'menu_order' => 110,
		'excerpt'    => 'An urban repair project in Wiesbaden that reworks the former City-Passage into a porous mixed-use block with passages, courtyards, a central plaza, and layered public thresholds.',
		'content'    => $base_dir . '/mauritius-hoefe/Website_Project_Content.wp.html',
		'meta'       => array(
			array( 'label' => 'Type', 'value' => 'Professional project' ),
			array( 'label' => 'Programme', 'value' => 'Mixed-use urban quarter' ),
			array( 'label' => 'Location', 'value' => 'Wiesbaden, Germany' ),
			array( 'label' => 'Phase', 'value' => 'LPH 2-4 + tenant planning' ),
			array( 'label' => 'Role', 'value' => 'Project team, design development + coordination' ),
			array( 'label' => 'Scope', 'value' => 'Layouts, block structure, approvals, presentations' ),
		),
		'poster'     => array(
			'file'    => 'mauritius-hoefe/website/mauritius-hoefe-card-poster.jpg',
			'title'   => 'Mauritius Höfe card poster',
			'caption' => '',
			'alt'     => 'Mauritius Höfe street perspective on Schwalbacher Strasse',
			'credit'  => 'KSP ENGEL / Art-Invest Real Estate / bloomimages',
		),
		'media'      => array(
			array( 'file' => 'mauritius-hoefe/website/mauritius-hoefe-01-street-perspective.jpg', 'title' => 'Schwalbacher Strasse perspective', 'caption' => 'Schwalbacher Strasse perspective', 'alt' => 'Mauritius Höfe street perspective with mixed-use facade in Wiesbaden', 'credit' => 'KSP ENGEL / Art-Invest Real Estate / bloomimages' ),
			array( 'file' => 'mauritius-hoefe/website/mauritius-hoefe-02-passage-courtyard.jpg', 'title' => 'Passage and courtyard perspective', 'caption' => 'Passage and courtyard perspective', 'alt' => 'Mauritius Höfe passage and courtyard perspective with retail edges', 'credit' => 'KSP ENGEL / Art-Invest Real Estate / bloomimages' ),
			array( 'file' => 'mauritius-hoefe/website/mauritius-hoefe-03-aerial-block.jpg', 'title' => 'Aerial block view', 'caption' => 'Aerial block view', 'alt' => 'Aerial view of Mauritius Höfe with courtyards and roof terraces', 'credit' => 'KSP ENGEL / Art-Invest Real Estate / bloomimages' ),
			array( 'file' => 'mauritius-hoefe/website/mauritius-hoefe-04-site-plan-courtyards.jpg', 'title' => 'Site plan with courtyards and passages', 'caption' => 'Site plan with courtyards and passages', 'alt' => 'Site plan diagram of Mauritius Höfe showing courtyards and passages', 'credit' => 'Drawing: Pascal Nuenninghoff / KSP ENGEL' ),
			array( 'file' => 'mauritius-hoefe/website/mauritius-hoefe-05-urban-concept-and-block-logic.jpg', 'title' => 'Urban block structure and concept', 'caption' => 'Urban block structure and concept in four moves', 'alt' => 'Mauritius Höfe urban block structure and concept diagrams', 'credit' => 'Drawing: Pascal Nuenninghoff / KSP ENGEL' ),
		),
	),
	array(
		'slug'       => '160-park-view-hochhaus-am-park',
		'title'      => '160 Park View / Hochhaus am Park',
		'title_one'  => '160 Park View',
		'title_two'  => 'Hochhaus am Park',
		'menu_order' => 120,
		'excerpt'    => 'A high-rise transformation in Frankfurt\'s Westend, converting a former office tower into residential and hotel use through differentiated facade identities, landscape threshold, and BIM-based execution planning.',
		'content'    => $base_dir . '/160-park-view-hochhaus-am-park/Website_Project_Content.wp.html',
		'meta'       => array(
			array( 'label' => 'Type', 'value' => 'Professional project' ),
			array( 'label' => 'Programme', 'value' => 'Residential + hotel high-rise transformation' ),
			array( 'label' => 'Location', 'value' => 'Frankfurt am Main, Germany' ),
			array( 'label' => 'Phase', 'value' => 'LPH 5, execution planning / BIM' ),
			array( 'label' => 'Role', 'value' => 'Project team, detailed design + coordination' ),
			array( 'label' => 'Scope', 'value' => 'Revit WPS, details, RCPs, floor plans, plan updates' ),
		),
		'poster'     => array(
			'file'    => '160-park-view-hochhaus-am-park/website/160-park-view-card-poster.jpg',
			'title'   => '160 Park View card poster',
			'caption' => '',
			'alt'     => '160 Park View tower with Frankfurt skyline',
			'credit'  => 'KSP ENGEL / bloomimages',
		),
		'media'      => array(
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-01-park-edge-facade.jpg', 'title' => 'Park-edge facade and podium landscape', 'caption' => 'Park-edge facade and podium landscape', 'alt' => '160 Park View facade and podium landscape along the park edge', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-02-tower-skyline.jpg', 'title' => 'Tower and Frankfurt skyline', 'caption' => 'Tower and Frankfurt skyline', 'alt' => '160 Park View tower with Frankfurt skyline at sunset', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-03-grueneburgpark-context.jpg', 'title' => 'Grüneburgpark context', 'caption' => 'Grüneburgpark context', 'alt' => 'View from Grüneburgpark toward 160 Park View', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-04-roof-terrace-view.jpg', 'title' => 'Hotel roof terrace view', 'caption' => 'Hotel roof terrace view', 'alt' => 'Hotel roof terrace view from 160 Park View toward Frankfurt', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-05-residential-interior-terrace.jpg', 'title' => 'Residential interior with terrace', 'caption' => 'Residential interior with terrace', 'alt' => 'Residential interior with terrace and panoramic windows at 160 Park View', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-06-apartment-daylight.jpg', 'title' => 'Apartment daylight study', 'caption' => 'Apartment daylight study', 'alt' => 'Daylight apartment interior with skyline view at 160 Park View', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-07-apartment-night.jpg', 'title' => 'Apartment night view', 'caption' => 'Apartment night view', 'alt' => 'Night apartment interior with Frankfurt skyline view at 160 Park View', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-08-lobby-frontage.jpg', 'title' => 'Lobby frontage and podium', 'caption' => 'Lobby frontage and podium', 'alt' => '160 Park View lobby frontage and podium landscape', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-09-lobby-interior.jpg', 'title' => 'Lobby interior', 'caption' => 'Lobby interior', 'alt' => '160 Park View lobby interior with warm material palette', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-10-bedroom-skyline.jpg', 'title' => 'Bedroom with skyline view', 'caption' => 'Bedroom with skyline view', 'alt' => 'Bedroom interior with skyline view at 160 Park View', 'credit' => 'KSP ENGEL / bloomimages' ),
			array( 'file' => '160-park-view-hochhaus-am-park/website/160-park-view-11-bathroom-view.jpg', 'title' => 'Bathroom with city view', 'caption' => 'Bathroom with city view', 'alt' => 'Bathroom interior with city view at 160 Park View', 'credit' => 'KSP ENGEL / bloomimages' ),
		),
	),
);

$results = array();

foreach ( $projects as $project ) {
	$content = file_exists( $project['content'] ) ? file_get_contents( $project['content'] ) : '';
	$existing = get_page_by_path( $project['slug'], OBJECT, 'project' );

	$post_data = array(
		'post_type'    => 'project',
		'post_status'  => 'publish',
		'post_name'    => $project['slug'],
		'post_title'   => $project['title'],
		'post_excerpt' => $project['excerpt'],
		'post_content' => $content,
		'menu_order'   => $project['menu_order'],
	);

	if ( $existing instanceof WP_Post ) {
		$post_data['ID'] = $existing->ID;
		$post_id = wp_update_post( $post_data, true );
	} else {
		$post_id = wp_insert_post( $post_data, true );
	}

	if ( is_wp_error( $post_id ) ) {
		throw new RuntimeException( $post_id->get_error_message() );
	}

	wp_set_object_terms( $post_id, array( 'work' ), 'project_type' );
	update_post_meta( $post_id, 'nunlab_project_title_line_one', $project['title_one'] );
	update_post_meta( $post_id, 'nunlab_project_title_line_two', $project['title_two'] );
	update_post_meta( $post_id, 'nunlab_project_meta_items', $project['meta'] );

	$poster = $project['poster'];
	$poster_id = nunlab_import_attachment(
		$post_id,
		$base_dir . '/' . $poster['file'],
		$poster['title'],
		$poster['caption'],
		$poster['alt'],
		$poster['credit']
	);
	set_post_thumbnail( $post_id, $poster_id );

	$media_items = array();
	foreach ( $project['media'] as $media ) {
		$attachment_id = nunlab_import_attachment(
			$post_id,
			$base_dir . '/' . $media['file'],
			$media['title'],
			$media['caption'],
			$media['alt'],
			$media['credit']
		);

		$media_items[] = array(
			'type'      => 'image',
			'image_id'  => $attachment_id,
			'image_url' => '',
		);
	}

	update_post_meta( $post_id, 'nunlab_project_media_items', $media_items );
	delete_post_meta( $post_id, 'nunlab_project_gallery_ids' );
	delete_post_meta( $post_id, 'nunlab_demo_gallery' );

	$results[] = sprintf( '%s: post %d, %d media items', $project['title'], $post_id, count( $media_items ) );
}

echo implode( "\n", $results ) . "\n";

<?php
/**
 * Seed local example notebook posts for the N:UN theme.
 *
 * Run with:
 * php -d error_reporting=24575 /opt/homebrew/bin/wp --path=.wp-local eval-file scripts/seed-local-notebook-posts.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$posts = array(
	array(
		'slug'    => 'field-notes-river-edge',
		'title'   => 'Field Notes from the River Edge',
		'excerpt' => 'Observations on thresholds, pauses, and small spatial cues along a stitched-together river path.',
		'date'    => '2026-04-12 09:30:00',
		'content' => '
<!-- wp:paragraph -->
<p>The river edge is less a single promenade than a sequence of changes in pace. A wall becomes a seat, a ramp becomes a lookout, and a planted strip suddenly reads like climate infrastructure rather than leftover green.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>What matters most in these fragments is not the object itself but the way one condition prepares the next. Shade leads to pause. Pause leads to observation. Observation changes how distance is measured.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>Three recurring conditions</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><li>soft edges between path and water</li><li>lightweight structures that register use without over-defining it</li><li>small markers that make movement legible across longer sequences</li></ul>
<!-- /wp:list -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>The most useful interventions are often the ones that make the site feel calmer rather than more designed.</p></blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>For the notebook, the value of these notes is not completeness. It is that they preserve a way of looking that can later become a project logic.</p>
<!-- /wp:paragraph -->
',
	),
	array(
		'slug'    => 'material-memory-and-temporary-use',
		'title'   => 'Material Memory and Temporary Use',
		'excerpt' => 'Notes on how reused materials carry prior atmospheres into temporary installations and lightweight interiors.',
		'date'    => '2026-04-08 16:10:00',
		'content' => '
<!-- wp:paragraph -->
<p>Temporary work is often described through speed, cost, or flexibility. Less often through memory. Yet reused timber, worn metal, and repaired textiles all bring prior time into the new arrangement.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>Working assumption</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A temporary intervention does not need to feel provisional. It can feel deliberate, precise, and still remain open to change.</p>
<!-- /wp:paragraph -->

<!-- wp:separator -->
<hr class="wp-block-separator has-alpha-channel-opacity"/>
<!-- /wp:separator -->

<!-- wp:paragraph -->
<p>That shift matters for small public-space projects. When the assembly logic is visible, the intervention becomes easier to trust. It reads as authored rather than improvised.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>In that sense, material reuse is not only an ecological question. It is also a question of tone.</p>
<!-- /wp:paragraph -->
',
	),
	array(
		'slug'    => 'diagramming-the-in-between-city',
		'title'   => 'Diagramming the In-Between City',
		'excerpt' => 'A short study on drawing spaces that sit between street, building, infrastructure, and landscape.',
		'date'    => '2026-04-03 11:45:00',
		'content' => '
<!-- wp:paragraph -->
<p>Many of the sites that matter most are hard to name cleanly. They are not exactly plazas, corridors, thresholds, or residual spaces, yet they borrow something from each of those categories.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Drawing them as fixed figures tends to flatten their spatial role. A better diagram often begins with relation: what is adjacent, what is crossed, what is buffered, and what remains unresolved.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>Useful lenses</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><li>movement and friction</li><li>exposure and enclosure</li><li>surface continuity</li><li>programmed versus incidental use</li></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>The notebook becomes a place to test these diagram types before they harden into a polished representation.</p>
<!-- /wp:paragraph -->
',
	),
	array(
		'slug'    => 'why-a-notebook-matters-in-practice',
		'title'   => 'Why a Notebook Matters in Practice',
		'excerpt' => 'On keeping a working layer between immediate production and finished publication.',
		'date'    => '2026-03-28 14:20:00',
		'content' => '
<!-- wp:paragraph -->
<p>Not every useful thought should become a manifesto, and not every fragment belongs inside a project page. A notebook creates a third condition: public enough to be shareable, unfinished enough to stay agile.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>That matters for an architecture and design practice because so much of the real work happens between outputs. Site notes, references, tests, diagrams, and partial arguments all accumulate before they ever resolve into a case study.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>A notebook is not secondary content. It is where a practice learns how it thinks in public.</p></blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>For N:UN, the notebook should stay lighter than the project pages but not feel disposable. It is a working layer, not a leftovers page.</p>
<!-- /wp:paragraph -->
',
	),
);

$hello_world = get_page_by_path( 'hello-world', OBJECT, 'post' );

if ( $hello_world instanceof WP_Post ) {
	wp_delete_post( $hello_world->ID, true );
}

foreach ( $posts as $post ) {
	$existing = get_page_by_path( $post['slug'], OBJECT, 'post' );

	$post_data = array(
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'post_title'   => $post['title'],
		'post_name'    => $post['slug'],
		'post_excerpt' => $post['excerpt'],
		'post_content' => trim( $post['content'] ),
		'post_date'    => $post['date'],
	);

	if ( $existing instanceof WP_Post ) {
		$post_data['ID'] = $existing->ID;
		$post_id         = wp_update_post( $post_data, true );
	} else {
		$post_id = wp_insert_post( $post_data, true );
	}

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( sprintf( 'Skipping %s: %s', $post['title'], $post_id->get_error_message() ) );
		continue;
	}

	WP_CLI::log( sprintf( 'Seeded notebook post %s.', $post['title'] ) );
}

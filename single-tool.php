<?php
/**
 * Single Plugin Template
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
	<div class="content-shell content-shell--tool">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content/content', 'tool' );
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();

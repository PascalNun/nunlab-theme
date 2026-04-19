<?php
/**
 * Main Template File (Fallback)
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
	<div class="archive-shell">
		<header class="archive-header">
			<p class="archive-eyebrow"><?php esc_html_e( 'Content', 'nunlab-theme' ); ?></p>
			<h1 class="archive-title"><?php bloginfo( 'name' ); ?></h1>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="content-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/content', 'excerpt' );
				endwhile;
				?>
			</div>
			<?php the_posts_navigation(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php

get_footer();

<?php
/**
 * Notebook Index Template
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
			<p class="archive-eyebrow"><?php esc_html_e( 'Notebook', 'nunlab-theme' ); ?></p>
			<h1 class="archive-title">
				<?php
				$posts_page_id = (int) get_option( 'page_for_posts' );
				echo esc_html( $posts_page_id ? get_the_title( $posts_page_id ) : __( 'Notebook', 'nunlab-theme' ) );
				?>
			</h1>
			<p class="archive-description">
				<?php esc_html_e( 'Notes, essays, fragments, and working thoughts from N:UN Lab.', 'nunlab-theme' ); ?>
			</p>
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

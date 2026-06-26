<?php
/**
 * Search Results Template
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
			<p class="archive-eyebrow"><?php esc_html_e( 'Search', 'nunlab-theme' ); ?></p>
			<h1 class="archive-title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Results for "%s"', 'nunlab-theme' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
			<p class="archive-description">
				<?php
				global $wp_query;

				printf(
					/* translators: %s: result count. */
					esc_html(
						_n(
							'%s result across work, notebook, tools, and pages.',
							'%s results across work, notebook, tools, and pages.',
							(int) $wp_query->found_posts,
							'nunlab-theme'
						)
					),
					number_format_i18n( (int) $wp_query->found_posts )
				);
				?>
			</p>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="content-grid content-grid--search">
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

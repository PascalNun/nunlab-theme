<?php
/**
 * Plugins Index Page Template
 *
 * Template Name: Plugins Index
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tool_query = new WP_Query(
	array(
		'post_type'      => 'tool',
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	)
);

get_header();
?>

<main id="primary" class="site-main">
	<div class="archive-shell archive-shell--plugins">
		<?php
		while ( have_posts() ) :
			the_post();

			$plugins_intro       = trim( (string) apply_filters( 'the_content', get_the_content( null, false ) ) );
			$has_plugins_intro   = '' !== trim( wp_strip_all_tags( $plugins_intro ) ) || nunlab_block_has_visible_media_markup( $plugins_intro );
			$plugins_description = has_excerpt() ? get_the_excerpt() : __( 'Plugins and practical systems for moving between data, geometry, and design.', 'nunlab-theme' );
			?>
			<header class="archive-header archive-header--plugins">
				<p class="archive-eyebrow"><?php esc_html_e( 'Plugin Index', 'nunlab-theme' ); ?></p>
				<h1 class="archive-title"><?php the_title(); ?></h1>
				<?php if ( '' !== trim( $plugins_description ) ) : ?>
					<p class="archive-description">
						<?php echo esc_html( $plugins_description ); ?>
					</p>
				<?php endif; ?>
			</header>

			<?php if ( $has_plugins_intro ) : ?>
				<div class="plugins-index__intro entry-content">
					<?php echo $plugins_intro; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		<?php endwhile; ?>

		<?php if ( $tool_query->have_posts() ) : ?>
			<div class="content-grid content-grid--plugins">
				<?php
				while ( $tool_query->have_posts() ) :
					$tool_query->the_post();
					get_template_part( 'template-parts/content/content', 'tool-card' );
				endwhile;
				?>
			</div>
		<?php else : ?>
			<section class="content-empty content-empty--plugins">
				<p class="archive-eyebrow"><?php esc_html_e( 'Coming Soon', 'nunlab-theme' ); ?></p>
				<h2 class="content-empty__title"><?php esc_html_e( 'Plugin cards will appear here once entries are published.', 'nunlab-theme' ); ?></h2>
				<p class="content-empty__text">
					<?php esc_html_e( 'The section is ready for RhinoSpatial and future tools authored in WordPress.', 'nunlab-theme' ); ?>
				</p>
			</section>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</main>

<?php
get_footer();

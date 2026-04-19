<?php
/**
 * Front Page About Section
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$about_page_id = (int) nunlab_get_front_page_field( 'nunlab_about_page_id', 0 );
$about_page    = $about_page_id ? get_post( $about_page_id ) : get_page_by_path( 'about' );

if ( ! $about_page instanceof WP_Post || 'page' !== $about_page->post_type || 'publish' !== $about_page->post_status ) {
	return;
}
?>
<section id="about" class="home-section home-section--about">
	<div class="home-section__inner">
		<div class="section-heading">
			<div>
				<p class="archive-eyebrow"><?php esc_html_e( 'About', 'nunlab-theme' ); ?></p>
				<h2 class="section-heading__title"><?php echo esc_html( get_the_title( $about_page ) ); ?></h2>
			</div>
		</div>

		<div class="text-panel text-panel--about">
			<?php if ( has_excerpt( $about_page ) ) : ?>
				<p class="text-panel__lead"><?php echo esc_html( get_the_excerpt( $about_page ) ); ?></p>
			<?php endif; ?>

			<div class="entry-content">
				<?php echo wp_kses_post( apply_filters( 'the_content', $about_page->post_content ) ); ?>
			</div>
		</div>
	</div>
</section>

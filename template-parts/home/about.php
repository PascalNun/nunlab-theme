<?php
/**
 * Front Page About Section
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$about_page = nunlab_get_front_page_source_page( 'nunlab_about_page_id', 'about' );

if ( ! $about_page instanceof WP_Post ) {
	return;
}
?>
<section id="about" class="home-section home-section--about">
	<div class="home-section__inner">
		<div class="section-heading">
			<div>
				<h2 class="section-heading__title"><?php echo esc_html( get_the_title( $about_page ) ); ?></h2>
			</div>
		</div>

		<div class="text-panel text-panel--about">
			<?php if ( has_excerpt( $about_page ) ) : ?>
				<p class="text-panel__lead"><?php echo esc_html( get_the_excerpt( $about_page ) ); ?></p>
			<?php endif; ?>

			<?php echo nunlab_render_editorial_sections( $about_page->post_content, 'flow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</section>

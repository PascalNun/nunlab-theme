<?php
/**
 * Front Page Manifesto Section
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$manifesto_page = nunlab_get_front_page_source_page( 'nunlab_manifesto_page_id', 'manifesto' );

if ( ! $manifesto_page instanceof WP_Post ) {
	return;
}
?>
<section id="manifesto" class="home-section home-section--manifesto">
	<div class="home-section__inner">
		<div class="section-heading">
			<div>
				<h2 class="section-heading__title"><?php echo esc_html( get_the_title( $manifesto_page ) ); ?></h2>
			</div>
		</div>

		<div class="text-panel text-panel--manifesto">
			<?php if ( has_excerpt( $manifesto_page ) ) : ?>
				<p class="text-panel__lead"><?php echo esc_html( get_the_excerpt( $manifesto_page ) ); ?></p>
			<?php endif; ?>

			<div class="entry-content">
				<?php echo wp_kses_post( apply_filters( 'the_content', $manifesto_page->post_content ) ); ?>
			</div>
		</div>
	</div>
</section>

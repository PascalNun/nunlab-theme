<?php
/**
 * Front Page Plugins Section
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugins_page = get_page_by_path( 'plugins' );
?>
<section class="home-section">
	<div class="home-section__inner">
		<div class="section-heading">
			<div>
				<p class="archive-eyebrow"><?php esc_html_e( 'Plugins', 'nunlab-theme' ); ?></p>
				<h2 class="section-heading__title"><?php esc_html_e( 'Prepare the space for tools and experiments without forcing a full product system too early.', 'nunlab-theme' ); ?></h2>
			</div>
			<?php if ( $plugins_page instanceof WP_Post ) : ?>
				<a class="section-heading__link" href="<?php echo esc_url( get_permalink( $plugins_page ) ); ?>">
					<?php esc_html_e( 'Open plugins', 'nunlab-theme' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="text-panel">
			<?php if ( $plugins_page instanceof WP_Post ) : ?>
				<p>
					<?php
					echo esc_html(
						wp_trim_words(
							has_excerpt( $plugins_page ) ? $plugins_page->post_excerpt : wp_strip_all_tags( $plugins_page->post_content ),
							28
						)
					);
					?>
				</p>
			<?php else : ?>
				<p><?php esc_html_e( 'Start this area as a well-shaped landing page. Only introduce a dedicated content type once you have repeated plugin data that genuinely needs structure.', 'nunlab-theme' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>

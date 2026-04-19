<?php
/**
 * Front Page Manifesto Section
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$manifesto_page = get_page_by_path( 'manifesto' );
?>
<section class="home-section">
	<div class="home-section__inner">
		<div class="section-heading">
			<div>
				<p class="archive-eyebrow"><?php esc_html_e( 'Manifesto', 'nunlab-theme' ); ?></p>
				<h2 class="section-heading__title"><?php esc_html_e( 'A place for principles, positions, and the thinking behind the work.', 'nunlab-theme' ); ?></h2>
			</div>
			<?php if ( $manifesto_page instanceof WP_Post ) : ?>
				<a class="section-heading__link" href="<?php echo esc_url( get_permalink( $manifesto_page ) ); ?>">
					<?php esc_html_e( 'Open manifesto', 'nunlab-theme' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="text-panel">
			<?php if ( $manifesto_page instanceof WP_Post ) : ?>
				<p>
					<?php
					echo esc_html(
						wp_trim_words(
							has_excerpt( $manifesto_page ) ? $manifesto_page->post_excerpt : wp_strip_all_tags( $manifesto_page->post_content ),
							32
						)
					);
					?>
				</p>
			<?php else : ?>
				<p><?php esc_html_e( 'Keep this as a dedicated page for authored ideas, not a long block dropped into the homepage. The front page only needs to signal its presence and tone.', 'nunlab-theme' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>

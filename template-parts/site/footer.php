<?php
/**
 * Site Footer
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$imprint_page = get_page_by_path( 'imprint' );

if ( ! $imprint_page instanceof WP_Post ) {
	$imprint_page = get_page_by_path( 'impressum' );
}
?>
<footer class="site-footer">
	<div class="site-footer__inner">
		<div class="site-footer__meta">
			<p class="site-footer__title"><?php bloginfo( 'name' ); ?></p>
			<p class="site-footer__description">
				<?php esc_html_e( 'Architecture, design, research, tools, and making.', 'nunlab-theme' ); ?>
			</p>
		</div>

		<?php if ( $imprint_page instanceof WP_Post && 'publish' === $imprint_page->post_status ) : ?>
			<nav class="site-footer__legal" aria-label="<?php esc_attr_e( 'Legal Navigation', 'nunlab-theme' ); ?>">
				<a href="<?php echo esc_url( get_permalink( $imprint_page ) ); ?>">
					<?php echo esc_html( get_the_title( $imprint_page ) ); ?>
				</a>
			</nav>
		<?php endif; ?>

		<p class="site-footer__copyright">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>
	</div>
</footer>

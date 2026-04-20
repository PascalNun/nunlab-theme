<?php
/**
 * Site Footer
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

		<?php if ( has_nav_menu( 'legal' ) ) : ?>
			<nav class="site-footer__legal" aria-label="<?php esc_attr_e( 'Legal Navigation', 'nunlab-theme' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'legal',
						'container'      => false,
						'menu_class'     => 'site-footer__legal-menu',
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<p class="site-footer__copyright">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>
	</div>
</footer>

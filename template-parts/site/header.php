<?php
/**
 * Site Header
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="site-header">
	<div class="site-header__inner">
		<div class="site-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-brand__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="site-header__aside">
			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<button
					class="site-header__menu-button"
					type="button"
					aria-label="<?php esc_attr_e( 'Toggle menu', 'nunlab-theme' ); ?>"
					aria-controls="site-navigation-panel"
					aria-expanded="false"
					data-menu-toggle
				>
					<span class="site-header__menu-icon" aria-hidden="true">
						<span class="site-header__menu-line"></span>
						<span class="site-header__menu-line"></span>
						<span class="site-header__menu-line"></span>
					</span>
				</button>

				<nav
					id="site-navigation-panel"
					class="site-navigation"
					aria-label="<?php esc_attr_e( 'Main Navigation', 'nunlab-theme' ); ?>"
					data-menu-panel
				>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'site-navigation__menu',
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<div class="site-header__utilities">
				<button
					class="site-header__search-button"
					type="button"
					aria-label="<?php esc_attr_e( 'Open search', 'nunlab-theme' ); ?>"
					aria-controls="site-search-layer"
					aria-expanded="false"
					data-search-toggle
				>
					<span class="site-header__search-indicator" aria-hidden="true"></span>
				</button>
			</div>
		</div>
	</div>
</header>

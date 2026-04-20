<?php
/**
 * Site Header
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name = get_bloginfo( 'name' );

$logo_dark_uri   = nunlab_get_theme_asset_uri( 'assets/images/brand/Logo1_Dark.svg' );
$logo_light_uri  = nunlab_get_theme_asset_uri( 'assets/images/brand/Logo1_Light.svg' );
$search_dark_uri = nunlab_get_theme_asset_uri( 'assets/icons/search_icon_Dark.svg' );
$search_light_uri = nunlab_get_theme_asset_uri( 'assets/icons/search_icon_light.svg' );

$has_logo_assets   = '' !== $logo_dark_uri && '' !== $logo_light_uri;
$has_search_assets = '' !== $search_dark_uri && '' !== $search_light_uri;
?>
<header class="site-header">
	<div class="site-header__inner">
		<div class="site-brand">
			<?php if ( $has_logo_assets ) : ?>
				<a class="site-brand__asset-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( $site_name ); ?>">
					<span class="site-brand__asset-stack" aria-hidden="true">
						<img class="site-brand__asset site-brand__asset--dark" src="<?php echo esc_url( $logo_dark_uri ); ?>" alt="" />
						<img class="site-brand__asset site-brand__asset--light" src="<?php echo esc_url( $logo_light_uri ); ?>" alt="" />
					</span>
				</a>
			<?php elseif ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-brand__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php echo esc_html( $site_name ); ?>
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
					<?php if ( $has_search_assets ) : ?>
						<span class="site-header__search-icon-stack" aria-hidden="true">
							<img class="site-header__search-icon site-header__search-icon--dark" src="<?php echo esc_url( $search_dark_uri ); ?>" alt="" />
							<img class="site-header__search-icon site-header__search-icon--light" src="<?php echo esc_url( $search_light_uri ); ?>" alt="" />
						</span>
					<?php else : ?>
						<span class="site-header__search-indicator" aria-hidden="true"></span>
					<?php endif; ?>
				</button>
			</div>
		</div>
	</div>
</header>

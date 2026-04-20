<?php
/**
 * Search Overlay
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$search_dark_uri = nunlab_get_theme_asset_uri( 'assets/icons/search_icon_Dark.svg' );
?>
<div id="site-search-layer" class="search-layer" hidden data-search-layer>
	<div class="search-layer__panel" role="dialog" aria-modal="true" aria-labelledby="site-search-title">
		<h2 id="site-search-title" class="screen-reader-text"><?php esc_html_e( 'Site search', 'nunlab-theme' ); ?></h2>
		<form class="search-layer__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="site-search-input"><?php esc_html_e( 'Search the site', 'nunlab-theme' ); ?></label>
			<input
				id="site-search-input"
				class="search-layer__input"
				type="search"
				name="s"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php esc_attr_e( 'Search', 'nunlab-theme' ); ?>"
				data-search-input
			/>
			<button class="search-layer__submit" type="submit" aria-label="<?php esc_attr_e( 'Submit search', 'nunlab-theme' ); ?>">
				<?php if ( '' !== $search_dark_uri ) : ?>
					<img class="site-header__search-icon site-header__search-icon--overlay" src="<?php echo esc_url( $search_dark_uri ); ?>" alt="" aria-hidden="true" />
				<?php else : ?>
					<span class="site-header__search-indicator site-header__search-indicator--large" aria-hidden="true"></span>
				<?php endif; ?>
			</button>
		</form>
		<div class="search-layer__meta">
			<p class="search-layer__hint"><?php esc_html_e( 'Search projects, notebook entries, and pages.', 'nunlab-theme' ); ?></p>
			<button class="search-layer__close" type="button" data-search-close>
				<?php esc_html_e( 'Close', 'nunlab-theme' ); ?>
			</button>
		</div>
	</div>
</div>

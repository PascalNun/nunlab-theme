<?php
/**
 * 404 Template
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
	<section class="error-404">
		<div class="content-shell content-shell--narrow">
			<p class="archive-eyebrow"><?php esc_html_e( '404', 'nunlab-theme' ); ?></p>
			<h1 class="entry-title"><?php esc_html_e( 'Page Not Found', 'nunlab-theme' ); ?></h1>
			<p><?php esc_html_e( 'This address does not point to a published page. Try the homepage or the latest work instead.', 'nunlab-theme' ); ?></p>
			<p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button-link">
					<?php esc_html_e( 'Back to home', 'nunlab-theme' ); ?>
				</a>
			</p>
		</div>
	</section>
</main>

<?php
get_footer();

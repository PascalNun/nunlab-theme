<?php
/**
 * Front Page Hero Section
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tagline    = get_bloginfo( 'description' );
$hero_title = nunlab_get_front_page_field( 'nunlab_hero_title', $tagline ? $tagline : __( 'Architecture, design, research, tools, and making.', 'nunlab-theme' ) );
$hero_intro = nunlab_get_front_page_field( 'nunlab_hero_intro', __( 'A curated platform for selected work, working ideas, urban thinking, and the systems behind the practice.', 'nunlab-theme' ) );
?>
<section class="home-section home-section--hero">
	<div class="home-section__inner hero-block">
		<h1 class="hero-block__title">
			<?php echo esc_html( $hero_title ); ?>
		</h1>
		<p class="hero-block__intro">
			<?php echo esc_html( $hero_intro ); ?>
		</p>
		<div class="hero-block__actions">
			<a class="button-link" href="<?php echo esc_url( home_url( '/#work' ) ); ?>">
				<?php esc_html_e( 'View selected work', 'nunlab-theme' ); ?>
			</a>
		</div>
	</div>
</section>

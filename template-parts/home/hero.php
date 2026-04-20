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
$hero_intro = nunlab_get_front_page_field( 'nunlab_hero_intro', __( 'A curated body of work, ideas, and spatial thinking shaped through architecture, design, research, and making.', 'nunlab-theme' ) );
$hero_sources = array(
	array(
		'path' => NUNLAB_THEME_DIR . '/assets/media/hero/hero-video_scrub.mp4',
		'uri'  => NUNLAB_THEME_URI . '/assets/media/hero/hero-video_scrub.mp4',
		'type' => 'video/mp4',
	),
	array(
		'path' => NUNLAB_THEME_DIR . '/assets/media/hero/hero-video.mp4',
		'uri'  => NUNLAB_THEME_URI . '/assets/media/hero/hero-video.mp4',
		'type' => 'video/mp4',
	),
	array(
		'path' => NUNLAB_THEME_DIR . '/assets/media/hero/hero-video_av1.webm',
		'uri'  => NUNLAB_THEME_URI . '/assets/media/hero/hero-video_av1.webm',
		'type' => 'video/webm; codecs="av01.0.05M.08"',
	),
	array(
		'path' => NUNLAB_THEME_DIR . '/assets/media/hero/hero-video_vp9.webm',
		'uri'  => NUNLAB_THEME_URI . '/assets/media/hero/hero-video_vp9.webm',
		'type' => 'video/webm; codecs="vp9"',
	),
);

$hero_sources = array_values(
	array_filter(
		$hero_sources,
		static function ( $source ) {
			return file_exists( $source['path'] );
		}
	)
);

$hero_posters = array(
	NUNLAB_THEME_DIR . '/assets/media/hero/hero-poster.avif' => NUNLAB_THEME_URI . '/assets/media/hero/hero-poster.avif',
	NUNLAB_THEME_DIR . '/assets/media/hero/hero-poster.webp' => NUNLAB_THEME_URI . '/assets/media/hero/hero-poster.webp',
	NUNLAB_THEME_DIR . '/assets/media/hero/hero-poster.jpg'  => NUNLAB_THEME_URI . '/assets/media/hero/hero-poster.jpg',
);
$hero_sequence_jpg_paths = glob( NUNLAB_THEME_DIR . '/assets/media/hero/sequence/hero-frame-*.jpg' );
$hero_sequence_webp_paths = glob( NUNLAB_THEME_DIR . '/assets/media/hero/sequence/hero-frame-*.webp' );
$hero_sequence_jpg_count  = is_array( $hero_sequence_jpg_paths ) ? count( $hero_sequence_jpg_paths ) : 0;
$hero_sequence_webp_count = is_array( $hero_sequence_webp_paths ) ? count( $hero_sequence_webp_paths ) : 0;
$hero_sequence_count      = $hero_sequence_jpg_count;
$hero_sequence_base_jpg   = NUNLAB_THEME_URI . '/assets/media/hero/sequence/hero-frame-';
$hero_sequence_base_webp  = $hero_sequence_webp_count >= $hero_sequence_count && $hero_sequence_count ? NUNLAB_THEME_URI . '/assets/media/hero/sequence/hero-frame-' : '';
$hero_sequence_first      = $hero_sequence_count ? sprintf( '%s%03d.jpg', $hero_sequence_base_jpg, 1 ) : '';

$hero_poster_uri = '';

foreach ( $hero_posters as $poster_path => $poster_uri ) {
	if ( file_exists( $poster_path ) ) {
		$hero_poster_uri = $poster_uri;
		break;
	}
}
?>
<section
	class="home-section home-section--hero hero-stage"
	data-hero-stage
	<?php if ( $hero_sequence_count ) : ?>
		data-hero-sequence-count="<?php echo esc_attr( (string) $hero_sequence_count ); ?>"
		data-hero-sequence-base-jpg="<?php echo esc_url( $hero_sequence_base_jpg ); ?>"
		<?php if ( $hero_sequence_base_webp ) : ?>
			data-hero-sequence-base-webp="<?php echo esc_url( $hero_sequence_base_webp ); ?>"
		<?php endif; ?>
		data-hero-sequence-digits="3"
	<?php endif; ?>
>
	<div class="hero-stage__pin">
		<div class="hero-stage__media" aria-hidden="true">
			<?php if ( $hero_sequence_first ) : ?>
				<img class="hero-stage__sequence" src="<?php echo esc_url( $hero_sequence_first ); ?>" alt="" hidden data-hero-sequence-frame />
			<?php endif; ?>

			<?php if ( ! empty( $hero_sources ) ) : ?>
				<video
					class="hero-stage__video"
					autoplay
					loop
					muted
					playsinline
					webkit-playsinline="true"
					preload="auto"
					poster="<?php echo esc_url( $hero_poster_uri ); ?>"
					data-hero-video
				>
					<?php foreach ( $hero_sources as $hero_source ) : ?>
						<source src="<?php echo esc_url( $hero_source['uri'] ); ?>" type="<?php echo esc_attr( $hero_source['type'] ); ?>" />
					<?php endforeach; ?>
				</video>
			<?php elseif ( $hero_poster_uri ) : ?>
				<img class="hero-stage__poster" src="<?php echo esc_url( $hero_poster_uri ); ?>" alt="" />
			<?php endif; ?>

			<span class="hero-stage__veil"></span>
		</div>

		<div class="home-section__inner hero-block hero-block--stage" data-hero-content>
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
	</div>
</section>

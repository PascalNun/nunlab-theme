<?php
/**
 * Homepage Work Directory Card
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project_excerpt = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( get_the_excerpt() ) ) );
$summary         = $project_excerpt ? wp_trim_words( $project_excerpt, 28 ) : __( 'Open the case study to see the full project narrative.', 'nunlab-theme' );
$detail_content  = nunlab_render_project_editorial_content( get_the_content( null, false ) );
$title_line_one  = trim( (string) get_post_meta( get_the_ID(), 'nunlab_project_title_line_one', true ) );
$title_line_two  = trim( (string) get_post_meta( get_the_ID(), 'nunlab_project_title_line_two', true ) );
$presentation_title = trim( $title_line_one . ' ' . $title_line_two );
$subtitle        = $project_excerpt;

if ( '' === $presentation_title ) {
	$presentation_title = get_the_title();
}

if ( '' === $detail_content ) {
	$detail_content = '<div class="project-card__expand-fallback"><p>' . esc_html( $summary ) . '</p></div>';
}
$media_items     = nunlab_get_project_media_items( get_the_ID(), 'nunlab-project-large' );
$primary_image   = nunlab_get_project_primary_image( get_the_ID(), 'nunlab-project-large' );
$image_url       = isset( $primary_image['url'] ) ? (string) $primary_image['url'] : '';
$image_alt       = isset( $primary_image['alt'] ) ? (string) $primary_image['alt'] : '';
$media_payload   = array_map(
	function ( $item ) {
		return array(
			'type'             => (string) $item['type'],
			'url'              => isset( $item['url'] ) ? (string) $item['url'] : '',
			'posterUrl'        => isset( $item['poster_url'] ) ? (string) $item['poster_url'] : '',
			'embedUrl'         => isset( $item['embed_url'] ) ? (string) $item['embed_url'] : '',
			'autoplayEmbedUrl' => isset( $item['autoplay_embed_url'] ) ? (string) $item['autoplay_embed_url'] : '',
			'alt'              => isset( $item['alt'] ) ? (string) $item['alt'] : '',
		);
	},
	$media_items
);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'project-card project-card--directory' ); ?> data-work-card-item>
	<div class="project-card__frame project-card__frame--toggle">
		<div
			class="project-card__toggle"
			role="button"
			tabindex="0"
			aria-expanded="false"
			data-work-card
			data-work-title="<?php echo esc_attr( $presentation_title ); ?>"
			data-work-image="<?php echo esc_url( $image_url ); ?>"
			data-work-image-alt="<?php echo esc_attr( $image_alt ? $image_alt : get_the_title() ); ?>"
			data-work-media="<?php echo esc_attr( wp_json_encode( $media_payload ) ); ?>"
		>
			<div class="project-card__media">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'nunlab-project-large', array( 'class' => 'project-card__image', 'data-work-image-current' => '' ) ); ?>
				<?php elseif ( $image_url ) : ?>
					<img class="project-card__image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ? $image_alt : get_the_title() ); ?>" data-work-image-current />
				<?php else : ?>
					<span class="project-card__placeholder" data-work-placeholder-current></span>
				<?php endif; ?>

				<?php if ( $image_url ) : ?>
					<span class="project-card__placeholder" hidden data-work-placeholder-current></span>
				<?php endif; ?>

				<div class="project-card__video-frame" hidden data-work-video-frame></div>
				<button class="project-card__video-play" type="button" hidden data-work-video-play>
					<?php esc_html_e( 'Play video', 'nunlab-theme' ); ?>
				</button>

				<span class="project-card__overlay"></span>

				<div class="project-card__body">
					<h4 class="project-card__title">
						<?php if ( '' !== $title_line_one && '' !== $title_line_two ) : ?>
							<span class="project-card__title-line project-card__title-line--primary"><?php echo esc_html( $title_line_one ); ?></span>
							<span class="project-card__title-line project-card__title-line--emphasis"><?php echo esc_html( $title_line_two ); ?></span>
						<?php elseif ( '' !== $title_line_one ) : ?>
							<span class="project-card__title-line project-card__title-line--emphasis"><?php echo esc_html( $title_line_one ); ?></span>
						<?php elseif ( '' !== $title_line_two ) : ?>
							<span class="project-card__title-line project-card__title-line--emphasis"><?php echo esc_html( $title_line_two ); ?></span>
						<?php else : ?>
							<span class="project-card__title-line project-card__title-line--emphasis"><?php the_title(); ?></span>
						<?php endif; ?>
					</h4>

					<?php if ( $summary ) : ?>
						<div class="project-card__excerpt">
							<p><?php echo esc_html( $summary ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="project-card__media-nav" hidden data-work-nav>
			<button class="project-card__expand-arrow" type="button" data-work-prev>
				<?php esc_html_e( 'Previous', 'nunlab-theme' ); ?>
			</button>
			<p class="project-card__expand-counter" data-work-counter></p>
			<button class="project-card__expand-arrow" type="button" data-work-next>
				<?php esc_html_e( 'Next', 'nunlab-theme' ); ?>
			</button>
		</div>
	</div>

	<section class="project-card__expand" hidden data-work-expand>
		<div class="project-card__expand-body">
			<h4 class="project-card__expand-title">
				<?php if ( '' !== $title_line_one && '' !== $title_line_two ) : ?>
					<span class="project-card__expand-title-line project-card__expand-title-line--primary"><?php echo esc_html( $title_line_one ); ?></span>
					<span class="project-card__expand-title-line project-card__expand-title-line--emphasis"><?php echo esc_html( $title_line_two ); ?></span>
				<?php elseif ( '' !== $title_line_one ) : ?>
					<span class="project-card__expand-title-line project-card__expand-title-line--emphasis"><?php echo esc_html( $title_line_one ); ?></span>
				<?php elseif ( '' !== $title_line_two ) : ?>
					<span class="project-card__expand-title-line project-card__expand-title-line--emphasis"><?php echo esc_html( $title_line_two ); ?></span>
				<?php else : ?>
					<span class="project-card__expand-title-line project-card__expand-title-line--emphasis"><?php the_title(); ?></span>
				<?php endif; ?>
			</h4>

			<?php if ( $subtitle ) : ?>
				<p class="project-card__expand-subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>

			<div class="project-card__expand-content">
				<?php echo $detail_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</section>
</article>

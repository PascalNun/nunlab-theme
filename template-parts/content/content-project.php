<?php
/**
 * Single Project Content
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$media_items    = nunlab_get_project_media_items( get_the_ID(), 'nunlab-project-large' );
$project_terms  = get_the_terms( get_the_ID(), 'project_type' );
$eyebrow        = __( 'Project', 'nunlab-theme' );
$title_parts    = nunlab_get_project_presentation_title_parts( get_the_ID() );
$detail_content = nunlab_render_project_editorial_content( get_the_content( null, false ) );

if ( $project_terms && ! is_wp_error( $project_terms ) ) {
	$eyebrow = implode( ' / ', wp_list_pluck( $project_terms, 'name' ) );
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--project' ); ?>>
	<header class="entry-header">
		<p class="archive-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<h1 class="entry-title entry-title--project-presentation">
			<?php echo nunlab_get_project_presentation_title_markup( $title_parts, 'entry-title__line' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</h1>
		<?php if ( has_excerpt() ) : ?>
			<p class="entry-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( $media_items ) : ?>
		<section class="project-gallery" data-project-gallery>
			<div class="project-gallery__viewport" data-project-gallery-viewport>
				<?php foreach ( $media_items as $index => $media_item ) : ?>
					<figure
						class="project-gallery__slide<?php echo 'youtube' === $media_item['type'] ? ' project-gallery__slide--video' : ''; ?>"
						<?php echo 0 === $index ? '' : ' hidden'; ?>
						data-project-gallery-slide
						data-project-gallery-title="<?php the_title_attribute(); ?>"
					>
						<?php if ( 'youtube' === $media_item['type'] ) : ?>
							<img
								class="project-gallery__image"
								src="<?php echo esc_url( (string) $media_item['poster_url'] ); ?>"
								alt="<?php echo esc_attr( (string) ( $media_item['alt'] ? $media_item['alt'] : get_the_title() ) ); ?>"
							/>
							<div class="project-gallery__video-frame" hidden data-project-gallery-video-frame></div>
							<button
								class="project-gallery__video-play"
								type="button"
								data-project-gallery-video-play
								data-embed-url="<?php echo esc_url( (string) $media_item['embed_url'] ); ?>"
								data-autoplay-embed-url="<?php echo esc_url( (string) $media_item['autoplay_embed_url'] ); ?>"
							>
								<?php esc_html_e( 'Play video', 'nunlab-theme' ); ?>
							</button>
						<?php else : ?>
							<img
								class="project-gallery__image"
								src="<?php echo esc_url( (string) $media_item['url'] ); ?>"
								alt="<?php echo esc_attr( (string) ( $media_item['alt'] ? $media_item['alt'] : get_the_title() ) ); ?>"
							/>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>

			<?php if ( count( $media_items ) > 1 ) : ?>
				<div class="project-gallery__controls">
					<button class="project-gallery__button" type="button" data-project-gallery-prev>
						<?php esc_html_e( 'Previous', 'nunlab-theme' ); ?>
					</button>
					<p class="project-gallery__counter" data-project-gallery-counter>
						<?php
						printf(
							/* translators: %d is the number of gallery images. */
							esc_html__( '1 / %d', 'nunlab-theme' ),
							count( $media_items )
						);
						?>
					</p>
					<button class="project-gallery__button" type="button" data-project-gallery-next>
						<?php esc_html_e( 'Next', 'nunlab-theme' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</section>
	<?php elseif ( has_post_thumbnail() ) : ?>
		<figure class="entry-media entry-media--project">
			<?php the_post_thumbnail( 'nunlab-project-large' ); ?>
		</figure>
	<?php endif; ?>

	<?php if ( '' !== $detail_content ) : ?>
		<div class="entry-content entry-content--project-editorial">
			<?php echo $detail_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php else : ?>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	<?php endif; ?>
</article>

<?php
/**
 * Homepage Work List Item
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project_excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
$summary         = $project_excerpt ? wp_trim_words( $project_excerpt, 18 ) : __( 'Open the case study to see the full project narrative.', 'nunlab-theme' );
$panel_id        = 'work-panel-' . get_the_ID();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'work-listing' ); ?> data-work-item>
	<button
		class="work-listing__trigger"
		type="button"
		aria-expanded="false"
		aria-controls="<?php echo esc_attr( $panel_id ); ?>"
		data-work-toggle
	>
		<span class="work-listing__title"><?php the_title(); ?></span>
		<span class="work-listing__summary"><?php echo esc_html( $summary ); ?></span>
		<span class="work-listing__chevron" aria-hidden="true"></span>
	</button>

	<div id="<?php echo esc_attr( $panel_id ); ?>" class="work-listing__panel" hidden data-work-panel>
		<div class="work-listing__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large', array( 'class' => 'work-listing__image' ) ); ?>
			<?php else : ?>
				<div class="work-listing__placeholder"></div>
			<?php endif; ?>
		</div>

		<div class="work-listing__body">
			<?php if ( $project_excerpt ) : ?>
				<p class="work-listing__excerpt"><?php echo esc_html( $project_excerpt ); ?></p>
			<?php endif; ?>

			<a class="button-link button-link--muted" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Open case study', 'nunlab-theme' ); ?>
			</a>
		</div>
	</div>
</article>

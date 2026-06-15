<?php
/**
 * Single Plugin Content
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$walkthrough_url   = esc_url_raw( (string) get_post_meta( get_the_ID(), 'nunlab_tool_walkthrough_url', true ) );
$walkthrough_id    = nunlab_get_youtube_video_id( $walkthrough_url );
$walkthrough_embed = $walkthrough_id ? nunlab_get_youtube_embed_url( $walkthrough_id, false ) : '';
$tool_icon_url     = esc_url_raw( (string) get_post_meta( get_the_ID(), 'nunlab_tool_icon_url', true ) );
$tool_links        = nunlab_get_tool_links( get_the_ID() );
$tool_sections     = nunlab_get_tool_content_sections( get_the_content( null, false ) );
$overview_content  = nunlab_render_tool_overview( $tool_sections );
$chapter_content   = nunlab_render_tool_chapters( $tool_sections );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--tool' ); ?>>
	<header class="entry-header tool-hero">
		<h1 class="entry-title tool-hero__title">
			<span><?php the_title(); ?></span>
			<?php if ( '' !== $tool_icon_url ) : ?>
				<img class="tool-hero__icon" src="<?php echo esc_url( $tool_icon_url ); ?>" alt="" aria-hidden="true" />
			<?php endif; ?>
		</h1>

		<?php if ( has_excerpt() ) : ?>
			<p class="entry-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( '' !== $walkthrough_embed ) : ?>
		<section class="tool-walkthrough" aria-label="<?php esc_attr_e( 'Walkthrough video', 'nunlab-theme' ); ?>">
			<div class="tool-walkthrough__frame">
				<iframe
					src="<?php echo esc_url( $walkthrough_embed ); ?>"
					title="<?php echo esc_attr( sprintf( __( '%s walkthrough video', 'nunlab-theme' ), get_the_title() ) ); ?>"
					loading="lazy"
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
					allowfullscreen
				></iframe>
			</div>
		</section>
	<?php elseif ( has_post_thumbnail() ) : ?>
		<figure class="entry-media entry-media--tool">
			<?php the_post_thumbnail( 'nunlab-project-large' ); ?>
		</figure>
	<?php endif; ?>

	<?php if ( $tool_links ) : ?>
		<nav class="tool-quick-links" aria-label="<?php esc_attr_e( 'Plugin links', 'nunlab-theme' ); ?>">
			<span class="tool-quick-links__label"><?php esc_html_e( 'Quick links:', 'nunlab-theme' ); ?></span>
			<span class="tool-quick-links__items">
				<?php foreach ( $tool_links as $tool_link ) : ?>
					<a href="<?php echo esc_url( $tool_link['url'] ); ?>" target="_blank" rel="noreferrer noopener">
						<?php echo esc_html( $tool_link['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</span>
		</nav>
	<?php endif; ?>

	<?php if ( '' !== $overview_content ) : ?>
		<div class="entry-content entry-content--tool-overview">
			<?php echo $overview_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== $chapter_content ) : ?>
		<div class="entry-content entry-content--tool-chapters">
			<?php echo $chapter_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>
</article>

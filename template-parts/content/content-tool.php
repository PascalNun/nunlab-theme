<?php
/**
 * Single Plugin Content
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tool_status       = trim( (string) get_post_meta( get_the_ID(), 'nunlab_tool_status', true ) );
$tool_version      = trim( (string) get_post_meta( get_the_ID(), 'nunlab_tool_version', true ) );
$walkthrough_url   = esc_url_raw( (string) get_post_meta( get_the_ID(), 'nunlab_tool_walkthrough_url', true ) );
$walkthrough_id    = nunlab_get_youtube_video_id( $walkthrough_url );
$walkthrough_embed = $walkthrough_id ? nunlab_get_youtube_embed_url( $walkthrough_id, false ) : '';
$tool_links        = nunlab_get_tool_links( get_the_ID() );
$chapter_content   = nunlab_render_tool_chapters( get_the_content( null, false ) );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--tool' ); ?>>
	<header class="entry-header tool-hero">
		<p class="archive-eyebrow"><?php esc_html_e( 'Plugin', 'nunlab-theme' ); ?></p>
		<h1 class="entry-title"><?php the_title(); ?></h1>

		<?php if ( has_excerpt() ) : ?>
			<p class="entry-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $tool_status || '' !== $tool_version ) : ?>
			<ul class="tool-meta" aria-label="<?php esc_attr_e( 'Plugin details', 'nunlab-theme' ); ?>">
				<?php if ( '' !== $tool_status ) : ?>
					<li><?php echo esc_html( $tool_status ); ?></li>
				<?php endif; ?>
				<?php if ( '' !== $tool_version ) : ?>
					<li><?php echo esc_html( $tool_version ); ?></li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $tool_links ) : ?>
			<nav class="tool-links" aria-label="<?php esc_attr_e( 'Plugin links', 'nunlab-theme' ); ?>">
				<?php foreach ( $tool_links as $tool_link ) : ?>
					<a href="<?php echo esc_url( $tool_link['url'] ); ?>" target="_blank" rel="noreferrer noopener">
						<?php echo esc_html( $tool_link['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
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

	<?php if ( '' !== $chapter_content ) : ?>
		<div class="entry-content entry-content--tool-chapters">
			<?php echo $chapter_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>
</article>

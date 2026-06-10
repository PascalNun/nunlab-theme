<?php
/**
 * Plugin Card
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tool_status  = trim( (string) get_post_meta( get_the_ID(), 'nunlab_tool_status', true ) );
$tool_version = trim( (string) get_post_meta( get_the_ID(), 'nunlab_tool_version', true ) );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'content-card content-card--tool tool-card' ); ?>>
	<a class="content-card__media tool-card__media" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large', array( 'class' => 'content-card__image tool-card__image' ) ); ?>
		<?php else : ?>
			<span class="tool-card__placeholder">
				<span><?php esc_html_e( 'N:UN', 'nunlab-theme' ); ?></span>
			</span>
		<?php endif; ?>
	</a>

	<p class="content-card__meta tool-card__meta">
		<span><?php esc_html_e( 'Plugin', 'nunlab-theme' ); ?></span>
		<?php if ( '' !== $tool_status ) : ?>
			<span><?php echo esc_html( $tool_status ); ?></span>
		<?php endif; ?>
		<?php if ( '' !== $tool_version ) : ?>
			<span><?php echo esc_html( $tool_version ); ?></span>
		<?php endif; ?>
	</p>

	<h2 class="content-card__title tool-card__title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>

	<?php if ( has_excerpt() ) : ?>
		<div class="content-card__excerpt tool-card__excerpt">
			<?php the_excerpt(); ?>
		</div>
	<?php endif; ?>

	<a class="content-card__link tool-card__link" href="<?php the_permalink(); ?>">
		<?php esc_html_e( 'Open plugin', 'nunlab-theme' ); ?>
	</a>
</article>

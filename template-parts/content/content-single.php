<?php
/**
 * Single Post Content
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>
	<header class="entry-header">
		<p class="archive-eyebrow"><?php esc_html_e( 'Notebook', 'nunlab-theme' ); ?></p>
		<div class="entry-meta entry-meta--notebook">
			<?php nunlab_posted_on(); ?>
		</div>
		<h1 class="entry-title"><?php the_title(); ?></h1>
	</header>

	<div class="entry-layout entry-layout--notebook<?php echo has_post_thumbnail() ? ' entry-layout--with-media' : ''; ?>">
		<div class="entry-content entry-content--notebook">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="entry-media entry-media--notebook">
					<?php the_post_thumbnail( 'large' ); ?>
				</figure>
			<?php endif; ?>

			<?php the_content(); ?>
		</div>
	</div>
</article>

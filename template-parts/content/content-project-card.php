<?php
/**
 * Project Card
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project_excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'project-card' ); ?>>
	<a class="project-card__frame" href="<?php the_permalink(); ?>">
		<div class="project-card__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large', array( 'class' => 'project-card__image' ) ); ?>
			<?php else : ?>
				<span class="project-card__placeholder"></span>
			<?php endif; ?>

			<span class="project-card__overlay"></span>

			<div class="project-card__body">
				<h2 class="project-card__title"><?php the_title(); ?></h2>

				<?php if ( $project_excerpt ) : ?>
					<div class="project-card__excerpt">
						<p><?php echo esc_html( wp_trim_words( $project_excerpt, 28 ) ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</a>
</article>

<?php
/**
 * Content Card
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_type        = get_post_type();
$post_type_object = $post_type ? get_post_type_object( $post_type ) : null;
$link_label       = 'post' === $post_type ? __( 'Read entry', 'nunlab-theme' ) : ( 'project' === $post_type ? __( 'Open project', 'nunlab-theme' ) : __( 'Open item', 'nunlab-theme' ) );
$meta_label       = '';
$image_markup     = '';

if ( 'post' === $post_type ) {
	ob_start();
	nunlab_posted_on();
	$meta_label = trim( ob_get_clean() );
} elseif ( 'project' === $post_type ) {
	$project_terms = get_the_terms( get_the_ID(), 'project_type' );

	if ( $project_terms && ! is_wp_error( $project_terms ) ) {
		$meta_label = implode(
			' / ',
			wp_list_pluck( $project_terms, 'name' )
		);
	}
} elseif ( $post_type_object ) {
	$meta_label = $post_type_object->labels->singular_name;
}

if ( has_post_thumbnail() ) {
	$image_markup = get_the_post_thumbnail( get_the_ID(), 'medium_large', array( 'class' => 'content-card__image' ) );
} elseif ( 'project' === $post_type ) {
	$project_image = nunlab_get_project_primary_image( get_the_ID(), 'medium_large' );

	if ( ! empty( $project_image['url'] ) ) {
		$image_markup = sprintf(
			'<img class="content-card__image" src="%1$s" alt="%2$s" />',
			esc_url( (string) $project_image['url'] ),
			esc_attr( (string) ( $project_image['alt'] ? $project_image['alt'] : get_the_title() ) )
		);
	}
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'content-card' ); ?>>
	<?php if ( $image_markup ) : ?>
		<a class="content-card__media" href="<?php the_permalink(); ?>">
			<?php echo wp_kses_post( $image_markup ); ?>
		</a>
	<?php endif; ?>

	<p class="content-card__meta">
		<?php echo wp_kses_post( $meta_label ); ?>
	</p>
	<h2 class="content-card__title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>
	<div class="content-card__excerpt">
		<?php the_excerpt(); ?>
	</div>
	<a class="content-card__link" href="<?php the_permalink(); ?>">
		<?php echo esc_html( $link_label ); ?>
	</a>
</article>

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
$link_labels      = array(
	'post'    => __( 'Read entry', 'nunlab-theme' ),
	'project' => __( 'Open project', 'nunlab-theme' ),
	'tool'    => __( 'Open tool', 'nunlab-theme' ),
);
$link_label       = isset( $link_labels[ $post_type ] ) ? $link_labels[ $post_type ] : __( 'Open item', 'nunlab-theme' );
$card_classes     = array( 'content-card' );
$media_classes    = array( 'content-card__media' );
$image_classes    = array( 'content-card__image' );
$meta_classes     = array( 'content-card__meta' );
$meta_label       = '';
$meta_parts       = array();
$image_markup     = '';

if ( 'tool' === $post_type ) {
	$card_classes[]  = 'content-card--tool';
	$card_classes[]  = 'tool-card';
	$media_classes[] = 'tool-card__media';
	$image_classes[] = 'tool-card__image';
	$meta_classes[]  = 'tool-card__meta';
}

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
} elseif ( 'tool' === $post_type ) {
	$meta_parts = nunlab_get_tool_meta_parts( get_the_ID() );
	$meta_label = implode( ' / ', $meta_parts );
} elseif ( $post_type_object ) {
	$meta_label = $post_type_object->labels->singular_name;
}

if ( has_post_thumbnail() ) {
	$image_markup = get_the_post_thumbnail( get_the_ID(), 'medium_large', array( 'class' => implode( ' ', $image_classes ) ) );
} elseif ( 'project' === $post_type ) {
	$project_image = nunlab_get_project_primary_image( get_the_ID(), 'medium_large' );

	if ( ! empty( $project_image['url'] ) ) {
		$image_markup = sprintf(
			'<img class="content-card__image" src="%1$s" alt="%2$s" />',
			esc_url( (string) $project_image['url'] ),
			esc_attr( (string) ( $project_image['alt'] ? $project_image['alt'] : get_the_title() ) )
		);
	}
} elseif ( 'tool' === $post_type ) {
	$image_markup = '<span class="tool-card__placeholder"><span>' . esc_html__( 'N:UN', 'nunlab-theme' ) . '</span></span>';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $card_classes ); ?>>
	<?php if ( $image_markup ) : ?>
		<a class="<?php echo esc_attr( implode( ' ', $media_classes ) ); ?>" href="<?php the_permalink(); ?>">
			<?php echo wp_kses_post( $image_markup ); ?>
		</a>
	<?php endif; ?>

	<p class="<?php echo esc_attr( implode( ' ', $meta_classes ) ); ?>">
		<?php if ( 'tool' === $post_type && $meta_parts ) : ?>
			<?php foreach ( $meta_parts as $meta_part ) : ?>
				<span><?php echo esc_html( $meta_part ); ?></span>
			<?php endforeach; ?>
		<?php else : ?>
			<?php echo wp_kses_post( $meta_label ); ?>
		<?php endif; ?>
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

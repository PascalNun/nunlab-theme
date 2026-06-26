<?php
/**
 * Front Page Practice Section
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$notebook_page_id = (int) get_option( 'page_for_posts' );
$notebook_url     = $notebook_page_id ? get_permalink( $notebook_page_id ) : home_url( '/notebook/' );

$practice_lenses = array(
	array(
		'title' => __( 'Architecture', 'nunlab-theme' ),
		'text'  => __( 'Spatial work, built environments, and the discipline of form, material, sequence, and atmosphere.', 'nunlab-theme' ),
		'url'   => '#work',
		'label' => __( 'View work', 'nunlab-theme' ),
	),
	array(
		'title' => __( 'Design', 'nunlab-theme' ),
		'text'  => __( 'Identity, systems, interfaces, and visual decisions that need precision more than noise.', 'nunlab-theme' ),
		'url'   => '#work',
		'label' => __( 'View work', 'nunlab-theme' ),
	),
	array(
		'title' => __( 'Research', 'nunlab-theme' ),
		'text'  => __( 'Urban thinking, observations, notes, and essays that connect practice to context and ideas.', 'nunlab-theme' ),
		'url'   => $notebook_url,
		'label' => __( 'Read notes', 'nunlab-theme' ),
	),
	array(
		'title' => __( 'Tools', 'nunlab-theme' ),
		'text'  => __( 'Plugins, workflows, and practical systems that support making rather than distract from it.', 'nunlab-theme' ),
		'url'   => home_url( '/tools/' ),
		'label' => __( 'Open tools', 'nunlab-theme' ),
	),
);
?>
<section class="home-section">
	<div class="home-section__inner">
		<div class="section-heading">
			<div>
				<p class="archive-eyebrow"><?php esc_html_e( 'Practice', 'nunlab-theme' ); ?></p>
				<h2 class="section-heading__title"><?php esc_html_e( 'Four lenses shape the practice without flattening everything into one category.', 'nunlab-theme' ); ?></h2>
			</div>
		</div>

		<div class="practice-grid">
			<?php foreach ( $practice_lenses as $lens ) : ?>
				<a class="practice-card practice-card--link" href="<?php echo esc_url( $lens['url'] ); ?>">
					<h3 class="practice-card__title"><?php echo esc_html( $lens['title'] ); ?></h3>
					<p><?php echo esc_html( $lens['text'] ); ?></p>
					<span class="practice-card__action"><?php echo esc_html( $lens['label'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

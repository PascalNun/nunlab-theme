<?php
/**
 * Front Page Practice Section
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="home-section">
	<div class="home-section__inner">
		<div class="section-heading">
			<div>
				<p class="archive-eyebrow"><?php esc_html_e( 'Practice', 'nunlab-theme' ); ?></p>
				<h2 class="section-heading__title"><?php esc_html_e( 'Four lenses shape the platform without flattening everything into one category.', 'nunlab-theme' ); ?></h2>
			</div>
		</div>

		<div class="practice-grid">
			<article class="practice-card">
				<h3 class="practice-card__title"><?php esc_html_e( 'Architecture', 'nunlab-theme' ); ?></h3>
				<p><?php esc_html_e( 'Spatial work, built environments, and the discipline of form, material, sequence, and atmosphere.', 'nunlab-theme' ); ?></p>
			</article>
			<article class="practice-card">
				<h3 class="practice-card__title"><?php esc_html_e( 'Design', 'nunlab-theme' ); ?></h3>
				<p><?php esc_html_e( 'Identity, systems, interfaces, and visual decisions that need precision more than noise.', 'nunlab-theme' ); ?></p>
			</article>
			<article class="practice-card">
				<h3 class="practice-card__title"><?php esc_html_e( 'Research', 'nunlab-theme' ); ?></h3>
				<p><?php esc_html_e( 'Urban thinking, observations, notes, and essays that connect practice to context and ideas.', 'nunlab-theme' ); ?></p>
			</article>
			<article class="practice-card">
				<h3 class="practice-card__title"><?php esc_html_e( 'Tools', 'nunlab-theme' ); ?></h3>
				<p><?php esc_html_e( 'Plugins, workflows, and practical systems that support making rather than distract from it.', 'nunlab-theme' ); ?></p>
			</article>
		</div>
	</div>
</section>

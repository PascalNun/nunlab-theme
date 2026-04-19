<?php
/**
 * Front Page Featured Projects Section
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project_sections = nunlab_get_project_archive_sections(
	array(
		'preferred_order'    => array( 'concept', 'work', 'research' ),
		'hide_empty'         => false,
		'include_additional' => false,
	)
);

$work_eyebrow = nunlab_get_front_page_field( 'nunlab_work_eyebrow', __( 'Work', 'nunlab-theme' ) );
$work_heading = nunlab_get_front_page_field( 'nunlab_work_heading', __( 'A structured index of selected work, research, and ideas.', 'nunlab-theme' ) );
?>
<section id="work" class="home-section home-section--work">
	<div class="home-section__inner">
		<div class="section-heading">
			<div>
				<p class="archive-eyebrow"><?php echo esc_html( $work_eyebrow ); ?></p>
				<h2 class="section-heading__title"><?php echo esc_html( $work_heading ); ?></h2>
			</div>
		</div>

		<div class="work-directory">
			<?php foreach ( $project_sections as $project_section ) : ?>
				<?php
				$section_query = new WP_Query(
					array(
						'post_type'      => 'project',
						'posts_per_page' => -1,
						'no_found_rows'  => true,
						'orderby'        => array(
							'menu_order' => 'ASC',
							'date'       => 'DESC',
						),
						'tax_query'      => array(
							array(
								'taxonomy' => 'project_type',
								'field'    => 'term_id',
								'terms'    => (int) $project_section->term_id,
							),
						),
					)
				);
				?>
				<section class="work-directory__group">
					<header class="work-directory__group-header">
						<h3 class="work-directory__title"><?php echo esc_html( $project_section->name ); ?></h3>
					</header>

					<?php if ( $section_query->have_posts() ) : ?>
						<div class="work-preview-grid" data-work-group>
							<?php
							while ( $section_query->have_posts() ) :
								$section_query->the_post();
								get_template_part( 'template-parts/content/content', 'project-directory-card' );
							endwhile;
							?>
						</div>
					<?php else : ?>
						<p class="work-directory__empty"><?php esc_html_e( 'Projects in this track will appear here as they are published.', 'nunlab-theme' ); ?></p>
					<?php endif; ?>

					<?php wp_reset_postdata(); ?>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</section>

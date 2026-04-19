<?php
/**
 * Project Archive Template
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project_sections = nunlab_get_project_archive_sections();

get_header();
?>

<main id="primary" class="site-main site-main--work-archive">
	<div class="work-archive">
		<header class="work-archive__toolbar">
			<h1 class="screen-reader-text"><?php post_type_archive_title(); ?></h1>
			<div class="work-filter" aria-hidden="true">
				<span class="work-filter__chip">
					<?php esc_html_e( 'Filter None', 'nunlab-theme' ); ?>
					<span class="work-filter__chevron"></span>
				</span>
			</div>
		</header>

		<?php if ( ! empty( $project_sections ) ) : ?>
			<?php foreach ( $project_sections as $project_section ) : ?>
				<?php
				$section_query = new WP_Query(
					array(
						'post_type'     => 'project',
						'nopaging'      => true,
						'no_found_rows' => true,
						'orderby'       => array(
							'menu_order' => 'ASC',
							'date'       => 'DESC',
						),
						'tax_query'     => array(
							array(
								'taxonomy' => 'project_type',
								'field'    => 'term_id',
								'terms'    => (int) $project_section->term_id,
							),
						),
					)
				);
				?>

				<?php if ( $section_query->have_posts() ) : ?>
					<section class="work-section">
						<h2 class="work-section__title"><?php echo esc_html( $project_section->name ); ?></h2>

						<div class="work-grid">
							<?php
							while ( $section_query->have_posts() ) :
								$section_query->the_post();
								get_template_part( 'template-parts/content/content', 'project-card' );
							endwhile;
							?>
						</div>
					</section>
				<?php endif; ?>

				<?php wp_reset_postdata(); ?>
			<?php endforeach; ?>
		<?php elseif ( have_posts() ) : ?>
			<section class="work-section">
				<h2 class="work-section__title"><?php esc_html_e( 'Work', 'nunlab-theme' ); ?></h2>

				<div class="work-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/content', 'project-card' );
					endwhile;
					?>
				</div>
			</section>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
		<?php endif; ?>

		<div class="work-archive__line" aria-hidden="true"></div>
	</div>
</main>

<?php
get_footer();

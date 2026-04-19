<?php
/**
 * Footer Template
 *
 * Main footer wrapper.
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
		<?php get_template_part( 'template-parts/site/footer' ); ?>
		<?php get_template_part( 'template-parts/site/search-overlay' ); ?>
	</div>
	<?php wp_footer(); ?>
</body>
</html>

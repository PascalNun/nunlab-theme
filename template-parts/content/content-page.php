<?php
/**
 * Page Content
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$entry_classes = array( 'entry', 'entry--page' );

if ( is_page( array( 'legal-notice', 'privacy-policy' ) ) ) {
	$entry_classes[] = 'entry--legal';
}

if ( is_page( 'contact' ) ) {
	$entry_classes[] = 'entry--contact';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $entry_classes ); ?>>
	<header class="entry-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>
	</header>

	<div class="entry-content">
		<?php the_content(); ?>
	</div>
</article>

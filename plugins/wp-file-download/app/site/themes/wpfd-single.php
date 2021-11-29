<?php
/**
 * WP File Download Single Template
 *
 * @package    WP File Download
 * @subpackage Inject file in wordpress search result
 * @since      11.2017
 */

get_header(); ?>

<?php
// Start the Loop.
while (have_posts()) :
    the_post();
    wpfdTheContent();
endwhile;
?>

<?php
get_footer();

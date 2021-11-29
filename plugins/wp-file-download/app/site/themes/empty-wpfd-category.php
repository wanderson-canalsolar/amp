<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */
get_header(); ?>

    <section id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <header class="page-header">
                <?php
                the_archive_title('<h1 class="page-title">', '</h1>');
                ?>
            </header><!-- .page-header -->
            <div class="entry-content">
                <?php esc_html_e('No files found', 'wpfd'); ?>
            </div><!-- .entry-content -->
        </main><!-- .site-main -->
    </section><!-- .content-area -->
<?php get_footer(); ?>

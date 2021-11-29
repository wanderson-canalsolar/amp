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
            <?php if (have_posts()) : ?>
                <header class="page-header">
                    <?php
                    the_archive_title('<h1 class="page-title">', '</h1>');
                    ?>
                </header><!-- .page-header -->
                <div class="entry-content">
                    <?php wpfd_detail_category(); ?>
                </div><!-- .entry-content -->
                <?php
            // If no content, include the "No posts found" template.
            else :
                get_template_part('content', 'none');
            endif;
            ?>
        </main><!-- .site-main -->
    </section><!-- .content-area -->
<?php get_footer(); ?>

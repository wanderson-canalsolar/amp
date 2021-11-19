<?php 
// Loading the Components
//Search
add_amp_theme_support('AMP-search');
//Logo
add_amp_theme_support('AMP-logo');
//Social Icons
add_amp_theme_support('AMP-social-icons');
//Menu
add_amp_theme_support('AMP-menu');
//Call Now
add_amp_theme_support('AMP-call-now');
//Sidebar
add_amp_theme_support('AMP-sidebar');
// Featured Image
add_amp_theme_support('AMP-featured-image');
//Author box
add_amp_theme_support('AMP-author-box');
//Loop
add_amp_theme_support('AMP-loop');
// Categories and Tags list
add_amp_theme_support('AMP-categories-tags');
// Comments
add_amp_theme_support('AMP-comments');
//Post Navigation
add_amp_theme_support('AMP-post-navigation');
// Related Posts
add_amp_theme_support('AMP-related-posts');
// Post Pagination
add_amp_theme_support('AMP-post-pagination');

amp_font('https://fonts.googleapis.com/css?family=Source+Serif+Pro:400,600|Source+Sans+Pro:400,700');

// Creating Shortcodes to display posts from category
function diwp_shortcode_display_post($attr, $content = null){
 
    global $post;
 
    // Defining Shortcode's Attributes
    $shortcode_args = shortcode_atts(
                        array(
                                'cat'     => '',
                                'num'     => '5',
                                'order'  => 'desc'
                        ), $attr);    
     
    // array with query arguments
        $args = array(
            'category_name'   => $shortcode_args['cat'],
            'posts_per_page'   => $shortcode_args['num'],
            'order'                    => $shortcode_args['order'],
         );
                     
 
     
    $recent_posts = get_posts($args);
 
    $output = '<ul>';
 
    foreach ($recent_posts as $post) :
         
        setup_postdata($post);
 
        $output .= '<li><a href="'.get_permalink().'">'.get_the_title().' '.get_the_post_thumbnail( ).' '.get_the_excerpt(  ).'<a></li>';    
 
 
    endforeach;    
     
    wp_reset_postdata();
 
    $output .= '</ul>';
     
    return $output;
 
}
 
add_shortcode( 'diwp_recent_posts', 'diwp_shortcode_display_post' );
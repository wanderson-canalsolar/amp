<?php 

add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);

function salient_child_enqueue_styles() {

    wp_enqueue_style( 'owl', get_stylesheet_directory_uri() . '/assets/assets/owl.carousel.css', 0.1);
    wp_enqueue_style( 'owl-default', get_stylesheet_directory_uri() . '/assets/assets/owl.theme.default.css');
    wp_register_script( 'owl', get_stylesheet_directory_uri() . '/assets/owl.carousel.js', array( 'jquery' ), 2.35, true );
    wp_register_script( 'rdstation', 'https://d335luupugsy2.cloudfront.net/js/rdstation-forms/stable/rdstation-forms.min.js', array( 'jquery' ), 3.2, true );
    wp_register_script( 'custom', get_stylesheet_directory_uri() . '/custom.js', array( 'jquery', 'rdstation' ), 3.4, true );
    wp_enqueue_script('owl');
    wp_enqueue_script('custom');
    wp_enqueue_style( 'child', get_stylesheet_directory_uri() . '/style.css', '',6.6);
    wp_enqueue_style('font-awesome-2',get_template_directory_uri() . '/css/font-awesome.min.css' );
}




function wpb_widgets_init() {

    register_sidebar( array(
        'name'          => 'Potencias Mobile',
        'id'            => 'header-1',
        'before_widget' => '<div class="chw-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="chw-title">',
        'after_title'   => '</h2>',
    ) );
    register_sidebar( array(
        'name'          => 'Potencias Desktop',
        'id'            => 'header-2',
        'before_widget' => '<div class="chw-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="chw-title">',
        'after_title'   => '</h2>',
    ) );
    register_sidebar( array(
        'name'          => 'Header 3',
        'id'            => 'header-3',
        'before_widget' => '<div class="chw-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="chw-title">',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => 'Related Sidebar',
        'id'            => 'related-sidebar',
        'before_widget' => '<div class="related-sidebar">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="related-sidebar-title">',
        'after_title'   => '</h2>',
    ) );

}
add_action( 'widgets_init', 'wpb_widgets_init' );


function searchfilter($query) {

    if ($query->is_search && !is_admin() ) {
        $query->set('post_per_page',12);
    }

    return $query;
}

add_filter('pre_get_posts','searchfilter');


function resumo( $limit ) {

    if ( has_excerpt() ) {
        $the_excerpt = get_the_excerpt();
        $the_excerpt = preg_replace( '/\[[^\]]+\]/', '', $the_excerpt );  // strip shortcodes, keep shortcode content
        return wp_trim_words( $the_excerpt, $limit );
    } else {
       return "";
    }
}


/* WC: Avoid alert message for individual sold product already in cart. */
add_action( 'woocommerce_add_to_cart_validation', 'softeo_woocommerce_add_to_cart_validation', 11, 2 ); 
function softeo_woocommerce_add_to_cart_validation( $passed, $product_id ) {
	$product = wc_get_product( $product_id );
	if( $product->get_sold_individually()                                              // if individual product
	&& WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $product_id ) ) // if in the cart
	&& $product->is_purchasable()                                                      // if conditions
	&& $product->is_in_stock() ) {
		wp_safe_redirect( wc_get_checkout_url() );
		exit();
    }
    return $passed;
}

function admin_post_list_add_export_button( $which ) {
    global $typenow;
  
    if ( 'post' === $typenow && 'top' === $which ) {
        ?>
        <input type="submit" name="export_all_posts" class="button button-primary" value="<?php _e('Exportar posts'); ?>" />
        <?php
    }
}
 
add_action( 'manage_posts_extra_tablenav', 'admin_post_list_add_export_button', 20, 1 );

function func_export_all_posts() {
    if(isset($_GET['export_all_posts'])) {
        $arg = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
  
        global $post;
        $arr_post = get_posts($arg);
        if ($arr_post) {
  
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="wp-posts.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
  
            $file = fopen('php://output', 'w');
  
            fputcsv($file, array('Date','Post Title', 'URL', 'Categories', 'Tags'));
  
            foreach ($arr_post as $post) {
                setup_postdata($post);
                  
                $categories = get_the_category();
                $cats = array();
                if (!empty($categories)) {
                    foreach ( $categories as $category ) {
                        $cats[] = $category->name;
                    }
                }
  
                $post_tags = get_the_tags();
                $tags = array();
                if (!empty($post_tags)) {
                    foreach ($post_tags as $tag) {
                        $tags[] = $tag->name;
                    }
                }
  
                fputcsv($file, array(get_the_date('d/m/Y'), get_the_title(), get_the_permalink(), implode(",", $cats), implode(",", $tags)));
            }
  
            exit();
        }
    }
}
 
add_action( 'init', 'func_export_all_posts' );

//PRELOAD REQUESTS
/*preload tff fonts*/

function enqueue_scripts_ttf() {
	wp_enqueue_style('ttf-handle', '/wp-content/themes/salient/css/fonts/icomoon.ttf', array(), null);
  }
  add_action('wp_enqueue_scripts', 'enqueue_scripts_ttf');
  
  
  function style_loader_ttf($html, $handle) {
	if($handle === 'ttf-handle') {
  return str_replace("rel='stylesheet'", "rel='preload' as='font' type='font/ttf' crossorigin='anonymous'", $html);
	}
	return $html;
  }
  add_filter('style_loader_tag', 'style_loader_ttf', 10, 2);
  
  /*code ends */
  
  
  /*preload woff */
  
  function enqueue_scripts_woff() {
	wp_enqueue_style('woff-handle', '/wp-content/themes/salient/css/fonts/icomoon.woff', array(), null);
  }
  add_action('wp_enqueue_scripts', 'enqueue_scripts_woff');
  
  
  function style_loader_woff($html, $handle) {
	if($handle === 'woff-handle') {
  return str_replace("rel='stylesheet'", "rel='preload' as='font' type='font/woff2' crossorigin='anonymous'", $html);
	}
	return $html;
  }
  add_filter('style_loader_tag', 'style_loader_woff', 10, 2);
  
  /*code ends */


/* MOSTRAR LEGENDA IMAGEM DESTACADA*/

  add_filter( 'post_thumbnail_html', 'custom_add_post_thumbnail_caption',10,5 );
 
function custom_add_post_thumbnail_caption($html, $post_id, $post_thumbnail_id, $size, $attr) {
    if(is_page()){
        echo '<style>
        .wp-caption .wp-caption-text, .row .col .wp-caption .wp-caption-text{
            display:none;
        }
        </style>';
    }else{
        echo '<style>
        .wp-caption .wp-caption-text, .row .col .wp-caption .wp-caption-text{
            display:block;
        }
        </style>';
    }
 
if( $html == '' ) { 
  
    return $html;
  
} else {
  
    $out = '';
  
    $thumbnail_image = get_posts(array('p' => $post_thumbnail_id, 'post_type' => 'attachment'));
  
    if ($thumbnail_image && isset($thumbnail_image[0])) {
  
        $image = wp_get_attachment_image_src($post_thumbnail_id, $size);
 
        if($thumbnail_image[0]->post_excerpt) 
            $out .= '<div class="wp-caption thumb-caption">';
  
        $out .= $html;
  
        if($thumbnail_image[0]->post_excerpt) 
            $out .= '<p class="wp-caption-text thumb-caption-text">'.$thumbnail_image[0]->post_excerpt.'</p></div>';
   
    }
 
    return $out;
   
}
}
/*FIM*/

?>
<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

$app = Application::getInstance('Wpfd');

load_plugin_textdomain(
    'wpfd',
    null,
    dirname(plugin_basename(WPFD_PLUGIN_FILE)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'languages'
);

/**
 * Check is rest api call
 *
 * @return boolean
 */
function wpfd_is_rest_api_request()
{
    if (empty($_SERVER['REQUEST_URI'])) {
        // Probably a CLI request
        return false;
    }

    $rest_prefix         = trailingslashit(rest_get_url_prefix());
    $is_rest_api_request = ( false !== strpos($_SERVER['REQUEST_URI'], $rest_prefix) );

    return apply_filters('wpfd_is_rest_api_request', $is_rest_api_request);
}

add_action('init', 'wpfd_session_start', -1);
/**
 * Start new or resume existing session
 *
 * @return void
 */
function wpfd_session_start()
{
    if (!wpfd_is_rest_api_request() && is_user_logged_in()) {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }
}

add_action('wp_ajax_nopriv_wpfd', 'wpfd_ajax');
add_action('wp_ajax_wpfd', 'wpfd_ajax');
add_action('init', 'wpfd_register_post_type');
add_filter('woocommerce_prevent_admin_access', 'wpfd_disable_woo_login', 10, 1);
add_filter('posts_where', 'wpfd_files_query', 100, 2);
add_action('media_buttons', 'wpfd_button');
// Enable shortcodes in text widgets
add_filter('widget_text', 'do_shortcode');
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('wpfd-search_filter', plugins_url('app/site/assets/css/search_filter.css', WPFD_PLUGIN_FILE));
});
add_shortcode('wpfd_upload', 'wpfd_upload_shortcode');
add_action('wp_enqueue_scripts', 'wpfd_register_assets');
/**
 * Method execute ajax
 *
 * @return void
 */
function wpfd_ajax()
{
    $application = Application::getInstance('Wpfd');
    $path_wpfdbase = $application->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
    $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
    require_once $path_wpfdbase;
    $application->execute('file.download');
}

if (!get_option('_wpfd_import_notice_flag', false)) {
    $application = Application::getInstance('Wpfd');
    $path_wpfdtool = $application->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
    $path_wpfdtool .= DIRECTORY_SEPARATOR . 'WpfdTool.php';
    require_once $path_wpfdtool;
}


/**
 * Search query
 *
 * @param string $where Where
 * @param object $ob    Ob
 *
 * @return string
 */
function wpfd_files_query($where, $ob)
{
    global $wpdb;
    $postTypes = $ob->get('post_type');
    if (is_array($postTypes) && !empty($postTypes) && in_array('wpfd_file', $postTypes)) {
        $where .= ' AND ' . $wpdb->prefix . "posts.post_date <= '" . current_time('mysql') . "'";
    }
    return $where;
}

/**
 * Register post type
 *
 * @return void
 */
function wpfd_register_post_type()
{
    $labels = array(
        'label' => esc_html__('WP File Download', 'wpfd'),
        'rewrite' => array('slug' => 'wp-file-download'),
        'menu_name' => esc_html__('WP File Download', 'wpfd'),
        'hierarchical' => true,
        'show_in_nav_menus' => true,
        'show_ui' => false
    );

    register_taxonomy('wpfd-category', 'wpfd_file', $labels);
    $labels = array(
        'name' => _x('Tags', 'wpfd'), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain -- Domain is optional
        'singular_name' => _x('Tag', 'wpfd'), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain -- Domain is optional
        'search_items' => esc_html__('Search Tags', 'wpfd'),
        'popular_items' => esc_html__('Popular Tags', 'wpfd'),
        'all_items' => esc_html__('All Tags', 'wpfd'),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => esc_html__('Edit Tag', 'wpfd'),
        'update_item' => esc_html__('Update Tag', 'wpfd'),
        'add_new_item' => esc_html__('Add New Tag', 'wpfd'),
        'new_item_name' => esc_html__('New Tag Name', 'wpfd'),
        'separate_items_with_commas' => esc_html__('Separate tags with commas', 'wpfd'),
        'add_or_remove_items' => esc_html__('Add or remove tags', 'wpfd'),
        'choose_from_most_used' => esc_html__('Choose from the most used tags', 'wpfd'),
        'not_found' => esc_html__('No tags found.', 'wpfd'),
        'menu_name' => esc_html__('Tags', 'wpfd'),
    );

    $args = array(
        'public' => false,
        'rewrite' => false,
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => false,
        'show_admin_column' => false,
        'query_var' => false,
    );

    register_taxonomy('wpfd-tag', 'wpfd_file', $args);

    $publicWpfdFile = true;
    $config = get_option('_wpfd_global_search_config', null);
    if (!is_null($config) && isset($config['include_global_search']) && intval($config['include_global_search']) === 0) {
        $publicWpfdFile = false;
    }
    register_post_type(
        'wpfd_file',
        array(
            'labels' => array(
                'name' => esc_html__('Files', 'wpfd'),
                'singular_name' => esc_html__('File', 'wpfd')
            ),
            'public' => $publicWpfdFile,
            'show_ui' => false,
            'show_in_nav_menu' => false,
            'exclude_from_search' => true,
            'taxonomies' => array('wpfd-category'),
            'has_archive' => false,
            'rewrite' => array('slug' => 'wpfd_file', 'with_front' => true),
        )
    );
}

/**
 * Disable woocommerce login when downloading a file
 *
 * @param boolean $bool Return value
 *
 * @return boolean
 */
function wpfd_disable_woo_login($bool)
{
    return false;
}


/**
 * Display category
 *
 * @return void
 */
function wpfd_detail_category()
{

    $term = get_queried_object();
    if ((string)$term->taxonomy !== 'wpfd-category') {
        return;
    }

    wp_enqueue_style(
        'wpfd-front',
        plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );

    $application = Application::getInstance('Wpfd');
    $path_wpfdbase = $application->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
    $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
    require_once $path_wpfdbase;

    $modelFiles = Model::getInstance('filesfront');
    $modelCategories = Model::getInstance('categoriesfront');
    $modelCategory = Model::getInstance('categoryfront');
    $category = $modelCategory->getCategory($term->term_id);

    $orderCol = Utilities::getInput('orderCol', 'GET', 'none');
    $orderDir = Utilities::getInput('orderDir', 'GET', 'none');
    $ordering = $orderCol !== null ? $orderCol : $category->ordering;
    $orderingdir = $orderDir !== null ? $orderDir : $category->orderingdir;
    $files = $modelFiles->getFiles($term->term_id, $ordering, $orderingdir);
    $categories = $modelCategories->getCategories($term->term_id);
    $themename = $category->params['theme'];
    $params = $category->params;
    $themefile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-';
    $themefile .= strtolower($themename) . DIRECTORY_SEPARATOR . 'theme.php';

    if (file_exists($themefile)) {
        include_once $themefile;
    }

    $class = 'WpfdTheme' . ucfirst(str_replace('_', '', $themename));
    $theme = new $class();
    $options = array('files' => $files, 'category' => $category, 'categories' => $categories, 'params' => $params);

    echo $theme->showCategory($options); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escape in showCategory
}

/**
 * View file
 *
 * @return void
 */
function wpfd_file_viewer()
{

    $post_type = get_query_var('post_type');
    if ($post_type !== 'wpfd_file') {
        return;
    }

    wp_enqueue_style(
        'wpfd-front',
        plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );

    $id = get_the_ID();
    $catid = Utilities::getInt('catid');
    $ext = Utilities::getInput('ext', 'GET', 'string');
    $mediaType = Utilities::getInput('type', 'GET', 'string');

    $app = Application::getInstance('Wpfd');
    $downloadLink = wpfd_sanitize_ajax_url($app->getAjaxUrl()). '&task=file.download&wpfd_file_id=' . $id . '&wpfd_category_id=';
    $downloadLink .= $catid . '&preview=1';
    $mineType = WpfdHelperFile::mimeType(strtolower($ext));
    wp_enqueue_script('jquery');
    wp_enqueue_style(
        'wpfd-mediaelementplayer',
        plugins_url('app/site/assets/css/mediaelementplayer.min.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_enqueue_script(
        'wpfd-mediaelementplayer',
        plugins_url('app/site/assets/js/mediaelement-and-player.js', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );


    $themefile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'frontviewer';
    $themefile .= DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'default.php';
    if (file_exists($themefile)) {
        include_once $themefile;
    }
}

// Add assets to front
add_action('wp_enqueue_scripts', function () {
    Application::getInstance('Wpfd');
    $modelConfig = Model::getInstance('configfront');
    $config = $modelConfig->getGlobalConfig();

    if ((int)$config['enablewpfd'] !== 1 || is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        return;
    }

    wp_enqueue_script('wpfd-mediaTable', plugins_url('app/site/themes/wpfd-table/js/jquery.mediaTable.js', WPFD_PLUGIN_FILE), array('jquery'));
    wp_enqueue_style('wpfd-modal', plugins_url('app/admin/assets/css/leanmodal.css', WPFD_PLUGIN_FILE));
    wp_enqueue_script('wpfd-modal', plugins_url('app/admin/assets/js/jquery.leanModal.min.js', WPFD_PLUGIN_FILE), array('jquery'));
    wp_enqueue_script('wpfd-modal-init', plugins_url('app/site/assets/js/leanmodal.init.js', WPFD_PLUGIN_FILE), array('jquery'));
    wp_localize_script('wpfd-modal-init', 'wpfdmodalvars', array('adminurl' => admin_url()));
    wp_enqueue_style(
        'wpfd-viewer',
        plugins_url('app/site/assets/css/viewer.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
});

/**
 * Display insert wpfd button
 *
 * @return void
 */
function wpfd_button()
{
    Application::getInstance('Wpfd');
    $modelConfig = Model::getInstance('configfront');
    $config = $modelConfig->getGlobalConfig();
    if ((int)$config['enablewpfd'] === 1) {
        $context = "<a href='#wpfdmodal' class='button wpfdlaunch' id='wpfdlaunch' title='WP File Download'>";
        $context .= "<span class='dashicons dashicons-download' style='line-height: inherit;'></span> ";
        $context .= esc_html__('WP File Download', 'wpfd') . '</a>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- content escape above
        echo $context;
    }
}

/**
 * Upload access
 *
 * @return void
 */
function wpfd_assets_upload()
{
    wp_enqueue_script('jquery.filedrop', plugins_url('app/admin/assets/js/jquery.filedrop.min.js', WPFD_PLUGIN_FILE));
    wp_enqueue_script('wpfd.bootbox.upload', plugins_url('app/admin/assets/js/bootbox.js', WPFD_PLUGIN_FILE));
    wp_enqueue_script('wpfd-base64js', plugins_url('app/admin/assets/js/encodingHelper.js', WPFD_PLUGIN_FILE));
    wp_enqueue_script('wpfd-TextEncoderLite', plugins_url('app/admin/assets/js/TextEncoderLite.js', WPFD_PLUGIN_FILE));
    wp_enqueue_script('resumable', plugins_url('app/admin/assets/js/resumable.js', WPFD_PLUGIN_FILE));
    wp_enqueue_script(
        'wpfd-upload',
        plugins_url('app/site/assets/js/wpfd.upload.js', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );

    wp_localize_script('wpfd-upload', 'wpfd_permissions', array(
        'can_create_category' => wpfd_can_create_category(),
        'can_edit_category' => (wpfd_can_edit_category() || wpfd_can_edit_own_category()) ? true : false,
        'can_delete_category' => (wpfd_can_delete_category() || wpfd_can_edit_own_category()) ? true : false,
        'translate' => array(
            'wpfd_create_category' => esc_html__("You don't have permission to create new category", 'wpfd'),
            'wpfd_edit_category' => esc_html__("You don't have permission to edit category", 'wpfd')
        ),
    ));
    wp_localize_script('wpfd-upload', 'wpfd_var', array(
        'adminurl' => admin_url('admin.php'),
        'wpfdajaxurl' => admin_url('admin-ajax.php'),
    ));
    Application::getInstance('Wpfd');
    $configModel = Model::getInstance('configfront');
    $config = $configModel->getGlobalConfig();
    if (!class_exists('WpfdTool')) {
        $application   = Application::getInstance('Wpfd');
        $path_wpfdtool = $application->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
        $path_wpfdtool .= DIRECTORY_SEPARATOR . 'WpfdTool.php';
        require_once $path_wpfdtool;
    }
    $serverUploadLimit = min(
        10 * 1024 * 1024, // Maximum for chunks size is 10MB if other settings is greater than 10MB
        WpfdTool::parseSize(ini_get('upload_max_filesize')),
        WpfdTool::parseSize(ini_get('post_max_size'))
    );
    wp_localize_script('wpfd-upload', 'wpfd_admin', array(
        'allowed' => $config['allowedext'],
        'maxFileSize' => $config['maxinputfile'],
        'serverUploadLimit' => ((int) $serverUploadLimit === 0) ? 10 * 1024 * 1204 : $serverUploadLimit,
        'msg_remove_file' => esc_html__('Files removed with success!', 'wpfd'),
        'msg_remove_files' => esc_html__('File(s) removed with success!', 'wpfd'),
        'msg_move_file' => esc_html__('Files moved with success!', 'wpfd'),
        'msg_move_files' => esc_html__('File(s) moved with success!', 'wpfd'),
        'msg_copy_file' => esc_html__('Files copied with success!', 'wpfd'),
        'msg_copy_files' => esc_html__('File(s) copied with success!', 'wpfd'),
        'msg_add_category' => esc_html__('Category created with success!', 'wpfd'),
        'msg_remove_category' => esc_html__('Category removed with success!', 'wpfd'),
        'msg_move_category' => esc_html__('New category order saved!', 'wpfd'),
        'msg_edit_category' => esc_html__('Category renamed with success!', 'wpfd'),
        'msg_save_category' => esc_html__('Category config saved with success!', 'wpfd'),
        'msg_save_file' => esc_html__('File config saved with success!', 'wpfd'),
        'msg_ordering_file' => esc_html__('File ordering with success!', 'wpfd'),
        'msg_ordering_file2' => esc_html__('File order saved with success!', 'wpfd'),
        'msg_upload_file' => esc_html__('New File(s) uploaded with success!', 'wpfd'),
        'msg_ask_delete_file' => esc_html__('Are you sure you want to delete this file?', 'wpfd'),
        'msg_ask_delete_files' => esc_html__('Are you sure you want to delete the files you have selected?', 'wpfd'),
        'msg_multi_files_text' => esc_html__(
            'This file is listed in several categories, settings are available in the original version of the file',
            'wpfd'
        ),
        'msg_multi_files_btn_label' => esc_html__('EDIT ORIGINAL FILE', 'wpfd'),
        'msg_copied_to_clipboard' => esc_html__('File URL copied to clipboard', 'wpfd')
    ));
}

/**
 * Search shortcode
 *
 * @param string $atts Shortcode Attributes
 *
 * @return string
 */
function wpfd_upload_shortcode($atts)
{
    static $alreadyRun = false;

    wp_enqueue_style(
        'wpfd-upload',
        plugins_url('app/site/assets/css/upload.min.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    // Show login form if user not logged in
    if (!is_user_logged_in()) {
        if ($alreadyRun === true) {
            return '';
        }
        $html = '<div class="wpfd_upload_login_form">';
        $html .= '<h3>' . esc_html__('You need to login to be able to upload file!', 'wpfd') . '</h3>';
        $html .= wp_login_form(array('echo' => false));
        $html .= '</div>';
        $alreadyRun = true;
        return $html;
    }

    $args = shortcode_atts(array('category_id' => 0), $atts);

    $app = Application::getInstance('Wpfd');
    $modelCategorie = Model::getInstance('categoryfront');
    $category = $modelCategorie->getCategory($args['category_id']);
    Application::getInstance('Wpfd');
    $modelConfig = Model::getInstance('configfront');
    $global_settings = $modelConfig->getGlobalConfig();
    if (!$category) {
        return '';
    }

    $params = $category->params;
    if ((int)$category->access === 1) {
        $user = wp_get_current_user();
        $roles = array();
        foreach ($user->roles as $role) {
            $roles[] = strtolower($role);
        }
        $allows = array_intersect($roles, $category->roles);

        $singleuser = false;

        if (isset($params['canview']) && (string)$params['canview'] === '') {
            $params['canview'] = 0;
        }

        $canview = isset($params['canview']) ? $params['canview'] : 0;
        if ((int)$global_settings['restrictfile'] === 1) {
            $user = wp_get_current_user();
            $user_id = $user->ID;

            if ($user_id) {
                if ((int)$canview === (int)$user_id || (int)$canview === 0) {
                    $singleuser = true;
                } else {
                    $singleuser = false;
                }
            } else {
                if ((int)$canview === 0) {
                    $singleuser = true;
                } else {
                    $singleuser = false;
                }
            }
        }
        if ((int)$canview !== 0 && !count($category->roles)) {
            if ($singleuser === false) {
                return '';
            }
        } elseif ((int)$canview !== 0 && count($category->roles)) {
            if (empty($allows) || !$singleuser) {
                return '';
            }
        } else {
            if (empty($allows)) {
                return '';
            }
        }
    }
    // Everything seem ok load assets
    wp_enqueue_script('wpfd-bootstrap', plugins_url('app/admin/assets/js/bootstrap.min.js', WPFD_PLUGIN_FILE));
    wp_enqueue_style('wpfd-bootstrap', plugins_url('app/admin/assets/css/bootstrap.min.css', WPFD_PLUGIN_FILE));
    wpfd_enqueue_assets();
    wpfd_assets_upload();
    wp_enqueue_style(
        'wpfd-front',
        plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );

    if (!isset($args)) {
        $args = '';
    }
    // Random upload form id
    $args['formId'] = rand();

    return wpfd_get_template_html('tpl-upload.php', $args);
}

/**
 * Print single file content
 *
 * @return void
 */
function wpfdTheContent()
{
    include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'filters.php');
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Allow html here
    echo wpfdFilter::wpfdFileContent();
}

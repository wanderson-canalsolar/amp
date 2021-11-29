<?php
namespace WPDM\AuthorDashboard;

use WPDM\__\Messages;
use WPDM\__\Template;

class AuthorDashboard
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    function __construct(){

        add_shortcode("wpdm_frontend", array($this, 'dashboard'));
        add_shortcode("wpdm_manage_packages", array($this, 'packageList'));
        add_shortcode("wpdm_package_form", array($this, 'packageForm'));
        add_shortcode("wpdm_profile_info", array($this, 'editProfile'));
        add_shortcode("wpdm_author_settings", array($this, 'Settings'));
        add_action('wp_ajax_delete_package_frontend', array($this, 'deletePackage'));
        add_action('wp_ajax_wpdm_frontend_file_upload', array($this, 'uploadFile'));
        add_action('wp_ajax_wpdm_update_public_profile', array($this, 'updateProfile'));
        add_action('wp_ajax_wpdm_author_settings', array($this, 'saveSettings'));
        add_action('wp', array($this, 'savePackage'));
    }

    /**
     * @usage Short-code function for front-end UI
     * @return string
     */
    function dashboard($params = array())
    {

        global $current_user, $WPDM;

        if(!is_user_logged_in()) {
            return WPDM()->user->login->form($params);
        }

        $msg = get_option('__wpdm_front_end_access_blocked', __( "Sorry, Your Are Not Allowed!" , "download-manager" ));
        $msg = $msg != ''?$msg:__( "Sorry, Your Are Not Allowed!" , "download-manager" );

        wp_reset_query();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));
        $urlfix = isset($params['flaturl']) && $params['flaturl'] == 0?1:0;
        update_post_meta(get_the_ID(), '__urlfix', $urlfix);
        $adb_page = get_query_var('adb_page');
        if($urlfix == 1) $adb_page = wpdm_query_var('adb_page');
        $adb_page = explode("/", $adb_page);
        $hide = isset($params['hide']) ? $params['hide'] : "";
        if($adb_page[0] == 'edit-package')
        {
            $pid = $adb_page[1];
        }
        if($adb_page[0] == 'page')
        {
            $adb_page[0] = '';
            set_query_var('paged', $adb_page[1]);
        }
        $adb_page = $adb_page[0];

        if($adb_page == 'edit-package' && get_post_type($pid) !== 'wpdmpro') return Messages::error(array('message' =>  __( "Package Not Found" , "download-manager" ), 'title' =>  __( "ERROR!" , "download-manager" )), -1);

        //if($task == 'edit-package' && get_current_user_id() == get_post_au) return \WPDM\__\Messages::error(array('message' =>  __( "Your are not authorized to edit this package" , "download-manager" ), 'title' =>  __( "ERROR!" , "download-manager" )), -1);

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return "<div class='w3eden'><div class='alert alert-danger'>" . wpautop(stripslashes($msg)) . "</div></div>";

        $id = wpdm_query_var('ID');


        $menu_items = array(
            'manage-packs' => array('label' => __( "All Items" , "download-manager" ), 'shortcode' => "[wpdm_manage_packages view=table]", 'icon' => 'far fa-arrow-alt-circle-down'),
            'add-new' => array('label' => __( "Add New" , "download-manager" ), 'shortcode' => "[wpdm_package_form hide='{$hide}']", 'icon' => 'fa fa-file-upload'),
            'edit-profile' => array('label' => __( "Edit Profile" , "download-manager" ), 'shortcode' => '[wpdm_profile_info]', 'icon' => 'fa fa-user-edit'),
            'settings' => array('label' => __( "Settings" , "download-manager" ), 'shortcode' => '[wpdm_author_settings]', 'icon' => 'fa fa-cog'),
        );
        $menu_items = apply_filters('wpdm_frontend', $menu_items);

        $menu_ids = array_keys($menu_items);
        $adb_page = $adb_page == ''?$menu_ids[0]:$adb_page;
        $burl = get_permalink();
        $sap = strpos($burl, '?') ? '&' : '?';

        $default_icons['seller-dashboard'] = 'fa fa-tachometer-alt';
        $default_icons['manage-packs'] = 'far fa-arrow-alt-circle-down';
        $default_icons['add-new'] = 'fa fa-file-upload';
        $default_icons['edit-profile'] = 'fa fa-user-edit';
        $default_icons['categories'] = 'fa fa-sitemap';
        $default_icons['sales'] = 'fa fa-chart-line';
        $default_icons['settings'] = 'fa fa-cog';
        $default_icons['file-manager'] = 'far fa-images';

        ob_start();
        include Template::locate('author-dashboard.php', __DIR__.'/views');
        $data = ob_get_clean();
        wp_reset_query();
        return $data;
    }

    /**
     * @usage Save Package Data
     */

    function savePackage()
    {
        global $current_user, $wpdb;
        //wpdmdd($_REQUEST);
        if (!is_user_logged_in()) return;
        $allowed_roles = get_option('__wpdm_front_end_access');
        $allowed_roles = maybe_unserialize($allowed_roles);
        $allowed_roles = is_array($allowed_roles) ? $allowed_roles : array();
        $allowed = array_intersect($allowed_roles, $current_user->roles);
        if (isset($_REQUEST['act']) && in_array($_REQUEST['act'], array('_ap_wpdm', '_ep_wpdm')) && count($allowed) > 0) {
            if (!wp_verify_nonce($_REQUEST['__wpdmepnonce'], NONCE_KEY)) {
                $data = array('error' => __("Invalid Request! Refresh the page and try again", "download-manager"));
                header('Content-type: application/json');
                echo json_encode($data);
                die();
            }

            $pack = $_POST['pack'];
            $pack['post_type'] = 'wpdmpro';
            $ostatus = 'pending';
            if ($_POST['act'] == '_ep_wpdm') {

                $p = get_post($_POST['id']);

                $adminAccess = maybe_unserialize(get_option('__wpdm_front_end_admin', array()));
                $adminAccess = array_intersect($adminAccess, $current_user->roles);
                if ($current_user->ID != $p->post_author && !$adminAccess) return;

                $hook = "edit_package_frontend";
                $pack['ID'] = (int)$_POST['id'];
                unset($pack['post_status']);

                // If not an admin, don't allow to update post author field
                if (!$adminAccess)
                    unset($pack['post_author']);

                $post = get_post($pack['ID']);

                $ostatus = $post->post_status === 'publish' ? 'publish' : get_option('__wpdm_ips_frontend', 'publish');
                $status = isset($_POST['status']) && $_POST['status'] === 'draft' ? 'draft' : $ostatus;
                $pack['post_status'] = $status;
                $pack = apply_filters("wpdm_before_update_package_frontend", $pack);
                $id = wp_update_post($pack);

            }
            if ($_POST['act'] == '_ap_wpdm') {
                $hook = "create_package_frontend";
                //$status = isset($_POST['status']) && $_POST['status'] == 'draft'?'draft': get_option('__wpdm_ips_frontend','publish');
                $status = isset($_POST['status']) && in_array($_POST['status'], array('draft', 'auto-draft')) ? $_POST['status'] : $ostatus;
                $pack['post_status'] = $status;
                $pack['post_author'] = $current_user->ID;
                $pack = apply_filters("wpdm_before_create_package_frontend", $pack);
                if($pack)
                    $id = wp_insert_post($pack);
                else
                    wp_send_json( array('result' => false, 'id' => 0 ));
            }

            if (isset($_POST['cats'])) {
                foreach ($_POST['cats'] as $cat){
                    $cats[] = (int)$cat;
                }
                $ret = wp_set_post_terms($id, $cats, 'wpdmcategory');
            }

            if (isset($_POST['wpdmtags'])) {
                foreach ($_POST['wpdmtags'] as $tag){
                    $tags[] = (int)$tag;
                }
                wp_set_post_terms($id, $tags, 'wpdmtag');
            }

            //Save custom taxonomies
            if (isset($_POST['taxonomy']) && is_array($_POST['taxonomy'])) {
                foreach ($_POST['taxonomy'] as $taxonmy => $terms){
                    foreach ($terms as &$term) {
                        $term = (int)$term;
                    }
                    wp_set_post_terms($id, $terms, $taxonmy);
                }
            }

            // Save Package Meta
            $cdata = get_post_custom($id);
            $hdnmt = array('__wpdm_favs', '__wpdm_masterkey');
            foreach ($cdata as $k => $v) {
                $tk = str_replace("__wpdm_", "", $k);
                if (!isset($_POST['file'][$tk]) && !in_array($k, $hdnmt) && $tk != $k)
                    delete_post_meta($id, $k);

            }

            if (isset($_POST['file']['preview'])) {
                $preview = $_POST['file']['preview'];
                $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid='%s';", $preview));
                set_post_thumbnail($id, $attachment_id);
                unset($_POST['file']['preview']);
            } else {
                delete_post_thumbnail($id);
            }

            if (isset($_POST['file']) && is_array($_POST['file'])) {
                foreach ($_POST['file'] as $meta_key => $meta_value) {
                    if ($meta_key == 'package_size' && (double)$meta_value == 0) $meta_value = "";
                    $key_name = "__wpdm_" . $meta_key;
                    update_post_meta($id, $key_name, $meta_value);
                }
            }

            update_post_meta($id, '__wpdm_masterkey', uniqid());

            if (isset($_POST['reset_key']) && $_POST['reset_key'] == 1)
                update_post_meta($id, '__wpdm_masterkey', uniqid());

            if (get_option('__wpdm_disable_new_package_email') == 0 && $status !== 'auto-draft' && (int)get_post_meta($id, '__amailed', true) !== 1) {
                update_post_meta($id, '__amailed', 1);
                \WPDM\__\Email::send("new-package-frontend", array('package_name' => $pack['post_title'], 'name' => $current_user->user_nicename, 'author' => "<a href='" . admin_url('user-edit.php?user_id=' . $current_user->ID) . "'>{$current_user->user_nicename}</a>", 'edit_url' => admin_url('post.php?action=edit&post=' . $id)));
            }

            do_action($hook, $id, get_post($id));

            $data = array('result' => $_POST['act'], 'id' => $id);

            header('Content-type: application/json');
            echo json_encode($data);
            die();


        }
    }

    /**
     * @usage Delete package from front-end
     */
    function deletePackage()
    {
        global $wpdb, $current_user;
        if (isset($_GET['ID']) && intval($_GET['ID'])>0) {
            $id = (int)$_GET['ID'];
            $uid = $current_user->ID;
            if ($uid == '') die('Error! You are not logged in.');
            $post = get_post($id);
            if($post->post_author == $uid)
                wp_delete_post($id, true);
            echo "deleted";
            die();
        }
    }

    /**
     * @usage Upload files
     */
    function uploadFile(){

        global $current_user;

        $currentAccess = maybe_unserialize(get_option( '__wpdm_front_end_access', array()));
        // Check if user is authorized to upload file from front-end
        if(!is_user_logged_in() || !array_intersect($currentAccess, $current_user->roles) ) die(__( "Error! You are not allowed to upload files." , "download-manager" ));

        $upload_dir = current_user_can('manage_options')?UPLOAD_DIR:UPLOAD_DIR.$current_user->user_login.'/';

        check_ajax_referer(NONCE_KEY);

        $name = isset($_FILES['attach_file']['name']) && !isset($_REQUEST["chunks"])?$_FILES['attach_file']['name']:$_REQUEST['name'];

        if(file_exists($upload_dir.$name) && get_option('__wpdm_overwrite_file_frontend',0)==1 && !isset($_REQUEST["chunks"])){
            @unlink($upload_dir.$name);
        }
        if(file_exists($upload_dir.$name) && !isset($_REQUEST["chunks"]))
            $filename = time().'wpdm_'.$name;
        else
            $filename = $name;

        $filename = esc_html($filename);

        //move_uploaded_file($_FILES['attach_file']['tmp_name'],UPLOAD_DIR.$filename);
        //echo $filename;

        //Validate file type
        if(WPDM()->fileSystem->isBlocked($name, $_FILES['attach_file']['tmp_name'])) die(esc_attr__( 'Invalid file type!', 'download-manager' ));

        if(!file_exists($upload_dir)){
            mkdir($upload_dir);
            \WPDM\__\FileSystem::blockHTTPAccess($upload_dir);
        }
        if (isset($_POST['current_path']) && $_POST['current_path'] != ''){
            $user_upload_dir = $upload_dir;
            $upload_dir  = realpath($upload_dir.'/'.$_POST['current_path']).'/';
            if(!strstr($upload_dir, $user_upload_dir)) die('Error! '. $upload_dir);
        }

        if(get_option('__wpdm_sanitize_filename', 0) == 1)
            $filename = sanitize_file_name($filename);

        if(isset($_REQUEST["chunks"])) $this->chunkUploadFile($upload_dir.$filename);
        else {
            $ret = move_uploaded_file($_FILES['attach_file']['tmp_name'], $upload_dir . $filename);
            if(!$ret) debug_print_backtrace();
        }
        do_action("wpdm_after_upload_file_frontend", $upload_dir . $filename);
        echo "|||".str_replace(UPLOAD_DIR, '', $upload_dir).$filename."|||";

        exit;
    }

    function chunkUploadFile($destFilePath){

        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $out = @fopen("{$destFilePath}.part", $chunk == 0 ? "wb" : "ab");
        if ($out) {
            // Read binary input stream and append it to temp file
            $in = @fopen($_FILES['attach_file']['tmp_name'], "rb");

            if ($in) {
                while ($buff = fread($in, 4096))
                    fwrite($out, $buff);
            } else
                die('-3');

            @fclose($in);
            @fclose($out);

            @unlink($_FILES['package_file']['tmp_name']);
        } else
            die('-3');

        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$destFilePath}.part", $destFilePath);
            do_action("wpdm_after_upload_file_frontend", $destFilePath);
        }
    }

    function packageList($params = array()){

        global $current_user;

        if(!is_user_logged_in()) {
            return wpdm_login_form($params);
        }

        $msg = get_option('__wpdm_front_end_access_blocked', __( "Sorry, Your Are Not Allowed!" , "download-manager" ));
        $msg = $msg != ''?$msg:__( "Sorry, Your Are Not Allowed!" , "download-manager" );

        wp_reset_query();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return "<div class='w3eden'><div class='alert alert-danger'>" . wpautop(stripslashes($msg)) . "</div></div>";

        $limit = 10;

        $flaturl = isset($params['flaturl'])?$params['flaturl']:0;

        $cond[] = "uid='{$current_user->ID}'";
        $Q = wpdm_query_var('q','txt');
        $paged = wpdm_query_var('pg','num');
        $paged = $paged>0?$paged:1;

        $start = $paged?(($paged-1)*$limit):0;
        $field = wpdm_query_var('sfield')?wpdm_query_var('sfield'):'publish_date';
        $ord = wpdm_query_var('sorder')?wpdm_query_var('sorder'):'desc';

        $author = $current_user->ID;
        $query_params = array('post_status'=>array('publish','pending','draft'), 'post_type'=>'wpdmpro', 'offset'=>$start, 'posts_per_page' => $limit);

        //Admin or author
        $admin = 1;
        if(!array_intersect($current_user->roles, get_option('__wpdm_front_end_admin', array()))) {
            $admin = 0;
            $query_params['author'] = $author;
        }

        if($admin == 1 && wpdm_query_var('_author')){
            $query_params['author'] = wpdm_query_var('_author', 'int');
        }

        $query_params['orderby'] = $field;
        $query_params['order'] = $ord;
        if(isset($params['base_category'])){
            $query_params['tax_query'] = array(
                array(
                    'taxonomy' => 'wpdmcategory',
                    'field'    => 'slug',
                    'terms'    => $params['base_category'],
                    'include_children' => true
                )
            );
        }
        if($field=='download_count'){
            $query_params['orderby'] = 'meta_value_num';
            $query_params['meta_key'] = '__wpdm_download_count';
            $query_params['order'] = $ord;
        }

        if($Q) $query_params['s'] = $Q;

        $base_url = get_permalink();

        $edit_url = add_query_arg('adb_page', 'edit-package/%d/', $base_url);

        if($flaturl == 1)
            $edit_url = $base_url . '/edit-package/%d/';

        //If you want to modify query params
        $query_params = apply_filters("wpdm_adb_package_list_query_params", $query_params);

        $query_packages = new \WP_Query($query_params);

        $sap = strpos($base_url, '?') ? '&' : '?';


        if(!isset($qr)) $qr = '';


        ob_start();
        echo "<div class='w3eden'>";
        if(isset($params['view']) && $params['view'] == 'table')
            include Template::locate("author-dashboard/list-packages-table.php", __DIR__.'/views');
        else
            include Template::locate("author-dashboard/list-packages-panel.php", __DIR__.'/views');
        echo "</div>";
        return ob_get_clean();
    }

    function packageForm($params = array()){
        global $current_user;
        if(!is_user_logged_in()) {
            return wpdm_login_form($params);
        }

        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));

        $msg = get_option('__wpdm_front_end_access_blocked', __( "Sorry, Your Are Not Allowed!" , "download-manager" ));
        $msg = $msg != ''?$msg:__( "Sorry, Your Are Not Allowed!" , "download-manager" );

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return "<div class='w3eden'><div class='alert alert-danger'>" . wpautop(stripslashes($msg)) . "</div></div>";

        $post = get_post(wpdm_query_var('id'));

        if(wpdm_query_var('id') > 0 && $current_user->ID != $post->post_author && !current_user_can('manage_options'))
            return "<div class='w3eden'><div class='alert alert-danger'>" . wpautop(stripslashes($msg)) . "</div></div>";

        ob_start();
        include Template::locate("author-dashboard/new-package-form.php", __DIR__.'/views');
        wp_reset_postdata();
        return ob_get_clean();
    }

    public static function hasAccess($uid = null){
        global $current_user;
        if(!$uid) $uid = $current_user->ID;
        if(current_user_can('manage_options')) return true;
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));
        return array_intersect($currentAccess, $current_user->roles) && is_user_logged_in()?true:false;
    }

    function updateProfile(){
        if(!is_user_logged_in()) die('Error!');
        $profile = wpdm_query_var('__wpdm_public_profile');
        update_user_meta(get_current_user_id(), '__wpdm_public_profile', $profile);
        if(isset($_POST['__wpdm_public_profile']['paypal']) && $_POST['__wpdm_public_profile']['paypal'] != '') {
            update_user_meta(get_current_user_id(), 'payment_account', esc_attr($_POST['__wpdm_public_profile']['paypal']));
        }
        die('OK');
    }

    function editProfile(){
        ob_start();
        include Template::locate("author-dashboard/edit-user-profile.php", __DIR__.'/views');
        return ob_get_clean();
    }


    function settings(){
        ob_start();
        $settings = get_user_meta(get_current_user_id(), '__wpdm_author_settings', true);
        include Template::locate("author-settings.php", __DIR__.'/views');
        return ob_get_clean();
    }
    function saveSettings(){
        if(!self::hasAccess()) die('Error!');
        if(isset($_POST['__saveas']) && wp_verify_nonce($_POST['__saveas'], NONCE_KEY)){
            update_user_meta(get_current_user_id(), '__wpdm_author_settings', wpdm_query_var('__wpdm_author_settings'));
            do_action("wpdm_after_save_author_settings", $_POST['__wpdm_author_settings']);
            die('OK');

        }
        die('Error');
    }



}

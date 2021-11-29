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

//-- No direct access
defined('ABSPATH') || die();

/**
 * Class WpfdHelperFile
 */
class WpfdHelperShortcodes
{
    /**
     * Global config
     *
     * @var array
     */
    public $globalConfig;

    /**
     * Initializing the helper Shortcodes class.
     *
     * @access public
     *
     * @throws Exception If arguments are missing when initializing a full widget instance.
     */
    public function __construct()
    {
        add_shortcode('wpfd_category', array($this, 'categoryShortcode'));
        add_shortcode('wpfd_single_file', array($this, 'singleFileShortcode'));
        add_shortcode('wpfd_files', array($this, 'filesShortcode'));
        add_shortcode('wpfd_search', array($this, 'wpfdSearchShortcode'));
        Application::getInstance('Wpfd');
        $configModel = Model::getInstance('configfront');
        if (method_exists($configModel, 'getGlobalConfig')) {
            $this->globalConfig = $configModel->getGlobalConfig();
        } elseif (method_exists($configModel, 'getConfig')) {
            $this->globalConfig = $configModel->getConfig();
        }
    }

    /**
     * Category shortcode
     *
     * @param array $atts Attribute
     *
     * @return string
     */
    public function categoryShortcode($atts)
    {
        if (isset($atts['id']) && $atts['id']) {
            add_action('wp_footer', array($this, 'wpfdFooter'));
            return $this->callTheme($atts['id'], $atts);
        } else {
            add_action('wp_footer', array($this, 'wpfdFooter'));
            return $this->contentAllCat($atts);
        }
    }

    /**
     * Display wpfd scripts in footer
     *
     * @return void
     */
    public function wpfdFooter()
    {
        echo '<div id="wpfd-loading-wrap"><div class="wpfd-loading"></div></div>';
        echo '<div id="wpfd-loading-tree-wrap"><div class="wpfd-loading-tree-bg"></div></div>';
    }
    /**
     * Files shortcode
     *
     * Use: [wpfd_files catids="1,2,3" order="id|title|date|modified|rand" direction="asc|desc" users="<user_id>" limit="<total_display_file>" style="1" download="1" showhits="1"]
     * Params:
     * catids: list category or use 'all' for all categories. Default 'all'
     * order: Order of file accept id,title,date,modified and rand value. Default 'id'
     * direction: Ordering direction. Accept asc or desc. Default 'desc'
     * limit: limit of file will showing, max 100 files. Default '5'
     * download: Allow download or not. Accept 1 or 0. Default 1
     * preview: Allow preview or not. Accept 1 or 0. Default 1
     * showhits: Showing download count or not. Accept 1 or 0. Default 1
     * liststyle: Style for listing. Accept all value for list-style-type css properties. Default 'none'
     * width: Width of the list in pixel. Default '500'
     *
     * @param array $atts Attribute
     *
     * @return string
     */
    public function filesShortcode($atts)
    {
        $user = wp_get_current_user();

        if (isset($atts['limit'])) {
            // Cast limit to number for security reason
            $limit = (int) $atts['limit'];
        } else {
            $limit = 5;
        }

        // Check for limit
        if ($limit === 0) {
            return '';
        }

        // Setup default value for missing attribute
        if (isset($atts['catids']) && $atts['catids'] !== '') {
            // Filter category id in number only
            $categories = preg_split('/[\D]+/', $atts['catids']);

            // Check for sure there is a valid category id
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            if (is_countable($categories) && count($categories) === 0) {
                $categories = 'all';
            }
        } else {
            $categories = 'all';
        }

        if (isset($atts['cat_operator']) && in_array($atts['cat_operator'], array('IN', 'AND', 'NOT IN'))) {
            $categoryOperator = $atts['cat_operator'];
        } else {
            $categoryOperator = 'IN';
        }

        if (isset($atts['order']) && in_array(strtolower($atts['order']), array('id', 'title', 'date', 'modified', 'rand'))) {
            $fileOrder = (strtolower($atts['order']) === 'id') ? strtoupper($atts['order']) : strtolower($atts['order']);
        } else {
            $fileOrder = 'ID';
        }

        if (isset($atts['direction']) && in_array(strtolower($atts['direction']), array('asc', 'desc'))) {
            $orderDirection = strtoupper($atts['direction']);
        } else {
            $orderDirection = 'DESC';
        }

        if (isset($atts['users']) && $atts['users'] !== '') {
            // Filter category id in number only
            $userIds = preg_split('/[\D]+/', $atts['users']);
        }

        if (!isset($atts['style'])) {
            $style = 1;
        } else {
            $style = (int) $atts['style'];
        }

        if (!isset($atts['download'])) {
            $download = 1;
        } else {
            $download = (int) $atts['download'];
        }

        if (!isset($atts['showhits'])) {
            $showhits = 1;
        } else {
            $showhits = (int) $atts['showhits'];
        }

        if (!isset($atts['preview'])) {
            $preview = 1;
        } else {
            $preview = (int) $atts['preview'];
        }

        if (!isset($atts['width'])) {
            $width = 500;
        } else {
            $width = (int) $atts['width'];
        }

        $startList = 'ol';
        if (isset($atts['liststyle']) && in_array($atts['liststyle'], array('disc','armenian','circle','cjk-ideographic','decimal','decimal-leading-zero','georgian','hebrew','hiragana','hiragana-iroha','katakana','katakana-iroha','lower-alpha','lower-greek','lower-latin','lower-roman','none','square','upper-alpha','upper-greek','upper-latin','upper-roman','initial','inherit'))) {
            switch ($atts['liststyle']) {
                case 'disk':
                case 'circle':
                case 'square':
                    $startList = 'ul';
                    break;
                default:
                    $startList = 'ol';
                    break;
            }
            $liststyle = $atts['liststyle'];
        } else {
            $liststyle = 'none';
        }

        // Check permission on categories
        if ($categories === 'all' || $categoryOperator === 'NOT IN') {
            $allCats = array();
            $allCat = get_terms(
                array(
                    'taxonomy' => 'wpfd-category',
                    'hide_empty' => 1
                )
            );
            if (!is_wp_error($allCat)) {
                foreach ($allCat as $cat) {
                    $allCats[] = $cat->term_id;
                }
            }

            // If not have any category, return
            if (empty($allCats)) {
                return '';
            }
        }

        $args = array(
            'post_type' => 'wpfd_file',
            'post_status' => array('publish'),
            'posts_per_page' => -1,
            'order_by' => $fileOrder,
            'order' => $orderDirection
        );

        if (isset($userIds) && !empty($userIds)) {
            $args['author__in'] = $userIds;
        }
        // Get categories and check current user have permission to see the files
        if ($categoryOperator === 'NOT IN') {
            $taxQuery = array(
                array (
                    'taxonomy' => 'wpfd-category',
                    'fields' => 'term_id',
                    'terms' => $categories,
                    'operator' => $categoryOperator
                )
            );
        } else {
            $taxQuery = array(
                array (
                    'taxonomy' => 'wpfd-category',
                    'fields' => 'term_id',
                    'terms' => isset($allCats) ? $allCats : $categories,
                    'operator' => $categoryOperator
                )
            );
        }
        $args['relation'] = 'AND';
        $args['tax_query'] = $taxQuery;

        // Fix conflict plugin Go7 Pricing Table
        remove_all_filters('posts_fields');
        remove_filter('the_posts', array($this, 'wpfdGetMeta'), 0);
        $query = new WP_Query($args);
        $posts = $query->get_posts();

        if (is_wp_error($posts)) {
            return '';
        }

        $latestFiles = array();
        $countPost = 0;

        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($posts) && count($posts)) {
            $totalFiles = count($posts);
            foreach ($posts as $post) {
                if ($totalFiles === $countPost) {
                    break;
                }
                if ($countPost < $limit) {
                    $file = $this->wpfdCheckAccess($post, $user);
                    if (false !== $file) {
                        $latestFiles[] = $file;
                        $countPost++;
                    }
                } else {
                    break;
                }
            }
            wp_reset_postdata();
        } else {
            return '';
        }
        //$latestFiles = array_reverse($latestFiles);


        if ($style === 1) {
            wp_enqueue_style('wpfd-google-icon', plugins_url('app/admin/assets/ui/fonts/material-icons.min.css', WPFD_PLUGIN_FILE));
            wp_enqueue_style(
                'wpfd-material-design',
                plugins_url('app/site/assets/css/material-design-iconic-font.min.css', WPFD_PLUGIN_FILE),
                array(),
                WPFD_VERSION
            );
        }
        $content = '<' . $startList . ' style="list-style-type: ' . $liststyle . '; width: ' . $width . 'px" class="wpfd_files">';

        foreach ($latestFiles as $file) {
            // Download button
            $dHtml = '';
            if ($download) {
                $dHtml .= '<a style="float: right;box-shadow: 0 0 0 0;" class="wpfd_files_download" href="' . $file->linkdownload . '">';
                $dHtml .= '&nbsp;<i class="zmdi zmdi-cloud-download"></i></a>';
            }

            // Preview button
            $pHtml = '';
            if ($preview) {
                if (isset($file->openpdflink)) {
                    $pHtml .= '<a style="float: right;box-shadow: 0 0 0 0;width:16px;" class="wpfd_files_preview" target="_blank" href="' . $file->openpdflink . '">';
                    $pHtml .= '<img style="display:inline;margin-right: 5px;" src="' . plugins_url('/app/site/assets/images/open_242.png', WPFD_PLUGIN_FILE) . '" title="' . esc_html__('Open', 'wpfd') . '"/></a>';
                } else {
                    $pHtml .= '<a style="float: right;box-shadow: 0 0 0 0;width:16px;" class="wpfd_files_preview" target="_blank" href="' . $file->viewerlink . '">';
                    $pHtml .= '<img style="display:inline;margin-right: 5px;" src="' . plugins_url('/app/site/assets/images/open_242.png', WPFD_PLUGIN_FILE) . '" title="' . esc_html__('Open', 'wpfd') . '"/></a>';
                }
            }

            $hHtml = '';
            if ($showhits) {
                $hHtml .= '(' . sprintf(esc_html__('Download %d times', 'wpfd'), $file->hits) . ')';
            }

            // Content
            $content .= '<li class="' . strtolower($file->ext) . '">';
            if ($download) {
                $content .= '<a class="wpfd_files_download" href="' . $file->linkdownload . '" style="box-shadow: 0 0 0 0;">';
            }
            $content .= $file->title . '.' . $file->ext;
            if ($download) {
                $content .= '</a>';
            }
            if ($showhits) {
                $content .= $hHtml;
            }

            if ($download) {
                $content .= $dHtml;
            }
            if ($preview) {
                $content .= $pHtml;
            }

            $content .=  '</li>';
        }
        $content .= '</' . $startList . '>';

        return $content;
    }

    /**
     * Single file shortcode
     *
     * @param array $atts Attribute
     *
     * @return string
     */
    public function singleFileShortcode($atts)
    {
        if (isset($atts['id']) && $atts['id']) {
            if (isset($atts['catid'])) {
                $catid = $atts['catid'];
            } else {
                $term_list = wp_get_post_terms((int)$atts['id'], 'wpfd-category', array('fields' => 'ids'));
                if (empty($term_list)) {
                    return '';
                }
                $catid = $term_list[0];
            }
            $diplayName = false;
            if (isset($atts['name']) && $atts['name']) {
                $diplayName = $atts['name'];
            }
            return $this->callSingleFile($atts['id'], $catid, $diplayName);
        }
        return '';
    }

    /**
     * Get content of a single file
     *
     * @param mixed $file_id     File Id
     * @param mixed $catid       Category Id
     * @param null  $nameDisplay Name Display
     *
     * @return string
     */
    public function callSingleFile($file_id, $catid, $nameDisplay = null)
    {
        $ds = DIRECTORY_SEPARATOR;
        wp_enqueue_style(
            'wpfd-front',
            plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_script(
            'wpfd-frontend',
            plugins_url('app/site/assets/js/frontend.js', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_localize_script('wpfd-frontend', 'wpfdfrontend', array('pluginurl' => plugins_url('', WPFD_PLUGIN_FILE)));

        wp_enqueue_style(
            'wpfd-theme-default',
            plugins_url('app/site/themes/wpfd-default/css/style.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_style(
            'wpfd-colorbox-viewer',
            plugins_url('app/site/assets/css/viewer.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wpfd_enqueue_assets();

        $app = Application::getInstance('Wpfd');

        $path_wpfdbase = $app->getPath() . $ds . 'admin' . $ds . 'classes';
        $path_wpfdbase .= $ds . 'WpfdBase.php';
        require_once $path_wpfdbase;

        Application::getInstance('Wpfd');
        $modelConfig = Model::getInstance('configfront');
        /* @var WpfdModelIconsBuilder $modelIconsBuilder */
        $modelIconsBuilder = Model::getInstance('iconsbuilder');
        $modelCategory = Model::getInstance('categoryfront');
        $modelFile = Model::getInstance('filefront');
        $modelTokens = Model::getInstance('tokens');

        $token = $modelTokens->getOrCreateNew();
        $category = $modelCategory->getCategory((int)$catid);
        if (!$category) {
            return '';
        }
        if ((int) $category->access === 1) {
            $user = wp_get_current_user();
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);
            if (empty($allows)) {
                return '';
            }
        }

        $params = $modelConfig->getConfig();
        $file_params = $modelConfig->getFileConfig();
        $config = $this->globalConfig;
        $singleParams = $modelIconsBuilder->getSingleButtonParams();
        $idFile = $file_id;

        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $catid) ;
        if ($categoryFrom === 'googleDrive') {
            $file = apply_filters('wpfdAddonGetGoogleDriveFile', $idFile, $catid, $token);
        } elseif ($categoryFrom === 'dropbox') {
            $file = apply_filters('wpfdAddonGetDropboxFile', $idFile, $catid, $token);
        } elseif ($categoryFrom === 'onedrive') {
            $file = apply_filters('wpfdAddonGetOneDriveFile', $idFile, $catid, $token);
        } elseif ($categoryFrom === 'onedrive_business') {
            $file = apply_filters('wpfdAddonGetOneDriveBusinessFile', $idFile, $catid, $token);
        } else {
            $file = $modelFile->getFile($idFile, $catid);
        }
        if (!$file) {
            return '';
        }
        if (isset($file->state) && (int) $file->state === 0) {
            return '';
        }
        if ((int) $config['restrictfile'] === 1) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $canview = isset($file->canview) ? $file->canview : 0;
            $canview = array_map('intval', explode(',', $canview));
            if ($user_id) {
                if (!(in_array($user_id, $canview) || in_array(0, $canview))) {
                    return '';
                }
            } else {
                if (!in_array(0, $canview)) {
                    return '';
                }
            }
        }
        $file = (object)$file;
        $file->social = isset($file->social) ? $file->social : 0;

        if (!isset($file->crop_title) || (isset($file->crop_title) && strlen($file->crop_title) === 0)) {
            $file->crop_title = $file->post_title;
        }

        if (defined('WPFD_OLD_SINGLE_FILE') && WPFD_OLD_SINGLE_FILE) {
            $bg_color    = WpfdBase::loadValue($file_params, 'singlebg', '#444444');
            $hover_color = WpfdBase::loadValue($file_params, 'singlehover', '#888888');
            $font_color  = WpfdBase::loadValue($file_params, 'singlefontcolor', '#ffffff');
            $showsize    = ((int) WpfdBase::loadValue($params, 'showsize', 1) === 1) ? true : false;
            $singleCss   = '.wpfd-single-file .wpfd_previewlink {margin-top: 10px;display: block;font-weight: bold;}';
            if ($bg_color !== '') {
                $singleCss .= '.wpfd-single-file .wpfd-file-link {background-color: ' . esc_html($bg_color) . ' !important;}';
            }
            if ($font_color !== '') {
                $singleCss .= '.wpfd-single-file .wpfd-file-link {color: ' . esc_html($font_color) . ' !important;}';
            }
            if ($hover_color !== '') {
                $singleCss .= '.wpfd-single-file .wpfd-file-link:hover {background-color: ' . esc_html($hover_color) . ' !important;}';
            }

            if (!$nameDisplay) {
                $nameDisplay = $file->title;
            }

            $variables = array(
                'file' => $file,
                'nameDisplay' => $nameDisplay,
                'showsize' => $showsize,
                'previewType' => WpfdBase::loadValue($config, 'use_google_viewer', 'lightbox'),
            );
            $html = wpfd_get_template_html('tpl-single.php', $variables);
            $html .= '<style>' . $singleCss . '</style>';
        } else {
            // New style using icon builder
            // Load customized CSS file
            $customizeCssPath = WP_CONTENT_DIR . $ds .'wp-file-download' . $ds . 'wpfd-single-file-button.css';
            wp_enqueue_style(
                'wpfd-single-file-css',
                plugins_url('app/admin/assets/ui/css/singlefile.css', WPFD_PLUGIN_FILE),
                array(),
                WPFD_VERSION
            );
            if (file_exists($customizeCssPath)) {
                // Using hash to reload file
                $hash = get_option('wpfd_single_file_css_hash', WPFD_VERSION);
                wp_enqueue_style(
                    'wpfd-single-file-button',
                    wpfd_abs_path_to_url($customizeCssPath),
                    array('wpfd-single-file-css'),
                    $hash
                );
                wp_add_inline_style('wpfd-single-file-button', $singleParams['custom_css']);
            } else {
                wp_enqueue_style(
                    'wpfd-single-file-button',
                    plugins_url('app/site/assets/css/wpfd-single-file-button.css', WPFD_PLUGIN_FILE),
                    array('wpfd-single-file-css'),
                    WPFD_VERSION
                );
            }

            // Get current file icon url
            $baseIconSet = isset($singleParams['base_icon_set']) ? $singleParams['base_icon_set'] : 'png';
            $file->icon_style = '';
            $iconUrl = WpfdHelperFile::getUploadedIconPath($file->ext, $baseIconSet);

            $isCustomIcon = false;
            if (isset($file->file_custom_icon) && $file->file_custom_icon !== '') {
                $iconUrl = site_url() . $file->file_custom_icon;
                $isCustomIcon = true;
            }

            if ($baseIconSet === 'default') {
                $backgroundSize = 'background-size: contain;background-position: center center;';
            } else {
                $backgroundSize = 'background-size: 100%;';
            }
            $file->icon_style .= 'background-image: url("' . esc_url($iconUrl) . '");';
            if ($baseIconSet === 'svg' && !$isCustomIcon) {
                $iconParam = $modelIconsBuilder->getIconParams($baseIconSet, $file->ext);
                if (false !== $iconParam) {
                    if (intval($iconParam['wrapper-active']) === 1) {
                        $customCss = isset($iconParam['border-radius']) && intval($iconParam['border-radius']) > 0 ? 'border-radius: ' . $iconParam['border-radius'] . '%;' : '';
                        $customCss .= 'box-shadow: ' . $iconParam['horizontal-position'] . 'px ' . $iconParam['vertical-position'] . 'px ' . $iconParam['blur-radius'] . 'px ' . $iconParam['spread-radius'] . 'px ' . $iconParam['shadow-color'] . ';';
                        $customCss .= 'background-color: ' . $iconParam['background-color'] . ';';
                        $customCss .= 'border: ' . $iconParam['border-size'] . 'px solid ' . $iconParam['border-color'] . ';';
                        $file->icon_style .= $customCss;
                    }
                }
            }
            $file->icon_style .= $backgroundSize;
            $file->size = WpfdHelperFile::bytesToSize($file->size);
            $previewType = WpfdBase::loadValue($config, 'use_google_viewer', 'lightbox');
            if ($previewType === 'lightbox') {
                $file->open_in_lightbox = true;
            } elseif ($previewType === 'tab') {
                $file->open_in_newtab = true;
            } else {
                $classes[] = 'noLightbox';
            }
            // Hide preview link on no link available
            if (isset($file->openpdflink) && $file->openpdflink === '' && $file->viewerlink === '') {
                $singleParams['link_on_icon'] = 'none';
            }

            $template = wpfd_get_template_html('tpl-single2.php');
            $data = array(
                'settings' => $singleParams,
                'file' => json_decode(json_encode($file), true),
                'config' => $config
            );

            $html = wpfdHandlerbarsRender($template, $data, 'singlefile');
        }

        if ((int) $file->social === 1 && defined('WPFDA_VERSION')) {
            return do_shortcode('[wpfdasocial]' . $html . '[/wpfdasocial]');
        } else {
            return $html;
        }
    }

    /**
     * Call category theme
     *
     * @param mixed   $param           Category id
     * @param boolean $shortcode_param Shortcode Param
     *
     * @return string
     */
    public function callTheme($param, $shortcode_param = false)
    {
        wp_enqueue_style(
            'wpfd-front',
            plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );

        $app = Application::getInstance('Wpfd');

        $path_wpfdbase = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
        $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
        require_once $path_wpfdbase;

        $modelConfig     = Model::getInstance('configfront');
        $modelFiles      = Model::getInstance('filesfront');
        $modelCategories = Model::getInstance('categoriesfront');
        $modelCategory   = Model::getInstance('categoryfront');
        $modelTokens     = Model::getInstance('tokens');

        $global_settings = $this->globalConfig;



        // Check and generate missing SVG icons
        $category        = $modelCategory->getCategory($param);
        if (empty($category)) {
            return '';
        }
        $themename = $category->params['theme'];

        $lastRebuildTime = get_option('wpfd_icon_rebuild_time', false);
        if (false === $lastRebuildTime) {
            // Icon CSS was never build, build it
            $lastRebuildTime = WpfdHelperFile::renderCss();
        }

        $iconSet = (isset($global_settings['icon_set'])) ? $global_settings['icon_set'] : 'default';
        if ($iconSet !== 'default' && in_array($iconSet, array('png', 'svg'))) {
            $path = WpfdHelperFile::getCustomIconPath($iconSet);
            $cssPath = $path . 'styles-' . $lastRebuildTime . '.css';
            if (file_exists($cssPath)) {
                $cssUrl = wpfd_abs_path_to_url($cssPath);
            } else {
                $lastRebuildTime = WpfdHelperFile::renderCss();
                $cssPath = $path . 'styles-' . $lastRebuildTime . '.css';
                if (file_exists($cssPath)) {
                    $cssUrl = wpfd_abs_path_to_url($cssPath);
                } else {
                    // Use default css pre-builed
                    $cssUrl = WPFD_PLUGIN_URL . 'app/site/assets/icons/' . $iconSet . '/icon-styles.css';
                }
            }
            // Include file
            wp_enqueue_style(
                'wpfd-style-icon-set-' . $iconSet,
                $cssUrl,
                array('wpfd-theme-' . $themename),
                WPFD_VERSION
            );
        }

        $params = $category->params;
        if (isset($global_settings['catparameters']) && (int) $global_settings['catparameters'] === 0) {
            $defaultTheme = $global_settings['defaultthemepercategory'];
            $defaultParams = $modelConfig->getConfig($defaultTheme);
            foreach ($params as $key => $value) {
                if (isset($defaultParams[$key])) {
                    $params[$key] = $defaultParams[$key];
                }
            }
        }
        $params['social'] = isset($params['social']) ? $params['social'] : 0;
        if ((int) $category->access === 1) {
            $user = wp_get_current_user();
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);

            $singleuser = false;

            if (isset($params['canview']) && $params['canview'] === '') {
                $params['canview'] = 0;
            }

            $canview = isset($params['canview']) ? (int) $params['canview'] : 0;

            if ((int) $global_settings['restrictfile'] === 1) {
                $user = wp_get_current_user();
                $user_id = $user->ID;

                if ($user_id) {
                    if ($canview === $user_id || $canview === 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                } else {
                    if ($canview === 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                }
            }
            if ($canview !== 0 && !count($category->roles)) {
                if ($singleuser === false) {
                    return '';
                }
            } elseif ($canview !== 0 && count($category->roles)) {
                if (!(!empty($allows) || ($singleuser === true))) {
                    return '';
                }
            } else {
                if (empty($allows)) {
                    return '';
                }
            }
        }
        if (isset($global_settings['use_google_viewer']) && $global_settings['use_google_viewer'] === 'lightbox') {
            wp_enqueue_script('wpfd-colorbox', plugins_url('app/site/assets/js/jquery.colorbox-min.js', WPFD_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script(
                'wpfd-colorbox-init',
                plugins_url('app/site/assets/js/colorbox.init.js', WPFD_PLUGIN_FILE),
                array('jquery', 'wpfd-colorbox'),
                WPFD_VERSION
            );
            wp_localize_script(
                'wpfd-colorbox-init',
                'wpfdcolorboxvars',
                array(
                    'preview_loading_message' => sprintf(esc_html__('The preview is still loading, you can %s it at any time...', 'wpfd'), '<span class="wpfd-loading-close">' . esc_html__('cancel', 'wpfd') . '</span>'),
                )
            );
            wp_enqueue_style(
                'wpfd-colorbox',
                plugins_url('app/site/assets/css/colorbox.css', WPFD_PLUGIN_FILE),
                array(),
                WPFD_VERSION
            );
            wp_enqueue_style(
                'wpfd-viewer',
                plugins_url('app/site/assets/css/viewer.css', WPFD_PLUGIN_FILE),
                array(),
                WPFD_VERSION
            );
        }
        /**
         * Get theme instance follow priority
         *
         * 1. /wp-content/wp-file-download/themes
         * 2. /wp-content/uploads/wpfd-themes
         * 3. /wp-content/plugins/wp-file-download/app/site/themes
         */
        $theme = wpfd_get_theme_instance($themename);

        // Set theme params, separator it to made sure theme can work well
        if (method_exists($theme, 'setAjaxUrl')) {
            $theme->setAjaxUrl(wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()));
        }

        if (method_exists($theme, 'setConfig')) {
            $theme->setConfig($global_settings);
        }

        if (method_exists($theme, 'setPath')) {
            $theme->setPath(Application::getInstance('Wpfd')->getPath());
        }

        if (method_exists($theme, 'setThemeName')) {
            $theme->setThemeName($themename);
        }

        $token = $modelTokens->getOrCreateNew();

        $tpl = null;
        $category = $modelCategory->getCategory($param);

        $orderCol = Utilities::getInput('orderCol', 'GET', 'none');
        $ordering = $orderCol !== null ? $orderCol : $category->ordering;
        $orderDir = Utilities::getInput('orderDir', 'GET', 'none');
        $orderingdir = $orderDir !== null ? $orderDir : $category->orderingdir;

        $categories = $modelCategories->getCategories($param);
        $description = json_decode($category->description, true);
        $lstAllFile = null;

        if (!empty($description) && isset($description['refToFile'])) {
            if (isset($description['refToFile'])) {
                $listCatRef = $description['refToFile'];
                $lstAllFile = $this->getAllFileRef($modelFiles, $listCatRef, $ordering, $orderingdir);
            }
        }

        if ($shortcode_param && isset($shortcode_param['order']) && !empty($shortcode_param['order'])) {
            $ordering = $shortcode_param['order'];
        }
        if ($shortcode_param && isset($shortcode_param['direction']) && !empty($shortcode_param['direction'])) {
            $orderingdir = $shortcode_param['direction'];
        }
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */

        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $param);
        if ($categoryFrom === 'googleDrive') {
            $tpl = 'googleDrive';
            $files = apply_filters(
                'wpfdAddonGetListGoogleDriveFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );

            $categories = $modelCategories->getCategories($param);
        } elseif ($categoryFrom === 'dropbox') {
            $tpl = 'dropbox';
            $files = apply_filters(
                'wpfdAddonGetListDropboxFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
            $categories = $modelCategories->getCategories($param);
        } elseif ($categoryFrom === 'onedrive') {
            $tpl = 'onedrive';
            $files = apply_filters(
                'wpfdAddonGetListOneDriveFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
            $categories = $modelCategories->getCategories($param);
        } elseif ($categoryFrom === 'onedrive_business') {
            $tpl = 'onedrive_business';
            $files = apply_filters(
                'wpfdAddonGetListOneDriveBusinessFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );

            $categories = $modelCategories->getCategories($param);
        } else {
            $files = $modelFiles->getFiles($param, $ordering, $orderingdir);
            if (!empty($files) && ((int) $global_settings['restrictfile'] === 1)) {
                foreach ($files as $key => $file) {
                    $metadata = get_post_meta($file->ID, '_wpfd_file_metadata', true);
                    $canview = isset($metadata['canview']) ? $metadata['canview'] : 0;
                    $files[$key]->canview = $canview;
                }
            }
        }

        // Check permissiong for User allow to access file feature
        if (is_array($files) && !empty($files) && ((int) $global_settings['restrictfile'] === 1)) {
            $user    = wp_get_current_user();
            $user_id = $user->ID;
            foreach ($files as $key => $file) {
                if (!isset($file->canview)) {
                    continue;
                }
                $canview = array_map('intval', explode(',', $file->canview));
                if ($user_id) {
                    if (!(in_array($user_id, $canview) || in_array(0, $canview))) {
                        unset($files[$key]);
                    }
                } else {
                    if (!in_array(0, $canview)) {
                        unset($files[$key]);
                    }
                }
            }
        }

        if ($lstAllFile && !empty($lstAllFile)) {
            $files = array_merge($lstAllFile, $files);
        }

        // Reorder for correct ordering
        $ordering_array = array(
            'created_time', 'modified_time', 'hits', 'size', 'ext', 'version', 'title', 'description', 'ordering');
        if (is_array($files) && in_array($ordering, $ordering_array)) {
            switch ($ordering) {
                case 'created_time':
                    usort($files, array('WpfdHelperShortcodes', 'cmpCreated'));
                    break;
                case 'modified_time':
                    usort($files, array('WpfdHelperShortcodes', 'cmpUpdated'));
                    break;
                case 'hits':
                    usort($files, array('WpfdHelperShortcodes', 'cmpHits'));
                    break;
                case 'size':
                    usort($files, array('WpfdHelperShortcodes', 'cmpSize'));
                    break;
                case 'ext':
                    usort($files, array('WpfdHelperShortcodes', 'cmpExt'));
                    break;
                case 'version':
                    usort($files, array('WpfdHelperShortcodes', 'cmpVersionNumber'));
                    break;
                case 'description':
                    usort($files, array('WpfdHelperShortcodes', 'cmpDescription'));
                    break;
                case 'ordering':
                    break;
                case 'title':
                default:
                    usort($files, array('WpfdHelperShortcodes', 'cmpTitle'));
                    break;
            }
            if (strtoupper($orderingdir) === 'DESC') {
                $files = array_reverse($files);
            }
        }

        $limit = $global_settings['paginationnunber'];
        $total = (is_array($files)) ? ceil(count($files) / $limit) : 0;

        $page = Utilities::getInput('paged', 'POST', 'string');
        $page = $page !== '' ? $page : 1;
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        if ($theme->getThemeName() !== 'tree') {
            $files = (is_array($files)) ? array_slice($files, $offset, $limit) : array();
        }

        $filesx = array();
        // Crop file titles
        if (is_array($files) && !empty($files)) {
            foreach ($files as $i => $file) {
                if (isset($file->state) && (int) $file->state === 0) {
                    continue;
                }
                $filesx[$i]             = $file;
                $filesx[$i]->crop_title = WpfdBase::cropTitle($params, $theme->getThemeName(), $file->post_title);
                if (isset($file->file_custom_icon) && $file->file_custom_icon !== '') {
                    if (strpos($file->file_custom_icon, site_url()) !== 0) {
                        $filesx[$i]->file_custom_icon = site_url() . $file->file_custom_icon;
                    }
                }

                $filesx[$i]->iconset = (isset($global_settings['icon_set']) && $global_settings['icon_set'] !== 'default') ? ' wpfd-icon-set-' . $global_settings['icon_set'] : '';
            }
            unset($files);
            $files = $filesx;
        }

        if ($shortcode_param && isset($shortcode_param['number']) &&
            !empty($shortcode_param['number']) &&
            (is_numeric($shortcode_param['number']) &&
                (int)$shortcode_param['number'] > 0)
        ) {
            $files = array_slice($files, 0, $shortcode_param['number']);
        }

        $options = array('files' => $files,
            'category' => $category,
            'categories' => $categories,
            'ordering' => $ordering,
            'orderingDirection' => $orderingdir,
            'params' => $params,
            'tpl' => $tpl);
        if ((int) $params['social'] === 1 && defined('WPFDA_VERSION')) {
            $content = do_shortcode(
                '[wpfdasocial]' . $theme->showCategory($options) . ($category->params['theme'] !== 'tree' ?
                    wpfd_category_pagination(
                        array('base' => '', 'format' => '', 'current' => max(1, $page), 'total' => $total, 'sourcecat' => $param)
                    ) : ''
                ) . '[/wpfdasocial]'
            );
        } else {
            $content = $theme->showCategory($options) . ($category->params['theme'] !== 'tree' ?
                    wpfd_category_pagination(
                        array('base' => '', 'format' => '', 'current' => max(1, $page), 'total' => $total, 'sourcecat' => $param)
                    ) : ''
                );
        }
        return $content;
    }

    /**
     * Get content all Category
     *
     * @param boolean $shortcode_param Shortcode params
     *
     * @return string
     */
    public function contentAllCat($shortcode_param = false)
    {
        wp_enqueue_style(
            'wpfd-front',
            plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );

        $app = Application::getInstance('Wpfd');
        $allFiles = array();
        $files = array();
        if ($shortcode_param && isset($shortcode_param['number']) && !empty($shortcode_param['number']) &&
            (is_numeric($shortcode_param['number']) && (int)$shortcode_param['number'] > 0)
        ) {
            $param_number = $shortcode_param['number'];
        }
        $path_wpfdbase = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
        $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
        require_once $path_wpfdbase;
        $modelCategories = Model::getInstance('categoriesfront');
        $categories = $modelCategories->getLevelCategories();
        $modelConfig = Model::getInstance('configfront');
        $global_settings = $this->globalConfig;

        foreach ($categories as $keyCat => $category) {
            $termId = $category->term_id;
            if (!is_numeric($termId) && isset($category->wp_term_id)) {
                $termId = $category->wp_term_id;
            }
            $allFile1 = $this->fileAllCat($termId, $shortcode_param);
            if (!empty($allFile1)) {
                foreach ($allFile1 as $key => $val) {
                    if (!empty($val)) {
                        $allFiles[] = $val;
                    }
                }
            }
        }

        $ordering = 'created_time';
        $orderingdir = 'desc';
        if ($shortcode_param && isset($shortcode_param['order']) && !empty($shortcode_param['order'])) {
            $ordering = $shortcode_param['order'];
        }
        if ($shortcode_param && isset($shortcode_param['direction']) && !empty($shortcode_param['direction'])) {
            $orderingdir = $shortcode_param['direction'];
        }

        $ordering_array = array(
            'created_time', 'modified_time', 'hits', 'size', 'ext', 'version', 'title', 'description', 'ordering');
        if (in_array($ordering, $ordering_array)) {
            switch ($ordering) {
                case 'created_time':
                    usort($allFiles, array('WpfdHelperShortcodes', 'cmpCreated'));
                    break;
                case 'modified_time':
                    usort($allFiles, array('WpfdHelperShortcodes', 'cmpUpdated'));
                    break;
                case 'hits':
                    usort($allFiles, array('WpfdHelperShortcodes', 'cmpHits'));
                    break;
                case 'size':
                    usort($allFiles, array('WpfdHelperShortcodes', 'cmpSize'));
                    break;
                case 'ext':
                    usort($allFiles, array('WpfdHelperShortcodes', 'cmpExt'));
                    break;
                case 'version':
                    usort($allFiles, array('WpfdHelperShortcodes', 'cmpVersionNumber'));
                    break;
                case 'description':
                    usort($allFiles, array('WpfdHelperShortcodes', 'cmpDescription'));
                    break;
                case 'ordering':
                    usort($allFiles, array('WpfdHelperShortcodes', 'cmpTitle'));
                    break;
                default:
                    usort($allFiles, array('WpfdHelperShortcodes', 'cmpTitle'));
                    break;
            }
            if (strtoupper($orderingdir) === 'DESC') {
                $allFiles = array_reverse($allFiles);
            }
        }

        $modelCategory = Model::getInstance('categoryfront');
        if (is_array($categories) && is_countable($categories) && count($categories) === 0) {
            return '';
        }
        $termId = $categories[0]->term_id;
        if (!is_numeric($termId) && isset($categories[0]->wp_term_id)) {
            $termId = $categories[0]->wp_term_id;
        }
        $category = $modelCategory->getCategory($termId);

        // Show categories or not on all categories
        $params['show_categories'] = '0';
        if (isset($shortcode_param['show_categories']) && ((int) $shortcode_param['show_categories'] === 1)) {
            $categories = array_filter($categories, function ($category) {
                if ($category->parent === 0) {
                    return true;
                }
            });
            $categories = array_values($categories);
            $params['show_categories'] = '1';
        } else {
            $categories = array();
        }

        // Global theme parameter
        $modelConfig = Model::getInstance('configfront');
        $main_config = $this->globalConfig;
        $defaultTheme = $main_config['defaultthemepercategory'];
        $params = $modelConfig->getConfig($defaultTheme);

        $lastRebuildTime = get_option('wpfd_icon_rebuild_time', false);
        if (false === $lastRebuildTime) {
            // Icon CSS was never build, build it
            $lastRebuildTime = WpfdHelperFile::renderCss();
        }

        $iconSet = (isset($global_settings['icon_set'])) ? $global_settings['icon_set'] : 'default';
        if ($iconSet !== 'default' && in_array($iconSet, array('png', 'svg'))) {
            $path = WpfdHelperFile::getCustomIconPath($iconSet);
            $cssPath = $path . 'styles-' . $lastRebuildTime . '.css';
            if (file_exists($cssPath)) {
                $cssUrl = wpfd_abs_path_to_url($cssPath);
            } else {
                $lastRebuildTime = WpfdHelperFile::renderCss();
                $cssPath = $path . 'styles-' . $lastRebuildTime . '.css';
                if (file_exists($cssPath)) {
                    $cssUrl = wpfd_abs_path_to_url($cssPath);
                } else {
                    // Use default css pre-builed
                    $cssUrl = WPFD_PLUGIN_URL . 'app/site/assets/icons/' . $iconSet . '/icon-styles.css';
                }
            }
            // Include file
            wp_enqueue_style(
                'wpfd-style-icon-set-' . $iconSet,
                $cssUrl,
                array('wpfd-theme-' . $defaultTheme),
                WPFD_VERSION
            );
        }
        
        $prefix = '';
        if ($defaultTheme !== 'default') {
            $prefix = $defaultTheme . '_';
        }
        // Disable breadcrumb
        $params[$prefix . 'showbreadcrumb'] = '0';
        // Disable category name
        $params[$prefix . 'showcategorytitle'] = '0';
        // Remove wpfd-categories element
//        $params['show_categories'] = '0';
        if (!class_exists('WpfdTheme')) {
            $themeclass = realpath(dirname(WPFD_PLUGIN_FILE)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'templates';
            $themeclass .= DIRECTORY_SEPARATOR . 'wpfd-theme.class.php';
            require_once $themeclass;
        }
        /**
         * Get theme instance follow priority
         *
         * 1. /wp-content/wp-file-download/themes
         * 2. /wp-content/uploads/wpfd-themes
         * 3. /wp-content/plugins/wp-file-download/app/site/themes
         */
        $theme = wpfd_get_theme_instance($defaultTheme);

        // Set theme params, separator it to made sure theme can work well
        if (method_exists($theme, 'setAjaxUrl')) {
            $theme->setAjaxUrl(wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()));
        }

        $global_settings['download_selected'] = 0; // Not allow download categories here
        $global_settings['download_category'] = 0;
        if (method_exists($theme, 'setConfig')) {
            $theme->setConfig($global_settings);
        }

        if (method_exists($theme, 'setPath')) {
            $theme->setPath(Application::getInstance('Wpfd')->getPath());
        }

        if (method_exists($theme, 'setThemeName')) {
            $theme->setThemeName($defaultTheme);
        }

        $files = $allFiles;
        $limit = $global_settings['paginationnunber'];
        $total = ceil(count($files) / $limit);
        if (isset($param_number) && $param_number) {
            $files = array_slice($files, 0, $param_number);
        }

        $page = Utilities::getInput('paged', 'POST', 'string');
        $page = $page !== '' ? $page : 1;

        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        if ($theme->getThemeName() !== 'tree') {
            $files = array_slice($files, $offset, $limit);
        }

        $filesx = array();
        // Crop file titles
        if (is_array($files) && !empty($files)) {
            foreach ($files as $i => $file) {
                if (isset($file->state) && (int) $file->state === 0) {
                    continue;
                }
                $filesx[$i]             = $file;
                $filesx[$i]->crop_title = WpfdBase::cropTitle($params, $theme->getThemeName(), $file->post_title);
            }
            unset($files);
            $files = $filesx;
        }
//        $category->term_id = 'all-' . $category->term_id;
        $category->name = esc_html__('All Categories', 'wpfd');
        $category->slug = sanitize_title($category->name);
        $category->term_id = 'all_0';
        $options = array(
            'files' => $files,
            'category' => $category,
            'categories' => $categories,
            'ordering' => $ordering,
            'orderingDirection' => $orderingdir,
            'params' => $params,
            'tpl' => null,
            'latest' => false // True: Disable show categories
        );
        $pagination = '';

        if (isset($category->params['theme']) && $category->params['theme'] !== 'tree') {
            $pagination = wpfd_category_pagination(
                array('base' => '', 'format' => '', 'current' => max(1, $page), 'total' => $total, 'sourcecat' => 0)
            );
        }
        // We need to disable pagination on content all cat so temporary
        // todo: fix pagination for content all cat
        $content = $theme->showCategory($options) . $pagination;
        return $content;
    }

    /**
     * Get files all cat
     *
     * @param mixed   $param           Category id
     * @param boolean $shortcode_param Shortcode param
     *
     * @return array|mixed
     */
    public function fileAllCat($param, $shortcode_param = false)
    {
        Application::getInstance('Wpfd');

        $modelCategory = Model::getInstance('categoryfront');
        $global_settings = $this->globalConfig;
        $category = $modelCategory->getCategory($param);
        $param_number = null;
        if ($shortcode_param && isset($shortcode_param['number']) && !empty($shortcode_param['number']) &&
            (is_numeric($shortcode_param['number']) && (int)$shortcode_param['number'] > 0)
        ) {
            $param_number = $shortcode_param['number'];
        }

        if (empty($category)) {
            return '';
        }
        //$themename = $category->params['theme'];
        $params = $category->params;
        $params['social'] = isset($params['social']) ? $params['social'] : 0;
        if ((int) $category->access === 1) {
            $user = wp_get_current_user();
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);

            $singleuser = false;

            if (isset($params['canview']) && $params['canview'] === '') {
                $params['canview'] = 0;
            }

            $canview = isset($params['canview']) ? (int) $params['canview'] : 0;

            if ((int) $global_settings['restrictfile'] === 1) {
                $user = wp_get_current_user();
                $user_id = (int) $user->ID;

                if ($user_id) {
                    if ($canview === $user_id || $canview === 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                } else {
                    if ($canview === 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                }
            }

            if ($canview !== 0 && !count($category->roles)) {
                if ($singleuser === false) {
                    return '';
                }
            } elseif ($canview !== 0 && count($category->roles)) {
                if (empty($allows) && !$singleuser) {
                    return '';
                }
            } else {
                if (empty($allows)) {
                    return '';
                }
            }
        }
        Application::getInstance('Wpfd');
        $modelFiles = Model::getInstance('filesfront');
        $modelCategory = Model::getInstance('categoryfront');
        $modelTokens = Model::getInstance('tokens');

        $token = $modelTokens->getOrCreateNew();

        $tpl = null;
        $category = $modelCategory->getCategory($param);
        $orderCol = Utilities::getInput('orderCol', 'GET', 'none');
        $ordering = $orderCol !== null ? $orderCol : $category->ordering;
        $orderDir = Utilities::getInput('orderDir', 'GET', 'none');
        $orderingdir = $orderDir !== null ? $orderDir : $category->orderingdir;

        $description = json_decode($category->description, true);
        $lstAllFile = null;
        if ($shortcode_param && isset($shortcode_param['order']) && !empty($shortcode_param['order'])) {
            $ordering = $shortcode_param['order'];
        }
        if ($shortcode_param && isset($shortcode_param['direction']) && !empty($shortcode_param['direction'])) {
            $orderingdir = $shortcode_param['direction'];
        }

        if (!empty($description) && isset($description['refToFile'])) {
            if (isset($description['refToFile'])) {
                $listCatRef = $description['refToFile'];
                $lstAllFile = $this->getAllFileRef($modelFiles, $listCatRef, $ordering, $orderingdir, $param);
            }
        }
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $param);
        if ($categoryFrom === 'googleDrive') {
            $files = apply_filters(
                'wpfdAddonGetListGoogleDriveFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
        } elseif ($categoryFrom === 'dropbox') {
            $files = apply_filters(
                'wpfdAddonGetListDropboxFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
        } elseif ($categoryFrom === 'onedrive') {
            $files = apply_filters(
                'wpfdAddonGetListOneDriveFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
        } elseif ($categoryFrom === 'onedrive_business') {
            $files = apply_filters(
                'wpfdAddonGetListOneDriveBusinessFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
        } else {
            $files = $modelFiles->getFiles($param, $ordering, $orderingdir);

            if (!empty($files) && ((int) $global_settings['restrictfile'] === 1)) {
                $user = wp_get_current_user();
                $user_id = $user->ID;
                foreach ($files as $key => $file) {
                    $metadata = get_post_meta($file->ID, '_wpfd_file_metadata', true);
                    $canview = isset($metadata['canview']) ? $metadata['canview'] : 0;
                    $canview = array_map('intval', explode(',', $canview));
                    if ($user_id) {
                        if (!(in_array($user_id, $canview) || in_array(0, $canview))) {
                            unset($files[$key]);
                        }
                    } else {
                        if (!in_array(0, $canview)) {
                            unset($files[$key]);
                        }
                    }
                }
            }
        }

        if ($lstAllFile && !empty($lstAllFile)) {
            $files = array_merge($lstAllFile, $files);
        }

        return $files;
    }

    /**
     * Get all files reference category
     *
     * @param object  $model             File model
     * @param array   $listCatRef        List Categories
     * @param string  $ordering          Ordering
     * @param string  $orderingDirection Ordering direction
     * @param integer $refCatId          Ref cat id
     *
     * @return array
     */
    public function getAllFileRef($model, $listCatRef, $ordering, $orderingDirection, $refCatId = null)
    {
        $lstAllFile = array();
        foreach ($listCatRef as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $lstFile    = $model->getFiles($key, $ordering, $orderingDirection, $value, $refCatId);
                $lstAllFile = array_merge($lstFile, $lstAllFile);
            }
        }
        return $lstAllFile;
    }

    /**
     * Method to compare by property
     *
     * @param object $a        First object
     * @param object $b        Second object
     * @param string $property Property to sort
     * @param string $type     Type
     *
     * @return boolean|integer
     */
    public function compareByProperty($a, $b, $property, $type = 'string')
    {
        switch ($type) {
            case 'datetime':
                $result = (strtotime($a->{$property}) < strtotime($b->{$property})) ? -1 : 1;
                break;
            case 'number':
                $result = ($a->{$property} > $b->{$property});
                break;
            case 'string':
            default:
                $result = strnatcmp($a->{$property}, $b->{$property});
                break;
        }
        return $result;
    }

    /**
     * Method to compare created
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    public function cmpCreated($a, $b)
    {
        return $this->compareByProperty($a, $b, 'created_time', 'datetime');
    }

    /**
     * Method to compare updated
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    public function cmpUpdated($a, $b)
    {
        return $this->compareByProperty($a, $b, 'modified_time', 'datetime');
    }

    /**
     * Method to compare hits
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    public function cmpHits($a, $b)
    {
        return $this->compareByProperty($a, $b, 'hits', 'number');
    }

    /**
     * Method to compare size
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    public function cmpSize($a, $b)
    {
        return $this->compareByProperty($a, $b, 'size', 'number');
    }

    /**
     * Method to compare ext
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    public function cmpExt($a, $b)
    {
        return $this->compareByProperty($a, $b, 'ext', 'string');
    }

    /**
     * Method to compare version
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    public function cmpVersionNumber($a, $b)
    {
        return $this->compareByProperty($a, $b, 'versionNumber', 'string');
    }


    /**
     * Method to compare Description
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    public function cmpDescription($a, $b)
    {
        return $this->compareByProperty($a, $b, 'description', 'string');
    }

    /**
     * Method to compare title
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    public function cmpTitle($a, $b)
    {
        return $this->compareByProperty($a, $b, 'post_title', 'string');
    }
    /**
     * Check permission for single post
     *
     * @param mixed $post Post object
     * @param mixed $user Current user object
     *
     * @return boolean
     */
    public function wpfdCheckAccess($post, $user)
    {
        $app = Application::getInstance('Wpfd');
        $fileModel = Model::getInstance('filefront');
        $categoryModel = Model::getInstance('categoryfront');

        $file = $fileModel->getFile($post->ID);

        if (!$file) {
            return false;
        }

        $category = $categoryModel->getCategory($file->catid);
        if (empty($category) || is_wp_error($category)) {
            return false;
        }

        if ((int) $category->access === 1) {
            $roles = array();

            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }

            $allows = array_intersect($roles, $category->roles);
            $allows_single = false;

            if (isset($category->params['canview']) && $category->params['canview'] === '') {
                $category->params['canview'] = 0;
            }
            if (isset($category->params['canview']) &&
                ((int) $category->params['canview'] !== 0) &&
                is_countable($category->roles) && // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                !count($category->roles)
            ) {
                if ((int) $category->params['canview'] === (int) $user->ID) {
                    $allows_single = true;
                }
                if ($allows_single === false) {
                    return false;
                }
            } elseif (isset($category->params['canview']) &&
                ((int) $category->params['canview'] !== 0) &&
                is_countable($category->roles) && // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                count($category->roles)
            ) {
                if ((int) $category->params['canview'] === (int) $user->ID) {
                    $allows_single = true;
                }

                if (!($allows_single === true || !empty($allows))) {
                    return false;
                }
            } else {
                if (empty($allows)) {
                    return false;
                }
            }
        }
        return $file;
    }
    /**
     * Search shortcode
     *
     * @param string $atts Shortcode attributes
     *
     * @return string
     */
    public function wpfdSearchShortcode($atts)
    {
        wpfd_enqueue_assets();
        wpfd_assets_search();
        wp_enqueue_style(
            'wpfd-front',
            plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );

        $variables = array(
            'args' => array(),
            'filters' => array(),
            'categories' => array(),
            'allTagsFiles' => '',
            'TagLabels' => array()
        );
        $variables['args'] = shortcode_atts(array(
            'catid' => '0',
            'exclude' => '0',
            'cat_filter' => 1,
            'tag_filter' => 0,
            'display_tag' => 'searchbox',
            'create_filter' => 1,
            'update_filter' => 1,
            'file_per_page' => 15,
            'theme' => ''
        ), $atts);

        $q = Utilities::getInput('q', 'GET', 'string');
        if (!empty($q)) {
            $variables['filters']['q'] = $q;
        }
        $catid = Utilities::getInput('catid', 'GET', 'string');

        if (!empty($catid)) {
            $variables['filters']['catid'] = $catid;
        }

        // Use default catid in shortcode param
        $rootCategoryId = 0;
        if ((int) $variables['args']['cat_filter'] === 0 && (int) $variables['args']['catid'] !== 0) {
            $variables['filters']['catid'] = (string) $variables['args']['catid'];
        }

        if ((int) $variables['args']['cat_filter'] !== 0 && (int) $variables['args']['catid'] !== 0) {
            $rootCategoryId = $variables['args']['catid'];
            $rootCategory = get_term($rootCategoryId, 'wpfd-category');
            $variables['catname'] = $rootCategory->name;
        }

        if ($variables['args']['exclude'] !== '') {
            $variables['filters']['exclude'] = $variables['args']['exclude'];
        }

        $ftags = Utilities::getInput('ftags', 'GET', 'none');
        if (is_array($ftags)) {
            $ftags = array_unique($ftags);
            $ftags = implode(',', $ftags);
        } else {
            $ftags = Utilities::getInput('ftags', 'GET', 'string');
        }

        if (!empty($ftags)) {
            $variables['filters']['ftags'] = $ftags;
        }

        $cfrom = Utilities::getInput('cfrom', 'GET', 'string');
        if (!empty($cfrom)) {
            $variables['filters']['cfrom'] = $cfrom;
        }
        $cto = Utilities::getInput('cto', 'GET', 'string');
        if (!empty($cto)) {
            $variables['filters']['cto'] = $cto;
        }
        $ufrom = Utilities::getInput('ufrom', 'GET', 'string');
        if (!empty($ufrom)) {
            $variables['filters']['ufrom'] = $ufrom;
        }
        $uto = Utilities::getInput('uto', 'GET', 'string');
        if (!empty($uto)) {
            $variables['filters']['uto'] = $uto;
        }
        $limit = Utilities::getInput('limit', 'GET', 'string');
        if (empty($limit)) {
            $limit = $variables['args']['file_per_page'];
        }
        $variables['filters']['limit'] = $limit;
        $variables['ordering'] = Utilities::getInput('ordering', 'GET', 'string');
        $variables['dir'] = Utilities::getInput('dir', 'GET', 'string') === null ? 'asc' : 'desc';

        $app = Application::getInstance('Wpfd');
        $modelCategories = Model::getInstance('categoriesfront');
        $modelConfig = Model::getInstance('configfront');

        $theme = Utilities::getInput('theme', 'GET', 'string');
        $themes = $modelConfig->getThemes();

        if ($theme !== '' && in_array($theme, $themes)) {
            $variables['args']['theme'] = $theme;
        }
        $theme = isset($variables['args']['theme']) ? $variables['args']['theme'] : '';
        if ($theme !== '') {
            $params = $modelConfig->getConfig($theme);
            $config = $modelConfig->getGlobalConfig();
            if ($theme === 'default') {
                $params['showfoldertree'] = 0;
                $params['showsubcategories'] = 0;
                $params['showcategorytitle'] = 0;
                $params['showbreadcrumb'] = 0;
                $params['download_popup'] = 0;
                $params['download_selected'] = 0;
                $params['download_category'] = 0;
            } else {
                $params[$theme . '_showfoldertree'] = 0;
                $params[$theme . '_showsubcategories'] = 0;
                $params[$theme . '_showcategorytitle'] = 0;
                $params[$theme . '_showbreadcrumb'] = 0;
                $params[$theme . '_download_popup'] = 0;
                $params[$theme . '_download_selected'] = 0;
                $params[$theme . '_download_category'] = 0;
            }
            /**
             * Get theme instance follow priority
             *
             * 1. /wp-content/wp-file-download/themes
             * 2. /wp-content/uploads/wpfd-themes
             * 3. /wp-content/plugins/wp-file-download/app/site/themes
             */
            $themeInstance = wpfd_get_theme_instance($theme);
            // Set theme params, separator it to made sure theme can work well
            if (method_exists($themeInstance, 'setAjaxUrl')) {
                $themeInstance->setAjaxUrl(wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()));
            }

            if (method_exists($themeInstance, 'setConfig')) {
                $themeInstance->setConfig($config);
            }

            if (method_exists($themeInstance, 'setPath')) {
                $themeInstance->setPath(Application::getInstance('Wpfd')->getPath());
            }

            if (method_exists($themeInstance, 'setThemeName')) {
                $themeInstance->setThemeName($theme);
            }
            wp_enqueue_style(
                'wpfd-front',
                plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
                array(),
                WPFD_VERSION
            );
            $themeInstance->loadAssets();
            $themeInstance->loadLightboxAssets();
        }


        $variables['categories'] = $modelCategories->getLevelCategories($rootCategoryId);
        $variables['config'] = $this->globalConfig;

        $tags = get_terms(array(
            'taxonomy'   => 'wpfd-tag',
            'orderby'    => 'name',
            'order'      => 'ASC',
            'hide_empty' => 0,
        ));

        if ($tags) {
            $variables['availableTags'] = array();
            foreach ($tags as $tag) {
                $TagsFiles[$tag->term_id] = '' . esc_attr($tag->slug);
                $variables['TagLabels'][$tag->term_id] = esc_html($tag->name);
//                $availableTags[] = '{id: '.$tag->term_id.',value: \''.esc_attr($tag->slug).'\', label: \''.esc_html($tag->name).'\'}';
                $currentTag = new \stdClass;
                $currentTag->id = $tag->term_id;
                $currentTag->value = esc_attr($tag->slug);
                $currentTag->label = esc_html($tag->name);
                $variables['availableTags'][] = $currentTag;
            }
            if (!isset($TagsFiles)) {
                $TagsFiles = array();
            }
            $variables['allTagsFiles'] = '["' . implode('","', $TagsFiles) . '"]';
            $variables['TagsFiles'] = $TagsFiles;
        }

        $variables['baseUrl'] = $app->getBaseUrl();
        $variables['ajaxUrl'] = wpfd_sanitize_ajax_url($app->getAjaxUrl());
        $lastRebuildTime = get_option('wpfd_icon_rebuild_time', false);
        if (false === $lastRebuildTime) {
            // Icon CSS was never build, build it
            $lastRebuildTime = WpfdHelperFile::renderCss();
        }

        $iconSet = (isset($variables['config']['icon_set'])) ? $variables['config']['icon_set'] : 'default';
        if ($iconSet !== 'default' && in_array($iconSet, array('png', 'svg'))) {
            $path = WpfdHelperFile::getCustomIconPath($iconSet);
            $cssPath = $path . 'styles-' . $lastRebuildTime . '.css';
            if (file_exists($cssPath)) {
                $cssUrl = wpfd_abs_path_to_url($cssPath);
            } else {
                // Use default css pre-builed
                $cssUrl = WPFD_PLUGIN_URL . 'app/site/assets/icons/' . $iconSet . '/icon-styles.css';
            }
            // Include file
            wp_enqueue_style(
                'wpfd-style-icon-set-' . $iconSet,
                $cssUrl,
                array('wpfd-search_filter'),
                WPFD_VERSION
            );
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Don't escape a template
        return wpfd_get_template_html('tpl-search-form.php', $variables);
    }
}

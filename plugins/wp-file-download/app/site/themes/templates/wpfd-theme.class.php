<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

//-- No direct access
defined('ABSPATH') || die();
use Joomunited\WPFramework\v1_0_5\Application;

/**
 * Class WpfdTheme
 */
class WpfdTheme
{
    /**
     * Theme name
     *
     * @var string
     */
    public $name = 'default';

    /**
     * Hide empty file info
     *
     * @var booleam
     */
    public static $hideEmpty = true;

    /**
     * Theme param prefix
     *
     * @var string
     */
    public static $prefix = '';

    /**
     * Category theme config
     *
     * @var array
     */
    public $params;

    /**
     * Ajax url
     *
     * @var string
     */
    public $ajaxUrl = '';

    /**
     * Plugin path
     *
     * @var string
     */
    public $path = '';

    /**
     * Config
     *
     * @var array
     */

    public $config = array();

    /**
     * Options
     *
     * @var array
     */
    public $options;

    /**
     * Static theme name
     *
     * @var string
     */
    public static $themeName;

    /**
     * WpfdThemeDefault constructor.
     */
    public function __construct()
    {
        if (!class_exists('WpfdBase')) {
            $application = Application::getInstance('Wpfd');
            $path_wpfdbase = $application->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
            $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
            require_once $path_wpfdbase;
        }
    }

    /**
     * Get theme name
     *
     * @return string
     */
    public function getThemeName()
    {
        return $this->name;
    }

    /**
     * Set hideEmpty
     *
     * @param boolean $value Hide empty file info or not
     *
     * @return void
     */
    public function hideEmpty($value)
    {
        self::$hideEmpty = $value;
    }

    /**
     * Set theme name
     *
     * @param string $name Theme name
     *
     * @return void
     */
    public function setThemeName($name)
    {
        self::$themeName = $name;
        self::$prefix    = ($name !== 'default') ? $name . '_' : '';
    }

    /**
     * Set ajax url
     *
     * @param string $url Ajaxurl
     *
     * @return void
     */
    public function setAjaxUrl($url)
    {
        $this->ajaxUrl = $url;
    }

    /**
     * Set path
     *
     * @param string $path Plugin path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Set config
     *
     * @param array $config Configs
     *
     * @return void
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Get tpl path for include
     *
     * @return string
     */
    public function getTplPath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tpl-default.php';
    }

    /**
     * Show category
     *
     * @param array   $options Options
     * @param boolean $search  Search flag
     *
     * @return mixed|string
     */
    public function showCategory($options, $search = false)
    {
        if (empty($options['files']) && empty($options['categories'])) {
            if (is_admin()) {
                return __('There are no files in this category', 'wpfd');
            } else {
                return '';
            }
        }

        $this->options   = $options;
        self::$themeName = $this->getThemeName();

        $content           = '';
        $theme             = $this;
        $files             = $this->options['files'];
        $category          = $this->options['category'];
        $categories        = $this->options['categories'];
        $params            = $this->options['params'];
        $config            = $this->config;
        $padding           = self::getPadding($params);
        $name              = $this->getThemeName();
        $showsubcategories = (int) WpfdBase::loadValue($params, self::$prefix . 'showsubcategories', 1) === 1 ? true : false;
        $showfoldertree    = (int) WpfdBase::loadValue($params, self::$prefix . 'showfoldertree', 0) === 1 ? true : false;

        $catId = $category->term_id;
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
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $catId);

        if ((int) $categoryFrom === (int) $catId) {
            $categoryFrom = false;
        } elseif (in_array($categoryFrom, wpfd_get_support_cloud())) {
            // Not allow category from cloud can use download all/selected feature
            $config['download_category'] = 0;
            $config['download_selected'] = 0;
        }

        $this->params       = $params;
        $this->category     = $category;
        $this->categoryFrom = $categoryFrom;
        $this->categories   = $categories;
        $this->latest       = false;

        $this->ordering = $this->options['ordering'];
        $this->orderingDirection = $this->options['orderingDirection'];

        if (isset($this->options['latest'])) {
            $this->latest = $this->options['latest'];
        }

        if (!empty($files) || $showsubcategories || $showfoldertree) {
            if (!empty($category) || $search) {
                if (!empty($files) || !empty($categories)) {
                    // Load css and scripts when have something for showing
                    $this->loadAssets();
                    $this->loadLightboxAssets();
                    $this->loadHooks();
                    ob_start();

                    include $this->getTplPath();
                    $content = ob_get_contents();
                    ob_end_clean();
                    // Fix conflict with wpautop in VC
                    $content = str_replace(array("\n", "\r"), '', $content);
                }
            }
        }
        return $content;
    }

    /**
     * Load theme styles and scripts
     *
     * @return void
     */
    public function loadAssets()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'handlebars',
            plugins_url('app/site/assets/js/handlebars-v4.1.0.js', WPFD_PLUGIN_FILE),
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
            'wpfd-material-design',
            plugins_url('app/site/assets/css/material-design-iconic-font.min.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_script(
            'wpfd-foldertree',
            plugins_url('app/site/assets/js/jaofiletree.js', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_style(
            'wpfd-foldertree',
            plugins_url('app/site/assets/css/jaofiletree.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        $path_foobar = $this->path . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'foobar';
        wp_enqueue_script('wpfd-helper', plugins_url('assets/js/helper.js', $path_foobar));
        wp_localize_script('wpfd-helper', 'wpfdHelper', array(
            'fileMeasure' => WpfdHelperFile::getSupportFileMeasure()
        ));

        if (WpfdBase::checkExistTheme($this->name)) {
            $url = plugin_dir_url($this->path . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-' . $this->name . DIRECTORY_SEPARATOR . 'foobar');
        } else {
            $url  = wpfd_abs_path_to_url(realpath(dirname(wpfd_locate_theme($this->name, 'theme.php'))) . DIRECTORY_SEPARATOR);
        }

        wp_enqueue_style('wpfd-theme-' . $this->name, $url . 'css/style.css', array(), WPFD_VERSION);
        $bg_download    = WpfdBase::loadValue($this->params, self::$prefix . 'bgdownloadlink', '');
        $color_download = WpfdBase::loadValue($this->params, self::$prefix . 'colordownloadlink', '');
        $style          = '';
        if ($bg_download !== '') {
            $style .= 'background-color:' . esc_html($bg_download) . ';border-color: ' . esc_html($bg_download) . ';';
        }
        if ($color_download !== '') {
            $style .= 'color:' . esc_html($color_download) . ';';
        }
        $css = '.wpfd-content .' .$this->name.'-download-category, .wpfd-content .'.$this->name.'-download-selected {'.$style.'}';
        wp_add_inline_style('wpfd-theme-' . $this->name, $css);
        wp_enqueue_script('wpfd-theme-' . $this->name, $url . 'js/script.js', array(), WPFD_VERSION);
        wp_localize_script('wpfd-theme-' . $this->name, 'wpfdparams', array(
            'wpfdajaxurl'          => $this->ajaxUrl,
            'ga_download_tracking' => $this->config['ga_download_tracking'],
            'ajaxadminurl'         => admin_url('admin-ajax.php') . '?juwpfisadmin=0',
            'translates'           => array(
                'download_selected' => esc_html__('Download selected', 'wpfd')
            )
        ));
    }

    /**
     * Load Lightbox style and scripts
     *
     * @return void
     */
    public function loadLightboxAssets()
    {
        if ($this->config['use_google_viewer'] === 'lightbox') {
            wpfd_enqueue_assets();
        }
    }

    /**
     * Load template hooks
     *
     * @return void
     */
    public function loadHooks()
    {
        $name              = self::$themeName;
        $showcategorytitle = (int) WpfdBase::loadValue($this->params, self::$prefix . 'showcategorytitle', 1) === 1 ? true : false;
        $showsubcategories = (int) WpfdBase::loadValue($this->params, self::$prefix . 'showsubcategories', 1) === 1 ? true : false;
        $globalConfig      = get_option('_wpfd_global_config');

        /**
         * Action fire before templates hooked
         *
         * @hookname wpfd_{$themeName}_before_template_hooks
         */
        do_action('wpfd_' . $name . '_before_template_hooks');

        /* Theme Content Output  */
        add_action('wpfd_' . $name . '_before_theme_content', array(__CLASS__, 'outputContentWrapper'), 10, 1);
        add_action('wpfd_' . $name . '_before_theme_content', array(__CLASS__, 'outputContentHeader'), 20, 1);

        // Before files loop handlebars
        add_action('wpfd_' . $name . '_before_files_loop_handlebars', array(__CLASS__, 'outputCategoriesWrapper'), 10, 2);
        add_action('wpfd_' . $name . '_before_files_loop_handlebars', array(__CLASS__, 'showCategoryTitleHandlebars'), 20, 2);
        if ($showsubcategories && !$this->latest) {
            add_action('wpfd_' . $name . '_before_files_loop_handlebars', array(__CLASS__, 'showCategoriesHandlebars'), 30, 2);
        }
        add_action('wpfd_' . $name . '_before_files_loop_handlebars', array(__CLASS__, 'outputCategoriesWrapperEnd'), 90, 2);
        // Before files loop
        add_action('wpfd_' . $name . '_before_files_loop', array(__CLASS__, 'outputCategoriesWrapper'), 10, 2);
        if ($showcategorytitle && !$this->latest) {
            add_action('wpfd_' . $name . '_before_files_loop', array(__CLASS__, 'showCategoryTitle'), 20, 2);
        }
        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($this->categories) && count($this->categories) && $showsubcategories && !$this->latest) {
            add_action('wpfd_' . $name . '_before_files_loop', array(__CLASS__, 'showCategories'), 30, 2);
        }
        add_action('wpfd_' . $name . '_before_files_loop', array(__CLASS__, 'outputCategoriesWrapperEnd'), 90, 2);

        /* Folder Tree */
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showfoldertree', 0) === 1 && !$this->latest) {
            add_action('wpfd_' . $name . '_folder_tree', array(__CLASS__, 'showTree'), 10, 2);
        }
        /* File Block */
        // File content
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showtitle', 1) === 1) {
            add_action('wpfd_' . $name . '_file_content', array(__CLASS__, 'showTitle'), 20, 3);
            add_action('wpfd_' . $name . '_file_content_handlebars', array(__CLASS__, 'showTitleHandlebars'), 20, 2);
        }

        add_action('wpfd_' . $name . '_file_content_handlebars', array(__CLASS__, 'showIconHandlebars'), 10, 2);
        add_action('wpfd_' . $name . '_file_content', array(__CLASS__, 'showIcon'), 10, 3);

        // File info
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdescription', 1) === 1) {
            add_action('wpfd_' . $name . '_file_info', array(__CLASS__, 'showDescription'), 10, 3);
            add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showDescriptionHandlebars'), 10, 2);
        }

        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showversion', 1) === 1) {
            add_action('wpfd_' . $name . '_file_info', array(__CLASS__, 'showVersion'), 20, 3);
            add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showVersionHandlebars'), 20, 2);
        }

        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showsize', 1) === 1) {
            add_action('wpfd_' . $name . '_file_info', array(__CLASS__, 'showSize'), 30, 3);
            add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showSizeHandlebars'), 30, 2);
        }

        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showhits', 1) === 1) {
            add_action('wpfd_' . $name . '_file_info', array(__CLASS__, 'showHits'), 40, 3);
            add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showHitsHandlebars'), 40, 2);
        }

        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdateadd', 1) === 1) {
            add_action('wpfd_' . $name . '_file_info', array(__CLASS__, 'showCreated'), 50, 3);
            add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showCreatedHandlebars'), 50, 2);
        }

        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdatemodified', 0) === 1) {
            add_action('wpfd_' . $name . '_file_info', array(__CLASS__, 'showModified'), 60, 3);
            add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showModifiedHandlebars'), 60, 2);
        }


        // File buttons
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdownload', 1) === 1 && wpfd_can_download_files()) {
            add_action('wpfd_' . $name . '_buttons', array(__CLASS__, 'showDownload'), 10, 3);
            add_action('wpfd_' . $name . '_buttons_handlebars', array(__CLASS__, 'showDownloadHandlebars'), 10, 2);
        }

        if ($this->config['use_google_viewer'] !== 'no' && wpfd_can_preview_files()) {
            add_action('wpfd_' . $name . '_buttons_handlebars', array(__CLASS__, 'showPreviewHandlebars'), 20, 2);
            add_action('wpfd_' . $name . '_buttons', array(__CLASS__, 'showPreview'), 20, 3);
        }
        /* End File Block */

        // End theme content
        add_action('wpfd_' . $name . '_after_theme_content', array(__CLASS__, 'outputContentWrapperEnd'), 10, 1);

        /**
         * Action fire after template hooked
         *
         * @hookname wpfd_{$themeName}_after_template_hooks
         */
        do_action('wpfd_' . $name . '_after_template_hooks');

        // Call custom hooks
        $this->loadCustomHooks();
    }

    /**
     * Load custom hooks and filters
     *
     * @return void
     */
    public function loadCustomHooks()
    {
    }

    /**
     * Print content wrapper
     *
     * @param object $theme This theme object
     *
     * @return void
     */
    public static function outputContentWrapper($theme)
    {
        $name   = self::$themeName;
        $output = '';
        $html   = sprintf(
            '<div class="wpfd-content wpfd-content-' . self::$themeName . ' wpfd-content-multi" data-category="%s">',
            (string) esc_attr($theme->category->term_id)
        );
        /**
         * Filter to change content wrapper output
         *
         * @param string Content wrapper
         * @param object Current theme object
         *
         * @hookname wpfd_{$themeName}_content_wrapper
         *
         * @return string
         */
        $output .= apply_filters('wpfd_' . $name . '_content_wrapper', $html, $theme);

        // Print hidden input
        $sc = esc_attr($theme->category->term_id);
        $html = sprintf(
            '<input type="hidden" id="current_category_' . $sc . '" value="%s" />
                    <input type="hidden" id="current_category_slug_' . $sc . '" value="%s" />
                    <input type="hidden" id="current_ordering_' . $sc . '" value="%s" />
                    <input type="hidden" id="current_ordering_direction_' . $sc . '" value="%s" />',
            esc_attr($theme->category->term_id),
            esc_attr($theme->category->slug),
            esc_attr($theme->ordering),
            esc_attr($theme->orderingDirection)
        );

        /**
         * Filters to print hidden input below content wrapper
         *
         * @param string Input html
         * @param object Current theme object
         *
         * @hookname wpfd_{$themeName}_content_wrapper_input
         *
         * @return string
         */
        $output .= apply_filters('wpfd_' . $name . '_content_wrapper_input', $html, $theme);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Print content header
     *
     * @param object $theme This theme object
     *
     * @return void
     */
    public static function outputContentHeader($theme)
    {
        $output           = '';
        $showDownloadAll  = (int) $theme->config['download_category'] === 1 ? true : false;
        $display_download = (empty($theme->options['files']) || $showDownloadAll === false) ? 'display-download-category' : '';
        $globalConfig     = get_option('_wpfd_global_config');

        $showCategoryTitle = (int) WpfdBase::loadValue($theme->params, self::$prefix . 'showcategorytitle', 1);
        $showBreadcrumb = (int) WpfdBase::loadValue($theme->params, self::$prefix . 'showbreadcrumb', 1);

        if ($showBreadcrumb === 1 && !$theme->latest) {
            if ($theme->config['download_category'] && !$theme->categoryFrom && wpfd_can_download_files()) {
                $output .= sprintf(
                    '<a data-catid="" class="' . self::$themeName . '-download-category %s" href="%s">%s
                                <i class="zmdi zmdi-check-all wpfd-download-category"></i>
                            </a>',
                    esc_attr($display_download),
                    esc_url($theme->category->linkdownload_cat),
                    esc_html__('Download all ', 'wpfd')
                );
            }
            $output .= sprintf(
                '<ul class="breadcrumbs wpfd-breadcrumbs-' . self::$themeName . ' head-category-' . self::$themeName . '">
                            <li class="active">%s</li>
                        </ul>',
                esc_html($theme->category->name)
            );
        } elseif ($showDownloadAll && !$theme->categoryFrom && !$theme->latest) {
            if ($showDownloadAll && !$theme->categoryFrom && wpfd_can_download_files()) {
                $output .= sprintf(
                    '<a data-catid=""
                       class="' . self::$themeName . '-download-category %s"
                       href="%s">%s
                        <i class="zmdi zmdi-check-all wpfd-download-category"></i>
                    </a>',
                    $display_download,
                    esc_url($theme->category->linkdownload_cat),
                    esc_html__('Download all ', 'wpfd')
                );
            }
            if ($showCategoryTitle === 1 || $showBreadcrumb === 1) {
                $output .= '<ul class="head-category head-category-' . self::$themeName . '"><li>&nbsp;</li></ul>';
            }
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Print file description handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showDescriptionHandlebars($config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showdescription', 1) === 1) {
            $name     = self::$themeName;

            $template = array(
                'html' => '{{#if description}}<div class="file-desc">%value$s</div>{{/if}}',
                'args' => array(
                    'value' => '{{{description}}}'
                )
            );

            /**
             * Filter to change html and arguments of description handlebars
             *
             * @param array Template array
             * @param array Main config
             * @param array Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_description_handlebars_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_description_handlebars_args', $template, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print file description
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showDescription($file, $config, $params)
    {
        if (isset($file->description)) {
            if (trim($file->description === '') && static::$hideEmpty === true) {
                echo '';
            } else {
                $name     = self::$themeName;
                $template = array(
                    'html' => '<div class="file-desc">%value$s</div>',
                    'args' => array(
                        'value' => wpfd_esc_desc($file->description)
                    )
                );
                /**
                 * Filter to change html and arguments of description
                 *
                 * @param array  Template array
                 * @param object Current file object
                 * @param array  Main config
                 * @param array  Current category config
                 *
                 * @hookname wpfd_{$themeName}_file_info_description_args
                 *
                 * @return array
                 */
                $args = apply_filters('wpfd_' . $name . '_file_info_description_args', $template, $file, $config, $params);
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
                echo self::render($args['html'], $args['args']);
            }
        }
    }

    /**
     * Print version handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showVersionHandlebars($config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showversion', 1) === 1) {
            $name = self::$themeName;

            $template = array(
                'html' => '{{#if versionNumber}}<div class="file-version"><span>%text$s</span> %value$s</div>{{/if}}',
                'args' => array(
                    'text'  => esc_html__('Version:', 'wpfd'),
                    'value' => '{{versionNumber}}'
                )
            );
            /**
             * Filter to change html and arguments of version handlebars
             *
             * @param array Template array
             * @param array Main config
             * @param array Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_version_handlebars_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_version_handlebars_args', $template, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print version
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showVersion($file, $config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showversion', 1) === 1) {
            if (trim($file->versionNumber) === '' && static::$hideEmpty === true) {
                echo '';
            } else {
                $name     = self::$themeName;
                $template = array(
                    'html' => '<div class="file-version"><span>%text$s</span> %value$s</div>',
                    'args' => array(
                        'text'  => esc_html__('Version:', 'wpfd'),
                        'value' => esc_html($file->versionNumber)
                    )
                );
                /**
                 * Filter to change html and arguments of version
                 *
                 * @param array  Template array
                 * @param object Current file object
                 * @param array  Main config
                 * @param array  Current category config
                 *
                 * @hookname wpfd_{$themeName}_file_info_version_args
                 *
                 * @return array
                 */
                $args = apply_filters('wpfd_' . $name . '_file_info_version_args', $template, $file, $config, $params);
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
                echo self::render($args['html'], $args['args']);
            }
        }
    }

    /**
     * Print size handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showSizeHandlebars($config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showsize', 1) === 1) {
            $name     = self::$themeName;
            $template = array(
                'html' => '<div class="file-size"><span>%text$s</span> %value$s</div>',
                'args' => array(
                    'text'  => esc_html__('Size:', 'wpfd'),
                    'value' => '{{bytesToSize size}}'
                )
            );
            /**
             * Filter to change html and arguments of size handlebars
             *
             * @param array Template array
             * @param array Main config
             * @param array Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_size_handlebars_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_size_handlebars_args', $template, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print size
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showSize($file, $config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showsize', 1) === 1) {
            $name     = self::$themeName;
            $fileSize = (strtolower($file->size) === 'n/a' || $file->size <= 0) ? 'N/A' : WpfdHelperFile::bytesToSize($file->size);
            $template = array(
                'html' => '<div class="file-size"><span>%text$s</span> %value$s</div>',
                'args' => array(
                    'text'  => esc_html__('Size:', 'wpfd'),
                    'value' => esc_html($fileSize)
                )
            );
            /**
             * Filter to change html and arguments of size
             *
             * @param array  Template array
             * @param object Current file object
             * @param array  Main config
             * @param array  Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_size_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_size_args', $template, $file, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print hits handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showHitsHandlebars($config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showhits', 1) === 1) {
            $name     = self::$themeName;
            $template = array(
                'html' => '<div class="file-hits"><span>%text$s</span> %value$s</div>',
                'args' => array(
                    'text'  => esc_html__('Hits:', 'wpfd'),
                    'value' => '{{hits}}'
                )
            );
            /**
             * Filter to change html and arguments of hits handlebars
             *
             * @param array Template array
             * @param array Main config
             * @param array Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_hits_handlebars_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_hits_handlebars_args', $template, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print hits
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showHits($file, $config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showhits', 1) === 1) {
            $name     = self::$themeName;
            $template = array(
                'html' => '<div class="file-hits"><span>%text$s</span> %value$s</div>',
                'args' => array(
                    'text'  => esc_html__('Hits:', 'wpfd'),
                    'value' => esc_html($file->hits)
                )
            );
            /**
             * Filter to change html and arguments of hits
             *
             * @param array  Template array
             * @param object Current file object
             * @param array  Main config
             * @param array  Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_hits_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_hits_args', $template, $file, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print created date handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showCreatedHandlebars($config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showdateadd', 0) === 1) {
            $name     = self::$themeName;
            $template = array(
                'html' => '<div class="file-dated"><span>%text$s</span> %value$s</div>',
                'args' => array(
                    'text'  => esc_html__('Date added:', 'wpfd'),
                    'value' => '{{created}}'
                )
            );
            /**
             * Filter to change html and arguments of created handlebars
             *
             * @param array Template array
             * @param array Main config
             * @param array Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_created_handlebars_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_created_handlebars_args', $template, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print created date
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showCreated($file, $config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showdateadd', 0) === 1) {
            $name     = self::$themeName;
            $template = array(
                'html' => '<div class="file-dated"><span>%text$s</span> %value$s</div>',
                'args' => array(
                    'text'  => esc_html__('Date added:', 'wpfd'),
                    'value' => esc_html($file->created)
                )
            );
            /**
             * Filter to change html and arguments of created
             *
             * @param array  Template array
             * @param object Current file object
             * @param array  Main config
             * @param array  Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_created_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_created_args', $template, $file, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print modified date handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showModifiedHandlebars($config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showdatemodified', 0) === 1) {
            $name     = self::$themeName;
            $template = array(
                'html' => '<div class="file-dated"><span>%text$s</span> %value$s</div>',
                'args' => array(
                    'text'  => esc_html__('Date modified:', 'wpfd'),
                    'value' => '{{modified}}'
                )
            );
            /**
             * Filter to change html and arguments of modified handlebars
             *
             * @param array Template array
             * @param array Main config
             * @param array Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_modified_handlebars_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_modified_handlebars_args', $template, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print modified date
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showModified($file, $config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showdatemodified', 0) === 1) {
            $name     = self::$themeName;
            $template = array(
                'html' => '<div class="file-dated"><span>%text$s</span> %value$s</div>',
                'args' => array(
                    'text'  => esc_html__('Date modified:', 'wpfd'),
                    'value' => esc_html($file->modified)
                )
            );
            /**
             * Filter to change html and arguments of modified
             *
             * @param array  Template array
             * @param object Current file object
             * @param array  Main config
             * @param array  Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_modified_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_modified_args', $template, $file, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print icon handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showIconHandlebars($config, $params)
    {
        $html = '';
        $name = self::$themeName;
        $iconSet = isset($config['icon_set']) && $config['icon_set'] !== 'default' ? ' wpfd-icon-set-' . $config['icon_set'] : '';
        if ($config['custom_icon']) {
            $html = '{{#if file_custom_icon}}
                    <div class="icon-custom"><img src="{{file_custom_icon}}"></div>
                    {{else}}
                    <div class="ext ext-{{ext}}' . $iconSet . '"><span class="txt">{{ext}}</span></div>
                    {{/if}}';
        } else {
            $html = '<div class="ext ext-{{ext}}' . $iconSet . '"><span class="txt">{{ext}}</span></div>';
        }
        /**
         * Filter to change icon html for handlebars template
         *
         * @param string Output html for handlebars template
         * @param array  Main config
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_icon_hanlebars
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_file_info_icon_hanlebars', $html, $config, $params);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Print icon
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showIcon($file, $config, $params)
    {
        $html = '';
        $name = self::$themeName;
        if ($config['custom_icon'] && isset($file->file_custom_icon) && $file->file_custom_icon !== '') {
            $html = sprintf(
                '<div class="icon-custom"><img src="%s"></div>',
                esc_url($file->file_custom_icon)
            );
        } else {
            $html = sprintf(
                '<div class="ext ext-%s%s"><span class="txt">%s</div>',
                esc_attr(strtolower($file->ext)),
                (isset($config['icon_set']) && $config['icon_set'] !== 'default') ? ' wpfd-icon-set-' . esc_attr($config['icon_set']) : '',
                esc_html($file->ext)
            );
        }

        /**
         * Filter to change icon html
         *
         * @param string Output html for handlebars template
         * @param object Current file object
         * @param array  Main config
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_icon_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_file_info_icon_html', $html, $file, $config, $params);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Print title handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showTitleHandlebars($config, $params)
    {
        $selectFileInput = '';
        if ((int) $config['download_selected'] === 1 && wpfd_can_download_files()) {
            $selectFileInput = '<label class="wpfd_checkbox"><input class="cbox_file_download" type="checkbox" data-id="{{ID}}" /><span></span></label>';
        }
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showtitle', 1) === 1) {
            $name     = self::$themeName;
            $template = array(
                'html' => '<h3>' . $selectFileInput . '<a href="%url$s" %data$s class="wpfd_downloadlink" title="%title$s">%text$s</a></h3>',
                'args' => array(
                    'url'   => '{{linkdownload}}',
                    'data'  => apply_filters('wpfd_download_data_attributes_handlebars', ''),
                    'title' => '{{post_title}}',
                    'text'  => '{{{crop_title}}}'
                )
            );
            /**
             * Filter to change html and arguments of title handlebars
             *
             * @param array Template array
             * @param array Main config
             * @param array Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_title_handlebars_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_title_handlebars_args', $template, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print title
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showTitle($file, $config, $params)
    {
        $selectFileInput = '';
        if ((int) $config['download_selected'] === 1 && wpfd_can_download_files()) {
            $selectFileInput = '<label class="wpfd_checkbox"><input class="cbox_file_download" type="checkbox" data-id="' . $file->ID . '" /><span></span></label>';
        }
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showtitle', 1) === 1) {
            $name     = self::$themeName;
            $attributes = apply_filters('wpfd_download_data_attributes', array(), $file);
            $data = implode(' ', $attributes);
            $template = array(
                'html' => '<h3>' . $selectFileInput . '<a href="%url$s" %data$s class="wpfd_downloadlink" title="%title$s">%text$s</a></h3>',
                'args' => array(
                    'url'   => esc_url($file->linkdownload),
                    'data'  => $data,
                    'title' => esc_html($file->post_title),
                    'text'  => esc_html($file->crop_title)
                )
            );
            /**
             * Filter to change html and arguments of title
             *
             * @param array  Template array
             * @param object Current file object
             * @param array  Main config
             * @param array  Current category config
             *
             * @hookname wpfd_{$themeName}_file_info_title_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_info_title_args', $template, $file, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print download button handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showDownloadHandlebars($config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showdownload', 1) === 1) {
            $name           = self::$themeName;
            $bg_download    = WpfdBase::loadValue($params, self::$prefix . 'bgdownloadlink', '');
            $color_download = WpfdBase::loadValue($params, self::$prefix . 'colordownloadlink', '');
            $style          = '';
            if ($bg_download !== '') {
                $style .= 'background-color:' . esc_html($bg_download) . ';';
            }
            if ($color_download !== '') {
                $style .= 'color:' . esc_html($color_download) . ';';
            }
            $template = array(
                'html' => '<a class="%class$s" %data$s href="%url$s" style="%style$s">%text$s%icon$s</a>',
                'args' => array(
                    'class' => 'downloadlink wpfd_downloadlink',
                    'data'  => apply_filters('wpfd_download_data_attributes_handlebars', ''),
                    'url'   => '{{linkdownload}}',
                    'style' => $style,
                    'text'  => apply_filters('wpfd_download_text_handlebars', esc_html__('Download', 'wpfd')),
                    'icon'  => apply_filters('wpfd_download_icon_handlebars', '<i class="zmdi zmdi-cloud-download wpfd-download"></i>')
                )
            );
            /**
             * Filter to change html and arguments of download button handlebars
             *
             * @param array Template array
             * @param array Main config
             * @param array Current category config
             *
             * @hookname wpfd_{$themeName}_file_download_button_handlebars_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_download_button_handlebars_args', $template, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print download button
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showDownload($file, $config, $params)
    {
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'showdownload', 1) === 1) {
            $name           = self::$themeName;
            $bg_download    = WpfdBase::loadValue($params, self::$prefix . 'bgdownloadlink', '');
            $color_download = WpfdBase::loadValue($params, self::$prefix . 'colordownloadlink', '');
            $style          = '';
            if ($bg_download !== '') {
                $style .= 'background-color:' . esc_html($bg_download) . ';';
            }
            if ($color_download !== '') {
                $style .= 'color:' . esc_html($color_download) . ';';
            }
            $attributes = apply_filters('wpfd_download_data_attributes', array(), $file);
            $data = implode(' ', $attributes);
            $template = array(
                'html' => '<a class="%class$s" %data$s href="%url$s" style="%style$s">%text$s%icon$s</a>',
                'args' => array(
                    'class' => 'downloadlink wpfd_downloadlink',
                    'data' => $data,
                    'url'   => esc_url($file->linkdownload),
                    'style' => $style,
                    'text'  => apply_filters('wpfd_download_text', esc_html__('Download', 'wpfd'), $file),
                    'icon'  => apply_filters('wpfd_download_icon', '<i class="zmdi zmdi-cloud-download wpfd-download"></i>', $file)
                )
            );
            /**
             * Filter to change html and arguments of download button
             *
             * @param array  Template array
             * @param object Current file object
             * @param array  Main config
             * @param array  Current category config
             *
             * @hookname wpfd_{$themeName}_file_download_button_args
             *
             * @return array
             */
            $args = apply_filters('wpfd_' . $name . '_file_download_button_args', $template, $file, $config, $params);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
            echo self::render($args['html'], $args['args']);
        }
    }

    /**
     * Print preview button handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showPreviewHandlebars($config, $params)
    {
        $output      = '';
        $name        = self::$themeName;
        $viewer_attr = 'openlink wpfdlightbox wpfd_previewlink';
        $target      = '';
        if ((string) $config['use_google_viewer'] === 'tab') {
            $viewer_attr = 'openlink wpfd_previewlink';
            $target      = '_blank';
        }
        $viewer_attr = apply_filters('wpfd_preview_classes_handlebars', $viewer_attr);
        $output   .= '{{#if openpdflink}}';
        $template = array(
            'html' => '<a class="%class$s" href="%url$s" target="%target$s">%text$s
                            %icon$s
                        </a>',
            'args' => array(
                'class'  => esc_html($viewer_attr),
                'url'    => '{{openpdflink}}',
                'target' => esc_html($target),
                'text'   => apply_filters('wpfd_preview_text_handlebars', esc_html__('Preview', 'wpfd')),
                'icon'   => '<i class="zmdi zmdi-filter-center-focus wpfd-preview"></i>'
            )
        );
        /**
         * Filter to change html and arguments of open pdf button handlebars
         *
         * @param array Template array
         * @param array Main config
         * @param array Current category config
         *
         * @hookname wpfd_{$themeName}_file_open_pdf_button_handlebars_args
         *
         * @return array
         */
        $args = apply_filters('wpfd_' . $name . '_file_open_pdf_button_handlebars_args', $template, $config, $params);

        $output   .= self::render($args['html'], $args['args']);
        $output   .= '{{else}}';
        $template = array(
            'html' => '{{#if viewerlink}}<a
                            href="%url$s"
                            class="%class$s"
                            target="%target$s"
                            data-id="{{ID}}"
                            data-catid="{{catid}}"
                            data-file-type="{{ext}}">%text$s%icon$s
                        </a>{{/if}}',
            'args' => array(
                'url'    => '{{viewerlink}}',
                'class'  => esc_attr($viewer_attr),
                'target' => esc_attr($target),
                'text'   => apply_filters('wpfd_preview_text_handlebars', esc_html__('Preview', 'wpfd')),
                'icon'   => '<i class="zmdi zmdi-filter-center-focus wpfd-preview"></i>'
            )
        );
        /**
         * Filter to change html and arguments of preview button handlebars
         *
         * @param array Template array
         * @param array Main config
         * @param array Current category config
         *
         * @hookname wpfd_{$themeName}_file_preview_button_handlebars_args
         *
         * @return array
         */
        $args   = apply_filters('wpfd_' . $name . '_file_preview_button_handlebars_args', $template, $config, $params);
        $output .= self::render($args['html'], $args['args']);
        $output .= '{{/if}}';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Print preview button
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showPreview($file, $config, $params)
    {
        $output = '';
        $name   = self::$themeName;

        if (!isset($file->viewerlink) && !isset($file->openpdflink)) {
            return;
        }
        $viewer_attr = 'openlink wpfdlightbox wpfd_previewlink';
        $target      = '';
        if (isset($file->viewer_type) && $file->viewer_type === 'tab') {
            $viewer_attr = 'openlink wpfd_previewlink';
            $target      = '_blank';
        }
        $viewer_attr = apply_filters('wpfd_preview_classes', $viewer_attr, $file);
        if (isset($file->openpdflink)) {
            $template = array(
                'html' => '<a class="%class$s" href="%url$s" target="%target$s">%text$s
                            %icon$s
                        </a>',
                'args' => array(
                    'class'  => esc_html($viewer_attr),
                    'url'    => esc_url($file->openpdflink),
                    'target' => esc_html($target),
                    'text'   => apply_filters('wpfd_preview_text', esc_html__('Preview', 'wpfd'), $file),
                    'icon'   => '<i class="zmdi zmdi-filter-center-focus wpfd-preview"></i>'
                )
            );
            /**
             * Filter to change html and arguments of open pdf button
             *
             * @param array  Template array
             * @param object Current file object
             * @param array  Main config
             * @param array  Current category config
             *
             * @hookname wpfd_{$themeName}_file_open_pdf_button_args
             *
             * @return array
             */
            $args   = apply_filters('wpfd_' . $name . '_file_open_pdf_button_args', $template, $file, $config, $params);
            $output .= self::render($args['html'], $args['args']);
        } else {
            $template = array(
                'html' => '<a
                            href="%url$s"
                            class="%class$s"
                            target="%target$s"
                            data-id="%id$s"
                            data-catid="%catid$s"
                            data-file-type="%ext$s">%text$s%icon$s
                        </a>',
                'args' => array(
                    'url'    => esc_url(isset($file->viewerlink) ? $file->viewerlink : '#'),
                    'class'  => esc_attr($viewer_attr),
                    'target' => esc_attr($target),
                    'id'     => esc_attr($file->ID),
                    'catid'  => esc_attr($file->catid),
                    'ext'    => esc_attr(strtolower($file->ext)),
                    'text'   => apply_filters('wpfd_preview_text', esc_html__('Preview', 'wpfd'), $file),
                    'icon'   => '<i class="zmdi zmdi-filter-center-focus wpfd-preview"></i>'
                )
            );
            /**
             * Filter to change html and arguments of preview button
             *
             * @param array  Template array
             * @param object Current file object
             * @param array  Main config
             * @param array  Current category config
             *
             * @hookname wpfd_{$themeName}_file_preview_button_args
             *
             * @return array
             */
            $args   = apply_filters('wpfd_' . $name . '_file_preview_button_args', $template, $file, $config, $params);
            $output .= self::render($args['html'], $args['args']);
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Print content wrapper closing tag
     *
     * @param object $theme Current theme object
     *
     * @return void
     */
    public static function outputContentWrapperEnd($theme)
    {
        echo '</div>';
    }

    /**
     * Print Categories wrapper opening tag
     *
     * @param object $theme  Current theme object
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function outputCategoriesWrapper($theme, $params)
    {
        if (!isset($params['show_categories']) || (isset($params['show_categories']) && (int) $params['show_categories'] === 1)) {
            echo '<div class="wpfd-categories">';
        }
    }

    /**
     * Print Categories wrapper closing tag
     *
     * @param object $theme  Current theme object
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function outputCategoriesWrapperEnd($theme, $params)
    {
        if (!isset($params['show_categories']) || (isset($params['show_categories']) && (int) $params['show_categories'] === 1)) {
            echo '</div>';
        }
    }

    /**
     * Print Category title handlebars
     *
     * @param object $theme  Current theme object
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showCategoryTitleHandlebars($theme, $params)
    {
        $name     = self::$themeName;
        $template = array(
            'html' => '<a class="catlink backcategory" href="#" data-idcat="{{parent}}">
                        %icon$s</i><span>%text$s</span></a>',
            'args' => array(
                'icon' => '<i class="zmdi zmdi-chevron-left">',
                'text' => esc_html__('Back', 'wpfd'),
            )
        );
        /**
         * Filter to change html and arguments of back button handlebars
         *
         * @param array  Template array
         * @param object Current theme object
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_back_button_handlebars
         *
         * @return array
         */
        $backButton     = apply_filters('wpfd_' . $name . '_back_button_handlebars', $template, $theme, $params);
        $backButtonHtml = self::render('{{#if parent}}' . $backButton['html'] . '{{/if}}', $backButton['args']);
        $showcategorytitle = ((int) WpfdBase::loadValue($params, self::$prefix . 'showcategorytitle', 1)) === 1 ? true : false;
        $template       = array(
            'html' => '%title$s%back$s',
            'args' => array(
                'title' => $showcategorytitle ? '<h2>{{name}}</h2>' : '',
                'back'  => $backButtonHtml
            )
        );
        /**
         * Filter to change html and arguments of category title handlebars
         *
         * @param array  Template array
         * @param object Current theme object
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_category_title_handlebars
         *
         * @return array
         */
        $args = apply_filters('wpfd_' . $name . '_category_title_handlebars', $template, $theme, $params);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo self::render('{{#if category}}{{#with category}}' . $args['html'] . '{{/with}}{{/if}}', $args['args']);
    }

    /**
     * Print Category title
     *
     * @param object $theme  Current theme object
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showCategoryTitle($theme, $params)
    {
        $name     = self::$themeName;
        $template = array(
            'html' => '<h2>%title$s</h2>',
            'args' => array(
                'title' => esc_html($theme->category->name)
            )
        );
        /**
         * Filter to change html and arguments of category title
         *
         * @param array  Template array
         * @param object Current theme object
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_category_title
         *
         * @return array
         */
        $args = apply_filters('wpfd_' . $name . '_category_title', $template, $theme, $params);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo self::render($args['html'], $args['args']);
    }

    /**
     * Print Categories handlebars
     *
     * @param object $theme  Current theme object
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showCategoriesHandlebars($theme, $params)
    {
        $output   = '';
        $name     = self::$themeName;
        $template = array(
            'html' => '<a class="wpfdcategory catlink" style="%style$s" href="#" data-idcat="%id$s" title="%title$s">
                                <span>%text$s</span>%icon$s
                            </a>',
            'args' => array(
                'style' => self::getPadding($theme->params),
                'id'    => '{{term_id}}',
                'title' => '{{name}}',
                'text'  => '{{name}}',
                'icon'  => '<i class="zmdi zmdi-folder wpfd-folder"></i>'
            )
        );
        /**
         * Filter to change html and arguments of categories item handlebars
         *
         * @param array  Template array
         * @param object Current theme object
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_category_item_handlebars
         *
         * @return array
         */
        $args   = apply_filters('wpfd_' . $name . '_category_item_handlebars', $template, $theme, $params);

        $style = 'margin : 0 ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginright', 10) . 'px ';
        $style .= '0 ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginleft', 10) . 'px;';

        $folderdefaultholder = '<div class="wpfdcategory_placeholder" style="' . $style . '"></div><div class="wpfdcategory_placeholder" style="' . $style . '"></div><div class="wpfdcategory_placeholder" style="' . $style . '"></div>';
        $output .= self::render('{{#if categories}}{{#each categories}}' . $args['html'] . '{{/each}}{{/if}}' . $folderdefaultholder, $args['args']);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Print Categories
     *
     * @param object $theme  Current theme object
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showCategories($theme, $params)
    {
        $output     = '';
        $name       = self::$themeName;
        $categories = $theme->categories;
        foreach ($categories as $category) {
            $template = array(
                'html' => '<a class="wpfdcategory catlink" style="%style$s" href="#"
                                   data-idcat="%id$s"
                                   title="%title$s">
                                    <span>%text$s</span>
                                    %icon$s
                                </a>',
                'args' => array(
                    'style' => self::getPadding($theme->params),
                    'id'    => esc_attr($category->term_id),
                    'title' => esc_html($category->name),
                    'text'  => esc_html($category->name),
                    'icon'  => '<i class="zmdi zmdi-folder wpfd-folder"></i>'
                )
            );

            /**
             * Filter to change html and arguments of categories item
             *
             * @param array  Template array
             * @param object Current category object
             * @param object Current theme object
             * @param array  Current category config
             *
             * @hookname wpfd_{$themeName}_category_item
             *
             * @return array
             */
            $args   = apply_filters('wpfd_' . $name . '_category_item', $template, $category, $theme, $params);
            $output .= self::render($args['html'], $args['args']);
        }
        $style = 'margin : 0 ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginright', 10) . 'px ';
        $style .= '0 ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginleft', 10) . 'px;';

        $folderdefaultholder = '<div class="wpfdcategory_placeholder" style="' . $style . '"></div><div class="wpfdcategory_placeholder" style="' . $style . '"></div><div class="wpfdcategory_placeholder" style="' . $style . '"></div>';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output . $folderdefaultholder;
    }

    /**
     * Print Left Tree
     *
     * @param object $theme  Current theme object
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showTree($theme, $params)
    {
        $name     = self::$themeName;
        $classes  = (int) WpfdBase::loadValue($params, self::$prefix . 'showsubcategories', 1) === 1 ? 'foldertree-hide' : '';
        $template = array(
            'html' => '<div class="wpfd-foldertree wpfd-foldertree-' . esc_attr(self::$themeName) . ' %class$s"></div>',
            'args' => array('class' => $classes)
        );
        /**
         * Filter to change html and arguments of category tree
         *
         * @param array  Template array
         * @param object Current theme object
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_category_tree
         *
         * @return array
         */
        $args = apply_filters('wpfd_' . $name . '_category_tree', $template, $theme, $params);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo self::render($args['html'], $args['args']);
    }

    /**
     * Get padding from category config
     *
     * @param array $params Current category config
     *
     * @return string
     */
    public static function getPadding($params)
    {
        $style = 'margin : ' . WpfdBase::loadValue($params, self::$prefix . 'margintop', 10) . 'px ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginright', 10) . 'px ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginbottom', 10) . 'px ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginleft', 10) . 'px;';

        return $style;
    }

    /**
     * Render html using $args
     *
     * @param string $str  Html with placeholder
     * @param array  $args Arguments
     *
     * @return string
     */
    public static function render($str, $args)
    {
        if (is_object($args)) {
            $args = get_object_vars($args);
        }
        $map     = array_flip(array_keys($args));
        $new_str = preg_replace_callback(
            '/(^|[^%])%([a-zA-Z0-9_-]+)\$/',
            function ($m) use ($map) {
                return $m[1] . '%' . ($map[$m[2]] + 1) . '$';
            },
            $str
        );

        return vsprintf($new_str, $args);
    }
}

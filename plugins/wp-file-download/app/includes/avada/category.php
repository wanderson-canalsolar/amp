<?php
use Joomunited\WPFramework\v1_0_5\Application;

if (fusion_is_element_enabled('wpfd_category_file')) {
    if (!class_exists('WpfdCategoryFile')) {

        /**
         * Class WpfdCategoryFile
         */
        class WpfdCategoryFile extends Fusion_Element
        {

            /**
             * An array of the shortcode arguments.
             *
             * @var array
             */
            protected $args;

            /**
             * WpfdCategoryFile construction
             */
            public function __construct()
            {
                parent::__construct();
                add_shortcode('wpfd_category_file', array($this, 'render'));
            }

            /**
             * WpfdAvadaCategoryShortcode
             *
             * @param string|mixed $categoryId Category id
             *
             * @throws Exception Fire when errors
             *
             * @return string|mixed
             */
            public function wpfdAvadaCategoryShortcode($categoryId)
            {
                $app                  = Application::getInstance('Wpfd');
                $cateId               = $categoryId;
                $path_helper          = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'WpfdHelperShortcodes.php';
                require_once $path_helper;
                $helper               = new WpfdHelperShortcodes();
                $atts                 = (isset($cateId)) ? array('id' => $cateId) : array('id' => '');
                $categoryShortcode    = $helper->categoryShortcode($atts);

                return $categoryShortcode;
            }

            /**
             * Render
             *
             * @param string|mixed $args Param contents
             *
             * @throws Exception Fire when errors
             *
             * @return string|mixed
             */
            public function render($args)
            {
                $selectedCategoryId = $args['wpfd_selected_category_id'];
                $extraClass         = $args['class'];
                $extraId            = $args['id'];
                $type               = 'stylesheet';
                $styles             = array();
                $styles[]           = WPFD_PLUGIN_URL . '/app/site/assets/css/front.css';
                $theme              = 'default';
                $result             = '';
                $html               = '';
                $defaultConfig      = array(
                    'defaultthemepercategory' => 'default',
                    'catparameters' => 1,
                    'icon_set' => 'default',
                );
                $config = get_option('_wpfd_global_config', $defaultConfig);

                if (intval($config['catparameters']) === 1) {
                    $category = get_term($selectedCategoryId, 'wpfd-category');
                    if (isset($category->description) && $category->description !== '') {
                        $description    = json_decode($category->description);
                        $theme          = isset($description->theme) ? $description->theme : 'default';
                    }
                } else {
                    $theme = isset($config['defaultthemepercategory']) ? $config['defaultthemepercategory'] : 'default';
                }

                $styles[] = wpfd_abs_path_to_url(wpfd_locate_theme($theme, 'css/style.css'));

                if (!class_exists('WpfdHelperFile')) {
                    require_once WPFD_PLUGIN_DIR_PATH . 'app/site/helpers/WpfdHelperFile.php';
                }

                // Regenerate icons
                $lastRebuildTime = get_option('wpfd_icon_rebuild_time', false);
                if (false === $lastRebuildTime) {
                    // Icon CSS was never build, build it
                    $lastRebuildTime = \WpfdHelperFile::renderCss();
                }

                $iconSet = (isset($config['icon_set'])) ? $config['icon_set'] : 'default';
                if ($iconSet !== 'default' && in_array($iconSet, array('png', 'svg'))) {
                    $path       = \WpfdHelperFile::getCustomIconPath($iconSet);
                    $cssPath    = $path . 'styles-' . $lastRebuildTime . '.css';
                    if (file_exists($cssPath)) {
                        $cssUrl = wpfd_abs_path_to_url($cssPath);
                    } else {
                        // Use default css
                        $cssUrl = WPFD_PLUGIN_URL . 'app/site/assets/icons/' . $iconSet . '/icon-styles.css';
                    }
                    // Include files
                    $styles[]   = $cssUrl;
                }

                if ($selectedCategoryId === '') {
                    $result .= '<div id="wpfd-category-placeholder" class="wpfd-category-placeholder">';
                    $result .= '<img class="category-icon" style="background: url('. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/category_widget_placeholder.svg') .') no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;" src="'. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif') .'" data-mce-src="'. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif') .'" data-mce-style="background: url('. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/category_widget_placeholder.svg') .') no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;">';
                    $result .= '<span style="font-size: 13px; text-align: center;">' . __('Please select a WP File Download content to activate the preview', 'wpfd') . '</span>';
                    $result .= '</div>';
                } else {
                    foreach ($styles as $style) {
                        $result .= '<link rel="' . esc_attr($type) . '" href="' . esc_url($style) . '" />';
                    }

                    $result     .= $this->wpfdAvadaCategoryShortcode($selectedCategoryId);
                }

                $html .= '<div class="wpfd-avada-category '. $extraClass .'" id="' . $extraId . '">';
                $html .= $result;
                $html .= '</div>';

                return apply_filters('wpfd_category_file_element_content', $html, $args);
            }

            /**
             * Sets the necessary scripts.
             *
             * @access public
             * @since  1.1
             * @return void
             */
            public function add_scripts()
            {

                Fusion_Dynamic_JS::enqueue_script(
                    'wpfd-category-live-script',
                    WPFD_PLUGIN_URL . '/app/includes/avada/assets/js/category.js',
                    WPFD_PLUGIN_DIR_PATH . '/app/includes/avada/assets/js/category.js',
                    [ 'jquery', 'fusion-animations' ],
                    '1',
                    true
                );
            }

            /**
             * Load base CSS.
             *
             * @access public
             * @since  3.0
             * @return void
             */
            public function add_css_files()
            {
                FusionBuilder()->add_element_css(WPFD_PLUGIN_DIR_PATH . '/app/includes/avada/assets/css/category.live.css');
            }
        }

    }

    new WpfdCategoryFile();
}

/**
 * Wpfd_category_custom_fields
 *
 * @param string|mixed $field_types Field types
 *
 * @throws Exception Fire when errors
 *
 * @return string|mixed
 */
function wpfd_category_custom_fields($field_types)
{

    $field_types['wpfd_category'] = array(
        'wpfd_category',
        realpath(WPFD_PLUGIN_DIR_PATH) . '/app/includes/avada/templates/wpfd_category.php'
    );

    return $field_types;
}

add_filter('fusion_builder_fields', 'wpfd_category_custom_fields', 10, 1);

/**
 * Wpfd_category_file_element
 *
 * @throws Exception Fire when errors
 *
 * @return void
 */
function wpfd_category_file_element()
{

    fusion_builder_map(
        fusion_builder_frontend_data(
            'WpfdCategoryFile',
            array(
                'name'              => esc_attr__('WP File Download Category', 'wpfd'),
                'shortcode'         => 'wpfd_category_file',
                'icon'              => 'wpfd-category-file-icon',
                'allow_generator'   => true,
                'inline_editor'     => true,
                'admin_enqueue_css' => WPFD_PLUGIN_URL . 'app/includes/avada/assets/css/avada.css',
                'preview'           => WPFD_PLUGIN_DIR_PATH . 'app/includes/avada/templates/category-file-preview.php',
                'preview_id'        => 'wpfd-category-file-block-module-preview-template',
                'params'            => array(
                    array(
                        'type'        => 'wpfd_category',
                        'heading'     => esc_attr__('Choose Category', 'wpfd'),
                        'description' => 'Select the WP File Download Category that will be displayed on this page.',
                        'param_name'  => 'wpfd_choose_category',
                    ),
                    array(
                        'type'        => 'textfield',
                        'param_name'  => 'element_content',
                        'value'       => ''
                    ),
                    array(
                        'type'        => 'textfield',
                        'param_name'  => 'wpfd_selected_category_random',
                        'value'       => '',
                    ),
                    array(
                        'type'        => 'textfield',
                        'param_name'  => 'wpfd_selected_category_id',
                        'value'       => '',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_attr__('Category Title', 'wpfd'),
                        'description' => esc_attr__('The title of the selected category.', 'wpfd'),
                        'param_name'  => 'wpfd_selected_category_title',
                        'value'       => '',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_attr__('CSS Class', 'wpfd'),
                        'description' => esc_attr__('Add a class to the wrapping HTML element.', 'wpfd'),
                        'param_name'  => 'class',
                        'value'       => '',
                        'group'       => esc_attr__('Extras', 'wpfd')
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_attr__('CSS ID', 'wpfd'),
                        'description' => esc_attr__('Add an ID to the wrapping HTML element.', 'wpfd'),
                        'param_name'  => 'id',
                        'value'       => '',
                        'group'       => esc_attr__('Extras', 'wpfd'),
                    ),
                )
            )
        )
    );
}

wpfd_category_file_element();

add_action('fusion_builder_before_init', 'wpfd_category_file_element');

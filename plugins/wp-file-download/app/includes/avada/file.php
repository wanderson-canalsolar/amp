<?php
use Joomunited\WPFramework\v1_0_5\Application;

if (fusion_is_element_enabled('wpfd_file')) {
    if (!class_exists('WpfdSingleFile')) {

        /**
         * Class WpfdSingleFile
         */
        class WpfdSingleFile extends Fusion_Element
        {

            /**
             * An array of the shortcode arguments.
             *
             * @var array
             */
            protected $args;

            /**
             * WpfdSingleFile construction
             */
            public function __construct()
            {
                parent::__construct();
                add_shortcode('wpfd_file', array($this, 'render'));
            }

            /**
             * WpfdAvadaSingleFileShortcode
             *
             * @param string|mixed $fileId     File id
             * @param string|mixed $categoryId Category id
             *
             * @throws Exception Fire when errors
             *
             * @return string|mixed
             */
            public function wpfdAvadaSingleFileShortcode($fileId, $categoryId)
            {
                $app             = Application::getInstance('Wpfd');
                $id_file         = $fileId;
                $id_category     = $categoryId;
                $wpfdhelperPath  = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'WpfdHelperShortcodes.php';
                require_once $wpfdhelperPath;

                $helperShortcode = new WpfdHelperShortcodes();
                $singleFile      = $helperShortcode->callSingleFile($id_file, $id_category);
                return $singleFile;
            }

            /**
             * Render
             *
             * @param array|string|mixed $args Param contents
             *
             * @throws Exception Fire when errors
             *
             * @return string|mixed
             */
            public function render($args)
            {
                $selectedCategoryId     = $args['wpfd_selected_category_id_related'];
                $selectedFileId         = $args['wpfd_selected_file_id'];
                $extraClass             = $args['class_extra'];
                $extraId                = $args['id_extra'];
                $result                 = '';
                $html                   = '';
                $type                   = 'stylesheet';
                $styles                 = array();
                $customSingleFilePath   = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'wpfd-single-file-button.css';
                $customSingleFileUrl    = WP_CONTENT_URL . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'wpfd-single-file-button.css';
                $styles[]               = WPFD_PLUGIN_URL . 'app/site/assets/css/front.css';
                $styles[]               = WPFD_PLUGIN_URL . 'app/admin/assets/ui/css/singlefile.css';
                $styles[]               = WPFD_PLUGIN_URL . 'app/site/assets/css/wpfd-single-file-button.css';

                if (file_exists($customSingleFilePath)) {
                    $styles[]           = $customSingleFileUrl;
                }

                if ($selectedFileId !== '' && $selectedCategoryId !== '') {
                    foreach ($styles as $style) {
                        $result .= '<link rel="'. esc_attr($type) .'" href="'. esc_url($style) .'" />';
                    }

                    $result .= $this->wpfdAvadaSingleFileShortcode($selectedFileId, $selectedCategoryId);
                } else {
                    $result .= '<div id="wpfd-file-placeholder" class="wpfd-file-placeholder">';
                    $result .= '<img class="single-file-icon" style="background: url('. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/file_widget_placeholder.svg') .') no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;" src="'. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif') .'" data-mce-src="'. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif') .'" data-mce-style="background: url('. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/file_widget_placeholder.svg') .') no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;">';
                    $result .= '<span style="font-size: 13px; text-align: center;">' . __('Please select a WP File Download content to activate the preview', 'wpfd') . '</span>';
                    $result .= '</div>';
                }

                $html .= '<div class="wpfd-avada-single-file '. $extraClass .'" id="' . $extraId . '">';
                $html .= $result;
                $html .= '</div>';

                return apply_filters('wpfd_single_file_element_content', $html, $args);
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
                FusionBuilder()->add_element_css(WPFD_PLUGIN_DIR_PATH . '/app/includes/avada/assets/css/singlefile.live.css');
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
                    'wpfd-single-file-live-script',
                    WPFD_PLUGIN_URL . '/app/includes/avada/assets/js/file.js',
                    WPFD_PLUGIN_DIR_PATH . '/app/includes/avada/assets/js/file.js',
                    [ 'jquery', 'fusion-animations' ],
                    '1',
                    true
                );
            }
        }

    }

    new WpfdSingleFile();
}

/**
 * WpfdSingleFileCustomFields
 *
 * @param string|mixed $field_types File types
 *
 * @throws Exception Fire when errors
 *
 * @return string|mixed
 */
function wpfdSingleFileCustomFields($field_types)
{

    $field_types['wpfd_single_file'] = array(
        'wpfd_single_file',
        realpath(WPFD_PLUGIN_DIR_PATH) . '/app/includes/avada/templates/wpfd_single_file.php'
    );

    return $field_types;
}

add_filter('fusion_builder_fields', 'wpfdSingleFileCustomFields', 10, 1);

/**
 * Wpfd_single_file_element
 *
 * @throws Exception Fire when errors
 *
 * @return void
 */
function wpfd_single_file_element()
{

    fusion_builder_map(
        fusion_builder_frontend_data(
            'WpfdSingleFile',
            array(
                'name'              => esc_attr__('WP File Download File', 'wpfd'),
                'shortcode'         => 'wpfd_file',
                'icon'              => 'wpfd-single-file-icon',
                'allow_generator'   => true,
                'admin_enqueue_css' => WPFD_PLUGIN_URL . 'app/includes/avada/assets/css/avada.css',
                'preview'           => WPFD_PLUGIN_DIR_PATH . 'app/includes/avada/templates/single-file-preview.php',
                'preview_id'        => 'wpfd-single-file-block-module-preview-template',
                'params'            => array(
                    array(
                        'type'        => 'wpfd_single_file',
                        'heading'     => esc_attr__('Choose File', 'wpfd'),
                        'description' => 'Select the WP File Download File that will be displayed on this page.',
                        'param_name'  => 'wpfd_choose_file',
                    ),
                    array(
                        'type'        => 'textfield',
                        'param_name'  => 'element_content',
                        'value'       => ''
                    ),
                    array(
                        'type'        => 'textfield',
                        'param_name'  => 'wpfd_selected_file_random',
                        'value'       => '',
                    ),
                    array(
                        'type'        => 'textfield',
                        'param_name'  => 'wpfd_selected_category_id_related',
                        'value'       => '',
                    ),
                    array(
                        'type'        => 'textfield',
                        'param_name'  => 'wpfd_selected_file_id',
                        'value'       => '',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_attr__('File Title', 'wpfd'),
                        'description' => esc_attr__('The title of the selected file.', 'wpfd'),
                        'param_name'  => 'wpfd_selected_file_title',
                        'value'       => '',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_attr__('CSS Class', 'wpfd'),
                        'description' => esc_attr__('Add a class to the wrapping HTML element.', 'wpfd'),
                        'param_name'  => 'class_extra',
                        'value'       => '',
                        'group'       => esc_attr__('Extras', 'wpfd')
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_attr__('CSS ID', 'wpfd'),
                        'description' => esc_attr__('Add an ID to the wrapping HTML element.', 'wpfd'),
                        'param_name'  => 'id_extra',
                        'value'       => '',
                        'group'       => esc_attr__('Extras', 'wpfd'),
                    ),
                )
            )
        )
    );
}

wpfd_single_file_element();

add_action('fusion_builder_before_init', 'wpfd_single_file_element');

<?php
use Joomunited\WPFramework\v1_0_5\Application;

if (fusion_is_element_enabled('wpfd_search_file')) {
    if (!class_exists('WpfdSearch')) {

        /**
         * Class WpfdSearch
         */
        class WpfdSearch extends Fusion_Element
        {

            /**
             * An array of the shortcode arguments.
             *
             * @var array
             */
            protected $args;

            /**
             * WpfdSearch construction
             */
            public function __construct()
            {
                parent::__construct();
                add_shortcode('wpfd_search_file', array($this, 'render'));
            }

            /**
             * WpfdAvadaSearchShortcode
             *
             * @param string|mixed $categoryFilter Filter by category
             * @param string|mixed $tagFilter      Filter by tags
             * @param string|mixed $tagAs          Display tag as
             * @param string|mixed $creationDate   Filter by creation date
             * @param string|mixed $updateDate     Filter by update date
             * @param string|mixed $pageFilter     List files on page
             *
             * @throws Exception Fire when errors
             *
             * @return string|mixed
             */
            public function wpfdAvadaSearchShortcode($categoryFilter, $tagFilter, $tagAs, $creationDate, $updateDate, $pageFilter)
            {
                $app                    = Application::getInstance('Wpfd');
                $searchAtts             = array(
                    'cat_filter'        => $categoryFilter,
                    'tag_filter'        => $tagFilter,
                    'display_tag'       => $tagAs,
                    'create_filter'     => $creationDate,
                    'update_filter'     => $updateDate,
                    'file_per_page'     => $pageFilter
                );
                $helperPath             = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'WpfdHelperShortcodes.php';
                require_once $helperPath;
                $helper                 = new WpfdHelperShortcodes();
                $searchShortCode        = $helper->wpfdSearchShortcode($searchAtts);
                return $searchShortCode;
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
                $categoryFilter     = ( $args['wpfd_filter_by_category'] === 'yes' ) ? '1' : '0';
                $tagFilter          = ( $args['wpfd_filter_by_tag'] === 'yes' ) ? '1' : '0';
                $tagAs              = $args['wpfd_filter_tag_as'];
                $creationDate       = ( $args['wpfd_filter_creation_date'] === 'yes' ) ? '1' : '0';
                $updateDate         = ( $args['wpfd_filter_update_date'] === 'yes' ) ? '1' : '0';
                $perPageFilter      = $args['wpfd_filter_per_page'];
                $extraClass         = $args['class'];
                $extraId            = $args['id'];
                $html               = '';
                $result             = $this->wpfdAvadaSearchShortcode($categoryFilter, $tagFilter, $tagAs, $creationDate, $updateDate, $perPageFilter);

                $html .= '<div class="wpfd-avada-search '. $extraClass .'" id="' . $extraId . '">';
                $html .= $result;
                $html .= '</div>';

                return apply_filters('wpfd_search_element_content', $html, $args);
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
                FusionBuilder()->add_element_css(WPFD_PLUGIN_DIR_PATH . '/app/includes/avada/assets/css/search.live.css');
            }
        }

    }

    new WpfdSearch();
}

/**
 * Wpfd_search_element
 *
 * @throws Exception Fire when errors
 *
 * @return void
 */
function wpfd_search_element()
{

    fusion_builder_map(
        fusion_builder_frontend_data(
            'WpfdSearch',
            array(
                'name'              => esc_attr__('WP File Download Search', 'wpfd'),
                'shortcode'         => 'wpfd_search_file',
                'icon'              => 'wpfd-search-file-icon',
                'allow_generator'   => true,
                'inline_editor'     => true,
                'admin_enqueue_css' => WPFD_PLUGIN_URL . 'app/includes/avada/assets/css/avada.css',
                'preview'           => WPFD_PLUGIN_DIR_PATH . 'app/includes/avada/templates/search-file-preview.php',
                'preview_id'        => 'wpfd-search-file-block-module-preview-template',
                'params'            => array(
                    array(
                        'type'        => 'textfield',
                        'param_name'  => 'element_content',
                        'value'       => '[wpfd_search cat_filter="1" tag_filter="0" display_tag="searchbox" create_filter="1" update_filter="1" file_per_page="20"]'
                    ),
                    array(
                        'type'        => 'radio_button_set',
                        'heading'     => esc_attr__('Filter by category', 'wpfd'),
                        'description' => esc_attr__('If you want to search by category, choose Yes and vice versa choose No.', 'wpfd'),
                        'param_name'  => 'wpfd_filter_by_category',
                        'value'       => array(
                            'yes'     => esc_attr__('Yes', 'wpfd'),
                            'no'      => esc_attr__('No', 'wpfd'),
                        ),
                        'default'     => 'yes'
                    ),
                    array(
                        'type'        => 'radio_button_set',
                        'heading'     => esc_attr__('Filter by tag', 'wpfd'),
                        'description' => esc_attr__('If you want to search by tag, choose Yes and vice versa choose No.', 'wpfd'),
                        'param_name'  => 'wpfd_filter_by_tag',
                        'value'       => array(
                            'yes'     => esc_attr__('Yes', 'wpfd'),
                            'no'      => esc_attr__('No', 'wpfd'),
                        ),
                        'default'     => 'no'
                    ),
                    array(
                        'type'        => 'select',
                        'heading'     => esc_attr__('Display tag as', 'wpfd'),
                        'description' => esc_attr__('You can choose how to display the search tag by Search box or Multiple option.', 'wpfd'),
                        'param_name'  => 'wpfd_filter_tag_as',
                        'default'     => 'searchbox',
                        'value'       => array(
                            'searchbox'      => esc_attr__('Search box', 'wpfd'),
                            'checkbox'       => esc_attr__('Multiple select', 'wpfd')
                        ),
                    ),
                    array(
                        'type'        => 'radio_button_set',
                        'heading'     => esc_attr__('Filter by creation date', 'wpfd'),
                        'description' => esc_attr__('If you want to search by creation date, choose Yes and vice versa choose No.', 'wpfd'),
                        'param_name'  => 'wpfd_filter_creation_date',
                        'value'       => array(
                            'yes'     => esc_attr__('Yes', 'wpfd'),
                            'no'      => esc_attr__('No', 'wpfd'),
                        ),
                        'default'     => 'yes'
                    ),
                    array(
                        'type'        => 'radio_button_set',
                        'heading'     => esc_attr__('Filter by update date', 'wpfd'),
                        'description' => esc_attr__('If you want to search by update date, choose Yes and vice versa choose No.', 'wpfd'),
                        'param_name'  => 'wpfd_filter_update_date',
                        'value'       => array(
                            'yes'     => esc_attr__('Yes', 'wpfd'),
                            'no'      => esc_attr__('No', 'wpfd'),
                        ),
                        'default'     => 'yes'
                    ),
                    array(
                        'type'        => 'select',
                        'heading'     => esc_attr__('# Files per page', 'wpfd'),
                        'description' => esc_attr__('Select the number of files found to show up on your search page.', 'wpfd'),
                        'param_name'  => 'wpfd_filter_per_page',
                        'default'     => '20',
                        'value'       => array(
                            '5'        => esc_attr__('5', 'wpfd'),
                            '10'       => esc_attr__('10', 'wpfd'),
                            '15'       => esc_attr__('15', 'wpfd'),
                            '20'       => esc_attr__('20', 'wpfd'),
                            '25'       => esc_attr__('25', 'wpfd'),
                            '30'       => esc_attr__('30', 'wpfd'),
                            '50'       => esc_attr__('50', 'wpfd'),
                            '100'      => esc_attr__('100', 'wpfd'),
                            '-1'       => esc_attr__('all', 'wpfd')
                        ),
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

wpfd_search_element();

add_action('fusion_builder_before_init', 'wpfd_search_element');

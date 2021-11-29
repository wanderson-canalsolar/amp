<?php
use Joomunited\WPFramework\v1_0_5\Application;

/**
 * Class WpfdWPBakerySearch
 */
class WpfdWPBakerySearch extends WPBakeryShortCode
{
    /**
     * WpfdWPBakerySearch constructor.
     */
    public function __construct()
    {
        add_action('init', array( $this, 'wpfdCreateShortcode' ), 999);
        add_shortcode('wpfd_search_shortcode', array( $this, 'wpfdRenderShortcode' ));
    }

    /**
     * WpfdCreateShortcode
     *
     * @return void
     */
    public function wpfdCreateShortcode()
    {

        vc_map(
            array(
                'name'          => __('WP File Download Search', 'wpfd'),
                'base'          => 'wpfd_search_shortcode',
                'description'   => __('Display search engine with options', 'wpfd'),
                'category'      => __('JoomUnited', 'wpfd'),
                'icon'          => 'wpfd-search-icon',
                'params'        => array(

                    array(
                        'type'          => 'textfield',
                        'holder'        => 'div',
                        'param_name'    => 'content',
                        'value'         => '<!-- wp:paragraph --><p style="margin: 0"><span style="font-weight: bold">Search Shortcode: </span>[wpfd_search cat_filter="1" tag_filter="0" display_tag="searchbox" create_filter="1" update_filter="1" file_per_page="20"]</p><!-- /wp:paragraph -->'
                    ),

                    array(
                        'type'          => 'dropdown',
                        'heading'       => __('Filter by category', 'wpfd'),
                        'param_name'    => 'wpfd_filter_by_category',
                        'value'         => array(
                            __('Yes', 'wpfd')     => 1,
                            __('No', 'wpfd')    => 0
                        ),
                        'description'   => __('If you want to search by category, choose Yes and vice versa choose No.', 'wpfd')
                    ),

                    array(
                        'type'          => 'dropdown',
                        'heading'       => __('Filter by tag', 'wpfd'),
                        'param_name'    => 'wpfd_filter_by_tag',
                        'value'         => array(
                            __('No', 'wpfd')     => 0,
                            __('Yes', 'wpfd')     => 1
                        ),
                        'description'   => __('If you want to search by tag, choose Yes and vice versa choose No.', 'wpfd')
                    ),

                    array(
                        'type'          => 'dropdown',
                        'heading'       => __('Display tag as', 'wpfd'),
                        'param_name'    => 'wpfd_filter_tag_as',
                        'value'         => array(
                            __('Search box', 'wpfd')        => 1,
                            __('Multiple select', 'wpfd')   => 2
                        ),
                        'description'   => __('You can choose how to display the search tag by Search box or Multiple option.', 'wpfd')
                    ),

                    array(
                        'type'          => 'dropdown',
                        'heading'       => __('Filter by creation date', 'wpfd'),
                        'param_name'    => 'wpfd_filter_creation_date',
                        'value'         => array(
                            __('Yes', 'wpfd')        => 1,
                            __('No', 'wpfd')         => 0
                        ),
                        'description'   => __('If you want to search by creation date, choose Yes and vice versa choose No.', 'wpfd')
                    ),

                    array(
                        'type'          => 'dropdown',
                        'heading'       => __('Filter by update date', 'wpfd'),
                        'param_name'    => 'wpfd_filter_update_date',
                        'value'         => array(
                            __('Yes', 'wpfd')        => 1,
                            __('No', 'wpfd')         => 0
                        ),
                        'description'   => __('If you want to search by update date, choose Yes and vice versa choose No.', 'wpfd')
                    ),

                    array(
                        'type'          => 'dropdown',
                        'heading'       => __('# Files per page', 'wpfd'),
                        'param_name'    => 'wpfd_filter_per_page',
                        'value'         => array(
                            __('20', 'wpfd')         => 20,
                            __('5', 'wpfd')          => 5,
                            __('10', 'wpfd')         => 10,
                            __('15', 'wpfd')         => 15,
                            __('25', 'wpfd')         => 25,
                            __('30', 'wpfd')         => 30,
                            __('50', 'wpfd')         => 50,
                            __('100', 'wpfd')        => 100,
                            __('all', 'wpfd')        => -1
                        ),
                        'description'   => __('Select the number of files found to show up on your search page.', 'wpfd')
                    ),

                    array(
                        'type'          => 'textfield',
                        'heading'       => __('Element ID', 'wpfd'),
                        'param_name'    => 'wpfd_search_extra_id',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd'),
                        'description'   => __('Enter element ID (Note: make sure it is unique and valid).', 'wpfd'),
                        'group'         => __('Extra', 'wpfd'),
                    ),

                    array(
                        'type'          => 'textfield',
                        'heading'       => __('Extra class name', 'wpfd'),
                        'param_name'    => 'wpfd_search_extra_class',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd'),
                        'description'   => __('Style particular content element differently - add a class name and refer to it in custom CSS.', 'wpfd'),
                        'group'         => __('Extra', 'wpfd'),
                    ),

                    array(
                        'type'          => 'css_editor',
                        'heading'       => esc_html__('CSS box', 'wpfd'),
                        'param_name'    => 'css',
                        'group'         => esc_html__('Design Options', 'wpfd'),
                    )

                )
            )
        );
    }

    /**
     * WpfdRenderShortcode
     *
     * @param array|mixed $atts Params value
     *
     * @throws Exception Fire when errors
     *
     * @return string|array|mixed
     */
    public function wpfdRenderShortcode($atts)
    {
        $atts = (shortcode_atts(array(
            'wpfd_filter_by_category'     => '',
            'wpfd_filter_by_tag'          => '',
            'wpfd_filter_tag_as'          => '',
            'wpfd_filter_creation_date'   => '',
            'wpfd_filter_update_date'     => '',
            'wpfd_filter_per_page'        => '',
            'wpfd_search_extra_class'     => '',
            'wpfd_search_extra_id'        => '',
            'css'                         => ''
        ), $atts));

        $filter_by_category             = esc_attr($atts['wpfd_filter_by_category']);
        $wpfd_filter_by_tag             = esc_attr($atts['wpfd_filter_by_tag']);
        $wpfd_filter_tag_as             = (esc_attr($atts['wpfd_filter_tag_as']) === '1') ? 'searchbox' : 'checkbox';
        $wpfd_filter_creation_date      = esc_attr($atts['wpfd_filter_creation_date']);
        $wpfd_filter_update_date        = esc_attr($atts['wpfd_filter_update_date']);
        $wpfd_filter_per_page           = esc_attr($atts['wpfd_filter_per_page']);
        $search_class                   = esc_attr($atts['wpfd_search_extra_class']);
        $search_id                      = esc_attr($atts['wpfd_search_extra_id']);
        $css_animation                  = esc_attr($atts['css']);
        $css_animation_class            = vc_shortcode_custom_css_class($css_animation, ' ');

        $result                         = $this->wpfdWPBakerySearchShortcode($filter_by_category, $wpfd_filter_by_tag, $wpfd_filter_tag_as, $wpfd_filter_creation_date, $wpfd_filter_update_date, $wpfd_filter_per_page);

        $output = '';
        $output .= '<div class="wpfd-wpbakery-search ' . $search_class . ' '. $css_animation_class .'" id="' . $search_id . '" >';
        $output .= $result;
        $output .= '</div>';

        return $output;
    }

    /**
     * WpfdWPBakerySearchShortcode
     *
     * @param mixed $categoryFilter     Filter by category option
     * @param mixed $tagFilter          Filter by tag option
     * @param mixed $tagAs              Display tag as
     * @param mixed $creationDateFilter Filter by creation date option
     * @param mixed $updateDateFilter   Filter by update date option
     * @param mixed $pageFilter         Display file number in search result
     *
     * @throws Exception Fire when errors
     *
     * @return string|array|mixed
     */
    public function wpfdWPBakerySearchShortcode($categoryFilter, $tagFilter, $tagAs, $creationDateFilter, $updateDateFilter, $pageFilter)
    {
        $app                    = Application::getInstance('Wpfd');
        $searchAtts             = array(
            'cat_filter'        => $categoryFilter,
            'tag_filter'        => $tagFilter,
            'display_tag'       => $tagAs,
            'create_filter'     => $creationDateFilter,
            'update_filter'     => $updateDateFilter,
            'file_per_page'     => $pageFilter
        );
        $helperPath             = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'WpfdHelperShortcodes.php';
        require_once $helperPath;
        $helper                 = new WpfdHelperShortcodes();
        $searchShortCode        = $helper->wpfdSearchShortcode($searchAtts);
        return $searchShortCode;
    }
}

new WpfdWPBakerySearch();

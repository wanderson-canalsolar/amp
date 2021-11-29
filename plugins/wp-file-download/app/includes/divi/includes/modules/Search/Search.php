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

class DIVI_Search extends ET_Builder_Module
{

    public $slug       = 'divi_wpfd_search';
    public $vb_support = 'on';

    public function init()
    {
        $this->name = esc_html__('WPFD Search File', 'wpfd');
    }

    public function get_advanced_fields_config()
    {
        return array(
            'button'       => false,
            'link_options' => false
        );
    }

    public function get_fields()
    {
        return array(
            'category_filter'           => array(
                'label'                 => esc_html__('Filter by category: ', 'wpfd'),
                'type'                  => 'yes_no_button',
                'option_category'       => 'configuration',
                'options'               => array(
                    'off'               => esc_html__('No', 'wpfd'),
                    'on'                => esc_html__('Yes', 'wpfd'),
                ),
                'default'               => 'on',
                'class'                 => 'search-category-filter'
            ),

            'tag_filter'                => array(
                'label'                 => esc_html__('Filter by tag: ', 'wpfd'),
                'type'                  => 'yes_no_button',
                'option_category'       => 'configuration',
                'options'               => array(
                    'off'               => esc_html__('No', 'wpfd'),
                    'on'                => esc_html__('Yes', 'wpfd'),
                ),
                'default'               => 'off',
                'class'                 => 'search-tag-filter'
            ),

            'display_tag_as'            => array(
                'label'                 => esc_html__('Display tag as: ', 'wpfd'),
                'type'                  => 'select',
                'option_category'       => 'configuration',
                'options'               => array(
                    'searchbox'        => esc_html__('Search box', 'wpfd'),
                    'checkbox'   => esc_html__('Multiple select', 'wpfd'),
                ),
                'default'               => 'searchbox',
                'class'                 => 'search-display-tag-as'
            ),

            'creation_date_filter'      => array(
                'label'                 => esc_html__('Filter by creation date: ', 'wpfd'),
                'type'                  => 'yes_no_button',
                'option_category'       => 'configuration',
                'options'               => array(
                    'off'               => esc_html__('No', 'wpfd'),
                    'on'                => esc_html__('Yes', 'wpfd'),
                ),
                'default'               => 'on',
                'class'                 => 'search-creation-date-filter'
            ),

            'update_date_filter'        => array(
                'label'                 => esc_html__('Filter by update date: ', 'wpfd'),
                'type'                  => 'yes_no_button',
                'option_category'       => 'configuration',
                'options'               => array(
                    'off'               => esc_html__('No', 'wpfd'),
                    'on'                => esc_html__('Yes', 'wpfd'),
                ),
                'default'               => 'on',
                'class'                 => 'search-update-date-filter'
            ),

            'per_page_filter'           => array(
                'label'                 => esc_html__('# Files per page: ', 'wpfd'),
                'type'                  => 'select',
                'option_category'       => 'configuration',
                'options'               => array(
                    '5'                 => esc_html__('5', 'wpfd'),
                    '10'                => esc_html__('10', 'wpfd'),
                    '15'                => esc_html__('15', 'wpfd'),
                    '20'                => esc_html__('20', 'wpfd'),
                    '25'                => esc_html__('25', 'wpfd'),
                    '30'                => esc_html__('30', 'wpfd'),
                    '50'                => esc_html__('50', 'wpfd'),
                    '100'               => esc_html__('100', 'wpfd'),
                    '-1'                => esc_html__('all', 'wpfd')
                ),
                'default'               => '20',
                'class'                 => 'search-per-page-filter'
            )
        );
    }

    public function render($attrs, $content = null, $render_slug)
    {
        $category_filter        = ($this->props['category_filter'] === 'on') ? 1 : 0;
        $tag_filter             = ($this->props['tag_filter'] === 'on') ? 1 : 0;
        $display_tag            = $this->props['display_tag_as'];
        $creation_date_filter   = ($this->props['creation_date_filter'] === 'on') ? 1 : 0;
        $update_date_filter     = ($this->props['update_date_filter'] === 'on') ? 1 : 0;
        $page_filter            = $this->props['per_page_filter'];

        return do_shortcode('[wpfd_search cat_filter="'. $category_filter .'" tag_filter="'. $tag_filter .'" display_tag="'. $display_tag .'" create_filter="'. $creation_date_filter .'" update_filter="'. $update_date_filter .'" file_per_page="'. $page_filter .'"]');
    }
}

new DIVI_Search;

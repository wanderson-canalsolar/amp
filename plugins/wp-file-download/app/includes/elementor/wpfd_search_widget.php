<?php
namespace WpfdSearchWidget;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Elementor Search Widget.
 *
 * Elementor widget that inserts Search content into the page.
 */
class ElementorSearchWidget extends \Elementor\Widget_Base
{
    /**
    * Get widget name.
    *
    * Retrieve Search widget name.
    *
    * @access public
    *
    * @return string Widget name.
    */
    public function get_name()
    {
        return 'wpfd_search';
    }

    /**
    * Get widget title.
    *
    * Retrieve Search widget title.
    *
    * @access public
    *
    * @return string Widget title.
    */
    public function get_title()
    {
        return __('WP File Download Search', 'wpfd');
    }

    /**
    * Get widget icon.
    *
    * Retrieve Search widget icon.
    *
    * @access public
    *
    * @return string Widget icon.
    */
    public function get_icon()
    {
        return 'wp-file-download-search';
    }

    /**
    * Get widget categories.
    *
    * Retrieve the list of categories the Search widget belongs to.
    *
    * @access public
    *
    * @return array Widget categories.
    */
    public function get_categories()
    {
        return array('general');
    }

    /**
    * Register Search widget controls.
    *
    * Add different input fields to allow the user to change and customize the widget settings.
    *
    * @access protected
    *
    * @return void
    */
    protected function _register_controls()
    {

        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'wpfd'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT
            )
        );

        $this->add_control(
            'wpfd_filter_by_category',
            array(
                'label' => __('Filter by category', 'wpfd'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('.', 'wpfd'),
                'label_off' => __('.', 'wpfd'),
                'return_value' => '1',
                'default' => '1',
                'classes'   => 'wpfd_search_switcher_control'
            )
        );

        $this->add_control(
            'wpfd_filter_by_tag',
            array(
                'label' => __('Filter by tag', 'wpfd'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('.', 'wpfd'),
                'label_off' => __('.', 'wpfd'),
                'return_value' => '1',
                'default' => 'no',
                'classes'   => 'wpfd_search_switcher_control'
            )
        );

        $this->add_control(
            'wpfd_filter_display_tag_as',
            array(
                'label' => __('Display tag as', 'wpfd'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'searchbox',
                'options' => array(
                    'searchbox'  => __('Search box', 'wpfd'),
                    'checkbox' => __('Multiple select', 'wpfd')
                )
            )
        );

        $this->add_control(
            'wpfd_filter_by_creation_date',
            array(
                'label' => __('Filter by creation date', 'wpfd'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('.', 'wpfd'),
                'label_off' => __('.', 'wpfd'),
                'return_value' => '1',
                'default' => '1',
                'classes'   => 'wpfd_search_switcher_control'
            )
        );

        $this->add_control(
            'wpfd_filter_by_update_date',
            array(
                'label' => __('Filter by update date', 'wpfd'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('.', 'wpfd'),
                'label_off' => __('.', 'wpfd'),
                'return_value' => '1',
                'default' => '1',
                'classes'   => 'wpfd_search_switcher_control'
            )
        );

        $this->add_control(
            'wpfd_filter_per_page',
            array(
                'label' => __('# Files per page', 'wpfd'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '20',
                'options' => array(
                    '5'     => __('5', 'wpfd'),
                    '10'    => __('10', 'wpfd'),
                    '15'    => __('15', 'wpfd'),
                    '20'    => __('20', 'wpfd'),
                    '25'    => __('25', 'wpfd'),
                    '30'    => __('30', 'wpfd'),
                    '50'    => __('50', 'wpfd'),
                    '100'   => __('100', 'wpfd'),
                    '-1'    => __('all', 'wpfd')
                )
            )
        );

        $this->end_controls_section();
    }

    /**
    * Render Search widget output on the frontend.
    *
    * Written in PHP and used to generate the final HTML.
    *
    * @access protected
    *
    * @return void
    */
    public function render()
    {
        $settings = $this->get_settings_for_display();
        $settings['wpfd_filter_by_category']        = ((int) $settings['wpfd_filter_by_category'] === 1) ? 1 : 0;
        $settings['wpfd_filter_by_tag']             = ((int) $settings['wpfd_filter_by_tag'] === 1) ? 1 : 0;
        $settings['wpfd_filter_by_creation_date']   = ((int) $settings['wpfd_filter_by_creation_date'] === 1) ? 1 : 0;
        $settings['wpfd_filter_by_update_date']     = ((int) $settings['wpfd_filter_by_update_date'] === 1) ? 1 : 0;
        $type = 'stylesheet';
        $styles = array();
        $styles[] = WPFD_PLUGIN_URL . 'app/site/assets/css/front.css';
        $styles[] = WPFD_PLUGIN_URL . 'app/site/assets/css/search_filter.css';
        if (is_admin()) {
            foreach ($styles as $style) {
                echo '<link rel="'.esc_attr($type).'" href="'.esc_url($style).'" />';
            }
        }
        echo do_shortcode('[wpfd_search cat_filter="'. $settings['wpfd_filter_by_category'] .'" tag_filter="'. $settings['wpfd_filter_by_tag'] .'" display_tag="'. $settings['wpfd_filter_display_tag_as'] .'" create_filter="'. $settings['wpfd_filter_by_creation_date'] .'" update_filter="'. $settings['wpfd_filter_by_update_date'] .'" file_per_page="'. $settings['wpfd_filter_per_page'] .'"]');
    }
}

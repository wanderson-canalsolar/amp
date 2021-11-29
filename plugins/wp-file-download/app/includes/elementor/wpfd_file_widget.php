<?php
namespace WpfdSingleFileWidget;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Elementor Single File Widget.
 *
 * Elementor widget that inserts an Single File content into the page.
 */
class ElementorSingleFileWidget extends \Elementor\Widget_Base
{
    /**
    * Get widget name.
    *
    * Retrieve Single File widget name.
    *
    * @access public
    *
    * @return string Widget name.
    */
    public function get_name()
    {
        return 'wpfd_choose_file';
    }

    /**
    * Get widget title.
    *
    * Retrieve Single File widget title.
    *
    * @access public
    *
    * @return string Widget title.
    */
    public function get_title()
    {
        return __('WP File Download File', 'wpfd');
    }

    /**
    * Get widget icon.
    *
    * Retrieve Single File widget icon.
    *
    * @access public
    *
    * @return string Widget icon.
    */
    public function get_icon()
    {
        return 'wp-file-download-file';
    }

    /**
    * Get widget categories.
    *
    * Retrieve the list of categories the Single File widget belongs to.
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
    * Register Single File widget controls.
    *
    * Adds different input fields to allow the user to change and customize the widget settings.
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
            'wpfd_choose_file',
            array(
                'label' => __('Choose File', 'wpfd'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<a href="#wpfdelementormodal" class="button wpfdelementorlaunch" id="wpfdelementorlaunch" title="WP File Download"><span class="dashicons wpfd-choose-file-button"></span> <span class="title">' . esc_html__('WP File Download', 'wpfd') . '</span></a>',
                'content_classes' => 'wpfd-choose-file-controls'
            )
        );

        $this->add_control(
            'wpfd_file_id',
            array(
                'label' => __('File Id', 'wpfd'),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'input_type' => 'text',
                'classes' => 'wpfd-file-id-controls'
            )
        );

        $this->add_control(
            'wpfd_category_id',
            array(
                'label' => __('Category Id', 'wpfd'),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'input_type' => 'text',
                'classes' => 'wpfd-category-id-controls'
            )
        );

        $this->add_control(
            'wpfd_file_name',
            array(
                'label' => __('File Title', 'wpfd'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'input_type' => 'text',
                'classes' => 'wpfd-file-name-controls'
            )
        );

        $this->end_controls_section();
    }

    /**
    * Render Single File widget output on the frontend.
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
        $fileId = $settings['wpfd_file_id'];
        $type = 'stylesheet';
        $styles = array();
        $styles[] = WPFD_PLUGIN_URL . 'app/site/assets/css/front.css';
        $styles[] = WPFD_PLUGIN_URL . 'app/admin/assets/ui/css/singlefile.css';
        $styles[] = WPFD_PLUGIN_URL . 'app/site/assets/css/wpfd-single-file-button.css';

        if ($fileId) {
            if (is_admin()) {
                foreach ($styles as $style) {
                    echo '<link rel="'. esc_attr($type) .'" href="'.esc_url($style).'" />';
                }
            }
            echo '<div id="wpfd-elementor-single-file" class="wpfd-elementor-single-file">';
            echo do_shortcode('[wpfd_single_file id="'. $fileId .'" catid="'. $settings['wpfd_category_id'] .'" name="'. $settings['wpfd_file_name'] .'"]');
            echo '</div>';
        } else { ?>
            <div id="wpfd-file-placeholder" class="wpfd-file-placeholder">
                <img class="single-file-icon" style="background: url(<?php echo esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/file_widget_placeholder.svg'); ?>) no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;" src="<?php echo esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif'); ?>" data-mce-src="<?php echo esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif'); ?>" data-mce-style="background: url(<?php echo esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/file_widget_placeholder.svg'); ?>) no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;">
                <span style="font-size: 13px; text-align: center;"><?php echo esc_html_e('Please select a WP File Download content to activate the preview', 'wpfd'); ?></span>
            </div>
        <?php }
    }
}
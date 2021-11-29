<?php
namespace WpfdCategoryWidget;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Elementor Category Widget.
 *
 * Elementor widget that inserts an Category content into the page.
 */
class ElementorCategoryWidget extends \Elementor\Widget_Base
{
    /**
    * Get widget name.
    *
    * Retrieve Category widget name.
    *
    * @access public
    *
    * @return string Widget name.
    */
    public function get_name()
    {
        return 'wpfd_choose_category';
    }

    /**
    * Get widget title.
    *
    * Retrieve Category widget title.
    *
    * @access public
    *
    * @return string Widget title.
    */
    public function get_title()
    {
        return __('WP File Download Category', 'wpfd');
    }

    /**
    * Get widget icon.
    *
    * Retrieve Category widget icon.
    *
    * @access public
    *
    * @return string Widget icon.
    */
    public function get_icon()
    {
        return 'fa wp-file-download-category';
    }

    /**
    * Get widget categories.
    *
    * Retrieve the list of categories the Category widget belongs to.
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
    * Register Category widget controls.
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
            'wpfd_category_alert',
            array(
                'label' => __('Title', 'wpfd'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="elementor-control-content"><div class="elementor-control-raw-html elementor-panel-alert elementor-panel-alert-info">'. esc_html__('The current Elementor preview looks different from the public view (frontend). Please do load a real page preview to see the rendering.', 'wpfd') .'</div></div>',
                'content_classes' => 'wpfd-category-alert-controls'
            )
        );

        $this->add_control(
            'wpfd_choose_category',
            array(
                'label' => __('Choose Category', 'wpfd'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<a href="#wpfdelementormodal" class="button wpfdcategorylaunch" id="wpfdcategorylaunch" title="WP File Download"><span class="dashicons wpfd-choose-category-button"></span> <span class="title">' . esc_html__('WP File Download', 'wpfd') . '</span></a>',
                'content_classes' => 'wpfd-choose-category-controls'
            )
        );

        $this->add_control(
            'wpfd_selected_category_id',
            array(
                'label' => __('Category Id', 'wpfd'),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'input_type' => 'text',
                'classes' => 'wpfd-selected-category-id-controls'
            )
        );

        $this->add_control(
            'wpfd_selected_category_name',
            array(
                'label' => __('Category Title', 'wpfd'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'input_type' => 'text',
                'classes' => 'wpfd-selected-category-name-controls'
            )
        );

        $this->end_controls_section();
    }

    /**
    * Render Category widget output on the frontend.
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
        $catId = $settings['wpfd_selected_category_id'];
        $type = 'stylesheet';
        $styles = array();
        $styles[] = WPFD_PLUGIN_URL . '/app/site/assets/css/front.css';
        $theme = 'default';
        $defaultConfig = array(
            'defaultthemepercategory' => 'default',
            'catparameters' => 1,
            'icon_set' => 'default',
        );

        $config = get_option('_wpfd_global_config', $defaultConfig);
        if (intval($config['catparameters']) === 1) {
            $category = get_term($catId, 'wpfd-category');
            if (isset($category->description) && $category->description !== '') {
                $description = json_decode($category->description);
                $theme = isset($description->theme) ? $description->theme : 'default';
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
            $path = \WpfdHelperFile::getCustomIconPath($iconSet);
            $cssPath = $path . 'styles-' . $lastRebuildTime . '.css';
            if (file_exists($cssPath)) {
                $cssUrl = wpfd_abs_path_to_url($cssPath);
            } else {
                // Use default css pre-builed
                $cssUrl = WPFD_PLUGIN_URL . 'app/site/assets/icons/' . $iconSet . '/icon-styles.css';
            }
            // Include file
            $styles[] = $cssUrl;
        }

        if ($catId) {
            if (is_admin()) {
                foreach ($styles as $style) {
                    echo '<link rel="'.esc_attr($type).'" href="'.esc_url($style).'" />';
                }
            }
            echo '<div id="wpfd-elementor-category" class="wpfd-elementor-category">';
            echo do_shortcode('[wpfd_category id="'. $catId .'"]');
            echo '</div>';
        } else { ?>
            <div id="wpfd-category-placeholder" class="wpfd-category-placeholder">
                <img class="category-icon" style="background: url(<?php echo esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/category_widget_placeholder.svg'); ?>) no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;" src="<?php echo esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif'); ?>" data-mce-src="<?php echo esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif'); ?>" data-mce-style="background: url(<?php echo esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/category_widget_placeholder.svg'); ?>) no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;">
                <span style="font-size: 13px; text-align: center;"><?php echo esc_html_e('Please select a WP File Download content to activate the preview', 'wpfd'); ?></span>
            </div>
        <?php }
    }
}
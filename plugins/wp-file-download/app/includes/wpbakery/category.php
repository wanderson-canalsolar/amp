<?php
use Joomunited\WPFramework\v1_0_5\Application;

/**
 * Class WpfdWPBakeryCategory
 */
class WpfdWPBakeryCategory extends WPBakeryShortCode
{

    /**
     * WpfdWPBakeryCategory construction
     */
    public function __construct()
    {
        add_action('init', array( $this, 'wpfdCreateShortcode' ), 999);
        add_shortcode('wpfd_category_shortcode', array( $this, 'wpfdRenderShortcode' ));
    }

    /**
     * WpfdCreateShortcode
     *
     * @throws Exception Fire when errors
     *
     * @return void
     */
    public function wpfdCreateShortcode()
    {

        $this->wpfdWPBakeryInitCustomCategoryField();

        vc_map(
            array(
                'name'          => __('WP File Download Category', 'wpfd'),
                'base'          => 'wpfd_category_shortcode',
                'description'   => __('Responsive file category with themes', 'wpfd'),
                'category'      => __('JoomUnited', 'wpfd'),
                'icon'          => 'wpfd-category-icon',
                'params'        => array(

                    array(
                        'type'          => 'wpfd_category',
                        'class'         => 'wpfd-choose-category-control',
                        'holder'        => 'div',
                        'heading'       => __('Choose Category', 'wpfd'),
                        'param_name'    => 'content',
                        'value'         => '<!-- wp:paragraph --><p>Hello! This is the Wp File Download Category you can edit directly from the WPBakery Page Builder.</p><!-- /wp:paragraph -->',
                        'description'   => __('Select the WP File Download Category that will be displayed on this page.', 'wpfd'),
                    ),

                    array(
                        'type'          => 'hidden',
                        'param_name'    => 'wpfd_category_random',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd')
                    ),

                    array(
                        'type'          => 'hidden',
                        'class'         => 'wpfd-selected-category-id-control',
                        'param_name'    => 'wpfd_selected_category_id',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd')
                    ),

                    array(
                        'type'          => 'textfield',
                        'class'         => 'wpfd-category-title-control',
                        'heading'       => __('Category Title: ', 'wpfd'),
                        'param_name'    => 'wpfd_category_title',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd'),
                        'description'   => __('The title of the selected category.', 'wpfd'),
                    ),

                    array(
                        'type'          => 'textfield',
                        'heading'       => __('Element ID', 'wpfd'),
                        'param_name'    => 'wpfd_category_id',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd'),
                        'description'   => __('Enter element ID (Note: make sure it is unique and valid).', 'wpfd'),
                        'group'         => __('Extra', 'wpfd'),
                    ),

                    array(
                        'type'          => 'textfield',
                        'heading'       => __('Extra class name', 'wpfd'),
                        'param_name'    => 'wpfd_category_class',
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
     * @param array|mixed $atts Attributes
     *
     * @throws Exception Fire when errors
     *
     * @return string|mixed
     */
    public function wpfdRenderShortcode($atts)
    {
        $atts = (shortcode_atts(array(
            'wpfd_selected_category_id'     => '',
            'wpfd_category_class'           => '',
            'wpfd_category_id'              => '',
            'css'                           => ''
        ), $atts));

        $category_selected_id       = esc_attr($atts['wpfd_selected_category_id']);
        $category_class             = esc_attr($atts['wpfd_category_class']);
        $category_id                = esc_attr($atts['wpfd_category_id']);
        $css_animation              = esc_attr($atts['css']);
        $css_animation_class        = vc_shortcode_custom_css_class($css_animation, ' ');
        $result                     = '';
        if ($category_selected_id === '') {
            $result .= '<div id="wpfd-category-placeholder" class="wpfd-category-placeholder">';
            $result .= '<img class="category-icon" style="background: url('. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/category_widget_placeholder.svg') .') no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;" src="'. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif') .'" data-mce-src="'. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif') .'" data-mce-style="background: url('. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/category_widget_placeholder.svg') .') no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;">';
            $result .= '<span style="font-size: 13px; text-align: center;">' . __('Please select a WP File Download content to activate the preview', 'wpfd') . '</span>';
            $result .= '</div>';
        } else {
            $result = $this->wpfdWPBakeryCategoryShortcode($category_selected_id);
        }

        $output  = '';
        $output .= '<div class="wpfd-wpbakery-category ' . $category_class . ' ' . $css_animation_class . '" id="' . $category_id . '" >';
        $output .= $result;
        $output .= '</div>';

        return $output;
    }

    /**
     * WpfdWPBakeryInitCustomCategoryField
     *
     * @throws Exception Fire when errors
     *
     * @return void
     */
    public function wpfdWPBakeryInitCustomCategoryField()
    {
        $customCategoryPath             = WPFD_PLUGIN_DIR_PATH . '/app/includes/wpbakery/params/category/category.php';
        require_once $customCategoryPath;
        $wpbakeryshortcodeparamspath    = vc_path_dir('PARAMS_DIR', '/params.php');
        include_once $wpbakeryshortcodeparamspath;
        $shortcodeparams                = new WpbakeryShortcodeParams();
        global $vc_params_list;
        if (isset($vc_params_list)) {
            array_push($vc_params_list, 'wpfd_category');
        }

        if (isset($shortcodeparams)) {
            $name                   = 'wpfd_category';
            $form_field_callback    = 'vc_wpfd_category_form_field';
            $shortcodeparams->addField($name, $form_field_callback);
        }
    }

    /**
     * WpfdWPBakeryCategoryShortcode
     *
     * @param string|mixed $categoryId Category id
     *
     * @throws Exception Fire when errors
     *
     * @return string|mixed
     */
    public function wpfdWPBakeryCategoryShortcode($categoryId)
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
}

new WpfdWPBakeryCategory();

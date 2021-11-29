<?php
use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;

/**
 * Class WpfdWPBakeryFile
 */
class WpfdWPBakeryFile extends WPBakeryShortCode
{

    /**
     * WpfdWPBakeryFile construction
     */
    public function __construct()
    {
        add_action('init', array( $this, 'wpfdCreateShortcode' ), 999);
        add_shortcode('wpfd_file_shortcode', array( $this, 'wpfdRenderShortcode' ));
    }

    /**
     * WpfdCreateShortcode
     *
     * @return void
     */
    public function wpfdCreateShortcode()
    {

        $this->wpfdWPBakeryInitCustomFileField();

        vc_map(
            array(
                'name'          => __('WP File Download File', 'wpfd'),
                'base'          => 'wpfd_file_shortcode',
                'description'   => __('Single file design with pre-set icon builder', 'wpfd'),
                'category'      => __('JoomUnited', 'wpfd'),
                'icon'          => 'wpfd-file-icon',
                'params'        => array(

                    array(
                        'type'          => 'wpfd_file',
                        'class'         => 'wpfd-choose-file-control',
                        'holder'        => 'div',
                        'heading'       => __('Choose File', 'wpfd'),
                        'param_name'    => 'content',
                        'value'         => '<!-- wp:paragraph --><p>Hello! This is the Wp File Download File you can edit directly from the WPBakery Page Builder.</p><!-- /wp:paragraph -->',
                        'description'   => __('Select the WP File Download File that will be displayed on this page.', 'wpfd'),
                    ),

                    array(
                        'type'          => 'hidden',
                        'param_name'    => 'wpfd_file_random',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd')
                    ),

                    array(
                        'type'          => 'hidden',
                        'param_name'    => 'wpfd_file_related_category_id',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd')
                    ),

                    array(
                        'type'          => 'hidden',
                        'param_name'    => 'wpfd_file_id',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd')
                    ),

                    array(
                        'type'          => 'textfield',
                        'class'         => 'wpfd-file-title-control',
                        'heading'       => __('File Title: ', 'wpfd'),
                        'param_name'    => 'wpfd_file_title',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd'),
                        'description'   => __('The title of the selected file.', 'wpfd'),
                    ),

                    array(
                        'type'          => 'textfield',
                        'heading'       => __('Element ID', 'wpfd'),
                        'param_name'    => 'wpfd_file_extra_id',
                        // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings -- This is for set init
                        'value'         => __('', 'wpfd'),
                        'description'   => __('Enter element ID (Note: make sure it is unique and valid).', 'wpfd'),
                        'group'         => __('Extra', 'wpfd'),
                    ),

                    array(
                        'type'          => 'textfield',
                        'heading'       => __('Extra class name', 'wpfd'),
                        'param_name'    => 'wpfd_file_extra_class',
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
     * @param array|string|mixed $atts Attributes
     *
     * @throws Exception Fire when errors
     *
     * @return string|mixed
     */
    public function wpfdRenderShortcode($atts)
    {
        $atts = (shortcode_atts(array(
            'wpfd_file_related_category_id'     => '',
            'wpfd_file_id'                      => '',
            'wpfd_file_title'                   => '',
            'wpfd_file_extra_class'             => '',
            'wpfd_file_extra_id'                => '',
            'css'                               => ''
        ), $atts));

        $file_related_category_id   = esc_attr($atts['wpfd_file_related_category_id']);
        $file_selected_id           = esc_attr($atts['wpfd_file_id']);
        $file_extra_class           = esc_attr($atts['wpfd_file_extra_class']);
        $file_extra_id              = esc_attr($atts['wpfd_file_extra_id']);
        $css_animation              = esc_attr($atts['css']);
        $css_animation_class        = vc_shortcode_custom_css_class($css_animation, ' ');
        $result                     = '';
        if ($file_selected_id === '' && $file_related_category_id === '') {
            $result .= '<div id="wpfd-file-placeholder" class="wpfd-file-placeholder">';
            $result .= '<img class="file-icon" style="background: url('. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/file_widget_placeholder.svg') .') no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;" src="'. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif') .'" data-mce-src="'. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/t.gif') .'" data-mce-style="background: url('. esc_url(WPFD_PLUGIN_URL . 'app/admin/assets/images/file_widget_placeholder.svg') .') no-repeat scroll center center #fafafa; height: 200px; border-radius: 2px; width: 99%;">';
            $result .= '<span style="font-size: 13px; text-align: center;">' . __('Please select a WP File Download content to activate the preview', 'wpfd') . '</span>';
            $result .= '</div>';
        } else {
            $result  = $this->wpfdWPBakeryFileShortcode($file_selected_id, $file_related_category_id);
        }

        $output  = '';
        $output .= '<div class="wpfd-wpbakery-single-file ' . $file_extra_class . ' ' . $css_animation_class . '" id="' . $file_extra_id . '" >';
        $output .= $result;
        $output .= '</div>';

        return $output;
    }

    /**
     * WpfdWPBakeryInitCustomFileField
     *
     * @return void
     */
    public function wpfdWPBakeryInitCustomFileField()
    {
        $customFilePath             = WPFD_PLUGIN_DIR_PATH . '/app/includes/wpbakery/params/file/file.php';
        require_once $customFilePath;
        $shortcodeParamsPath        = vc_path_dir('PARAMS_DIR', '/params.php');
        include_once $shortcodeParamsPath;
        $shortcodeParams            = new WpbakeryShortcodeParams();
        global $vc_params_list;

        if (isset($vc_params_list)) {
            array_push($vc_params_list, 'wpfd_file');
        }

        if (isset($shortcodeParams)) {
            $shortcodeParams->addField('wpfd_file', 'vc_wpfd_file_form_field');
        }
    }

    /**
     * WpfdWPBakeryFileShortcode
     *
     * @param string|mixed $fileId     File id
     * @param string|mixed $categoryId Category id
     *
     * @throws Exception Fire when errors
     *
     * @return string|mixed
     */
    public function wpfdWPBakeryFileShortcode($fileId, $categoryId)
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
}

new WpfdWPBakeryFile();

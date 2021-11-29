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

/**
* Class diviFile
*
* @param mixed $slug Slug
* @param mixed $vb_support Support
*/
class diviFile extends ET_Builder_Module
{

    public $slug       = 'divi_wpfd_single_file';
    public $vb_support = 'on';

    /**
     * Init function
     *
     * @return void
     */
    public function init()
    {
        $this->name = esc_html__('WPFD Single File', 'wpfd');
    }

    /**
     * Advanced Fields Config
     *
     * @return array
     */
    public function get_advanced_fields_config()
    {
        return array(
            'button'       => false,
            'link_options' => false
        );
    }

    /**
     * Get Fields
     *
     * @return array
     */
    public function get_fields()
    {
        return array(
            'file_params' => array(
                'type'              => 'wpfd_file',
                'option_category'   => 'configuration',
                'default_on_front'  => 'root',
                'class'             => 'wpfd-file-module'
            )
        );
    }

    /**
     * Render Contents
     *
     * @param array|mixed $attrs Attributes
     * @param array|mixed $content Contents
     * @param array|mixed $render_slug Slug
     *
     * @return mixed|array
     */
    public function render($attrs, $content = null, $render_slug)
    {
        $fileParams = $this->props['file_params'];
        if ($fileParams === 'root') {
            $placeHolder = '<div class="divi-file-container"><div id="divi-single-file-placeholder" class="divi-single-file-placeholder">';
            $placeHolder.= '<span class="file-message">Please select a WP File Download content to activate the preview</span>';
            $placeHolder.= '</div></div>';
            return $placeHolder;
        } else {
            $fileParams = json_decode($fileParams);
            return do_shortcode('[wpfd_single_file id="'. $fileParams->selected_file_id .'" catid="'. $fileParams->selected_category_id .'" name="'. $fileParams->selected_file_name .'"]');
        }
    }
}

new diviFile;

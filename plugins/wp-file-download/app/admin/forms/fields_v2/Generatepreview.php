<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

namespace Joomunited\WP_File_Download\Admin\Fields;

use Joomunited\WPFramework\v1_0_5\Field;
use Joomunited\WPFramework\v1_0_5\Factory;
use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die();

/**
 * Class Switcher
 */
class Generatepreview extends Field
{
    /**
     * Get field
     *
     * @param array $field Field meta
     * @param array $data  Field data
     *
     * @return string
     */
    public function getfield($field, $data)
    {
        $attributes = $field['@attributes'];
        $html       = '<div class="ju-settings-option">';
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
        $tooltip    = isset($attributes['tooltip']) ? __($attributes['tooltip'], 'wpfd') : '';
        if (empty($attributes['hidden']) || (!empty($attributes['hidden']) && $attributes['hidden'] !== 'true')) {
            if (!empty($attributes['label']) && $attributes['label'] !== '' &&
                !empty($attributes['name']) && $attributes['name'] !== '') {
                $html .= '<label title="' . $tooltip . '" class="ju-setting-label" for="' . $attributes['name'] . '">';
                // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Dynamic translate
                $html .= esc_html__($attributes['label'], 'wpfd') . '</label>';
            }
        }
        // Switch
        $inputValue = 0;
        $html .= '<div class="ju-switch-button"><label class="switch">';
        $html .= '<input';
        $html .= ' type="checkbox"';

        if (!empty($attributes)) {
            $attribute_array = array('class', 'name', 'value');
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, $attribute_array) && isset($value)) {
                    if ($attribute === 'value') {
                        $inputValue = $value;
                        $html .= ' ' . $attribute . '="' . $value . '"';
                        if ((string) $value === '1') {
                            $html .= ' checked';
                        }
                    } elseif ($attribute === 'name') {
                        $html .= ' ' . $attribute . '="ref_' . $value . '"';
                    } else {
                        $html .= ' ' . $attribute . '="' . $value . '"';
                    }
                }
            }
        }
        $html .= ' />';

        $html .= '<span class="slider"></span>';
        $html .= '</label>';
        $val = ($inputValue === '' || (string) $inputValue === '0') ? '0' : '1';
        $html .= '<input type="hidden" id="' . $attributes['name'] . '" name="' . $attributes['name'] . '" value="' . $val . '" />';
        $html .= '</div>';
        $html .= $this->showGenerateButton($val);

        $html .= '</div>';

        return $html;
    }
    /**
     * Generate indexer button
     *
     * @param string $show Show indexer or not by default
     *
     * @return string
     */
    public function showGenerateButton($show)
    {
        $confirmText = esc_html__('You are about to launch a generate preview image of all your files. It requires that you let this tab open until the end of the process. Click OK to launch', 'wpfd');
        $style = !$show ? 'display:none' : '';
        $html = '<div class="wpfd-process-switcher generate_preview_wrapper" style="' . $style . '">';
        $html .= '<button ';
        $html .= 'data-confirm="' . $confirmText . '" ';
        $html .= 'id="wpfd_generate_preview" type="button" class="ju-button ju-material-button">';
        $html .= esc_html__('Generate preview', 'wpfd');
        $html .= '</button>';
        $html .= '<div class="wpfd_sub_control">';
        $errorTitle = esc_html__('Mainly due to file format not supported by the previewer or because the file size is over 10MB. but no worries, we\'ll try to use the Google previewer for those files instead', 'wpfd');
        $html .= '<label rel="ref_secure_preview_file" title="' . esc_html__('Your preview file will have the same access limitation as the downloadable file, meaning that if the file is under access limitation, non authorized users won\'t be able to access to the preview', 'wpfd') . '"><input type="checkbox" id="ref_secure_preview_file" rel="secure_preview_file"  onChange="jQuery(\'input[name=secure_preview_file]\').val(jQuery(this).is(\':checked\') ? 1 : 0)" />&nbsp;' . __('Secure generated file', 'wpfd') . '</label>';
        $html .= '<script>jQuery(document).ready(function() {jQuery(\'input[rel=secure_preview_file]\').prop(\'checked\', jQuery(\'input[name=secure_preview_file]\').val() === \'1\' ? true : false);})</script>';
        $html .= '<span id="wpfd_generate_error_message" title="' . $errorTitle . '"></span>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}

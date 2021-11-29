<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

namespace Joomunited\WP_File_Download\Admin\Fields;

use Joomunited\WPFramework\v1_0_5\Fields\Typeint;

defined('ABSPATH') || die();

/**
 * Class Singleshortcode
 */
class Singleshortcode extends Typeint
{
    /**
     * Render <input> tag
     *
     * @param array $field Fields
     * @param array $data  Data
     *
     * @return string
     */
    public function getfield($field, $data)
    {
        $attributes = $field['@attributes'];
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wpfd-security')) {
            wp_die(esc_html__('You don\'t have permission to perform this action!', 'wpfd'));
        }
        $fileInfo   = $_POST['fileInfo'][0];
        if (!empty($attributes['value'])) {
            $attributes['value'] = str_replace('\\', '', $attributes['value']);
        }
        $html = '<div class="ju-settings-option">';
        if (isset($attributes['fullwidth']) && !empty($attributes['fullwidth'])) {
            $html = '<div class="ju-settings-option full-width">';
        }
        $html .= '<div class="ju-settings-toolbox">';
        if (!empty($attributes['help']) && $attributes['help'] !== '') {
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
            $html .= '<p class="help-block">' . __($attributes['help'], 'wpfd') . '</p>';
        }
        // Copy shortcode to clipboard
        $shortcodeName = (isset($attributes['name']) && !empty($attributes['name'])) ? $attributes['name'] : '';
        $html .= '<button type="button" class="ju-button orange-outline-button shortcode-copy" data-ref="';
        $html .= $shortcodeName;
        $html .= '"><i class="material-icons" data-ref="' . $shortcodeName . '">file_copy</i>';
        $html .= esc_html__('Copy', 'wpfd');
        $html .= '</button>';
        $html .= '</div>';
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
        $tooltip = isset($attributes['tooltip']) ? __($attributes['tooltip'], 'wpfd') : '';
        if (!empty($attributes['label']) && $attributes['label'] !== '' &&
            !empty($attributes['name']) && $attributes['name'] !== '') {
            $html .= '<label title="' . $tooltip . '" class="ju-setting-label" for="' . $attributes['name'] . '">';
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Dynamic translate
            $html .= esc_html__($attributes['label'], 'wpfd') . '</label>';
        }

        $fileName = str_replace('[', '&amp;#91;', $fileInfo['title']);
        $fileName = str_replace(']', '&amp;#93;', $fileName);

        $html .= "<input type='text' name='" . $attributes['name'] . "' id='" . $attributes['id'] . "' readonly='true' value='[wpfd_single_file id=\"";
        $html .= $fileInfo['fileId'] . '" catid="' . $fileInfo['catid'] . '" name="';
        $html .= $fileName . "\"]' class='ju-input' />";
        $html .= '<small>';
        $html .= esc_html__('Usage: Copy this shortcode then paste to where you want to display this file.', 'wpfd');
        $html .= '</small>';
        $html .= '</div>';

        return $html;
    }
}

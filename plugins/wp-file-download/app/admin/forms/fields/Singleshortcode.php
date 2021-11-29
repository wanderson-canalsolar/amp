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
        $html       = '';
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
        $tooltip    = isset($attributes['tooltip']) ? __($attributes['tooltip'], 'wpfd') : '';
        $html       .= '<div class="control-group">';
        if (!empty($attributes['label']) && $attributes['label'] !== '' &&
            !empty($attributes['name']) && $attributes['name'] !== '') {
            $html .= '<label title="' . $tooltip . '" class="control-label" for="' . $attributes['name'] . '">';
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Dynamic translate
            $html .= esc_html__($attributes['label'], 'wpfd') . '</label>';
        }
        $html .= '<div class="controls">';
        $html .= "<input type='text' id='singleshortcodecat' readonly='true' value='[wpfd_single_file id=\"";
        $html .= $fileInfo['fileId'] . '" catid ="' . $fileInfo['catid'] . '" name ="';
        $html .= $fileInfo['title'] . "\"]' class='inputbox input-block-level'>";
        $html .= '</div></div>';

        return $html;
    }
}

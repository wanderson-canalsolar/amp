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
 * Class Hits
 */
class Hits extends Typeint
{

    /**
     *  Render <input> tag
     *
     * @param array $field Fields
     * @param array $data  Data
     *
     * @return string
     */
    public function getfield($field, $data)
    {
        $attributes = $field['@attributes'];
        $html       = '<div class="ju-settings-option">';

        $html .= '<div class="ju-settings-toolbox">';
        $html .= '<button type="button" class="ju-button orange-outline-button" onclick="jQuery(\'#' . $attributes['id'] . '\').val(0);";" >';
        $html .= '<i class="material-icons">autorenew</i>';
        $html .= esc_html__('Reset', 'wpfd') . '</button>';
        $html .= '</div>';

        $html .= '<label title="" class="ju-setting-label" for="hits">' . esc_html__('Hits', 'wpfd') . '</label>';

        if (!empty($attributes['value'])) {
            $attributes['value'] = (int) $attributes['value'];
        } else {
            $attributes['value'] = 0;
        }

        $html .= '<input type="text" readonly="true" ';
        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, array('id', 'class', 'name', 'value', 'size')) && isset($value)) {
                    $html .= ' ' . $attribute . '="' . $value . '"';
                }
            }
        }
        $html .= ' />';

        $html .= '</div>';


        return $html;
    }
}

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
 * Class Radio2
 */
class Radio2 extends Field
{
    /**
     * Display radio
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
        $cleanfield = $field;
        unset($cleanfield['@attributes']);
        if (!empty($cleanfield[0])) {
            $attributes_input = array('type', 'id', 'class', 'name', 'onchange', 'value');
            $html .= '<div class="ju-radio-group">';
            foreach ($cleanfield[0] as $child) {
                if (!empty($child['option']['@attributes'])) {
                    $html .= '<label>';
                    $html .= '<input ';
                    foreach ($child['option']['@attributes'] as $childAttribute => $childValue) {
                        if (in_array($childAttribute, $attributes_input) && isset($childValue)) {
                            $html .= ' ' . $childAttribute . '="' . $childValue . '"';
                            if (($childAttribute === 'value' && isset($attributes['value'])) &&
                                $attributes['value'] === $childValue) {
                                $html .= ' checked ';
                            }
                        }
                    }
                    $html .= '>';
                    // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Dynamic translate
                    $html .= '<span>' . esc_html__($child['option'][0], 'wpfd') . '</span>';
                    $html .= '</input>';
                    $html .= '</label>';
                }
            }
        }

        $html .= '</div>';
        if (!empty($attributes['help']) && $attributes['help'] !== '') {
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
            $html .= '<div class="ju-settings-help">' . __($attributes['help'], 'wpfd') . '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}

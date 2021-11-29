<?php
/**
 * WP Framework
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

namespace Joomunited\WP_File_Download\Admin\Fields;

use Joomunited\WPFramework\v1_0_5\Field;
use Joomunited\WPFramework\v1_0_5\Factory;

defined('ABSPATH') || die();

/**
 * Class Button
 */
class Button extends Field
{

    /**
     * Get the field
     *
     * @param array $field Field attributes
     * @param array $datas Full datas
     *
     * @return string
     */
    public function getfield($field, $datas)
    {
        $attributes = $field['@attributes'];
        $html = '<div class="control-group">';
        $html .= '<button';
        $content = '';
        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, array('id', 'class', 'name', 'value', 'type')) && !empty($value)) {
                    if ($attribute === 'value') {
                        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Dynamic translate
                        $content = esc_html_x($value, 'button-text', 'wpfd');
                        continue;
                    }
                    $html .= ' ' . $attribute . '="' . $value . '"';
                }
            }
        }
        $html .= '>' . $content . '</button>';
        $html .= '</div>';
        return $html;
    }
}

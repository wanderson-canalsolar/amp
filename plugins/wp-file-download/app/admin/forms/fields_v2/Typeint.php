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

defined('ABSPATH') || die();

/**
 * Class Typeint
 * phpcs:disable WordPress.Security.NonceVerification -- Nonce verification is made in the Form::validate method
 */
class Typeint extends Field
{

    /**
     * Validation regex
     *
     * @var string
     */
    protected $validate = '/[0-9]+/';

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
        $attributes = &$field['@attributes'];
        $attributes['value'] = (int)$attributes['value'];
        $attributes['type'] = 'text';

        $attributes = $field['@attributes'];
        $html = '<div class="ju-settings-option">';
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
        $tooltip    = isset($attributes['tooltip']) ? __($attributes['tooltip'], 'wpfd') : '';
        if (!empty($attributes['type']) || (!empty($attributes['hidden']) && $attributes['hidden'] !== 'true')) {
            if (!empty($attributes['label']) && $attributes['label'] !== '' && !empty($attributes['name']) && $attributes['name'] !== '') {
                // phpcs:ignore WordPress.WP.I18n -- Allow non literal arg
                $html .= '<label title="' . $tooltip . '" class="ju-setting-label" for="' . $attributes['name'] . '">';
                // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Dynamic translate
                $html .= esc_html__($attributes['label'], 'wpfd') . '</label>';
            }
        }
        if (empty($attributes['hidden']) || (!empty($attributes['hidden']) && $attributes['hidden'] !== 'true')) {
            $html .= '<input';
        } else {
            $html .= '<hidden';
        }

        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, array('type', 'id', 'class', 'placeholder', 'name', 'value')) && isset($value)) {
                    $html .= ' ' . $attribute . '="' . $value . '"';
                }
            }
        }
        $html .= ' />';
        if (!empty($attributes['help']) && $attributes['help'] !== '') {
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
            $html .= '<p class="help-block">' . __($attributes['help'], 'wpfd') . '</p>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Sanitize a value
     *
     * @param array $field Field attributes to sanitize
     *
     * @return integer
     */
    public function sanitize($field)
    {
        $value = null;
        if (!empty($_POST[$field['name']])) {
            $value = $_POST[$field['name']];
        } elseif (!empty($_GET[$field['name']])) {
            $value = $_GET[$field['name']];
        }
        return (int)$value;
    }
}

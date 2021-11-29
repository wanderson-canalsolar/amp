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
use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class FileMultiCategory
 */
class FileMultiCategory extends Field
{
    /**
     * Display files file multi category
     *
     * @param array $field Fields
     * @param array $data  Data
     *
     * @return string
     */
    public function getfield($field, $data)
    {
        $attributes = $field['@attributes'];
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
        $html            .= '<div class="controls">';
        $selectionValues = $this->getSelectionValues();
        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        $selection_value = (is_countable($selectionValues) && count($selectionValues)) ? $selectionValues : array();
        $select          = $attributes['value'];
        $idCategory      = null;
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wpfd-security')) {
            wp_die(esc_html__('You don\'t have permission to perform this action!', 'wpfd'));
        }
        if (isset($_POST['fileInfo'][0])) {
            if (isset($_POST['fileInfo'][0]['catid'])) {
                $idCategory = $_POST['fileInfo'][0]['catid'];
            }
        }
        $data_placeholder = 'data-placeholder="' . esc_html__('Additional categories', 'wpfd') . '" class="inputbox chosen ';
        $data_placeholder .= $attributes['class'] . '" multiple="true"';
        $html             .= '<div class="controls-multi-cat-button">';
        $html             .= wpfd_select($selection_value, $attributes['name'] . '[]', $select, $data_placeholder, $idCategory);
        $html             .= '</div></div></div>';

        return $html;
    }

    /**
     * Get selection values
     *
     * @return array
     */
    public function getSelectionValues()
    {
        $options  = array();
        $modelCat = Model::getInstance('categories');
        $cats     = $modelCat->getCategories();

        if ($cats) {
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            $total = is_countable($cats) ? count($cats) : 0;
            for ($index = 0; $index < $total; $index++) {
                if ($index + 1 !== $total) {
                    $nextlevel = $cats[$index + 1]->level;
                } else {
                    $nextlevel = 0;
                }
                $space_str = '';
                if ($nextlevel > $cats[$index]->level) {
                    if (($cats[$index]->level) > 0) {
                        $space_str = str_repeat('&nbsp&nbsp', $cats[$index]->level);
                    }
                } elseif ($nextlevel === $cats[$index]->level) {
                    $space_str = str_repeat('&nbsp&nbsp', $nextlevel);
                } else {
                    if (($cats[$index]->level) > 0) {
                        $space_str = str_repeat('&nbsp&nbsp', $cats[$index]->level);
                    }
                }
                $options[$cats[$index]->term_id] = $space_str . $cats[$index]->name;
            }
        }

        return $options;
    }
}

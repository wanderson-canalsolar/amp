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
 * Class Category
 */
class Category extends Field
{

    /**
     * Display all categories
     *
     * @param array $field Fields
     * @param array $data  Data
     *
     * @return string
     */
    public function getfield($field, $data)
    {
        $attributes          = $field['@attributes'];
        $attributes['value'] = (int) $attributes['value'];
        $html = '<div class="ju-settings-option">';
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
        $tooltip             = isset($attributes['tooltip']) ? __($attributes['tooltip'], 'wpfd') : '';
        if (!empty($attributes['label']) && $attributes['label'] !== '' &&
            !empty($attributes['name']) && $attributes['name'] !== '') {
            $html .= '<label title="' . $tooltip . '" class="ju-setting-label" for="' . $attributes['name'] . '">';
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Dynamic translate
            $html .= esc_html__($attributes['label'], 'wpfd') . '</label>';
        }
        $html .= $this->renderCategory($attributes);
        if (!empty($attributes['help']) && $attributes['help'] !== '') {
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
            $html .= '<p class="help-block">' . __($attributes['help'], 'wpfd') . '</p>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Render category
     *
     * @param array $att Attributes
     *
     * @return string
     */
    public function renderCategory($att)
    {
        $modelCat   = Model::getInstance('categories');
        $categories = $modelCat->getCategories();
        $content    = '';
        $content    .= '<select name = "' . $att['name'] . '" id = "' . $att['name'] . '" class="' . $att['class'] . '" >';
        $optionname = isset($att['optionname']) ? $att['optionname'] : '— Select —';
        $content    .= '<option value ="0">' . $optionname . '</option >';
        if (!empty($categories)) {
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            $catCount = is_countable($categories) ? count($categories) : 0;
            for ($index = 0; $index < $catCount; $index++) {
                $category = $categories[$index];
                $category = apply_filters('wpfd_level_category', $category);
                $category = apply_filters('wpfd_level_category_dropbox', $category);
                $category = apply_filters('wpfd_level_category_onedrive', $category);
                $category = apply_filters('wpfd_level_category_onedrive_business', $category);

                if ($index + 1 !== $catCount) {
                    $nextlevel = $categories[$index + 1]->level;
                } else {
                    $nextlevel = 0;
                }
                $space_str = '';
                if ($nextlevel > $category->level) {
                    if (($category->level) > 0) {
                        $space_str = str_repeat('-', $category->level);
                    }
                } elseif ($nextlevel === $category->level) {
                    $space_str = str_repeat('-', $nextlevel);
                } else {
                    if (($category->level) > 0) {
                        $space_str = str_repeat('-', $category->level);
                    }
                }
                if ($category->term_id === $att['value']) {
                    $content .= '<option selected ="selected" value = ' .
                                $category->term_id . '>' . $space_str . '' . $category->name . '</option >';
                } else {
                    $content .= '<option value = ' . $category->term_id . '> ' . $space_str . '';
                    $content .= $category->name . '</option >';
                }
            }
            $content .= '</select >';

            return $content;
        }
    }
}

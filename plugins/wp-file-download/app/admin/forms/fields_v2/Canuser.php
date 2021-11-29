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
 * Class Canuser
 */
class Canuser extends Field
{

    /**
     * Field display user
     *
     * @param array $field Fields
     * @param array $data  Data
     *
     * @return string
     */
    public function getfield($field, $data)
    {
        $attributes  = $field['@attributes'];
        Application::getInstance('Wpfd');
        $modelConfig = Model::getInstance('config');
        $config      = $modelConfig->getConfig();
        if ((int) $config['restrictfile'] === 0) {
            return '';
        }
        $canview = 0;
        if (isset($attributes['value']) && !empty($attributes['value'])) {
            $canview = explode(',', $attributes['value']);
        }
        $username = array();
        if ($canview) {
            foreach ($canview as $key => $value) {
                $user = get_userdata((int) $value);
                if ($user) {
                    $username[] = $user->display_name;
                }
            }
        }
        $html    = '';
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
        $tooltip = isset($attributes['tooltip']) ? __($attributes['tooltip'], 'wpfd') : '';
        $html    .= '<div class="ju-settings-option">';
        if (!empty($attributes['label']) && $attributes['label'] !== '' && !empty($attributes['name']) &&
            $attributes['name'] !== '') {
            $html .= '<label title="' . $tooltip . '" class="ju-setting-label" for="' . $attributes['name'] . '">';
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Dynamic translate
            $html .= esc_html__($attributes['label'], 'wpfd') . '</label>';
        }
        $url_user_display = admin_url() . 'admin.php?page=wpfd&task=user.display&noheader=true';
        $url_user_display .= '&fieldtype=field-user-input&listCanview=' . $attributes['value'];
        $url_user_display .= '&TB_iframe=true&height=400&width=800';
        $html             .= '<div class="field-user-wrapper">';
        $html             .= '<div class="input-append">
                    <textarea id="' . $attributes['name'] . '_select"  
                       placeholder="' . esc_html__('Select a User', 'wpfd') . '" 
                       readonly="" class="ju-input field-user-input-name file">' . implode(',', $username) . '
                     </textarea>
                    <a href="' . $url_user_display . '" role="button" class="thickbox btn button-select file" 
                        title="Select User"><span class="icon-user"></span>
                    </a>
                     <a class="btn user-clear file"><span class="icon-remove"></span></a>
                        </div>
                        <input type="hidden" id="' . $attributes['name'] . '_id" name="' . $attributes['name'] . '" 
                        value="' . $attributes['value'] . '" class="field-user-input file inputbox" data-onchange="">
                    </div>';
        if (!empty($attributes['help']) && $attributes['help'] !== '') {
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
            $html .= '<p class="help-block">' . __($attributes['help'], 'wpfd') . '</p>';
        }
        $html .= '</div>';

        return $html;
    }
}

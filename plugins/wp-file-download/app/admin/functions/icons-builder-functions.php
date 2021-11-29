<?php
/*
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version: 4.4
 */

defined('ABSPATH') || die();

/**
 * Load wpfd icons builder page assets
 *
 * @param string $hook Hook name
 *
 * @return void
 */
function wpfd_admin_icons_builder_load_assets($hook)
{
    if (strpos($hook, 'page_wpfd-icons-builder') === false) {
        return;
    }

    wp_register_script('wpfd-admin-ui-script-velocity', plugins_url('../assets/ui/js/velocity.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_enqueue_style('wpfd-google-icon', plugins_url('../assets/ui/fonts/material-icons.min.css', __FILE__));
    wp_enqueue_style('wpfd-admin-ui-style', plugins_url('../assets/ui/css/style.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-admin-ui-style-gritter', plugins_url('../assets/css/jquery.gritter.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-admin-ui-style-waves', plugins_url('../assets/ui/css/waves.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-admin-ui-style-icons-builder', plugins_url('../assets/ui/css/iconsbuilder.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-admin-ui-style-single-file', plugins_url('../assets/ui/css/singlefile.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-admin-ui-style-png', plugins_url('../assets/ui/css/png.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('jquery-qtip-style', plugins_url('../assets/ui/css/jquery.qtip.css', __FILE__), array(), WPFD_VERSION, false);
    wp_enqueue_style('jquery-customscroll', plugins_url('../assets/ui/css/jquery.mCustomScrollbar.min.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_code_editor(array( 'type' => 'text/css' ));

    wp_register_script('wpfd-admin-ui-script-waves', plugins_url('../assets/ui/js/waves.js', __FILE__), array(), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-tabs', plugins_url('../assets/ui/js/tabs.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-filedrop', plugins_url('../assets/js/jquery.filedrop.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-gritter', plugins_url('../assets/js/jquery.gritter.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script', plugins_url('../assets/ui/js/script.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-toastr', plugins_url('../assets/ui/js/toastr.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-rangeslider', plugins_url('../assets/ui/js/rangeslider.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-minicolors', plugins_url('../assets/js/jquery.minicolors.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-inlinesvg', plugins_url('../assets/ui/js/inlinesvg.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-iconset', plugins_url('../assets/ui/js/iconset.js', __FILE__), array('jquery', 'wpfd-admin-ui-script-rangeslider', 'wpfd-admin-ui-script-inlinesvg'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-handlebars', WPFD_PLUGIN_URL . 'app/site/assets/js/handlebars-v4.1.0.js', array(), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-single-file', plugins_url('../assets/ui/js/singlefile.js', __FILE__), array('jquery', 'wpfd-admin-ui-script-rangeslider', 'wpfd-admin-ui-script-handlebars'), WPFD_VERSION);
    wp_register_script('jquery-qtip', plugins_url('../assets/ui/js/jquery.qtip.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('jquery-customscroll', plugins_url('../assets/ui/js/jquery.mCustomScrollbar.min.js', __FILE__), array(), WPFD_VERSION);
    // Load fonts
    wp_enqueue_style('wpfd-admin-ui-font-nutiosans', plugins_url('../assets/ui/fonts/nutiosans.css', __FILE__));
    $scripts = array(
        'wpfd-admin-ui-script',
        'wpfd-admin-ui-toastr',
        'wpfd-admin-ui-script-filedrop',
        'wpfd-admin-ui-script-gritter',
        'wpfd-admin-ui-script-rangeslider',
        'wpfd-admin-ui-script-inlinesvg',
        'wpfd-admin-ui-script-minicolors',
        'wpfd-admin-ui-script-handlebars',
        'wpfd-admin-ui-script-iconset',
        'wpfd-admin-ui-script-single-file',
        'wpfd-admin-ui-script-png',
        'wpfd-admin-ui-script-velocity',
        'wpfd-admin-ui-script-tabs',
        'wpfd-admin-ui-script-waves',
        'jquery-qtip',
        'jquery-customscroll',
    );

    foreach ($scripts as $script) {
        wp_enqueue_script($script);
    }
}

add_action('admin_enqueue_scripts', 'wpfd_admin_icons_builder_load_assets', 10, 1);

add_action('wpfd_admin_ui_icons_builder_menu', 'wpfd_admin_ui_menu_logo', 10);
add_action('wpfd_admin_ui_icons_builder_menu', 'wpfd_admin_ui_menu_search', 20);
add_action('wpfd_admin_ui_icons_builder_menu', 'wpfd_admin_ui_icons_builder_menu_items', 30);

/**
 * Menu items
 *
 * @return array
 */
function wpfd_admin_ui_icons_builder_menu_get_items()
{
    $items = array(
        'iconssets' => array(esc_html__('Icons sets', 'wpfd'), 'iconssets', 10),
        'singlebutton' => array(esc_html__('Single file', 'wpfd'), 'singlebutton', 20),
    );
    $items = apply_filters('wpfd_admin_ui_icons_builder_menu_get_items', $items);

    // Sort menu by position
    uasort($items, function ($a, $b) {
        return $a[2] - $b[2];
    });
    return $items;
}

/**
 * Build left menu html
 *
 * @param null|array $items Menu items
 *
 * @return string
 */
function wpfd_admin_ui_icons_builder_menu_html($items = null)
{
    if (is_null($items)) {
        $items = wpfd_admin_ui_icons_builder_menu_get_items();
    }

    $html = '<ul class="tabs ju-menu-tabs">';
    foreach ($items as $key => $item) {
        $html .= '<li class="tab">';
        $html .= '<a href="#wpfd-' . $key . '" class="link-tab waves-effect waves-light ' . $key . '">';

        if (wpfd_admin_ui_icon_exists($key)) {
            $icon = plugins_url('app/admin/assets/ui/images/icon-' . $key . '.svg', WPFD_PLUGIN_FILE);
            $html .= '<img src="' . $icon . '" />&nbsp;';
        } elseif (isset($item[3])) {
            $html .= '<img src="' . esc_url($item[3]) . '" />&nbsp;';
        }
        $html .= $item[0];
        $html .= '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';

    return $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Print menu items
 *
 * @return void
 */
function wpfd_admin_ui_icons_builder_menu_items()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this need to print out menu struct
    echo wpfd_admin_ui_icons_builder_menu_html();
}

/**
 * Render slider
 *
 * @param string  $name  Input name
 * @param string  $label Input label
 * @param string  $unit  Unit
 * @param integer $value Default value
 * @param integer $min   Min value
 * @param integer $max   Max value
 * @param integer $step  Step
 *
 * @return void
 */
function wpfdRenderSlider($name, $label, $unit = 'px', $value = 0, $min = 0, $max = 100, $step = 1)
{
    if (!$value) {
        $value = '0';
    }
    $html = '<div class="ju-option-group ju-range-group" data-id="' . $name . '">';
    $html .= '<label for="' . esc_attr($name) . '">' . esc_html($label) . '</label>';
    $html .= '<input type="number" class="ju-input" data-ref="' . esc_attr($name) . '" value="' . esc_attr($value) . '" min="' . esc_attr($min) . '" max="' . esc_attr($max) . '" step="' . esc_attr($step) . '" data-rangeslider-number/>';
    $html .= '<span>' . esc_html($unit) . '</span>';
    $html .= '<input name="' . esc_attr($name) . '" type="range" value="' . esc_attr($value) . '" min="' . esc_attr($min) . '" max="' . esc_attr($max) . '" step="' . esc_attr($step) . '" data-rangeslider>';
    $html .= '</div>';

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Render spacing box
 *
 * Values must be array of ['top' => <number>, 'right' => <number>, 'bottom' => <number>, 'left => <number>]
 *
 * @param string  $name   Input name
 * @param string  $label  Input label
 * @param array   $values Default value
 * @param string  $unit   Unit
 * @param integer $min    Min value
 * @param integer $max    Max value
 *
 * @return void
 */
function wpfdRenderSpacingBox($name, $label, $values = array(), $unit = 'px', $min = 0, $max = 100)
{
    $values = array(
        'top' => isset($values[esc_attr($name) . '_top']) ? intval($values[esc_attr($name) . '_top']) : 0,
        'right' => isset($values[esc_attr($name) . '_right']) ? intval($values[esc_attr($name) . '_right']) : 0,
        'bottom' => isset($values[esc_attr($name) . '_bottom']) ? intval($values[esc_attr($name) . '_bottom']) : 0,
        'left' => isset($values[esc_attr($name) . '_left']) ? intval($values[esc_attr($name) . '_left']) : 0
    );

    $html = '<div class="ju-option-group ju-spacing-group" data-id="' . esc_attr($name) . '">';
    $html .= '<label class="wpfd-label" for="' . esc_attr($name) . '">' . esc_html($label) . '</label>';
    $html .= '<div class="ju-spacing-box">';
    $html .= '<div class="ju-spacing-item">';
    $html .= '<input class="ju-input" type="number" name="' . esc_attr($name) . '_top" value="' . $values['top'] . '" />';
    $html .= '<span>' . __('Top', 'wpfd') . '</span>';
    $html .= '</div>';
    $html .= '<div class="ju-spacing-item">';
    $html .= '<input class="ju-input" type="number" name="' . esc_attr($name) . '_right" value="' . $values['right'] . '" />';
    $html .= '<span>' . __('Right', 'wpfd') . '</span>';
    $html .= '</div>';
    $html .= '<div class="ju-spacing-item">';
    $html .= '<input class="ju-input" type="number" name="' . esc_attr($name) . '_bottom" value="' . $values['bottom'] . '" />';
    $html .= '<span>' . __('Bottom', 'wpfd') . '</span>';
    $html .= '</div>';
    $html .= '<div class="ju-spacing-item">';
    $html .= '<input class="ju-input" type="number" name="' . esc_attr($name) . '_left" value="' . $values['left'] . '" />';
    $html .= '<span>' . __('Left', 'wpfd') . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Render box shadow settings
 *
 * @param string $name   Setting name
 * @param string $label  Setting label
 * @param array  $values Values
 *
 * @return void
 */
function wpfdRenderBoxShadow($name, $label, $values = array())
{
    $values = array(
        'horizontal' => isset($values[esc_attr($name) . '_horizontal']) ? intval($values[esc_attr($name) . '_horizontal']) : 0,
        'vertical' => isset($values[esc_attr($name) . '_vertical']) ? intval($values[esc_attr($name) . '_vertical']) : 0,
        'blur' => isset($values[esc_attr($name) . '_blur']) ? intval($values[esc_attr($name) . '_blur']) : 0,
        'spread' => isset($values[esc_attr($name) . '_spread']) ? intval($values[esc_attr($name) . '_spread']) : 0,
        'color' => isset($values[esc_attr($name) . '_color']) ? $values[esc_attr($name) . '_color'] : '#000000',
    );

    $html = '<div class="ju-option-group ju-spacing-group ju-boxshadow" data-id="' . esc_attr($name) . '">';
    $html .= '<label class="wpfd-label" for="' . esc_attr($name) . '">' . esc_html($label) . '</label>';
    $html .= '<div class="ju-spacing-box">';
    $html .= '<div class="ju-spacing-item">';
    $html .= '<input class="ju-input" type="number" name="' . esc_attr($name) . '_horizontal" value="' . $values['horizontal'] . '" />';
    $html .= '<span>' . __('X', 'wpfd') . '</span>';
    $html .= '</div>';
    $html .= '<div class="ju-spacing-item">';
    $html .= '<input class="ju-input" type="number" name="' . esc_attr($name) . '_vertical" value="' . $values['vertical'] . '" />';
    $html .= '<span>' . __('Y', 'wpfd') . '</span>';
    $html .= '</div>';
    $html .= '<div class="ju-spacing-item">';
    $html .= '<input class="ju-input" type="number" name="' . esc_attr($name) . '_blur" value="' . $values['blur'] . '" />';
    $html .= '<span>' . __('Blur', 'wpfd') . '</span>';
    $html .= '</div>';
    $html .= '<div class="ju-spacing-item">';
    $html .= '<input class="ju-input" type="number" name="' . esc_attr($name) . '_spread" value="' . $values['spread'] . '" />';
    $html .= '<span>' . __('Spread', 'wpfd') . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="ju-shadow-color">';
    $html .= '<input class="ju-input minicolors minicolors-input" type="text" name="' . esc_attr($name) . '_color" value="' . $values['color'] . '" />';
    $html .= '<span>' . __('Color', 'wpfd') . '</span>';
    $html .= '</div>';
    $html .= '</div>';

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Render selectet box
 *
 * @param string $name            Input name
 * @param string $label           Input label
 * @param array  $options         Default value
 * @param string $currentSelected Selected value
 *
 * @return void
 */
function wpfdRenderSelectBox($name, $label, $options, $currentSelected = '')
{
    $html = '<div class="ju-option-group" data-id="' . esc_attr($name) . '">
                <label class="wpfd-label" for="' . esc_attr($name) . '">' . esc_html($label) . '</label>
                <select id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" class="ju-input">';
    if (!empty($options)) {
        foreach ($options as $key => $value) {
            $selected = '';
            if ($key === $currentSelected) {
                $selected = ' selected';
            }
            $html .= '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($value) . '</option>';
        }
    }
    $html .= '</select></div>';

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Render gradient switcher
 *
 * @param string $name    Option name
 * @param string $label   Option label
 * @param string $default Default
 *
 * @return void
 */
function wpfdRenderGradientSwitcher($name, $label, $default = 'gradient')
{
    $optionLeftName = esc_html__('Gradient', 'wpfd');
    $optionRightName = esc_html__('Solid', 'wpfd');
    $optionLeftHandle = 'gradient';
    $optionRightHandle = 'solid';
    $html = '<div class="ju-option-group" data-id="' . $name . '">
                <label class="wpfd-label" for="">' . $label . '</label>
                <div class="wpfd-switch wpfd-switch-blue">';
    $checked = $optionLeftHandle === $default ? ' checked="checked"' : '';
    $html .= '           <input data-wpfd-gradient type="radio" class="wpfd-switch-input" name="' . $name . '" value="' . $optionLeftHandle . '" id="' . $name . '_' . $optionLeftHandle . '"' . $checked . '>
                    <label for="' . $name . '_' . $optionLeftHandle . '" class="wpfd-switch-label wpfd-switch-label-off">' . $optionLeftName . '</label>';
    $checked = $optionRightHandle === $default ? ' checked="checked"' : '';
    $html .= '                <input data-wpfd-gradient type="radio" class="wpfd-switch-input" name="' . $name . '" value="' . $optionRightHandle . '" id="' . $name . '_' . $optionRightHandle . '"' . $checked . '>
                    <label for="' . $name . '_' . $optionRightHandle . '" class="wpfd-switch-label wpfd-switch-label-on">' . $optionRightName . '</label>
                    <span class="wpfd-switch-selection"></span>
                </div>
            </div>';

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Render Left Right switcher
 *
 * @param string $name    Option name
 * @param string $label   Option label
 * @param string $default Default
 *
 * @return void
 */
function wpfdRenderLeftRightSwitcher($name, $label, $default = 'left')
{
    $optionLeftName = esc_html__('Left', 'wpfd');
    $optionRightName = esc_html__('Right', 'wpfd');
    $optionLeftHandle = 'left';
    $optionRightHandle = 'right';
    $html = '<div class="ju-option-group" data-id="' . $name . '">
                <label class="wpfd-label" for="">' . $label . '</label>
                <div class="wpfd-switch wpfd-switch-blue">';
    $checked = $optionLeftHandle === $default ? ' checked="checked"' : '';
    $html .= '           <input data-wpfd-lr type="radio" class="wpfd-switch-input" name="' . $name . '" value="' . $optionLeftHandle . '" id="' . $name . '_' . $optionLeftHandle . '"' . $checked . '>
                    <label for="' . $name . '_' . $optionLeftHandle . '" class="wpfd-switch-label wpfd-switch-label-off">' . $optionLeftName . '</label>';
    $checked = $optionRightHandle === $default ? ' checked="checked"' : '';
    $html .= '                <input data-wpfd-lr type="radio" class="wpfd-switch-input" name="' . $name . '" value="' . $optionRightHandle . '" id="' . $name . '_' . $optionRightHandle . '"' . $checked . '>
                    <label for="' . $name . '_' . $optionRightHandle . '" class="wpfd-switch-label wpfd-switch-label-on">' . $optionRightName . '</label>
                    <span class="wpfd-switch-selection"></span>
                </div>
            </div>';

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Render color chooser
 *
 * @param string  $name        Input name
 * @param string  $default     Default color
 * @param string  $label       Input label
 * @param boolean $applyForAll Css property for this object
 * @param integer $checked     Default state for apply all checkbox
 *
 * @return void
 */
function wpfdRenderColor($name, $default = '#fff', $label = '', $applyForAll = false, $checked = 0)
{
    $html = '<div class="ju-option-group" data-id="' . $name . '">';
    if ($applyForAll) {
        if (intval($checked) !== 0) {
            $checked = ' checked="checked"';
        }
        $html .= '<div class="wpfd-toolbar"><input type="checkbox" name="' . $name . '-all" ' . $checked . '/> ' . esc_html__('Apply for all', 'wpfd') . '</div>';
    }
    $html .= '<label class="wpfd-label" for="' . esc_attr($name) . '">' . esc_html($label) . '</label>';
    $html .= '<input name="' . esc_attr($name) . '" value="' . $default . '" type="text" class="ju-input minicolors minicolors-input" />';
    $html .= '</div>';

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Render text input
 *
 * @param string $name        Input name
 * @param string $default     Default text
 * @param string $placeholder Placeholder text
 * @param string $label       Input label
 *
 * @return void
 */
function wpfdRenderText($name, $default = '', $placeholder = '', $label = '')
{
    $html = '<div class="ju-option-group" data-id="' . $name . '">';
    $html .= '<label class="wpfd-label" for="' . esc_attr($name) . '">' . esc_html($label) . '</label>';
    $html .= '<input name="' . esc_attr($name) . '" value="' . $default . '" placeholder="' . esc_attr($placeholder) . '" type="text" class="ju-input" />';
    $html .= '</div>';

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Render icons box
 *
 * @param string $value Default value
 *
 * @return void
 */
function wpfdRenderIconBox($value)
{
    $html = '<div class="svg-icon-selected">
                <div class="svg-icons-list-wrapper">
                    <i class="material-icons">expand_more</i>
                    <div class="svg-icons-chooser">
                        <ul class="svg-icons-list">';
    $html .= wpfdRenderIconsList(wpfdGetSvgIcons(), $value, 'svg');
    $html .= '                    </ul>
                    </div>
                    <input type="hidden" name="icon" value="' . $value . '"/>
                </div>
            </div>';

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK
}

/**
 * Get Svg icons
 *
 * @return array List of icon name
 */
function wpfdGetSvgIcons()
{
    $path = WPFD_PLUGIN_DIR_PATH . 'app/site/assets/icons/svgicons/svgs' . DIRECTORY_SEPARATOR;
    $files = glob($path . '*.[sS][vV][gG]'); // Function glob is case sensitive, even on Windows systems.
    $svgs = array();
    foreach ($files as $file) {
        $fileInfo = pathinfo($file);
        $fileName = $fileInfo['filename'];
        $fileUrl = wpfd_abs_path_to_url($file);
        $svgs[$fileName] = $fileUrl;
    }
    return $svgs;
}

/**
 * Render icons list.
 * Input array must be [id => url, id2 => url2]. Id is filename without [dot]extension part
 *
 * @param array  $icons Icon list
 * @param string $value Default value
 * @param string $type  Inline image type. Accept 'svg','png'. Default 'svg'
 *
 * @return string
 */
function wpfdRenderIconsList($icons, $value, $type = 'svg')
{
    if (!is_array($icons)) {
        return '';
    }
    $list = '';
    foreach ($icons as $filename => $url) {
        $selected = '';
        if (esc_attr($value) === esc_attr($filename)) {
            $selected = 'class="selected"';
        }
        $list .= '<li data-icon-name="' . esc_attr($filename) . '" ' . $selected . '><img class="wpfdsvg" data-src="' . esc_url($url) . '" width="24" height="24"/></li>';
    }

    return $list;
}

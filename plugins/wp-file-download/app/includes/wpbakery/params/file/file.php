<?php
defined('ABSPATH') || die();

/**
 * Wpfd file field param.
 *
 * @param string|array|mixed $settings Setting params
 * @param string|array|mixed $value    Field value
 *
 * @return string - html string.
 */
function vc_wpfd_file_form_field($settings, $value)
{
    $value  = htmlspecialchars($value);
    $result = '<div id="wpfd-wpbakery-choose-file-section" class="wpfd-wpbakery-choose-file-section">';
    $result .= '<a href="#wpfdwpbakerymodal" class="button wpfdwpbakeryfilelaunch" id="wpfdwpbakeryfilelaunch" title="WP File Download">';
    $result .= '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 28 28" style="width: 20px; height: 20px; vertical-align: top; display: inline-block; margin-top: 4px"><title>ICON REQ NEW </title><path class="cls-1" d="M24.82,6.57h-.63V5.2A.81.81,0,0,0,24,4.58a1.34,1.34,0,0,0-1-.26H12.75L11.42,2.7a.38.38,0,0,0-.29-.13H5.48A1.38,1.38,0,0,0,4.1,4V6.57H3.22a.89.89,0,0,0-.88.88V22.69a.88.88,0,0,0,.88.88h21.6a.87.87,0,0,0,.88-.88V7.45A.88.88,0,0,0,24.82,6.57ZM4.85,4a.63.63,0,0,1,.63-.63H11l1.34,1.62a.36.36,0,0,0,.29.14H23a2.13,2.13,0,0,1,.48,0,.36.36,0,0,1,0,.1V6.57H4.85ZM25,22.69a.13.13,0,0,1-.13.13H3.22a.13.13,0,0,1-.13-.13V7.45a.12.12,0,0,1,.13-.12h21.6a.12.12,0,0,1,.13.12ZM12.26,9H5A.38.38,0,1,0,5,9.8h7.28a.38.38,0,0,0,0-.76Zm-2,1.89H5a.38.38,0,1,0,0,.75h5.28a.38.38,0,0,0,0-.75Zm11.79,5.92-1.73,1.73v-5A.52.52,0,0,0,19.8,13a.52.52,0,0,0-.52.52v5l-1.73-1.73a.52.52,0,0,0-.73,0,.53.53,0,0,0,0,.74l2.61,2.61a.57.57,0,0,0,.18.12h0a.58.58,0,0,0,.19,0h0a.58.58,0,0,0,.19,0h0l.17-.11h0l2.62-2.61a.52.52,0,1,0-.74-.74Z"/></svg>';
    $result .= '<span class="title"> '. esc_html__('WP File Download', 'wpfd') .'</span>';
    $result .= '</a>';
    $result .= '<input name="' . $settings['param_name'] . '" class="wpb_vc_param_value wpfd_file-field vc_param-name-' . $settings['param_name'] . ' ' . $settings['type'] . '" type="hidden" value="' . $value . '"/>';
    $result .= '</div>';

    return $result;
}

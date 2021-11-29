<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdModelIconsBuilder
 */
class WpfdModelIconsBuilder extends Model
{
    /**
     * Return param
     *
     * @var integer
     */
    const UPDATED_ICON_PARAMS = 1;

    /**
     * Option prefix
     *
     * @var string
     */
    protected $optionPrefix = 'wpfd_icons_params_';

    /**
     * Single file param option name
     *
     * @var string
     */
    private $singeFileParamOptionName = 'wpfd_single_file_params';

    /**
     * Get all icon params
     *
     * @param string $set Icon set
     *
     * @return array
     */
    public function getParams($set = 'svg')
    {
        $defaultParams = $this->getDefaultIconParams();
        $params = get_option($this->optionPrefix . esc_attr($set), array());
        return array(
            'icons' => isset($params['icons']) ? $this->fillTransparent(array_merge($defaultParams['icons'], $params['icons'])) : $this->fillTransparent($defaultParams['icons'])
        );
    }

    /**
     * Fill empty color with transparent
     *
     * @param array $options Options
     *
     * @return array
     */
    public function fillTransparent($options)
    {
        if (is_array($options)) {
            foreach ($options as $key => &$value) {
                if (strpos($key, '-color') !== false ||strpos($key, '_solid') !== false || strpos($key, '_color') !== false || strpos($key, '_start') !== false || strpos($key, '_end') !== false) {
                    if ($value === '' || $value === 'transparent') {
                        $value = 'rgba(0,0,0,0)';
                    }
                }
            }
        }
        return $options;
    }
    /**
     * Restore default SVG Icon param
     *
     * @param string $set       Icon set
     * @param string $extension Extension to restore
     *
     * @return void
     */
    public function restoreDefaultSVGIconParam($set, $extension)
    {
        $defaultParam = $this->getDefaultSVGIconParam($extension);
        $this->saveIconParams($extension, $set, $defaultParam);
    }

    /**
     * Restore all SVG params
     *
     * @param string $set Icon set
     *
     * @return void
     */
    public function restoreAllSVGParams($set)
    {
        $defaultParams = $this->getDefaultIconParams();
        update_option($this->optionPrefix . esc_attr($set), $defaultParams);
    }

    /**
     * Get default svg icon param
     *
     * @param string $extension Extension
     *
     * @return boolean|string
     */
    public function getDefaultSVGIconParam($extension)
    {
        $defaultParams = $this->getDefaultIconParams();

        if (isset($defaultParams['icons']) && isset($defaultParams['icons']['wpfd-icon-' . esc_attr($extension)])) {
            return $defaultParams['icons']['wpfd-icon-' . esc_attr($extension)];
        }

        return false;
    }
    /**
     * Get single icon params
     *
     * @param string $set       Icon set
     * @param string $extension File extension
     *
     * @return boolean|array
     */
    public function getIconParams($set = 'svg', $extension = '')
    {
        $params = $this->getParams($set);
        if (isset($params['icons']['wpfd-icon-' . esc_attr($extension)])) {
            return $params['icons']['wpfd-icon-' . esc_attr($extension)];
        }
        return false;
    }

    /**
     * Save icon params
     *
     * @param string $extension  File extension
     * @param string $set        Icons set
     * @param array  $iconParams Icon params
     *
     * @return boolean|integer
     */
    public function saveIconParams($extension, $set, $iconParams = array())
    {
        if (!in_array($set, array('svg'))) {
            return false;
        }

        $params = $this->getParams($set);
        if (!empty($iconParams)) {
            if (isset($iconParams['current_icon_set'])) {
                unset($iconParams['current_icon_set']);
            }
            $params['icons']['wpfd-icon-' . esc_attr($extension)] = $iconParams;
        }

        $this->saveParams($set, $params);

        return self::UPDATED_ICON_PARAMS;
    }
    /**
     * Save params
     *
     * @param string $set    Icons set
     * @param array  $params Icon params
     *
     * @return boolean|integer
     */
    public function saveParams($set, $params = array())
    {
        if (!in_array($set, array('svg'))) {
            return false;
        }
        update_option($this->optionPrefix . esc_attr($set), $params);
    }
    /**
     * Generate default options for single file button options
     *
     * @return array
     */
    public function getSingleButtonOptionsParams()
    {
        $defaultOptions = array(
            'file_size' => array(
                'name' => esc_html__('File size', 'wpfd'),
                'value' => 1
            ),
            'file_created_date' => array(
                'name' => esc_html__('File created date', 'wpfd'),
                'value' => 1
            ),
            'file_update_date' => array(
                'name' => esc_html__('File update date', 'wpfd'),
                'value' => 1
            ),
            'file_download_hit' => array(
                'name' => esc_html__('File download hit', 'wpfd'),
                'value' => 1
            ),
            'file_version' => array(
                'name' => esc_html__('File version', 'wpfd'),
                'value' => 1
            ),
//            'download_button' => array(
//                'name' => esc_html__('Download button', 'wpfd'),
//                'value' => 1
//            ),
//            'preview_button' => array(
//                'name' => esc_html__('Preview button', 'wpfd'),
//                'value' => 1
//            ),
        );

        return $defaultOptions;
    }

    /**
     * Get default single button params
     *
     * @return array
     */
    public function getDefaultSingleButtonParams()
    {
        return array (
            // Icon configuration
            'icon' => true,
            'link_on_icon' => 'preview',
            'base_icon_set' => 'svg',
            'icon_size' => 144,

            'icon_margin_top' => 0,
            'icon_margin_right' => 0,
            'icon_margin_bottom' => 0,
            'icon_margin_left' => 0,
            // File title
            'file_title' => true,
            'title_font_size' => 24,

            'title_margin_top' => 0,
            'title_margin_right' => 0,
            'title_margin_bottom' => 15,
            'title_margin_left' => 0,

            'title_padding_top' => 0,
            'title_padding_right' => 0,
            'title_padding_bottom' => 0,
            'title_padding_left' => 0,
            'title_wrapper_tag' => 'h3',
            // File description
            'file_description' => true,

            'description_font_size' => 15,

            'description_margin_top' => 0,
            'description_margin_right' => 0,
            'description_margin_bottom' => 10,
            'description_margin_left' => 0,

            'description_padding_top' => 0,
            'description_padding_right' => 0,
            'description_padding_bottom' => 0,
            'description_padding_left' => 0,
            // File Information
            'file_information' => true,
            'file_information_font_size' => 14,

            'file_information_margin_top' => 2,
            'file_information_margin_right' => 0,
            'file_information_margin_bottom' => 2,
            'file_information_margin_left' => 0,

            'file_information_padding_top' => 0,
            'file_information_padding_right' => 0,
            'file_information_padding_bottom' => 0,
            'file_information_padding_left' => 0,

            'file_size' => true,
            'file_created_date' => true,
            'file_update_date' => true,
            'file_download_hit' => true,
            'file_version' => true,
            // Download button
            'download_button' => true,
            'download_font_size' => 16,
            'download_width' => 170,

            'download_padding_top' => 5,
            'download_padding_right' => 5,
            'download_padding_bottom' => 5,
            'download_padding_left' => 5,

            'download_margin_top' => 15,
            'download_margin_right' => 15,
            'download_margin_bottom' => 15,
            'download_margin_left' => 0,

            'download_font_color' => '#ffffff',
            'download_background' => 'solid',
            'download_background_start' => '#5c5a57',
            'download_background_end' => '#5c5a57',
            'download_background_solid' => '#5c5a57',

            'download_hover_font_color' => '#ffffff',
            'download_hover_background' => 'solid',
            'download_hover_background_start' => '#6b6b6b',
            'download_hover_background_end' => '#6b6b6b',
            'download_hover_background_solid' => '#6b6b6b',

            'download_border_color' => '',
            'download_border_radius' => 4,
            'download_border_size' => 0,

            'download_boxshadow_horizontal' => 2,
            'download_boxshadow_vertical' => 2,
            'download_boxshadow_blur' => 12,
            'download_boxshadow_spread' => 5,
            'download_boxshadow_color' => '#f0f0f0',

            'download_icon_position' => 'left',
            'download_icon_spacing' => 10,

            'download_icon_active' => true,
            'download_icon' => 'download-icon4',
            'download_icon_color' => '#ffffff',
            'download_icon_size' => 38,

            // Preview button

            'preview_button' => true,
            'preview_font_size' => 16,
            'preview_width' => 170,

            'preview_padding_top' => 5,
            'preview_padding_right' => 5,
            'preview_padding_bottom' => 5,
            'preview_padding_left' => 5,

            'preview_margin_top' => 15,
            'preview_margin_right' => 15,
            'preview_margin_bottom' => 15,
            'preview_margin_left' => 15,

            'preview_font_color' => '#ffffff',
            'preview_background' => 'solid',
            'preview_background_start' => '#a7a7a7',
            'preview_background_end' => '#a7a7a7',
            'preview_background_solid' => '#a7a7a7',

            'preview_hover_font_color' => '#ffffff',
            'preview_hover_background' => 'solid',
            'preview_hover_background_start' => '#595756',
            'preview_hover_background_end' => '#595756',
            'preview_hover_background_solid' => '#595756',

            'preview_border_color' => '#a7a7a7',
            'preview_border_radius' => 4,
            'preview_border_size' => 0,

            'preview_boxshadow_horizontal' => 0,
            'preview_boxshadow_vertical' => 0,
            'preview_boxshadow_blur' => 0,
            'preview_boxshadow_spread' => 0,
            'preview_boxshadow_color' => '#000000',

            'preview_icon_position' => 'left',
            'preview_icon_spacing' => 10,

            'preview_icon_active' => true,
            'preview_icon' => 'preview-icon4',
            'preview_icon_color' => '#ffffff',
            'preview_icon_size' => 38,

            'custom_css' => '',
        );
    }
    /**
     * Get single file params
     *
     * @return array
     */
    public function getSingleButtonParams()
    {
        $default = $this->getDefaultSingleButtonParams();

        $options = get_option($this->singeFileParamOptionName, $default);

        if (is_array($options)) {
            foreach ($options as $key => &$value) {
                if (strpos($key, '_solid') !== false || strpos($key, '_color') !== false || strpos($key, '_start') !== false || strpos($key, '_end') !== false) {
                    if ($value === '' || $value === 'transparent') {
                        $value = 'transparent';
                    }
                }
            }
        }
        return $options;
    }
    /**
     * Save single file params
     *
     * @param array $params Validated params
     *
     * @return boolean
     */
    public function saveSingleParams($params)
    {
        if (is_array($params) && is_countable($params) && count($params)) {
            return update_option($this->singeFileParamOptionName, $params); // If params not modify return False too
        }

        return false;
    }

    /**
     * Get default icon params
     *
     * @return array
     */
    public function getDefaultIconParams()
    {
        $icons = array(
            'wpfd-icon-7z' => array('icon' => 'wpfd-svg-icon-228', 'icon-text' => '7z', 'background-color' => '#859594'),
            'wpfd-icon-ace' => array('icon' => 'wpfd-svg-icon-249', 'icon-text' => 'ace', 'background-color' => '#B53538'),
            'wpfd-icon-bz2' => array('icon' => 'wpfd-svg-icon-81', 'icon-text' => 'bz2', 'background-color' => '#859594'),
            'wpfd-icon-dmg' => array('icon' => 'wpfd-svg-icon-50', 'icon-text' => 'dmg', 'background-color' => '#859594'),
            'wpfd-icon-gz' => array('icon' => 'wpfd-svg-icon-196', 'icon-text' => 'gz', 'background-color' => '#F6B701'),
            'wpfd-icon-rar' => array('icon' => 'wpfd-svg-icon-38', 'icon-text' => 'rar', 'background-color' => '#B5353A'),
            'wpfd-icon-tgz' => array('icon' => 'wpfd-svg-icon-37', 'icon-text' => 'tgz', 'background-color' => '#B5353A'),
            'wpfd-icon-zip' => array('icon' => 'wpfd-svg-icon-229', 'icon-text' => 'zip', 'background-color' => '#6AB86F'),
            'wpfd-icon-csv' => array('icon' => 'wpfd-svg-icon-45', 'icon-text' => 'csv', 'background-color' => '#464D6E'),
            'wpfd-icon-doc' => array('icon' => 'wpfd-svg-icon-53', 'icon-text' => 'doc', 'background-color' => '#004faf'),
            'wpfd-icon-docx' => array('icon' => 'wpfd-svg-icon-55', 'icon-text' => 'docx', 'background-color' => '#004faf'),
            'wpfd-icon-html' => array('icon' => 'wpfd-svg-icon-226', 'icon-text' => 'html', 'background-color' => '#B53538'),
            'wpfd-icon-key' => array('icon' => 'wpfd-svg-icon-97', 'icon-text' => 'key', 'background-color' => '#BAAFA9', 'font-size' => '44'),
            'wpfd-icon-keynote' => array('icon' => 'wpfd-svg-icon-101', 'icon-text' => 'keynote', 'background-color' => '#859594'),
            'wpfd-icon-odp' => array('icon' => 'wpfd-svg-icon-138', 'icon-text' => 'odp', 'background-color' => '#859594'),
            'wpfd-icon-ods' => array('icon' => 'wpfd-svg-icon-47', 'icon-text' => 'ods', 'background-color' => '#7E8BD0'),
            'wpfd-icon-odt' => array('icon' => 'wpfd-svg-icon-146', 'icon-text' => 'odt', 'background-color' => '#EF3C54'),
            'wpfd-icon-pages' => array('icon' => 'wpfd-svg-icon-157', 'icon-text' => 'pages', 'background-color' => '#EF3C54'),
            'wpfd-icon-pdf' => array('icon' => 'wpfd-svg-icon-159', 'icon-text' => 'pdf', 'background-color' => '#CB0606'),
            'wpfd-icon-pps' => array('icon' => 'wpfd-svg-icon-165', 'icon-text' => 'pps', 'background-color' => '#859594'),
            'wpfd-icon-ppt' => array('icon' => 'wpfd-svg-icon-169', 'icon-text' => 'ppt', 'background-color' => '#c43622'),
            'wpfd-icon-pptx' => array('icon' => 'wpfd-svg-icon-173', 'icon-text' => 'pptx', 'background-color' => '#c43622'),
            'wpfd-icon-rtf' => array('icon' => 'wpfd-svg-icon-186', 'icon-text' => 'rtf', 'background-color' => '#6AB86F'),
            'wpfd-icon-tex' => array('icon' => 'wpfd-svg-icon-193', 'icon-text' => 'tex', 'background-color' => '#4F73BA'),
            'wpfd-icon-txt' => array('icon' => 'wpfd-svg-icon-205', 'icon-text' => 'txt', 'background-color' => '#90D396'),
            'wpfd-icon-xls' => array('icon' => 'wpfd-svg-icon-219', 'icon-text' => 'xls', 'background-color' => '#00743e'),
            'wpfd-icon-xlsx' => array('icon' => 'wpfd-svg-icon-223', 'icon-text' => 'xlsx', 'background-color' => '#00743e'),
            'wpfd-icon-xml' => array('icon' => 'wpfd-svg-icon-225', 'icon-text' => 'xml', 'background-color' => '#90D396'),
            'wpfd-icon-bmp' => array('icon' => 'wpfd-svg-icon-33', 'icon-text' => 'bmp', 'background-color' => '#BAAFA9'),
            'wpfd-icon-exif' => array('icon' => 'wpfd-svg-icon-58', 'icon-text' => 'exif', 'background-color' => '#7E8BD0'),
            'wpfd-icon-gif' => array('icon' => 'wpfd-svg-icon-78', 'icon-text' => 'gif', 'background-color' => '#4F73BA'),
            'wpfd-icon-ico' => array('icon' => 'wpfd-svg-icon-87', 'icon-text' => 'ico', 'background-color' => '#7E8BD0'),
            'wpfd-icon-jpeg' => array('icon' => 'wpfd-svg-icon-93', 'icon-text' => 'jpeg', 'background-color' => '#7E8BD0'),
            'wpfd-icon-jpg' => array('icon' => 'wpfd-svg-icon-96', 'icon-text' => 'jpg', 'background-color' => '#90D396'),
            'wpfd-icon-png' => array('icon' => 'wpfd-svg-icon-163', 'icon-text' => 'png', 'background-color' => '#6AB86F'),
            'wpfd-icon-psd' => array('icon' => 'wpfd-svg-icon-175', 'icon-text' => 'psd', 'background-color' => '#4F73BA'),
            'wpfd-icon-tif' => array('icon' => 'wpfd-svg-icon-202', 'icon-text' => 'tif', 'background-color' => '#464D6E'),
            'wpfd-icon-tiff' => array('icon' => 'wpfd-svg-icon-203', 'icon-text' => 'tiff', 'background-color' => '#B53538'),
            'wpfd-icon-aac' => array('icon' => 'wpfd-svg-icon-246', 'icon-text' => 'aac', 'background-color' => '#859594'),
            'wpfd-icon-aif' => array('icon' => 'wpfd-svg-icon-244', 'icon-text' => 'aif', 'background-color' => '#4F73BA'),
            'wpfd-icon-aiff' => array('icon' => 'wpfd-svg-icon-248', 'icon-text' => 'aiff', 'background-color' => '#859594'),
            'wpfd-icon-alac' => array('icon' => 'wpfd-svg-icon-249', 'icon-text' => 'alac', 'background-color' => '#B53538'),
            'wpfd-icon-amr' => array('icon' => 'wpfd-svg-icon-21', 'icon-text' => 'amr', 'background-color' => '#6AB86F'),
            'wpfd-icon-au' => array('icon' => 'wpfd-svg-icon-61', 'icon-text' => 'au', 'background-color' => '#6AB86F'),
            'wpfd-icon-cdda' => array('icon' => 'wpfd-svg-icon-41', 'icon-text' => 'cdda', 'background-color' => '#4F73BA'),
            'wpfd-icon-flac' => array('icon' => 'wpfd-svg-icon-70', 'icon-text' => 'flac', 'background-color' => '#BAAFA9'),
            'wpfd-icon-m3u' => array('icon' => 'wpfd-svg-icon-104', 'icon-text' => 'm3u', 'background-color' => '#B53538'),
            'wpfd-icon-m4a' => array('icon' => 'wpfd-svg-icon-107', 'icon-text' => 'm4a', 'background-color' => '#B5353A'),
            'wpfd-icon-m4p' => array('icon' => 'wpfd-svg-icon-110', 'icon-text' => 'm4p', 'background-color' => '#B5353A'),
            'wpfd-icon-mid' => array('icon' => 'wpfd-svg-icon-117', 'icon-text' => 'mid', 'background-color' => '#4F73BA'),
            'wpfd-icon-mp3' => array('icon' => 'wpfd-svg-icon-135', 'icon-text' => 'mp3', 'background-color' => '#464D6E'),
            'wpfd-icon-mp4' => array('icon' => 'wpfd-svg-icon-113', 'icon-text' => 'mp4', 'background-color' => '#859594'),
            'wpfd-icon-mpa' => array('icon' => 'wpfd-svg-icon-125', 'icon-text' => 'mpa', 'background-color' => '#B5353A'),
            'wpfd-icon-ogg' => array('icon' => 'wpfd-svg-icon-148', 'icon-text' => 'ogg', 'background-color' => '#F6B701'),
            'wpfd-icon-pac' => array('icon' => 'wpfd-svg-icon-154', 'icon-text' => 'pac', 'background-color' => '#464D6E'),
            'wpfd-icon-ra' => array('icon' => 'wpfd-svg-icon-155', 'icon-text' => 'ra', 'background-color' => '#BAAFA9'),
            'wpfd-icon-wav' => array('icon' => 'wpfd-svg-icon-211', 'icon-text' => 'wav', 'background-color' => '#6AB86F'),
            'wpfd-icon-wma' => array('icon' => 'wpfd-svg-icon-213', 'icon-text' => 'wma', 'background-color' => '#4F73BA'),
            'wpfd-icon-3gp' => array('icon' => 'wpfd-svg-icon-233', 'icon-text' => '3gp', 'background-color' => '#859594'),
            'wpfd-icon-asf' => array('icon' => 'wpfd-svg-icon-24', 'icon-text' => 'asf', 'background-color' => '#4F73BA'),
            'wpfd-icon-avi' => array('icon' => 'wpfd-svg-icon-189', 'icon-text' => 'avi', 'background-color' => '#859594'),
            'wpfd-icon-flv' => array('icon' => 'wpfd-svg-icon-73', 'icon-text' => 'flv', 'background-color' => '#4F73BA'),
            'wpfd-icon-m4v' => array('icon' => 'wpfd-svg-icon-112', 'icon-text' => 'm4v', 'background-color' => '#F6B701'),
            'wpfd-icon-mkv' => array('icon' => 'wpfd-svg-icon-120', 'icon-text' => 'mkv', 'background-color' => '#7E8BD0'),
            'wpfd-icon-mov' => array('icon' => 'wpfd-svg-icon-123', 'icon-text' => 'mov', 'background-color' => '#464D6E'),
            'wpfd-icon-mpeg' => array('icon' => 'wpfd-svg-icon-127', 'icon-text' => 'mpeg', 'background-color' => '#4F73BA'),
            'wpfd-icon-mpg' => array('icon' => 'wpfd-svg-icon-131', 'icon-text' => 'mpg', 'background-color' => '#4F73BA'),
            'wpfd-icon-rm' => array('icon' => 'wpfd-svg-icon-184', 'icon-text' => 'rm', 'background-color' => '#F6B701'),
            'wpfd-icon-swf' => array('icon' => 'wpfd-svg-icon-191', 'icon-text' => 'swf', 'background-color' => '#464D6E'),
            'wpfd-icon-vob' => array('icon' => 'wpfd-svg-icon-209', 'icon-text' => 'vob', 'background-color' => '#BAAFA9'),
            'wpfd-icon-wmv' => array('icon' => 'wpfd-svg-icon-217', 'icon-text' => 'wmv', 'background-color' => '#464D6E'),
            'wpfd-icon-css' => array('icon' => 'wpfd-svg-icon-43', 'icon-text' => 'css', 'background-color' => '#EF3C54'),
            'wpfd-icon-img' => array('icon' => 'wpfd-svg-icon-88', 'icon-text' => 'img', 'background-color' => '#EF3C54'),
        );
        $sharedParams = array(
            'icon-active' => '1',
            'icon-size' => '160',
            'frame-active' => '1',
            'extension-name-active' => '1',
            'wrapper-active' => '1',
            'text-color' => '#ffffff',
            'icon-color' => '#ffffff',
            'frame-color' => '#ffffff',
            'border-radius' => '4',
            'border-color' => '',
            'border-size' => '0',
            'vertical-position' => '0',
            'blur-radius' => '0',
            'spread-radius' => '0',
            'horizontal-position' => '0',
            'shadow-color' => 'transparent',
            'svg-frame' => '2',
            'frame-width' => '240',
            'frame-stroke' => '4',
            'font-family' => 'arial',
            'font-size' => '60',
        );

        foreach ($icons as &$icon) {
            $icon = array_merge($icon, $sharedParams);
        }

        return array(
            'icons' => $icons
        );
    }
}

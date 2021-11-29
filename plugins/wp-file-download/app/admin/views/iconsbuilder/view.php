<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 4.6.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdViewStatistics
 */
class WpfdViewIconsBuilder extends View
{
    /**
     * Render view
     *
     * @param null $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {
        // Load settings
        Application::getInstance('Wpfd');
        /* @var WpfdModelConfig $configModel */
        $configModel = Model::getInstance('config');
        /* @var WpfdModelIconsBuilder $iconModel */
        $iconModel = Model::getInstance('iconsbuilder');
        $svgParams = $iconModel->getParams('svg');
//        $svg2Params = $iconModel->getParams('svg2');

        $svgIconParams = reset($svgParams['icons']);
//        $svg2IconParams = reset($svg2Params['icons']);

        $config      = $configModel->getConfig();
        $currentIconSet = isset($config['icon_set']) ? $config['icon_set'] : 'default';
        // Set a default if the list is empty
        $defaultExtensions = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,'
            . 'ppt,pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,'
            . 'aiff,alac,amr,au,cdda,flac,m3u,m4a,m4p,mid,mp3,mp4,mpa,ogg,pac,ra,wav,wma,3gp,asf,avi,flv,m4v,'
            . 'mkv,mov,mpeg,mpg,rm,swf,vob,wmv,css,img';
        $defaultExtensionsArr = array_map('trim', explode(',', $defaultExtensions));

        if (!isset($config['allowedext']) || (isset($config['allowedext']) && $config['allowedext'] === '')) {
            $config['allowedext'] = $defaultExtensions;
        }

        $allowedExtensions = array_map('trim', explode(',', $config['allowedext']));

        // Made sure additional extension in last of list
        $additionalExtensionsSet = array_diff($allowedExtensions, $defaultExtensionsArr);
        $defaultExtensionsSet = array_diff($allowedExtensions, $additionalExtensionsSet);
        $allowedExtensions = array_replace($defaultExtensionsSet, $additionalExtensionsSet);

        // Load icons background for png set
        $extensions = array();
        $missingExtension = array();
        foreach ($allowedExtensions as $extension) {
            foreach (array('png', 'svg') as $type) {
                $icon = WpfdHelperFile::getIconUrls($extension, $type);
                if (false !== $icon) {
                    $extensions[$type][$extension] = $icon;
                    if ($type === 'svg' && isset($svgParams['icons']['wpfd-icon-' . $extension])) {
                        $customCss = '';
                        if (intval($svgParams['icons']['wpfd-icon-' . $extension]['wrapper-active']) === 1) {
                            $customCss = ' style="';
                            $customCss .= isset($svgParams['icons']['wpfd-icon-' . $extension]['border-radius']) && intval($svgParams['icons']['wpfd-icon-' . $extension]['border-radius']) > 0 ? 'border-radius: ' . $svgParams['icons']['wpfd-icon-' . $extension]['border-radius'] . '%;' : '';
                            $customCss .= 'box-shadow: ' . $svgParams['icons']['wpfd-icon-' . $extension]['horizontal-position'] . 'px ' . $svgParams['icons']['wpfd-icon-' . $extension]['vertical-position'] . 'px ' . $svgParams['icons']['wpfd-icon-' . $extension]['blur-radius'] . 'px ' . $svgParams['icons']['wpfd-icon-' . $extension]['spread-radius'] . 'px ' . $svgParams['icons']['wpfd-icon-' . $extension]['shadow-color'] . ';';
                            $customCss .= 'background-color: ' . $svgParams['icons']['wpfd-icon-' . $extension]['background-color'] . ';';
                            $customCss .= 'border: ' . $svgParams['icons']['wpfd-icon-' . $extension]['border-size'] . 'px solid ' . $svgParams['icons']['wpfd-icon-' . $extension]['border-color'] . ';';
                            $customCss .= '"';
                        }
                        $extensions[$type][$extension]['css'] = $customCss;
                    }
                } else {
                    if ($type === 'svg') {
                        // Copy a svg icon for missing extension
                        // Select a random ready icon
                        $extensionRand = array_rand($extensions[$type], 1);
                        $option = $iconModel->getIconParams($type, $extensionRand);
                        // Replace icon extension name
                        $sourceIconPath = WpfdHelperFile::getUploadedIconPath($extensionRand, $type);
                        $sourceIconContent = file_get_contents($sourceIconPath);
                        $sourceIconContent = str_replace('>' . $extensionRand . '<', '>' . $extension . '<', $sourceIconContent);
                        $option['icon-text'] = $extension;
                        // Save file
                        $savePath = WpfdHelperFile::getCustomIconPath($type);
                        $newIconPath = $savePath . $extension . '.' . preg_replace('/[0-9]+/', '', $type);
                        file_put_contents($newIconPath, $sourceIconContent);
                        // Save the settings
                        $iconModel->saveIconParams($extension, $type, $option);
                        $extensions[$type][$extension]['uploaded'] = wpfd_abs_path_to_url($newIconPath);
                        $extensions[$type][$extension]['default'] = '';
                    } else {
                        $missingExtension[$type][] = $extension;
                    }
                }
            }
        }

        $unknownPng = WpfdHelperFile::getIconUrls('unknown', 'png');

        $singleFileOptions = $iconModel->getSingleButtonOptionsParams();
        $singleFileParams = $iconModel->getSingleButtonParams();

        $pdfPngIcon = WpfdHelperFile::getIconUrls('pdf', 'png');
        $pdfSvgIcon = WpfdHelperFile::getIconUrls('pdf', 'svg');
        $pdfIcon = array(
            'png' => $pdfPngIcon['uploaded'] === '' ? $pdfPngIcon['default'] : $pdfPngIcon['uploaded'],
            'svg' => $pdfSvgIcon['uploaded'] === '' ? $pdfSvgIcon['default'] : $pdfSvgIcon['uploaded'],
        );
        $pdfIconParam = isset($svgParams['icons']['wpfd-icon-pdf']) ? $svgParams['icons']['wpfd-icon-pdf'] : null;


        $this->singlebutton = include WPFD_PLUGIN_DIR_PATH . 'app/admin/views/iconsbuilder/tpl/singlebutton.php';
        $this->iconssets = array(
            'svg' => array(
                'title'   => esc_html__('SET SVG', 'wpfd'),
                'content' => include WPFD_PLUGIN_DIR_PATH . 'app/admin/views/iconsbuilder/tpl/iconssets/svg.php'
            ),
            'png'  => array(
                'title'   => esc_html__('SET PNG', 'wpfd'),
                'content' => include WPFD_PLUGIN_DIR_PATH . 'app/admin/views/iconsbuilder/tpl/iconssets/png.php'
            ),
        );
        add_action('wpfd_admin_ui_icons_builder_content', array($this, 'buildConfigContents'), 10, 1);
        parent::render($tpl);
    }

    /**
     * Build config content
     *
     * @return void
     */
    public function buildConfigContents()
    {
        $html      = '';
        $menuItems = wpfd_admin_ui_icons_builder_menu_get_items();
        $success   = Utilities::getInput('msg', 'GET', 'string');
        $message   = '';

        if ($success === 'success') {
            $message = '<div class="save-message">';
            $message .= '<p>' . esc_html__('Saved successfully!', 'wpfd') . '</p>';
            $message .= '<a type="button" class="cancel-btn"></a>';
            $message .= '</div>';
        }
        foreach ($menuItems as $key => $item) {
            if (isset($item[1]) && trim($item[1]) !== '') {
                $multiHtml = '';
                $forms     = explode(',', $item[1]);
                $isTab     = false;
                foreach ($forms as $form) {
                    if (is_array($this->{$form})) {
                        $multiHtml .= wpfd_admin_ui_configuration_build_tabs($this->{$form}, $message);
                        $isTab     = true;
                    } else {
                        $multiHtml .= $this->{$form};
                    }
                }
                if ($isTab) {
                    $html .= wpfd_admin_ui_configuration_build_content($key, $multiHtml);
                } else {
                    $html .= wpfd_admin_ui_configuration_build_content($key, $multiHtml, $message);
                }
            }
        }
        // phpcs:ignore -- escaped
        echo $html;
    }
}

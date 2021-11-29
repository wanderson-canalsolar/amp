<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Controller;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerStatistics
 */
class WpfdControllerIconsbuilder extends Controller
{
    /**
     * Allow icon set for upload
     *
     * @var array
     */
    protected $allowIconSets = array('png', 'svg');

    /**
     * File extension allow to upload
     *
     * @var array
     */
    protected $whiteListExt = array('png', 'svg');

    /**
     * File mime type allow to upload
     *
     * @var array
     */
    protected $whiteListType = array('png' => 'image/png', 'svg' => 'image/svg+xml', 'zip' => 'application/zip', 'jpg' => 'image/jpg', 'jpeg' => 'image/jpeg');

    /**
     * Svg icon viewbox
     *
     * @var integer
     */
    protected $svgViewBoxWidth = 400;

    /**
     * Svg icon size
     *
     * @var integer
     */
    protected $svgIconWidth = 400;

    /**
     * Upload new icon
     *
     * @return void
     */
    public function upload()
    {
        if (!current_user_can('manage_options')) {
            $this->exitStatus(false, array('message' => 'Not allow!'));
        }
        /**
         * Security check
         */
        if (empty($_FILES)) {
            $this->exitStatus(false, array('message' => 'Not allow!'));
        }

        if (!isset($_FILES['image'])) {
            $this->exitStatus(false, array('message' => 'Not allow!'));
        }

        // Check error
        if (isset($_FILES['image']['error']) && $_FILES['image']['error'] !== 0) {
            $this->exitStatus(false, array('message' => 'Not allow!'));
        }

        // Check upload type
        $uploadIconSet = Utilities::getInput('set', 'POST', 'string');
        if (!in_array($uploadIconSet, $this->allowIconSets)) {
            $this->exitStatus(false, array('message' => 'Not allow!'));
        }

        // Check real file upload
        if (!in_array($_FILES['image']['type'], array_values($this->whiteListType))) {
            $this->exitStatus(false, array('message' => 'Not allow!'));
        }
        $uploadedIconsPath = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR;
        $newIconPath = $uploadedIconsPath . $uploadIconSet . DIRECTORY_SEPARATOR;
        // Create and secure folder
        wpfdCreateSecureFolder($newIconPath);
        if (!file_exists($newIconPath)) {
            $this->exitStatus(false, array('message' => esc_html__('Upload failed! Destination folder not exits or not writeable!', 'wpfd')));
        }
        $allowedExtensions = $this->getAllowedExtensions();
        // Zip
//        if ($_FILES['image']['type'] === 'application/zip') {
//            $newZipFileName = md5(time());
//            $zipFilePath = $newIconPath . $newZipFileName . '.zip';
//            if (!move_uploaded_file($_FILES['image']['tmp_name'], $zipFilePath)) {
//                $this->exitStatus(false, array('message' => esc_html__('Can\'t move uploaded file', 'wpfd') . ' ' . $_FILES['image']['name']));
//            }
//            if (!class_exists('ZipArchive')) {
//                $this->exitStatus(false, array('message' => esc_html__('Class ZipArchive missing! Please install it first!', 'wpfd')));
//            }
//            $zip = new ZipArchive();
//            if ($zip->open($zipFilePath)) {
//                wpfdCreateSecureFolder($newIconPath . $newZipFileName);
//                $etractSuccess = $zip->extractTo($newIconPath . $newZipFileName);
//                $zip->close();
//                if (!$etractSuccess) {
//                    $this->exitStatus(false, array('message' => esc_html__('Can\'t extract ZIP file!', 'wpfd')));
//                }
//                // Check extracted files then move
//                $files = glob($newIconPath . $newZipFileName . DIRECTORY_SEPARATOR . '*.png');
//                $copiedUrl = array();
//                if (!empty($files) && count($files)) {
//                    foreach ($files as $file) {
//                        $fileName = basename($file, '.png');
//                        if (!in_array($fileName, $allowedExtensions)) {
//                            continue;
//                        }
//
//                        // Move file to png folder
//                        rename($file, $newIconPath . $fileName . '.png');
//                        $copiedUrl[$fileName] = wpfd_abs_path_to_url($newIconPath . $fileName . '.png');
//                    }
//                }
//                // Clean unzip folder and zip file
//                rrmdir($newIconPath . $newZipFileName);
//                unlink($newIconPath . $newZipFileName . '.zip');
//
//                $this->exitStatus(true, array(
//                    'message' => esc_html__('Upload success!', 'wpfd'),
//                    'urls' => $copiedUrl
//                ));
//            }
//
//            $this->exitStatus(false, array('message' => esc_html__('Upload failed!', 'wpfd')));
//        }

        // Png
        $iconExtensionName = Utilities::getInput('extension', 'POST', 'string');

        if ($iconExtensionName !== 'unknown' && !in_array($iconExtensionName, $allowedExtensions)) {
            $this->exitStatus(false, array('message' => esc_html__('Current extension is not allow to upload. Please add this extension to Allow extensions in Configuration!', 'wpfd')));
        }

        // Start upload
        if ($uploadIconSet === 'png') {
            $uploadIconExtension = 'png';
        } else {
            $uploadIconExtension = 'svg';
        }

        $newIconFileName = $newIconPath . $iconExtensionName . '.' . $uploadIconExtension;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $newIconFileName)) {
            $this->exitStatus(false, array('message' => esc_html__('Can\'t move uploaded file', 'wpfd') . ' ' . $_FILES['image']['name']));
        }
        // Check mime type
        if (!WpfdHelperFile::checkMimeType($newIconFileName)) {
            unlink($newIconFileName);
            $this->exitStatus(false, array('message' => esc_html__('The file type (mime type) is not valid', 'wpfd')));
        }
        WpfdHelperFile::renderCss();
        $this->exitStatus(true, array(
            'message' => esc_html__('Upload success!', 'wpfd'),
            'ext' => $iconExtensionName,
            'url' => wpfd_abs_path_to_url($newIconFileName)
        ));
    }

    /**
     * Restore default icon
     *
     * @return void
     */
    public function restore()
    {
        if (!current_user_can('manage_options')) {
            $this->exitStatus(false, array('message' => 'Not allow!'));
        }

        $iconExtensionName = Utilities::getInput('extension', 'POST', 'string');
        $uploadIconSet = Utilities::getInput('set', 'POST', 'string');

        if ($iconExtensionName !== 'unknown' && !in_array($iconExtensionName, $this->getAllowedExtensions())) {
            $this->exitStatus(false, array('message' => esc_html__('Current extension is not allow to restore. Please add this extension to Allow extensions in Configuration!', 'wpfd')));
        }

        if (!in_array($uploadIconSet, $this->allowIconSets)) {
            $this->exitStatus(false, array('message' => 'Not allow!'));
        }

        $uploadedIconsPath = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR;
        $newIconPath = $uploadedIconsPath . $uploadIconSet . DIRECTORY_SEPARATOR;

        if ($uploadIconSet === 'png') {
            $uploadIconExtension = 'png';
        } else {
            $uploadIconExtension = 'svg';
        }

        $newIconFileName = $newIconPath . $iconExtensionName . '.' . $uploadIconExtension;

        if (!file_exists($newIconFileName)) {
            $this->exitStatus(false, array('message' => esc_html__('This is default icon!', 'wpfd')));
        }
        // Send default one
        $defaultIcon = WPFD_PLUGIN_DIR_PATH . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR;
        $defaultIcon .= $uploadIconSet . DIRECTORY_SEPARATOR . $iconExtensionName . '.' . $uploadIconExtension;

        // If default icon not exists, don't delete
        if (strpos($uploadIconSet, 'svg') === 0) {
            $this->restoreSvgParam($uploadIconSet, $iconExtensionName);
            if (file_exists($defaultIcon)) {
                unlink($newIconFileName);
            } else {
                // You can not delete the default one in svg set
                $this->exitStatus(false, array('message' => esc_html__('This is default icon!', 'wpfd')));
            }
        } else {
            unlink($newIconFileName);
        }
        WpfdHelperFile::renderCss();
        if (!file_exists($defaultIcon)) {
            $this->exitStatus(true, array(
                'message' => esc_html__('Delete success!', 'wpfd'),
                'ext' => $iconExtensionName,
                'deleted' => true
            ));
        }

        $this->exitStatus(true, array(
            'message' => esc_html__('Restore success! Uploaded icon was deleted!', 'wpfd'),
            'ext' => $iconExtensionName,
            'url' => wpfd_abs_path_to_url($defaultIcon)
        ));
    }

    /**
     * AJAX: Apply styles for all icons
     *
     * @return boolean
     */
    public function applyall()
    {
        // Get current icon params
        $extension = Utilities::getInput('extension', 'POST', 'string');
        if ($extension === '') {
            return false;
        }
        /* @var WpfdModelIconsBuilder $model */
        $model = $this->getModel('iconsbuilder');
        $param = $model->getIconParams('svg', $extension);

        // Remove icon, text
        if (!is_array($param)) {
            return false;
        }
        if (isset($param['icon'])) {
            unset($param['icon']);
        }
        if (isset($param['icon-text'])) {
            unset($param['icon-text']);
        }
        // Apply for all other icons
        $params = $model->getParams('svg');
        if (!isset($params['icons'])) {
            return false;
        }
        $icons = $params['icons'];
        foreach ($icons as $key => $p) {
            $params['icons'][$key] = array_merge($p, $param);
        }

        // Save modified params
        $model->saveParams('svg', $params);
        // Rebuild Icons and Return
        $this->rebuildIcons('svg');
    }
    /**
     * AJAX: Not use
     *
     * @return void
     */
    public function restoreicons()
    {
        if (!current_user_can('manage_options')) {
            $this->exitStatus(false, array('message' => 'Not allow!'));
        }

        $set = Utilities::getInput('set', 'POST', 'string');

        // Restore all default params
        /* @var WpfdModelIconsBuilder $model */
        $model = $this->getModel('iconsbuilder');
        /* @var WpfdModelConfig $modelConfig */
        $modelConfig = $this->getModel('config');
        $extensions = $modelConfig->getAllowedExt();
        $model->restoreAllSVGParams($set);

        // Delete all generated icons
        $svgPath = WpfdHelperFile::getCustomIconPath($set);
        array_map('unlink', (glob($svgPath . '*.svg') ? glob($svgPath . '*.svg') : array()));
        $icons = array();
        foreach ($extensions as $ext) {
            $icon = WpfdHelperFile::getIconUrls($ext, 'svg');
            if (false !== $icon) {
                $icons[$ext] = $icon['default'];
            } else {
                // Copy a svg icon for missing extension
                // Select a random ready icon
                $extensionRand = array_rand($icons, 1);
                $option = $model->getIconParams($set, $extensionRand);
                // Replace icon extension name
                $sourceIconPath = WpfdHelperFile::getUploadedIconPath($extensionRand, $set);
                $sourceIconContent = file_get_contents($sourceIconPath);
                $sourceIconContent = str_replace('>' . $extensionRand . '<', '>' . $ext . '<', $sourceIconContent);
                $option['icon-text'] = $ext;
                // Save file
                $savePath = WpfdHelperFile::getCustomIconPath($set);
                $newIconPath = $savePath . $ext . '.' . preg_replace('/[0-9]+/', '', $set);
                file_put_contents($newIconPath, $sourceIconContent);
                // Save the settings
                $model->saveIconParams($ext, $set, $option);
                $icons[$ext] = wpfd_abs_path_to_url($newIconPath);
            }
        }
        WpfdHelperFile::renderCss();
        $result = array(
            'message' => esc_html__('Your icons restored!', 'wpfd')
        );
        if (is_array($icons) && count($icons) > 0) {
            $result['icons'] = $icons;
        }

//        $result['params'] = $model->getParams($set);

        $this->exitStatus(true, $result);
    }

    /**
     * Restore SVG params
     *
     * @param string $set       Icon set
     * @param string $extension Extension
     *
     * @return void
     */
    private function restoreSvgParam($set, $extension)
    {
        /* @var WpfdModelIconsBuilder $iconModel */
        $iconModel = $this->getModel('iconsbuilder');
        $iconModel->restoreDefaultSVGIconParam($set, $extension);
    }
    /**
     * Get allowed extensions from settings
     *
     * @return array
     */
    private function getAllowedExtensions()
    {
        // Load settings
        Application::getInstance('Wpfd');
        /* @var WpfdModelConfig $configModel */
        $configModel = Model::getInstance('config');
        $config      = $configModel->getConfig();

        // Set a default if the list is empty
        if (!isset($config['allowedext']) || (isset($config['allowedext']) && empty($config['allowdext']))) {
            $config['allowedext'] = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,'
                                    . 'ppt,pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,'
                                    . 'aiff,alac,amr,au,cdda,flac,m3u,m4a,m4p,mid,mp3,mp4,mpa,ogg,pac,ra,wav,wma,3gp,asf,avi,flv,m4v,'
                                    . 'mkv,mov,mpeg,mpg,rm,swf,vob,wmv,css,img';
        }

        $allowedExtensions = explode(',', $config['allowedext']);
        $allowedExtensions = array_map('trim', $allowedExtensions);

        return $allowedExtensions;
    }

    /**
     * Get main svg icon mockup
     *
     * @return string
     */
    private function getSvgMockup()
    {
        return '
        <svg class="preview-svg" width="300" height="300" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" version="1.1">
            <g id="iconBackground" transform="translate(0 0)">
                <rect width="400" height="400" fill="{background-color}" rx="0"/>
            </g>
            <g id="iconFrame" transform="translate(100 15)" style="color: {frame-color};stroke-width: {frame-stroke}px;">
                {frame-place-holder}
            </g>
            <g id="iconIcon" transform="translate(220 81)" style="color: {icon-color}">
                {icon-place-holder}
            </g>
            <g id="iconText" transform="translate(0 300)" style="color: {text-color};font-size: {font-size}px;font-family: {font-family};text-transform: uppercase;">
                <text id="iconText" x="200" y="0" fill="currentColor" dominant-baseline="middle" text-anchor="middle">{icon-text}</text>
            </g>
        </svg>
        ';
    }

    /**
     * Get Icon by file name
     *
     * @param string $iconName Icon extension name
     *
     * @return array|false|string
     */
    public function getIcons($iconName = '')
    {
        $iconsPath = WPFD_PLUGIN_DIR_PATH . 'app/site/assets/icons/svgicons/svgs/';
        $files = array();
        foreach (glob($iconsPath . '*.svg') as $filePath) {
            if (file_exists($filePath)) {
                $fileName = pathinfo($filePath, PATHINFO_FILENAME);
                if ($iconName !== '' && $iconName === $fileName) {
                    return file_get_contents($filePath);
                }

                $files[$fileName] = file_get_contents($filePath);
            }
        }

        return $files;
    }

    /**
     * Get frame svg by id
     *
     * @param integer $id Frame id
     *
     * @return array|boolean|mixed
     */
    private function getFrames($id = -1)
    {
        $frames = array(
            1 => '<svg width="45" height="45" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g transform="translate(74 240)"><rect width="240" height="100" rx="20" fill="none" stroke="currentColor" stroke-miterlimit="0" /></g></svg>',
            2 => '<svg width="45" height="45" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g transform="translate(74 44)"><rect id="frame-rect" width="240" height="300" rx="20" transform="translate(0.606)" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="5"></rect><line id="frame-line" x2="240" transform="translate(0 200)" fill="none" stroke="currentColor" stroke-linecap="butt" stroke-linejoin="round"></line></g></svg>',
            3 => '<svg width="45" height="45" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g transform="translate(74 44)" fill="none" stroke="currentColor"><path d="m20,0a20,20 0 0 0 -20,20l0,260a20,20 0 0 0 20,20l200,0a20,20 0 0 0 20,-20l0,-220l-60,-60zm0,0m160,0l60,60l-60,0l0,-60z" data-path-raw="m20,0a20,20 0 0 0 -20,20l0,260a20,20 0 0 0 20,20l{frame-bottom-width},0a20,20 0 0 0 20,-20l0,-220l-60,-60zm0,0m{frame-top-width},0l60,60l-60,0l0,-60z" stroke-linecap="round" stroke-linejoin="round"/><line id="frame-line" x2="240" transform="translate(0 200)" fill="none" stroke="currentColor" stroke-linecap="butt" stroke-linejoin="round"></line></g></svg>',
            4 => '<svg width="45" height="45" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g transform="translate(74 44)"><rect id="frame-rect-no-border" width="240" height="300" rx="0" transform="translate(0.606)" fill="none" stroke="currentColor" stroke-miterlimit="10"></rect><line id="frame-line-no-border" x2="240" transform="translate(0 200)" fill="none" stroke="currentColor" stroke-linecap="butt" stroke-linejoin="round"></line></g></svg>',
            5 => '<svg width="45" height="45" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g fill="none" stroke-width="5" stroke-dasharray="60"><circle cx="200" cy="200" r="140" fill="none" stroke="currentColor" /></g></svg>',
            6 => '<svg width="45" height="45" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g fill="none" stroke-width="5"><circle cx="200" cy="200" r="140" fill="none" stroke="currentColor"/></g></svg>',
            7 => '<svg width="45" height="45" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g transform="translate(60 240)" fill="none" stroke-width="5"><rect x="0" y="0" width="280" height="80" rx="40" fill="none" stroke="currentColor"/></g></svg>',
            8 => '<svg width="45" height="45" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g transform="translate(60 240)" fill="none" stroke="currentColor"><line x2="280" stroke-width="5"/></g></svg>',
        );
        if ($id === -1) {
            return $frames;
        }

        return isset($frames[$id]) ? $frames[$id] : false;
    }

    /**
     * AJAX: Load params
     *
     * @return void
     */
    public function load()
    {
        $set = Utilities::getInput('set', 'POST', 'string');
        $extension = Utilities::getInput('extension', 'POST', 'string');
        if (!in_array($set, array('svg'))) {
            $this->exitStatus(false, array('message' => esc_html__('Your icons set not exists!', 'wpfd')));
        }
        // Security
        if (strpos($set, '../')) {
            wp_exit(__('Good try!', 'wpfd'));
        }
        /* @var $model WpfdModelIconsBuilder */
        $model = $this->getModel();
        $params = $model->getIconParams($set, $extension);

        if (!$params) {
            $this->exitStatus(false, array('message' => esc_html__('Your icon not exists!', 'wpfd')));
        }

        $this->exitStatus(true, $params);
    }

    /**
     * AJAX: Save svg icon
     *
     * @return void
     */
    public function saveicon()
    {
        $extension = Utilities::getInput('extension', 'POST', 'string');
        $iconParams = Utilities::getInput('icon', 'POST', 'string');
        $iconParams = json_decode(base64_decode($iconParams), true);

        /* @var $model WpfdModelIconsBuilder */
        $model = $this->getModel();
        $saveResult = $model->saveIconParams($extension, 'svg', $iconParams);
        if (false === $saveResult) {
            $this->exitStatus(false, array('message' => esc_html__('Your icon params can not save!', 'wpfd')));
        }
        // Build icon
        $rawSvg = $this->renderSvg($iconParams);
        WpfdHelperFile::renderCss();
        if (!$rawSvg) {
            $this->exitStatus(false, array('message' => esc_html__('Your icon can not render!', 'wpfd')));
        }

        $icon = $this->saveSvg($rawSvg, 'svg', $extension);

        if (!$icon) {
            $this->exitStatus(false, array('message' => esc_html__('Your icon can not save!', 'wpfd')));
        }
        $result = array(
            'message' => esc_html__('Your icon saved!', 'wpfd'),
            'url' => wpfd_abs_path_to_url($icon)
        );

        $this->exitStatus(true, $result);
    }

    /**
     * AJAX: Save svg params
     *
     * @return void
     */
    public function save()
    {
        $extension = Utilities::getInput('extension', 'POST', 'string');
        $iconParams = Utilities::getInput('icon', 'POST', 'string');

        $iconParams = json_decode(base64_decode($iconParams), true);


        $set = isset($iconParams['current_icon_set']) ? $iconParams['current_icon_set'] : false;

        if (!in_array($set, array('svg'))) {
            $this->exitStatus(false, array('message' => esc_html__('Your icons set not exists!', 'wpfd')));
        }
        // Security
        if (strpos($set, '../')) {
            wp_exit(__('Good try!', 'wpfd'));
        }

        // Save params
        /* @var $model WpfdModelIconsBuilder */
        $model = $this->getModel();
        $saveResult = $model->saveIconParams($extension, $set, $iconParams);
        if (false === $saveResult) {
            $this->exitStatus(false, array('message' => esc_html__('Your icon params can not save!', 'wpfd')));
        }

        // Build icon
        $rawSvg = $this->renderSvg($iconParams);
        WpfdHelperFile::renderCss();
        if (!$rawSvg) {
            $this->exitStatus(false, array('message' => esc_html__('Your icon can not render!', 'wpfd')));
        }

        $icon = $this->saveSvg($rawSvg, $set, $extension);

        if (!$icon) {
            $this->exitStatus(false, array('message' => esc_html__('Your icon can not save!', 'wpfd')));
        }
        $result = array(
            'message' => esc_html__('Your icon saved!', 'wpfd'),
            'url' => wpfd_abs_path_to_url($icon)
        );


        $this->exitStatus(true, $result);
    }

    /**
     * AJAX: Get svg preview
     *
     * @return void
     */
    public function getSvgPreview()
    {
        $iconParams = Utilities::getInput('params', 'POST', 'string');
        $iconParams = json_decode(base64_decode($iconParams), true);
        // Build icon
        $rawSvg = $this->renderSvg($iconParams);
        if (!$rawSvg) {
            $this->exitStatus(false, array('message' => esc_html__('Your icon can not render!', 'wpfd')));
        }
        $result = array(
            'message' => esc_html__('Your icon saved!', 'wpfd'),
            'icon' => wpfd_abs_path_to_url($rawSvg)
        );


        $this->exitStatus(true, $result);
    }

    /**
     * Rebuild icon and css files
     *
     * @param string $set Icon set
     *
     * @return void
     */
    public function rebuildIcons($set = 'png')
    {
        if (!in_array($set, array('png', 'svg'))) {
            $this->exitStatus(false, array('message' => esc_html__('Your icons set not exists!', 'wpfd')));
        }
        // Security
        if (strpos($set, '../')) {
            wp_exit(__('Good try!', 'wpfd'));
        }

        $icons = $this->renderAllIcons($set);
        WpfdHelperFile::renderCss();
        $result = array(
            'message' => esc_html__('Your icon saved!', 'wpfd')
        );
        if (is_array($icons) && count($icons) > 0) {
            $result['icons'] = $icons;
        }

        $this->exitStatus(true, $result);
    }
    /**
     * Render all icons
     *
     * @param string $set Icon set
     *
     * @return array
     */
    public function renderAllIcons($set = 'svg')
    {
        /* @var $model WpfdModelIconsBuilder */
        $model = $this->getModel();
        $params = $model->getParams($set);

        $icons = $params['icons'];

        $outputIcons = array();
        foreach ($icons as $key => $param) {
            $svgRaw = $this->renderSvg($param);
            $extension = str_replace('wpfd-icon-', '', $key);
            $url = $this->saveSvg($svgRaw, $set, $extension);
            $url = wpfd_abs_path_to_url($url);
            $outputIcons[$extension] = $url;
        }

        return $outputIcons;
    }
    /**
     * Render svg raw
     *
     * @param array $params Icon params
     *
     * @return string
     */
    public function renderSvg($params)
    {
        if (!class_exists('Joomunited\WPFileDownload\simple_html_dom')) {
            require_once WPFD_PLUGIN_DIR_PATH . 'app/admin/classes/class.simplehtmldom.php';
        }
        $svg = $this->getSvgMockup();

        // Change background color on wrapper active
        if (intval($params['wrapper-active']) === 0) {
            $params['background-color'] = 'transparent';
            $params['border-radius'] = 0;
            $params['border-size'] = 0;
        }
        // Replace all svg mockup params
        foreach ($params as $key => $value) {
            $svg = str_replace('{' . $key . '}', $value, $svg);
        }
        /* @var \Joomunited\WPFileDownload\simple_html_dom $svgDom */
        $svgDom = Joomunited\WPFileDownload\str_get_html($svg);
        $frameId = $params['svg-frame'];
        $frameRaw = $this->getFrames($frameId);
        $frameWidth = isset($params['frame-width']) ? intval($params['frame-width']) : 240;
        $viewBox = $this->svgViewBoxWidth;
        $frameHeight = 300;

        // Render frame
        if (intval($params['frame-active']) !== 1 || intval($frameId) === 0) {
            $svgDom->find('#iconFrame', 0)->remove();
        } else {
            if (!$frameRaw) {
                return '';
            }

            $frameRaw = str_replace('width="45"', 'width="400"', $frameRaw);
            $frameRaw = str_replace('height="45"', 'height="400"', $frameRaw);
            $frameRaw = preg_replace('/ stroke-width="([0-9]+)"/', '', $frameRaw);
            // Change frame width

            /* @var \Joomunited\WPFileDownload\simple_html_dom $frameDom */
            $frameDom = Joomunited\WPFileDownload\str_get_html($frameRaw);

            if (is_countable($frameDom->find('path'))) {
                // phpcs:ignore PHPCompatibility.ControlStructures.NewForeachExpressionReferencing.Found -- It's OK
                foreach ($frameDom->find('path') as &$path) {
                    $rawPath = $path->{'data-path-raw'};
                    if (strpos($rawPath, 'frame-')) {
                        $top = $frameWidth - 80;
                        $bottom = $frameWidth - 40;
                        $rawPath = str_replace('{frame-bottom-width}', $bottom, $rawPath);
                        $rawPath = str_replace('{frame-top-width}', $top, $rawPath);
                        $path->d = $rawPath;
                    }
                }
            }

            if (is_countable($frameDom->find('line'))) {
                // phpcs:ignore PHPCompatibility.ControlStructures.NewForeachExpressionReferencing.Found -- It's OK
                foreach ($frameDom->find('line') as &$line) {
                    $line->x2 = $frameWidth;
                }
            }
            if (is_countable($frameDom->find('rect'))) {
                // phpcs:ignore PHPCompatibility.ControlStructures.NewForeachExpressionReferencing.Found -- It's OK
                foreach ($frameDom->find('rect') as &$rect) {
                    $rect->width = $frameWidth;
                }
            }
            if (is_countable($frameDom->find('circle'))) {
                // phpcs:ignore PHPCompatibility.ControlStructures.NewForeachExpressionReferencing.Found -- It's OK
                foreach ($frameDom->find('circle') as &$circle) {
                    $circle->r = $frameWidth/2;
                }
            }

            // Center frame
            $innerTransform = $frameDom->find('g', 0)->transform;
            $innerTransformX = 0;
            $innerTransformY = 0;
            if ($innerTransform) {
                $innerTransform = str_replace('translate(', '', $innerTransform);
                $innerTransform = str_replace(')', '', $innerTransform);
                $innerTransformAr = explode(' ', $innerTransform);
                $innerTransformX = isset($innerTransformAr[0]) ? intval($innerTransformAr[0]) : 0;
                $innerTransformY = isset($innerTransformAr[1]) ? intval($innerTransformAr[1]) : 0;
            }
            if (intval($frameId) === 1) {
                $innerTransformY = 40;
            }
            if (intval($frameId) === 8) {
                $innerTransformX = 60;
            }
            $translateX = ($viewBox - $frameWidth)/2  - $innerTransformX;
            $translateY = ($viewBox - $frameHeight)/2 - $innerTransformY;

            if (intval($frameId) === 7 || intval($frameId) === 8) {
                $translateY = 10;
            }
            if (intval($frameId) === 5 || intval($frameId) === 6) {
                $translateX = 0;
                $translateY = 0;
            }
            $svgDom->find('#iconFrame', 0)->transform = 'translate(' . $translateX . ' ' . $translateY . ')';
            $svgDom->find('#iconFrame', 0)->innertext = $frameDom;
        }

        // Render icon
        $iconIconWidth = isset($params['icon-size']) ? $params['icon-size'] : 160;
        $iconIconHeight = isset($params['icon-size']) ? $params['icon-size'] : 160;
        $iconName = $params['icon'];
        if ($iconName === '') {
            return '';
        }
        $iconRaw = $this->getIcons($iconName);
        if (empty($iconRaw)) {
            return '';
        }
        /* @var \Joomunited\WPFileDownload\simple_html_dom $iconDom */
        $iconDom = Joomunited\WPFileDownload\str_get_html($iconRaw);
        $g = $iconDom->find('g', 0);
        $innerTransformX = 0;
        $innerTransformY = 0;
        if (is_object($g) && property_exists($g, 'transform')) {
            $innerTransform = $iconDom->find('g', 0)->transform;
            if ($innerTransform) {
                $innerTransform = str_replace('translate(', '', $innerTransform);
                $innerTransform = str_replace(')', '', $innerTransform);
                $innerTransformAr = explode(' ', $innerTransform);
                $innerTransformX = isset($innerTransformAr[0]) ? intval($innerTransformAr[0]) : 0;
                $innerTransformY = isset($innerTransformAr[1]) ? intval($innerTransformAr[1]) : 0;
            }
        }

        $translateX = ($viewBox - $iconIconWidth)/2 - $innerTransformX;
        $translateY = ($viewBox - $iconIconHeight)/2 - $innerTransformY - 55; // Magic number
        $iconDom->find('svg', 0)->width = isset($params['icon-size']) ? $params['icon-size'] : 160;
        $iconDom->find('svg', 0)->height = isset($params['icon-size']) ? $params['icon-size'] : 160;
        $svgDom->find('#iconIcon', 0)->transform = 'translate(' . $translateX . ' ' . $translateY . ')';
        $svgDom->find('#iconIcon', 0)->innertext = $iconDom;

        // Box Shadow
        if (intval($params['wrapper-active']) === 1) {
            //$customCss = isset($params['border-radius']) && intval($params['border-radius']) > 0 ? 'border-radius: ' . $params['border-radius'] . '%;' : '';
            //$customCss .= 'box-shadow: ' . $params['horizontal-position'] . 'px ' . $params['vertical-position'] . 'px ' . $params['blur-radius'] . 'px ' . $params['spread-radius'] . 'px ' . $params['shadow-color'] . ';';
            $customCss = 'background-color: ' . $params['background-color'] . ';';
            //$customCss .= 'border: ' . $params['border-size'] . 'px solid ' . $params['border-color'] . ';';
            $svgDom->find('svg', 0)->style = $customCss;
        }
        if (intval($params['icon-active']) === 0) {
            $svgDom->find('#iconIcon', 0)->remove();
        }
        if (intval($params['extension-name-active']) === 0) {
            $svgDom->find('#iconText', 0)->remove();
        }
        return str_replace('viewbox', 'viewBox', $svgDom->save());
    }

    /**
     * Write svg icon to disk
     *
     * @param string $content Raw svg icon content
     * @param string $set     Icon set
     * @param string $ext     Icon extension
     *
     * @return boolean|string False on not saved. Return icon path.
     */
    private function saveSvg($content, $set, $ext)
    {
        $defaultIconsPath = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR;
        $iconPath = $defaultIconsPath . $set . DIRECTORY_SEPARATOR;
        // Create and secure folder
        wpfdCreateSecureFolder($iconPath);
        if (!file_exists($iconPath)) {
            mkdir($iconPath, '0755', true);
        }

        $iconPath .= $ext . '.svg';

        file_put_contents($iconPath, $content);

        if (file_exists($iconPath)) {
            return $iconPath;
        }

        return false;
    }

    /**
     * Save icon as png to disk
     *
     * Require Imagick extension installed
     *
     * @param string $content Raw svg icon content
     * @param string $set     Icon set
     * @param string $ext     Icon extension
     *
     * @return boolean|string
     *
     * @throws ImagickException Throw on Imagick not execution
     */
    private function savePng($content, $set, $ext)
    {
        if (!class_exists('Imagick')) {
            return false;
        }
        try {
            $imagick = new Imagick();
        } catch (ImagickException $e) {
            return false;
        }

        // Set transparent
        $imagick->setBackgroundColor(new ImagickPixel('transparent'));
        $imagick->readImageBlob($content);
        $imagick->setImageFormat('png24');
        $imagick->resizeImage(400, 400, imagick::FILTER_LANCZOS, 1);  // Optional, resize image to 400x400

        $defaultIconsPath = WPFD_PLUGIN_DIR_PATH . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'icons';
        $iconPath = $defaultIconsPath . DIRECTORY_SEPARATOR . $set . DIRECTORY_SEPARATOR;
        if (!file_exists($iconPath)) {
            mkdir($iconPath, '0755', true);
        }
        $iconPath .= $ext . '.png';
        $imagick->writeImage($iconPath);
        $imagick->clear();
        $imagick->destroy();

        if (file_exists($iconPath)) {
            return $iconPath;
        }

        return false;
    }

    /**
     * Save single file params and rebuild css
     *
     * @return void
     */
    public function saveSingleParams()
    {
        $params = Utilities::getInput('params', 'POST', 'none');
        // Corecting data
        $params = $this->validate($params);

        if (false === $params) {
            $this->exitStatus(false, array('message' => esc_html__('Can\'t save single file design params!', 'wpfd')));
        }
        // Save the data
        $model = $this->getModel('iconsbuilder');
        if (!$model->saveSingleParams($params)) {
            $this->exitStatus(false, array('message' => esc_html__('Params not saved or data not modified!', 'wpfd')));
        }
        // Rebuild css
        $params = $model->fillTransparent($params);
        $css = $this->rebuildSingleFileCss($params);
        // Minify
        $css = $this->minifyCSS($css);
        // Save to file
        $ds = DIRECTORY_SEPARATOR;
        $path = WP_CONTENT_DIR . $ds .'wp-file-download' . $ds;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $path .= 'wpfd-single-file-button.css';

        $result = file_put_contents($path, $css);
        if (!$result) {
            $this->exitStatus(false, array('message' => esc_html__('Params saved but CSS file can\'t not render!', 'wpfd')));
        }
        // Generate a hash to reload new file in front
        $hash = md5($css);
        update_option('wpfd_single_file_css_hash', $hash);

        $this->exitStatus(true, array('message' => esc_html__('Params saved success!', 'wpfd')));
    }
    /**
     * AJAX: Restore single file params
     *
     * @return void
     */
    public function restoresinglefile()
    {
        Application::getInstance('Wpfd');
        /* @var WpfdModelIconsBuilder $model */
        $model = $this->getModel('iconsbuilder');

        delete_option('wpfd_single_file_params');
        delete_option('wpfd_single_file_css_hash');

        header('Content-Type: application/json');
//        header('Connection: Close');

        $params = $model->getDefaultSingleButtonParams();

        $ds = DIRECTORY_SEPARATOR;
        $path = WP_CONTENT_DIR . $ds .'wp-file-download' . $ds;

        $path .= 'wpfd-single-file-button.css';
        if (file_exists($path)) {
            unlink(realpath($path));
        }
        $this->exitStatus(true, array(
            'message' => esc_html__('Restore success!', 'wpfd'),
            'params' => $params
        ));
    }
    /**
     * AJAX: Change default icon set
     *
     * @return void
     */
    public function changedefaulticonset()
    {
        $svgActive = Utilities::getInput('svg', 'POST', 'int');
        $pngActive = Utilities::getInput('png', 'POST', 'int');

        if (1 === $svgActive && 0 === $pngActive) {
            $iconSet = 'svg';
        } elseif (0 === $svgActive && 1 === $pngActive) {
            $iconSet = 'png';
        } else {
            $iconSet = 'default';
        }
        /* @var WpfdModelConfig $configModel */
        $configModel = $this->getModel('config');
        $globalSettings = $configModel->getConfig();
        $globalSettings['icon_set'] = $iconSet;

        $configModel->save($globalSettings);

        $this->exitStatus(true, array('message' => esc_html__('Success!', 'wpfd')));
    }
    /**
     * Validate data and convert to correct data
     *
     * @param array $params Raw params
     *
     * @return boolean
     */
    private function validate($params)
    {
        if (!is_array($params) || !is_countable($params)) {
            return false;
        }

        foreach ($params as $key => &$value) {
            if ($value === 'false') {
                $value = false;
            } elseif ($value === 'true') {
                $value = true;
            } elseif (is_numeric($value)) {
                settype($value, 'integer');
            } else {
                settype($value, 'string');
            }
        }

        return $params;
    }

    /**
     * Generate CSS V2
     *
     * @param array $params Validated params
     *
     * @return string
     */
    private function rebuildSingleFileCss($params)
    {
        $css = wpfd_get_template_html('tpl-single-css.php');
        return wpfdHandlerbarsRender($css, $params, 'singlefilecss');
    }
    /**
     * Minify CSS
     *
     * @param string $css CSS string
     *
     * @return string|string[]|null
     */
    private function minifyCSS($css)
    {
        $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css);
        $css = preg_replace('/\s{2,}/', ' ', $css);
        $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
        $css = preg_replace('/;}/', '}', $css);

        return $css;
    }
    /**
     * Exit with application/json header
     *
     * @param string $status Status
     * @param array  $datas  Data to return
     *
     * @return void
     */
    protected function exitStatus($status = '', $datas = array())
    {
        header('Content-Type: application/json');
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        die();
    }
}

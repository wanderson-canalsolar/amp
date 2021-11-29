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

//-- No direct access
defined('ABSPATH') || die();

/**
 * Class WpfdHelperFile
 */
class WpfdHelperFile
{
    /**
     * Convert bytes to size
     *
     * @param integer $bytes     Bytes
     * @param integer $precision Decimal fraction
     *
     * @return string
     */
    public static function bytesToSize($bytes, $precision = 2)
    {
        $sz     = self::getSupportFileMeasure();
        $factor = floor((strlen($bytes) - 1) / 3);
        if ((int) $factor === -1) {
            return esc_html__('N/A', 'wpfd');
        }
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- This is not problem
        return sprintf('%.' . $precision . 'f', $bytes / pow(1024, $factor)) . ' ' . esc_html__($sz[$factor], 'wpfd');
    }

    /**
     * Get support file measure list
     *
     * @return array
     */
    public static function getSupportFileMeasure()
    {
        return array(
            esc_html__('B', 'wpfd'),
            esc_html__('KB', 'wpfd'),
            esc_html__('MB', 'wpfd'),
            esc_html__('GB', 'wpfd'),
            esc_html__('TB', 'wpfd'),
            esc_html__('PB', 'wpfd')
        );
    }

    /**
     * Get preview url
     *
     * @param string  $id    File id
     * @param integer $catid Category id
     * @param string  $token Token key
     *
     * @return string
     */
    public static function getViewerUrl($id, $catid, $token = '')
    {
        $app = Application::getInstance('Wpfd');
        $generatedPreviewUrl = self::getGeneratedPreviewUrl($id, $catid, $token);

        if (false !== $generatedPreviewUrl) {
            return $generatedPreviewUrl;
        }

        $url = wpfd_sanitize_ajax_url($app->getAjaxUrl()) . 'task=file.download&wpfd_category_id=' . $catid . '&wpfd_file_id=';
        $url .= $id . '&token=' . $token . '&preview=1';
        /**
         * Filter to change preview service url
         *
         * @param string Preview url with %s placeholder for url
         *
         * @return string
         */
        $previewServiceUrl = apply_filters('wpfd_preview_service_url', 'https://docs.google.com/viewer?url=%s&embedded=true');

        /**
         * Filter to change preview url
         *
         * @param string Output url
         * @param string Preview url with %s placeholder for file encoded url
         * @param string Ajax Url to preview file
         *
         * @return string
         */
        return apply_filters('wpfd_preview_url', sprintf($previewServiceUrl, urlencode($url)), $previewServiceUrl, $url);
    }

    /**
     * Get url to open pdf in browser
     *
     * @param string  $id    File id
     * @param integer $catid Category id
     * @param string  $token Token key
     *
     * @return string
     */
    public static function getPdfUrl($id, $catid, $token = '')
    {
        $app = Application::getInstance('Wpfd');
        $url = wpfd_sanitize_ajax_url($app->getAjaxUrl()) . 'task=file.download&wpfd_category_id=' . $catid . '&wpfd_file_id=';
        $url .= $id . '&token=' . $token . '';

        return $url;
    }

    /**
     * Get generated preview file
     *
     * @param string  $id    File id
     * @param integer $catId Category id
     * @param string  $token Token key
     *
     * @return boolean|string
     */
    public static function getGeneratedPreviewUrl($id, $catId, $token = '')
    {
        $app = Application::getInstance('Wpfd');
        $modelConfig = Model::getInstance('configfront');
        $config = $modelConfig->getGlobalConfig();
        $useGeneratedPreview = isset($config['auto_generate_preview']) && intval($config['auto_generate_preview']) === 1 ? true : false;
        $securePreviewFile = isset($config['secure_preview_file']) && intval($config['secure_preview_file']) === 1 ? true : false;

        if (is_numeric($id)) {
            $previewFilePath = get_post_meta($id, '_wpfd_preview_file_path', true);
        } else {
            // Fix the id of onedrive
            $id = str_replace('-', '!', $id);
            $previewFileInfo = get_option('_wpfdAddon_preview_info_' . md5($id), false);
            $previewFilePath = is_array($previewFileInfo) && isset($previewFileInfo['path']) ? $previewFileInfo['path'] : false;
        }

        if ($useGeneratedPreview && $previewFilePath) {
            $previewFilePath = WP_CONTENT_DIR . $previewFilePath;
            if (file_exists($previewFilePath)) {
                if (!$securePreviewFile) {
                    return wpfd_abs_path_to_url($previewFilePath);
                } else {
                    return sprintf(
                        '%stask=file.preview&wpfd_category_id=%s&wpfd_file_id=%s&token=%s',
                        wpfd_sanitize_ajax_url($app->getAjaxUrl()),
                        $catId,
                        $id,
                        $token
                    );
                }
            }
        }

        return false;
    }
    /**
     * Get media viewer url
     *
     * @param string  $id    File id
     * @param integer $catid Category id
     * @param string  $ext   Extension
     *
     * @return string
     */
    public static function getMediaViewerUrl($id, $catid, $ext = '')
    {
        $app = Application::getInstance('Wpfd');

        $imagesType = array('jpg', 'png', 'gif', 'jpeg', 'jpe', 'bmp', 'ico', 'tiff', 'tif', 'svg', 'svgz');
        $videoType  = array(
            'mp4',
            'mpeg',
            'mpe',
            'mpg',
            'mov',
            'qt',
            'rv',
            'avi',
            'movie',
            'flv',
            'webm',
            'ogv'
        );//,'3gp'
        $audioType  = array(
            'mid',
            'midi',
            'mp2',
            'mp3',
            'mpga',
            'ram',
            'rm',
            'rpm',
            'ra',
            'wav'
        );  // ,'aif','aifc','aiff'
        if (in_array($ext, $imagesType)) {
            $type = 'image';
        } elseif (in_array($ext, $videoType)) {
            $type = 'video';
        } elseif (in_array($ext, $audioType)) {
            $type = 'audio';
        } else {
            $type = '';
        }

        $return = wpfd_sanitize_ajax_url($app->getAjaxUrl()) . 'task=frontviewer.display&view=frontviewer&id=' . $id . '&catid=';

        return $return . $catid . '&type=' . $type . '&ext=' . $ext;
    }

    /**
     * Check if it is media file
     *
     * @param string $ext Extension
     *
     * @return boolean
     */
    public static function isMediaFile($ext)
    {
        $media_arr = array(
            'mid',
            'midi',
            'mp2',
            'mp3',
            'mpga',
            'ram',
            'rm',
            'rpm',
            'ra',
            'wav', //,'aif','aifc','aiff'
            'm4a',
            'mp4',
            'mpeg',
            'mpe',
            'mpg',
            'mov',
            'qt',
            'rv',
            'avi',
            'movie',
            'flv',
            'webm',
            'ogv', //'3gp',
            'jpg',
            'png',
            'gif',
            'jpeg',
            'jpe',
            'bmp',
            'ico',
            'tiff',
            'tif',
            'svg',
            'svgz'
        );
        if (in_array(strtolower($ext), $media_arr)) {
            return true;
        }

        return false;
    }


    /**
     * Get mime type
     *
     * @param string $ext Extension
     *
     * @return string
     */
    public static function mimeType($ext)
    {
        $mime_types = array(
            //flash
            'swf'   => 'application/x-shockwave-flash',
            'flv'   => 'video/x-flv',
            // images
            'png'   => 'image/png',
            'jpe'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'jpg'   => 'image/jpeg',
            'gif'   => 'image/gif',
            'bmp'   => 'image/bmp',
            'ico'   => 'image/vnd.microsoft.icon',
            'tiff'  => 'image/tiff',
            'tif'   => 'image/tiff',
            'svg'   => 'image/svg+xml',
            'svgz'  => 'image/svg+xml',

            // audio
            'mid'   => 'audio/midi',
            'midi'  => 'audio/midi',
            'mp2'   => 'audio/mpeg',
            'mp3'   => 'audio/mpeg',
            'mpga'  => 'audio/mpeg',
            'aif'   => 'audio/x-aiff',
            'aifc'  => 'audio/x-aiff',
            'aiff'  => 'audio/x-aiff',
            'ram'   => 'audio/x-pn-realaudio',
            'rm'    => 'audio/x-pn-realaudio',
            'rpm'   => 'audio/x-pn-realaudio-plugin',
            'ra'    => 'audio/x-realaudio',
            'wav'   => 'audio/x-wav',
            'wma'   => 'audio/wma',
            'm4a'   => 'audio/m4a',

            //Video
            'mp4'   => 'video/mp4',
            'mpeg'  => 'video/mpeg',
            'mpe'   => 'video/mpeg',
            'mpg'   => 'video/mpeg',
            'mov'   => 'video/quicktime',
            'qt'    => 'video/quicktime',
            'rv'    => 'video/vnd.rn-realvideo',
            'avi'   => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            '3gp'   => 'video/3gpp',
            'webm'  => 'video/webm',
            'ogv'   => 'video/ogg',
            //doc
            'pdf'   => 'application/pdf'
        );

        if (array_key_exists(strtolower($ext), $mime_types)) {
            return $mime_types[strtolower($ext)];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Get mime type
     *
     * @param string $ext     Extenstion
     * @param string $fileExt Extenstion
     *
     * @return string
     */
    public static function isCorrectMimeType($ext, $fileExt)
    {
        $ext = strtolower($ext);
        if (empty($ext)) {
            return false;
        }

        $mime_types_map = array(
            'application/x-msdownload' => 'exe',
            'application/x-dosexec'    => 'exe'
        );

        if (isset($mime_types_map[$fileExt])) {
            if ($mime_types_map[$fileExt] === $ext) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }


    /**
     * Check mime file type
     *
     * @param string $file File
     *
     * @return boolean
     */
    public static function checkMimeType($file)
    {
        if (!function_exists('finfo_open') || !function_exists('finfo_file')) {
            return true;
        }
        $ext          = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $file_info    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeFileInfo = finfo_file($file_info, $file);
        finfo_close($file_info);

        // Always return true for mising mimetype
        // Some server or php version always return application/octet-stream
        if (isset($mimeFileInfo) && $mimeFileInfo !== null) {
            return self::isCorrectMimeType($ext, $mimeFileInfo);
        }

        return true;
    }

    /**
     * Search assets
     *
     * @return void
     */
    public static function wpfdAssets()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_style(
            'jquery-ui-1.9.2',
            plugins_url('app/admin/assets/css/ui-lightness/jquery-ui-1.9.2.custom.min.css', WPFD_PLUGIN_FILE)
        );
        wp_enqueue_style('dashicons');

        wp_enqueue_script(
            'wpfd-videojs',
            plugins_url('app/site/assets/js/video.js', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_style(
            'wpfd-videojs',
            plugins_url('app/site/assets/css/video-js.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_style(
            'wpfd-colorbox',
            plugins_url('app/site/assets/css/colorbox.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_style(
            'wpfd-viewer',
            plugins_url('app/site/assets/css/viewer.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
    }

    /**
     * Search access
     *
     * @return void
     */
    public static function wpfdAssetsSearch()
    {
        wp_enqueue_style('wpfd-jquery-tagit', plugins_url('app/admin/assets/css/jquery.tagit.css', WPFD_PLUGIN_FILE));
        wp_enqueue_style(
            'wpfd-datetimepicker',
            plugins_url('app/site/assets/css/jquery.datetimepicker.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_style(
            'wpfd-search_filter',
            plugins_url('app/site/assets/css/search_filter.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );

        if (!is_admin()) {
            wp_enqueue_script('wpfd-jquery-tagit', plugins_url('app/admin/assets/js/jquery.tagit.js', WPFD_PLUGIN_FILE));
            wp_enqueue_script(
                'wpfd-datetimepicker',
                plugins_url('app/site/assets/js/jquery.datetimepicker.js', WPFD_PLUGIN_FILE),
                array(),
                WPFD_VERSION
            );
            wp_enqueue_script(
                'wpfd-search_filter',
                plugins_url('app/site/assets/js/search_filter.js', WPFD_PLUGIN_FILE),
                array(),
                WPFD_VERSION
            );
        }
        Application::getInstance('Wpfd');
        $modelConfig  = Model::getInstance('configfront');
        $globalConfig = $modelConfig->getGlobalConfig();
        $searchconfig = $modelConfig->getSearchConfig();
        $locale       = substr(get_locale(), 0, 2);
        wp_localize_script(
            'wpfd-search_filter',
            'wpfdvars',
            array(
                'basejUrl'   => home_url('?page_id=' . $searchconfig['search_page']),
                'dateFormat' => $globalConfig['date_format'],
                'locale'     => $locale
            )
        );
    }

    /**
     * Download Large File
     *
     * @param string  $filePath         File path
     * @param boolean $deleteWhenFinish Delete file when finish
     *
     * @return void
     */
    public static function downloadLargeFile($filePath, $deleteWhenFinish = false)
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped -- not print any error to file content
        @ini_set('error_reporting', E_ALL & ~E_NOTICE);
        @ini_set('zlib.output_compression', 'Off');

        $chunksize = 1 * (1024 * 1024);
        if (file_exists($filePath)) {
            @set_time_limit(0);
            $size = intval(sprintf('%u', filesize($filePath)));
            if ($size > $chunksize) {
                $handle = fopen($filePath, 'rb');
                while (!feof($handle)) {
                    print(@fread($handle, $chunksize));
                    ob_flush();
                    flush();
                }
                fclose($handle);
            } else {
                readfile($filePath);
            }
            if ($deleteWhenFinish) {
                unlink($filePath);
            }
            exit;
        } else {
            exit(sprintf(esc_html('File "%s" does not exist!'), $filePath));
        }
        // phpcs:enable
    }

    /**
     * Send Download File to the browser
     *
     * @param string  $filePath         Absolute path to the file
     * @param string  $fileName         File name return to Browser
     * @param string  $fileExt          File extension for check it mime
     * @param boolean $preview          Is preview
     * @param boolean $openPdfInBrowser Is open in browser
     *
     *
     * Copyright 2012 Armand Niculescu - media-division.com
     * Redistribution and use in source and binary forms, with or without modification,
     * are permitted provided that the following conditions are met:
     * 1. Redistributions of source code must retain the above copyright notice,
     * this list of conditions and the following disclaimer.
     * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the
     * following disclaimer in the documentation and/or other materials provided with the distribution.
     * THIS SOFTWARE IS PROVIDED BY THE FREEBSD PROJECT "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
     * BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
     * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
     * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
     * OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
     * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
     * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     * @return boolean|void
     */
    public static function sendDownloadFallback($filePath, $fileName, $fileExt, $preview = false, $openPdfInBrowser = false)
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.Security.NonceVerification.Recommended -- not print any error to file content, output is file content, $_REQUEST['stream'] is checking condition
        @ini_set('error_reporting', E_ALL & ~E_NOTICE);
        @ini_set('zlib.output_compression', 'Off');
        $isAttachment = isset($_REQUEST['stream']) ? false : true;
        if ($openPdfInBrowser && strtolower($fileExt) === 'pdf' && $preview) {
            $isAttachment = false;
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        // make sure the file exists on server
        if (is_file($filePath)) {
            $fileSize    = filesize($filePath);
            $fileHandler = @fopen($filePath, 'rb');
            if ($fileHandler) {
                // set the headers, prevent caching
                header('Pragma: public');
                header('Expires: -1');
                /**
                 * Filter to add X-Robots-Tag to download link
                 *
                 * @param boolean
                 */
                if (apply_filters('wpfd_nofollow_noindex_header', true)) {
                    header('X-Robots-Tag: noindex, nofollow', true);
                }
                header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
                // set appropriate headers for attachment or streamed file
                if ($isAttachment) {
                    header('Content-Disposition: attachment; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName));
                } else {
                    header('Content-Disposition: inline; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName));
                }
                header('Content-Type: ' . self::mimeType($fileExt));

                // check if http_range is sent by browser (or download manager)
                // todo: Apply multiple ranges
                if (isset($_SERVER['HTTP_RANGE'])) {
                    list($sizeUnit, $rangeOrig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                    if ($sizeUnit === 'bytes') {
                        // multiple ranges could be specified at the same time,
                        // but for simplicity only serve the first range
                        // http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
                        $ranges = explode(',', $rangeOrig, 2);
                        if (is_array($ranges) && count($ranges) === 2) {
                            list($range, $extraRanges) = explode(',', $rangeOrig, 2);
                        } else {
                            $range = '';
                        }
                    } else {
                        $range = '';
                        header('HTTP/1.1 416 Requested Range Not Satisfiable');

                        return false;
                    }
                } else {
                    $range = '';
                }
                // figure out download piece from range (if set)
                list($seekStart, $seekEnd) = explode('-', $range, 2);
                // set start and end based on range (if set), else set defaults
                // also check for invalid ranges.
                $seekEnd   = (empty($seekEnd)) ? ($fileSize - 1) : min(abs(intval($seekEnd)), ($fileSize - 1));
                $seekStart = (empty($seekStart) || $seekEnd < abs(intval($seekStart))) ?
                    0 : max(abs(intval($seekStart)), 0);
                // Only send partial content header if downloading a piece of the file (IE workaround)
                if ($seekStart > 0 || $seekEnd < ($fileSize - 1)) {
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Range: bytes ' . $seekStart . '-' . $seekEnd . '/' . $fileSize);
                    if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
                        header('Content-Length: ' . ($seekEnd - $seekStart + 1));
                    }
                } else {
                    if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
                        header('Content-Length: ' . $fileSize);
                    }
                }
                header('Accept-Ranges: bytes');
                @set_time_limit(0);
                fseek($fileHandler, $seekStart);
                while (!feof($fileHandler)) {
                    print(@fread($fileHandler, 1024 * 8));
                    @ob_flush();
                    flush();
                    if (connection_status() !== 0) {
                        @fclose($fileHandler);

                        return true;
                    }
                }
                // File save was a success
                @fclose($fileHandler);

                return true;
            } else {
                // File couldn't be opened
                header('HTTP/1.0 500 Internal Server Error');

                return false;
            }
        } else {
            // File does not exist
            header('HTTP/1.0 404 Not Found');

            return false;
        }
        // phpcs:enable
    }

    /**
     * Send download
     *
     * @param string  $filePath         File path
     * @param string  $fileName         File name
     * @param string  $fileExt          File extension
     * @param boolean $preview          Preview file
     * @param boolean $openPdfInBrowser Open preview type
     *
     * @return void|boolean
     */
    public static function sendDownload($filePath, $fileName, $fileExt, $preview = false, $openPdfInBrowser = false)
    {
        if (!is_file($filePath)) {
            header('HTTP/1.0 404 Not Found');
            return false;
        }

        Application::getInstance('Wpfd');
        $modelConfig  = Model::getInstance('configfront');
        $globalConfig = $modelConfig->getGlobalConfig();
        if ($globalConfig &&
            isset($globalConfig['use_xsendfile']) &&
            (int)$globalConfig['use_xsendfile'] === 1 &&
            function_exists('apache_get_modules') &&
            in_array('mod_xsendfile', apache_get_modules(), true)
        ) {
            self::downloadHeaders($filePath, $fileName, $preview);
            $filepath = apply_filters('wpfd_download_file_xsendfile_file_path', $filePath, $filePath, $fileName);
            header('X-Sendfile: ' . $filepath);
            return true; // DONOT exits or email notification won't work
        } else {
            // Fallback.
            self::sendDownloadFallback($filePath, $fileName, $fileExt, $preview, $openPdfInBrowser);
        }
    }
    /**
     * Set headers for the download.
     *
     * @param string $file_path File path.
     * @param string $filename  File name.
     * @param array  $preview   Inline header for preview files
     *
     * @return void
     */
    private static function downloadHeaders($file_path, $filename, $preview = false)
    {
        self::checkServerConfig();
        self::cleanBuffers();
        /**
         * Filter to add X-Robots-Tag to download link
         *
         * @param boolean
         *
         * @ignore
         */
        if (apply_filters('wpfd_nofollow_noindex_header', true)) {
            header('X-Robots-Tag: noindex, nofollow', true);
        }
        header('Content-Type: ' . self::getDownloadContentType($file_path));
        header('Content-Description: File Transfer');
        if ($preview) {
            header('Content-Disposition: inline; filename="' . $filename . '"; filename*=UTF-8\'\'' . $filename);
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $filename);
        }

        header('Content-Transfer-Encoding: binary');

        $file_size = @filesize($file_path); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
        if (!$file_size) {
            return;
        }
        header('Content-Length: ' . $file_size);
    }
    /**
     * Check and set certain server config variables to ensure downloads work as intended.
     *
     * @return void
     */
    private static function checkServerConfig()
    {
        $limit = 0;
        // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved -- This is for checking
        if (function_exists('set_time_limit') && false === strpos(ini_get('disable_functions'), 'set_time_limit') && !ini_get('safe_mode')) {
            // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- This is for checking
            @set_time_limit($limit);
        }
        // phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.get_magic_quotes_runtimeDeprecated,Generic.PHP.DeprecatedFunctions.Deprecated -- This check
        if (function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime() && version_compare(phpversion(), '5.4', '<')) {
            set_magic_quotes_runtime(0); // phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.set_magic_quotes_runtimeDeprecatedRemoved,Generic.PHP.DeprecatedFunctions.Deprecated -- It's OK
        }
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv
        }
        @ini_set('zlib.output_compression', 'Off'); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_ini_set
        @session_write_close(); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.VIP.SessionFunctionsUsage.session_session_write_close
    }

    /**
     * Clean all output buffers.
     *
     * Can prevent errors, for example: transfer closed with 3 bytes remaining to read.
     *
     * @return void
     */
    private static function cleanBuffers()
    {
        if (ob_get_level()) {
            $levels = ob_get_level();
            for ($i = 0; $i < $levels; $i ++) {
                @ob_end_clean(); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
            }
        } else {
            @ob_end_clean(); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
        }
    }
    /**
     * Get content type of a download.
     *
     * @param string $file_path File path.
     *
     * @return string
     */
    private static function getDownloadContentType($file_path)
    {
        $file_extension = strtolower(substr(strrchr($file_path, '.'), 1));
        $ctype          = 'application/force-download';

        foreach (get_allowed_mime_types() as $mime => $type) {
            $mimes = explode('|', $mime);
            if (in_array($file_extension, $mimes, true)) {
                $ctype = $type;
                break;
            }
        }

        return $ctype;
    }
    /**
     * Santize File Name for download
     *
     * @param string $fileName File name
     *
     * @return string
     */
    public static function santizeFileName($fileName)
    {
        if (function_exists('sanitize_file_name')) {
            return sanitize_file_name($fileName);
        } elseif (function_exists('mb_ereg_replace')) {
            return mb_ereg_replace('([^\w\s\d\-_~,;\[\]\(\).])', '', $fileName);
        } else {
            return preg_replace('([^\w\s\d\-_~,;\[\]\(\).])', '', $fileName);
        }
    }

    /**
     * Check access for single file
     *
     * @param array $file File
     *
     * @return boolean
     */
    public static function checkAccess($file)
    {
        $user = wp_get_current_user();
        Application::getInstance('Wpfd');
        //check access
        $modelCategory = Model::getInstance('categoryfront');
        $configModel   = Model::getInstance('configfront');
        $config        = array();
        if (method_exists($configModel, 'getGlobalConfig')) {
            $config = $configModel->getGlobalConfig();
        } elseif (method_exists($configModel, 'getConfig')) {
            $config = $configModel->getConfig();
        }
        $category = $modelCategory->getCategory($file['catid']);

        if (empty($category) || is_wp_error($category)) {
            return false;
        }

        if ((int) $category->access === 1) {
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows        = array_intersect($roles, $category->roles);
            $allows_single = false;

            if (isset($category->params['canview']) && $category->params['canview'] === '') {
                $category->params['canview'] = 0;
            }
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            if (isset($category->params['canview']) && ((int) $category->params['canview'] !== 0) && is_countable($category->roles) &&
                !count($category->roles)) {
                if ((int) $category->params['canview'] === (int) $user->ID) {
                    $allows_single = true;
                }
                if ($allows_single === false) {
                    return false;
                }
                // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            } elseif (isset($category->params['canview']) && ((int) $category->params['canview'] !== 0) && is_countable($category->roles) &&
                      count($category->roles)) {
                if ((int) $category->params['canview'] === (int) $user->ID) {
                    $allows_single = true;
                }
                if ($allows_single === false && empty($allows)) {
                    return false;
                }
            } else {
                if (empty($allows)) {
                    return false;
                }
            }
        }

        // Check single user permission
        if ((int) WpfdBase::loadValue($config, 'restrictfile', 0) === 1) {
            $canview = isset($file['canview']) ? $file['canview'] : 0;
            $canview = array_map('intval', explode(',', $canview));
            if (!in_array($user->ID, $canview) && !in_array(0, $canview)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add statistics row
     *
     * @param string $fid  File id
     * @param string $type Statistic type
     *
     * @return void
     */
    public static function addStatisticsRow($fid, $type = 'default')
    {
        global $wpdb;
        $date          = date('Y-m-d');
        $currentUserId = 0;
        $modelConfig = Model::getInstance('configfront');
        if (method_exists($modelConfig, 'getGlobalConfig')) {
            $params = $modelConfig->getGlobalConfig();
        } elseif (method_exists($modelConfig, 'getConfig')) {
            $params = $modelConfig->getConfig();
        }

        if (isset($params)) {
            if (!class_exists('WpfdBase')) {
                include_once WPFD_PLUGIN_DIR_PATH . '/app/admin/classes/WpfdBase.php';
            }
            $trackUserDownload = (int) WpfdBase::loadValue($params, 'track_user_download', 0);

            // Check tracking user downloading
            if ($trackUserDownload === 1) {
                $currentUserId = get_current_user_id();
            }
        }

        $object = $wpdb->get_row($wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wpfd_statistics WHERE related_id=%s AND date=%s AND type=%s AND uid=%d',
            $fid,
            $date,
            $type,
            (int) $currentUserId
        ));

        if ($object) {
            $wpdb->query($wpdb->prepare(
                'UPDATE ' . $wpdb->prefix . 'wpfd_statistics SET count=(count+1) WHERE related_id=%s AND date=%s AND type=%s AND uid=%d',
                $fid,
                $date,
                $type,
                (int) $currentUserId
            ));
        } else {
            $wpdb->query($wpdb->prepare(
                'INSERT INTO ' . $wpdb->prefix . 'wpfd_statistics (related_id, uid, type, date, count) VALUES (%s, %d, %s, %s, 1)',
                $fid,
                (int) $currentUserId,
                $type,
                $date
            ));
        }
    }
    /**
     * Check and get icon path if exists
     *
     * @param string $extension Extension to get
     * @param string $set       Set icon type
     *
     * @return boolean|string
     */
    public static function getDefaultIconPath($extension, $set = 'png')
    {
        $siteAssetsPath = WPFD_PLUGIN_DIR_PATH . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
        $defaultIconsPath = $siteAssetsPath . 'icons';

        switch ($set) {
            case 'png':
                $iconsPath = $defaultIconsPath . DIRECTORY_SEPARATOR . 'png' . DIRECTORY_SEPARATOR;
                $filePath  = $iconsPath . $extension . '.png';
                if (file_exists($filePath)) {
                    return $filePath;
                } else {
                    $iconsPath = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'png' . DIRECTORY_SEPARATOR;
                    $filePath  = $iconsPath . 'unknown.png';
                    if (file_exists($filePath)) {
                        return $filePath;
                    }
                    $iconsPath = $defaultIconsPath . DIRECTORY_SEPARATOR . 'png' . DIRECTORY_SEPARATOR;
                    $filePath  = $iconsPath . 'unknown.png';
                    if (file_exists($filePath)) {
                        return $filePath;
                    }
                }
                break;
            case 'svg':
                $iconsPath = $defaultIconsPath . DIRECTORY_SEPARATOR . 'svg' . DIRECTORY_SEPARATOR;
                $filePath  = $iconsPath . $extension . '.svg';
                if (file_exists($filePath)) {
                    return $filePath;
                }
                break;
//            case 'svg2':
//                $iconsPath = $defaultIconsPath . DIRECTORY_SEPARATOR . 'svg2' . DIRECTORY_SEPARATOR;
//                $filePath  = $iconsPath . $extension . '.svg';
//                if (file_exists($filePath)) {
//                    return $filePath;
//                }
//                break;
            default:
                $iconsPath = $siteAssetsPath . 'images' . DIRECTORY_SEPARATOR. 'theme' . DIRECTORY_SEPARATOR;
                $filePath  = $iconsPath . $extension . '.png';
                if (file_exists($filePath)) {
                    return $filePath;
                }
                return false;
        }
        return false;
    }

    /**
     * Get uploaded icon path
     *
     * @param string  $extension Extension to get
     * @param string  $set       Set icon type
     * @param boolean $url       Retun Url
     *
     * @return boolean|string
     */
    public static function getUploadedIconPath($extension, $set = 'png', $url = true)
    {
        $iconPath = self::getCustomIconPath($set) . $extension . '.' . preg_replace('/[0-9]+/', '', $set);
        if (file_exists($iconPath)) {
            if ($url) {
                return wpfd_abs_path_to_url($iconPath);
            } else {
                return $iconPath;
            }
        }

        $iconPath = self::getDefaultIconPath($extension, $set);
        if (file_exists($iconPath)) {
            if ($url) {
                return wpfd_abs_path_to_url($iconPath);
            } else {
                return $iconPath;
            }
        }

        return false;
    }
    /**
     * Get icons urls
     *
     * @param string $extension Extension to get
     * @param string $set       Set icon type
     *
     * @return array
     */
    public static function getIconUrls($extension, $set = 'png')
    {
        $output = array(
            'default' => '',
            'uploaded' => '',
        );
        $iconPath = self::getCustomIconPath($set) . $extension . '.' . preg_replace('/[0-9]+/', '', $set);
        if (file_exists($iconPath)) {
            $output['uploaded'] = wpfd_abs_path_to_url($iconPath);
        }

        $iconPath = self::getDefaultIconPath($extension, $set);

        if (file_exists($iconPath)) {
            $output['default'] = wpfd_abs_path_to_url($iconPath);
        }
        if ($output['uploaded'] !== '' || $output['default'] !== '') {
            return $output;
        }

        return false;
    }
    /**
     * Get custom icon path
     *
     * @param string $set Icon set name
     *
     * @return string
     */
    public static function getCustomIconPath($set)
    {
        $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $set . DIRECTORY_SEPARATOR;
        wpfdCreateSecureFolder($path);
        return $path;
    }

    /**
     * Render Icons set css
     *
     * @return integer
     */
    public static function renderCSS()
    {
        Application::getInstance('Wpfd');
        /* @var WpfdModelConfig $configModel */
        $configModel = Model::getInstance('configfront');
        /* @var WpfdModelIconsBuilder $iconBuilderModel */
        $iconBuilderModel = Model::getInstance('iconsbuilder');
        $svgParams = $iconBuilderModel->getParams('svg');
        $extension = $configModel->getAllowedExt();
        $iconSets = array('png','svg');
        $rebuildTime = time();
        $svgParams = isset($svgParams['icons']) ? $svgParams['icons'] : null;
        foreach ($iconSets as $set) {
            $path = self::getCustomIconPath($set);
            // Remove unused css
            $cssFiles = glob($path . '*.css');
            foreach ($cssFiles as $file) {
                unlink($file);
            }

            $css = '';
            if ($set === 'png') {
                $unknownPng = self::getIconUrls('unknown', 'png');
                if (false !== $unknownPng) {
                    if (isset($unknownPng['uploaded']) && $unknownPng['uploaded'] !== '') {
                        $defaultUrl = $unknownPng['uploaded'];
                    } elseif (isset($unknownPng['default']) && $unknownPng['default'] !== '') {
                        $defaultUrl = $unknownPng['default'];
                    } else {
                        $defaultUrl = wpfd_abs_path_to_url(WPFD_PLUGIN_DIR_PATH . 'app/site/assets/icons/png/default.png');
                    }
                    $css .= '.wpfd-icon-set-' . $set . '.ext{background: url(' . $defaultUrl . ') no-repeat center center}';
                }
            }

            foreach ($extension as $ext) {
                $iconUrl = self::getUploadedIconPath($ext, $set);
                if (false !== $iconUrl) {
                    $css .= '.wpfd-icon-set-' . $set . '.ext.ext-' . $ext . '{background: url(' . $iconUrl . '?version=' . $rebuildTime . ') no-repeat center center;';
                    if ($set === 'svg' && !is_null($svgParams)) {
                        if (isset($svgParams['wpfd-icon-' . $ext])) {
                            $svgParam = $svgParams['wpfd-icon-' . esc_attr($ext)];
                            if (isset($svgParam['wrapper-active']) && intval($svgParam['wrapper-active']) === 1) {
                                // box-shadow
                                $css .= isset($svgParam['border-radius']) && intval($svgParam['border-radius']) > 0 ? 'border-radius: ' . $svgParam['border-radius'] . '%;' : '';
                                // border
                                $css .= 'border: ' . $svgParam['border-size'] . 'px solid ' . $svgParam['border-color'] . ';';
                                $css .= 'box-shadow: ' . $svgParam['horizontal-position'] . 'px ' . $svgParam['vertical-position'] . 'px ' . $svgParam['blur-radius'] . 'px ' . $svgParam['spread-radius'] . 'px ' . $svgParam['shadow-color'] . ';';
                                // background-color
                                $css .= 'background-color: ' . $svgParam['background-color'] . ';';
                            }
                        }
                    }
                    $css .= '}';
                }
            }

            // Save file
            file_put_contents($path . 'styles-' . $rebuildTime . '.css', $css);
        }
        update_option('wpfd_icon_rebuild_time', $rebuildTime);

        return $rebuildTime;
    }

    /**
     * Check expiration date for file
     *
     * @param integer $id File id
     *
     * @throws Exception  Fire message if error
     *
     * @return boolean
     */
    public static function wpfdIsExpired($id)
    {
        $expires    = get_post_meta($id, '_wpfd_file_meta_expiration_date', true);
        $format     = 'Y-m-d H:i:s';

        if (!empty($expires)) {
            $current = new DateTime();
            $current->setTimezone(self::wpfdGetWpTimezone());

            $expiration = DateTime::createFromFormat(
                $format,
                $expires,
                self::wpfdGetWpTimezone()
            );

            if ($expiration
                // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison -- This is for checking
                && $expiration->format($format) == $expires
                && $current >= $expiration) {
                return true;
            }
        }

        return false;
    }

    /**
     * WpfdGetWpTimezone
     *
     * @throws Exception  Fire message if error
     *
     * @return object
     */
    private static function wpfdGetWpTimezone()
    {

        $timezone_string = get_option('timezone_string');
        if (!empty($timezone_string)) {
            $timezone = new DateTimeZone($timezone_string);
            return $timezone;
        }
        $offset     = get_option('gmt_offset');
        $hours      = (int) $offset;
        $minutes    = abs(( $offset - (int) $offset ) * 60);
        $offset     = sprintf('%+03d:%02d', $hours, $minutes);
        $timezone   = new DateTimeZone($offset);

        return $timezone;
    }
}

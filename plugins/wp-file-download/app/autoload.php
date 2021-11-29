<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die('No direct script access allowed!');

if (!function_exists('wpfdAutoload')) {
    /**
     * Prohibit direct script loading
     *
     * @param string $className Class name will load
     *
     * @return void
     */
    function wpfdAutoload($className)
    {
        $className = ltrim($className, '\\');
        // Fix conflict with plugin Multisite Robotstxt Manager
        if ($className === 'MSRTM_Api') {
            return;
        }
        //Return if it's not a Joomunited's class
        if (strpos($className, 'Joomunited\WP_File_Download\Admin\Fields') === 0) {
            $fileName = '';
            $lastNsPos = strripos($className, '\\');
            if ($lastNsPos) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

            $suffix = '';
            if (defined('WPFD_ADMIN_UI') && WPFD_ADMIN_UI === true) {
                $suffix = '_v2';
            }

            $folder = 'admin' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'fields' . $suffix . DIRECTORY_SEPARATOR;

            $fileName = '' . DIRECTORY_SEPARATOR . substr($fileName, 41);
            if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . $folder . $fileName)) {
                require dirname(__FILE__) . DIRECTORY_SEPARATOR . $folder . $fileName;
            } else {
                $folder = 'admin' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR;
                if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . $folder . $fileName)) {
                    require dirname(__FILE__) . DIRECTORY_SEPARATOR . $folder . $fileName;
                }
            }

            return;
        }

        //don't load any namespace class
        if (strpos($className, '\\') !== false) {
            return;
        }
        $fileName = basename($className) . '.php';
        $app = Application::getInstance('Wpfd', WPFD_PLUGIN_FILE);
        if ($app->isAdmin()) {
            $path_admin_file = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers';
            $path_admin_file .= DIRECTORY_SEPARATOR . $fileName;
            $file = $path_admin_file;
        } else {
            $path_site_file = $app->getPath() . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'helpers';
            $path_site_file .= DIRECTORY_SEPARATOR . $fileName;
            $file = $path_site_file;
        }
        if (file_exists($file)) {
            require_once($file);
        }
    }
}
spl_autoload_register('wpfdAutoload');

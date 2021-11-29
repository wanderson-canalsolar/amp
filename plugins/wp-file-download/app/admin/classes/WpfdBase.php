<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

// no direct access
defined('ABSPATH') || die();

/**
 * Class WpfdBase
 */
class WpfdBase
{

    /**
     * Load value with default value
     *
     * @param object $var     Object
     * @param string $value   Value
     * @param string $default Default value
     *
     * @return string
     */
    public static function loadValue($var, $value, $default = '')
    {
        if (is_object($var) && isset($var->$value)) {
            return $var->$value;
        } elseif (is_array($var) && isset($var[$value])) {
            return $var[$value];
        }
        return $default;
    }

    /**
     * Method to retrieve the path to the component image directory
     *
     * @param mixed $categoryId Category id to get path
     *
     * @return string
     */
    public static function getFilesPath($categoryId = null)
    {
        $upload_dir = wp_upload_dir();
        $wpfdUploadDir = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'wpfd';
        /**
         * Filter allow to change the files upload dir
         *
         * @param string
         */
        $wpfdUploadDir = apply_filters('wpfd_upload_dir', $wpfdUploadDir);
        if (!file_exists($wpfdUploadDir)) {
            mkdir($wpfdUploadDir, 0777, true);
            $data = '<html><body bgcolor="#FFFFFF"></body></html>';
            $file = fopen($wpfdUploadDir . 'index.html', 'w');
            fwrite($file, $data);
            fclose($file);
            $data = 'deny from all';
            $file = fopen($wpfdUploadDir . '.htaccess', 'w');
            fwrite($file, $data);
            fclose($file);
        }

        if ($categoryId === null) {
            return $wpfdUploadDir . DIRECTORY_SEPARATOR;
        }

        return $wpfdUploadDir . DIRECTORY_SEPARATOR . $categoryId . DIRECTORY_SEPARATOR;
    }

    /**
     * Method check exist theme
     *
     * @param string $themeName Theme name to check
     *
     * @return boolean
     */
    public static function checkExistTheme($themeName)
    {
        $themes = array('default', 'ggd', 'table', 'tree');
        if (in_array($themeName, $themes)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Crop title
     *
     * @param object $catParams Category parameters
     * @param string $catTheme  Category's theme name
     * @param string $title     Title
     *
     * @return string
     */
    public static function cropTitle($catParams, $catTheme, $title)
    {
        $cropedTitle = $title;
        $cropTitle = (int)self::loadValue($catParams, $catTheme . '_croptitle', 0);
        if (!$cropTitle) {
            $cropTitle = (int)self::loadValue($catParams, 'croptitle', 0);
        }
        if ($cropTitle && strlen($title) > $cropTitle) {
            $cropedTitle = substr($title, 0, $cropTitle) . '...';
        }
        return $cropedTitle;
    }

    /**
     * Validate Date
     *
     * @param string $date       Date to validdate
     * @param string $configDate Date format
     *
     * @return boolean
     */
    public static function validateDate($date, $configDate)
    {
        $date = self::translateDateToEnglish($date);
        $date = str_replace('marchch', 'march', $date);
        $format = 'Y-m-d H:i:s';
        if (preg_match('/(a|A|g|h|G|H|i|s|T)/i', $configDate)) {
            $outputDate = DateTime::createFromFormat($configDate, $date);
            if ($outputDate) {
                return $outputDate->format($format);
            }
        } else {
            $tz = get_option('timezone_string');
            if ($tz) {
                $datetime = DateTime::createFromFormat($configDate, $date, new DateTimeZone($tz));
                if ($datetime) {
                    return $datetime->format($format);
                }
            } else {
                $datetime = DateTime::createFromFormat($configDate, $date);
                if ($datetime) {
                    return get_date_from_gmt($datetime->format($format), $format);
                }
            }
        }

        return mysql2date($format, $date);
    }
    /**
     * Translate month to english
     *
     * @param string $dateString Date input
     *
     * @return string
     */
    public static function translateDateToEnglish($dateString)
    {
        return strtr(
            strtolower($dateString),
            array(
                // fr
                'janvier'   => 'jan',
                'février'   => 'feb',
                'mars'      => 'march',
                'avril'     => 'apr',
                'mai'       => 'may',
                'juin'      => 'jun',
                'juillet'   => 'jul',
                'août'      => 'aug',
                'septembre' => 'sep',
                'octobre'   => 'oct',
                'novembre'  => 'nov',
                'décembre'  => 'dec',
                // id
                'januari'   => 'jan',
                'februari'  => 'feb',
                'maret'     => 'march',
                'april'     => 'apr',
                'mei'       => 'may',
                'juni'      => 'jun',
                'juli'      => 'jul',
                'agustus'   => 'aug',
                'september' => 'sep',
                'oktober'   => 'oct',
                'november'  => 'nov',
                'desember'  => 'dec',
                // it
                'gen' => 'jan',
                'mar' => 'march',
                'mag' => 'may',
                'giu' => 'jun',
                'lug' => 'jul',
                'ago' => 'aug',
                'set' => 'sep',
                'ott' => 'oct',
                'dic' => 'dec',
                // Fix
                'marchch' => 'march'
            )
        );
    }
}

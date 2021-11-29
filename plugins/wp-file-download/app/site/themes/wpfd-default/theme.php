<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

//-- No direct access
defined('ABSPATH') || die();

/**
 * Class WpfdThemeDefault
 */
class WpfdThemeDefault extends WpfdTheme
{
    /**
     * Theme name
     *
     * @var string
     */
    public $name = 'default';
    /**
     * Get tpl.php path for include
     *
     * @return string
     */
    public function getTplPath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tpl.php';
    }
}

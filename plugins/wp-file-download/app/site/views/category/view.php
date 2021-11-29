<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdViewCategory
 */
class WpfdViewCategory extends View
{

    /**
     * Display category
     *
     * @param string $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {
        $modelCat          = $this->getModel('categoryfront');
        $content           = new stdClass();
        $content->category = $modelCat->getCategory(Utilities::getInt('id'));
        echo wp_json_encode($content);
        die();
    }
}

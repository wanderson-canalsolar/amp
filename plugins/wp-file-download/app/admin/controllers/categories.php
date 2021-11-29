<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Controller;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerCategories
 */
class WpfdControllerCategories extends Controller
{
    /**
     * List all categories, used for gutenberg block.
     *
     * @return void
     */
    public function listCats()
    {
        $categoriesModel = $this->getModel();
        $hierarchy = $categoriesModel->getCategories();
        wp_send_json(array(
            'success' => true,
            'data'    => $hierarchy
        ));
        die();
    }
}

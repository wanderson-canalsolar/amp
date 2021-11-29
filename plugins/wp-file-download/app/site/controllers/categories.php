<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Controller;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerCategories
 */
class WpfdControllerCategories extends Controller
{
    /**
     * Get subs categories
     *
     * @return void
     */
    public function getSubs()
    {

        $modelCats = $this->getModel('categoriesfront');
        $cats      = $modelCats->getCategories(Utilities::getInt('dir'));
        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($cats) && count($cats)) {
            foreach ($cats as $cat) {
                $cat->count_child = $modelCats->getSubCategoriesCount($cat->term_id);
            }
        }
        echo json_encode($cats);
        die();
    }

    /**
     * Get Categories
     *
     * @return void
     */
    public function getCats()
    {
        $term  = array();
        $catId = Utilities::getInt('dir');

        $catModel             = $this->getModel('categoriesfront');
        $configModel          = $this->getModel('configfront');
        if ($catId === 0) {
            $currentCat = new stdClass;
            $currentCat->term_id = 0;
            $currentCat->slug = 'all_0';
            $currentCat->name = esc_html__('All Categories', 'wpfd');
        } else {
            $currentCat           = get_term($catId, 'wpfd-category');
        }
        $currentCat->children = array();
        $config               = $configModel->getGlobalConfig();

        /**
         * Filters allow to change ordering direction of categories
         *
         * @param string
         *
         * @return string
         */
        $orderDirection = apply_filters('wpfd_categories_order', 'asc');

        /**
         * Filters allow to change order column of categories
         *
         * @param string
         *
         * @return string
         */
        $orderBy = apply_filters('wpfd_categories_orderby', 'term_group');

        if (!is_wp_error($currentCat)) {
            $hierarchy = $catModel->getCategoriesHierarchy($catId, $orderBy, $orderDirection, $config);
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            if (is_countable($hierarchy) && ($hierarchy)) {
                $currentCat->children = $hierarchy;
            }
            $term[] = $currentCat;
        }

        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($term) && count($term) > 0) {
            wp_send_json(array(
                'success' => true,
                'data'    => $term
            ));
        } else {
            wp_send_json(array(
                'success' => false,
                'message' => esc_html__('No category found', 'wpfd')
            ));
        }
    }

    /**
     * Get parents categories
     *
     * @return void
     */
    public function getParentsCats()
    {
        $modelCats = $this->getModel('categoriesfront');
        $cats      = $modelCats->getParentsCat(Utilities::getInt('id'), Utilities::getInt('displaycatid'));
        $cats      = array_reverse($cats);
        echo json_encode($cats);
        die();
    }
}

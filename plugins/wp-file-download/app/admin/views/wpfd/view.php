<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die();

/**
 * Class WpfdViewWpfd
 */
class WpfdViewWpfd extends View
{
    /**
     * Render view wpfd
     *
     * @param null $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {
        Application::getInstance('Wpfd');
        $modelCat           = $this->getModel('categories');
        $modelConfig        = $this->getModel('config');

        $this->categories   = $modelCat->getCategories();
        $this->globalConfig = $modelConfig->getConfig();
        if ((int) WpfdBase::loadValue($this->globalConfig, 'file_count', 0) !== 0) {
            if ($this->categories && !empty($this->categories)) {
                $this->categories = $this->countFileRefCat($this->categories);
            }
        }

        if (defined('WPFD_ADMIN_UI') && WPFD_ADMIN_UI === true) {
            $tpl = 'ui-default';
        }

        parent::render($tpl);
    }

    /**
     * Count file referent to category
     *
     * @param array $categories Categories
     *
     * @return array
     */
    public function countFileRefCat($categories)
    {
        $modelCategory = $this->getModel('category');
        foreach ($categories as $keycat => $category) {
            $description = json_decode($category->description, true);
            $fileCount   = 0;
            if (!empty($description) && isset($description['refToFile'])) {
                $listCatRef = $description['refToFile'];
                if (!empty($listCatRef) && $listCatRef) {
                    foreach ($listCatRef as $key => $lst) {
                        $cat = $modelCategory->getCategory($key);
                        if ($cat && !empty($cat)) {
                            $lstFile = $modelCategory->checkListFiles($key, $lst, $category->term_id);
                            if (!empty($lstFile)) {
                                $fileCount = $fileCount + count($lstFile);
                            }
                        }
                    }
                }
            }

            /**
             * Filter to check category source
             *
             * @param integer Term id
             *
             * @return string
             *
             * @internal
             *
             * @ignore
             */
            $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $category->term_id);
            if (in_array($categoryFrom, wpfd_get_support_cloud())) {
                /**
                 * Filters get files from cloud category
                 *
                 * @param integer Category id
                 *
                 * @internal
                 *
                 * @return array
                 */
                $files = apply_filters('wpfd_addon_get_files', $category->term_id, $categoryFrom, false);
                // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                if (is_countable($files)) {
                    $fileCount = $fileCount + count($files);
                }
            }
            $categories[$keycat]->count = $category->count + (int) $fileCount;
        }

        return $categories;
    }
}

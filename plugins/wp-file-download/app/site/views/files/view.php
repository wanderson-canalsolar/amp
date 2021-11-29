<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdViewFiles
 */
class WpfdViewFiles extends View
{

    /**
     * Display files
     *
     * @param string $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {
        $id_category   = Utilities::getInt('id');
        $root_category = Utilities::getInt('rootcat');

        $app           = Application::getInstance('Wpfd');
        $modelCat      = $this->getModel('categoryfront');
        $modelFiles    = $this->getModel('filesfront');
        $modelTokens   = $this->getModel('tokens');
        $modelConfig   = $this->getModel('configfront');
        if ($id_category === 0) {
            $root = new \stdClass;
            $root->name = get_bloginfo('name');
            $root->slug = sanitize_title(get_bloginfo('name'));
            $root->term_id = 'all_0';
            $category = new WP_Term($root);
        } else {
            $category = $modelCat->getCategory($id_category);
        }
        $rootcategory  = $modelCat->getCategory($root_category);

        $path_wpfdhelper = $app->getPath() . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'helpers';
        $path_wpfdhelper .= DIRECTORY_SEPARATOR . 'WpfdHelper.php';
        require_once $path_wpfdhelper;
        if (!WpfdHelper::checkCategoryAccess($category)) {
            $content           = new stdClass();
            $content->files    = array();
            $content->category = new stdClass();
            echo json_encode($content);
            die();
        }

        $token       = $modelTokens->getOrCreateNew();
        $orderCol    = Utilities::getInput('orderCol', 'GET', 'none');
        $orderDir    = Utilities::getInput('orderDir', 'GET', 'none');
        $ordering    = $orderCol !== null ? $orderCol : $category->ordering;
        $orderingdir = $orderDir !== null ? $orderDir : $category->orderingdir;

        $description = json_decode($category->description, true);
        $lstAllFile  = null;
        if (!empty($description) && isset($description['refToFile'])) {
            if (isset($description['refToFile'])) {
                $listCatRef = $description['refToFile'];
                $lstAllFile = $this->getAllFileRef($modelFiles, $listCatRef, $ordering, $orderingdir);
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
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $id_category);
        if ($categoryFrom === 'googleDrive') {
            $files             = apply_filters(
                'wpfdAddonGetListGoogleDriveFile',
                $id_category,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
            $content           = new stdClass();
            $content->files    = $files;
            $content->category = $category;
        } elseif ($categoryFrom === 'dropbox') {
            $files             = apply_filters(
                'wpfdAddonGetListDropboxFile',
                $id_category,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
            $content           = new stdClass();
            $content->files    = $files;
            $content->category = $category;
        } elseif ($categoryFrom === 'onedrive') {
            $files             = apply_filters(
                'wpfdAddonGetListOneDriveFile',
                $id_category,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
            $content           = new stdClass();
            $content->files    = $files;
            $content->category = $category;
        } elseif ($categoryFrom === 'onedrive_business') {
            $files             = apply_filters(
                'wpfdAddonGetListOneDriveBusinessFile',
                $id_category,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
            $content           = new stdClass();
            $content->files    = $files;
            $content->category = $category;
        } else {
            $content           = new stdClass();
            if ($id_category === 0) {
                $content->files = $modelFiles->getFilesAllCat($ordering, $orderingdir);
            } else {
                $content->files    = $modelFiles->getFiles($id_category, $ordering, $orderingdir);
            }

            $content->category = $category;
        }
        if ($lstAllFile && !empty($lstAllFile)) {
            $content->files = array_merge($lstAllFile, $content->files);
        }

        // Sort before cut
        $reverse = strtoupper($orderingdir) === 'DESC' ? true : false;
        if ($ordering === 'size') {
            $content->files = wpfd_sort_by_property($content->files, 'size', 'ID', $reverse);
        } elseif ($ordering === 'version') {
            $content->files = wpfd_sort_by_property($content->files, 'versionNumber', 'ID', $reverse);
        } elseif ($ordering === 'hits') {
            $content->files = wpfd_sort_by_property($content->files, 'hits', 'ID', $reverse);
        } elseif ($ordering === 'ext') {
            $content->files = wpfd_sort_by_property($content->files, 'ext', 'ID', $reverse);
        } elseif ($ordering === 'description') {
            $content->files = wpfd_sort_by_property($content->files, 'description', 'ID', $reverse);
        } elseif ($ordering === 'title') {
            if ($reverse) {
                // Descending order
                usort($content->files, function ($a, $b) {
                    // String comparisons using a "natural order" algorithm
                    return strnatcmp($b->post_title, $a->post_title);
                });
            } else {
                // Ascending order
                usort($content->files, function ($a, $b) {
                    // String comparisons using a "natural order" algorithm
                    return strnatcmp($a->post_title, $b->post_title);
                });
            }
        } elseif ($ordering === 'created_time') {
            if ($reverse) {
                usort($content->files, array($this, 'cmpCreatedDesc'));
            } else {
                usort($content->files, array($this, 'cmpCreated'));
            }
        } elseif ($ordering === 'modified_time') {
            if ($reverse) {
                usort($content->files, array($this, 'cmpModifiedDesc'));
            } else {
                usort($content->files, array($this, 'cmpModified'));
            }
        }

        $global_settings = $modelConfig->getGlobalConfig();
        $limit           = $global_settings['paginationnunber'];
        $page            = Utilities::getInt('page');

        $total  = ceil(count($content->files) / $limit);
        $page   = $page ? $page : 1;
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }
        if (!$rootcategory || (isset($rootcategory->params['theme']) && $rootcategory->params['theme'] !== 'tree')) {
            $content->files = array_slice($content->files, $offset, $limit);
        }
        // Crop file titles
        foreach ($content->files as $i => $file) {
            if ((int) $global_settings['restrictfile'] === 1) {
                $user = wp_get_current_user();
                $user_id = $user->ID;
                $canview = isset($file->canview) ? $file->canview : 0;
                $canview = array_map('intval', explode(',', $canview));
                if ($user_id) {
                    if (!(in_array($user_id, $canview) || in_array(0, $canview))) {
                        unset($content->files[$i]);
                        continue;
                    }
                } else {
                    if (!in_array(0, $canview)) {
                        unset($content->files[$i]);
                        continue;
                    }
                }
            }
            $content->files[$i]->crop_title = $file->post_title;
            if ($root_category) {
                $content->files[$i]->crop_title = WpfdBase::cropTitle(
                    $rootcategory->params,
                    $rootcategory->params['theme'],
                    $file->post_title
                );
            } else {
                $content->files[$i]->crop_title = WpfdBase::cropTitle(
                    $category->params,
                    $category->params['theme'],
                    $file->post_title
                );
            }

            if (isset($file->file_custom_icon) && $file->file_custom_icon !== '') {
                if (strpos($file->file_custom_icon, site_url()) !== 0) {
                    $content->files[$i]->file_custom_icon = site_url() . $file->file_custom_icon;
                }
            }
        }

        $content->pagination = wpfd_category_pagination(
            array(
                'base'    => '',
                'format'  => '',
                'current' => max(1, $page),
                'total'   => $total,
                'sourcecat' => $root_category
            )
        );
        echo wp_json_encode($content);
        die();
    }

    /**
     * Get all file referent category
     *
     * @param object $model       Files Model
     * @param array  $listCatRef  List cat ref
     * @param string $ordering    Ordering
     * @param string $orderingdir Ordering direction
     *
     * @return array
     */
    public function getAllFileRef($model, $listCatRef, $ordering, $orderingdir)
    {
        $lstAllFile = array();
        if (is_array($listCatRef) && !empty($listCatRef)) {
            foreach ($listCatRef as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    $lstFile    = $model->getFiles($key, $ordering, $orderingdir, $value);
                    $lstAllFile = array_merge($lstFile, $lstAllFile);
                }
            }
        }

        return $lstAllFile;
    }

    /**
     * Method compare Create date
     *
     * @param object $a First file object
     * @param object $b Second file object
     *
     * @return integer
     */
    private function cmpCreated($a, $b)
    {
        return (strtotime($a->created_time) < strtotime($b->created_time)) ? -1 : 1;
    }

    /**
     * Method compare Create date desc
     *
     * @param object $a First file object
     * @param object $b Second file object
     *
     * @return integer
     */
    private function cmpCreatedDesc($a, $b)
    {
        return (strtotime($a->created_time) > strtotime($b->created_time)) ? -1 : 1;
    }

    /**
     * Method compare Modified date
     *
     * @param object $a First file object
     * @param object $b Second file object
     *
     * @return integer
     */
    private function cmpModified($a, $b)
    {
        return (strtotime($a->modified_time) < strtotime($b->modified_time)) ? -1 : 1;
    }

    /**
     * Method compare Modified date desc
     *
     * @param object $a First file object
     * @param object $b Second file object
     *
     * @return integer
     */
    private function cmpModifiedDesc($a, $b)
    {
        return (strtotime($a->modified_time) > strtotime($b->modified_time)) ? -1 : 1;
    }
}

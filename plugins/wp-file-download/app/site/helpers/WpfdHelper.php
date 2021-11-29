<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

defined('ABSPATH') || die();

/**
 * Class WpfdHelper
 */
class WpfdHelper
{
    /**
     * Method check category access
     *
     * @param object $category Category
     *
     * @return boolean
     */
    public static function checkCategoryAccess($category)
    {
        if ((int) $category->access === 1) {
            $user  = wp_get_current_user();
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows        = array_intersect($roles, $category->roles);
            $params        = json_decode($category->description, true);
            $allows_single = false;
            if (isset($params['canview']) && $params['canview'] !== '') {
                if (((int) $params['canview'] !== 0) && (int) $params['canview'] === $user->ID) {
                    $allows_single = true;
                }
            }
            if ($allows || $allows_single) {
                return true;
            }

            return false;
        }

        return true;
    }
}

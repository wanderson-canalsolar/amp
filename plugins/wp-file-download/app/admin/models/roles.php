<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdModelRoles
 */
class WpfdModelRoles extends Model
{
    /**
     * Save access, roles for category
     *
     * @param integer $id_cat     Cat id
     * @param string  $visibility Visibility
     * @param array   $roles      Roles
     *
     * @return boolean
     */
    public function save($id_cat, $visibility, $roles)
    {
        global $wpdb;
        $term_meta           = get_option('taxonomy_' . $id_cat);
        $term_meta['access'] = $visibility;
        $term_meta['roles']  = $roles;
        update_option('taxonomy_' . $id_cat, $term_meta);

        return true;
    }
}

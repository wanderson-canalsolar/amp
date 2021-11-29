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
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerSearch
 */
class WpfdControllerSearch extends Controller
{
    /**
     * Search query
     *
     * @return void
     */
    public function query()
    {
        $modelConfig  = Model::getInstance('configfront');
        $searchConfig = $modelConfig->getSearchConfig();

        $filters = array();
        $q       = Utilities::getInput('q', 'POST', 'string');

        if (!empty($q)) {
            $filters['q'] = urlencode($q);
        }
        $catid = Utilities::getInput('catid', 'POST', 'string');
        if (!empty($catid)) {
            $filters['catid'] = $catid;
        }

        $ftags = Utilities::getInput('ftags', 'POST', 'none');
        if (is_array($ftags)) {
            $ftags = array_unique($ftags);
            $ftags = implode(',', $ftags);
        } else {
            $ftags = Utilities::getInput('ftags', 'POST', 'string');
        }

        if (!empty($ftags)) {
            $filters['ftags'] = $ftags;
        }
        $cfrom = Utilities::getInput('cfrom', 'POST', 'string');
        if (!empty($cfrom)) {
            $filters['cfrom'] = $cfrom;
        }
        $cto = Utilities::getInput('cto', 'POST', 'string');
        if (!empty($cto)) {
            $filters['cto'] = $cto;
        }
        $ufrom = Utilities::getInput('ufrom', 'POST', 'string');
        if (!empty($ufrom)) {
            $filters['ufrom'] = $ufrom;
        }
        $uto = Utilities::getInput('uto', 'POST', 'string');
        if (!empty($uto)) {
            $filters['uto'] = $uto;
        }
        $doSearch = false;
        if (!empty($filters)) {
            $doSearch = true;
        }
        wp_redirect(add_query_arg($filters, home_url('?page_id=' . $searchConfig['search_page'])));
        exit();
    }

    /**
     * Get tags by category Id
     *
     * @return void
     */
    public function getTagByCatId()
    {
        global $wpdb;

        $catId = Utilities::getInput('catId', 'GET', 'string');
        if (!is_numeric($catId)) {
            // todo: get tags for cloud
            wp_send_json(array('success' => false, 'message' => 'No tags in this category found!'), 200);
        }
        $term  = get_term($catId, 'wpfd-category', OBJECT);

        if (!is_wp_error($term)) {
            $cats = get_term_children($term->term_id, 'wpfd-category');

            if (!is_wp_error($cats) && !empty($cats)) {
                $cats[] = $term->term_id;
                $terms = implode(',', esc_sql($cats));
            } else {
                $terms = (string) esc_sql($term->term_id);
            }
            if (empty($terms)) {
                wp_send_json(array('success' => false, 'message' => 'No tags in this category found!'), 200);
            }
            // phpcs:disable WordPress.Security.EscapeOutput.NotPrepared -- Esc ready above
            $tags = $wpdb->get_results(
                'SELECT DISTINCT t.* from ' . $wpdb->terms . ' as t
                    INNER JOIN ' . $wpdb->term_relationships . ' as s on t.term_id = s.term_taxonomy_id
                    INNER JOIN ' . $wpdb->term_taxonomy . ' as x on x.term_taxonomy_id = s.term_taxonomy_id
                    WHERE s.object_id IN (SELECT p.ID from ' . $wpdb->posts . ' as p
                    INNER JOIN ' . $wpdb->term_relationships . ' as r on p.ID = r.object_id
                    WHERE r.term_taxonomy_id IN (' . $terms . '))
                    AND x.taxonomy = \'wpfd-tag\'
                    ORDER BY t.name ASC;'
            );
            // phpcs:enable

            if ($tags) {
                $tagsArray = array();
                foreach ($tags as $tag) {
                    $tagsArray[] = array(
                        'name' => $tag->name,
                        'slug' => $tag->slug
                    );
                }
                wp_send_json(array('success' => true, 'tags' => $tagsArray), 200);
            }
        }

        wp_send_json(array('success' => false, 'message' => 'No tags in this category found!'), 200);
    }
}

<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdModelStatistics
 */
class WpfdModelStatistics extends Model
{

    /**
     * Total count
     *
     * @var integer
     */
    private $total = 0;

    /**
     * Pagination for query
     *
     * @var string
     */
    private $pagination = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        add_filter('posts_clauses', array($this, 'postClauses'), 0);
    }

    /**
     * Get selection values
     *
     * @return array
     */
    public function getSelectionValues()
    {
        $selection = Utilities::getInput('selection', 'POST', 'string');
        $options   = array();
        if (!empty($selection)) {
            if ($selection === 'category') {
                $cats = get_terms('wpfd-category', array(
                    'hide_empty' => false,
                ));
                if ($cats) {
                    foreach ($cats as $cat) {
                        $options[$cat->term_id] = $cat->name;
                    }
                }
            } elseif ($selection === 'files') {
                $args = array(
                    'post_type'        => 'wpfd_file',
                    'post_status'      => 'publish',
                    'suppress_filters' => false,
                    'posts_per_page'   => -1
                );

                $files = get_posts($args);
                if ($files) {
                    foreach ($files as $file) {
                        $options[$file->ID] = $file->post_title;
                    }
                }
            } elseif ($selection === 'users') {
                global $wpdb;
                $users = $wpdb->get_results($wpdb->prepare(
                    'SELECT ID, display_name FROM ' . $wpdb->prefix . 'users WHERE ID IN (SELECT uid FROM ' . $wpdb->prefix . 'wpfd_statistics WHERE uid > %d GROUP BY uid)',
                    0
                ));
                if ($users) {
                    foreach ($users as $user) {
                        $options[$user->ID] = $user->display_name;
                    }
                }
            }
        }
        /**
         * Statistics get selection value
         *
         * @param array
         * @param string
         */
        $options = apply_filters('wpfd_statistics_get_selection_value', $options, $selection);

        return $options;
    }

    /**
     * Get items
     *
     * @return mixed
     */
    public function getItems()
    {
        $args          = array(
            'post_type'        => 'wpfd_file',
            'post_status'      => 'publish',
            'suppress_filters' => false,
            'posts_per_page'   => -1
        );
        $paged         = Utilities::getInput('paged', 'POST', 'string');
        $paged         = $paged !== '' ? $paged : 1;
        $args['paged'] = (int) $paged;

        // Filter by search in title.
        $search = Utilities::getInput('query', 'POST', 'string');
        if (!empty($search)) {
            $args['s'] = $search;
        }
        $selection = Utilities::getInput('selection', 'POST', 'string');
        if (!empty($selection)) {
            $selection_value = Utilities::getInput('selection_value', 'POST', 'none');

            if (!empty($selection_value)) {
                if ($selection === 'files') {
                    $args['post__in'] = $selection_value;
                } elseif ($selection === 'category') {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy'         => 'wpfd-category',
                            'terms'            => $selection_value,
                            'field'            => 'term_id',
                            'include_children' => true
                        )
                    );
                } elseif ($selection === 'users') {
                    // Get file id downloaded by users id
                    global $wpdb;
                    $users = array_map(function ($id) {
                        return (int) esc_attr($id);
                    }, $selection_value);

                    // Clean user ids for safe query
                    $placeHolders = implode(', ', array_fill(0, count($users), '%d'));
                    $fileIds = $wpdb->get_results(
                        $wpdb->prepare(
                            'SELECT related_id FROM ' . $wpdb->prefix . 'wpfd_statistics WHERE uid IN (' . $placeHolders . ') GROUP BY related_id', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Escaped above
                            $users
                        )
                    );

                    $fileIds = array_map(function ($file) {
                        return $file->related_id;
                    }, $fileIds);

                    $args['post__in'] = $fileIds;
                }
            }
            /**
             * Statistics get selection value arguments
             *
             * @param array
             * @param string
             * @param string
             */
            $args = apply_filters('wpfd_statistics_get_selection_arguments', $args, $selection, $selection_value);
        }

        $this->totalFiles = count(get_posts($args));

        $limit                  = Utilities::getInput('limit', 'POST', 'string');
        if ($limit === 'all') {
            $limit = -1;
        }
        $limit                  = ($limit !== '') ? $limit : 5;
        $args['posts_per_page'] = (int) $limit;

        if ($this->totalFiles > $limit) {
            $this->total = ceil($this->total / $limit);
        } else {
            $this->total = $this->totalFiles;
        }

        // Add the list ordering clause.
        $orderCol        = Utilities::getInput('filter_order', 'POST', 'string');
        $orderDirn       = Utilities::getInput('filter_order_dir', 'POST', 'string');
        $orderCol        = $orderCol !== '' ? $orderCol : 'ID';
        $orderDirn       = $orderDirn !== '' ? $orderDirn : 'DESC';
        $args['orderby'] = $orderCol;
        $args['order']   = $orderDirn;
        $query           = new WP_Query($args);
        $items           = $query->get_posts();

        if ($orderCol === 'count_hits') {
            usort($items, function ($a, $b) {
                return $a->count_hits > $b->count_hits;
            });
            if ($orderDirn === 'desc') {
                $items = array_reverse($items);
            }
        }
        $this->pagination = paginate_links(array(
            'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'total'        => $query->max_num_pages,
            'current'      => max(1, $paged),
            'format'       => '?paged=%#%',
            'show_all'     => false,
            'type'         => 'plain',
            'end_size'     => 2,
            'mid_size'     => 1,
            'prev_next'    => true,
            'prev_text'    => sprintf('<i></i> %1$s', __('<', 'wpfd')),
            'next_text'    => sprintf('%1$s <i></i>', __('>', 'wpfd')),
            'add_args'     => false,
            'add_fragment' => '',
        ));

        return $items;
    }

    /**
     * Get pagination
     *
     * @return string
     */
    public function getPagination()
    {
        return $this->pagination;
    }
    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Get download count by date
     *
     * @param array $fids Fids
     *
     * @return array
     */
    public function getDownloadCountByDate($fids)
    {
        global $wpdb;
        $cleanFids = array_map(function ($fid) {
            return (int) $fid;
        }, $fids);

        $sql   = 'SELECT f.ID, ch.date, ch.uid, ch.count FROM ' . $wpdb->posts . ' AS f INNER JOIN ';
        $sql   .= $wpdb->prefix . 'wpfd_statistics AS ch ON ch.related_id = f.ID WHERE f.ID IN (';
        $sql   .= implode(',', $cleanFids) . ") AND f.post_type='wpfd_file'";

        $selection = Utilities::getInput('selection', 'POST', 'string');
        if (!empty($selection)) {
            $selection_value = Utilities::getInput('selection_value', 'POST', 'none');
            if (!empty($selection_value) && $selection === 'users') {
                $users = array_map(function ($id) {
                    return (int) esc_attr($id);
                }, $selection_value);
                $placeHolders = implode(', ', array_fill(0, count($users), '%d'));
                // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Escaped above
                $sql .= $wpdb->prepare(' AND ch.uid IN (' . $placeHolders . ')', $users);
            }
            $type = apply_filters('wpfd_statistics_type', 'default', $selection, $selection_value);
            $sql .= $wpdb->prepare(' AND ( ch.type = %s )', $type);
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.NotPrepared -- input cleaned
        $items = $wpdb->get_results($sql);

        $rows  = array();
        if (count($items)) {
            foreach ($items as $item) {
                if (!isset($rows[$item->date][$item->ID])) {
                    $rows[$item->date][$item->ID] = $item->count;
                } else {
                    $rows[$item->date][$item->ID] += $item->count;
                }
            }
        }

        return $rows;
    }

    /**
     * Append clauses query
     *
     * @param array $args Args
     *
     * @return array
     */
    public function postClauses($args)
    {
        global $wpdb;
        $args['fields'] .= ', SUM(ch.count) AS count_hits';

        $args['join']   .= 'INNER JOIN ' . $wpdb->prefix  . 'wpfd_statistics AS ch ON (ch.related_id = ' . $wpdb->posts . '.ID)';

        $date_from      = Utilities::getInput('fdate', 'POST', 'string');
        $date_to        = Utilities::getInput('tdate', 'POST', 'string');
        if ($date_from) {
            $args['where'] .= " AND ( ch.date >= '" . esc_sql($date_from) . "') ";
        }
        if ($date_to) {
            $args['where'] .= " AND ( ch.date <= '" . esc_sql($date_to) . "') ";
        }
        if (empty($date_from) && empty($date_to)) {
            $dfrom         = date('Y-m-d', strtotime('-1 month', time()));
            $dto           = date('Y-m-d');
            $args['where'] .= " AND ( ch.date >= '" . esc_sql($dfrom) . "') ";
            $args['where'] .= " AND ( ch.date <= '" . esc_sql($dto) . "') ";
        }
        // Add the list ordering clause.
        $orderCol        = Utilities::getInput('filter_order', 'POST', 'string');
        $orderDirn       = Utilities::getInput('filter_order_dir', 'POST', 'string');
        $orderCol        = $orderCol !== '' ? $orderCol : 'ID';
        $orderDirn       = $orderDirn !== '' ? $orderDirn : 'DESC';
        $args['orderby'] = ' ' . esc_sql($orderCol) . ' ' . esc_sql($orderDirn);
        // Set type
        $type = 'default';
        // Default group by post id
        $groupBy = $wpdb->posts .'.ID';
        $selection = Utilities::getInput('selection', 'POST', 'string');
        if (!empty($selection)) {
            $selection_value = Utilities::getInput('selection_value', 'POST', 'none');
            if (!empty($selection_value) && $selection === 'users') {
                $args['fields'] .= ', ch.uid, u.display_name';
                $args['join']   .= ' INNER JOIN ' . $wpdb->prefix  . 'users AS u ON (ch.uid = u.ID)';
                $users = array_map(function ($id) {
                    return (int) esc_attr($id);
                }, $selection_value);
                $placeHolders = implode(', ', array_fill(0, count($users), '%d'));
                // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Escaped above
                $args['where'] .= $wpdb->prepare(' AND ch.uid IN (' . $placeHolders . ') ', $users);
                $groupBy .= ', u.ID';
            }

            /**
             * Statistics get selection posts clauses
             *
             * @param array
             * @param string
             * @param string
             */
            $args = apply_filters('wpfd_statistics_get_selection_posts_clauses', $args, $selection, $selection_value);

            /**
             * Change statistics type
             *
             * @param string
             * @param string
             * @param string
             */
            $type = apply_filters('wpfd_statistics_type', $type, $selection, $selection_value);
        }

        $args['where'] .= $wpdb->prepare(' AND ( ch.type = %s )', $type);
        $args['groupby'] = $groupBy;

        return $args;
    }

    /**
     * Get all download count
     *
     * @param string $type Type to get statistics count
     *
     * @return mixed
     */
    public function getTotalByType($type = 'default')
    {
        global $wpdb;
        $items = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT date, SUM(count) as count, uid FROM ' . $wpdb->prefix . 'wpfd_statistics WHERE type=%s GROUP BY date',
                $type
            )
        );

        return $items;
    }
}

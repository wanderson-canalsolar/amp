<?php
/**
 * WP Framework
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdModelFts
 */
class WpfdModelFts extends Model
{
    /**
     * Prefix
     *
     * @var string
     */
    public $prefix = '';
    /**
     * Max word to parser
     *
     * @var integer
     */
    public $max_word_length = 32;
    /**
     * Last error
     *
     * @var string
     */
    public $error = '';
    /**
     * Lock time
     *
     * @var integer
     */
    public $lock_time = 300; // 5min
    /**
     * Log time
     *
     * @var array
     */
    public $timelog = array();

    /**
     * Stop words
     *
     * @var array
     */
    protected $stops = array();
    /**
     * Log
     *
     * @var array
     */
    protected $log = array();
    /**
     * Table locked
     *
     * @var boolean
     */
    protected $is_lock = true;

    /**
     * Get data prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        global $wpdb;

        return $wpdb->prefix . 'wpfd_';
    }

    /**
     * Parse search terms
     *
     * @param array $keywords Keywords
     *
     * @return array
     */
    public function parseSearchTerms($keywords)
    {
        $keys = array();

        foreach ($keywords as $key) {
            $keys[] = mb_strtolower(trim($key), 'utf-8');
        }

        return $keys;
    }

    /**
     * SQL parts
     *
     * @param WP_Query $wpq     WP Query object
     * @param array    $cw      Words
     * @param string   $nocache No cache
     *
     * @return array
     */
    public function sqlParts(&$wpq, $cw, $nocache)
    {
        global $wpdb;
        $prefix = $this->getPrefix();

        $q = &$wpq->query_vars;

        //$txnc = $nocache ? ' SQL_NO_CACHE' : '';
        $txnc    = '';
        $join    = '';
        $fields  = '';
        $matches = array();
        if ((!empty($q['s']))) {
            $qs = stripslashes($q['s']);
            if (empty($q['s']) && $wpq->is_main_query()) {
                $qs = urldecode($qs);
            }

            $qs                      = str_replace(array("\r", "\n"), '', $qs);
            $q['search_terms_count'] = 1;
            if (!empty($q['sentence'])) {
                $q['search_terms'] = array($qs);
            } else {
                if (preg_match_all('/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $qs, $matches)) {
                    // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                    $q['search_terms_count'] = is_countable($matches[0]) ? count($matches[0]) : 0;
                    $q['search_terms']       = $this->parseSearchTerms($matches[0]);
                    // if the search string has only short terms or stopwords,
                    // or is 10+ terms long, match it as sentence
                    if (empty($q['search_terms']) || count($q['search_terms']) > 9) {
                        $q['search_terms'] = array($qs);
                    }
                } else {
                    $q['search_terms'] = array($qs);
                }
            }
            // Decode terms
            $minchars = 0;
            $ts       = array();
            foreach ($q['search_terms'] as $t) {
                $f = !empty($q['exact']) ? 1 : 0;
                if (mb_substr($t, 0, 1, 'utf-8') === '"') {
                    $t2 = explode(' ', trim($t, '"'));
                    $f  = 1;
                } else {
                    $t2 = explode(' ', trim($t));
                }
                if (is_array($t2)) {
                    foreach ($t2 as $tt) {
                        if (mb_strlen(trim($tt), 'utf-8') >= $minchars) {
                            if ($f) {
                                $ts[] = array(1, trim($tt));
                            } else {
                                $ts[] = array(0, trim($tt));
                            }
                        }
                    }
                }
            }
            $q['search_terms']       = $ts;
            $q['search_terms_count'] = count($ts);

            $j = '';
            if ($q['search_terms_count'] > 0) {
                $q['search_orderby_title'] = array();
                $i                         = 1;
                foreach ($q['search_terms'] as $term) {
                    if ($i > 1) {
                        $j .= ' inner join ';
                    }
                    if (false) { // phpcs:ignore Generic.CodeAnalysis.UnconditionalIfStatement.Found -- Alway use exact mode - performance issue in long term search
                        // Like
                        $j .= '(
                                 select ' . $txnc . '
                                    ds1.id,
                                    ds1.index_id,
                                    ds1.token,
                                    v1.f
                                 from `' . $prefix . 'words` w1    
                                 left join `' . $prefix . 'vectors` v1
                                    on v1.wid = w1.id
                                 left join `' . $prefix . 'docs` ds1
                                    on v1.did = ds1.id
                                 where
                                    (w1.`word` like "%' . $wpdb->esc_like($term[1]) . '%")
                                 ) t' . $i;
                    } else {
                        // Exact equality
                        $j .= '(
                                select ' . $txnc . '
                                    ds1.id,
                                    ds1.index_id,
                                    ds1.token,
                                    v1.f
                                from `' . $prefix . 'words` w1    
                                left join `' . $prefix . 'vectors` v1
                                    on v1.wid = w1.id
                                left join `' . $prefix . 'docs` ds1
                                    on v1.did = ds1.id
                                    where
                                    (w1.`word` = "' . $wpdb->esc_like($term[1]) . '")
                                ) t' . $i;
                    }
                    if ($i > 1) {
                        $j .= ' on t' . $i . '.id = t1.id';
                    }

                    $i++;
                }

                $j .= ' group by t1.index_id
                        ) t3
                            on t3.index_id = fi.id
                        ) wpfd_t
                            on wpfd_t.tid = ' . $wpdb->posts . '.ID';

                $fields = ', wpfd_t.relev ';
            }

            $i--;
            if ($i < 2) {
                $relev = 't1.f';
            } else {
                $sum = array();
                for ($ii = 1; $ii <= $i; $ii++) {
                    $sum[] = 't' . $ii . '.f';
                }
                $relev = '(' . implode('+', $sum) . ') / ' . count($sum);
            }
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            if (is_countable($cw) && count($cw) > 1) {
                $x = array();
                foreach ($cw as $k => $d) {
                    //if(t1.token = "post_title", 100, 50)
                    $x[] = ' when "' . $k . '" then ' . floatval($d);
                }
                $rcv = ' (case t1.token ' . implode('', $x) . ' else 1 end)';
            } else {
                $rcv = 1;
            }

            $jhdr = ' left join (
                        select ' . $txnc . '
                            fi.tid,
                            t3.relev
                        from `' . $prefix . 'index` fi
                        inner join (
                            select
                                t1.index_id, 
                                sum(' . $relev . ' * ' . $rcv . ') relev
                            from ';

            $join .= $jhdr . $j;
        } else {
            $issearch = 0;
        }

        $parts = array(
            'token'        => md5(time() . '|' . uniqid('session')),
            'issearch'     => isset($issearch) ? $issearch : 1,
            'nocache'      => $nocache,
            'join'         => $join,
            'select'       => ' and (not isnull(wpfd_t.tid))',
            'orderby'      => ' (wpfd_t.relev) desc',
            'fields'       => $fields,
            'sql_no_cache' => $nocache ? ' SQL_NO_CACHE' : '',
        );

        return $parts;
    }

    /**
     * Sql joins
     *
     * @param string   $join Join
     * @param WP_Query $wpq  WP query object
     * @param array    $cw   Words
     *
     * @return string
     */
    public function sqlJoins($join, &$wpq, $cw)
    {
        if ((isset($wpq->wpfd_session['token'])) && ($wpq->wpfd_session['issearch'])) {
            return $join . $wpq->wpfd_session['join'];
        }

        return $join;
    }

    /**
     * Constructing SQL search part
     *
     * @param string   $search Search SQL from WP
     * @param WP_Query $wpq    WP query object
     *
     * @return string
     */
    public function sqlSelect($search, &$wpq)
    {
        if ((isset($wpq->wpfd_session['token'])) && ($wpq->wpfd_session['issearch'])) {
            $search = $wpq->wpfd_session['select'];
        }

        return $search;
    }

    /**
     * Sql Order by
     *
     * @param string   $orderby Order by
     * @param WP_Query $wpq     Wp query
     *
     * @return string
     */
    public function sqlOrderby($orderby, &$wpq)
    {
        if ((isset($wpq->wpfd_session['token'])) && ($wpq->wpfd_session['issearch'])) {
            $orderby = $wpq->wpfd_session['orderby'];
        }

        return $orderby;
    }

    /**
     * Sql pre posts
     *
     * @param WP_Query $wpq                 Wp query
     * @param array    $cw                  Words
     * @param boolean  $includeGlobalSearch Include global search
     *
     * @return void
     */
    public function sqlPrePosts($wpq, $cw, $includeGlobalSearch)
    {
        if ($wpq->is_file_search) {
            if ((!isset($wpq->wpfd_session['token'])) || (!$wpq->wpfd_session['token'])) {
                $nocache = false;

                // Calculate data
                $sql_parts          = $this->sqlParts($wpq, $cw, $nocache);
                $sql_parts['token'] = md5(time() . '|' . uniqid('session'));
                $wpq->wpfd_session  = $sql_parts;
            }
        } elseif ($wpq->is_search && $includeGlobalSearch) {
            $types = array('post', 'page');

            if (isset($wpq->query_vars['post_type'])) {
                $types = $wpq->query_vars['post_type'];
            }

            if (is_array($types)) {
                $types[] = 'wpfd_file';
            }

            $wpq->set('post_type', $types);
        }
    }

    /**
     * Sql posts fields
     *
     * @param string   $fields Fields
     * @param WP_Query $wpq    Wp query
     *
     * @return string
     */
    public function sqlPostsFields($fields, &$wpq)
    {
        if ((isset($wpq->wpfd_session['token'])) && ($wpq->wpfd_session['issearch'])) {
            return $fields . $wpq->wpfd_session['fields'];
        }

        return $fields;
    }

    /**
     * Sql posts distinct
     *
     * @param string   $distinct Distinct
     * @param WP_Query $wpq      Wp query
     *
     * @return string
     */
    public function sqlPostsDistinct($distinct, &$wpq)
    {
//        if ((isset($wpq->wpfd_session['token'])) && ($wpq->wpfd_session['issearch'])) {
//            return str_replace('SQL_NO_CACHE', '', $distinct).$wpq->wpfd_session['sql_no_cache'];
//        }

        return $distinct;
    }

    /**
     * Sql the posts
     *
     * @param array    $posts Posts
     * @param WP_Query $wpq   Wp query
     *
     * @return array
     */
    public function sqlThePosts($posts, &$wpq)
    {
        if (isset($wpq->wpfd_session)) {
            $wpq->wpfd_session = null;
        }

        return $posts;
    }
}

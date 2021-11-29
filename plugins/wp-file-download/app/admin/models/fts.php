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
     * Max word length
     *
     * @var integer
     */
    public $max_word_length = 32;

    /**
     * Error log
     *
     * @var string
     */
    public $error = '';
    /**
     * Lock table timeout
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
     * Log index error
     *
     * @var string
     */
    public $index_error = '';

    /**
     * Stop word list
     *
     * @var array
     */
    protected $stops = array();
    /**
     * Log list
     *
     * @var array
     */
    protected $log = array();
    /**
     * Is table locked
     *
     * @var boolean
     */
    protected $is_lock = true;

    /**
     * Get table prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        global $wpdb;

        return $wpdb->prefix . 'wpfd_';
    }

    /**
     * Create tables
     *
     * @return boolean
     */
    public function createDbTables()
    {
        global $wpdb;

        $success = true;
        $sch     = $this->getDbScheme();

        foreach ($sch as $k => $d) {
            $q = 'drop table if exists `' . $this->getPrefix() . $k . '`';
            // phpcs:ignore WordPress.Security.EscapeOutput.NotPrepared -- No input
            $wpdb->query($q);
            // phpcs:ignore WordPress.Security.EscapeOutput.NotPrepared -- No input
            $wpdb->query($d['create2']);
            if ($wpdb->last_error) {
                $this->log('Can\'t create table "' . $this->getPrefix() . $k . '": ' . $wpdb->last_error);
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get db scheme
     *
     * @return array
     */
    public function getDbScheme()
    {
        global $wpdb;
        $collate = '';

        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) {
                $collate .= 'DEFAULT CHARACTER SET ' . $wpdb->charset;
            }
            if (!empty($wpdb->collate)) {
                $collate .= ' COLLATE ' . $wpdb->collate;
            }
        }
        $dbscheme = array(
            'docs'    => array(
                'cols'   => array(
                    // name => type, isnull, keys, default, extra
                    'id'       => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
                    'index_id' => array('int(10) unsigned', 'NO', 'MUL'),
                    'token'    => array('varchar(255)', 'NO', 'MUL'),
                    'n'        => array('int(10) unsigned', 'NO'),
                ),
                'index'  => array(
                    'PRIMARY'  => array(0, 'id'),
                    'token'    => array(1, 'token'),
                    'index_id' => array(1, 'index_id'),
                ),
                'create' => 'CREATE TABLE `' . $this->getPrefix() . 'docs` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `index_id` int(10) unsigned NOT NULL,
                                `token` varchar(255) NOT NULL,
                                `n` int(10) unsigned NOT NULL,
                                PRIMARY KEY  (`id`),
                                KEY `token` (`token`),
                                KEY `index_id` USING BTREE (`index_id`)
                            ) ' . $collate,
            ),
            'index'   => array(
                'cols'   => array(
                    'id'            => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
                    'tid'           => array('bigint(10) unsigned', 'NO', 'MUL'),
                    'tsrc'          => array('varchar(255)', 'NO', 'MUL'),
                    'tdt'           => array('datetime', 'NO', '', '0000-00-00 00:00:00'),
                    'build_time'    => array('int(11)', 'NO', 'MUL', '0'),
                    'update_dt'     => array('datetime', 'NO', '', '0000-00-00 00:00:00'),
                    'force_rebuild' => array('tinyint(4)', 'NO', 'MUL', '0'),
                    'locked_dt'     => array('datetime', 'NO', 'MUL', '0000-00-00 00:00:00'),
                ),
                'index'  => array(
                    'PRIMARY'         => array(0, 'id'),
                    'tid_tsrc_unique' => array(0, 'tid,tsrc'),
                    'tid'             => array(1, 'tid'),
                    'build_time'      => array(1, 'build_time'),
                    'force_rebuild'   => array(1, 'force_rebuild'),
                    'locked_dt'       => array(1, 'locked_dt'),
                    'tsrc'            => array(1, 'tsrc'),
                ),
                'create' => 'CREATE TABLE `' . $this->getPrefix() . 'index` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `tid` bigint(10) unsigned NOT NULL,
                                `tsrc` varchar(255) NOT NULL,
                                `tdt` datetime NOT NULL default \'0000-00-00 00:00:00\',
                                `build_time` int(11) NOT NULL default \'0\',
                                `update_dt` datetime NOT NULL default \'0000-00-00 00:00:00\',
                                `force_rebuild` tinyint(4) NOT NULL default \'0\',
                                `locked_dt` datetime NOT NULL default \'0000-00-00 00:00:00\',
                                PRIMARY KEY  (`id`),
                                UNIQUE KEY `tid_tsrc_unique` USING BTREE (`tid`,`tsrc`),
                                KEY `tid` (`tid`),
                                KEY `build_time` (`build_time`),
                                KEY `force_rebuild` (`force_rebuild`),
                                KEY `locked_dt` (`locked_dt`),
                                KEY `tsrc` USING HASH (`tsrc`)
                            ) ' . $collate,
            ),
            'stops'   => array(
                'cols'   => array(
                    'id'   => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
                    'word' => array('varchar(32)', 'NO', 'UNI'),
                ),
                'index'  => array(
                    'PRIMARY' => array(0, 'id'),
                    'word'    => array(0, 'word'),
                ),
                'create' => 'CREATE TABLE `' . $this->getPrefix() . 'stops` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `word` varchar(32) character set utf8 collate utf8_bin NOT NULL,
                                PRIMARY KEY  (`id`),
                                UNIQUE KEY `word` (`word`)
                            ) ' . $collate,
            ),
            'vectors' => array(
                'cols'   => array(
                    'wid' => array('int(10) unsigned', 'NO', 'PRI'),
                    'did' => array('int(10) unsigned', 'NO', 'PRI'),
                    'f'   => array('float(10,4)', 'NO', ''),
                ),
                'index'  => array(
                    'wid_did' => array(0, 'wid,did'),
                    'wid'     => array(1, 'wid'),
                    'did'     => array(1, 'did'),
                ),
                'create' => 'CREATE TABLE `' . $this->getPrefix() . 'vectors` (
                                `wid` int(10) unsigned NOT NULL,
                                `did` int(10) unsigned NOT NULL,
                                `f` float(10,4) NOT NULL,
                                UNIQUE KEY `wid` (`wid`,`did`),
                                KEY `wid_2` (`wid`),
                                KEY `did` (`did`)
                            ) ' . $collate,
            ),
            'words'   => array(
                'cols'   => array(
                    'id'   => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
                    'word' => array('varchar(32)', 'NO', 'UNI'),
                ),
                'index'  => array(
                    'PRIMARY' => array(0, 'id'),
                    'word'    => array(0, 'word'),
                ),
                'create' => 'CREATE TABLE `' . $this->getPrefix() . 'words` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `word` varchar(32) character set utf8 collate utf8_bin NOT NULL,
                                PRIMARY KEY  (`id`),
                                UNIQUE KEY `word` (`word`)
                            ) ' . $collate,
            ),
        );
        // Make Mysql Db creation queries
        foreach ($dbscheme as $k => $d) {
            $s = 'CREATE TABLE `' . $this->getPrefix() . $k . '` (' . "\n";

            $cs = array();
            $ai = false;
            foreach ($d['cols'] as $kk => $dd) {
                $ss = '`' . $kk . '` ' . $dd[0] . ' ' . ($dd[1] === 'NO' ? 'NOT NULL' : 'NULL');
                if (isset($dd[3])) {
                    $ss .= ' default \'' . $dd[3] . '\'';
                }
                if ((isset($dd[4])) && ($dd[4] === 'auto_increment')) {
                    $ss .= ' auto_increment';
                    $ai = true;
                }
                $cs[] = $ss;
            }

            $iz = array();
            foreach ($d['index'] as $kk => $dd) {
                $ss = '';
                if ($kk === 'PRIMARY') {
                    $ss = 'PRIMARY KEY';
                } else {
                    if ((int) $dd[0] === 0) {
                        $ss = 'UNIQUE KEY `' . $kk . '`';
                    } else {
                        $ss = 'KEY `' . $kk . '`';
                    }
                }
                $ws = explode(',', $dd[1]);
                $zz = array();
                foreach ($ws as $z) {
                    $zz[] = '`' . $z . '`';
                }
                $ss .= ' (' . implode(',', $zz) . ')';

                $iz[] = $ss;
            }

            $s .= implode(",\n", $cs);

            if (count($iz) > 0) {
                $s .= ",\n" . implode(",\n", $iz);
            }

            $s .= "\n" . ') ENGINE=MyISAM' . ($ai ? ' AUTO_INCREMENT=1' : '') . ' DEFAULT CHARSET=utf8';

            $dbscheme[$k]['create2'] = $s;
        }

        return $dbscheme;
    }

    /**
     * Load stops
     *
     * @return void
     */
    protected function loadStops()
    {
        global $wpdb;
        $q   = 'select word from ' . $this->getPrefix() . 'stops';
        $res = $wpdb->get_result($q, ARRAY_A);
        $z   = array();
        foreach ($res as $d) {
            $z[mb_strtolower($d['word'])] = 1;
        }
        $this->stops = $z;
    }

    /**
     * Log
     *
     * @param string $message Log message
     *
     * @return void
     */
    protected function log($message)
    {
        $this->log[] = $message;
    }

    /**
     * Clear log
     *
     * @return void
     */
    public function clearLog()
    {
        $this->log = array();
    }

    /**
     * Get log
     *
     * @return string
     */
    public function getLog()
    {
        return implode("\n", $this->log);
    }

    /**
     * Split to words
     *
     * @param string $str Title string
     *
     * @return string
     */
    protected function splitToWords($str)
    {
        $str = str_replace('_', ' ', $str);
        $str = str_replace('-', ' ', $str);
        $pattern_str = "~([\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w][\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w'\-]*[\x{00C0}" .
                       "-\x{1FFF}\x{2C00}-\x{D7FF}\w]+|[\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w]+)~u";
        preg_match_all($pattern_str, $str, $matches);

        return apply_filters('wpfd_fts_split_to_words', $matches[1], $str);
    }

    /**
     * Reindex
     *
     * @param string $indexId Index id
     * @param array  $chunks  Chunks to index
     *
     * @return boolean
     */
    public function reindex($indexId, $chunks)
    {
        global $wpdb;

        if (!is_array($chunks)) {
            $this->log('Class Index - line 66: Wrong chunks format');

            return false;
        }

        foreach ($chunks as $key => $doc) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    'select id from `' . $wpdb->prefix . 'wpfd_docs` where `index_id` = %d and `token` = %s',
                    addslashes($indexId),
                    addslashes($key)
                ),
                ARRAY_A
            );


            if (!isset($results[0]['id'])) {
                // Insert token record
                $wpdb->insert($this->getPrefix() . 'docs', array(
                    'index_id' => $indexId,
                    'token'    => $key,
                    'n'        => 0,
                ));

                $docId = $wpdb->insert_id;
            } else {
                $docId = $results[0]['id'];
            }

            $r2 = $this->add(array($docId => $doc));

            if (!$r2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add documents
     *
     * @param array $docs Documents
     *
     * @return boolean
     */
    public function add($docs = array())
    {
        global $wpdb;

        if (!is_array($docs)) {
            $this->log('Add doc line 101: Parameter should be an array');

            return false;
        }

        if (count($docs) < 1) {
            return false;
        }

        foreach ($docs as $id => $doc) {
            if (!is_numeric($id)) {
                $this->log('Add document: bad index "' . $id . '" given.');

                return false;
            } else {
                $a_ids[] = $id;
            }
        }

        $prefix = $this->getPrefix();

        $wordlist = array();
        $doclist  = array();
        foreach ($docs as $id => $doc) {
            if (!isset($doc) || (mb_strlen($doc) < 1)) {
                continue;
            }

            $words        = $this->splitToWords($doc);
            $numOfWords   = count($words);
            $doclist[$id] = $numOfWords;

            // Clean words, remote stop words, 1 char word and too long
            $word2 = array();

            foreach ($words as $word) {
                $len = mb_strlen($word);
                $lv  = mb_strtolower($word);

                if (($len > 1) && ($len <= $this->max_word_length) && (!isset($this->stops[$lv]))) {
                    if (!isset($word2[$lv])) {
                        $word2[$lv] = 1;
                    } else {
                        $word2[$lv]++;
                    }
                }
            }
            foreach ($word2 as $key => $value) {
                $wordlist[] = array($key, $id, $value / $numOfWords);
            }

            $wpdb->update($prefix . 'docs', array('n' => $numOfWords), array('id' => $id));
        }

        // Insert words into words table
        $wordlist_ch = array_chunk($wordlist, 1000);
        foreach ($wordlist_ch as $d) {
            $z = array();

            foreach ($d as $dd) {
                $z[] = '("' . addslashes($dd[0]) . '")';
            }

            $query = 'insert ignore into `' . $wpdb->prefix . 'wpfd_words` (`word`) values ' . implode(',', $z);
            // phpcs:ignore WordPress.Security.EscapeOutput.NotPrepared -- Dont need escape
            $wpdb->query($query);

            if ($wpdb->last_error) {
                $this->log('Add document: can not add words. Error: ' . $wpdb->last_error);
                $wpdb->query('unlock tables');

                return false;
            }
        }

        // lock the tables in case some other process remove a certain word
        // between step 0 and 1 and 2 and 3
        if ($this->is_lock) {
            $wpdb->query('lock tables `' . $wpdb->prefix . 'wpfd_vectors` write, `' . $wpdb->prefix . 'wpfd_words` write');

            if ($wpdb->last_error) {
                // Disable locking
                $this->is_lock = false;
                $wpdb->query('unlock tables');
                //$this->log('Add document: Error when locking tables: '.$wpdb->last_error);
                //return false;
            }
        }

        // Remove old vectors
        $query = 'delete from `' . $wpdb->prefix . 'wpfd_vectors` where `did` in (' . implode(',', $a_ids) . ')';
        // phpcs:ignore WordPress.Security.EscapeOutput.NotPrepared -- Dont need escape
        $wpdb->query($query);

        if ($wpdb->last_error) {
            $this->log('Add document: Error when removing old vectors: ' . $wpdb->last_error);
            if ($this->is_lock) {
                $wpdb->query('unlock tables');
            }

            return false;
        }

        // Insert new vectors
        foreach ($wordlist as $d) {
            $query = 'insert ignore into `' . $wpdb->prefix . 'wpfd_vectors` (`wid`,`did`,`f`)
                    select 
                        id,
                        ' . $d[1] . ',
                        ' . $d[2] . '
                    from `' . $wpdb->prefix . 'wpfd_words`
                    where `word` = "' . addslashes($d[0]) . '"
                ';
            // phpcs:ignore WordPress.Security.EscapeOutput.NotPrepared -- Escaped
            $wpdb->query($query);

            if ($wpdb->last_error) {
                $this->log('Add vectors: can not add vector. Error: ' . $wpdb->last_error);
                if ($this->is_lock) {
                    $wpdb->query('unlock tables');
                }

                return false;
            }
        }

        if ($this->is_lock) {
            $wpdb->query('unlock tables');
        }

        return true;
    }


    /**
     * Get clusters
     *
     * @return array
     */
    public function getClusters()
    {
        global $wpdb;

        $z   = array('post_title' => 1, 'post_content' => 1);
        $res = $wpdb->get_results('select distinct `token` from `' . $wpdb->prefix . 'wpfd_docs` limit 100', ARRAY_A);

        $z = array();
        foreach ($res as $d) {
            if (!isset($z[$d['token']])) {
                $z[$d['token']] = 1;
            }
        }

        return array_keys($z);
    }

    /**
     * Check And Sync WPPosts
     *
     * @param integer $current_build_time Current build time
     *
     * @return void
     */
    public function checkAndSyncWPPosts($current_build_time)
    {

        global $wpdb;

        $prefix = $this->getPrefix();

        // Step 1. Mark index rows contains old posts and posts with wrong date of post or build time.
        $wpdb->query(
            $wpdb->prepare(
                'update `' . $wpdb->prefix . 'wpfd_index` wi
                    left join `' . $wpdb->posts . '` p
                        on p.ID = wi.tid
                    set 
                        wi.force_rebuild = if(p.ID is null, 2, if ((wi.build_time = %s) and (wi.tdt = p.post_modified), 0, 1))
                    where 
                        (wi.tsrc = "wp_files") and (wi.force_rebuild = 0)
                        and (p.post_type = "wpfd_file")',
                addslashes($current_build_time)
            )
        );

        // Step 2. Find and add new posts // @todo need to be optimized!

        $wpdb->query('insert ignore into `' . $wpdb->prefix . 'wpfd_index` 
                (`tid`, `tsrc`, `tdt`, `build_time`, `update_dt`, `force_rebuild`, `locked_dt`) 
                select 
                    p.ID tid,
                    "wpfd_files" tsrc,
                    "0000-00-00 00:00:00" tdt,
                    0 build_time,
                    "0000-00-00 00:00:00" update_dt,
                    1 force_rebuild,
                    "0000-00-00 00:00:00" locked_dt
                    from `' . $wpdb->posts . '` p
                where
                    p.post_type = "wpfd_file"');
        // Step 3. What else?
    }

    /**
     * Get status
     *
     * @return array
     */
    public function getStatus()
    {
        global $wpdb;

        $indexTable = $wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "wpfd_index'");
        if (is_null($indexTable) || strtolower($indexTable) !== strtolower($wpdb->prefix . 'wpfd_index')) {
            return array(
                'n_inindex' => 0,
                'n_actual'  => 0,
                'n_pending' => 0,
                'message'   => esc_html__('Index not ready! Click to build it!', 'wpfd')
            );
        }

        $res = $wpdb->get_results('select 
                sum(if (build_time != 0, 1, 0)) n_inindex, 
                sum(if ((force_rebuild = 0) and (build_time != 0), 1, 0)) n_actual,
                sum(if ((force_rebuild != 0) or (build_time = 0), 1, 0)) n_pending
            from `' . $wpdb->prefix . 'wpfd_index` 
            where tsrc = "wpfd_files"', ARRAY_A);

        $ret = array();
        if (isset($res[0]['n_inindex'])) {
            $ret = array(
                'n_inindex' => intval($res[0]['n_inindex']),
                'n_actual'  => intval($res[0]['n_actual']),
                'n_pending' => intval($res[0]['n_pending']),
            );
        } else {
            $ret = array(
                'n_inindex' => 0,
                'n_actual'  => 0,
                'n_pending' => 0,
            );
        }

        return $ret;
    }

    /**
     * Update record data
     *
     * @param integer $id   Record id
     * @param array   $data Record data
     *
     * @return void
     */
    public function updateRecordData($id, $data = array())
    {
        global $wpdb;
        $prefix = $this->getPrefix();
        $a      = array();
        foreach ($data as $k => $d) {
            if (in_array($k, array('tdt', 'build_time', 'update_dt', 'force_rebuild', 'locked_dt'))) {
                $a[$k] = $d;
            }
        }
        $wpdb->update($prefix . 'index', $a, array('id' => $id));
    }

    /**
     * Unlock Record
     *
     * @param integer $id Record id
     *
     * @return void
     */
    public function unlockRecord($id)
    {
        global $wpdb;
        $prefix = $this->getPrefix();
        $wpdb->update($prefix . 'index', array('locked_dt' => '0000-00-00 00:00:00'), array('id' => $id));
    }

    /**
     * Insert Record Data
     *
     * @param array $data Data array
     *
     * @return integer
     */
    public function insertRecordData($data = array())
    {

        global $wpdb;

        $prefix = $this->getPrefix();

        $a = array();
        foreach ($data as $k => $d) {
            if (in_array($k, array('tdt', 'build_time', 'update_dt', 'force_rebuild', 'locked_dt', 'tid', 'tsrc'))) {
                $a[$k] = $d;
            }
        }
        $wpdb->insert($prefix . 'index', $a);

        return $wpdb->insert_id;
    }

    /**
     * Update index record for post
     *
     * @param integer $post_id       Post id
     * @param integer $modt          Modify time
     * @param integer $build_time    Build time
     * @param boolean $time          Update time
     * @param integer $force_rebuild Force rebuild
     *
     * @return integer
     */
    public function updateIndexRecordForPost($post_id, $modt, $build_time, $time = false, $force_rebuild = 0)
    {

        global $wpdb;

        if ($time === false) {
            $time = time();
        }

        $res = $wpdb->get_results(
            $wpdb->prepare(
                'select * from `' . $wpdb->prefix . 'wpfd_index` where (`tid` = %d) and (`tsrc` = "wpfd_files")',
                $post_id
            ),
            ARRAY_A
        );

        if (isset($res[0])) {
            // Update existing record
            $this->updateRecordData(
                $res[0]['id'],
                array(
                    'tdt'           => $modt,
                    'build_time'    => $build_time,
                    'update_dt'     => date('Y-m-d H:i:s', $time),
                    'force_rebuild' => $force_rebuild,
                    'locked_dt'     => '0000-00-00 00:00:00',
                )
            );

            return $res[0]['id'];
        } else {
            // Insert new record
            $insert_id = $this->insertRecordData(
                array(
                    'tid'           => $post_id,
                    'tsrc'          => 'wpfd_files',
                    'tdt'           => $modt,
                    'build_time'    => $build_time,
                    'update_dt'     => date('Y-m-d H:i:s', $time),
                    'force_rebuild' => $force_rebuild,
                    'locked_dt'     => '0000-00-00 00:00:00',
                )
            );

            return $insert_id;
        }
    }

    /**
     * Get records to rebuild
     *
     * @param integer $n_max Maximum record per request
     *
     * @return array|null|object
     */
    public function getRecordsToRebuild($n_max = 1)
    {

        global $wpdb;

        $prefix = $this->getPrefix();

        $time  = time();
        $time2 = date('Y-m-d H:i:s', $time - $this->lock_time);

        $results = $wpdb->get_results(
            $wpdb->prepare(
                'select 
                    id, tid, tsrc 
                    from `' . $wpdb->prefix . 'wpfd_index` 
                    where 
                        ((force_rebuild != 0) or (build_time = 0)) and 
                        ((locked_dt = "0000-00-00 00:00:00") or (locked_dt < %s))
                    order by build_time asc, id asc 
                    limit %d',
                $time2,
                intval($n_max)
            ),
            ARRAY_A
        );

        return $results;
    }

    /**
     * Lock unlocked record
     *
     * @param integer $id Record id
     *
     * @return boolean
     */
    public function lockUnlockedRecord($id)
    {

        global $wpdb;

        $prefix = $this->getPrefix();

        $time     = time();
        $time2    = date('Y-m-d H:i:s', $time - $this->lock_time);
        $new_time = date('Y-m-d H:i:s', $time);

        $res = $wpdb->get_results(
            $wpdb->prepare(
                'select id, if((locked_dt = "0000-00-00 00:00:00") or (locked_dt < %s), 0, 1) islocked from `' . $wpdb->prefix . 'wpfd_index` where id = %d',
                $time2,
                (int) $id
            ),
            ARRAY_A
        );

        if (isset($res[0])) {
            if ($res[0]['islocked']) {
                // Already locked
                return false;
            } else {
                // Lock it
                $wpdb->update($prefix . 'index', array('locked_dt' => $new_time), array('id' => $id));

                return true;
            }
        } else {
            // Record not found
            return false;
        }
    }

    /**
     * Get column
     *
     * @param array  $a   Array columns
     * @param string $col Column
     *
     * @return array
     */
    public function getColumn($a, $col)
    {
        $r = array();
        foreach ($a as $d) {
            if (isset($d[$col])) {
                $r[] = (int) $d[$col];
            }
        }

        return $r;
    }

    /**
     * Remove index record for post
     *
     * @param integer $post_id Post id
     *
     * @return boolean
     */
    public function removeIndexRecordForPost($post_id)
    {
        global $wpdb;
        $prefix = $this->getPrefix();
        $indexTable = $wpdb->get_var("SHOW TABLES LIKE '" . $prefix . "index'");
        // phpcs:ignore WordPress.Security.EscapeOutput.NotPrepared -- no input in query
        if (is_null($indexTable) || strtolower($indexTable) !== strtolower($prefix . 'index')) {
            return false;
        }
        /**
         * Action fire before an index remove
         *
         * @param integer Post id
         */
        do_action('wpfd_before_index_remove', $post_id);

        $res_index = $wpdb->get_results(
            $wpdb->prepare(
                'select `id` from `' . $wpdb->prefix . 'wpfd_index` where (`tid` = %d) and (`tsrc` = "wpfd_files")',
                (int) $post_id
            ),
            ARRAY_A
        );

        if (isset($res_index[0])) {
            $q        = 'select `id` from `' . $prefix;
            $q        .= 'docs` where `index_id` in (' . implode(',', $this->getColumn($res_index, 'id')) . ')';
            // phpcs:disable WordPress.Security.EscapeOutput.NotPrepared -- case to int for list res_index in getColumn()
            $res_docs = $wpdb->get_results($q, ARRAY_A);

            if (isset($res_docs[0])) {
                $q = 'delete from `' . $prefix . 'vectors` where `did` in (';
                $q .= implode(',', $this->getColumn($res_docs, 'id')) . ')';
                $wpdb->query($q);

                $q = 'delete from `' . $prefix . 'docs` where `index_id` in (';
                $q .= implode(',', $this->getColumn($res_index, 'id')) . ')';
                $wpdb->query($q);
            }

            $q = 'delete from `' . $prefix;
            $q .= 'index` where (`tid` = "' . (int) $post_id . '") and (`tsrc` = "wpfd_files")';

            $wpdb->query($q);
            // phpcs:enable
        }

        return true;
    }

    /**
     * Post reindex
     *
     * @param integer $post_id         Post id
     * @param boolean $is_force_remove Fore remove
     *
     * @return boolean
     */
    public function wpfdPostReindex($post_id, $is_force_remove = false)
    {
        global $wpdb;

        $indexTable = $wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "wpfd_index'");
        if (is_null($indexTable) || strtolower($indexTable) !== strtolower($wpdb->prefix . 'wpfd_index')) {
            return false;
        }
        $res = $this->reindexPost($post_id, $is_force_remove);
        if (!$res) {
            $this->log[] ='Error reindex post ID=' . $post_id . ': ' . $this->index_error;

            return false;
        }

        return true;
    }

    /**
     * Reindex post
     *
     * @param integer $post_id         Post id
     * @param boolean $is_force_remove Force remove
     *
     * @return boolean
     */
    public function reindexPost($post_id, $is_force_remove = false)
    {
        $post = get_post($post_id);

        if ($post && (!$is_force_remove)) {
            // Insert or update index record
            $chunks  = array(
                'post_title'   => $post->post_title,
                'post_content' => $post->post_content,
            );
            /**
             * Filter to add file content on index
             *
             * @param array   Chunks
             * @param WP_Post Post object
             */
            $chunks2 = apply_filters('wpfd_index_file', $chunks, $post);

            $modt       = $post->post_modified_gmt;
            $time       = time();
            $build_time = get_option('wpfd_fts_rebuild_time');
            $insert_id  = $this->updateIndexRecordForPost($post_id, $modt, $build_time, $time, 0);

            $this->clearLog();
            $res               = $this->reindex($insert_id, $chunks2);
            $this->index_error = (!$res) ? 'Indexing error: ' . $this->getLog() : '';
            /**
             * Action fire after file indexed
             *
             * @param integer Inserted post id
             * @param array   Post content to index
             */
            do_action('wpfd_file_indexed', $insert_id, $chunks2);
            return $res;
        } else {
            // Check if index record exists and delete it
            $this->removeIndexRecordForPost($post_id);

            return true;
        }
    }
}

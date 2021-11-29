<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Controller;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerFts
 */
class WpfdControllerFts extends Controller
{

    /**
     * Indexer progress Id
     *
     * @var boolean|string
     */
    protected $p_id = false;

    /**
     * Get current progress id
     *
     * @return boolean|string
     */
    public function getpid()
    {
        if (!$this->p_id) {
            $this->p_id = sha1(time() . uniqid());
        }

        return $this->p_id;
    }

    /**
     * Default options fts
     *
     * @return array
     */
    protected function defaultOptions()
    {
        return array(
            'enabled'          => 1,
            'autoreindex'      => 1,
            'index_ready'      => 0,
            'deflogic'         => 0, // AND
            'minlen'           => 3,
            'maxrepeat'        => 80, // 80%
            'stopwords'        => '',
            'epostype'         => '',
            'cluster_weights'  => serialize(array(
                'post_title'   => 0.8,
                'post_content' => 0.5,
            )),
            'testpostid'       => '',
            'testquery'        => '',
            'tq_disable'       => 0,
            'tq_nocache'       => 1,
            'tq_post_status'   => 'any',
            'tq_post_type'     => 'any',
            'rebuild_time'     => 0,
            'process_time'     => '0|',
            'ping_period'      => 30,
            'est_time'         => '00:00:00',
            'activation_error' => '',
            'admin_message'    => '',
        );
    }

    /**
     * Get option fts
     *
     * @param string $optname Option name
     *
     * @return array|mixed
     */
    public function wpfdFtsGetOption($optname)
    {
        $defaults = $this->defaultOptions();
        $v = get_option('wpfd_fts_' . $optname, isset($defaults[$optname]) ? $defaults[$optname] : false);
        switch ($optname) {
            case 'epostype':
                $v = (strlen($v) > 0) ? unserialize($v) : array();
                break;
            case 'cluster_weights':
                $v = (strlen($v) > 0) ? unserialize($v) : array();
                break;
        }
        return $v;
    }

    /**
     * Set option
     *
     * @param string $optname Option name
     * @param mixed  $value   Option value
     *
     * @return boolean
     */
    public function wpfdFtsSetOption($optname, $value)
    {
        $defaults = $this->defaultOptions();

        if (isset($defaults[$optname])) {
            // Allowed option
            $v = $value;
            switch ($optname) {
                case 'epostype':
                case 'cluster_weights':
                    $v = serialize($value);
                    break;
            }

            $option_name = 'wpfd_fts_' . $optname;
            if (get_option($option_name, false) !== false) {
                update_option($option_name, $v);
            } else {
                add_option($option_name, $v, '', 'no');
            }
            return true;
        } else {
            // Not allowed option
            return false;
        }
    }

    /**
     * Check Sync WPPosts
     *
     * @return mixed
     */
    public function checkAndSyncWPPosts()
    {
        $model = $this->getModel();
        return $model->checkAndSyncWPPosts($this->wpfdFtsGetOption('rebuild_time'));
    }

    /**
     * Get status of indexer
     *
     * @return array
     */
    public function getStatus()
    {
        $model = $this->getModel();
        $st = $model->getStatus();
        $st['est_time'] = $this->wpfdFtsGetOption('est_time');
        $st['enabled'] = $this->wpfdFtsGetOption('enabled');
        $st['index_ready'] = $this->wpfdFtsGetOption('index_ready');
        $st['autoreindex'] = $this->wpfdFtsGetOption('autoreindex');

        return $st;
    }

    /**
     * Action rebuild index
     *
     * @param boolean|string $time Last index time
     *
     * @return mixed
     */
    public function rebuildindex($time = false)
    {
        if (!$time) {
            $time = time();
        }

        $this->wpfdFtsSetOption('rebuild_time', $time);

        return $this->checkAndSyncWPPosts();
    }

    /**
     * Get current process state
     *
     * @param tring $process_id Current progress Id
     *
     * @return integer
     */
    public function indexerProcessState($process_id)
    {
        $time = time();
        $process_time = explode('|', $this->wpfdFtsGetOption('process_time'));
        $ping_period = 30;
        if ((string) $process_id !== (string) $process_time[1]) {
            if ($process_time[0] + $ping_period * 4 > $time) {
                return 2;    // Other pid indexing goes
            } else {
                return 0;    // Free
            }
        } else {
            if ($process_time[0] + $ping_period * 2 > $time) {
                return 1;    // Our pid indexing goes
            } else {
                return 0;    // Free
            }
        }
    }

    /**
     * Action submit rebuild
     *
     * @return void
     */
    public function submitrebuild()
    {
        $app = Application::getInstance('Wpfd');
        $config = $this->getModel('config');
        $searchConfig = $config->getSearchConfig();
        $enableFts = (int) $searchConfig['plain_text_search'] === 1 ? true : false;
        if (!$enableFts) {
            $searchConfig['plain_text_search'] = 1;
            $config->saveSearchParams($searchConfig);
        }
        $path_wpfdhelperresponse = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $path_wpfdhelperresponse .= 'helpers' . DIRECTORY_SEPARATOR . 'WpfdHelperResponse.php';
        require_once $path_wpfdhelperresponse;
        $jx = new WpfdHelperResponse();
        $model = $this->getModel();
        $data = $jx->getData();
        if ($data !== false) {
            $this->wpfdFtsSetOption('index_ready', 0);
            $model->createDbTables();
            $this->rebuildindex(time());
            $jx->reload();
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Just string to output
        echo $jx->getJSON();
        wp_die();
    }

    /**
     * Action ajaxping
     *
     * @return void
     */
    public function ajaxping()
    {
        $t0 = microtime(true);
        $app = Application::getInstance('Wpfd');
        $path_wpfdhelperresponse = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers';
        $path_wpfdhelperresponse .= DIRECTORY_SEPARATOR . 'WpfdHelperResponse.php';
        require_once $path_wpfdhelperresponse;
        $jx = new WpfdHelperResponse();
        $data = $jx->getData();
        if ($data !== false) {
            $process_id = $data['pid'];
            $status = $this->getStatus();

            $st = $this->indexerProcessState($process_id);

            $jx->variable('code', 0);

            // $view = $this->loadView();
            $jx->variable('status', json_encode($status));
            switch ($st) {
                case 2:
                case 1:
                    // Other pid is indexing
                    $jx->variable('result', 10);    // Just wait, ping
                    break;
                case 0:
                default:
                    // Indexer is free now, lets check what to do
                    if ($status['n_pending'] > 0) {
                        // There is something to index
                        $jx->variable('result', 5);    // Start to index
                    } else {
                        // Nothing to index
                        $jx->variable('result', 0);    // Indexing stopped, ping
                    }
            }

            $jx->console('pong! ' . (microtime(true) - $t0) . ' s');
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Just string to output
        echo $jx->getJSON();
        wp_die();
    }

    /**
     * Action rebuild step
     *
     * @return void
     */
    public function rebuildstep()
    {
        $app = Application::getInstance('Wpfd');
        $path_wpfdhelperresponse = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers';
        $path_wpfdhelperresponse .= DIRECTORY_SEPARATOR . 'WpfdHelperResponse.php';
        require_once $path_wpfdhelperresponse;
        $jx = new WpfdHelperResponse();
        $model = $this->getModel();
        $data = $jx->getData();
        if ($data !== false) {
            $process_id = $data['pid'];
            $st = $this->indexerProcessState($process_id);
            if ($st !== 2) {
                // Allow to start indexer session
                // Set up lock
                $this->wpfdFtsSetOption('process_time', time() . '|' . $process_id);

                $build_time = $this->wpfdFtsGetOption('rebuild_time');

                $maxtime = 10;
                $start_ts = microtime(true);

                ignore_user_abort(true);

                $n = 0;
                while (microtime(true) - $start_ts < $maxtime) {
                    $ids = $model->getRecordsToRebuild(10);
                    foreach ($ids as $item) {
                        if (!(microtime(true) - $start_ts < $maxtime)) {
                            break;
                        }

                        // Rebuild this record
                        if ($item['tsrc'] === 'wpfd_files') {
                            // Check if locked and lock if not locked
                            if ($model->lockUnlockedRecord($item['id'])) {
                                // Record is locked, lets index it now
                                $post = get_post($item['tid']);
                                $modt = get_date_from_gmt($post->post_modified_gmt);
                                $chunks = array(
                                    'post_title' => $post->post_title,
                                    'post_content' => $post->post_content,
                                );
                                $chunks2 = apply_filters('wpfd_index_file', $chunks, $post);

                                $model->clearLog();
                                $res = $model->reindex($item['id'], $chunks2);
                                if (!$res) {
                                    $jx->console('Indexing error: ' . $model->getLog());
                                }
                                // Store some statistic
                                $time = time();
                                $model->updateRecordData($item['id'], array(
                                    'tdt' => $modt,
                                    'build_time' => $build_time,
                                    'update_dt' => date('Y-m-d H:i:s', $time),
                                    'force_rebuild' => 0,
                                ));
                                $model->unlockRecord($item['id']);
                            }
                        }
                        $n++;
                    }
                    if ($n < 1) {
                        break;
                    }
                }

                $finish_ts = microtime(true);

                $jx->variable('code', 0);

                $status = $this->getStatus();

                $est_seconds = $n > 0 ? intval((($finish_ts - $start_ts) * $status['n_pending']) / $n) : 0;

                $est_h = intval($est_seconds / 3600);
                $est_m = intval(($est_seconds - $est_h * 3600) / 60);
                $est_s = ($est_seconds - $est_h * 3600) % 60;
                $est_str = sprintf('%02d:%02d:%02d', $est_h, $est_m, $est_s);

                $this->wpfdFtsSetOption('est_time', $est_str);

                $status['est_time'] = $est_str;

                if ($status['n_pending'] > 0) {
                    if (($finish_ts - $start_ts) < ($maxtime / 2)) {
                        // Just a delay
                        $this->wpfdFtsSetOption('process_time', '0|' . $process_id);
                        $jx->variable('result', 10);
                    } else {
                        // There is something to index
                        // Remove lock
                        $this->wpfdFtsSetOption('process_time', '0|' . $process_id);
                        $jx->variable('result', 5);    // Continue indexing
                    }
                } else {
                    // Nothing to index
                    // Remove lock
                    $this->wpfdFtsSetOption('process_time', '0|0');
                    $jx->variable('result', 0);    // Indexing stopped, ping
                    $this->wpfdFtsSetOption('index_ready', 1);
                    $jx->variable('delay', 0);
                }
                $status = $this->getStatus();
                // $view = $this->loadView();
                $jx->variable('status', json_encode($status));

                $jx->console(sprintf(esc_html__('%s files has been rebuilt', 'wpfd'), $n));
            } else {
                // Unable to index
                $jx->variable('code', 1);
            }
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Just string to output
        echo $jx->getJSON();
        wp_die();
    }
}

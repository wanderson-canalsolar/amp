<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 4.6.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\View;

defined('ABSPATH') || die();

/**
 * Class WpfdViewStatistics
 */
class WpfdViewStatistics extends View
{
    /**
     * Default date format for statistics view
     *
     * @var string
     */
    public $dateFormat = 'Y-m-d';

    /**
     * Default line color for Total Download
     *
     * @var array
     */
    public $defaultLineColor = array('r' => 255, 'g' => 99, 'b' => 132);

    /**
     * Allow tracking user download
     *
     * @var integer
     */
    public $allowTrackUserDownload = 0;

    /**
     * Wpfd Statistics Model
     *
     * @var WpfdModelStatistics
     */
    private $model;

    /**
     * Wpfd Config Model
     *
     * @var WpfdModelConfig
     */
    private $configModel;

    /**
     * Render view statistics
     *
     * @param null $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {
        Application::getInstance('Wpfd');
        $this->model = $this->getModel();
        $this->configModel = $this->getModel('config');
        // Check model type is correct to reduce error
        if ($this->model instanceof WpfdModelStatistics && $this->configModel instanceof WpfdModelConfig) {
            $globalConfig = $this->configModel->getConfig();
//            $this->dateFormat = isset($globalConfig['date_format']) ? $globalConfig['date_format'] : 'Y-m-d';
            $this->dateFormat = WpfdBase::loadValue($globalConfig, 'date_format', 0);
            $this->allowTrackUserDownload = (int) WpfdBase::loadValue($globalConfig, 'track_user_download', 0);
            /* Files */
            $this->getFiles();

            // Init first load data
            $this->totalDownloads = $this->model->getTotalByType('default');

            parent::render($tpl);
        } else {
            die('Model wrong'); // todo: return other view?
        }
    }

    /**
     * Get Files
     *
     * @throws Exception Throw exception on error
     * @return void
     */
    public function getFiles()
    {
        $this->files = $this->model->getItems();
        $this->pagination = $this->model->getPagination();
        if (!empty($this->files)) {
            foreach ($this->files as $file) {
                $cat = wp_get_post_terms($file->ID, 'wpfd-category');
                if (!empty($cat)) {
                    $file->cattitle = $cat[0]->name;
                }
            }
        }
        $this->selectionValues = $this->model->getSelectionValues();
        $this->total           = $this->model->getTotal();
        //get download count by date of each file
        $this->dates = array();
        $minDate     = date('Y-m-d');
        $maxDate     = date('Y-m-d');

        $filter_order      = Utilities::getInput('filter_order', 'POST', 'none');
        $filter_order_dir  = Utilities::getInput('filter_order_dir', 'POST', 'none');
        $this->ordering    = $filter_order !== null ? $filter_order : 'count_hits';
        $this->orderingdir = $filter_order_dir !== null ? $filter_order_dir : 'desc';

        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($this->files) && count($this->files)) {
            $reverse     = $this->orderingdir === 'asc' ? false : true;
            $selection = Utilities::getInput('selection', 'POST', 'string');
            if (!empty($selection)) {
                $selection_value = Utilities::getInput('selection_value', 'POST', 'none');
                if (empty($selection_value) || $selection !== 'users') {
                    $this->files = wpfd_sort_by_property($this->files, $this->ordering, 'ID', $reverse);
                }
            }

            $fids        = array();
            foreach ($this->files as $file) {
                $fids[] = $file->ID;
            }
            $this->dates = $this->model->getDownloadCountByDate($fids);
            $date_arr    = array_keys($this->dates);
            if (!empty($date_arr)) {
                if (strtotime($date_arr[0]) < strtotime($minDate)) {
                    $minDate = $date_arr[0];
                }
                if (strtotime(end($date_arr)) > strtotime($maxDate)) {
                    $maxDate = end($date_arr);
                }
            }
        }
        //calculate date range to draw chart
        $date_from = Utilities::getInput('fdate', 'POST', 'string');
        $date_to   = Utilities::getInput('tdate', 'POST', 'string');

        if (empty($date_from) && empty($date_to)) {
            $date_from = date('Y-m-d', strtotime('-1 month', time()));
            $date_to   = date('Y-m-d');
        } elseif (empty($date_to)) {
            $date_to = date('Y-m-d', strtotime('+1 day', strtotime($maxDate)));
        } elseif (empty($date_from)) {
            $date_from = date('Y-m-d', strtotime('-1 day', strtotime($minDate)));
        }
        // Build data for chart
        $this->dateFiles = array();
        $begin           = new DateTime($date_from);
        $end             = new DateTime($date_to);
        $end->modify('+1 day');
        $interval = DateInterval::createFromDateString('1 day');
        $period   = new DatePeriod($begin, $interval, $end);

        foreach ($period as $dt) {
            $temp                   = $dt->format('Y-m-d');
            $this->dateFiles[$temp] = array();
            foreach ($this->files as $file) {
                if (isset($this->dates[$temp][$file->ID])) {
                    $this->dateFiles[$temp][$file->ID] = $this->dates[$temp][$file->ID];
                } else {
                    $this->dateFiles[$temp][$file->ID] = 0;
                }
            }
        }

        // Default global download count
        if (self::isEmptyForm()) {
            $this->files = array();
        }
    }

    /**
     * Get Labels and Datas
     *
     * @throws Exception Throws exception on error
     * @return array
     */
    public function getLablesDatas()
    {
        $lables = array();
        $datas = array();
        if (isset($this->files) && is_countable($this->files) && count($this->files)) {
            // Show statistics for files
            foreach ($this->files as $file) {
                $row = array();
                $row['label'] = $file->post_title;
                $row['color'] = $this->randomColor();
                foreach ($this->dateFiles as $date => $columns) {
//                    if ((int) $columns[$file->ID] > 0) {
                        $date = new DateTime($date);
                        $row['datas'][] = '{x:\'' . esc_attr($date->format($this->dateFormat)) . '\', y: \'' . esc_attr($columns[$file->ID]) . '\'}';
//                    }
                }
                $datas[] = $row;
            }

            foreach ($this->dateFiles as $date => $columns) {
                $date = new DateTime($date);
                $lables[] = '\'' . esc_attr($date->format($this->dateFormat)) . '\'';
            }
        } else {
            $datas[0]['label'] = esc_html__('Total Download', 'wpfd');
            $datas[0]['color'] = $this->defaultLineColor; // Default color for Total Download
            $datas[0]['datas'] = array();
            foreach ($this->totalDownloads as $item) {
                $date = new DateTime($item->date);
                $lables[] = '\'' . esc_attr($date->format($this->dateFormat)) . '\'';
                $datas[0]['datas'][] = esc_attr($item->count);
            }
        }
        // Filter lables for single value only
            $lables = array_unique($lables);

        return array($lables, $datas);
    }

    /**
     * Generate random RGB color for chart line
     *
     * @return array
     */
    public function randomColor()
    {
        return array('r' => rand(50, 222), 'g' => rand(50, 222), 'b' => rand(50, 222));
    }

    /**
     * Check empty form on POST
     *
     * @return boolean
     */
    public static function isEmptyForm()
    {
        if (Utilities::getInput('selection', 'POST', 'string') === ''
            && Utilities::getInput('query', 'POST', 'string') === ''
            && Utilities::getInput('fdate', 'POST', 'string') === ''
            && Utilities::getInput('tdate', 'POST', 'string') === ''
        ) {
            return true;
        }

        return false;
    }
}

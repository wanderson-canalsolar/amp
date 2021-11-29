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
class WpfdControllerGeneratepreview extends Controller
{
    /**
     * Model
     *
     * @var WpfdModelGeneratepreview
     */
    private $model;

    /**
     * WpfdControllerGeneratepreview constructor.
     */
    public function __construct()
    {
        $this->model = $this->getModel('generatepreview');
    }

    /**
     * AJAX: Generate files queue
     *
     * @return void
     */
    public function generatequeue()
    {
        $this->model->generateQueue();
        header('Content-Type: application/json');
        header('Status: 200');
        echo json_encode(array('success' => true));
        die;
    }

    /**
     * AJAX: Run queue
     *
     * @return void
     */
    public function runqueue()
    {
        $this->model->runQueue();
        header('Content-Type: application/json');
        header('Status: 200');
        echo json_encode(array('success' => true));
        die;
    }

    /**
     * AJAX: Restart queue
     *
     * @return void
     */
    public function restartqueue()
    {
        $generatedQueue = $this->model->restartQueue();
        header('Content-Type: application/json');
        header('Status: 200');
        $result = array('success' => true);
        if (is_array($generatedQueue) && count($generatedQueue) === 0) {
            $result = array('success' => false, 'code' => 'no_file_vaild', 'message' => esc_html__('There is no file to generate preview!', 'wpfd'));
        }
        echo json_encode($result);
        die;
    }

    /**
     * AJAX: Get current status for generator
     *
     * @return void
     */
    public function status()
    {
        $status = $this->model->getStatus();
        header('Content-Type: application/json');
        header('Status: 200');
        echo json_encode($status);
        die;
    }

    /**
     * Exit with application/json header
     *
     * @param string $status Status
     * @param array  $datas  Data to return
     *
     * @return void
     */
    protected function exitStatus($status = '', $datas = array())
    {
        header('Content-Type: application/json');
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        die();
    }
}

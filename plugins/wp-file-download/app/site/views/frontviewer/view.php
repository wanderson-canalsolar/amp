<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die();

/**
 * Class WpfdViewFrontviewer
 */
class WpfdViewFrontviewer extends View
{
    /**
     * Display front viewer
     *
     * @param string $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {

        $id              = Utilities::getInput('id', 'GET', 'string');
        $catid           = Utilities::getInt('catid');
        $ext             = Utilities::getInput('ext', 'GET', 'string');
        $this->mediaType = Utilities::getInput('type', 'GET', 'string');

        $app                = Application::getInstance('Wpfd');
        $downloadlink       = wpfd_sanitize_ajax_url($app->getAjaxUrl()) . '&task=file.download&wpfd_file_id=' . $id;
        $downloadlink       .= '&wpfd_category_id=' . $catid . '&preview=1';
        $this->downloadLink = $downloadlink;
        $this->mineType     = WpfdHelperFile::mimeType(strtolower($ext));

        wp_enqueue_style(
            'wpfd-mediaelementplayer',
            plugins_url('app/site/assets/css/mediaelementplayer.min.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_script(
            'wpfd-mediaelementplayer',
            plugins_url('app/site/assets/js/mediaelement-and-player.js', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        parent::render($tpl);
        die();
    }
}

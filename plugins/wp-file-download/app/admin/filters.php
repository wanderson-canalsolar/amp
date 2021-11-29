<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Filter;
use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die();

/**
 * Class WpfdFilter
 */
class WpfdFilter extends Filter // phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps -- use wpfd prefix
{
    /**
     * Model for full text search
     *
     * @var mixed
     */
    protected $ftsModel;
    /**
     * Error string
     *
     * @var string
     */
    public $index_error = '';

    /**
     * Shortcode string
     *
     * @var string
     */
    private $shortcodes;
    /**
     * Filter Load
     *
     * @return void
     */
    public function load()
    {
        $this->shortcodes = new WpfdHelperShortcodes();
        add_filter('wpfd_index_file', array($this, 'wpfdInjectFileContent'), 3, 2);
        add_filter('wp_link_query', array($this, 'wpfdAddFilePermalink'), 10, 2);
        // Made plugin working with Category Order and Taxonomy Terms Order plugin
        add_filter('to/get_terms_orderby/ignore', array($this, 'ignoreGetTermByTermOrder'), 10, 3);
        if (!defined('WPFDA_VERSION')) {
            add_filter('wpfdAddonCategoryFrom', array($this, 'wpfdAddonCategoryFrom'));
        }
    }

    /**
     * Function to avoid error when apply_filters
     *
     * @param mixed $termId Term id
     *
     * @return string|boolean
     */
    public function wpfdAddonCategoryFrom($termId)
    {
        $maybeCloudType = get_term_meta($termId, 'wpfd_drive_type', true);

        if (in_array($maybeCloudType, wpfd_get_support_cloud())) {
            return $maybeCloudType;
        }

        return false;
    }
    /**
     * Insert Index file when indexer running
     *
     * @param array $index Chunk to be index
     * @param mixed $post  Post object
     *
     * @return array
     */
    public function wpfdInjectFileContent($index, $post)
    {

        if ($post->post_type === 'wpfd_file') {
            $app = Application::getInstance('Wpfd');

            $modelConfig = Model::getInstance('config');
            $modelFile   = Model::getInstance('file');

            $searchConfig = $modelConfig->getSearchConfig();
            $read_content = (int) $searchConfig['plain_text_search'] === 1 ? true : false;
            $file         = $modelFile->getFile($post->ID);

            if (!$file) {
                return $index;
            }

            // TODO: Need optimize for faster and more file type ppt, rtf...
            // txt pdf docx xlsx rtf pptx OK
            // Open Office OK
            // .xls (Office 2003 Format) NOT working well
            // .doc (Office 2003 Format) read WELL in english - utf8 NOT working well
            // .ppt not ok
            $arr_ext = array('doc', 'docx', 'xls', 'xlsx', 'pdf', 'txt', 'pptx', 'rtf');

            if (isset($file['description']) && $file['description'] !== '') {
                $index['description'] = $file['description'];
            }

            if (!isset($file['ext'])) {
                return $index;
            }
            if ($read_content === true && in_array(strtolower($file['ext']), $arr_ext) && $file['size'] < 10 * 1024 * 1024) {
                // get file Content then index it
                $path_wpfdbase = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
                $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
                require_once $path_wpfdbase;
                $filepath = WpfdBase::getFilesPath($file['catid']) . '/' . $file['file'];

                if (!class_exists('WpfdHelperDocument')) {
                    require_once $app->getPath() . '/admin/helpers/WpfdHelperDocument.php';
                }

                $document    = new WpfdHelperDocument($post->ID, $filepath);
                $contentFile = $document->getContent();
                $contentFile = str_replace(array("\r\n", "\r", "\n"), ' ', $contentFile);
                $contentFile = str_replace("\xC2\xA0", ' ', $contentFile);
                $contentFile = html_entity_decode($contentFile);

                $index['post_content'] = $contentFile;
            }
        }

        return $index;
    }

    /**
     * Insert file permalink to wp-link-ajax
     *
     * @param array $results Search results
     * @param array $query   Search query
     *
     * @return array
     */
    public function wpfdAddFilePermalink($results, $query)
    {
        if (in_array('wpfd_file', $query['post_type'])) {
            $config = get_option('_wpfd_global_config');
            if (empty($config) || empty($config['uri'])) {
                $seoPrefix = 'download';
            } else {
                $seoPrefix = sanitize_title($config['uri']);
            }

            $perlink       = get_option('permalink_structure');
            $rewrite_rules = get_option('rewrite_rules');

            $resultsClone = $results;
            foreach ($results as $key => $result) {
                if ($result['info'] === 'File') {
                    // Change permalink
                    $permalink = $result['permalink'];
                    $termList  = wp_get_post_terms($result['ID'], 'wpfd-category', array('fields' => 'ids'));
                    if (!is_wp_error($termList) && isset($termList[0])) {
                        $fileTerm = get_term($termList[0], 'wpfd-category');
                        $catName  = sanitize_title($fileTerm->name);

                        if (!empty($rewrite_rules)) {
                            if (strpos($perlink, 'index.php')) {
                                $permalink = get_site_url() . '/index.php/' . $seoPrefix . '/' . $termList[0] . '/';
                                $permalink .= $catName . '/' . $result['ID'] . '/' . sanitize_title($result['title']);
                            } else {
                                $permalink = get_site_url() . '/' . $seoPrefix . '/' . $termList[0] . '/' . $catName;
                                $permalink .= '/' . $result['ID'] . '/' . sanitize_title($result['title']);
                            }
                        } else {
                            $permalink = admin_url('admin-ajax.php') . '?juwpfisadmin=false&action=wpfd&task=file.download';
                            $permalink .= '&wpfd_category_id=' . $termList[0] . '&wpfd_file_id=' . $result['ID'];
                        }
                    }
                    $resultsClone[$key]['permalink'] = $permalink;
                }
            }

            return $resultsClone;
        }

        return $results;
    }

    /**
     * Made plugin working with Category Order and Taxonomy Terms Order plugin
     *
     * @param boolean $ignore  Ignore value
     * @param array   $orderby Order by array
     * @param array   $args    Query arguments
     *
     * @return boolean
     */
    public function ignoreGetTermByTermOrder($ignore, $orderby, $args)
    {
        if (isset($args['taxonomy']) && is_array($args['taxonomy']) && in_array('wpfd-category', $args['taxonomy'])) {
            return true;
        }
    }
}

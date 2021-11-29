<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Filter;
use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdFilter
 */
class WpfdFilter extends Filter
{
    /**
     * Full text search model instance
     *
     * @var $ftsModel
     */
    private $ftsModel;

    /**
     * Include file in search
     *
     * @var $includeGlobalSearch
     */
    private $includeGlobalSearch;

    /**
     * Shortcode string
     *
     * @var string
     */
    private $shortcodes;
    /**
     * Load filters
     *
     * @return void
     */
    public function load()
    {
        add_filter('the_content', array($this, 'wpfdReplace'), 999999);
        add_filter('woocommerce_short_description', array($this, 'wpfdReplace'), 999999);
        add_filter('themify_builder_module_content', array($this, 'themifyModuleContent'));
        add_filter('template_include', array($this, 'includeTemplate'), 99);
        add_filter('rewrite_rules_array', array($this, 'wpfdInsertRewriteRules'), 99);
        add_filter('query_vars', array($this, 'wpfdInsertQueryVars'));
        add_action('wp_loaded', array($this, 'wpfdFlushRules'));
        add_action('parse_request', array($this, 'wpfdRedirect'), 1, 1);
        $this->shortcodes = new WpfdHelperShortcodes();
        // acf pro - filter for every value load
        add_filter('acf/format_value', array($this, 'wpfdAcfLoadValue'), 10, 3);

        // Full text search enable ?
        $configModel  = Model::getInstance('configfront');
        $searchConfig = $configModel->getSearchConfig();

        $enableFts = ((int) $searchConfig['plain_text_search'] === 1) ? true : false;
        if (!isset($searchConfig['include_global_search'])) {
            $searchConfig['include_global_search'] = 1;
        }
        $this->includeGlobalSearch = ((int) $searchConfig['include_global_search'] === 1) ? true : false;
        if ($this->includeGlobalSearch || $enableFts) {
            add_filter('the_title', array($this, 'wpfdAddMetadata'), 0, 2);
            add_filter('the_posts', array($this, 'wpfdGetMeta'), 10, 2);
            add_filter('the_excerpt', array($this, 'wpfdTheContentSearch'), 10);
        }

        if ($enableFts) {
            $this->ftsModel = Model::getInstance('fts');

            //Set hook to wp search query
            add_action('pre_get_posts', array($this, 'indexPreGetPosts'), 10);
            add_filter('posts_search', array($this, 'indexSqlSelect'), 10, 2);
            add_filter('posts_join', array($this, 'indexSqlJoins'), 10, 2);
            add_filter('posts_search_orderby', array($this, 'indexSqlOrderby'), 10, 2);
            add_filter('the_posts', array($this, 'indexThePosts'), 10, 2);
            add_filter('posts_clauses', array($this, 'indexPostsClauses'), 10, 2);
            add_filter('posts_fields', array($this, 'indexPostsFields'), 10, 2);
            add_filter('posts_distinct', array($this, 'indexPostsDistinct'), 10, 2);
        } elseif ($this->includeGlobalSearch) {
            add_filter('pre_get_posts', array($this, 'wpfdPreGetPosts'), 10);
        }
    }

    /**
     * FULL TEXT SEARCH
     *
     * @param mixed $wpq Wordpress query
     *
     * @return mixed
     */
    public function indexPreGetPosts($wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        $cluster_weights = array(
            'post_title' => 1,
            'post_content' => 1,
        );
        if (empty($wpq->query_vars['s'])) {
            return '';
        }
        return $this->ftsModel->sqlPrePosts($wpq, $cluster_weights, $this->includeGlobalSearch);
    }

    /**
     * Index Sql select
     *
     * @param mixed $search Search
     * @param mixed $wpq    Wordpress query
     *
     * @return mixed
     */
    public function indexSqlSelect($search, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        $q = $wpq->query_vars;

        return $this->ftsModel->sqlSelect($search, $wpq);
    }

    /**
     * Index Sql joins
     *
     * @param string $join Join query
     * @param mixed  $wpq  Wordpress query
     *
     * @return string
     */
    public function indexSqlJoins($join, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        $cluster_weights = array(
            'post_title' => 1,
            'post_content' => 1,
        );
        return $this->ftsModel->sqlJoins($join, $wpq, $cluster_weights);
    }

    /**
     * Index Sql order by
     *
     * @param string $orderby Order by
     * @param mixed  $wpq     Wordpress query
     *
     * @return string
     */
    public function indexSqlOrderby($orderby, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        return $this->ftsModel->sqlOrderby($orderby, $wpq);
    }

    /**
     * Index the posts
     *
     * @param mixed $posts Posts
     * @param mixed $wpq   Wordpress query
     *
     * @return mixed
     */
    public function indexThePosts($posts, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        return $this->ftsModel->sqlThePosts($posts, $wpq);
    }

    /**
     * Index posts clauses
     *
     * @param string $clauses Clauses
     * @param mixed  $wpq     Wordpress query
     *
     * @return string
     */
    public function indexPostsClauses($clauses, $wpq)
    {
        if ((!isset($GLOBALS['posts_clauses'])) || (!is_array($GLOBALS['posts_clauses']))) {
            $GLOBALS['posts_clauses'] = array();
        }
        $GLOBALS['posts_clauses'][] = $clauses;
        return $clauses;
    }

    /**
     * Index Posts Fields
     *
     * @param string $fields Fields
     * @param mixed  $wpq    Wordpress query
     *
     * @return string
     */
    public function indexPostsFields($fields, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        return $this->ftsModel->sqlPostsFields($fields, $wpq);
    }

    /**
     * Index posts distinct
     *
     * @param string $distinct Distinct
     * @param mixed  $wpq      Wordpress query
     *
     * @return string
     */
    public function indexPostsDistinct($distinct, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        return $this->ftsModel->sqlPostsDistinct($distinct, $wpq);
    }

    /**
     * Include wpfd files in search result
     *
     * @param mixed $query Wordpress query
     *
     * @return void
     */
    public function wpfdPreGetPosts(&$query)
    {
        if (!$query->is_search()) {
            return;
        }
        $types = array('post', 'page');
        $types = apply_filters('wpfd_search_post_types', $types);
        if (isset($query->query_vars['post_type'])) {
            $types = $query->query_vars['post_type'];
        }

        if ($query->is_main_query() && $query->is_search()) {
            if (is_array($types)) {
                $types[] = 'wpfd_file';
            }
            $query->set('post_type', $types);
        }
    }

    /**
     * Show file infomartion for $post->post_content in template used
     *
     * @param WP_Post[] $posts Array of posts
     * @param WP_Query  $query Query
     *
     * @return array
     */
    public function wpfdGetMeta($posts, $query)
    {
        if (!$query->is_main_query()) {
            return $posts;
        }
        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($posts) && !count($posts)) {
            return $posts;
        }

        $results = array();
        $user = wp_get_current_user();

        foreach ($posts as $post) {
            if ($post->post_type === 'wpfd_file') {
                if (false !== $this->shortcodes->wpfdCheckAccess($post, $user)) {
                    $results[] = $post;
                }
            } else {
                $results[] = $post;
            }
        }

        return $results;
    }



    /**
     * Include metadata to file title in search
     *
     * @param string  $title Title
     * @param integer $id    File Id
     *
     * @return string
     */
    public function wpfdAddMetadata($title, $id = null)
    {
        global $wp_query;
        $app = Application::getInstance('Wpfd');
        $fileModel = Model::getInstance('filefront');

        if ($wp_query->is_search && get_post_type($id) === 'wpfd_file') {
            $fileInfo = $fileModel->getFile($id);

            if (!$fileInfo) {
                return $title;
            }
            return $title . '.' . $fileInfo->ext . '&nbsp;(' . WpfdHelperFiles::bytesToSize($fileInfo->size) . ')';
        }
        return $title;
    }

    /**
     * Replace content with shortcode
     *
     * @param string $content Content
     *
     * @return string
     */
    public function wpfdTheContentSearch($content)
    {
        global $post;

        if (isset($post->post_type) && $post->post_type === 'wpfd_file') {
            if (is_search()) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escape in wpfdFileContent
                echo self::wpfdFileContent();

                return '';
            }
        }
        return $content;
    }

    /**
     * Get single file content
     *
     * @return string
     */
    public static function wpfdFileContent()
    {
        global $post;

        $app = Application::getInstance('Wpfd');
        $fileModel = Model::getInstance('filefront');
        $fileInfo = $fileModel->getFile($post->ID);

        return do_shortcode(
            '[wpfd_single_file id="' . esc_attr($fileInfo->ID) . '" catid ="' . esc_attr($fileInfo->catid) . '" name ="' . esc_attr($fileInfo->post_title) . '"]'
        );
    }

    /**
     * Replace file permalink by download or preview link
     *
     * @param string $permalink Link
     * @param mixed  $post      Post Object
     *
     * @return string
     */
    public function wpfdSearchPermalink($permalink, $post)
    {
        global $wp_query;
        $app = Application::getInstance('Wpfd');
        $fileModel = Model::getInstance('filefront');

        if ($wp_query->is_search && $post->post_type === 'wpfd_file') {
            $fileInfo = $fileModel->getFile($post->ID);
            if ($fileInfo) {
                if (isset($fileInfo->viewerlink)) {
                    return $fileInfo->viewerlink;
                }

                return $fileInfo->linkdownload;
            }
        }

        return $permalink;
    }

    /**
     * Function to avoid error when apply_filters
     *
     * @param mixed $termId Term id
     *
     * @return void
     */
    public function wpfdAddonCategoryFrom($termId)
    {
    }

    /**
     * Redirect to download link
     *
     * @param mixed $query Wordpress query
     *
     * @return void
     */
    public function wpfdRedirect($query)
    {
        if (!empty($query->query_vars['wpfd_filename']) && !empty($query->query_vars['wpfd_file_id']) &&
            !empty($query->query_vars['wpfd_category_id']) && !empty($query->query_vars['wpfd_category_name'])
        ) {
            Application::getInstance('Wpfd');
            $path_control_file = dirname(WPFD_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
            $path_control_file .= 'site' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'file.php';
            include_once($path_control_file);
            $fileController = new WpfdControllerFile();
            $fileController->download($query->query_vars['wpfd_file_id'], $query->query_vars['wpfd_category_id']);
            exit;
        } elseif (!empty($query->query_vars['wpfd_category_id']) && !empty($query->query_vars['wpfd_category_name']) &&
            !empty($query->query_vars['wpfd_download_cat'])
        ) {
            Application::getInstance('Wpfd');
            $path_control_files = dirname(WPFD_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
            $path_control_files .= 'site' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'files.php';
            include_once($path_control_files);
            $filesController = new WpfdControllerFiles();
            $filesController->download(
                $query->query_vars['wpfd_category_id'],
                $query->query_vars['wpfd_category_name']
            );
            exit;
        }
    }

    /**
     * Method to flush rules
     *
     * @return void
     */
    public function wpfdFlushRules()
    {
        $rules = get_option('rewrite_rules');
        // Flush rule on download only.
        $config = get_option('_wpfd_global_config');
        if (empty($config) || empty($config['uri'])) {
            $seo_uri = 'download';
        } else {
            $seo_uri = rawurlencode($config['uri']);
        }

        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], $seo_uri) > 0 && (!isset($rules['index.php/([^/]*)/([0-9]+)/([^/]*)/(.*)/([^/]*)/?']) ||
            !isset($rules['([^/]*)/([0-9]+)/([^/]*)/(.*)/([^/]*)/?']))) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

    /**
     * Insert rewrite rules
     *
     * @param array $rules Rulers array
     *
     * @return array
     */
    public function wpfdInsertRewriteRules($rules)
    {
        $config = get_option('_wpfd_global_config');
        if (empty($config) || empty($config['uri'])) {
            $seo_uri = 'download';
        } else {
            $seo_uri = rawurlencode($config['uri']);
        }

        $newrules = array();
        $url1 = site_url();
        $url2 = home_url();

        $index = '';
        if (strpos($url1, $url2) !== false) {
            $index = str_replace($url2, '', $url1);
            $index = trim($index, '/');
        }

        if ($index !== '') {
            $url_str_1 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download&';
            $url_str_1 .= 'wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]';
            $url_str_1 .= '&wpfd_filename=$matches[4]';
            $site_url_1 = site_url($url_str_1);
            $newrules['index.php/' . $index . '/' . $seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = $site_url_1;

            $url_str_2 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download';
            $url_str_2 .= '&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]';
            $url_str_2 .= '&wpfd_filename=$matches[4]';
            $site_url_2 = site_url($url_str_2);
            $newrules[$index . '/' . $seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = $site_url_2;

            $url_str_3 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=files.download';
            $url_str_3 .= '&wpfd_download_cat=$matches[1]&wpfd_category_id=$matches[2]&wpfd_category_name=$matches[3]';
            $site_url_3 = site_url($url_str_3);
            $newrules['index.php/' . $index . '/' . $seo_uri . '/([^/]*)/([0-9]+)/([^/]*)/?'] = $site_url_3;

            $url_str_4 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=files.download';
            $url_str_4 .= '&wpfd_download_cat=$matches[1]&wpfd_category_id=$matches[2]&wpfd_category_name=$matches[3]';
            $site_url_4 = site_url($url_str_4);
            $newrules[$index . '/' . $seo_uri . '/([^/]*)/([0-9]+)/([^/]*)/?'] = $site_url_4;
        } else {
            $url_str_1 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download';
            $url_str_1 .= '&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]';
            $url_str_1 .= '&wpfd_filename=$matches[4]';
            $site_url_1 = site_url($url_str_1);
            $newrules['index.php/' . $seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = $site_url_1;

            $url_str_2 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download';
            $url_str_2 .= '&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]';
            $url_str_2 .= '&wpfd_filename=$matches[4]';
            $site_url_2 = site_url($url_str_2);
            $newrules[$seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = $site_url_2;

            $url_str_3 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=files.download';
            $url_str_3 .= '&wpfd_download_cat=$matches[1]&wpfd_category_id=$matches[2]&wpfd_category_name=$matches[3]';
            $site_url_3 = site_url($url_str_3);
            $newrules['index.php/' . $seo_uri . '/([^/]*)/([0-9]+)/([^/]*)/?'] = $site_url_3;

            $url_str_4 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=files.download';
            $url_str_4 .= '&wpfd_download_cat=$matches[1]&wpfd_category_id=$matches[2]&wpfd_category_name=$matches[3]';
            $site_url_4 = site_url($url_str_4);
            $newrules[$seo_uri . '/([^/]*)/([0-9]+)/([^/]*)/?'] = $site_url_4;
        }
        // Fix conflict with Membership Pro Ultimate WP
        if (class_exists('Ihc_Db')) {
            $inside_page = get_option('ihc_general_register_view_user');
            if ($inside_page && !defined('DOING_AJAX')) {
                $page_slug = Ihc_Db::get_page_slug($inside_page);
                $newrules[$page_slug . '/([^/]+)/?'] = 'index.php?pagename=' . $page_slug . '&ihc_name=$matches[1]';
            }
        }

        return $newrules + $rules;
    }

    /**
     * Append vars for download
     *
     * @param array $vars Query vars
     *
     * @return array
     */
    public function wpfdInsertQueryVars($vars)
    {
        $wpfd_insert_query_array = array(
            'wpfd_filename',
            'wpfd_file_id',
            'wpfd_category_id',
            'wpfd_category_name',
            'wpfd_download_cat'
        );
        foreach ($wpfd_insert_query_array as $v) {
            array_push($vars, $v);
        }
        return $vars;
    }

    /**
     * Archive template for category
     *
     * @param string $template_path Template path
     *
     * @return string
     */
    public function includeTemplate($template_path)
    {
        $post_type = get_query_var('post_type');
        $plugin_path = plugin_dir_path(WPFD_PLUGIN_FILE);
        if (is_tax('wpfd-category')) {
            if (get_post_type() === 'wpfd_file') {
                if (is_archive()) {
                    $theme_file = locate_template(array('archive-wpfd-category.php'));
                    if ($theme_file) {
                        $template_path = $theme_file;
                    } else {
                        $template_path = $plugin_path . 'app/site/themes/archive-wpfd-category.php';
                    }
                }
            } else {
                $wpfd_category = Utilities::getInput('wpfd-category', 'GET', 'none');
                if (!empty($wpfd_category)) {
                    $theme_file = locate_template(array('empty-wpfd-category.php'));
                    if ($theme_file) {
                        $template_path = $theme_file;
                    } else {
                        $template_path = $plugin_path . 'app/site/themes/empty-wpfd-category.php';
                    }
                }
            }
        } elseif ($post_type === 'wpfd_file') {
            if ($this->includeGlobalSearch) {
                $theme_file = locate_template(array('wpfd-single.php'));
                if ($theme_file) {
                    $template_path = $theme_file;
                } else {
                    $template_path = $plugin_path . 'app/site/themes/wpfd-single.php';
                }
            }
        }

        return $template_path;
    }

    /**
     * Method module content
     *
     * @param string $content Content
     *
     * @return string
     */
    public function themifyModuleContent($content)
    {
        $content = $this->wpfdReplace($content);
        return $content;
    }

    /**
     * Method replace content
     *
     * @param string $content Content
     *
     * @return string
     */
    public function wpfdReplace($content)
    {
        $content = preg_replace_callback(
            '@<img[^>]*?data\-wpfdcategory="([0-9]+)".*?>@',
            array($this, 'replace'),
            $content
        );

        //Replace single file
        $content = preg_replace_callback(
            '@<img[^>]*?data\-wpfdfile="(.*?)".*?>@',
            array($this, 'replaceSingle'),
            $content
        );

        return $content;
    }

    /**
     * Replace single category callback
     *
     * @param array $match Match place holder
     *
     * @return string
     */
    private function replace($match)
    {
        add_action('wp_footer', array($this->shortcodes, 'wpfdFooter'));
        return $this->shortcodes->callTheme($match[1]);
    }

    /**
     * Replace single file callback
     *
     * @param array $match Match place holder
     *
     * @return string
     */
    private function replaceSingle($match)
    {
        //get category of file then check access role
        preg_match('@.*data\-category="([0-9]+)".*@', $match[0], $matchCat);
        if (!empty($matchCat)) {
            $catid = (int)$matchCat[1];
        } else {
            $term_list = wp_get_post_terms((int)$match[1], 'wpfd-category', array('fields' => 'ids'));
            $catid = (isset($term_list[0])) ? $term_list[0] : 0;
        }
        return $this->shortcodes->callSingleFile($match[1], $catid);
    }


    /**
     * Function acf filter to replace plugin holder place
     *
     * @param string  $value   Value load from database
     * @param integer $post_id Id of current post
     * @param string  $field   Name of current field
     *
     * @return string
     */
    public function wpfdAcfLoadValue($value, $post_id, $field)
    {
        if (is_string($value)) {
            $value = $this->wpfdReplace($value);
        }

        return $value;
    }
}

<?php
/**
 * WordPress Importer class for managing the import process of a WXR file
 *
 * @package    WordPress
 * @subpackage Importer
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;

/**
 * WordPress importer class.
 */
class WPFD_Import extends WP_Importer
{
    var $max_wxr_version = 1.2; // max. supported WXR version

    var $id; // WXR attachment ID

    // information to import from WXR file
    var $version;
    var $authors = array();
    var $posts = array();
    var $terms = array();
    var $categories = array();
    var $tags = array();
    var $base_url = '';

    // mappings from old information to new
    var $processed_authors = array();
    var $author_mapping = array();
    var $processed_terms = array();
    var $processed_posts = array();
    var $post_orphans = array();
    var $processed_menu_items = array();
    var $menu_item_orphans = array();
    var $missing_menu_items = array();

    var $import_only_folder = false;
    var $url_remap = array();
    var $featured_images = array();
    var $error_message = '';
    var $new_term_ids = array();
    var $new_post_ids = array();

    /**
     * Start import
     *
     * @param string|mixed   $path               Upload file path
     * @param integer|mixed  $id_file            Upload file id
     * @param boolean|string $import_only_folder Import only folder option
     * @param integer $categoryDisc              Category disc
     *
     * @return void
     */
    function start($path = '', $id_file, $import_only_folder = true, $categoryDisc)
    {
        $this->import_only_folder   = $import_only_folder;
        $this->id                   = $id_file;
        $file                       = $path;
        $import_data                = $this->parse($file);

        if (is_wp_error($import_data)) {
            $this->error_message .= '<p><strong>' . __('Sorry, there has been an error.', 'wpfd') . '</strong><br />';
            $this->error_message .= esc_html($import_data->get_error_message()) . '</p>';
            wp_send_json(array('status' => true, 'msg' => $this->error_message));
        }

        $this->version = $import_data['version'];
        if ($this->version > $this->max_wxr_version) {
            $this->error_message .= '<div class="error"><p><strong>';
            $this->error_message .= sprintf(__('This WXR file (version %s) may not be supported by this version of the importer. Please consider updating.', 'wpfd'), esc_html($import_data['version']));
            $this->error_message .= '</strong></p></div>';
            wp_send_json(array('status' => true, 'msg' => $this->error_message));
        }

        $this->get_authors_from_import($import_data);

        set_time_limit(0);
        $this->import($file, $categoryDisc);
    }

    /**
     * Parse a WXR file
     *
     * @param  string $file Path to WXR file for parsing
     * @return array Information gathered from the WXR file
     */
    function parse($file)
    {
        $parser = new WXR_Parser();
        return $parser->parse($file);
    }

    /**
     * Retrieve authors from parsed WXR data
     *
     * Uses the provided author information from WXR 1.1 files
     * or extracts info from each post for WXR 1.0 files
     *
     * @param array $import_data Data returned by a WXR parser
     */
    function get_authors_from_import($import_data)
    {
        if (!empty($import_data['authors'])) {
            $this->authors = $import_data['authors'];
            // no author information, grab it from the posts
        } else {
            foreach ($import_data['posts'] as $post) {
                $login = sanitize_user($post['post_author'], true);
                if (empty($login)) {
                    $this->error_message .= sprintf(__('Failed to import author %s. Their posts will be attributed to the current user.', 'wpfd'), esc_html($post['post_author']));
                    $this->error_message .= '<br />';
                    continue;
                }

                if (!isset($this->authors[$login])) {
                    $this->authors[$login] = array(
                        'author_login' => $login,
                        'author_display_name' => $post['post_author']
                    );
                }
            }
        }
    }

    /**
     * The main controller for the actual import stage.
     *
     * @param string $file          Path to the WXR file for importing
     * @param integer $categoryDisc Category disc
     */
    function import($file, $categoryDisc)
    {
        add_filter('import_post_meta_key', array($this, 'is_valid_meta_key'));
        add_filter('http_request_timeout', array($this, 'bump_request_timeout'));

        $this->import_start($file);

        $this->get_author_mapping();
        wp_suspend_cache_invalidation(true);
        $this->process_categories();
        $this->process_tags();
        $this->process_terms($categoryDisc);
        if (!$this->import_only_folder) {
            $this->process_posts();
        }
        wp_suspend_cache_invalidation(false);

        // update incorrect/missing information in the DB
        $this->processRefToFiles();
        $this->backfill_parents();
        $this->backfill_attachment_urls();
        $this->remap_featured_images();

        $this->import_end();
    }

    /**
     * Parses the WXR file and prepares us for the task of processing parsed data
     *
     * @param string $file Path to the WXR file for importing
     */
    function import_start($file)
    {
        if (!is_file($file)) {
            $this->error_message .= '<p><strong>' . __('Sorry, there has been an error.', 'wpfd') . '</strong><br />';
            $this->error_message .= __('The file does not exist, please try again.', 'wpfd') . '</p>';
            wp_send_json(array('status' => true, 'msg' => $this->error_message));
        }

        $import_data = $this->parse($file);

        if (is_wp_error($import_data)) {
            $this->error_message .= '<p><strong>' . __('Sorry, there has been an error.', 'wpfd') . '</strong><br />';
            $this->error_message .= esc_html($import_data->get_error_message()) . '</p>';
            wp_send_json(array('status' => true, 'msg' => $this->error_message));
        }

        $this->version = $import_data['version'];
        $this->get_authors_from_import($import_data);
        $this->posts = $import_data['posts'];
        $this->terms = $import_data['terms'];
        $this->categories = $import_data['categories'];
        $this->tags = $import_data['tags'];
        $this->base_url = esc_url($import_data['base_url']);

        wp_defer_term_counting(true);
        wp_defer_comment_counting(true);

        do_action('import_start');
    }

    /**
     * Performs post-import cleanup of files and the cache
     */
    function import_end()
    {
        wp_import_cleanup($this->id);

        wp_cache_flush();
        foreach (get_taxonomies() as $tax) {
            delete_option("{$tax}_children");
            _get_term_hierarchy($tax);
        }

        wp_defer_term_counting(false);
        wp_defer_comment_counting(false);

        $this->error_message .= '<p>' . __('All done.', 'wpfd') . ' <a href="' . admin_url() . '">' . __('Have fun!', 'wpfd') . '</a>' . '</p>';
        $this->error_message .= '<p>' . __('Remember to update the passwords and roles of imported users.', 'wpfd') . '</p>';

        do_action('import_end');
    }

    /**
     * Decide if the given meta key maps to information we will want to import
     *
     * @param  string $key The meta key to check
     * @return string|boolean The key if we do want to import, false if not
     */
    function is_valid_meta_key($key)
    {
        // skip attachment metadata since we'll regenerate it from scratch
        // skip _edit_lock as not relevant for import
        if (in_array($key, array('_wp_attached_file', '_wp_attachment_metadata', '_edit_lock'))) {
            return false;
        }
        return $key;
    }

    /**
     * Added to http_request_timeout filter to force timeout at 60 seconds during import
     *
     * @return integer 60
     */
    function bump_request_timeout($val)
    {
        return 60;
    }

    /**
     * Map old author logins to local user IDs based on decisions made
     * in import options form. Can map to an existing user, create a new user
     * or falls back to the current user in case of error with either of the previous
     */
    function get_author_mapping()
    {
        $create_users = $this->allow_create_users();
        foreach ((array)$this->authors as $i => $author) {
            // Multisite adds strtolower to sanitize_user. Need to sanitize here to stop breakage in process_posts.
            $santized_old_login = sanitize_user($author['author_login'], true);
            $old_id = isset($this->authors[$author['author_login']]['author_id']) ? intval($this->authors[$author['author_login']]['author_id']) : false;
            if ($create_users) {
                $user_data = array(
                    'user_login' => $author['author_login'],
                    'user_pass' => wp_generate_password(),
                    'user_email' => isset($this->authors[$author['author_login']]['author_email']) ? $this->authors[$author['author_login']]['author_email'] : '',
                    'display_name' => $this->authors[$author['author_login']]['author_display_name'],
                    'first_name' => isset($this->authors[$author['author_login']]['author_first_name']) ? $this->authors[$author['author_login']]['author_first_name'] : '',
                    'last_name' => isset($this->authors[$author['author_login']]['author_last_name']) ? $this->authors[$author['author_login']]['author_last_name'] : '',
                );
                $user_id = wp_insert_user($user_data);
                if (!is_wp_error($user_id)) {
                    if ($old_id) {
                        $this->processed_authors[$old_id] = $user_id;
                    }
                    $this->author_mapping[$santized_old_login] = $user_id;
                } else {
                    $this->error_message .= sprintf(__('Failed to create new user for %s. Their posts will be attributed to the current user.', 'wpfd'), esc_html($this->authors[$author['author_login']]['author_display_name']));
                    if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                        $this->error_message .= ' ' . $user_id->get_error_message();
                    }
                    $this->error_message .= '<br />';
                }
            }

            // failsafe: if the user_id was invalid, default to the current user
            if (!isset($this->author_mapping[$santized_old_login])) {
                if ($old_id) {
                    $this->processed_authors[$old_id] = (int)get_current_user_id();
                }
                $this->author_mapping[$santized_old_login] = (int)get_current_user_id();
            }
        }
    }

    /**
     * Decide whether or not the importer is allowed to create users.
     * Default is true, can be filtered via import_allow_create_users
     *
     * @return boolean True if creating users is allowed
     */
    function allow_create_users()
    {
        return apply_filters('import_allow_create_users', true);
    }

    /**
     * Create new categories based on import information
     *
     * Doesn't create a new category if its slug already exists
     */
    function process_categories()
    {
        $this->categories = apply_filters('wpfd_import_categories', $this->categories);

        if (empty($this->categories)) {
            return;
        }

        foreach ($this->categories as $cat) {
            // if the category already exists leave it alone
            $term_id = term_exists($cat['category_nicename'], 'category');
            if ($term_id) {
                if (is_array($term_id)) {
                    $term_id = $term_id['term_id'];
                }
                if (isset($cat['term_id'])) {
                    $this->processed_terms[intval($cat['term_id'])] = (int)$term_id;
                }
                continue;
            }

            $parent = empty($cat['category_parent']) ? 0 : category_exists($cat['category_parent']);
            $description = isset($cat['category_description']) ? $cat['category_description'] : '';

            $data = array(
                'category_nicename' => $cat['category_nicename'],
                'category_parent' => $parent,
                'cat_name' => wp_slash($cat['cat_name']),
                'category_description' => wp_slash($description),
            );

            $id = wp_insert_category($data);
            if (!is_wp_error($id) && $id > 0) {
                if (isset($cat['term_id'])) {
                    $this->processed_terms[intval($cat['term_id'])] = $id;
                }
            } else {
                $this->error_message .= sprintf(__('Failed to import category %s', 'wpfd'), esc_html($cat['category_nicename']));
                if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                    $this->error_message .= ': ' . $id->get_error_message();
                }
                $this->error_message .= '<br />';
                continue;
            }

            $this->process_termmeta($cat, $id);
        }

        unset($this->categories);
    }

    /**
     * Add metadata to imported term.
     *
     * @param array   $term    Term data from WXR import.
     * @param integer $term_id ID of the newly created term.
     * @since 0.6.2
     */
    protected function process_termmeta($term, $term_id)
    {
        if (!function_exists('add_term_meta')) {
            return;
        }

        if (!isset($term['termmeta'])) {
            $term['termmeta'] = array();
        }

        /**
         * Filters the metadata attached to an imported term.
         *
         * @param array $termmeta Array of term meta.
         * @param int $term_id ID of the newly created term.
         * @param array $term Term data from the WXR import.
         * @since 0.6.2
         */
        $term['termmeta'] = apply_filters('wp_import_term_meta', $term['termmeta'], $term_id, $term);

        if (empty($term['termmeta'])) {
            return;
        }

        foreach ($term['termmeta'] as $meta) {
            /**
             * Filters the meta key for an imported piece of term meta.
             *
             * @param string $meta_key Meta key.
             * @param int $term_id ID of the newly created term.
             * @param array $term Term data from the WXR import.
             * @since 0.6.2
             */
            $key = apply_filters('import_term_meta_key', $meta['key'], $term_id, $term);
            if (!$key) {
                continue;
            }

            // Export gets meta straight from the DB so could have a serialized string
            $value = maybe_unserialize($meta['value']);

            add_term_meta($term_id, wp_slash($key), wp_slash_strings_only($value));

            /**
             * Fires after term meta is imported.
             *
             * @param int $term_id ID of the newly created term.
             * @param string $key Meta key.
             * @param mixed $value Meta value.
             * @since 0.6.2
             */
            do_action('import_term_meta', $term_id, $key, $value);
        }
    }

    /**
     * Create new post tags based on import information
     *
     * Doesn't create a tag if its slug already exists
     */
    function process_tags()
    {
        $this->tags = apply_filters('wp_import_tags', $this->tags);

        if (empty($this->tags)) {
            return;
        }

        foreach ($this->tags as $tag) {
            // if the tag already exists leave it alone
            $term_id = term_exists($tag['tag_slug'], 'post_tag');
            if ($term_id) {
                if (is_array($term_id)) {
                    $term_id = $term_id['term_id'];
                }
                if (isset($tag['term_id'])) {
                    $this->processed_terms[intval($tag['term_id'])] = (int)$term_id;
                }
                continue;
            }

            $description = isset($tag['tag_description']) ? $tag['tag_description'] : '';
            $args = array(
                'slug' => $tag['tag_slug'],
                'description' => wp_slash($description),
            );

            $id = wp_insert_term(wp_slash($tag['tag_name']), 'post_tag', $args);
            if (!is_wp_error($id)) {
                if (isset($tag['term_id'])) {
                    $this->processed_terms[intval($tag['term_id'])] = $id['term_id'];
                }
            } else {
                $this->error_message .= sprintf(__('Failed to import post tag %s', 'wpfd'), esc_html($tag['tag_name']));
                if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                    $this->error_message .= ': ' . $id->get_error_message();
                }
                $this->error_message .= '<br />';
                continue;
            }

            $this->process_termmeta($tag, $id['term_id']);
        }

        unset($this->tags);
    }

    /**
     * Create new terms based on import information
     *
     * Doesn't create a term its slug already exists
     *
     * @param integer $categoryDisc Category disc
     */
    function process_terms($categoryDisc)
    {
        $this->terms = apply_filters('wp_import_terms', $this->terms);

        if (empty($this->terms)) {
            return;
        }

        $author = (int)get_current_user_id();
        Application::getInstance('Wpfd');
        $rolesModel = Model::getInstance('roles');

        foreach ($this->terms as $term) {
            // if the term already exists in the correct taxonomy leave it alone
            $term_id = term_exists($term['slug'], $term['term_taxonomy']);
            if ($term_id) {
                if (is_array($term_id)) {
                    $term_id = $term_id['term_id'];
                }
                if (isset($term['term_id'])) {
                    $this->processed_terms[intval($term['term_id'])] = (int)$term_id;
                }
                continue;
            }

            if (empty($term['term_parent']) || $term['term_parent'] === '') {
                $parent = ( $categoryDisc !== '' &&  (int)$categoryDisc > 0 ) ? (int)$categoryDisc : 0;
            } else {
                $parent = term_exists($term['term_parent'], $term['term_taxonomy']);
                if (is_array($parent)) {
                    $parent = $parent['term_id'];
                }
            }

            $description        = isset($term['term_description']) ? $term['term_description'] : '';
            $description        = json_decode($description);
            $description->category_own = $description->category_own_old = (string) $author;
            $description->canview = '';
            $description->visibility = (string) 0;

            $description = json_encode($description);

            $args = array(
                'slug' => $term['slug'],
                'description' => wp_slash($description),
                'parent' => (int)$parent
            );
            $id = wp_insert_term(wp_slash($term['term_name']), $term['term_taxonomy'], $args);
            if (!is_wp_error($id)) {
                if (isset($term['term_id'])) {
                    $this->processed_terms[intval($term['term_id'])] = $id['term_id'];
                }
            } else {
                $this->error_message .= sprintf(__('Failed to import %1$s %2$s', 'wpfd'), esc_html($term['term_taxonomy']), esc_html($term['term_name']));
                if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                    $this->error_message .= ': ' . $id->get_error_message();
                }
                $this->error_message .= '<br />';
                continue;
            }

            // Map visibility, roles
            $category_params = json_decode($description);
            if (isset($category_params->visibility)) {
                $visibility = $category_params->visibility;
                $roles      = array();
                $rolesModel->save((int)$id['term_id'], $visibility, $roles);
            }

            $this->process_termmeta($term, $id['term_id']);

            // Map changed terms
            $this->new_term_ids[intval($term['term_id'])] = $id['term_id'];
        }

        unset($this->terms);
    }

    /**
     * Create new posts based on import information
     *
     * Posts marked as having a parent which doesn't exist will become top level items.
     * Doesn't create a new post if: the post type doesn't exist, the given post ID
     * is already noted as imported or a post with the same title and date already exists.
     * Note that new/updated terms, comments and meta are imported for the last of the above.
     */
    function process_posts()
    {
        $this->posts = apply_filters('wp_import_posts', $this->posts);

        foreach ($this->posts as $post) {
            $post = apply_filters('wp_import_post_data_raw', $post);

            if (!post_type_exists($post['post_type'])) {
                $this->error_message .= sprintf(
                    __('Failed to import &#8220;%1$s&#8221;: Invalid post type %2$s', 'wpfd'),
                    esc_html($post['post_title']),
                    esc_html($post['post_type'])
                );
                $this->error_message .= '<br />';
                do_action('wp_import_post_exists', $post);
                continue;
            }

            if (isset($this->processed_posts[$post['post_id']]) && !empty($post['post_id'])) {
                continue;
            }

            if ($post['status'] == 'auto-draft') {
                continue;
            }

//            if ('nav_menu_item' == $post['post_type']) {
//                $this->process_menu_item($post);
//                continue;
//            }

            $post_type_object = get_post_type_object($post['post_type']);

            $post_exists      = post_exists($post['post_title'], '', $post['post_date']);

            /**
             * Filter ID of the existing post corresponding to post currently importing.
             *
             * Return 0 to force the post to be imported. Filter the ID to be something else
             * to override which existing post is mapped to the imported post.
             *
             * @param int $post_exists Post ID, or 0 if post did not exist.
             * @param array $post The post array to be inserted.
             * @see   post_exists()
             * @since 0.6.2
             */
            $post_exists = apply_filters('wp_import_existing_post', $post_exists, $post);

            if ($post_exists && get_post_type($post_exists) == $post['post_type']) {
                $this->error_message .= sprintf(__('%1$s &#8220;%2$s&#8221; already exists.', 'wpfd'), $post_type_object->labels->singular_name, esc_html($post['post_title']));
                $this->error_message .= '<br />';
                $comment_post_ID = $post_id = $post_exists;
                $this->processed_posts[intval($post['post_id'])] = intval($post_exists);
            } else {
                $post_parent = (int)$post['post_parent'];
                if ($post_parent) {
                    // if we already know the parent, map it to the new local ID
                    if (isset($this->processed_posts[$post_parent])) {
                        $post_parent = $this->processed_posts[$post_parent];
                        // otherwise record the parent for later
                    } else {
                        $this->post_orphans[intval($post['post_id'])] = $post_parent;
                        $post_parent = 0;
                    }
                }

                // map the post author
                $author = sanitize_user($post['post_author'], true);
                if (isset($this->author_mapping[$author])) {
                    $author = $this->author_mapping[$author];
                } else {
                    $author = (int)get_current_user_id();
                }

                $postdata   = array(
                    'import_id' => $post['post_id'], 'post_author' => $author, 'post_date' => $post['post_date'],
                    'post_date_gmt' => $post['post_date_gmt'], 'post_content' => $post['post_content'],
                    'post_excerpt' => $post['post_excerpt'], 'post_title' => $post['post_title'],
                    'post_status' => $post['status'], 'post_name' => $post['post_name'],
                    'comment_status' => $post['comment_status'], 'ping_status' => $post['ping_status'],
                    'guid' => $post['guid'], 'post_parent' => $post_parent, 'menu_order' => $post['menu_order'],
                    'post_type' => $post['post_type'], 'post_password' => $post['post_password'],
                    'terms' => $post['terms']
                );

                $original_post_ID = $post['post_id'];
                $postdata = apply_filters('wp_import_post_data_processed', $postdata, $post);

                $postdata = wp_slash($postdata);

                if ('wpfd_file' == $postdata['post_type']) {
                    $remote_url = !empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];
                    $postdata['upload_date'] = $post['post_date'];
                    $comment_post_ID = $post_id = $this->process_attachment($postdata, $remote_url);
                } else {
                    if (isset($postdata['terms'])) {
                        unset($postdata['terms']);
                    }
                    $comment_post_ID = $post_id = wp_insert_post($postdata, true);
                    do_action('wp_import_insert_post', $post_id, $original_post_ID, $postdata, $post);
                }

                if (is_wp_error($post_id)) {
                    $this->error_message .= sprintf(
                        __('Failed to import %1$s &#8220;%2$s&#8221;', 'wpfd'),
                        $post_type_object->labels->singular_name,
                        esc_html($post['post_title'])
                    );
                    if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                        $this->error_message .= ': ' . $post_id->get_error_message();
                    }
                    $this->error_message .= '<br />';
                    continue;
                }

                if ($post['is_sticky'] == 1) {
                    stick_post($post_id);
                }

                // Map changed posts
                $this->new_post_ids[intval($post['post_id'])] = (int)$post_id;
            }

            // map pre-import ID to local ID
            $this->processed_posts[intval($post['post_id'])] = (int)$post_id;

            if (!isset($post['terms'])) {
                $post['terms'] = array();
            }

            $post['terms'] = apply_filters('wp_import_post_terms', $post['terms'], $post_id, $post);

            // add categories, tags and other terms
            if (!empty($post['terms'])) {
                $terms_to_set = array();
                foreach ($post['terms'] as $term) {
                    // back compat with WXR 1.0 map 'tag' to 'post_tag'
                    $taxonomy = ('tag' == $term['domain']) ? 'post_tag' : $term['domain'];
                    $term_exists = term_exists($term['slug'], $taxonomy);
                    $term_id = is_array($term_exists) ? $term_exists['term_id'] : $term_exists;
                    if (!$term_id) {
                        $t = wp_insert_term($term['name'], $taxonomy, array('slug' => $term['slug']));
                        if (!is_wp_error($t)) {
                            $term_id = $t['term_id'];
                            do_action('wp_import_insert_term', $t, $term, $post_id, $post);
                        } else {
                            $this->error_message .= sprintf(__('Failed to import %1$s %2$s', 'wpfd'), esc_html($taxonomy), esc_html($term['name']));
                            if (defined('IMPORT_DEBUG') && IMPORT_DEBUG) {
                                $this->error_message .= ': ' . $t->get_error_message();
                            }
                            $this->error_message .= '<br />';
                            do_action('wp_import_insert_term_failed', $t, $term, $post_id, $post);
                            continue;
                        }
                    }
                    $terms_to_set[$taxonomy][] = intval($term_id);
                }

                foreach ($terms_to_set as $tax => $ids) {
                    $tt_ids = wp_set_post_terms($post_id, $ids, $tax);
                    do_action('wp_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $post);
                }
                unset($post['terms'], $terms_to_set);
            }

            if (!isset($post['comments'])) {
                $post['comments'] = array();
            }

            $post['comments'] = apply_filters('wp_import_post_comments', $post['comments'], $post_id, $post);

            // add/update comments
            if (!empty($post['comments'])) {
                $num_comments = 0;
                $inserted_comments = array();
                foreach ($post['comments'] as $comment) {
                    $comment_id = $comment['comment_id'];
                    $newcomments[$comment_id]['comment_post_ID'] = $comment_post_ID;
                    $newcomments[$comment_id]['comment_author'] = $comment['comment_author'];
                    $newcomments[$comment_id]['comment_author_email'] = $comment['comment_author_email'];
                    $newcomments[$comment_id]['comment_author_IP'] = $comment['comment_author_IP'];
                    $newcomments[$comment_id]['comment_author_url'] = $comment['comment_author_url'];
                    $newcomments[$comment_id]['comment_date'] = $comment['comment_date'];
                    $newcomments[$comment_id]['comment_date_gmt'] = $comment['comment_date_gmt'];
                    $newcomments[$comment_id]['comment_content'] = $comment['comment_content'];
                    $newcomments[$comment_id]['comment_approved'] = $comment['comment_approved'];
                    $newcomments[$comment_id]['comment_type'] = $comment['comment_type'];
                    $newcomments[$comment_id]['comment_parent'] = $comment['comment_parent'];
                    $newcomments[$comment_id]['commentmeta'] = isset($comment['commentmeta']) ? $comment['commentmeta'] : array();
                    if (isset($this->processed_authors[$comment['comment_user_id']])) {
                        $newcomments[$comment_id]['user_id'] = $this->processed_authors[$comment['comment_user_id']];
                    }
                }
                ksort($newcomments);

                foreach ($newcomments as $key => $comment) {
                    // if this is a new post we can skip the comment_exists() check
                    if (!$post_exists || !comment_exists($comment['comment_author'], $comment['comment_date'])) {
                        if (isset($inserted_comments[$comment['comment_parent']])) {
                            $comment['comment_parent'] = $inserted_comments[$comment['comment_parent']];
                        }

                        $comment_data = wp_slash($comment);
                        unset($comment_data['commentmeta']); // Handled separately, wp_insert_comment() also expects `comment_meta`.
                        $comment_data = wp_filter_comment($comment_data);

                        $inserted_comments[$key] = wp_insert_comment($comment_data);

                        do_action('wp_import_insert_comment', $inserted_comments[$key], $comment, $comment_post_ID, $post);

                        foreach ($comment['commentmeta'] as $meta) {
                            $value = maybe_unserialize($meta['value']);

                            add_comment_meta($inserted_comments[$key], wp_slash($meta['key']), wp_slash_strings_only($value));
                        }

                        $num_comments++;
                    }
                }
                unset($newcomments, $inserted_comments, $post['comments']);
            }

            if (!isset($post['postmeta'])) {
                $post['postmeta'] = array();
            }

            $post['postmeta'] = apply_filters('wp_import_post_meta', $post['postmeta'], $post_id, $post);

            // add/update post meta
            if (!empty($post['postmeta'])) {
                foreach ($post['postmeta'] as $meta) {
                    $key   = apply_filters('import_post_meta_key', $meta['key'], $post_id, $post);
                    $value = false;

                    if ('_edit_last' == $key) {
                        if (isset($this->processed_authors[intval($meta['value'])])) {
                            $value = $this->processed_authors[intval($meta['value'])];
                        } else {
                            $key = false;
                        }
                    }

                    if ($key) {
                        // export gets meta straight from the DB so could have a serialized string
                        if (!$value) {
                            $value = maybe_unserialize($meta['value']);
                        }

                        $post_metadata = get_post_meta($post_id, '_wpfd_file_metadata', true);

                        $value['file'] = (isset($post_metadata['file'])) ? $post_metadata['file'] : '';
                        $value['hits'] = 0;
                        if (isset($value['file_custom_icon']) && $value['file_custom_icon'] !== '') {
                            $value['file_custom_icon'] = $this->processCustomIcon($value['file_custom_icon']);
                        }

                        update_post_meta($post_id, wp_slash($key), wp_slash_strings_only($value));

                        // if the post has a featured image, take note of this in case of remap
                        if ('_thumbnail_id' == $key) {
                            $this->featured_images[$post_id] = (int)$value;
                        }
                    }
                }
            }
        }

        unset($this->posts);
    }

    /**
     * ProcessRefToFiles
     */
    function processRefToFiles()
    {
        Application::getInstance('Wpfd');
        $categoryModel = Model::getInstance('category');
        $fileModel     = Model::getInstance('file');
        global $wpdb;
        if (!empty($this->new_term_ids) && !empty($this->new_post_ids)) {
            $args = array(
                'hide_empty'                    => false,
                'taxonomy'                      => 'wpfd-category',
                'pll_get_terms_not_translated'  => 1
            );

            $terms        = get_categories($args);

            foreach ($terms as $term) {
                $description        = ( isset($term->description) ) ? json_decode($term->description, true) : array();
                $new_description    = array();
                if (isset($description['refToFile']) && !empty($description['refToFile'])) {
                    $newRef = $newRefTerm = array();
                    foreach ($description['refToFile'] as $key => $value) {
                        // Map new term id
                        foreach ($this->new_term_ids as $old => $new) {
                            if ((int) $key === (int) $old) {
                                $key = (int) $new;
                                continue;
                            }
                        }

                        // Map new file id
                        $newRefFile = array();
                        foreach ($value as $old_file) {
                            foreach ($this->new_post_ids as $old_file_id => $new_file_id) {
                                if ((int)$old_file === (int)$old_file_id) {
                                    $newRefFile[] = $new_file_id;
                                    continue;
                                }
                            }
                        }

                        $newRef[$key] = $newRefFile;
                    }
                    $new_description                = $description;
                    $new_description['refToFile']   = $newRef;
                    $description                    = json_encode($description);
                    $new_description                = json_encode($new_description);

                    // Update new category params
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->term_taxonomy} SET description = REPLACE(description, %s, %s) WHERE term_taxonomy_id = %d",
                        $description,
                        $new_description,
                        (int)$term->term_id
                    ));
                }
            }

            $this->updateFiles();
        }
    }

    /**
     * If fetching attachments is enabled then attempt to create a new attachment
     *
     * @param  array  $post Attachment post details from WXR
     * @param  string $url  URL to fetch attachment from
     * @return integer|WP
     */
    function process_attachment($post, $url)
    {
        if ($this->import_only_folder) {
            return new WP_Error('attachment_processing_error', __('Fetching attachments is not enabled', 'wpfd'));
        }

        // if the URL is absolute, but does not contain address, then upload it assuming base_site_url
        if (preg_match('|^/[\w\W]+$|', $url)) {
            $url = rtrim($this->base_url, '/') . $url;
        }

        $upload = $this->fetch_remote_file($url, $post);

        if (is_wp_error($upload)) {
            return $upload;
        }

        if ($info = wp_check_filetype($upload['file'])) {
            $post['post_mime_type'] = $info['type'];
        } else {
            return new WP_Error('attachment_processing_error', __('Invalid file type', 'wpfd'));
        }

        $post['guid'] = $upload['file'];

        $post_id = $this->processUploadFiles($post, $url, $upload);

        return $post_id;
    }

    /**
     * processUploadFiles
     *
     * @param array        $post   Attachment post details from WXR
     * @param string       $url    URL to fetch attachment from
     * @param array|string $upload Upload params
     *
     * @return mixed|integer|WP
     */
    function processUploadFiles($post, $url, $upload)
    {
        Application::getInstance('Wpfd');
        $modelCat       = Model::getInstance('category');
        $configModel    = Model::getInstance('config');
        $modalNotify    = Model::getInstance('notification');
        $model          = Model::getInstance('files');
        $id_file = null;
        $default_allowed = array(
            'jpg',
            'jpeg',
            'png',
            'gif',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'zip',
            'tar',
            'rar',
            'odt',
            'ppt',
            'pps',
            'txt'
        );
        $args = array(
            'hide_empty'                    => false,
            'taxonomy'                      => 'wpfd-category',
            'pll_get_terms_not_translated'  => 1
        );

        $folders        = get_categories($args);
        $terms          = isset($post['terms']) ? $post['terms'] : array();
        $wpfd_term      = array();
        $id_category    = 0;
        foreach ($terms as $term) {
            if ('wpfd-category' === $term['domain']) {
                $wpfd_term = $term;
                continue;
            }
        }

        if (!empty($folders)) {
            foreach ($folders as $folder) {
                if ($folder->slug === $wpfd_term['slug'] && $folder->taxonomy === $wpfd_term['domain']) {
                    $id_category = $folder->term_id;
                    continue;
                }
            }
        }

        if (!term_exists($id_category, 'wpfd-category')) {
            $this->error_message .= sprintf(__('This category is no longer exists. It may be deleted!', 'wpfd'));
            $this->error_message .= '<br />';
        }

        if ($id_category <= 0) {
            $this->error_message .= sprintf(__('Wrong Category', 'wpfd'));
            $this->error_message .= '<br />';
        }

        if (isset($post['terms'])) {
            unset($post['terms']);
        }

        $category       = $modelCat->getCategory($id_category);
        $configNotify   = $modalNotify->getNotificationsConfig();
        $allowed        = $configModel->getAllowedExt();
        if (!empty($allowed)) {
            $default_allowed = $allowed;
        }

        $file_dir = WpfdBase::getFilesPath($id_category);

        if (!file_exists($file_dir)) {
            mkdir($file_dir, 0777, true);
            $data = '<html><body bgcolor="#FFFFFF"></body></html>';
            $file = fopen($file_dir . 'index.html', 'w');
            fwrite($file, $data);
            fclose($file);
            $data = 'deny from all';
            $file = fopen($file_dir . '.htaccess', 'w');
            fwrite($file, $data);
            fclose($file);
        }

        $file_name  = basename($url);
        $ext        = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $newname    = uniqid() . '.' . $ext;
        $file_title = ( isset($post['post_title']) ) ? $post['post_title'] : '';
        $path       = $file_dir . $newname;
        if (!file_put_contents($path, file_get_contents($url))) {
            $this->error_message .= sprintf(__('Upload failed', 'wpfd'));
            $this->error_message .= '<br />';
        }
        $id_file = $model->addFile(array(
            'title'         => $file_title,
            'id_category'   => $id_category,
            'file'          => $newname,
            'ext'           => $ext,
            'size'          => filesize($file_dir . $newname),
            'post_excerpt'  => $post['post_excerpt']
        ));

        if (!$id_file) {
            unlink($file_dir . $newname);
            $this->error_message .= sprintf(__('Can\'t save to database', 'wpfd'));
            $this->error_message .= '<br />';
        }

        return $id_file;
    }

    /**
     * ProcessCustomIcon
     *
     * @param string $icon_path File custom path
     *
     * @return string
     */
    function processCustomIcon($icon_path)
    {
        $from_path = $url = '';

        if (is_multisite()) {
            $base_site = network_home_url();
        } else {
            $base_site = get_bloginfo_rss('url');
        }

        if ($icon_path !== '') {
            $from_path      = $this->base_url . $icon_path;
            $file_content   = file_get_contents($from_path);
            $custom_name    = basename($from_path);

            $upload_dir     = wp_upload_dir();
            $destination    = $upload_dir['path'] . '/' . $custom_name;
            $url            = $upload_dir['url'] . '/' . $custom_name;

            file_put_contents($destination, $file_content);

            $url = str_replace($base_site, '', $url);
        }

        if ($url !== '') {
            return $url;
        } else {
            return $icon_path;
        }
    }

    /**
     * Attempt to download a remote file attachment
     *
     * @param  string $url  URL of item to fetch
     * @param  array  $post Attachment details
     * @return array|WP_Error Local file location details on success, WP_Error otherwise
     */
    function fetch_remote_file($url, $post)
    {
        // Extract the file name from the URL.
        $file_name = basename(parse_url($url, PHP_URL_PATH));

        if (!$file_name) {
            $file_name = md5($url);
        }

        $tmp_file_name = wp_tempnam($file_name);
        if (!$tmp_file_name) {
            return new WP_Error('import_no_file', __('Could not create temporary file.', 'wpfd'));
        }

        // Fetch the remote URL and write it to the placeholder file.
        $remote_response = wp_safe_remote_get($url, array(
            'timeout' => 300,
            'stream' => true,
            'filename' => $tmp_file_name,
            'headers' => array(
                'Accept-Encoding' => 'identity',
            ),
        ));

        if (is_wp_error($remote_response)) {
            @unlink($tmp_file_name);
            return new WP_Error(
                'import_file_error',
                sprintf(
                /* translators: 1: The WordPress error message. 2: The WordPress error code. */
                    __('Request failed due to an error: %1$s (%2$s)', 'wpfd'),
                    esc_html($remote_response->get_error_message()),
                    esc_html($remote_response->get_error_code())
                )
            );
        }

        $remote_response_code = (int)wp_remote_retrieve_response_code($remote_response);

        // Make sure the fetch was successful.
        if (200 !== $remote_response_code) {
            @unlink($tmp_file_name);
            return new WP_Error(
                'import_file_error',
                sprintf(
                /* translators: 1: The HTTP error message. 2: The HTTP error code. */
                    __('Remote server returned the following unexpected result: %1$s (%2$s)', 'wpfd'),
                    get_status_header_desc($remote_response_code),
                    esc_html($remote_response_code)
                )
            );
        }

        $headers = wp_remote_retrieve_headers($remote_response);

        // Request failed.
        if (!$headers) {
            @unlink($tmp_file_name);
            return new WP_Error('import_file_error', __('Remote server did not respond', 'wpfd'));
        }

        $filesize = (int)filesize($tmp_file_name);

        if (0 === $filesize) {
            @unlink($tmp_file_name);
            return new WP_Error('import_file_error', __('Zero size file downloaded', 'wpfd'));
        }

        if (!isset($headers['content-encoding']) && isset($headers['content-length']) && $filesize !== (int)$headers['content-length']) {
            @unlink($tmp_file_name);
            return new WP_Error('import_file_error', __('Downloaded file has incorrect size', 'wpfd'));
        }

        $max_size = (int)$this->max_attachment_size();

        if (!empty($max_size) && $filesize > $max_size) {
            @unlink($tmp_file_name);
            return new WP_Error('import_file_error', sprintf(__('Remote file is too large, limit is %s', 'wpfd'), size_format($max_size)));
        }

        // Override file name with Content-Disposition header value.
        if (!empty($headers['content-disposition'])) {
            $file_name_from_disposition = self::get_filename_from_disposition((array)$headers['content-disposition']);
            if ($file_name_from_disposition) {
                $file_name = $file_name_from_disposition;
            }
        }

        // Set file extension if missing.
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (!$file_ext && !empty($headers['content-type'])) {
            $extension = self::get_file_extension_by_mime_type($headers['content-type']);
            if ($extension) {
                $file_name = "{$file_name}.{$extension}";
            }
        }

        // Handle the upload like _wp_handle_upload() does.
        $wp_filetype    = wp_check_filetype(basename($file_name), null);
        if (!$wp_filetype['ext'] && !$wp_filetype['type']) {
            $new_mime_type          = mime_content_type(basename($file_name));
            $wp_filetype['ext']     = $file_ext;
            $wp_filetype['type']    = $new_mime_type;
        }
        $uploads        = wp_upload_dir($post['upload_date']);

        if (!($uploads && false === $uploads['error'])) {
            return new WP_Error('upload_dir_error', $uploads['error']);
        }

        // Move the file to the uploads dir.
        $file_name      = wp_unique_filename($uploads['path'], $file_name);
        $new_file       = $uploads['path'] . "/$file_name";
        $move_new_file  = copy($tmp_file_name, $new_file);

        if (!$move_new_file) {
            @unlink($tmp_file_name);
            return new WP_Error('import_file_error', __('The uploaded file could not be moved', 'wpfd'));
        }

        // Set correct file permissions.
        $stat = stat(dirname($new_file));
        $perms = $stat['mode'] & 0000666;
        chmod($new_file, $perms);

        $upload = array(
            'file' => $new_file,
            'url' => $uploads['url'] . "/$file_name",
            'type' => $wp_filetype['type'],
            'error' => false,
        );

        // keep track of the old and new urls so we can substitute them later
        $this->url_remap[$url] = $upload['url'];
        $this->url_remap[$post['guid']] = $upload['url']; // r13735, really needed?
        // keep track of the destination if the remote url is redirected somewhere else
        if (isset($headers['x-final-location']) && $headers['x-final-location'] != $url) {
            $this->url_remap[$headers['x-final-location']] = $upload['url'];
        }

        return $upload;
    }

    /**
     * Decide what the maximum file size for downloaded attachments is.
     * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
     *
     * @return integer Maximum attachment file size to import
     */
    function max_attachment_size()
    {
        return apply_filters('import_attachment_size_limit', 0);
    }

    /**
     * Attempt to associate posts and menu items with previously missing parents
     *
     * An imported post's parent may not have been imported when it was first created
     * so try again. Similarly for child menu items and menu items which were missing
     * the object (e.g. post) they represent in the menu
     */
    function backfill_parents()
    {
        global $wpdb;

        // find parents for post orphans
        foreach ($this->post_orphans as $child_id => $parent_id) {
            $local_child_id = $local_parent_id = false;
            if (isset($this->processed_posts[$child_id])) {
                $local_child_id = $this->processed_posts[$child_id];
            }
            if (isset($this->processed_posts[$parent_id])) {
                $local_parent_id = $this->processed_posts[$parent_id];
            }

            if ($local_child_id && $local_parent_id) {
                $wpdb->update($wpdb->posts, array('post_parent' => $local_parent_id), array('ID' => $local_child_id), '%d', '%d');
                clean_post_cache($local_child_id);
            }
        }

        // find parents for menu item orphans
        foreach ($this->menu_item_orphans as $child_id => $parent_id) {
            $local_child_id = $local_parent_id = 0;
            if (isset($this->processed_menu_items[$child_id])) {
                $local_child_id = $this->processed_menu_items[$child_id];
            }
            if (isset($this->processed_menu_items[$parent_id])) {
                $local_parent_id = $this->processed_menu_items[$parent_id];
            }

            if ($local_child_id && $local_parent_id) {
                update_post_meta($local_child_id, '_menu_item_menu_item_parent', (int)$local_parent_id);
            }
        }
    }

    /**
     * Use stored mapping information to update old attachment URLs
     */
    function backfill_attachment_urls()
    {
        global $wpdb;
        // make sure we do the longest urls first, in case one is a substring of another
        uksort($this->url_remap, array( $this, 'cmpr_strlen' ));
        foreach ($this->url_remap as $from_url => $to_url) {
            // remap urls in post_content
            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url));
            // remap enclosure urls
            $result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url));
        }
    }

    /**
     * Update _thumbnail_id meta to new, imported attachment IDs
     */
    function remap_featured_images()
    {
        // cycle through posts that have a featured image
        foreach ($this->featured_images as $post_id => $value) {
            if (isset($this->processed_posts[$value])) {
                $new_id = $this->processed_posts[$value];
                // only update if there's a difference
                if ($new_id != $value) {
                    update_post_meta($post_id, '_thumbnail_id', $new_id);
                }
            }
        }
    }

    // return the difference in length between two strings
    function cmpr_strlen($a, $b)
    {
        return strlen($b) - strlen($a);
    }

    /**
     * UpdateFiles
     *
     * @return void
     */
    function updateFiles()
    {
        foreach ($this->new_post_ids as $key => $val) {
            $metadata           = get_post_meta($val, '_wpfd_file_metadata', true);
            $multi_category     = ( isset($metadata['file_multi_category']) ) ? $metadata['file_multi_category'] : array();
            $multi_category_old = ( isset($metadata['file_multi_category_old']) ) ? $metadata['file_multi_category_old'] : '';

            if (empty($multi_category) || $multi_category_old === '') {
                continue;
            }

            $new_multi_category = $new_multi_category_old = array();
            $multi_category_old = explode(',', $multi_category_old);
            foreach ($multi_category as $value) {
                foreach ($this->new_term_ids as $old => $new) {
                    if ((int)$value === (int)$old) {
                        $new_multi_category[] = $new;
                        continue;
                    }
                }
            }

            if (!empty($multi_category_old)) {
                foreach ($multi_category_old as $old_val) {
                    foreach ($this->new_term_ids as $key => $termval) {
                        if ((int)$old_val === (int)$key) {
                            $new_multi_category_old[] = $termval;
                            continue;
                        }
                    }
                }
            }

            if (!empty($new_multi_category) && !empty($new_multi_category_old)) {
                $new_multi_category_old              = implode(',', $new_multi_category_old);
                $metadata['file_multi_category']     = $new_multi_category;
                $metadata['file_multi_category_old'] = $new_multi_category_old;

                update_post_meta($val, '_wpfd_file_metadata', $metadata);
            }
        }
    }

    /**
     * Parses filename from a Content-Disposition header value.
     *
     * As per RFC6266:
     *
     *     content-disposition = "Content-Disposition" ":"
     *                            disposition-type *( ";" disposition-parm )
     *
     *     disposition-type    = "inline" | "attachment" | disp-ext-type
     *                         ; case-insensitive
     *     disp-ext-type       = token
     *
     *     disposition-parm    = filename-parm | disp-ext-parm
     *
     *     filename-parm       = "filename" "=" value
     *                         | "filename*" "=" ext-value
     *
     *     disp-ext-parm       = token "=" value
     *                         | ext-token "=" ext-value
     *     ext-token           = <the characters in token, followed by "*">
     *
     * @param  string[] $disposition_header List of Content-Disposition header values.
     * @return string|null Filename if available, or null if not found.
     * @link   http://tools.ietf.org/html/rfc2388
     * @link   http://tools.ietf.org/html/rfc6266
     *
     * @since 0.7.0
     *
     * @see WP_REST_Attachments_Controller::get_filename_from_disposition()
     */
    protected static function get_filename_from_disposition($disposition_header)
    {
        // Get the filename.
        $filename = null;

        foreach ($disposition_header as $value) {
            $value = trim($value);

            if (strpos($value, ';') === false) {
                continue;
            }

            list($type, $attr_parts) = explode(';', $value, 2);

            $attr_parts = explode(';', $attr_parts);
            $attributes = array();

            foreach ($attr_parts as $part) {
                if (strpos($part, '=') === false) {
                    continue;
                }

                list($key, $value) = explode('=', $part, 2);

                $attributes[trim($key)] = trim($value);
            }

            if (empty($attributes['filename'])) {
                continue;
            }

            $filename = trim($attributes['filename']);

            // Unquote quoted filename, but after trimming.
            if (substr($filename, 0, 1) === '"' && substr($filename, -1, 1) === '"') {
                $filename = substr($filename, 1, -1);
            }
        }

        return $filename;
    }

    /**
     * Retrieves file extension by mime type.
     *
     * @param  string $mime_type Mime type to search extension for.
     * @return string|null File extension if available, or null if not found.
     * @since  0.7.0
     */
    protected static function get_file_extension_by_mime_type($mime_type)
    {
        static $map = null;

        if (is_array($map)) {
            return isset($map[$mime_type]) ? $map[$mime_type] : null;
        }

        $mime_types = wp_get_mime_types();
        $map = array_flip($mime_types);

        // Some types have multiple extensions, use only the first one.
        foreach ($map as $type => $extensions) {
            $map[$type] = strtok($extensions, '|');
        }

        return isset($map[$mime_type]) ? $map[$mime_type] : null;
    }
}

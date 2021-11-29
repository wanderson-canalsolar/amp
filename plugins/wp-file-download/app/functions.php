<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */


if (!function_exists('wpfd_sort_by_property')) {
    /**
     * Sort items by property
     *
     * @param array   $items    Files list
     * @param string  $property Property type
     * @param string  $key      Sort type
     * @param boolean $reverse  Reverse type
     *
     * @return array
     */
    function wpfd_sort_by_property(array $items, $property, $key = '', $reverse = false)
    {
        $sorted = array();
        $items_bk = $items;
        foreach ($items as $item) {
            $sorted[$item->$key] = $item->$property;
            $items_bk[$item->$key] = $item;
        }
        if ($reverse) {
            arsort($sorted);
        } else {
            asort($sorted);
        }
        $results = array();
        foreach ($sorted as $key2 => $value) {
            $results[] = $items_bk[$key2];
        }
        return $results;
    }
}

if (!function_exists('wpfd_getext')) {
    /**
     * Get extension of file
     *
     * @param string $file File name
     *
     * @return boolean|string
     */
    function wpfd_getext($file)
    {
        $dot = strrpos($file, '.') + 1;
        return substr($file, $dot);
    }
}
if (!function_exists('wpfd_remote_file_size')) {
    /**
     * Get size of file with remote url
     *
     * @param string $url Input url
     *
     * @return mixed|string
     */
    function wpfd_remote_file_size($url)
    {
        // Fix file size error on url have space
        $url = str_replace(' ', '%20', $url);
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_NOBODY => 1,
        ));
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);
        $clen = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        if (!$clen || ($clen <= 0)) {
            return esc_html__('N/A', 'wpfd');
        }
        return $clen;
    }
}

if (!function_exists('wpfd_num')) {
    /**
     * Display select pages number
     *
     * @param integer $paged Page number
     *
     * @return void
     */
    function wpfd_num($paged = 5)
    {
        ?>
        <div class="wpfd-num">
            <?php
            $p_number = array(5, 10, 15, 20, 25, 30, 50, 100, -1);
            ?>
            <div class="limit pull-right">
                <?php esc_html_e('Display #', 'wpfd'); // phpcs:ignore ?>
                <select title="" id="limit" name="limit" class="" size="1">
                    <?php
                    foreach ($p_number as $num) {
                        $selected = $num === (int)$paged ? ' selected="selected"' : '';
                        ?>
                        <option value="<?php echo $num; // phpcs:ignore ?>"
                            <?php echo $selected; // phpcs:ignore ?>><?php echo $num === -1 ? esc_html__('All', 'wpfd') : $num; ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('wpfd_select')) {
    /**
     * Render a select html
     *
     * @param array   $options  Options array
     * @param string  $name     Name
     * @param string  $select   Select
     * @param string  $attr     Attr
     * @param boolean $disabled Disable
     *
     * @return string
     */
    function wpfd_select(array $options = array(), $name = '', $select = '', $attr = '', $disabled = false)
    {
        $html = '';
        $html .= '<select';
        if ($name !== '') {
            $html .= ' name="' . esc_attr($name) . '"';
        }
        if ($attr !== '') {
            $html .= ' ' . $attr;
        }
        $html .= '>';
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $select_option = '';
                if (is_array($select)) {
                    if (in_array($key, $select)) {
                        $select_option = 'selected="selected"';
                    } elseif ((string)$key === (string)$disabled) {
                        $select_option = disabled($disabled, $key, false);
                    } else {
                        $select_option = '';
                    }
                } else {
                    if ($disabled) {
                        $select_option = disabled($disabled, $key, false);
                    } else {
                        $select_option = selected($select, $key, false);
                    }
                }
                $html .= '<option value="' . esc_attr($key) . '" ' . $select_option . '>' . $value . '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }
}
if (!function_exists('wpfd_pagination')) {
    /**
     * Display a custom pagination
     *
     * @param array  $args      Options args
     * @param string $form_name Form name
     *
     * @return array|string|boolean
     */
    function wpfd_pagination(array $args = array(), $form_name = '')
    {
        global $wp_query, $wp_rewrite;
        // Setting up default values based on the current URL.
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $url_parts = explode('?', $pagenum_link);
        // Get max pages and current page out of the current query, if available.
        $total = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
        $current = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
        // Append the format placeholder to the base URL.
        $pagenum_link = trailingslashit($url_parts[0]) . '%_%';
        // URL base depends on permalink settings.
        $pagination_base = user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged');
        $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? $pagination_base : '?paged=%#%';

        $defaults = array(
            'base' => $pagenum_link,
            // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
            'format' => $format,
            // ?page=%#% : %#% is replaced by the page number
            'total' => $total,
            'current' => $current,
            'show_all' => false,
            'prev_next' => true,
            'prev_text' => esc_html__('&laquo; Previous', 'wpfd'),
            'next_text' => esc_html__('Next &raquo;', 'wpfd'),
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'plain',
            'add_args' => array(),
            // array of query args to add
            'add_fragment' => '',
            'before_page_number' => '',
            'after_page_number' => ''
        );

        $args = wp_parse_args($args, $defaults);
        if (!is_array($args['add_args'])) {
            $args['add_args'] = array();
        }
        // Merge additional query vars found in the original URL into 'add_args' array.
        if (isset($url_parts[1])) {
            // Find the format argument.
            $format = explode('?', str_replace('%_%', $args['format'], $args['base']));
            $format_query = isset($format[1]) ? $format[1] : '';
            wp_parse_str($format_query, $format_args);
            // Find the query args of the requested URL.
            wp_parse_str($url_parts[1], $url_query_args);
            // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
            foreach ($format_args as $format_arg => $format_arg_value) {
                unset($url_query_args[$format_arg]);
            }
            $args['add_args'] = array_merge($args['add_args'], urlencode_deep($url_query_args));
        }
        // Who knows what else people pass in $args
        $total = (int)$args['total'];
        if ($total < 2) {
            return false;
        }
        $current = (int)$args['current'];
        $end_size = (int)$args['end_size']; // Out of bounds?  Make it the default.
        if ($end_size < 1) {
            $end_size = 1;
        }
        $mid_size = (int)$args['mid_size'];
        if ($mid_size < 0) {
            $mid_size = 2;
        }
        $add_args = $args['add_args'];
        $r = '';
        $page_links = array();
        $dots = false;
        if ($args['prev_next'] && $current && 1 < $current) :
            $link = str_replace('%_%', 2 === $current ? '' : $args['format'], $args['base']);
            $link = str_replace('%#%', $current - 1, $link);
            if ($add_args) {
                $link = add_query_arg($add_args, $link);
            }
            $link .= $args['add_fragment'];
            /**
             * Filter the paginated links for the given archive pages.
             *
             * @since 3.0.0
             *
             * param string $link The paginated link URL.
             */
            $page_link = "<a class='prev page-numbers' onclick='document." . esc_attr($form_name) . '.paged.value=';
            $page_link .= ($current - 1) . '; document.' . esc_attr($form_name) . ".submit();'>" . $args['prev_text'] . '</a>';
            $page_links[] = $page_link;
        endif;
        for ($n = 1; $n <= $total; $n++) :
            if ($n === $current) :
                $page_link = "<span class='page-numbers current'>" . $args['before_page_number'];
                $page_link .= number_format_i18n($n) . $args['after_page_number'] . '</span>';
                $page_links[] = $page_link;
                $dots = true;
            else :
                if ($args['show_all'] ||
                    ($n <= $end_size || ($current && $n >= $current - $mid_size && $n <= $current + $mid_size)
                        || $n > $total - $end_size)) :
                    $link = str_replace('%_%', 1 === $n ? '' : $args['format'], $args['base']);
                    $link = str_replace('%#%', $n, $link);
                    if ($add_args) {
                        $link = add_query_arg($add_args, $link);
                    }
                    $link .= $args['add_fragment'];
                    /**
 * This filter is documented in wp-includes/general-template.php
*/
                    $page_link = "<a class='page-numbers' onclick='document." . esc_attr($form_name) . '.paged.value=';
                    $page_link .= $n . '; document.' . esc_attr($form_name) . ".submit();'>" . $args['before_page_number'];
                    $page_link .= number_format_i18n($n) . $args['after_page_number'] . '</a>';
                    $page_links[] = $page_link;
                    $dots = true;
                elseif ($dots && !$args['show_all']) :
                    $page_links[] = '<span class="page-numbers dots">' . esc_html__('&hellip;', 'wpfd') . '</span>';
                    $dots = false;
                endif;
            endif;
        endfor;
        if ($args['prev_next'] && $current && ($current < $total || -1 === $total)) :
            $link = str_replace('%_%', $args['format'], $args['base']);
            $link = str_replace('%#%', $current + 1, $link);
            if ($add_args) {
                $link = add_query_arg($add_args, $link);
            }
            $link .= $args['add_fragment'];

            /**
 * This filter is documented in wp-includes/general-template.php
*/
            $page_link = "<a class='next page-numbers' onclick='document." . esc_attr($form_name) . '.paged.value=';
            $page_link .= ($current + 1) . '; document.' . esc_attr($form_name) . ".submit();'>" . $args['next_text'] . '</a>';
            $page_links[] = $page_link;
        endif;
        switch ($args['type']) {
            case 'array':
                return $page_links;
            case 'list':
                $r .= "<ul class='page-numbers'>\n\t<li>";
                $r .= join("</li>\n\t<li>", $page_links);
                $r .= "</li>\n</ul>\n";
                break;
            default:
                $r = join("\n", $page_links);
                break;
        }
        return $r;
    }
}

if (!function_exists('wpfd_category_pagination')) {
    /**
     * Display a custom pagination
     *
     * @param array  $args      Option args
     * @param string $form_name Form name
     *
     * @return array|string|boolean
     */
    function wpfd_category_pagination(array $args = array(), $form_name = '')
    {
        global $wp_query, $wp_rewrite;
        // Setting up default values based on the current URL.
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $url_parts = explode('?', $pagenum_link);
        // Get max pages and current page out of the current query, if available.
        $total = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
        $current = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
        // Append the format placeholder to the base URL.
        $pagenum_link = trailingslashit($url_parts[0]) . '%_%';
        // URL base depends on permalink settings.
        $pagination_base = user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged');
        $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? $pagination_base : '?paged=%#%';
        $defaults = array(
            'base' => $pagenum_link,
            // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
            'format' => $format,
            // ?page=%#% : %#% is replaced by the page number
            'total' => $total,
            'current' => $current,
            'show_all' => false,
            'prev_next' => true,
            'prev_text' => esc_html__('&laquo; Previous', 'wpfd'),
            'next_text' => esc_html__('Next &raquo;', 'wpfd'),
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'plain',
            'add_args' => array(),
            // array of query args to add
            'add_fragment' => '',
            'before_page_number' => '',
            'after_page_number' => ''
        );
        $args = wp_parse_args($args, $defaults);
        if (!is_array($args['add_args'])) {
            $args['add_args'] = array();
        }
        // Merge additional query vars found in the original URL into 'add_args' array.
        if (isset($url_parts[1])) {
            // Find the format argument.
            $format = explode('?', str_replace('%_%', $args['format'], $args['base']));
            $format_query = isset($format[1]) ? $format[1] : '';
            wp_parse_str($format_query, $format_args);
            // Find the query args of the requested URL.
            wp_parse_str($url_parts[1], $url_query_args);
            // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
            foreach ($format_args as $format_arg => $format_arg_value) {
                unset($url_query_args[$format_arg]);
            }
            $args['add_args'] = array_merge($args['add_args'], urlencode_deep($url_query_args));
        }
        // Who knows what else people pass in $args
        $total = (int)$args['total'];
        if ($total < 2) {
            return false;
        }
        $current = (int)$args['current'];
        $end_size = (int)$args['end_size']; // Out of bounds?  Make it the default.
        if ($end_size < 1) {
            $end_size = 1;
        }
        $mid_size = (int)$args['mid_size'];
        if ($mid_size < 0) {
            $mid_size = 2;
        }
        $add_args = $args['add_args'];
        $r = '';
        $page_links = array();
        $dots = false;
        if (isset($args['sourcecat']) && intval($args['sourcecat']) === 0) {
            $args['sourcecat'] = 'all_0';
        }
        if ($args['prev_next'] && $current && 1 < $current) :
            $link = str_replace('%_%', 2 === $current ? '' : $args['format'], $args['base']);
            $link = str_replace('%#%', $current - 1, $link);
            if ($add_args) {
                $link = add_query_arg($add_args, $link);
            }
            $link .= $args['add_fragment'];

            /**
             * Filter the paginated links for the given archive pages.
             *
             * @since 3.0.0
             *
             * param string $link The paginated link URL.
             */
            $page_link = "<a class='prev page-numbers' data-page='" . ($current - 1) . "' data-sourcecat='" . $args['sourcecat'] . "'>";
            $page_link .= $args['prev_text'] . '</a>';
            $page_links[] = $page_link;
        endif;
        for ($n = 1; $n <= $total; $n++) :
            if ($n === $current) :
                $page_link = "<span class='page-numbers current'>" . $args['before_page_number'];
                $page_link .= number_format_i18n($n) . $args['after_page_number'] . '</span>';
                $page_links[] = $page_link;
                $dots = true;
            else :
                if ($args['show_all'] ||
                    ($n <= $end_size || ($current && $n >= $current - $mid_size && $n <= $current + $mid_size) ||
                        $n > $total - $end_size)) :
                    $link = str_replace('%_%', 1 === $n ? '' : $args['format'], $args['base']);
                    $link = str_replace('%#%', $n, $link);
                    if ($add_args) {
                        $link = add_query_arg($add_args, $link);
                    }
                    $link .= $args['add_fragment'];

                    /**
                     * This filter is documented in wp-includes/general-template.php
                    */
                    $page_link = "<a class='page-numbers' data-page='" . $n . "' data-sourcecat='" . $args['sourcecat'] . "'>" . $args['before_page_number'];
                    $page_link .= number_format_i18n($n) . $args['after_page_number'] . '</a>';
                    $page_links[] = $page_link;
                    $dots = true;
                elseif ($dots && !$args['show_all']) :
                    $page_links[] = '<span class="page-numbers dots">' . esc_html__('&hellip;', 'wpfd') . '</span>';
                    $dots = false;
                endif;
            endif;
        endfor;
        if ($args['prev_next'] && $current && ($current < $total || -1 === $total)) :
            $link = str_replace('%_%', $args['format'], $args['base']);
            $link = str_replace('%#%', $current + 1, $link);
            if ($add_args) {
                $link = add_query_arg($add_args, $link);
            }
            $link .= $args['add_fragment'];

            /**
             * This filter is documented in wp-includes/general-template.php
            */
            $page_link = "<a class='next page-numbers' data-page='" . ($current + 1) . "' data-sourcecat='" . $args['sourcecat'] . "'>";
            $page_link .= $args['next_text'] . '</a>';
            $page_links[] = $page_link;
        endif;
        switch ($args['type']) {
            case 'array':
                return $page_links;
            case 'list':
                $r .= "<ul class='page-numbers'>\n\t<li>";
                $r .= join("</li>\n\t<li>", $page_links);
                $r .= "</li>\n</ul>\n";
                break;
            default:
                $r .= "<div class='wpfd-pagination'>\n\t";
                $r .= join("\n", $page_links);
                $r .= "\n</div>\n";
                break;
        }
        return $r;
    }
}

if (!function_exists('wpfd_esc_desc')) {
    /**
     * Escaping for HTML description blocks.
     *
     * @param string $text Text
     *
     * @return string
     */
    function wpfd_esc_desc($text)
    {
        $safe_text = wp_check_invalid_utf8($text);
        // Remove <script>
        $safe_text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $safe_text);
        return apply_filters('wpfd_esc_desc', $safe_text, $text);
    }
}

if (PHP_VERSION_ID < 70300) {
    if (!function_exists('is_countable')) {
        /**
         * Check countable variables from php version 7.3.0
         *
         * @param mixed $var Variable to check
         *
         * @return boolean
         */
        function is_countable($var)
        {
            return is_array($var) || $var instanceof Countable || $var instanceof ResourceBundle || $var instanceof SimpleXmlElement;
        }
    }
}

if (!function_exists('wpfd_sanitize_ajax_url')) {
    /**
     * Sanitize wp file download ajax url
     *
     * @param string $url Ajax url
     *
     * @return string
     */
    function wpfd_sanitize_ajax_url($url)
    {
        if (preg_match('/Wpfd/i', $url)) {
            $url = str_replace('action=Wpfd', 'action=wpfd', $url);
        }

        return apply_filters('wpfd_sanitize_ajax_url', $url);
    }
}
if (!function_exists('wpfd_locate_template')) {
    /**
     * Locate a template and return the path for inclusion.
     *
     * Loader order:
     *
     * wp-content/$content_path/$template_name
     * $default_path/app/site/themes/$template_name
     *
     * @param string $template_name Template name
     * @param string $content_path  Template path dir
     * @param string $default_path  Default path dir
     *
     * @return string
     */
    function wpfd_locate_template($template_name, $content_path = '', $default_path = '')
    {

        if (!$content_path) {
            $content_path = WP_CONTENT_DIR . '/wp-file-download/templates/';
        }

        if (!$default_path) {
            $default_path = WPFD_PLUGIN_DIR_PATH . '/app/site/themes/templates/';
        }

        // Look into wp-content directory for template file - this is priority.
        $template = file_exists(trailingslashit($content_path) . $template_name) ? trailingslashit($content_path) . $template_name : '';

        // Get default template.
        if (!$template) {
            $template = trailingslashit($default_path) . $template_name;
        }

        /**
         * Filter on return found template path
         *
         * @param string Template path
         * @param string Template name
         * @param string Template path dir
         */
        return apply_filters('wpfd_locate_template', $template, $template_name, $content_path);
    }
}
if (!function_exists('wpfd_get_template')) {
    /**
     * Get templates
     *
     * @param string $template_name Template name.
     * @param array  $args          Template arguments
     * @param string $content_path  Template path dir
     * @param string $default_path  Default path dir
     *
     * @return void
     */
    function wpfd_get_template($template_name, $args = array(), $content_path = '', $default_path = '')
    {
        if (!empty($args) && is_array($args)) {
            // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- use extract is ok
            extract($args);
        }

        $located = wpfd_locate_template($template_name, $content_path, $default_path);

        if (!file_exists($located)) {
            return;
        }

        /**
         * Allow 3rd party plugin filter
         *
         * @param string Template path
         * @param string Template name
         * @param array  Template variables
         * @param string Template path dir
         * @param string Default path dir
         *
         * @return string
         */
        $located = apply_filters('wpfd_get_template', $located, $template_name, $args, $content_path, $default_path);

        /**
         * Action fire before a template part called
         *
         * @param string Template name
         * @param string Template path dir
         * @param string Template path
         * @param array  Template variables
         */
        do_action('wpfd_before_template_part', $template_name, $content_path, $located, $args);

        include $located;

        /**
         * Action fire after a template part called
         *
         * @param string Template name
         * @param string Template path dir
         * @param string Template path
         * @param array  Template variables
         */
        do_action('wpfd_after_template_part', $template_name, $content_path, $located, $args);
    }
}

if (!function_exists('wpfd_get_template_html')) {
    /**
     * Get templates and return html
     *
     * @param string $template_name Template name
     * @param array  $args          Template arguments
     * @param string $content_path  Template path dir
     * @param string $default_path  Default path dir
     *
     * @return false|string
     */
    function wpfd_get_template_html($template_name, $args = array(), $content_path = '', $default_path = '')
    {
        ob_start();
        wpfd_get_template($template_name, $args, $content_path, $default_path);
        return ob_get_clean();
    }
}
/**
 * Locate theme file path
 *
 * @param string $theme        Theme name
 * @param string $file         Theme file to locate
 * @param string $content_path Template path dir
 * @param string $default_path Template default path dir
 * @param string $upload_path  Template old path dir
 *
 * @return mixed
 */
function wpfd_locate_theme($theme, $file, $content_path = '', $default_path = '', $upload_path = '')
{
    $ds = DIRECTORY_SEPARATOR;
    if ($content_path === '') {
        $content_path = WP_CONTENT_DIR . $ds .'wp-file-download' . $ds . 'themes' . $ds . 'wpfd-' . $theme . $ds;
    }
    if (!$upload_path) {
        $dir = wp_upload_dir();
        $upload_path = $dir['basedir'] . $ds . 'wpfd-themes' . $ds . 'wpfd-' . $theme . $ds;
    }
    if (!$default_path) {
        $default_path = WPFD_PLUGIN_DIR_PATH . $ds . 'app' . $ds . 'site' . $ds . 'themes' . $ds . 'wpfd-' . $theme . $ds;
    }

    if (file_exists(trailingslashit($content_path) . $file)) {
        $template = trailingslashit($content_path) . $file;
    } elseif (file_exists(trailingslashit($upload_path) . $file)) {
        $template = trailingslashit($upload_path) . $file;
    }

    // Get default template.
    if (!isset($template)) {
        $template = trailingslashit($default_path) . $file;
    }

    /**
     * Filter on return found template path
     *
     * @param string Template path
     * @param string Template name
     * @param string Template path dir
     */
    return apply_filters('wpfd_locate_theme', $template, $theme, $file, $content_path);
}

/**
 * Get theme instance by name
 *
 * @param string $theme        Theme name
 * @param string $content_path Template path dir
 * @param string $default_path Template default path dir
 * @param string $upload_path  Template old path dir
 *
 * @return WpfdTheme{NAME}
 */
function wpfd_get_theme_instance($theme, $content_path = '', $default_path = '', $upload_path = '')
{
    $file = 'theme.php';

    if (!class_exists('wpfdTheme')) {
        $wpfdTheme = WPFD_PLUGIN_DIR_PATH . '/app/site/themes/templates/wpfd-theme.class.php';
        include_once $wpfdTheme;
    }

    $located = wpfd_locate_theme($theme, $file, $content_path, $default_path, $upload_path);

    if (file_exists($located)) {
        include_once $located;
    } else {
        $themefile = WPFD_PLUGIN_DIR_PATH . '/app/site/themes/wpfd-default/theme.php';
        include_once $themefile;
        $theme = 'default';
    }
    $class = 'WpfdTheme' . ucfirst(str_replace('_', '', $theme));

    if (class_exists($class)) {
        $instance = new $class();
    } else {
        $instance = new WpfdThemeDefault;
    }

    return $instance;
}

/**
 * Get supported cloud platform
 *
 * @return array
 */
function wpfd_get_support_cloud()
{
    /**
     * Filter return supported cloud platform
     * Require to detect where categories/files from
     *
     * @param array Cloud platform list
     *
     * @return array
     */
    return apply_filters('wpfd_get_support_cloud', array('googleDrive', 'dropbox', 'onedrive', 'onedrive_business'));
}

/**
 * Get file url from real path
 *
 * @param string $path Real path
 *
 * @return string
 */
function wpfd_abs_path_to_url($path = '')
{
    // phpcs:ignore PHPCompatibility.LanguageConstructs.NewEmptyNonVariable.Found -- This is for check only
    $siteUrl = defined('WP_SITEURL') && !empty(WP_SITEURL) ? WP_SITEURL : site_url();

    $abspath = wpfd_get_abspath();

    /**
     * Filter to fix wrong ABSPATH in some hosting/server
     *
     * @param string
     */
    $realAbsPath = apply_filters('wpfd_real_abs_path', $abspath);

    if ($realAbsPath === '//') {
        return esc_url_raw($siteUrl . wp_normalize_path($path));
    }

    $url = str_replace(
        wp_normalize_path(untrailingslashit($realAbsPath)),
        $siteUrl,
        wp_normalize_path($path)
    );

    return esc_url_raw($url);
}
/**
 * Check capability of current user to manage file
 *
 * @return boolean
 */
function wpfd_can_manage_file()
{
    /**
     * Filter check capability of current user to manage file
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_manage_file'), 'manage_file');
}
/**
 * Check capability of current user to edit permissions settings
 *
 * @return boolean
 */
function wpfd_can_edit_permission()
{
    /**
     * Filter check capability of current user to edit permissions settings
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_edit_permission'), 'edit_permission');
}
/**
 * Check provided user is owner for a category
 *
 * @param object      $category Category
 * @param object|null $owner    User to check
 *
 * @return boolean
 */
function wpfd_user_is_owner_of_category($category, $owner = null)
{
    if ($owner === null) {
        $owner = wp_get_current_user();
    }

    if (!isset($owner->ID) || (isset($owner->ID) && !$owner->ID)) {
        // User is not login
        return false;
    }

    if (isset($category->params['category_own']) && intval($owner->ID) === intval($category->params['category_own'])) {
        return true;
    }

    return false;
}
/**
 * Check capability of current user to edit category
 *
 * @return boolean
 */
function wpfd_can_edit_category()
{
    /**
     * Filter check capability of current user to edit category
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_edit_category'), 'edit_category');
}

/**
 * Check capability of current user to edit own category
 *
 * @return boolean
 */
function wpfd_can_edit_own_category()
{
    /**
     * Filter check capability of current user to edit own category
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_edit_own_category'), 'edit_own_category');
}

/**
 * Check capability of current user to delete category
 *
 * @return boolean
 */
function wpfd_can_delete_category()
{
    /**
     * Filter check capability of current user to delete category
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_delete_category'), 'delete_category');
}

/**
 * Check capability of current user to create category
 *
 * @return boolean
 */
function wpfd_can_create_category()
{
    /**
     * Filter check capability of current user to create category
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_create_category'), 'create_category');
}

/**
 * Check capability of current user to download files
 *
 * @return boolean
 */
function wpfd_can_download_files()
{

    $canDownloadFiles   = false;
    $globalConfig       = get_option('_wpfd_global_config');
    $currentUser        = wp_get_current_user();
    if ((int)$currentUser->ID === 0) {
        $canDownloadFiles = (!isset($globalConfig['guest_download_files']) || (isset($globalConfig['guest_download_files']) && (int)$globalConfig['guest_download_files'] === 1));
    } else {
        if (current_user_can('wpfd_download_files')) {
            $canDownloadFiles = true;
        } else {
            // Set default value to true
            $roles = $currentUser->roles;
            if (is_countable($roles)) {
                foreach ($roles as $role) {
                    if (isset($globalConfig[$role . '_download_files']) && (int) $globalConfig[$role . '_download_files'] === 1) {
                        $canDownloadFiles = true;
                    } elseif (isset($globalConfig[$role . '_download_files']) && (int) $globalConfig[$role . '_download_files'] === 0) {
                        $canDownloadFiles = false;
                    } else {
                        $canDownloadFiles = true;
                    }
                    // This user can download file, stop checking other role
                    if ($canDownloadFiles) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * Filter check capability of current user to download files
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', $canDownloadFiles, 'download_files');
}

/**
 * Check capability of current user to preview files
 *
 * @return boolean
 */
function wpfd_can_preview_files()
{
    $canPreviewFiles   = false;
    $globalConfig       = get_option('_wpfd_global_config');
    $currentUser        = wp_get_current_user();
    if ((int)$currentUser->ID === 0) {
        $canPreviewFiles = (!isset($globalConfig['guest_preview_files']) || (isset($globalConfig['guest_preview_files']) && (int)$globalConfig['guest_preview_files'] === 1));
    } else {
        if (current_user_can('wpfd_preview_files')) {
            $canPreviewFiles = true;
        } else {
            // Set default value to true
            $roles = $currentUser->roles;
            if (is_countable($roles)) {
                foreach ($roles as $role) {
                    if (isset($globalConfig[$role . '_preview_files']) && (int) $globalConfig[$role . '_preview_files'] === 1) {
                        $canPreviewFiles = true;
                    } elseif (isset($globalConfig[$role . '_preview_files']) && (int) $globalConfig[$role . '_preview_files'] === 0) {
                        $canPreviewFiles = false;
                    } else {
                        $canPreviewFiles = true;
                    }

                    // This user can preview file, stop checking other role
                    if ($canPreviewFiles) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * Filter check capability of current user to preview files
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', $canPreviewFiles, 'preview_files');
}

if (!function_exists('wpfd_gutenberg_integration')) {
    /**
     * WP File Download gutenberg integration
     *
     * @return void
     */
    function wpfd_gutenberg_integration()
    {
        include_once WPFD_PLUGIN_DIR_PATH . 'app/admin/helpers/WpfdHelperFile.php';
        \Joomunited\WPFramework\v1_0_5\Application::getInstance('Wpfd', WPFD_PLUGIN_FILE, 'admin');
        $modelConfig = \Joomunited\WPFramework\v1_0_5\Model::getInstance('config');
        $globalConfig = $modelConfig->getConfig();
        $iconSet = isset($globalConfig['icon_set']) ? $globalConfig['icon_set'] : 'default';

        wp_enqueue_script(
            'wpfd-blocks',
            WPFD_PLUGIN_URL . 'app/admin/assets/blocks/wpfd-blocks.js',
            array('wp-blocks', 'wp-element', 'wp-components', 'wp-data')
        );
        wp_localize_script('wpfd-blocks', 'wpfd_block_vars', array(
                'iconSet' => $iconSet
        ));
        wp_enqueue_style(
            'wpfd-category-style',
            WPFD_PLUGIN_URL . 'app/admin/assets/css/wpfd-blocks.css',
            array('wp-edit-blocks')
        );

        if ($iconSet === 'default') {
            wp_enqueue_style(
                'wpfd-blocks-icon-style',
                WPFD_PLUGIN_URL . 'app/admin/assets/css/wpfd-blocks-icon.css',
                array('wp-edit-blocks')
            );
        } else {
            $lastRebuildTime = get_option('wpfd_icon_rebuild_time', false);
            if (false === $lastRebuildTime) {
                // Icon CSS was never build, build it
                $lastRebuildTime = WpfdHelperFile::renderCss();
            }

            if ($iconSet !== 'default' && in_array($iconSet, array('png', 'svg'))) {
                $path = WpfdHelperFile::getCustomIconPath($iconSet);
                $cssPath = $path . 'styles-' . $lastRebuildTime . '.css';
                if (file_exists($cssPath)) {
                    $cssUrl = wpfd_abs_path_to_url($cssPath);
                } else {
                    $lastRebuildTime = WpfdHelperFile::renderCss();
                    $cssPath = $path . 'styles-' . $lastRebuildTime . '.css';
                    if (file_exists($cssPath)) {
                        $cssUrl = wpfd_abs_path_to_url($cssPath);
                    } else {
                        // Use default css pre-builed
                        $cssUrl = WPFD_PLUGIN_URL . 'app/site/assets/icons/' . $iconSet . '/icon-styles.css';
                    }
                }
                // Include file
                wp_enqueue_style(
                    'wpfd-style-icon-set-' . $iconSet,
                    $cssUrl,
                    array('wpfd-category-style'),
                    WPFD_VERSION
                );
            }
        }
    }
}

/**
 * Add WP File Download categories to blocks categories
 *
 * @param array   $categories Categories array
 * @param WP_Post $post       Post object
 *
 * @return array
 */
function wpfd_blocks_categories($categories, $post)
{
    // Display wpfd blocks in all post type
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'wp-file-download',
                'title' => __('WP File Download', 'wpfd'),
            ),
        )
    );
}

/**
 * Install clean statistics job
 *
 * @param string $task     Task name
 * @param string $interval Interval name
 *
 * @return void
 */
function wpfd_install_job($task, $interval)
{
    if (empty($interval) || empty($task)) {
        return;
    }

    if (!wp_next_scheduled($task)) {
        wp_schedule_event(time(), $interval, $task);
    }
}

/**
 * Destroy clean statistics job
 *
 * @param string $task Task name
 *
 * @return void
 */
function wpfd_destroy_job($task)
{
    if (empty($task)) {
        return;
    }

    $timestamp = wp_next_scheduled($task);
    wp_unschedule_event($timestamp, $task);
}

/**
 * Reinstall clean statistics job
 *
 * @param string $task     Task name
 * @param string $interval Interval name
 *
 * @return void
 */
function wpfd_reinstall_job($task, $interval)
{
    if (empty($interval) || empty($task)) {
        return;
    }

    wpfd_destroy_job($task);
    wpfd_install_job($task, $interval);
}

/**
 * Schedules
 *
 * @return void
 */
function wpfd_schedules()
{
    add_filter('cron_schedules', 'wpfd_get_schedules');
}

/**
 * Get all wpfd schedules
 *
 * @param array $schedules Schedules list
 *
 * @return array
 */
function wpfd_get_schedules($schedules)
{
    /**
     * Filter to add wpfd schedules task
     *
     * @param array $schedule An array for schedule task
     *
     * @internal
     *
     * @return array
     */
    $schedule = apply_filters('wpfd_get_schedules', array());

    if (!is_array($schedule)) {
        return $schedules;
    }

    $schedules = array_merge($schedules, $schedule);

    return $schedules;
}

/**
 * Get wpfd_remove_statistics interval
 *
 * @return integer
 */
function wpfd_get_remove_statistics_interval()
{
    $interval = 0;

    $config = get_option('_wpfd_global_config');

    if (!is_array($config) && !isset($config['statistics_storage_duration'])) {
        return $interval;
    }

    $duration = isset($config['statistics_storage_duration']) ? $config['statistics_storage_duration'] : 'forever';
    $times = isset($config['statistics_storage_times']) ? (int) $config['statistics_storage_times'] : 0;

    // Calculate interval
    if ($times !== 0) {
        switch ($duration) {
            case 'years':
                $interval = $times * 31104000;
                break;
            case 'months':
                $interval = $times * 2592000;
                break;
            case 'days':
                $interval = $times * 86400;
                break;
            case 'forever':
            default:
                break;
        }
    }

    return (int) $interval;
}

/**
 * Get wpfd_remove_statistics schedule
 *
 * @param array $schedule Schedule list
 *
 * @return array|boolean
 */
function wpfd_get_remove_statistics_schedule($schedule)
{
    $interval = wpfd_get_remove_statistics_interval();

    if ($interval === 0) {
        return false;
    }

    $schedule['wpfd_remove_statistics'] = array(
        'interval' => $interval,
        'display'  => esc_html__('WPFD Clean Statistics', 'wpfd'),
    );

    return $schedule;
}

/**
 * Remove statistics
 *
 * @return void
 */
function wpfd_remove_statistics()
{
    global $wpdb;

    // Check last time statistics is run to prevent it clear in first time.
    $lastCleanTime = get_option('wpfd_remove_statistics_time', 0);
    $interval = wpfd_get_remove_statistics_interval();

    // Do not clear in first running and update
    if ($lastCleanTime === 0 || ($lastCleanTime > 0 && (time() - $lastCleanTime) < $interval)) {
        if ($lastCleanTime === 0) {
            update_option('wpfd_remove_statistics_time', time());
        }
        return;
    }

    $ts = $lastCleanTime + $interval;
    $deletePoint = date('Y-m-d', $ts);
    $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wpfd_statistics WHERE `date` < "' . $deletePoint . '"');

    update_option('wpfd_remove_statistics_time', time());
}

/**
 * Reinstall remove statistics task
 *
 * @return void
 */
function wpfd_after_main_setting_save()
{
    wpfd_reinstall_job('wpfd_remove_statistics_tasks', 'wpfd_remove_statistics');
}

/**
 * Clean expiry tokens
 *
 * @return void
 */
function wpfd_remove_expiry_tokens()
{
    global $wpdb;

    /**
     * Filter to change token live time
     *
     * @param int Token live time in seconds
     *
     * @return int
     *
     * @ignore
     */
    $time = time() - apply_filters('wpfd_token_live_time', 3600);

    $wpdb->query(
        $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wpfd_tokens WHERE created_at < %d', $time)
    );
}

/**
 * Get clean junks schedule
 *
 * @param array $schedule Schedule list
 *
 * @return array
 */
function wpfd_get_clean_junks_schedule($schedule)
{
    $interval = (defined('WPFD_CLEAN_INTERVAL') && WPFD_CLEAN_INTERVAL > 0) ? WPFD_CLEAN_INTERVAL : 43200; // 12 hours
    /**
     * Filter to change clean junks time, this will override WPFD_CLEAN_INTERVAL constance
     *
     * @param $interval Time in second
     */
    $interval = apply_filters('wpfd_clean_interval', $interval);
    $schedule['wpfd_clean_junks'] = array(
        'interval' => $interval,
        'display'  => esc_html__('WPFD Clean Junks', 'wpfd'),
    );
    return $schedule;
}

/**
 * Clean junks
 *
 * @return void
 */
function wpfd_clean_junks()
{
    $wp_upload_dir = wp_upload_dir();
    $dr = DIRECTORY_SEPARATOR;
    $uploadDir     = $wp_upload_dir['basedir'] . $dr . 'wpfd';
    $interval = (defined('WPFD_CLEAN_INTERVAL') && WPFD_CLEAN_INTERVAL > 0) ? WPFD_CLEAN_INTERVAL : 43200; // 12 hours

    // Check if upload dir exists. This folder was not created until a file uploaded
    if (file_exists($uploadDir)) {
        // Clean file upload junks
        $categories = glob($uploadDir . $dr . '*', GLOB_ONLYDIR);
        if (is_array($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $junkFolder = glob($category . $dr . '*', GLOB_ONLYDIR);
                // Check how old is this dir with WPFD_CLEAN_INTERVAL
                foreach ($junkFolder as $folderPath) {
                    $createdTime = filectime($folderPath);
                    if (!$createdTime || ($createdTime + $interval) > time()) {
                        continue;
                    }

                    rrmdir($folderPath);
                }
            }
        }

        // Clean category zip junks
        $objects = scandir($uploadDir);
        if (false !== $objects) {
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    $filePath = $uploadDir . $dr . $object;
                    if (filetype($filePath) === 'file' && strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'zip') {
                        // Check how old is this file with WPFD_CLEAN_INTERVAL
                        $createdTime = filectime($filePath);
                        if (!$createdTime || ($createdTime + $interval) > time()) {
                            continue;
                        }

                        unlink($filePath);
                    }
                }
            }
        }
    }

    // Clean expiry tokens
    wpfd_remove_expiry_tokens();
}

if (!function_exists('rrmdir')) {
    /**
     * Remove a dir
     *
     * @param string $dir Directory path
     *
     * @return void
     */
    function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . '/' . $object) === 'dir') {
                        rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
/**
 * Convert php date format to moment.js date format
 *
 * @param string $format Php date format
 *
 * @return string
 */
function wpfdPhpToMomentDateFormat($format)
{
    $replacements = array(
        'd' => 'DD',
        'D' => 'ddd',
        'j' => 'D',
        'l' => 'dddd',
        'N' => 'E',
        'S' => 'o',
        'w' => 'e',
        'z' => 'DDD',
        'W' => 'W',
        'F' => 'MMMM',
        'm' => 'MM',
        'M' => 'MMM',
        'n' => 'M',
        't' => '', // no equivalent
        'L' => '', // no equivalent
        'o' => 'YYYY',
        'Y' => 'YYYY',
        'y' => 'YY',
        'a' => 'a',
        'A' => 'A',
        'B' => '', // no equivalent
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => 'SSS',
        'e' => 'zz', // deprecated since version 1.6.0 of moment.js
        'I' => '', // no equivalent
        'O' => '', // no equivalent
        'P' => '', // no equivalent
        'T' => '', // no equivalent
        'Z' => '', // no equivalent
        'c' => '', // no equivalent
        'r' => '', // no equivalent
        'U' => 'X',
    );
    $momentFormat = strtr($format, $replacements);

    return $momentFormat;
}


/**
 * Enqueue assets
 *
 * @return void
 */
function wpfd_enqueue_assets()
{
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_style('dashicons');
//    wp_enqueue_script('jquery-ui-1.11.4');
    wp_enqueue_script('wpfd-colorbox');
    wp_enqueue_script('wpfd-colorbox-init');
    wp_enqueue_script('wpfd-videojs');
    wp_enqueue_style('wpfd-videojs');
    wp_enqueue_style('wpfd-colorbox');
    wp_enqueue_style('wpfd-viewer');
}

/**
 * Search assets
 *
 * @return void
 */
function wpfd_register_assets()
{
    wp_enqueue_script('jquery');
    wp_enqueue_style('dashicons');
    wp_register_style(
        'jquery-ui-1.9.2',
        plugins_url('app/admin/assets/css/ui-lightness/jquery-ui-1.9.2.custom.min.css', WPFD_PLUGIN_FILE)
    );
    wp_register_script('wpfd-colorbox', plugins_url('app/site/assets/js/jquery.colorbox-min.js', WPFD_PLUGIN_FILE));
    wp_register_script(
        'wpfd-colorbox-init',
        plugins_url('app/site/assets/js/colorbox.init.js', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_localize_script(
        'wpfd-colorbox-init',
        'wpfdcolorboxvars',
        array(
            'preview_loading_message' => sprintf(esc_html__('The preview is still loading, you can %s it at any time...', 'wpfd'), '<span class="wpfd-loading-close">' . esc_html__('cancel', 'wpfd') . '</span>'),
        )
    );
    wp_register_script(
        'wpfd-videojs',
        plugins_url('app/site/assets/js/video.js', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_localize_script(
        'wpfd-colorbox',
        'wpfdcolorbox',
        array('wpfdajaxurl' => wpfd_sanitize_ajax_url(\Joomunited\WPFramework\v1_0_5\Application::getInstance('Wpfd')->getAjaxUrl()))
    );

    wp_register_style(
        'wpfd-videojs',
        plugins_url('app/site/assets/css/video-js.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_register_style(
        'wpfd-colorbox',
        plugins_url('app/site/assets/css/colorbox.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_register_style(
        'wpfd-viewer',
        plugins_url('app/site/assets/css/viewer.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
}


/**
 * Search access
 *
 * @return void
 */
function wpfd_assets_search()
{
    wp_enqueue_style('wpfd-jquery-tagit', plugins_url('app/admin/assets/css/jquery.tagit.css', WPFD_PLUGIN_FILE));

    wp_enqueue_style(
        'wpfd-daterangepicker-style',
        plugins_url('app/admin/assets/ui/css/daterangepicker.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    if (!is_admin()) {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('wpfd-jquery-tagit', plugins_url('app/admin/assets/js/jquery.tagit.js', WPFD_PLUGIN_FILE), array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-autocomplete'));
        wp_enqueue_script(
            'wpfd-moment',
            plugins_url('app/admin/assets/ui/js/moment.min.js', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_script(
            'wpfd-daterangepicker',
            plugins_url('app/admin/assets/ui/js/daterangepicker.min.js', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_script(
            'wpfd-search_filter',
            plugins_url('app/site/assets/js/search_filter.js', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
    }
    \Joomunited\WPFramework\v1_0_5\Application::getInstance('Wpfd', WPFD_PLUGIN_FILE, 'site');
    $modelConfig = \Joomunited\WPFramework\v1_0_5\Model::getInstance('configfront');
    if (method_exists($modelConfig, 'getGlobalConfig')) {
        $globalConfig = $modelConfig->getGlobalConfig();
    } elseif (method_exists($modelConfig, 'getConfig')) {
        $globalConfig = $modelConfig->getConfig();
    }
    $searchconfig = $modelConfig->getSearchConfig();
    $locale = substr(get_locale(), 0, 2);

    // Add translable for search form daterangepicker
    wp_add_inline_script('wpfd-search_filter', 'var wpfdLocaleSettings = {
            "format": "' . wpfdPhpToMomentDateFormat($globalConfig['date_format']) . '",
            "separator": " - ",
            "applyLabel": "' . esc_html__('Apply', 'wpfd') . '",
            "cancelLabel": "' . esc_html__('Cancel', 'wpfd') . '",
            "fromLabel": "' . esc_html__('From', 'wpfd') . '",
            "toLabel": "' . esc_html__('To', 'wpfd') . '",
            "customRangeLabel": "' . esc_html__('Custom', 'wpfd') . '",
            "weekLabel": "' . esc_html__('W', 'wpfd') . '",
            "daysOfWeek": [
                "' . esc_html__('Su', 'wpfd') . '",
                "' . esc_html__('Mo', 'wpfd') . '",
                "' . esc_html__('Tu', 'wpfd') . '",
                "' . esc_html__('We', 'wpfd') . '",
                "' . esc_html__('Th', 'wpfd') . '",
                "' . esc_html__('Fr', 'wpfd') . '",
                "' . esc_html__('Sa', 'wpfd') . '",
            ],
            "monthNames": [
                "' . esc_html__('January', 'wpfd') . '",
                "' . esc_html__('February', 'wpfd') . '",
                "' . esc_html__('March', 'wpfd') . '",
                "' . esc_html__('April', 'wpfd') . '",
                "' . esc_html__('May', 'wpfd') . '",
                "' . esc_html__('June', 'wpfd') . '",
                "' . esc_html__('July', 'wpfd') . '",
                "' . esc_html__('August', 'wpfd') . '",
                "' . esc_html__('September', 'wpfd') . '",
                "' . esc_html__('October', 'wpfd') . '",
                "' . esc_html__('November', 'wpfd') . '",
                "' . esc_html__('December', 'wpfd') . '",
            ],
            "firstDay": 1,
        }', 'before');

    wp_localize_script(
        'wpfd-search_filter',
        'wpfdvars',
        array(
            'basejUrl' => home_url('?page_id=' . $searchconfig['search_page']),
            'dateFormat' => wpfdPhpToMomentDateFormat($globalConfig['date_format']),
            'locale' => $locale,
            'msg_search_box_placeholder' => esc_html__('Input tags here...', 'wpfd'),
            'msg_file_category' => esc_html__('FILES CATEGORY', 'wpfd'),
            'msg_filter_by_tags' => esc_html__('Filter by Tags', 'wpfd'),
            'msg_no_tag_in_this_category_found' => esc_html__('No tags in this category found!', 'wpfd'),
        )
    );
}


/**
 * Create secure folder
 *
 * @param string $path Path to folder need to created
 *
 * @return boolean
 */
function wpfdCreateSecureFolder($path)
{
    // Correct the path
    $path = trailingslashit($path);

    if (file_exists($path . 'index.html') && file_exists($path . '.htaccess')) {
        return true;
    }

    if (!file_exists($path)) {
        mkdir($path, 0777, true);
        $data = '<html><body bgcolor="#FFFFFF"></body></html>';
        $file = fopen($path . DIRECTORY_SEPARATOR . 'index.html', 'w');
        fwrite($file, $data);
        fclose($file);
        $data = "Options -Indexes\nOrder deny,allow\nDeny from all\n<Files ~ \".(png|svg|css)$\">\nAllow from all\n</Files>";
        $file = fopen($path . DIRECTORY_SEPARATOR . '.htaccess', 'w');
        fwrite($file, $data);
        fclose($file);

        return true;
    }

    return false;
}

/**
 * Generate PHP Handlebars Renderer
 *
 * @param string $template   Handlebars template
 * @param array  $data       Template data
 * @param string $renderName Template renderer file name
 *
 * @return mixed
 */
function wpfdHandlerbarsRender($template, $data, $renderName)
{
    // Load zorudis/lightncandy
    $ds = DIRECTORY_SEPARATOR;
    require_once WPFD_PLUGIN_DIR_PATH . 'app'. $ds . 'site' . $ds . 'classes' . $ds . 'packages' . $ds . 'autoload.php';
    $php = LightnCandy\LightnCandy::compile($template, array(
        'flags' => LightnCandy\LightnCandy::FLAG_HANDLEBARS,
        'helpers' => array(
            'svgicon' => function ($name, $color, $size) {
                $icons = array();
                $icons['download-icon1'] = '<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M400 200c0 112-89 200-201 200C88 400 0 311 0 199S89 0 201 0c111 0 199 89 199 200zm-179-8l-3-1V89c0-15-7-24-18-23-13 1-18 9-18 22v107l-34-35c-8-8-18-11-27-2-8 8-7 18 2 26l63 63c10 11 18 10 28 0l63-62c8-8 10-17 2-27-7-8-17-7-27 2l-31 32zm-21 113h82c13 0 24-4 32-14 10-14 8-29 6-44-1-4-8-9-12-8-5 0-9 6-12 10-2 3-1 8 0 13 1 13-5 17-18 17H131c-25 0-25 0-26-25-1-3 0-6-2-7-3-4-8-8-12-8s-10 5-11 9c-11 30 8 57 40 57h80z"/></g></svg>';
                $icons['download-icon2'] = '<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M45 355h310v-65c1-16 15-27 29-22 9 3 16 11 16 20v91c0 12-10 21-24 21H26c-17 0-26-9-26-26v-85a22 22 0 0143-8 31 31 0 011 11l1 57z"/><path data-name="Path 1270" d="M222 235l5-5 45-45c9-9 23-9 32 0s9 22-1 32l-86 86c-10 10-23 10-34 0l-86-86a22 22 0 1131-31l45 44a55 55 0 013 4l2-1V24c0-13 8-23 20-24 13-1 24 9 24 23v212z"/></g></svg>';
                $icons['download-icon3'] = '<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M200 400H26c-18 0-26-9-26-27v-73c0-16 9-25 24-25h105a14 14 0 018 4l28 28c21 21 48 21 69 0l28-29a13 13 0 018-3h106c14 0 24 9 24 23v79c0 14-10 23-25 23H200zm155-63c-9 0-17 8-17 16a15 15 0 0015 16c9 0 17-7 17-16a16 16 0 00-15-16zm-47 16c0-9-7-16-16-16a16 16 0 00-15 16c0 9 6 16 15 16s16-7 16-16zM245 127h55a56 56 0 017 0c7 0 11 4 13 10 3 6 1 11-3 16l-43 45-62 63c-8 9-17 9-25 1L83 154c-5-5-6-11-4-18 3-7 9-9 15-9h61v-9-99c0-14 5-19 17-19h55c13 0 18 5 18 19v99z"/></g></svg>';
                $icons['download-icon4'] = '<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M178 234v-7V24c0-13 8-23 20-24 13-1 24 9 24 23v212l5-5 44-44c10-9 23-10 32-1s9 23-1 33l-85 85c-10 11-23 11-34 0l-85-86a22 22 0 0123-37 28 28 0 018 6l44 44a31 31 0 013 5zM200 400H24c-17 0-28-14-23-29 3-10 12-15 23-16h351c12 0 21 6 24 16 5 15-6 29-22 29H200z"/></g></svg>';
                $icons['preview-icon1'] = '<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M200 325c-36 0-70-11-101-30a356 356 0 01-92-80c-9-11-9-19 0-29 37-44 79-79 131-99 51-20 102-14 151 12 41 22 76 52 106 89 6 8 7 16 1 23-39 48-85 85-141 105a167 167 0 01-55 9zm0-47c41 0 75-36 75-81s-34-81-75-81c-42 0-75 36-75 81s34 81 75 81z"/><path d="M200 159c21 0 38 17 38 38 0 20-17 37-38 37s-38-17-38-37c0-21 17-38 38-38z"/></g></svg>';
                $icons['preview-icon2'] = '<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M0 195c2-10 9-18 16-26 26-28 54-52 89-70a203 203 0 01202 6c33 20 61 46 86 75 3 4 5 10 7 15v10c-2 10-9 18-16 26-24 26-51 50-84 67a206 206 0 01-207-3c-31-18-57-42-81-69-5-6-10-13-12-21zm199 99c30 0 57-8 82-21 33-18 60-43 84-70 2-2 2-4 0-6-21-24-45-47-74-64-27-16-56-26-88-27-28 0-54 6-79 19-35 17-64 43-89 72-2 2-2 4 0 6 21 24 45 47 74 64 27 17 58 27 90 27z"/><path d="M202 276c-45 1-82-32-84-73-2-42 34-77 79-79s83 31 85 74c1 42-34 76-80 78zm-2-30c27-1 49-21 49-46s-22-46-49-45c-27 0-49 20-49 45s22 46 49 46z"/></g></svg>';
                $icons['preview-icon3'] = '<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M201 0l12 12 80 76a9 9 0 012 6v98c0 3 1 5 3 6a83 83 0 0133 122l-2 3 61 59-19 18-60-59-15 8-1 3v19H10v-5V4 0zm68 108h-86V25H36v321h176l-2-2a83 83 0 01-33-38c-1-3-3-4-6-4H66v-25h103l5-33H66v-25h121a8 8 0 005-2c19-19 41-29 68-28h9zm-11 225c34 0 62-27 62-59 0-34-28-61-62-61s-62 27-62 59c0 34 27 61 62 61zm-9-250l-40-39v39z"/></g></svg>';
                $icons['preview-icon4'] = '<svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M0 200V55C0 28 16 8 41 2a63 63 0 0115-2h289c32 0 55 23 55 54v248c0 10-6 16-15 16-8 0-14-6-14-16V56c0-16-11-27-28-27H57c-18 0-28 9-28 28v286c0 19 10 28 28 28h243c11 0 18 6 18 15s-7 14-18 14H56c-28 0-49-16-55-41a67 67 0 01-1-15V200z"/><path d="M314 302l15 15 52 51c7 7 7 14 2 20-6 6-13 5-20-2l-67-65a11 11 0 00-1-1 104 104 0 01-149-26c-29-43-20-99 20-133 39-33 99-31 137 4 38 34 45 93 11 137zm-159-64c-1 42 34 77 77 77 42 1 78-33 78-75 1-42-34-77-75-77-44-1-79 32-80 75z"/></g></svg>';

                $icon = isset($icons[$name]) ? $icons[$name] : '';
                $icon = preg_replace('/fill=\"#[a-z0-9]{3,6}\"/', 'fill="' . $color . '"', $icon);
                $icon = preg_replace('/width=\"[0-9]+\"/', 'width="' . $size . '"', $icon);
                $icon = preg_replace('/height=\"[0-9].\"/', 'height="' . $size . '"', $icon);

                return $icon;
            },
            'xif' => function ($param, $second, $options) {
                if ($param === $second) {
                    return $options['fn']();
                } else {
                    return $options['inverse']();
                }
            },
            'xunless' => function ($param, $second, $options) {
                if ($param !== $second) {
                    return $options['fn']();
                } else {
                    return $options['inverse']();
                }
            },
        )
    ));

    $allow_include_url = ini_get('allow_url_include'); // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.allow_url_includeDeprecated -- It will return false if not exists
    if (function_exists('file_put_contents') && (strval($allow_include_url) === '0' || strval($allow_include_url) === 'off' || strval($allow_include_url) === '')) {
        $rendererPath = WP_CONTENT_DIR . $ds . 'wp-file-download' . $ds . 'renderer';
        wpfdCreateSecureFolder($rendererPath);
        file_put_contents($rendererPath . $ds . $renderName . '.php', '<?php ' . $php . '?>');
        $renderer = include($rendererPath . $ds . $renderName . '.php');
    } else {
        /**
         * Deprecated
         * This method require php.allow_url_include enable.
         * Unfortunately, almost hosting provider disable this setting for security problem.
         */
        $renderer = LightnCandy\LightnCandy::prepare($php);
    }

    return $renderer($data);
}

/**
 * Initialize the plugin
 *
 * Load the plugin only after Elementor (and other plugins) are loaded.
 * Checks for basic plugin requirements, if one check fail don't continue,
 * if all check have passed load the files required to run the plugin.
 *
 * Fired by `plugins_loaded` action hook.
 *
 * @since 1.0.0
 *
 * @access public
 *
 * @return void
 */
function wpfd_elementor_init()
{
    // Add Plugin actions
    add_action('elementor/widgets/widgets_registered', 'wpfd_elementor_widgets');
    // Register Widget Scripts
    add_action('elementor/frontend/after_register_scripts', 'wpfd_elementor_widget_scripts');
    // Blacklist default search widget
    add_filter('elementor/widgets/black_list', function ($blacklists) {
        $blacklists[] = 'WPFDWidgetSearch';
        return $blacklists;
    });
}
add_action('plugins_loaded', 'wpfd_elementor_init');

/**
 * Elementor widget
 *
 * @return void
 */
function wpfd_elementor_widgets()
{
    // Add backend js
    wpfd_elementor_scripts();
    // Load widget icon style by theme mod
    $ui_theme = \Elementor\Core\Settings\Manager::get_settings_managers('editorPreferences')->get_model()->get_settings('ui_theme');

    if ('auto' === $ui_theme) {
        wp_enqueue_style(
            'wpfd-elementor-widget-dark-style',
            WPFD_PLUGIN_URL . 'app/includes/elementor/assets/css/elementor.dark.css',
            array(
                'wpfd-elementor-widget-style'
            ),
            ELEMENTOR_VERSION,
            '(prefers-color-scheme: dark)'
        );
        wp_enqueue_style(
            'wpfd-elementor-widget-light-style',
            WPFD_PLUGIN_URL . 'app/includes/elementor/assets/css/elementor.light.css',
            array(
                'wpfd-elementor-widget-style'
            ),
            ELEMENTOR_VERSION,
            '(prefers-color-scheme: light)'
        );
    } elseif ('dark' === $ui_theme) {
        wp_enqueue_style(
            'wpfd-elementor-widget-dark-style',
            WPFD_PLUGIN_URL . 'app/includes/elementor/assets/css/elementor.dark.css',
            array(
                'wpfd-elementor-widget-style'
            ),
            ELEMENTOR_VERSION,
            'all'
        );
    } else { // Light mode
        wp_enqueue_style(
            'wpfd-elementor-widget-light-style',
            WPFD_PLUGIN_URL . 'app/includes/elementor/assets/css/elementor.light.css',
            array(
                'wpfd-elementor-widget-style'
            ),
            ELEMENTOR_VERSION,
            'all'
        );
    }
    //Include wpfd file widgets
    require_once WPFD_PLUGIN_DIR_PATH . 'app'. DIRECTORY_SEPARATOR . 'includes'. DIRECTORY_SEPARATOR . 'elementor'. DIRECTORY_SEPARATOR . 'wpfd_file_widget.php';
    require_once WPFD_PLUGIN_DIR_PATH . 'app'. DIRECTORY_SEPARATOR . 'includes'. DIRECTORY_SEPARATOR . 'elementor'. DIRECTORY_SEPARATOR . 'wpfd_category_widget.php';
    require_once WPFD_PLUGIN_DIR_PATH . 'app'. DIRECTORY_SEPARATOR . 'includes'. DIRECTORY_SEPARATOR . 'elementor'. DIRECTORY_SEPARATOR . 'wpfd_search_widget.php';
    //Create new widgets
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new WpfdSingleFileWidget\ElementorSingleFileWidget());
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new WpfdCategoryWidget\ElementorCategoryWidget());
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new WpfdSearchWidget\ElementorSearchWidget());
}

/**
 * Elementor assets
 *
 * @return void
 */
function wpfd_elementor_scripts()
{
    // Add Js to wpfd elementor
    wp_enqueue_style('wpfd-modal', WPFD_PLUGIN_URL . 'app/admin/assets/css/leanmodal.css');
    wp_enqueue_script('wpfd-modal', WPFD_PLUGIN_URL . 'app/admin/assets/js/jquery.leanModal.min.js', array('jquery'));
    wp_enqueue_script(
        'wpfd-elementor',
        WPFD_PLUGIN_URL . 'app/includes/elementor/assets/js/jquery.elementor.js',
        array( 'jquery', 'wpfd-modal' )
    );

    wp_localize_script('wpfd-elementor', 'wpfd_elemetor_vars', array(
        'dir' => WPFD_PLUGIN_URL
    ));
    // Add css to wpfd elementor
    wp_enqueue_style(
        'wpfd-elementor-widget-style',
        WPFD_PLUGIN_URL . 'app/includes/elementor/assets/css/elementor.widgets.css',
        array(),
        WPFD_VERSION
    );
}
/**
 * Elementor widgets enqueue scripts.
 *
 * @since 1.0.0
 *
 * @access public
 *
 * @return void
 */
function wpfd_elementor_widget_scripts()
{
    wp_enqueue_script(
        'jquery-elementor-widgets',
        WPFD_PLUGIN_URL . 'app/includes/elementor/assets/js/jquery.elementor.widgets.js',
        array('jquery'),
        WPFD_VERSION
    );
}

/**
 * Get generate preview queue schedule
 *
 * @param integer $schedule Schedule interval time in second. Default 5min
 *
 * @return mixed
 */
function wpfd_get_generate_preview_queue_schedule($schedule)
{
    $interval = (defined('WPFD_GENERATE_PREVIEW_QUEUE_INTERVAL') && WPFD_GENERATE_PREVIEW_QUEUE_INTERVAL > 0) ? WPFD_GENERATE_PREVIEW_QUEUE_INTERVAL : 5 * 60;
    /**
     * Filter to change generate preview interval, this will override WPFD_GENERATE_PREVIEW_QUEUE_INTERVAL constance
     *
     * @param $interval Time in second
     */
    $interval = apply_filters('wpfd_generate_preview_queue_interval', $interval);
    $schedule['wpfd_generate_preview_queue_interval'] = array(
        'interval' => $interval,
        'display'  => esc_html__('WPFD Generate Preview Queue', 'wpfd'),
    );
    return $schedule;
}

/**
 * Get category visibility
 *
 * @param WP_Term|integer $category Category instance or id
 *
 * @return array|boolean
 */
function wpfd_get_category_visibility($category)
{
    if ($category === 0) {
        // This is root category, return public;
        return array('roles' => array(), 'access' => 0);
    }
    if (!$category instanceof WP_Term && is_numeric($category)) {
        $category = get_term($category, 'wpfd-category');
    }

    if (is_wp_error($category)) {
        return false;
    }

    if ($category && !isset($category->term_id)) {
        return false;
    }

    $termMeta = get_option('taxonomy_' . $category->term_id, false);

    if (!$termMeta) {
        // Category never saved before, return Inherited;
        return wpfd_get_category_visibility($category->parent);
    }

    /**
     * Filters allow setup default visibility
     *
     * @param array Default values: roles, private
     *
     * @return array
     */
    $defaultParams = apply_filters('wpfd_default_category_visibility', array('roles' => array(), 'access' => -1));
    $categoryRoles = isset($termMeta['roles']) ? (array) $termMeta['roles'] : $defaultParams['roles'];
    $categoryAccess = isset($termMeta['access']) ? (int) $termMeta['access'] : $defaultParams['access'];

    if ($categoryAccess === -1 && $category->parent > 0) {
        return wpfd_get_category_visibility($category->parent);
    }

    return array('roles' => $categoryRoles, 'access' => $categoryAccess);
}

/**
 * Handle redirects to setup/welcome page after install and updates.
 *
 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
 *
 * @return void
 */
function wpfd_wizard_setup_redirect()
{
    // Setup wizard redirect
    if (is_null(get_option('_wpfd_installed', null)) || get_option('_wpfd_installed') === 'false') {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
        if ((!empty($_GET['page']) && in_array($_GET['page'], array('wpfd-setup')))) {
            return;
        }

        wp_safe_redirect(admin_url('index.php?page=wpfd-setup'));
        exit;
    }
}

/**
 * Includes WP File Download setup
 *
 * @return void
 */
function wpfd_wizard_setup_include()
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
    if (!empty($_GET['page'])) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
        switch ($_GET['page']) {
            case 'wpfd-setup':
                require_once WPFD_PLUGIN_DIR_PATH . 'app/admin/classes/install-wizard/install-wizard.php';
                break;
        }
    }
}

/**
 * Generate preview queue task
 *
 * @return void
 */
function wpfd_generate_preview_queue_tasks()
{
    $modelPath = WPFD_PLUGIN_DIR_PATH . 'app/admin/models/generatepreview.php';
    if (file_exists($modelPath)) {
        include_once WPFD_PLUGIN_DIR_PATH . 'app/admin/models/generatepreview.php';
        $model = new WpfdModelGeneratepreview();
        $model->runqueue();
    }
}

/**
 * Init schedule tasks
 *
 * @return void
 */
function wpfd_init_tasks()
{
    // Setup cron interval for Statistics Storage be clear
    add_filter('wpfd_get_schedules', 'wpfd_get_remove_statistics_schedule');
    add_filter('wpfd_get_schedules', 'wpfd_get_clean_junks_schedule');
    add_filter('wpfd_get_schedules', 'wpfd_get_generate_preview_queue_schedule');

    wpfd_schedules();

        // Add hook for cronjob
    add_action('wpfd_remove_statistics_tasks', 'wpfd_remove_statistics');
    add_action('wpfd_remove_junks_tasks', 'wpfd_clean_junks');
    add_action('wpfd_generate_preview_queue_tasks', 'wpfd_generate_preview_queue_tasks');

    // Install cronjob
    wpfd_install_job('wpfd_remove_statistics_tasks', 'wpfd_remove_statistics');
    wpfd_install_job('wpfd_remove_junks_tasks', 'wpfd_clean_junks');
    wpfd_install_job('wpfd_generate_preview_queue_tasks', 'wpfd_generate_preview_queue_interval');

    // Reload schedule after main config saved
    add_action('wpfd_after_main_setting_save', 'wpfd_after_main_setting_save');
}

/**
 * Init Divi Modules
 *
 * @return void
 */
if (! function_exists('diviInitializeExtension')) :
    /**
     * Creates the extension's main class instance.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function diviInitializeExtension()
    {
        $config                 = WPFD_PLUGIN_DIR_PATH . '/app/admin/models/config.php';
        include_once $config;
        $configModel            = new WpfdModelConfig();
        $themeList              = $configModel->getThemes();
        require_once WPFD_PLUGIN_DIR_PATH . '/app/includes/divi/includes/Divi.php';
        include_once WPFD_PLUGIN_DIR_PATH . '/app/admin/helpers/WpfdHelperFile.php';
        $themeCustomPath        = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;
        $themeCustomUrl         = WP_CONTENT_URL . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;
        $customSingleFilePath   = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'wpfd-single-file-button.css';
        $customSingleFileUrl    = WP_CONTENT_URL . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'wpfd-single-file-button.css';
        $lastRebuildTime        = get_option('wpfd_icon_rebuild_time', false);
        if (false === $lastRebuildTime) {
            $lastRebuildTime = WpfdHelperFile::renderCss();
        }
        $iconsetPngPath         = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'png' . DIRECTORY_SEPARATOR . 'styles-'. $lastRebuildTime .'.css';
        $iconsetPngUrl          = WP_CONTENT_URL . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'png' . DIRECTORY_SEPARATOR . 'styles-'. $lastRebuildTime .'.css';
        $iconsetSvgPath         = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'svg' . DIRECTORY_SEPARATOR . 'styles-'. $lastRebuildTime .'.css';
        $iconsetSvgUrl          = WP_CONTENT_URL . DIRECTORY_SEPARATOR . 'wp-file-download' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'svg' . DIRECTORY_SEPARATOR . 'styles-'. $lastRebuildTime .'.css';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is for check only
        if (isset($_GET['et_fb']) && current_user_can('edit_posts')) {
            if (!empty($themeList)) {
                foreach ($themeList as $theme) {
                    if ($theme !== 'default' && $theme !== 'ggd' && $theme !== 'table' && $theme !== 'tree') {
                        if (file_exists($themeCustomPath . 'wpfd-' . $theme)) {
                            wp_enqueue_style(
                                'wpfd-divi-custom-'. $theme .'-theme-style',
                                $themeCustomUrl . 'wpfd-' . $theme . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'style.css',
                                array(),
                                WPFD_VERSION,
                                'all'
                            );
                        }
                    }
                }
            }

            if (file_exists($customSingleFilePath)) {
                wp_enqueue_style(
                    'wpfd-divi-custom-file-buttons-style',
                    $customSingleFileUrl,
                    array(),
                    WPFD_VERSION,
                    'all'
                );
            } else {
                wp_enqueue_style(
                    'wpfd-divi-file-buttons-style',
                    WPFD_PLUGIN_URL . 'app/site/assets/css/wpfd-single-file-button.css',
                    array(),
                    WPFD_VERSION,
                    'all'
                );
            }

            if (file_exists($iconsetPngPath)) {
                wp_enqueue_style(
                    'wpfd-divi-iconset-png-style',
                    $iconsetPngUrl,
                    array(),
                    WPFD_VERSION,
                    'all'
                );
            }

            if (file_exists($iconsetSvgPath)) {
                wp_enqueue_style(
                    'wpfd-divi-iconset-svg-style',
                    $iconsetSvgUrl,
                    array(),
                    WPFD_VERSION,
                    'all'
                );
            }
        }

        wp_enqueue_style(
            'wpfd-divi-modules-style',
            WPFD_PLUGIN_URL . 'app/admin/assets/css/divi-module-style.css',
            array(),
            WPFD_VERSION,
            'all'
        );

        wp_enqueue_style(
            'wpfd-divi-category-style',
            WPFD_PLUGIN_URL . 'app/admin/assets/css/divi-category-style.css',
            array(),
            WPFD_VERSION,
            'all'
        );

        wp_enqueue_style(
            'wpfd-divi-file-style',
            WPFD_PLUGIN_URL . 'app/admin/assets/css/divi-file-style.css',
            array(),
            WPFD_VERSION,
            'all'
        );

        wp_enqueue_style(
            'wpfd-divi-search-style',
            WPFD_PLUGIN_URL . 'app/admin/assets/css/divi-search-style.css',
            array(),
            WPFD_VERSION,
            'all'
        );
    }
    add_action('divi_extensions_init', 'diviInitializeExtension');
endif;


if (!function_exists('wpfd_initializeWPBakery')) {
    /**
     * Creates the extension's main class instance.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function wpfd_initializeWPBakery()
    {

        if (!defined('WPB_VC_VERSION')) {
            return;
        }

        require_once(WPFD_PLUGIN_DIR_PATH . '/app/includes/wpbakery/category.php');
        require_once(WPFD_PLUGIN_DIR_PATH . '/app/includes/wpbakery/file.php');
        require_once(WPFD_PLUGIN_DIR_PATH . '/app/includes/wpbakery/search.php');

        if (is_admin()) {
            //backend enqueue
            add_action('vc_backend_editor_enqueue_js_css', 'wpfd_wpbakery_enqueue_assets');

            //frontend enqueue
            add_action('vc_frontend_editor_enqueue_js_css', 'wpfd_wpbakery_enqueue_assets');
        }

        wp_enqueue_style(
            'wpfd-wpbakery-style',
            WPFD_PLUGIN_URL . 'app/includes/wpbakery/assets/css/wpbakery.css',
            array(),
            WPFD_VERSION,
            'all'
        );
    }
    add_action('init', 'wpfd_initializeWPBakery');
}

/**
 * WPBakery enqueue assets
 *
 * @return void
 */
function wpfd_wpbakery_enqueue_assets()
{

    wp_enqueue_style(
        'wpfd-wpbakery-category-style',
        WPFD_PLUGIN_URL . 'app/includes/wpbakery/assets/css/category.css',
        array(),
        WPFD_VERSION,
        'all'
    );

    wp_enqueue_style(
        'wpfd-wpbakery-file-style',
        WPFD_PLUGIN_URL . 'app/includes/wpbakery/assets/css/file.css',
        array(),
        WPFD_VERSION,
        'all'
    );

    wp_enqueue_style(
        'wpfd-wpbakery-search-style',
        WPFD_PLUGIN_URL . 'app/includes/wpbakery/assets/css/search.css',
        array(),
        WPFD_VERSION,
        'all'
    );

    wp_enqueue_style('wpfd-wpbakery-lean-modal', WPFD_PLUGIN_URL . 'app/admin/assets/css/leanmodal.css');

    wp_enqueue_script('wpfd-wpbakery-init-modal', WPFD_PLUGIN_URL . 'app/admin/assets/js/jquery.leanModal.min.js', array('jquery'));

    wp_enqueue_script(
        'wpfd-wpbakery-modal',
        WPFD_PLUGIN_URL . 'app/includes/wpbakery/assets/js/jquery.wpbakery.js',
        array( 'jquery', 'wpfd-wpbakery-init-modal' )
    );

    wp_localize_script('wpfd-wpbakery-modal', 'wpfd_wpbakery_vars', array(
        'dir' => WPFD_PLUGIN_URL
    ));
}

/**
 * Get ABSPATH
 *
 * @return string|null
 */
function wpfd_get_abspath()
{
    if (defined('ABSPATH') && ABSPATH) {
        return ABSPATH;
    }

    $pInfo = null;
    if (defined('WPFD_PLUGIN_DIR_PATH') && WPFD_PLUGIN_DIR_PATH) {
        $pInfo = pathinfo(WPFD_PLUGIN_DIR_PATH);
        $pInfo = explode(DIRECTORY_SEPARATOR, $pInfo['dirname']);
        array_splice($pInfo, count($pInfo) - 2, 2);
        $pInfo = implode(DIRECTORY_SEPARATOR, $pInfo);
    }

    return $pInfo;
}

if (!function_exists('wpfd_initializeAvada')) {

    /**
     * Wpfd_initializeAvada
     *
     * @return void
     */
    function wpfd_initializeAvada()
    {

        if (!defined('AVADA_VERSION') || !defined('FUSION_BUILDER_VERSION')) {
            return;
        }

        require_once(WPFD_PLUGIN_DIR_PATH . '/app/includes/avada/category.php');
        require_once(WPFD_PLUGIN_DIR_PATH . '/app/includes/avada/file.php');
        require_once(WPFD_PLUGIN_DIR_PATH . '/app/includes/avada/search.php');

        if (fusion_is_builder_frame()) {
            add_action('wp_enqueue_scripts', 'wpfd_avada_enqueue_assets', 999);
        }

        if (class_exists('Fusion_App')) {
            $fusion_app_instance = Fusion_App::get_instance();
            if ($fusion_app_instance->is_builder) {
                add_action('wp_enqueue_scripts', 'wpfd_avada_live_scripts', 997);
            }
        }

        if (is_admin()) {
            add_action('fusion_builder_admin_scripts_hook', 'wpfd_avada_live_scripts');
        }
    }
    add_action('init', 'wpfd_initializeAvada');
}

/**
 * Wpfd_avada_enqueue_assets
 *
 * @return void
 */
function wpfd_avada_enqueue_assets()
{

    wp_enqueue_style(
        'wpfd-avada-style',
        WPFD_PLUGIN_URL . 'app/includes/avada/assets/css/avada.css',
        array(),
        WPFD_VERSION,
        'all'
    );
}

/**
 * Wpfd_avada_live_scripts
 *
 * @return void
 */
function wpfd_avada_live_scripts()
{

    wp_enqueue_style('wpfd-avada-lean-modal', WPFD_PLUGIN_URL . 'app/admin/assets/css/leanmodal.css');

    wp_enqueue_script('wpfd-avada-lean-modal', WPFD_PLUGIN_URL . 'app/admin/assets/js/jquery.leanModal.min.js', array('jquery'));

    wp_enqueue_script(
        'wpfd-avada-modal',
        WPFD_PLUGIN_URL . 'app/includes/avada/assets/js/jquery.avada.js',
        array( 'jquery', 'wpfd-avada-lean-modal' )
    );

    wp_localize_script('wpfd-avada-modal', 'wpfd_avada_vars', array(
        'dir'       => WPFD_PLUGIN_URL,
        'adminurl'  => admin_url()
    ));
}
<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0.3
 */

//-- No direct access
defined('ABSPATH') || die();

/**
 * Class WpfdThemeTable
 */
class WpfdThemeTable extends WpfdTheme
{
    /**
     * Theme name
     *
     * @var string
     */
    public $name = 'table';

    /**
     * Get tpl path for include
     *
     * @return string
     */
    public function getTplPath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tpl.php';
    }

    /**
     * Load template hooks
     *
     * @return void
     */
    public function loadHooks()
    {
        $this->hideEmpty(false);
        parent::loadHooks();
        $this->customAssets();
    }

    /**
     * Load custom hooks and filters
     *
     * @return void
     */
    public function loadCustomHooks()
    {
        $name = $this->getThemeName();

        add_filter('wpfd_' . $name . '_content_wrapper', array(__CLASS__, 'contentWrapper'), 10, 2);

        // Using local title
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showtitle', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thTitle'), 10);
            add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showTitleHandlebars'), 5, 2);
            add_action('wpfd_' . $name . '_file_info', array(__CLASS__, 'showTitle'), 5, 3);
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdescription', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thDesc'), 20);
            add_filter('wpfd_' . $name . '_file_info_description_handlebars_args', array(
                __CLASS__,
                'descriptionHandlebars'
            ), 10, 3);
            add_filter('wpfd_' . $name . '_file_info_description_args', array(__CLASS__, 'description'), 10, 4);
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showversion', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thVersion'), 30);
            add_filter('wpfd_' . $name . '_file_info_version_handlebars_args', array(__CLASS__, 'versionHandlebars'), 10, 3);
            add_filter('wpfd_' . $name . '_file_info_version_args', array(__CLASS__, 'version'), 10, 4);
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showsize', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thSize'), 40);
            add_filter('wpfd_' . $name . '_file_info_size_handlebars_args', array(__CLASS__, 'sizeHandlebars'), 10, 3);
            add_filter('wpfd_' . $name . '_file_info_size_args', array(__CLASS__, 'size'), 10, 4);
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showhits', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thHits'), 50);
            add_filter('wpfd_' . $name . '_file_info_hits_handlebars_args', array(__CLASS__, 'hitsHandlebars'), 10, 3);
            add_filter('wpfd_' . $name . '_file_info_hits_args', array(__CLASS__, 'hits'), 10, 4);
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdateadd', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thCreated'), 60);
            add_filter('wpfd_' . $name . '_file_info_created_handlebars_args', array(__CLASS__, 'createdHandlebars'), 10, 3);
            add_filter('wpfd_' . $name . '_file_info_created_args', array(__CLASS__, 'created'), 10, 4);
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdatemodified', 0) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thModified'), 70);
            add_filter('wpfd_' . $name . '_file_info_modified_handlebars_args', array(__CLASS__, 'modifiedHandlebars'), 10, 3);
            add_filter('wpfd_' . $name . '_file_info_modified_args', array(__CLASS__, 'modified'), 10, 4);
        }

        // Show download heading when download or preview enabled
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdownload', 1) === 1 ||
            $this->config['use_google_viewer'] !== 'no') {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thDownload'), 80);
        }
    }

    /**
     * Load custom assets
     *
     * @return void
     */
    public function customAssets()
    {
        $themeName = $this->name;
        $classes = array(
            'wpfd-table',
            'wpfd-table-bordered',
            'wpfd-table-striped'
        );
        $classes = array_map(function ($class) use ($themeName) {
            return str_replace('table', $themeName, $class);
        }, $classes);
        /**
         * Additional classes for table
         *
         * @param array
         */
        $this->additionalClass = join(' ', apply_filters('wpfd_' . $this->name . 'additional_classes', $classes));

        // Load additional scripts
        wp_localize_script(
            'wpfd-theme-table',
            'wpfdTableTheme',
            array('wpfdajaxurl' => $this->ajaxUrl, 'columns' => esc_html__('Columns', 'wpfd'))
        );

        if (WpfdBase::checkExistTheme($this->name)) {
            $url = plugin_dir_url($this->path . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-' . $this->name . DIRECTORY_SEPARATOR . 'foobar');
        } else {
            $url  = wpfd_abs_path_to_url(realpath(dirname(wpfd_locate_theme($this->name, 'theme.php'))) . DIRECTORY_SEPARATOR);
        }
        wp_enqueue_script('wpfd-theme-table-mediatable', $url . 'js/jquery.mediaTable.js');
        wp_enqueue_style('wpfd-theme-table-mediatable', $url . 'css/jquery.mediaTable.css');
    }

    /**
     * Print content wrapper
     *
     * @param string $wrapper Content wrapper html
     * @param object $theme   Current theme object
     *
     * @return string
     */
    public static function contentWrapper($wrapper, $theme)
    {
        $wpfdcontentclass = '';
        if (WpfdBase::loadValue($theme->params, self::$prefix . 'stylingmenu', true)) {
            $wpfdcontentclass .= 'colstyle';
        }

        return sprintf(
            '<div class="wpfd-content wpfd-content-' . $theme->name . ' wpfd-content-multi %s" data-category="%s">',
            (string) esc_attr($wpfdcontentclass),
            (string) esc_attr($theme->category->term_id)
        );
    }

    /**
     * Print title handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showTitleHandlebars($config, $params)
    {
        $name = self::$themeName;
        $iconSet = isset($config['icon_set']) && $config['icon_set'] !== 'default' ? ' wpfd-icon-set-' . $config['icon_set'] : '';
        if ($config['custom_icon']) {
            $html = '{{#if file_custom_icon}}<span class="icon-custom"><img src="{{file_custom_icon}}"></span>{{else}}<span class="ext ext-{{ext}}' . $iconSet . '"></span>{{/if}}';
        } else {
            $html = '<span class="ext ext-{{ext}}' . $iconSet . '"></span>';
        }
        /**
         * Filter to change icon html for handlebars template
         *
         * @param string Output html for handlebars template
         * @param array  Main config
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_icon_hanlebars
         *
         * @return string
         *
         * @ignore
         */
        $html = apply_filters('wpfd_' . $name . '_file_info_icon_hanlebars', $html, $config, $params);

        $selectFileInput = '';
        if ((int) $config['download_selected'] === 1 && wpfd_can_download_files()) {
            $selectFileInput = '<label class="wpfd_checkbox"><input class="cbox_file_download" type="checkbox" data-id="{{ID}}" /><span></span></label>';
        }
        $template = array(
            'html' => $selectFileInput . '<a class="wpfd_downloadlink" href="%link$s" title="%title$s"><span class="extcol">%icon$s</span>%croptitle$s</a>',
            'args' => array(
                'link'      => '{{linkdownload}}',
                'title'     => '{{post_title}}',
                'icon'      => $html,
                'croptitle' => '{{{crop_title}}}'
            )
        );
        /**
         * Filter to change html and arguments of title handlebars
         *
         * @param array Template array
         * @param array Main config
         * @param array Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_title_handlebars_args
         *
         * @return array
         *
         * @ignore
         */
        $args = apply_filters('wpfd_' . $name . '_file_info_title_handlebars_args', $template, $config, $params);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo self::render('<td class="file_title">' . $args['html'] . '</td>', $args['args']);
    }

    /**
     * Print title
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showTitle($file, $config, $params)
    {
        $name = self::$themeName;
        if ($config['custom_icon'] && isset($file->file_custom_icon) && $file->file_custom_icon !== '') {
            $args = array(
                'html' => '<span class="icon-custom">
                                <img src="%iconurl$s">
                                <span class="icon-custom-title">
                                    %croptitle$s
                                </span>
                            </span>',
                'args' => array(
                    'iconurl'   => esc_url($file->file_custom_icon),
                    'croptitle' => esc_html($file->crop_title)
                )
            );
        } else {
            $args = array(
                'html' => '<span class="extcol">
                                <span class="ext ext-%class$s%iconset$s"></span>
                                %croptitle$s
                            </span>',
                'args' => array(
                    'class'     => esc_attr(strtolower($file->ext)),
                    'iconset'   => (isset($config['icon_set']) && $config['icon_set'] !== 'default') ? ' wpfd-icon-set-' . esc_attr($config['icon_set']) : '',
                    'croptitle' => esc_html($file->crop_title)
                )
            );
        }
        /**
         * Filter to change icon html
         *
         * @param array  Template array
         * @param object Current file object
         * @param array  Main config
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_icon_html
         *
         * @return string
         *
         * @ignore
         */
        $args = apply_filters('wpfd_' . $name . '_file_info_icon_html', $args, $file, $config, $params);

        $icon     = self::render($args['html'], $args['args']);

        $selectFileInput = '';
        if ((int) $config['download_selected'] === 1 && wpfd_can_download_files()) {
            $selectFileInput = '<label class="wpfd_checkbox"><input class="cbox_file_download" type="checkbox" data-id="' . $file->ID . '" /><span></span></label>';
        }
        $template = array(
            'html' => $selectFileInput . '<a class="wpfd_downloadlink" href="%link$s" title="%title$s">%icon$s</a>',
            'args' => array(
                'link'  => esc_url($file->linkdownload),
                'title' => esc_attr($file->post_title),
                'icon'  => $icon
            )
        );
        /**
         * Filter to change html and arguments of title
         *
         * @param array  Template array
         * @param object Current file object
         * @param array  Main config
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_title_args
         *
         * @return array
         *
         * @ignore
         */
        $args = apply_filters('wpfd_' . $name . '_file_info_title_args', $template, $file, $config, $params);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo self::render('<td class="file_title">' . $args['html'] . '</td>', $args['args']);
    }

    /**
     * Callback for file description handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function descriptionHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_desc">%value$s</td>',
            'args' => array(
                'value' => '{{{description}}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file description
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function description($args, $file, $config, $params)
    {
        $description = '';
        if (!empty($file->description)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Used wpfd_esc_desc to remove <script>
            $description = wpfd_esc_desc($file->description);
        }
        $args = array(
            'html' => '<td class="file_desc">%value$s</td>',
            'args' => array(
                'value' => $description
            )
        );

        return $args;
    }

    /**
     * Callback for file version handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function versionHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_version">%value$s</td>',
            'args' => array(
                'value' => '{{versionNumber}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file version
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function version($args, $file, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_version">%value$s</td>',
            'args' => array(
                'value' => esc_html(!empty($file->versionNumber) ? $file->versionNumber : '')
            )
        );

        return $args;
    }

    /**
     * Callback for file size handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function sizeHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_size">%value$s</td>',
            'args' => array(
                'value' => '{{bytesToSize size}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file size
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function size($args, $file, $config, $params)
    {
        $fileSize = (strtolower($file->size) === 'n/a' || $file->size <= 0) ? 'N/A' : WpfdHelperFile::bytesToSize($file->size);
        $args     = array(
            'html' => '<td class="file_size">%value$s</td>',
            'args' => array(
                'value' => esc_html($fileSize)
            )
        );

        return $args;
    }

    /**
     * Callback for file hits handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function hitsHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_hits">%value$s</td>',
            'args' => array(
                'value' => '{{hits}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file hits
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function hits($args, $file, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_hits">%value$s</td>',
            'args' => array(
                'value' => esc_html($file->hits)
            )
        );

        return $args;
    }

    /**
     * Callback for file created handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function createdHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_created">%value$s</td>',
            'args' => array(
                'value' => '{{created}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file created
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function created($args, $file, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_created">%value$s</td>',
            'args' => array(
                'value' => esc_html($file->created)
            )
        );

        return $args;
    }

    /**
     * Callback for file modified handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function modifiedHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_modified">%value$s</td>',
            'args' => array(
                'value' => '{{modified}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file modified
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function modified($args, $file, $config, $params)
    {
        $args = array(
            'html' => '<td class="file_modified">%value$s</td>',
            'args' => array(
                'value' => esc_html($file->modified)
            )
        );

        return $args;
    }

    /**
     * Callback for print title column header
     *
     * @return void
     */
    public static function thTitle()
    {
        $name = self::$themeName;
        $html = '<th class="essential persist file_title">' . esc_html__('Title', 'wpfd') . '</th>';

        /**
         * Filter to change html header of title column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_title_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_title_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print description column header
     *
     * @return void
     */
    public static function thDesc()
    {
        $name = self::$themeName;
        $html = '<th class="optional file_desc">' . esc_html__('Description', 'wpfd') . '</th>';

        /**
         * Filter to change html header of description column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_description_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_description_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print version column header
     *
     * @return void
     */
    public static function thVersion()
    {
        $name = self::$themeName;
        $html = '<th class="optional file_version">' . esc_html__('Version', 'wpfd') . '</th>';

        /**
         * Filter to change html header of version column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_version_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_version_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print size column header
     *
     * @return void
     */
    public static function thSize()
    {
        $name = self::$themeName;
        $html = '<th class="optional file_size">' . esc_html__('Size', 'wpfd') . '</th>';

        /**
         * Filter to change html header of size column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_size_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_size_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print hits column header
     *
     * @return void
     */
    public static function thHits()
    {
        $name = self::$themeName;
        $html = '<th class="optional file_hits">' . esc_html__('Hits', 'wpfd') . '</th>';

        /**
         * Filter to change html header of hits column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_hits_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_hits_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print created date column header
     *
     * @return void
     */
    public static function thCreated()
    {
        $name = self::$themeName;
        $html = '<th class="optional file_created">' . esc_html__('Date added', 'wpfd') . '</th>';

        /**
         * Filter to change html header of created date column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_created_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_created_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print modified date column header
     *
     * @return void
     */
    public static function thModified()
    {
        $name = self::$themeName;
        $html = '<th class="optional file_modified">' . esc_html__('Date modified', 'wpfd') . '</th>';

        /**
         * Filter to change html header of modified date column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_modified_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_modified_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print download column header
     *
     * @return void
     */
    public static function thDownload()
    {
        $name = self::$themeName;
        $html = '<th class="essential file_download">' . esc_html__('Download', 'wpfd') . '</th>';

        /**
         * Filter to change html header of download column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_download_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_download_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }
}

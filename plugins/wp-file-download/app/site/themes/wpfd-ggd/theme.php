<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

//-- No direct access
defined('ABSPATH') || die();

/**
 * Class WpfdThemeGgd
 */
class WpfdThemeGgd extends WpfdTheme
{
    /**
     * Theme name
     *
     * @var string
     */
    public $name = 'ggd';

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
        $name              = $this->getThemeName();
        $showsubcategories = (int) WpfdBase::loadValue($this->params, self::$prefix . 'showsubcategories', 1) === 1 ? true : false;
        $showcategorytitle = (int) WpfdBase::loadValue($this->params, self::$prefix . 'showcategorytitle', 1) === 1 ? true : false;
        $globalConfig      = get_option('_wpfd_global_config');

        /* File Block */
        // File content
        add_action('wpfd_' . $name . '_file_content_handlebars', array(__CLASS__, 'showIconHandlebars'), 10, 2);
        add_action('wpfd_' . $name . '_file_content_handlebars', array(__CLASS__, 'showTitleHandlebars'), 20, 2);

        add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showDescriptionHandlebars'), 10, 2);
        add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showVersionHandlebars'), 20, 2);
        add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showSizeHandlebars'), 30, 2);
        add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showHitsHandlebars'), 40, 2);
        add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showCreatedHandlebars'), 50, 2);
        add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showModifiedHandlebars'), 60, 2);

        // File buttons
        add_action('wpfd_' . $name . '_buttons_handlebars', array(__CLASS__, 'buttonWrapper'), 10);
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdownload', 1) === 1 && wpfd_can_download_files()) {
            add_action('wpfd_' . $name . '_buttons_handlebars', array(__CLASS__, 'showDownloadHandlebars'), 20, 2);
        }
        if ($this->config['use_google_viewer'] !== 'no' && wpfd_can_preview_files()) {
            add_action('wpfd_' . $name . '_buttons_handlebars', array(__CLASS__, 'showPreviewHandlebars'), 30, 2);
        }
        add_action('wpfd_' . $name . '_buttons_handlebars', array(__CLASS__, 'buttonWrapperEnd'), 90);
        // File info
        add_action('wpfd_' . $name . '_file_block_handlebars', array(__CLASS__, 'fileBlockWrapperHandlebars'), 10, 2);
        add_action('wpfd_' . $name . '_file_block_handlebars', array(__CLASS__, 'showFileBlockIconHandlebars'), 20, 2);
        add_action('wpfd_' . $name . '_file_block_handlebars', array(__CLASS__, 'showFileBlockTitleHandlebars'), 30, 2);
        add_action('wpfd_' . $name . '_file_block_handlebars', array(__CLASS__, 'linkClose'), 90, 1);

        add_action('wpfd_' . $name . '_file_block', array(__CLASS__, 'fileBlockWrapper'), 10, 3);
        add_action('wpfd_' . $name . '_file_block', array(__CLASS__, 'showFileBlockIcon'), 20, 3);
        add_action('wpfd_' . $name . '_file_block', array(__CLASS__, 'showFileBlockTitle'), 30, 3);
        add_action('wpfd_' . $name . '_file_block', array(__CLASS__, 'linkClose'), 90, 1);
        // End file info

        // Before files loop
        add_action('wpfd_' . $name . '_before_files_loop', array(__CLASS__, 'outputCategoriesWrapper'), 10, 2);
        add_action('wpfd_' . $name . '_before_files_loop_handlebars', array(__CLASS__, 'outputCategoriesWrapper'), 10, 2);

        if ($showcategorytitle && !$this->latest) {
            add_action('wpfd_' . $name . '_before_files_loop', array(__CLASS__, 'showCategoryTitle'), 20, 2);
        }

        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($this->categories) && count($this->categories) && $showsubcategories && !$this->latest) {
            add_action('wpfd_' . $name . '_before_files_loop', array(__CLASS__, 'showCategories'), 30, 2);
            add_action('wpfd_' . $name . '_before_files_loop_handlebars', array(__CLASS__, 'showCategoriesHandlebars'), 30, 2);
        }
        add_action('wpfd_' . $name . '_before_files_loop_handlebars', array(__CLASS__, 'showCategoryTitleHandlebars'), 20, 2);

        add_action('wpfd_' . $name . '_before_files_loop', array(__CLASS__, 'outputCategoriesWrapperEnd'), 90, 2);
        add_action('wpfd_' . $name . '_before_files_loop_handlebars', array(__CLASS__, 'outputCategoriesWrapperEnd'), 90, 2);
        // End before files loop

        /* Folder Tree */
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showfoldertree', 0) === 1 && !$this->latest) {
            add_action('wpfd_' . $name . '_folder_tree', array(__CLASS__, 'showTree'), 10, 2);
        }

        /* Theme Content Output - USER CAN NOT CHANGE THIS */
        add_action('wpfd_' . $name . '_before_theme_content', array(__CLASS__, 'outputContentWrapper'), 10, 1);
        add_action('wpfd_' . $name . '_before_theme_content', array(__CLASS__, 'outputContentHeader'), 20, 1);
        add_action('wpfd_' . $name . '_after_theme_content', array(__CLASS__, 'outputContentWrapperEnd'), 10, 1);

        /**
         * Action fire after template hooked
         *
         * @hookname wpfd_{$themeName}_after_template_hooks
         *
         * @ignore
         */
        do_action('wpfd_' . $name . '_after_template_hooks');
    }

    /**
     * Print button wrapper open
     *
     * @return void
     */
    public static function buttonWrapper()
    {
        echo '<div class="extra-downloadlink">';
    }

    /**
     * Print button wrapper end
     *
     * @return void
     */
    public static function buttonWrapperEnd()
    {
        echo '</div>';
    }

    /**
     * Show file block wrapper handlebars
     *
     * @param array $config Main settings
     * @param array $params Category params
     *
     * @return void
     */
    public static function fileBlockWrapperHandlebars($config, $params)
    {
        $name = self::$themeName;
        $selectFileInput = '';
        if ((int) $config['download_selected'] === 1 && wpfd_can_download_files()) {
            $selectFileInput = '<label class="wpfd_checkbox"><input class="cbox_file_download" type="checkbox" data-id="{{ID}}" /><span></span></label>';
        }
        $downloadlink = '#';
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'download_popup', 1) === 0) {
            $downloadlink = '{{linkdownload}}';
        }
        $style = 'margin : ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'margintop', 10) . 'px ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginright', 10) . 'px ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginbottom', 10) . 'px ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginleft', 10) . 'px;';

        /**
         * Filter to change html and arguments of file content wrapper in handlebars template
         *
         * @param array Html array
         * @param array Global config
         * @param array Category config
         *
         * @return array
         */
        $args = apply_filters(
            'wpfd_' . $name . '_file_content_wrapper_handlebars',
            array(
                'html' => '<div class="file" style="' . $style . '">' . $selectFileInput . '<a class="wpfd-file-link" href="%link$s" data-category_id="%catid$s" data-id="%fileid$s">',
                'args' => array(
                    'link' => $downloadlink,
                    'catid' => '{{catid}}',
                    'fileid' => '{{ID}}'
                )
            ),
            $config,
            $params
        );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo self::render($args['html'], $args['args']);
    }

    /**
     * Show file block wrapper
     *
     * @param object $file   File object
     * @param array  $config Main settings
     * @param array  $params Category params
     *
     * @return void
     */
    public static function fileBlockWrapper($file, $config, $params)
    {
        $name = self::$themeName;
        $selectFileInput = '';
        if ((int) $config['download_selected'] === 1 && wpfd_can_download_files()) {
            $selectFileInput = '<label class="wpfd_checkbox"><input class="cbox_file_download" type="checkbox" data-id="' . esc_attr($file->ID) . '" /><span></span></label>';
        }
        $downloadlink = '#';
        if ((int) WpfdBase::loadValue($params, self::$prefix . 'download_popup', 1) === 0) {
            $downloadlink = $file->linkdownload;
        }
        /**
         * Filter to change html and arguments of file content wrapper
         *
         * @param array Html array
         * @param array Global config
         * @param array Category config
         *
         * @return array
         */
        $style = 'margin : ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'margintop', 10) . 'px ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginright', 10) . 'px ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginbottom', 10) . 'px ';
        $style .= WpfdBase::loadValue($params, self::$prefix . 'marginleft', 10) . 'px;';
        $args = apply_filters(
            'wpfd_' . $name . '_file_content_wrapper',
            array(
                'html' => '<div class="file" style="' . $style . '">' . $selectFileInput . '<a class="wpfd-file-link" href="%link$s" data-category_id="%catid$d" data-id="%fileid$s">',
                'args' => array(
                    'link' => $downloadlink,
                    'catid' => esc_attr($file->catid),
                    'fileid' => esc_attr($file->ID)
                )
            ),
            $config,
            $params
        );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo self::render($args['html'], $args['args']);
    }

    /**
     * Print close tag for link
     *
     * @return void
     */
    public static function linkClose()
    {
        echo '</a></div>';
    }

    /**
     * Show file block icon handlebars
     *
     * @param array $config Main settings
     * @param array $params Category params
     *
     * @return void
     */
    public static function showFileBlockIconHandlebars($config, $params)
    {
        echo '<div class="dropblock">';
        $iconSet = isset($config['icon_set']) && $config['icon_set'] !== 'default' ? ' wpfd-icon-set-' . $config['icon_set'] : '';
        if ($config['custom_icon']) {
            echo '{{#if file_custom_icon}}
                    <div class="icon-custom"><img src="{{file_custom_icon}}"></div>
                    {{else}}
                    <div class="ext ext-{{ext}}' . esc_attr($iconSet) . '"><span class="txt">{{ext}}</span></div>
                    {{/if}}';
        } else {
            echo '<div class="ext ext-{{ext}}' . esc_attr($iconSet) . '"><span class="txt">{{ext}}</span></div>';
        }
        echo '</div>';
    }

    /**
     * Show file block icon
     *
     * @param object $file   File object
     * @param array  $config Main settings
     * @param array  $params Category params
     *
     * @return void
     */
    public static function showFileBlockIcon($file, $config, $params)
    {
        echo '<div class="dropblock">';
        if ($config['custom_icon'] && $file->file_custom_icon) {
            echo sprintf(
                '<div class="icon-custom"><img src="%s"></div>',
                esc_url($file->file_custom_icon)
            );
        } else {
            $iconSet = (isset($config['icon_set']) && $config['icon_set'] !== 'default') ? ' wpfd-icon-set-' . esc_attr($config['icon_set']) : '';
            echo sprintf(
                '<div class="ext ext-%s%s"><span class="txt">%s</div>',
                esc_attr(strtolower($file->ext)),
                esc_attr($iconSet),
                esc_html($file->ext)
            );
        }
        echo '</div>';
    }

    /**
     * Show file block title handlebars
     *
     * @param array $config Main settings
     * @param array $params Category params
     *
     * @return void
     */
    public static function showFileBlockTitleHandlebars($config, $params)
    {
        echo '<div class="droptitle">{{{crop_title}}}</div>';
    }

    /**
     * Show file block title
     *
     * @param object $file   File object
     * @param array  $config Main settings
     * @param array  $params Category params
     *
     * @return void
     */
    public static function showFileBlockTitle($file, $config, $params)
    {
        echo '<div class="droptitle">' . esc_html($file->crop_title) . '</div>';
    }
}

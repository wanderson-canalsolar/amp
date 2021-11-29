<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0.3
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;

//-- No direct access
defined('ABSPATH') || die();

/**
 * Class WpfdThemeTree
 */
class WpfdThemeTree extends WpfdTheme
{

    /**
     * Theme name
     *
     * @var string
     */
    public $name = 'tree';

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
        $name = self::$themeName;
        $globalConfig      = get_option('_wpfd_global_config');
        // Theme Content Output
        add_action('wpfd_' . $name . '_before_theme_content', array(__CLASS__, 'outputContentWrapper'), 10, 1);
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showcategorytitle', 1) === 1) {
            add_action('wpfd_' . $name . '_before_theme_content', array(__CLASS__, 'outputContentHeader'), 20, 1);
        }
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

        // End Theme Content Output
        add_action('wpfd_' . $name . '_after_theme_content', array(__CLASS__, 'outputContentWrapperEnd'), 10, 1);

        /**
         * Action fire after template hooked
         *
         * @hookname wpfd_{$themeName}_after_template_hooks
         *
         * @ignore
         */
        do_action('wpfd_' . $name . '_after_template_hooks');
        $this->loadCustomHooks();
    }

    /**
     * Load custom hooks and filters
     *
     * @return void
     */
    public function loadCustomHooks()
    {
        $name = self::$themeName;
        add_filter('wpfd_' . $name . '_content_wrapper_input', array($this, 'hiddenInput'), 10, 2);
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
     * Print hidden input
     *
     * @param string $html  Html return
     * @param object $theme Current theme object
     *
     * @return string
     */
    public function hiddenInput($html, $theme)
    {
        return sprintf(
            '<input type="hidden" id="root_linkdownload_cat" value="%s"/>
                           <input type="hidden" id="root_countfile_cat" value="%s"/>
                           <input type="hidden" id="root_category_slug" value="%s"/>',
            esc_attr($theme->category->linkdownload_cat),
            esc_attr(count($theme->options['files'])),
            esc_attr($theme->category->slug)
        );
    }
}

<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0W
 */

use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\Application;

// No direct access.
defined('ABSPATH') || die();

if (!wpfd_can_manage_file()) {
    wp_die(esc_html__('You don\'t have permission to view this page', 'wpfd'));
}

wp_localize_script('wpfd-main', 'l10n', array(
    'Drag & Drop your Document here'                        => esc_html__('Drag & Drop your Document here', 'wpfd'),
    'Or use the button below'                          => esc_html__('Or use the button below', 'wpfd'),
    'Add remote file'                                  => esc_html__('Add remote file', 'wpfd'),
    'Allowed extensions'                               => esc_html__('Allowed extensions', 'wpfd'),
    'SEO URL'                                          => esc_html__('SEO URL', 'wpfd'),
    'Show files import'                                => esc_html__('Show files import', 'wpfd'),
    'Max upload file size (Mb)'                        => esc_html__('Max upload file size (Mb)', 'wpfd'),
    'Delete all files on uninstall'                    => esc_html__('Delete all files on uninstall', 'wpfd'),
    'Close categories'                                 => esc_html__('Close categories', 'wpfd'),
    'Theme per categories'                             => esc_html__('Theme per categories', 'wpfd'),
    'Default theme per category'                       => esc_html__('Default theme per category', 'wpfd'),
    'Date format'                                      => esc_html__('Date format', 'wpfd'),
    'Use viewer'                                       => esc_html__('Use viewer', 'wpfd'),
    'Extensions to open with viewer'                   => esc_html__('Extensions to open with viewer', 'wpfd'),
    'GA download tracking'                             => esc_html__('GA download tracking', 'wpfd'),
    'Single user restriction'                          => esc_html__('Single user restriction', 'wpfd'),
    'Use WYSIWYG editor'                               => esc_html__('Use WYSIWYG editor', 'wpfd'),
    'Load the plugin on frontend'                      => esc_html__('Load the plugin on frontend', 'wpfd'),
    'Category owner'                                   => esc_html__('Category owner', 'wpfd'),
    'Search page'                                      => esc_html__('Search page', 'wpfd'),
    'Plain text search'                                => esc_html__('Plain text search', 'wpfd'),
    'Are you sure'                                     => esc_html__('Are you sure', 'wpfd'),
    'Delete'                                           => esc_html__('Delete', 'wpfd'),
    'Edit'                                             => esc_html__('Edit', 'wpfd'),
    'Your browser does not support HTML5 file uploads' => esc_html__(
        'Your browser does not support HTML5 file uploads',
        'wpfd'
    ),
    'Too many files'                                   => esc_html__('Too many files', 'wpfd'),
    'is too large'                                     => esc_html__('is too large', 'wpfd'),
    'Only images are allowed'                          => esc_html__('Only images are allowed', 'wpfd'),
    'Do you want to delete'                            => esc_html__('Do you want to delete', 'wpfd'),
    'Select files'                                     => esc_html__('Select files', 'wpfd'),
    'Image parameters'                                 => esc_html__('Image parameters', 'wpfd'),
    'Cancel'                                           => esc_html__('Cancel', 'wpfd'),
    'Ok'                                               => esc_html__('Ok', 'wpfd'),
    'Confirm'                                          => esc_html__('Confirm', 'wpfd'),
    'Save'                                             => esc_html__('Save', 'wpfd'),
    'close_categories'                                 => WpfdBase::loadValue($this->globalConfig, 'close_categories', 0),
    'add_remote_file'                                  => WpfdBase::loadValue($this->globalConfig, 'add_remote_file', 0),
    'Are you sure restore file'                        => esc_html__('Are you sure you want to restore the file: ', 'wpfd'),
    'Are you sure remove version'                      => esc_html__('Are you sure you want to definitively remove this file version', 'wpfd'),
));

if (Utilities::getInput('caninsert', 'GET', 'bool')) {
    global $hook_suffix;
    _wp_admin_html_begin();
    do_action('admin_enqueue_scripts', $hook_suffix);
    do_action('admin_print_scripts-' . $hook_suffix);
    do_action('admin_print_scripts');
    if (is_plugin_active('polylang/polylang.php') && class_exists('Polylang')) {
        echo '<script type="text/javascript">
           var ajaxurl = "' . esc_url(admin_url('admin-ajax.php')) . '";
         </script>';
    }
}

$alone = '';
?>
<script type="text/javascript">
    wpfdajaxurl = "<?php echo wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keep this, if not it error on backend?>";
    // Fix conflict with WPML
    if (wpfdajaxurl.substr(-1, 1) !== '&') {
        wpfdajaxurl = wpfdajaxurl + '&';
    }
    dir = "<?php echo esc_url(Application::getInstance('Wpfd')->getBaseUrl()); ?>";
    <?php if (Utilities::getInput('caninsert', 'GET', 'bool')) : ?>
    gcaninsert = true;
        <?php $alone = 'wpfdalone wp-core-ui '; ?>
    <?php else : ?>
    gcaninsert = false;
    <?php endif; ?>
    if (typeof(addLoadEvent) === 'undefined') {
        addLoadEvent = function (func) {
            if (typeof jQuery !== "undefined") {
                jQuery(document).ready(func);
            }
            else if (typeof wpOnload !== 'function') {
                wpOnload = func;
            } else {
                var oldonload = wpOnload;
                wpOnload = function () {
                    oldonload();
                    func();
                }
            }
        };
    }
</script>
<?php if (Utilities::getInput('caninsert', 'GET', 'bool')) : ?>
    <style>
        html.wp-toolbar {
            padding-top: 0 !important
        }
    </style>
<?php endif; ?>
<div id="mybootstrap" class="<?php echo esc_attr($alone); ?>">
    <div id="wpfd-categories-col" class="wpfd-column">
        <?php
        if (wpfd_can_create_category()) { ?>
            <div id="newcategory" class="button-primary button-big dropdown">
                <a class="" href="">
                    <span class="dashicons dashicons-category"></span>
                    <?php esc_html_e('New category', 'wpfd'); ?>
                </a>
                <?php if (has_filter('wpfdAddon_check_cloud_exist', 'check_cloud_exist')) { ?>
                    <?php if (apply_filters('wpfdAddon_check_cloud_exist', false)) { ?>
                        <a class="dropdown-toggle" type="button" data-toggle="dropdown">
                            <span class="caret"></span>
                        </a>
                    <?php } ?>
                <?php } ?>
                <ul class="dropdown-menu">
                    <?php
                    /**
                     * Action fire for display Dropdown
                     *
                     * @internal
                     */
                    do_action('wpfd_addon_dropdown');
                    ?>
                    <?php
                    if (apply_filters('wpfdAddon_check_cloud_exist', false)) {
                        ?>
                </ul>
                    <?php } ?>
            </div>
        <?php } ?>

        <?php
        /**
         * Action to display sync button
         *
         * @internal
         */
        do_action('wpfd_addon_sync_buttons');

        ?>
        <!-- display button connect to cloud -->
        <div class="nested dd">
            <ol id="categorieslist" class="dd-list nav bs-docs-sidenav2 ">
                <?php $content = '';
                if (!empty($this->categories)) {
                    $previouslevel = 1;
                    // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                    $categories = is_countable($this->categories) ? count($this->categories) : 0;
                    for ($index = 0; $index < $categories; $index++) {
                        if ($index + 1 !== $categories) {
                            $nextlevel = (int) $this->categories[$index + 1]->level;
                        } else {
                            $nextlevel = 0;
                        }
                        $content .= openItem($this->categories[$index], $index, $this->globalConfig);
                        if ($nextlevel > $this->categories[$index]->level) {
                            $content .= openlist();
                        } elseif ($nextlevel === (int) $this->categories[$index]->level) {
                            $content .= closeItem();
                        } else {
                            $c       = '';
                            $c       .= closeItem();
                            $c       .= closeList();
                            $content .= str_repeat($c, $this->categories[$index]->level - $nextlevel);
                        }
                        $previouslevel = (int) $this->categories[$index]->level;
                    }
                }
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside functions
                echo $content;
                ?>
            </ol>
            <input type="hidden" id="categoryToken" name=""/>
        </div>
    </div>

    <div id="pwrapper" class="wpfd-column">
        <div id="wpreview">
            <div class="wpfd-toolbar-wrapper">
                <div class="wpfd-btn-toolbar" id="wpfd-toolbar">
                    <div class="btn-wrapper">
                        <button onclick="Wpfd.submitbutton('files.movefile')" class="btn btn-small" id="wpfd-cut">
                            <span class="icon-random"></span>
                            <?php esc_html_e('Cut', 'wpfd'); ?></button>
                    </div>
                    <div class="btn-wrapper">
                        <button onclick="Wpfd.submitbutton('files.copyfile')" class="btn btn-small" id="wpfd-copy">
                            <span class="icon-book"></span>
                            <?php esc_html_e('Copy', 'wpfd'); ?></button>
                    </div>
                    <div class="btn-wrapper">
                        <button onclick="Wpfd.submitbutton('files.paste')" class="btn btn-small" id="wpfd-paste">
                            <span class="icon-inbox"></span>
                            <?php esc_html_e('Paste', 'wpfd'); ?></button>
                    </div>
                    <div class="btn-wrapper">
                        <button onclick="Wpfd.submitbutton('files.delete')" class="btn btn-small" id="wpfd-delete">
                            <span class="icon-trash"></span>
                            <?php esc_html_e('Delete', 'wpfd'); ?></button>
                    </div>
                    <div class="btn-wrapper">
                        <button onclick="Wpfd.submitbutton('files.download')" class="btn btn-small" id="wpfd-download">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e('Download', 'wpfd'); ?></button>
                    </div>
                    <div class="btn-wrapper">
                        <button onclick="Wpfd.submitbutton('files.uncheck')" class="btn btn-small" id="wpfd-uncheck">
                            <span class="icon-remove"></span>
                            <?php esc_html_e('Uncheck', 'wpfd'); ?></button>
                    </div>
                </div>
                <div class="wpfd-filter-file">
                <div class="wpfd-search-file hide">
                    <select title="" id="wpfd_filter_catid" class="chzn-select" name="catid">
                        <option value=""><?php echo ' ' . esc_html__('All categories', 'wpfd'); ?></option>
                        <?php
                        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                        if (is_countable($this->categories) && count($this->categories) > 0) {
                            foreach ($this->categories as $key => $category) {
                                $echo = '<option  value="' . esc_attr($category->term_id) . '">';
                                $echo .= str_repeat('-', $category->level) . ' ' . esc_html($category->name) . '</option>';
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above
                                echo $echo;
                            }
                        }
                        ?>

                    </select>
                    <input title="" type="text" class="wpfd-search-file-input">
                    <a href="#"
                       class="button button-primary wpfd-btn-search"><?php esc_html_e('Search files', 'wpfd') ?></a>
                    <a href="#" class="button wpfd-btn-exit-search"><?php esc_html_e('Exit search', 'wpfd') ?></a>
                </div>

                <i class="material-icons wpfd-iconsearch restablesearch">search</i>
            </div>
            </div>
            <?php
            $class = (wpfd_can_edit_category() || wpfd_can_edit_own_category()) ? 'has-wpfd' : 'no-wpfd';
            ?>
            <div id="preview"
                 class="<?php echo esc_attr($class); ?>">
            </div>
        </div>
        <input type="hidden" name="id_category" value=""/>
    </div>
    <?php if (wpfd_can_edit_category() || wpfd_can_edit_own_category() || Utilities::getInput('caninsert', 'GET', 'bool')) { ?>
        <div id="rightcol" class="wpfd-column">
            <?php if (Utilities::getInput('caninsert', 'GET', 'bool')) : ?>
                <a id="insertcategory" class="button button-primary button-big" href="#"
                   onclick="if (window.parent) insertCategory();"><?php esc_html_e('Insert this category', 'wpfd'); ?></a>
                <a id="insertfile" class="button button-primary button-big" style="display: none;" href="#"
                   onclick="if (window.parent) insertFile();"><?php esc_html_e('Insert this file', 'wpfd'); ?></a>
            <?php endif; ?>
            <?php if (wpfd_can_edit_category() || wpfd_can_edit_own_category()) { ?>
                <div class="categoryblock">
                    <div class="well">
                        <h4><?php esc_html_e('Parameters', 'wpfd'); ?></h4>
                        <div id="galleryparams">
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="fileblock" style="display: none;">
                <div class="well">
                    <h4><?php esc_html_e('Parameters', 'wpfd'); ?></h4>
                    <div id="fileparams">
                    </div>
                </div>
                <div id="fileversion">
                    <div class="well">
                        <h4><?php esc_html_e('Send a new version', 'wpfd'); ?></h4>
                        <div id="versions_content"></div>
                        <div id="dropbox_version">
                            <div class="upload">
                                <span class="message"><?php esc_html_e('Drag & Drop your Document here', 'wpfd'); ?></span>
                                <input class="hide" type="file" id="upload_input_version">
                                <span id="upload_button_version" class="button button-primary button-big">
                                        <?php esc_html_e('Select files', 'wpfd'); ?>
                                    </span>
                            </div>
                            <div class="progress progress-striped active hide">
                                <div class="bar" style="width: 0;"></div>
                            </div>
                        </div>
                        <div class="clr"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<?php
/**
 * Open Item
 *
 * @param object  $category     Category
 * @param integer $key          Key
 * @param array   $globalConfig Config
 *
 * @return string
 */
function openItem($category, $key, $globalConfig)
{
    $iconsCat = '';
    if (has_filter('wpfdAddonShowCategoryCloud', 'displayCategoriesGoogleCloud')) {
        $iconsCat .= apply_filters('wpfdAddonShowCategoryCloud', $category->term_id);
    }
    if (has_filter('wpfdAddonShowCategoryDropbox', 'displayCategoriesDropbox')) {
        $iconsCat .= apply_filters('wpfdAddonShowCategoryDropbox', $category->term_id);
    }
    if (has_filter('wpfdAddonShowCategoryOneDrive', 'displayCategoriesOneDrive')) {
        $iconsCat .= apply_filters('wpfdAddonShowCategoryOneDrive', $category->term_id);
    }

    if ($iconsCat === '') {
        $iconsCat = '<i class="material-icons wpfd-folder">folder</i>';
    }
    if (isset($category->disable) && $category->disable) {
        $item_id_disable = 'data-item-disable="' . esc_attr($category->term_id) . '"';
        $dd_handle       = '';
        $category_count  = '';
        $disable         = ' disabled ';
    } else {
        $disable         = ' not_disable ';
        $item_id_disable = '';
        $dd_handle       = ' dd-handle ';
        $category_count  = '(' . $category->count . ')';
    }
    $item = '<li class="' . $disable . ' dd-item dd3-item ' . ($key ? '' : 'active');
    $item .= '" data-id="' . $category->term_id . '" data-id-category="';
    $item .= $category->term_id . '"  ' . $item_id_disable . ' >
        <div class="' . $disable . $dd_handle . ' dd3-handle">' . $iconsCat . '</div>';
    $item .= '<div class="dd-content dd3-content' . $disable . '">';
    if (wpfd_can_edit_category() || wpfd_can_edit_own_category()) {
        $item .= '<a class="edit' . $disable . '"' . $disable . '><i class="icon-edit"></i></a>';
    }
    if (wpfd_can_delete_category() || wpfd_can_edit_own_category()) {
        $item .= '<a class="trash' . $disable . '"' . $disable . '><i class="icon-trash"></i></a>';
    }
    if ((int) WpfdBase::loadValue($globalConfig, 'file_count', 0) !== 0) {
        $item .= '<span class="countfile">' . esc_html($category_count) . '</span>';
    }
    $item .= '<a href="" title="' . esc_html($category->name) . '" class="t' . $disable . '"' . $disable . '>';
    $item .= '<span class="title">' . esc_html($category->name);
    $item .= '</span> </a> </div>';

    return $item;
}

/**
 * Close Item
 *
 * @return string
 */
function closeItem()
{
    return '</li>';
}

/**
 * Content Item
 *
 * @param object $category Category
 *
 * @return string
 */
function itemContent($category)
{
    if (isset($category->disable) && $category->disable) {
        $disable   = ' disabled ';
        $dd_handle = '';
    } else {
        $disable   = '';
        $dd_handle = ' dd-handle ';
    }
    $item = '<div class="' . $disable . $dd_handle . ' dd3-handle">
                <i class="material-icons wpfd-folder">folder</i>
             </div>
             <div class="dd-content dd3-content"
             <i class="icon-chevron-right"></i>';
    if (wpfd_can_edit_category() || wpfd_can_edit_own_category()) {
        $item .= '<a class="edit"><i class="icon-edit"></i></a>';
    }
    $item .= '<a href="" class="t"> <span class="title">' . esc_html($category->name) . '</span> </a>
            </div>';

    return $item;
}

/**
 * Open List
 *
 * @return string
 */
function openlist()
{
    return '<ol class="dd-list">';
}

/**
 * Close List
 *
 * @return string
 */
function closelist()
{
    return '</ol>';
}

?>

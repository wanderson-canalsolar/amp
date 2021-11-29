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
?>

<?php
/**
 * Action before theme content
 *
 * @param object Current theme params
 *
 * @hookname wpfd_{$themeName}_before_theme_content
 *
 * @hooked themeOutputContentWrapper - 10 (outputs opening divs for the content)
 *
 * @ignore
 */
do_action('wpfd_' . $name . '_before_theme_content', $this);
?>
<?php if ($showsubcategories) : ?>
    <script type="text/x-handlebars-template" class="wpfd-template-categories">
        <?php
        /**
         * Action before files loop in handlebars template
         *
         * @param array Current theme params
         * @param array Category config
         *
         * @hookname wpfd_{$themeName}_before_files_loop_handlebars
         *
         * @hooked outputCategoriesWrapper - 10 (outputs opening divs for the categories)
         * @hooked showCategoryTitleHandlebars - 20
         * @hooked showCategoriesHandlebars - 30
         * @hooked outputCategoriesWrapperEnd - 90 (outputs closing divs for the categories)
         *
         * @ignore
         */
        do_action('wpfd_' . $name . '_before_files_loop_handlebars', $this, $params);
        ?>
    </script>
<?php endif; ?>
    <script type="text/x-handlebars-template" class="wpfd-template-files">
        {{#if files}}
        <div class="wpfd_list">
            {{#each files}}
            <div class="file" style="<?php echo esc_html($padding); ?>" data-id="{{ID}}" data-catid="{{catid}}">
                <div class="filecontent">
                    <?php
                    /**
                     * Action to show file content in handlebars template
                     *
                     * @param array Main config
                     * @param array Category config
                     *
                     * @hookname wpfd_{$themeName}_file_content_handlebars
                     *
                     * @hooked: showIconHandlebars - 10
                     * @hooked: showTitleHandlebars - 20
                     *
                     * @ignore
                     */
                    do_action('wpfd_' . $name . '_file_content_handlebars', $config, $params);
                    ?>
                    <div class="file-xinfo">
                        <?php
                        /**
                         * Action to show file info in handlebars template
                         *
                         * @param array Main config
                         * @param array Category config
                         *
                         * @hookname wpfd_{$themeName}_file_info_handlebars
                         *
                         * @hooked showDescriptionHandlebars - 10
                         * @hooked showVersionHandlebars - 20
                         * @hooked showSizeHandlebars - 30
                         * @hooked showHitsHandlebars - 40
                         * @hooked showCreatedHandlebars - 50
                         * @hooked showModifiedHandlebars - 60
                         *
                         * @ignore
                         */
                        do_action('wpfd_' . $name . '_file_info_handlebars', $config, $params);
                        ?>
                    </div>
                </div>
                <span class="file-right">
                    <?php
                    /**
                     * Action to show buttons in handlebars template
                     *
                     * @param array Main config
                     * @param array Category config
                     *
                     * @hookname wpfd_{$themeName}_buttons_handlebars
                     *
                     * @hooked showDownloadHandlebars - 10
                     * @hooked showPreviewHandlebars - 20
                     *
                     * @ignore
                     */
                    do_action('wpfd_' . $name . '_buttons_handlebars', $config, $params);
                    ?>
                </span>
            </div>
            {{/each}}
            <div class="file flex_span" style="<?php echo esc_html($padding); ?>"></div>
        </div>
        {{/if}}
    </script>
<div class="wpfd-container">
    <?php
    /**
     * Action to show folder tree
     *
     * @param object Current theme params
     * @param array Category config
     *
     * @hookname wpfd_{$themeName}_folder_tree
     *
     * @hooked showTree - 10
     *
     * @ignore
     */
    do_action('wpfd_' . $name . '_folder_tree', $this, $params);
    ?>
    <div class="wpfd-open-tree"></div>
    <div class="wpfd-container-<?php echo esc_html($name); ?> <?php echo esc_attr($showfoldertree ? ' with_foldertree' : ''); ?>">
        <?php
        /**
         * Action before files loop
         *
         * @param object Current theme params
         * @param array  Category config
         *
         * @hookname wpfd_{$themeName}_before_files_loop
         *
         * @hooked outputCategoriesWrapper - 10 (outputs opening divs for the categories)
         * @hooked showCategoryTitle - 20
         * @hooked showCategories - 30
         * @hooked outputCategoriesWrapperEnd - 90 (outputs closing divs for the categories)
         *
         * @ignore
         */
        do_action('wpfd_' . $name . '_before_files_loop', $this, $params);
        ?>
        <?php if (!empty($files)) : ?>
            <div class="wpfd_list">
                <?php foreach ($files as $file) : ?>
                    <div class="file" style="<?php echo esc_html($padding); ?>"
                         data-id="<?php echo esc_attr($file->ID); ?>"
                         data-catid="<?php echo esc_attr($file->catid); ?>">
                        <div class="filecontent">
                            <?php
                            /**
                             * Action to show file content
                             *
                             * @param object Current file object
                             * @param array  Global config
                             * @param array  Category config
                             *
                             * @hooked: showIcon - 10
                             * @hooked: showTitle - 20
                             *
                             * @hookname wpfd_{$themeName}_file_content
                             *
                             * @ignore
                             */
                            do_action('wpfd_' . $name . '_file_content', $file, $config, $params);
                            ?>
                            <div class="file-xinfo">
                                <?php
                                /**
                                 * Action to show file info
                                 *
                                 * @param object Current file object
                                 * @param array  Category config
                                 *
                                 * @hookname wpfd_{$themeName}_file_info
                                 *
                                 * @hooked showDescription - 10
                                 * @hooked showVersion - 20
                                 * @hooked showSize - 30
                                 * @hooked showHits - 40
                                 * @hooked showCreated - 50
                                 * @hooked showModified - 60
                                 *
                                 * @ignore
                                 */
                                do_action('wpfd_' . $name . '_file_info', $file, $config, $params);
                                ?>
                            </div>
                        </div>
                        <div class="file-right">
                            <?php
                            /**
                             * Action to show buttons
                             *
                             * @param object Current file object
                             * @param array  Global config
                             * @param array  Category config
                             *
                             * @hookname wpfd_{$themeName}_buttons
                             *
                             * @hooked showDownload - 10
                             * @hooked showPreview - 20
                             *
                             * @ignore
                             */
                            do_action('wpfd_' . $name . '_buttons', $file, $config, $params);
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="file flex_span" style="<?php echo esc_html($padding); ?>"></div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
/**
 * Action after theme content
 *
 * @param object Current theme instance
 * @param array  Category config
 *
 * @hookname wpfd_{$themeName}_after_theme_content
 *
 * @hooked outputContentWrapperEnd - 10 (outputs closing divs for the content)
 *
 * @ignore
 */
do_action('wpfd_' . $name . '_after_theme_content', $this, $params);

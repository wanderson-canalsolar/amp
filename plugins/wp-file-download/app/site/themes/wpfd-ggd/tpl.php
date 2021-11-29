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

<script type="text/x-handlebars-template" id="wpfd-template-<?php echo esc_html($name) ?>-box">
    {{#with file}}
    <div class="dropblock wpfd-content">
        <a href="#" class="wpfd-close"></a>
        <div class="filecontent">
            <?php
            /**
             * Action to show file content in handlebars template
             *
             * @param array $config Main config
             * @param array $params Category config
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
            <div class="wpfd-extra">
                <?php
                /**
                 * Action to show file info in handlebars template
                 *
                 * @param array Global config
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
    </div>
    {{/with}}
</script>
<?php
/**
 * Action to show before theme content
 *
 * @param object Current theme params
 *
 * @hookname wpfd_{$themeName}_before_theme_content
 *
 * @hooked outputContentWrapper - 10 (outputs opening divs for the content)
 * @hooked outputContentHeader - 20 (breadcrumbs and category name)
 *
 * @ignore
 */
do_action('wpfd_' . $name . '_before_theme_content', $this);
?>

<script type="text/x-handlebars-template" class="wpfd-template-categories">
    <?php
    /**
     * Action fire before file loop in handlebars template
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

<script type="text/x-handlebars-template" class="wpfd-template-files">
    {{#if files}}
    <div class="wpfd_list">
        {{#each files}}
        <?php
        /**
         * Action to show file block in handlebars template
         *
         * @param array Main config
         * @param array Category config
         *
         * @hookname wpfd_{$themeName}_file_block_handlebars
         *
         * @hooked: fileBlockWrapperHandlebars - 10 (outputs opening a for the file)
         * @hooked: showFileBlockIconHandlebars - 20
         * @hooked: showFileBlockTitleHandlebars - 30
         * @hooked: linkClose - 90 (outputs closing a for the file)
         */
        do_action('wpfd_' . $name . '_file_block_handlebars', $config, $params);
        ?>
        {{/each}}
    </div>
    {{/if}}
</script>
<div class="wpfd-container">
    <?php
    /**
     * Action to show folder tree
     *
     * @param object Current theme params
     * @param array  Category config
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
    <div class="wpfd-col wpfd-container-<?php echo esc_html($name); ?> <?php echo esc_attr($showfoldertree ? ' with_foldertree' : ''); ?>">
            <?php
            /**
             * Action to show before file loop
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
            <?php if (count($files)) : ?>
                <div class="wpfd_list">
                    <?php foreach ($files as $file) : ?>
                        <?php
                        /**
                         * Action to show file block
                         *
                         * @param object Current file object
                         * @param array  Global config
                         * @param array  Category config
                         *
                         * @hookname wpfd_{$themeName}_file_block
                         *
                         * @hooked: fileBlockWrapper - 10 (outputs opening a for the file)
                         * @hooked: showFileBlockIcon - 20
                         * @hooked: showFileBlockTitle - 30
                         * @hooked: linkClose - 90 (outputs closing a for the file)
                         */
                        do_action('wpfd_' . $name . '_file_block', $file, $config, $params);
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
</div>
<?php
/**
 * Action to show after theme content
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
?>

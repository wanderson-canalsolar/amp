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
?>
<script type="text/x-handlebars-template"
        id="wpfd-template-<?php echo esc_html($name); ?>-categories-<?php echo esc_attr($category->term_id); ?>">
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

<script type="text/x-handlebars-template" id="wpfd-template-<?php echo esc_html($name); ?>-<?php echo esc_attr($category->term_id); ?>">
    {{#if files}}
    {{#each files}}
    <tr class="file" data-id="{{ID}}" data-catid="{{catid}}">
    <?php
    /**
     * Action to show file info in handlebars template
     *
     * @param array Main config
     * @param array Category config
     *
     * @hookname wpfd_{$themeName}_file_info_handlebars
     *
     * @hooked showTitleHandlebars - 5
     * @hooked showDescriptionHandlebars - 10
     * @hooked showVersionHandlebars - 20
     * @hooked showSizeHandlebars - 30
     * @hooked showHitsHandlebars - 40
     * @hooked showCreatedHandlebars - 50
     * @hooked showModifiedHandlebars - 60
     *
     * @ignore Hook already documented
     */
    do_action('wpfd_' . $name . '_file_info_handlebars', $config, $params);
    ?>

        <?php if ((int) WpfdBase::loadValue($params, $name . '_showdownload', 1) === 1 ||
                $this->config['use_google_viewer'] !== 'no') : ?>
            <td class="col-download">
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
                 * @ignore Hook already documented
                 */
                do_action('wpfd_' . $name . '_buttons_handlebars', $config, $params);
                ?>
            </td>
        <?php endif; ?>

    </tr>
    {{/each}}
    {{/if}}


</script>
<?php
/**
 * Action before theme content
 *
 * @param object Current theme params
 * @param array  Category config
 *
 * @hookname wpfd_{$themeName}_before_theme_content
 *
 * @hooked themeOutputContentWrapper - 10 (outputs opening divs for the content)
 *
 * @ignore Hook already documented
 */
do_action('wpfd_' . $name . '_before_theme_content', $this);
?>
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
    <div class="wpfd-container-<?php echo esc_html($name); ?> <?php echo esc_attr($showfoldertree ? ' with_foldertree' : ''); ?>">
        <?php
        /**
         * Action to show before files loop
         *
         * @param object $this   Current theme params
         * @param array $params Category config
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

        <table class="<?php echo esc_attr($this->additionalClass); ?> mediaTable">

            <thead>
            <tr>
                <?php
                /**
                 * Action to show columns
                 *
                 * @param array Main config
                 * @param array Category config
                 *
                 * @hookname wpfd_{$themeName}_columns
                 *
                 * @hooked thTitle - 10
                 * @hooked thDescription - 20
                 * @hooked thVersion - 30
                 * @hooked thSize - 40
                 * @hooked thHits - 50
                 * @hooked thCreated - 60
                 * @hooked thModified - 70
                 * @hooked thDownload - 80
                 */
                do_action('wpfd_' . $name . '_columns', $config, $params);
                ?>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($files as $file) : ?>
                <tr class="file" data-id="<?php echo esc_attr($file->ID); ?>"
                    data-catid="<?php echo esc_attr($file->catid); ?>">
                    <?php
                    /**
                     * Action to show file info
                     *
                     * @param object Current file object
                     * @param array  Main config
                     * @param array  Category config
                     *
                     * @hookname wpfd_{$themeName}_file_info
                     *
                     * @hooked showTitle - 5
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

                    <?php if ((int) WpfdBase::loadValue($params, $name . 'showdownload', 1) === 1) : ?>
                        <td class="col-download">
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
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
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


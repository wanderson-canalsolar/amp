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
<script type="text/x-handlebars-template" id="wpfd-template-tree-box">
    {{#with file}}
    <div class="dropblock">
        <a href="javascript:void(null)" class="wpfd-close"></a>
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
             * @ignore Hook already documented
             */
            do_action('wpfd_' . $name . '_file_content_handlebars', $config, $params);
            ?>

            <div class="wpfd-extra">
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
                 * @ignore Hook already documented
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
         * @ignore Hook already documented
         */
        do_action('wpfd_' . $name . '_buttons_handlebars', $config, $params);
        ?>
    {{/with}}
</script>

<?php if ((int) WpfdBase::loadValue($params, 'tree_showsubcategories', 1) === 1) : ?>
    <script type="text/x-handlebars-template" id="wpfd-template-tree-categories">
        {{#if categories}}
        {{#each categories}}
        <li class="directory collapsed">
            <a class="catlink" href="#" data-idcat="{{term_id}}">
                <div class="icon-open-close" data-id="{{term_id}}"></div>
                <i class="zmdi zmdi-folder wpfd-folder"></i>
                <span>{{name}}</span>
            </a>
        </li>
        {{/each}}
        {{/if}}
    </script>
<?php endif; ?>

<script type="text/x-handlebars-template" id="wpfd-template-tree-files">
    {{#if files}}
    {{#each files}}
    <li class="ext {{ext}}">
        <?php
        if ((int) $config['download_selected'] === 1 && wpfd_can_download_files()) {
            echo '<label class="wpfd_checkbox"><input class="cbox_file_download" type="checkbox" data-id="{{ID}}" /><span></span></label>';
        }
        $iconSet = isset($config['icon_set']) && $config['icon_set'] !== 'default' ? ' wpfd-icon-set-' . $config['icon_set'] : '';
        if ($this->config['custom_icon']) : ?>
            {{#if file_custom_icon}}
            <span class="wpfd-file ext icon-custom"><img src="{{file_custom_icon}}"></span>
            {{else}}
            <i class="wpfd-file ext ext-{{ext}}<?php echo esc_attr($iconSet); ?>"></i>
            {{/if}}
        <?php else : ?>
            <i class="wpfd-file ext ext-{{ext}}<?php echo esc_attr($iconSet); ?>"></i>
        <?php endif; ?>

        <a class="wpfd-file-link" data-category_id="{{catid}}" href="<?php $atthref = '#';
        if ((int) WpfdBase::loadValue($params, 'tree_download_popup', 1) === 0) {
            $atthref = '{{linkdownload}}';
        }
        echo esc_html($atthref); ?>" data-id="{{ID}}"
           title="{{post_title}}">{{{crop_title}}}</a>
    </li>
    {{/each}}
    </div>
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
 * @hooked outputContentWrapper - 10 (outputs opening divs for the content)
 * @hooked outputContentHeader - 20 (breadcrumbs and category name)
 *
 * @ignore Hook already documented
 */
do_action('wpfd_' . $name . '_before_theme_content', $this, $params);
?>
<ul>
    <?php if (count($categories) &&
              (int) WpfdBase::loadValue($params, $name . '_showsubcategories', 1) === 1) : ?>
        <?php foreach ($categories as $category) : ?>
            <li class="directory collapsed">
                <a class="catlink" href="#" data-idcat="<?php echo esc_attr($category->term_id); ?>">
                    <div class="icon-open-close" data-id="<?php echo esc_attr($category->term_id); ?>"></div>
                    <i class="zmdi zmdi-folder wpfd-folder"></i>
                    <span><?php echo esc_html($category->name); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (is_array($files) && count($files)) :
        $iconSet = isset($config['icon_set']) && $config['icon_set'] !== 'default' ? ' wpfd-icon-set-' . $config['icon_set'] : '';
        foreach ($files as $file) : ?>
            <li class="ext <?php echo esc_attr(strtolower($file->ext)); ?>">
                <?php
                if ((int) $config['download_selected'] === 1 && wpfd_can_download_files()) {
                    echo '<label class="wpfd_checkbox"><input class="cbox_file_download" type="checkbox" data-id="' . esc_attr($file->ID) . '" /><span></span></label>';
                }
                if ($this->config['custom_icon'] && $file->file_custom_icon) : ?>
                    <i class="wpfd-file"><img src="<?php echo esc_url($file->file_custom_icon); ?>"></i>
                <?php else : ?>
                    <i class="wpfd-file ext ext-<?php echo esc_attr(strtolower($file->ext)) . esc_attr($iconSet); ?>"></i>
                <?php endif; ?>
                <a class="wpfd-file-link" href="<?php $atthref = '#';
                if ((int) WpfdBase::loadValue($params, $name . '_download_popup', 1) === 0) {
                    $atthref = $file->linkdownload;
                }
                echo esc_url($atthref); ?>" data-category_id="<?php echo esc_attr($file->catid); ?>"
                   data-id="<?php echo esc_attr($file->ID); ?>"
                   title="<?php echo esc_attr($file->post_title); ?>"><?php echo esc_html($file->crop_title); ?></a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>
<?php
/**
 * Action before theme content
 *
 * @param object Current theme params
 *
 * @hookname wpfd_{$themeName}_before_theme_content
 *
 * @hooked outputContentWrapperEnd - 10 (outputs closing divs for the content)
 *
 * @ignore Hook already documented
 */
do_action('wpfd_' . $name . '_after_theme_content', $this, $params);
?>

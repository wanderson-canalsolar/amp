<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

// No direct access.
defined('ABSPATH') || die();
?>
<div class="wpfd_alt_wrapper">
    <div class="wpfd_categories_container">
        <div class="wpfd_cat wpfd_new_category wpfd_cat_<?php echo esc_attr($this->category_type); ?>" data-category-type="<?php echo esc_attr($this->category_type); ?>"><?php esc_html_e('Create folder', 'wpfd'); ?></div>
        <?php if (is_array($this->categories) || is_object($this->categories)) :
            foreach ($this->categories as $category) : ?>
                <div class="wpfd_cat wpfd_cat_<?php echo esc_attr($category->type); ?>" data-category-type="<?php echo esc_attr($category->type); ?>" data-category-id="<?php echo esc_attr($category->term_id); ?>">
                <?php echo esc_html($category->name); ?>
                </div>
            <?php endforeach; ?>
            <?php // Span for same category width on flex-grow ?>
            <div class="flex-span"></div><div class="flex-span"></div><div class="flex-span"></div><div class="flex-span"></div>
        <?php endif; ?>
    </div>
    <?php if (is_array($this->files) || is_object($this->files)) : ?>
    <div class="wpfd_files_container">
            <?php foreach ($this->files as $file) :
                $httpcheck = isset($file->guid) ? $file->guid : '';
                $classes = preg_match('(http://|https://)', $httpcheck) ? ' is-remote-url' : '';
                /**
                 * Check if file has linked to a product
                 *
                 * @param WP_Post
                 *
                 * @internal
                 */
                $classes .= apply_filters('wpfd_addon_has_products', false, $file) ? ' isWoocommerce' : '';

                $data = array(
                    'id-file'      => esc_attr($file->ID),
                    'catid-file'   => esc_attr($file->catid),
                    'linkdownload' => esc_url($file->linkdownload),
                );

                /**
                 * Init data for file row
                 *
                 * @param array
                 * @param WP_Post
                 *
                 * @internal
                 */
                $data = apply_filters('wpfd_admin_file_row_data', $data, $file);

                $dataHtml = '';
                foreach ($data as $key => $dataRow) {
                    $dataHtml .= 'data-' . esc_attr($key) . '="' . $dataRow . '" ';
                }
                $dataHtml = rtrim($dataHtml);
                ?>
            <div class="file<?php echo esc_attr($classes); ?>" <?php echo $dataHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above ?>>
                <div class="<?php echo ($this->iconSet !== 'default') ? 'wpfd-icon-set-' . esc_attr($this->iconSet) : ''; ?> ext ext-<?php echo esc_attr($file->ext); ?>"><span class="txt"><?php echo esc_html($file->ext); ?></span></div>
                <div class="file_info">
                    <h3 class="file_title title"><?php echo esc_html($file->post_title); ?></h3>
                    <span class="file_size">
                        <strong><?php esc_html_e('Size', 'wpfd'); ?>:</strong>&nbsp;<?php echo esc_html((strtolower($file->size) === 'n/a' || $file->size <= 0) ? 'N/A' : WpfdHelperFiles::bytesToSize($file->size)); ?>
                    </span>
                    <span class="file_created">
                        <strong><?php esc_html_e('Date added', 'wpfd'); ?>:</strong>&nbsp;<?php echo esc_html($file->created); ?>
                    </span>
                    <span class="file_modified">
                        <strong><?php esc_html_e('Modified', 'wpfd'); ?>:</strong>&nbsp;<?php echo esc_html($file->modified); ?>
                    </span>
                    <span class="file_version">
                        <strong><?php esc_html_e('Version', 'wpfd'); ?>:</strong>&nbsp;<?php echo esc_html((isset($file->versionNumber)) ? $file->versionNumber : ''); ?>
                    </span>
                    <span class="file_hits">
                        <strong><?php esc_html_e('Hits', 'wpfd'); ?>:</strong>&nbsp;<?php echo esc_html($file->hits) . ' ' . esc_html__('hits', 'wpfd'); ?>
                    </span>
                    <div class="ju-button orange-outline-button alt-download-button">
                        <i class="material-icons">cloud_download</i> <?php esc_html_e('Download', 'wpfd'); ?>
                    </div>
                </div>
                <div class="file_toolbox">
                    <ul class="file_menu">
                        <li data-action="files.movefile"><i class="wpfd-svg-icon-cut"></i> <?php esc_html_e('Cut', 'wpfd'); ?></li>
                        <li data-action="files.copyfile"><i class="wpfd-svg-icon-copy"></i> <?php esc_html_e('Copy', 'wpfd'); ?></li>
                        <li data-action="files.paste"><i class="wpfd-svg-icon-paste"></i> <?php esc_html_e('Paste', 'wpfd'); ?></li>
                        <li data-action="files.delete"><i class="wpfd-svg-icon-trash"></i> <?php esc_html_e('Delete', 'wpfd'); ?></li>
                        <li data-action="files.download"><i class="wpfd-svg-icon-download"></i> <?php esc_html_e('Download', 'wpfd'); ?></li>
                        <li data-action="files.selectall"><i class="wpfd-svg-icon-check-all"></i> <?php esc_html_e('Check all', 'wpfd'); ?></li>
                        <li data-action="files.uncheck"><i class="wpfd-svg-icon-remove"></i> <?php esc_html_e('Uncheck', 'wpfd'); ?></li>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

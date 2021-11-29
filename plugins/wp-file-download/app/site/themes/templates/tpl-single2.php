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
$download_attributes = apply_filters('wpfd_download_data_attributes_handlebars', '');
$config = get_option('_wpfd_global_config');

?>
<div class="wpfd-single-file">
    {{#if settings.icon}}
    <div class="wpfd-single-file--icon">
        {{#xunless settings.link_on_icon 'none'}}
            <a href="{{#xif settings.link_on_icon 'preview'}}{{#if file.openpdflink}}{{file.openpdflink}}{{else}}{{file.viewerlink}}{{/if}}{{/xif}}{{#xif settings.link_on_icon 'download'}}{{file.linkdownload}}{{/xif}}" alt="{{file.crop_title}}" class="{{#xif settings.link_on_icon 'preview'}}wpfdlightbox{{else}}noLightbox{{/xif}}">
        {{/xunless}}
            <div class="wpfd-icon-placeholder" style="{{file.icon_style}}"></div>
        {{#xunless settings.link_on_icon 'none'}}</a>{{/xunless}}
    </div>
    {{/if}}

    <div class="wpfd-single-file--details wpfd-file-content">
        {{#if settings.file_title}}
            {{#if file.crop_title}}
                <{{settings.title_wrapper_tag}} class="wpfd-file-content--title">{{{file.crop_title}}}</{{settings.title_wrapper_tag}}>
            {{/if}}
        {{/if}}
        {{#if settings.file_description}}
            {{#if file.description}}
                <p class="wpfd-file-content--description">
                    {{{file.description}}}
                </p>
            {{/if}}
        {{/if}}
        {{#if settings.file_information}}
        <div class="wpfd-file-content--meta">
            {{#if settings.file_size}}
                {{#if file.size}}
                    <div><?php esc_html_e('File size', 'wpfd'); ?>: {{file.size}}</div>
                {{/if}}
            {{/if}}
            {{#if settings.file_created_date}}
                {{#if file.created}}
                    <div><?php esc_html_e('Created', 'wpfd'); ?>: {{file.created}}</div>
                {{/if}}
            {{/if}}
            {{#if settings.file_update_date}}
                {{#if file.modified}}
                    <div><?php esc_html_e('Updated', 'wpfd'); ?>: {{file.modified}}</div>
                {{/if}}
            {{/if}}
            {{#if settings.file_download_hit}}
                {{#if file.hits}}
                    <div><?php esc_html_e('Hits', 'wpfd'); ?>: {{file.hits}}</div>
                {{/if}}
            {{/if}}
            {{#if settings.file_version}}
                {{#if file.version}}
                    <div><?php esc_html_e('Version', 'wpfd'); ?>: {{file.version}}</div>
                {{/if}}
            {{/if}}
        </div>
        {{/if}}
    </div>
    <div class="wpfd-single-file--buttons">
        {{#if settings.download_button}}
            {{#if file.show_add_to_cart}}
                <a class="wpfd_single_add_to_cart wpfd-single-file-button wpfd-button-download wpfd_downloadlink" href="{{file.linkdownload}}"{{#if file.product_id}} data-product_id="{{file.product_id}}"{{/if}}>
                    <i style="font-size: {{settings.download_icon_size}}px;" class="zmdi zmdi-shopping-cart-plus wpfd-add-to-cart"></i>
                    <span><?php esc_html_e('Add to cart', 'wpfd'); ?></span>
                </a>
            {{else}}
                <?php if (wpfd_can_download_files()) : ?>
                <a href="{{file.linkdownload}}" <?php echo $download_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Data attributes ?> data-id="{{file.ID}}" title="{{#if file.description}}{{file.description}}{{else}}{{file.title}}{{/if}}" class="noLightbox wpfd_downloadlink wpfd-single-file-button wpfd-button-download">
                    {{#if settings.download_icon_active}}
                    {{#xif settings.download_icon_position 'left'}}
                    {{{svgicon settings.download_icon settings.download_icon_color settings.download_icon_size}}}
                    {{/xif}}
                    {{/if}}
                    <span><?php esc_html_e('Download', 'wpfd'); ?></span>
                    {{#if settings.download_icon_active}}
                    {{#xif settings.download_icon_position 'right'}}
                    {{{svgicon settings.download_icon settings.download_icon_color settings.download_icon_size}}}
                    {{/xif}}
                    {{/if}}
                </a>
                <?php endif; ?>
            {{/if}}
        {{/if}}
        {{#if settings.preview_button}}
            {{#if file.show_add_to_cart}}
                <a class="wpfd-single-file-button wpfd-button-preview wpfd_single_view_product" href="{{file.viewerlink}}" target="_blank">
                    {{#if settings.preview_icon_active}}
                    {{#xif settings.preview_icon_position 'left'}}
                    {{{svgicon settings.preview_icon settings.preview_icon_color settings.preview_icon_size}}}
                    {{/xif}}
                    {{/if}}
                    <span><?php esc_html_e('View product', 'wpfd'); ?></span>
                    {{#if settings.preview_icon_active}}
                    {{#xif settings.preview_icon_position 'right'}}
                    {{{svgicon settings.preview_icon settings.preview_icon_color settings.preview_icon_size}}}
                    {{/xif}}
                    {{/if}}
                </a>
            {{else}}
                <?php if (wpfd_can_preview_files()) : ?>
                {{#if file.openpdflink}}
                    <a href="{{file.openpdflink}}" class="wpfd-single-file-button wpfd-button-preview{{#if file.open_in_lightbox}} wpfdlightbox{{/if}}"{{#if file.open_in_newtab}} target="_blank"{{/if}}>
                        {{#if settings.preview_icon_active}}
                        {{#xif settings.preview_icon_position 'left'}}
                        {{{svgicon settings.preview_icon settings.preview_icon_color settings.preview_icon_size}}}
                        {{/xif}}
                        {{/if}}
                        <span><?php esc_html_e('Preview', 'wpfd'); ?></span>
                        {{#if settings.preview_icon_active}}
                        {{#xif settings.preview_icon_position 'right'}}
                        {{{svgicon settings.preview_icon settings.preview_icon_color settings.preview_icon_size}}}
                        {{/xif}}
                        {{/if}}
                    </a>
                {{else}}
                    {{#if file.viewerlink}}
                        <a href="{{file.viewerlink}}" class="wpfd-single-file-button wpfd-button-preview wpfd_previewlink{{#if file.open_in_lightbox}} wpfdlightbox{{/if}}"{{#if file.open_in_newtab}} target="_blank"{{/if}}
                            data-id="{{file.ID}}" data-catid="{{file.catid}}"
                            data-file-type="{{file.ext}}">
                            {{#if settings.preview_icon_active}}
                            {{#xif settings.preview_icon_position 'left'}}
                            {{{svgicon settings.preview_icon settings.preview_icon_color settings.preview_icon_size}}}
                            {{/xif}}
                            {{/if}}
                            <span><?php esc_html_e('Preview', 'wpfd'); ?></span>
                            {{#if settings.preview_icon_active}}
                            {{#xif settings.preview_icon_position 'right'}}
                            {{{svgicon settings.preview_icon settings.preview_icon_color settings.preview_icon_size}}}
                            {{/xif}}
                            {{/if}}
                        </a>
                    {{/if}}
                {{/if}}
                <?php endif; ?>
            {{/if}}
        {{/if}}
    </div>
</div>

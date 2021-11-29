<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 4.8.0
 */

defined('ABSPATH') || die();

if (!isset($extensions) || empty($extensions)) {
    return esc_html__('You don\'t have any extensions, try to set one in WP File Download > Configuration > Main setting > Admin > Allowed extensions', 'wpfd');
}
ob_start();
?>
<script type="text/x-handlebars" data-template-name="wpfd-single-file-css-template">
<?php wpfd_get_template('tpl-single-css.php'); ?>
</script>
<style id="wpfd-single-file-css"></style>
<style id="wpfd-single-file-custom-css"></style>
<style>
    .wpfd-single-file {
        padding: 20px;
        margin: 10px;
        border-radius: 10px;
    }
    .wpfd-single-file--icon .wpfd-icon-placeholder {
        background-image: url(<?php echo esc_url($pdfIcon['png']); ?>);
    }
    .wpfd-single-file--icon .wpfd-icon-placeholder.svg {
        background-image: url(<?php echo esc_url($pdfIcon['svg']); ?>);
        background-size: 100%;
        <?php if (is_array($pdfIconParam) && isset($pdfIconParam['border-radius'])) : ?>
        border-radius: <?php echo esc_attr($pdfIconParam['border-radius']); ?>%;
        <?php endif; ?>
    }
    .wpfd-single-file--icon .wpfd-icon-placeholder.default {
        background-image: url(<?php echo esc_url(WPFD_PLUGIN_URL . 'app/site/assets/images/theme/pdf.png'); ?>);
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center center;
    }
</style>
<script type="text/x-handlebars" data-template-name="wpfd-single-file-template">
{{#if icon}}
<div class="wpfd-single-file--icon">
    {{#xxif " link_on_icon === 'download' || link_on_icon === 'preview' "}}<a href="#" alt="<?php esc_html_e('File icon', 'wpfd'); ?>">{{/xxif}}
    <div class="wpfd-icon-placeholder{{#xxif " base_icon_set === 'svg' "}} svg{{/xxif}}{{#xxif " base_icon_set === 'default' "}} default{{/xxif}}"></div>
    {{#xxif " link_on_icon === 'download' || link_on_icon === 'preview' "}}</a>{{/xxif}}
</div>
{{/if}}
<div class="wpfd-single-file--details wpfd-file-content">
    {{#if file_title}}<{{title_wrapper_tag}} class="wpfd-file-content--title"><?php esc_html_e('File title', 'wpfd'); ?></{{title_wrapper_tag}}>{{/if}}
    {{#if file_description}}
    <p class="wpfd-file-content--description">
        Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.
    </p>
    {{/if}}
    {{#if file_information}}
    <div class="wpfd-file-content--meta">
        {{#if file_size}}<div><?php esc_html_e('File size', 'wpfd'); ?>: 20Kb</div>{{/if}}
        {{#if file_created_date}}<div><?php esc_html_e('Created', 'wpfd'); ?>: 2020-12-10 09:23:11</div>{{/if}}
        {{#if file_update_date}}<div><?php esc_html_e('Updated', 'wpfd'); ?>: 2020-12-10 09:23:11</div>{{/if}}
        {{#if file_download_hit}}<div><?php esc_html_e('Hits', 'wpfd'); ?>: 20</div>{{/if}}
        {{#if file_version}}<div><?php esc_html_e('Version', 'wpfd'); ?>: 2.0</div>{{/if}}
    </div>
    {{/if}}
</div>
<div class="wpfd-single-file--buttons">
    {{#if download_button}}
    <a href="javascript:void(0);" class="wpfd-single-file-button wpfd-button-download">
    {{#xxif " download_icon_position === 'left' "}}
    {{#if download_icon_active}}
        {{{svgicon download_icon download_icon_color download_icon_size}}}
    {{/if}}
    {{/xxif}}
        <span><?php esc_html_e('Download', 'wpfd'); ?></span>
    {{#xxif " download_icon_position === 'right' "}}
    {{#if download_icon_active}}
        {{{svgicon download_icon download_icon_color download_icon_size}}}
    {{/if}}
    {{/xxif}}
    </a>
    {{/if}}
    {{#if preview_button}}
    <a href="javascript:void(0);" class="wpfd-single-file-button wpfd-button-preview">
    {{#xxif " preview_icon_position === 'left' "}}
    {{#if preview_icon_active}}
        {{{svgicon preview_icon preview_icon_color preview_icon_size}}}
    {{/if}}
    {{/xxif}}
        <span><?php esc_html_e('Preview', 'wpfd'); ?></span>
    {{#xxif " preview_icon_position === 'right' "}}
    {{#if preview_icon_active}}
        {{{svgicon preview_icon preview_icon_color preview_icon_size}}}
    {{/if}}
    {{/xxif}}
    </a>
    {{/if}}
</div>
</script>
<div class="ju-content-wrapper">
    <div class="wpfd-options-wrapper">
        <div class="wpfd-options-col wpfd-single-button">
            <div class="wpfd-single-button--actions">
                <button type="button" class="js-singleIconRestore-trigger ju-button ju-button-sm gray-outline-button" style="margin: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 1000 1000">
                        <path d="M430.75 14.31c-15.95 2.1-29.8 4.62-30.64 5.46-.84.84-21.83 134.31-21.83 139.35 0 .42-15.53 7.97-34.42 17.21-19.31 9.23-43.65 23.5-54.56 31.9-10.91 8.39-21.4 15.11-23.5 15.11-2.1 0-31.9-11.33-66.32-24.76l-62.54-24.78-9.23 8.81c-25.6 23.5-87.3 130.95-90.24 156.98-.84 7.55 9.23 18.05 50.79 51.2l52.47 41.97.42 67.58.42 67.57-54.16 41.99c-61.28 48.27-58.34 39.03-30.22 99.06 18.47 39.45 53.31 93.6 70.51 109.13l9.23 8.81 62.54-24.77c34.43-13.43 64.23-24.76 66.33-24.76 2.1 0 12.59 6.71 23.5 15.11 10.91 8.4 35.26 22.67 54.56 31.9 18.89 9.23 34.42 16.79 34.42 17.21s4.62 30.64 10.07 67.16c5.04 36.52 11.33 68 13.85 70.51 8.39 8.39 95.28 13.85 144.81 8.82 25.6-2.52 48.69-6.72 50.78-8.82 2.52-2.52 22.67-117.1 23.92-137.67 0-.84 12.59-6.71 27.71-13.85 15.11-6.72 39.45-20.99 54.56-31.48 15.11-10.49 28.96-18.89 30.64-18.89s31.48 11.33 65.89 24.76l62.54 25.18 14.27-15.53c20.99-23.08 49.53-68.42 67.57-107.03 25.6-54.99 28.54-46.59-32.31-94.44l-53.73-42.4V432.76l52.47-41.55c41.14-33.16 52.05-44.07 51.21-51.21-3.36-26.86-64.64-133.89-90.24-157.4l-9.24-8.81-62.95 24.78c-34.41 13.43-64.64 24.76-66.31 24.76-2.1 0-12.17-6.72-23.08-15.11-10.92-8.39-35.26-22.66-54.15-31.9-30.64-14.69-34.84-18.05-36.94-29.8-1.25-7.56-5.87-39.04-10.91-70.09-4.62-31.06-8.81-56.66-9.23-57.08-.42-.42-16.79-2.94-36.1-5.46-41.13-5.46-91.08-5.04-132.63.42zm133.05 353.4c28.96 14.69 53.72 39.04 69.25 69.67 10.49 20.57 11.75 27.7 11.75 62.96 0 35.26-1.25 42.39-11.75 62.96-15.53 30.22-40.3 54.98-69.25 69.67-21.41 10.92-28.13 12.17-63.8 12.17-35.26 0-42.81-1.26-63.38-11.75-28.12-13.43-59.6-45.75-72.19-73.45-13.01-28.12-13.01-91.08-.42-118.78 9.65-20.99 33.58-49.11 52.89-62.96 38.61-27.69 102.41-32.31 146.9-10.49z"></path>
                    </svg>
                    <span><?php esc_html_e('Restore default settings', 'wpfd'); ?></span>
                </button>
                <button type="button" class="js-singleIconSave-trigger ju-button ju-button-sm orange-button" style="margin: 10px;"><?php esc_html_e('Save Settings', 'wpfd'); ?></button>
            </div>
            <div class="wpfd-single-button--preview">
                <!-- DO NOT DELETE THIS -->
                <div class="wpfd-single-file"></div>
                <div class="wpfd-card wpfd-collapse" id="wpfd_single_custom_css_wrapper">
                    <div class="wpfd-card-header">
                        <span class="material-icons wpfd-collapse--icon">expand_more</span>
                        <span class="card-title"><?php esc_html_e('Custom CSS', 'wpfd'); ?></span>
                    </div>
                    <div class="wpfd-card-body">
                        <div class="wpfd-flex">
                            <div class="wpfd-col-flex flex-full">
                                <textarea style="background: lightgray" id="wpfd_singleicon_custom_css" name="custom_css" class="ju-input full-width" rows="10"><?php echo wp_unslash(WpfdBase::loadValue($singleFileParams, 'custom_css', '')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wpfd-single-button-settings">
                <!-- Icon -->
                <div class="wpfd-card wpfd-collapse">
                    <div class="wpfd-card-header">
                        <span class="material-icons wpfd-collapse--icon">expand_more</span>
                        <span class="card-title"><?php esc_html_e('Icon configuration', 'wpfd'); ?></span>
                        <div class="ju-switch-button">
                            <?php esc_html_e('Enable', 'wpfd'); ?>
                            <label class="switch">
                                <?php $checked = WpfdBase::loadValue($singleFileParams, 'icon', true) ? 'checked="checked"' : ''; ?>
                                <input type="checkbox" name="ref_icon" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                                <span class="slider"></span>
                            </label>
                            <input type="hidden" name="icon" value="<?php echo (WpfdBase::loadValue($singleFileParams, 'icon', true) ? '1' : '0'); ?>"/>
                        </div>
                    </div>
                    <div class="wpfd-card-body" style="display:none;">
                        <div class="wpfd-flex">
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSpacingBox('icon_margin', esc_html__('Margin', 'wpfd'), $singleFileParams); ?>
                                <?php $options = array('none' => __('None', 'wpfd'), 'preview' => __('Preview', 'wpfd'), 'download' => __('Download', 'wpfd')); ?>
                                <?php
                                $basedIconSet = WpfdBase::loadValue($singleFileParams, 'base_icon_set', 'png');
                                $basedIconSetOptions = array('default' => __('Default', 'wpfd'), 'png' => __('PNG SET', 'wpfd'), 'svg' => __('SVG SET', 'wpfd'));
                                ?>
                                <?php wpfdRenderSelectBox('base_icon_set', esc_html__('Select your icon set', 'wpfd'), $basedIconSetOptions, WpfdBase::loadValue($singleFileParams, 'base_icon_set', 'default')); ?>
                            </div>
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSlider('icon_size', esc_html__('Icon size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'icon_size', 144), 0, 300); ?>
                                <?php wpfdRenderSelectBox('link_on_icon', esc_html__('Link on Icon', 'wpfd'), $options, WpfdBase::loadValue($singleFileParams, 'link_on_icon', 'none')); ?>
                            </div>
                            <div class="wpfd-col-flex flex-full">
                                <h3><?php esc_html_e('This single file is based on the', 'wpfd'); ?>&nbsp;<span id="icon_set_heading"><?php echo esc_html($basedIconSetOptions[$basedIconSet]); ?></span></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End icon -->
                <!-- File title -->
                <div class="wpfd-card wpfd-collapse">
                    <div class="wpfd-card-header">
                        <span class="material-icons wpfd-collapse--icon">expand_more</span>
                        <span class="card-title"><?php esc_html_e('File title', 'wpfd'); ?></span>
                        <div class="ju-switch-button">
                            <?php esc_html_e('Enable', 'wpfd'); ?>
                            <label class="switch">
                                <?php $checked = WpfdBase::loadValue($singleFileParams, 'file_title', true) ? 'checked="checked"' : ''; ?>
                                <input type="checkbox" name="ref_file_title" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                                <span class="slider"></span>
                            </label>
                            <input type="hidden" name="file_title" value="<?php echo (WpfdBase::loadValue($singleFileParams, 'file_title', true) ? '1' : '0'); ?>"/>
                        </div>
                    </div>
                    <div class="wpfd-card-body" style="display:none;">
                        <div class="wpfd-flex">
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSpacingBox('title_margin', esc_html__('Margin', 'wpfd'), $singleFileParams); ?>
                                <?php wpfdRenderSlider('title_font_size', esc_html__('Font size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'title_font_size', 15)); ?>
                                <?php
                                $wrapperList = array(
                                    'h1' => __('H1', 'wpfd'),
                                    'h2' => __('H2', 'wpfd'),
                                    'h3' => __('H3', 'wpfd'),
                                    'h4' => __('H4', 'wpfd'),
                                    'h5' => __('H5', 'wpfd'),
                                    'h6' => __('H6', 'wpfd'),
                                    'div' => __('DIV', 'wpfd'),
                                    'p' => __('P', 'wpfd'),
                                ); ?>
                            </div>
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSpacingBox('title_padding', esc_html__('Padding', 'wpfd'), $singleFileParams); ?>
                                <?php wpfdRenderSelectBox('title_wrapper_tag', esc_html__('Wrapper tag', 'wpfd'), $wrapperList, WpfdBase::loadValue($singleFileParams, 'title_wrapper_tag', 'h3')); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End File title -->
                <!-- File description -->
                <div class="wpfd-card wpfd-collapse">
                    <div class="wpfd-card-header">
                        <span class="material-icons wpfd-collapse--icon">expand_more</span>
                        <span class="card-title"><?php esc_html_e('File description', 'wpfd'); ?></span>
                        <div class="ju-switch-button">
                            <?php esc_html_e('Enable', 'wpfd'); ?>
                            <label class="switch">
                                <?php $checked = WpfdBase::loadValue($singleFileParams, 'file_description', true) ? 'checked="checked"' : ''; ?>
                                <input type="checkbox" name="ref_file_description" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                                <span class="slider"></span>
                            </label>
                            <input type="hidden" name="file_description" value="<?php echo (WpfdBase::loadValue($singleFileParams, 'file_description', true) ? '1' : '0'); ?>"/>
                        </div>
                    </div>
                    <div class="wpfd-card-body" style="display:none;">
                        <div class="wpfd-flex">
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSpacingBox('description_margin', esc_html__('Margin', 'wpfd'), $singleFileParams); ?>
                                <?php wpfdRenderSlider('description_font_size', esc_html__('Font size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'description_font_size', 15)); ?>
                            </div>
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSpacingBox('description_padding', esc_html__('Padding', 'wpfd'), $singleFileParams); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End File description -->
                <!-- File Informations -->
                <div class="wpfd-card wpfd-collapse">
                    <div class="wpfd-card-header">
                        <span class="material-icons wpfd-collapse--icon">expand_more</span>
                        <span class="card-title"><?php esc_html_e('File information', 'wpfd'); ?></span>
                        <div class="ju-switch-button">
                            <?php esc_html_e('Enable', 'wpfd'); ?>
                            <label class="switch">
                                <?php $checked = WpfdBase::loadValue($singleFileParams, 'file_information', true) ? 'checked="checked"' : ''; ?>
                                <input type="checkbox" name="ref_file_information" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                                <span class="slider"></span>
                            </label>
                            <input type="hidden" name="file_information" value="<?php echo (WpfdBase::loadValue($singleFileParams, 'file_information', true) ? '1' : '0'); ?>"/>
                        </div>
                    </div>
                    <div class="wpfd-card-body" style="display:none;">
                        <div class="wpfd-flex">
                            <div class="wpfd-col-flex flex-half">
                                <div class="wpfd-single-button--options">
                                    <?php
                                    foreach ($singleFileOptions as $key => $option) {
                                        ?>
                                        <div class="wpfd-option">
                                            <label for="<?php esc_attr($key); ?>">
                                                <?php echo esc_html($option['name']); ?>
                                            </label>
                                            <div class="ju-switch-button">
                                                <label class="switch">
                                                    <?php $checked = (isset($singleFileParams[$key]) && $singleFileParams[$key]) ? 'checked="checked"' : ''; ?>
                                                    <input type="checkbox" id="ref_<?php echo esc_attr($key); ?>" name="ref_<?php echo esc_attr($key); ?>" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                                                    <span class="slider"></span>
                                                </label>
                                                <?php $value = (isset($singleFileParams[$key]) && $singleFileParams[$key]) ? '1' : '0'; ?>
                                                <input type="hidden" id="ref_<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>"/>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSlider('file_information_font_size', esc_html__('Font size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'file_information_font_size', 15)); ?>
                                <?php wpfdRenderSpacingBox('file_information_margin', esc_html__('Margin', 'wpfd'), $singleFileParams); ?>
                                <?php wpfdRenderSpacingBox('file_information_padding', esc_html__('Padding', 'wpfd'), $singleFileParams); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- .End File Informations -->
                <!-- Download button -->
                <div class="wpfd-card wpfd-collapse">
                    <div class="wpfd-card-header">
                        <span class="material-icons wpfd-collapse--icon">expand_more</span>
                        <span class="card-title"><?php esc_html_e('Download button', 'wpfd'); ?></span>
                        <div class="ju-switch-button">
                            <?php esc_html_e('Enable', 'wpfd'); ?>
                            <label class="switch">
                                <?php $checked = WpfdBase::loadValue($singleFileParams, 'download_button', true) ? 'checked="checked"' : ''; ?>
                                <input type="checkbox" name="ref_download_button" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                                <span class="slider"></span>
                            </label>
                            <input type="hidden" name="download_button" value="<?php echo (WpfdBase::loadValue($singleFileParams, 'download_button', true) ? '1' : '0'); ?>"/>
                        </div>
                    </div>
                    <div class="wpfd-card-body" style="display:none;">
                        <div class="wpfd-flex">
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSpacingBox('download_margin', esc_html__('Margin', 'wpfd'), $singleFileParams); ?>
                                <?php wpfdRenderSlider('download_width', esc_html__('Button width', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'download_width', 250), 0, 500); ?>
                                <?php wpfdRenderSlider('download_font_size', esc_html__('Font size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'download_font_size', 20)); ?>
                                <?php wpfdRenderBoxShadow('download_boxshadow', esc_html__('Box shadow', 'wpfd'), $singleFileParams); ?>
                            </div>
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSpacingBox('download_padding', esc_html__('Padding', 'wpfd'), $singleFileParams); ?>
                                <?php wpfdRenderSlider('download_border_radius', esc_html__('Border radius', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'download_border_radius', 20)); ?>
                                <?php wpfdRenderSlider('download_border_size', esc_html__('Border size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'download_border_size', 0), 0, 30); ?>
                                <?php wpfdRenderColor('download_border_color', WpfdBase::loadValue($singleFileParams, 'download_border_color', '#ff891b'), esc_html__('Border color', 'wpfd')); ?>
                            </div>
                            <div class="wpfd-col-flex flex-half">
                                <div class="wpfd-button-colors">
                                    <div class="wpfd-color-tabs">
                                        <div class="wpfd-color-tab active">
                                            <?php esc_html_e('Normal', 'wpfd'); ?>
                                        </div>
                                        <div class="wpfd-color-tab">
                                            <?php esc_html_e('Hover', 'wpfd'); ?>
                                        </div>
                                    </div>
                                    <div class="wpfd-color-contents">
                                        <div class="wpfd-color-content active">
                                            <?php wpfdRenderColor('download_font_color', WpfdBase::loadValue($singleFileParams, 'download_font_color', '#ffffff'), esc_html__('Font color', 'wpfd')); ?>
                                            <?php wpfdRenderGradientSwitcher('download_background', esc_html__('Button color type', 'wpfd'), WpfdBase::loadValue($singleFileParams, 'download_background', 'solid')); ?>
                                            <?php wpfdRenderColor('download_background_start', WpfdBase::loadValue($singleFileParams, 'download_background_start', '#ff891b'), esc_html__('Button gradient start', 'wpfd')); ?>
                                            <?php wpfdRenderColor('download_background_end', WpfdBase::loadValue($singleFileParams, 'download_background_end', '#ff891b'), esc_html__('Button gradient end', 'wpfd')); ?>
                                            <?php wpfdRenderColor('download_background_solid', WpfdBase::loadValue($singleFileParams, 'download_background_solid', '#ff891b'), esc_html__('Button background color', 'wpfd')); ?>
                                        </div>
                                        <div class="wpfd-color-content">
                                            <?php wpfdRenderColor('download_hover_font_color', WpfdBase::loadValue($singleFileParams, 'download_hover_font_color', '#ffffff'), esc_html__('Hover font color', 'wpfd')); ?>
                                            <?php wpfdRenderGradientSwitcher('download_hover_background', esc_html__('Hover background type', 'wpfd'), WpfdBase::loadValue($singleFileParams, 'download_hover_background', 'solid')); ?>
                                            <?php wpfdRenderColor('download_hover_background_start', WpfdBase::loadValue($singleFileParams, 'download_hover_background_start', '#ff891b'), esc_html__('Hover gradient start', 'wpfd')); ?>
                                            <?php wpfdRenderColor('download_hover_background_end', WpfdBase::loadValue($singleFileParams, 'download_hover_background_end', '#ff891b'), esc_html__('Hover gradient end', 'wpfd')); ?>
                                            <?php wpfdRenderColor('download_hover_background_solid', WpfdBase::loadValue($singleFileParams, 'download_hover_background_solid', '#ff891b'), esc_html__('Hover background color', 'wpfd')); ?>
                                        </div>
                                    </div>
                                </div>

                                </div>
                            <div class="wpfd-col-flex flex-half">
                                <div class="wpfd-block-title wpfd-flex wpfd-block-title--small">
                                    <h3><?php esc_html_e('Download Icon', 'wpfd'); ?></h3>
                                    <div class="ju-switch-button">
                                        <?php esc_html_e('Visible', 'wpfd'); ?>
                                        <label class="switch">
                                            <?php $checked = WpfdBase::loadValue($singleFileParams, 'download_icon_active', true) ? 'checked="checked"' : ''; ?>
                                            <input type="checkbox" name="ref_download_icon_active" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                                            <span class="slider"></span>
                                        </label>
                                        <input type="hidden" name="download_icon_active" value="<?php echo (WpfdBase::loadValue($singleFileParams, 'download_icon_active', true) ? '1' : '0'); ?>"/>
                                    </div>
                                </div>

                                <ul class="wpfd-frame-list" style="display: flex;flex: auto;flex-wrap: nowrap;">
                                    <?php $selected = WpfdBase::loadValue($singleFileParams, 'download_icon', 'download-icon1'); ?>
                                    <li data-icon-id="download-icon1"<?php echo ($selected === 'download-icon1' ? ' class="selected"' : ''); ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M400 200c0 112-89 200-201 200C88 400 0 311 0 199S89 0 201 0c111 0 199 89 199 200zm-179-8l-3-1V89c0-15-7-24-18-23-13 1-18 9-18 22v107l-34-35c-8-8-18-11-27-2-8 8-7 18 2 26l63 63c10 11 18 10 28 0l63-62c8-8 10-17 2-27-7-8-17-7-27 2l-31 32zm-21 113h82c13 0 24-4 32-14 10-14 8-29 6-44-1-4-8-9-12-8-5 0-9 6-12 10-2 3-1 8 0 13 1 13-5 17-18 17H131c-25 0-25 0-26-25-1-3 0-6-2-7-3-4-8-8-12-8s-10 5-11 9c-11 30 8 57 40 57h80z"/></g></svg>
                                    </li>
                                    <li data-icon-id="download-icon2"<?php echo ($selected === 'download-icon2' ? ' class="selected"' : ''); ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M45 355h310v-65c1-16 15-27 29-22 9 3 16 11 16 20v91c0 12-10 21-24 21H26c-17 0-26-9-26-26v-85a22 22 0 0143-8 31 31 0 011 11l1 57z"/><path data-name="Path 1270" d="M222 235l5-5 45-45c9-9 23-9 32 0s9 22-1 32l-86 86c-10 10-23 10-34 0l-86-86a22 22 0 1131-31l45 44a55 55 0 013 4l2-1V24c0-13 8-23 20-24 13-1 24 9 24 23v212z"/></g></svg>
                                    </li>
                                    <li data-icon-id="download-icon3"<?php echo ($selected === 'download-icon3' ? ' class="selected"' : ''); ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M200 400H26c-18 0-26-9-26-27v-73c0-16 9-25 24-25h105a14 14 0 018 4l28 28c21 21 48 21 69 0l28-29a13 13 0 018-3h106c14 0 24 9 24 23v79c0 14-10 23-25 23H200zm155-63c-9 0-17 8-17 16a15 15 0 0015 16c9 0 17-7 17-16a16 16 0 00-15-16zm-47 16c0-9-7-16-16-16a16 16 0 00-15 16c0 9 6 16 15 16s16-7 16-16zM245 127h55a56 56 0 017 0c7 0 11 4 13 10 3 6 1 11-3 16l-43 45-62 63c-8 9-17 9-25 1L83 154c-5-5-6-11-4-18 3-7 9-9 15-9h61v-9-99c0-14 5-19 17-19h55c13 0 18 5 18 19v99z"/></g></svg>
                                    </li>
                                    <li data-icon-id="download-icon4"<?php echo ($selected === 'download-icon4' ? ' class="selected"' : ''); ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M178 234v-7V24c0-13 8-23 20-24 13-1 24 9 24 23v212l5-5 44-44c10-9 23-10 32-1s9 23-1 33l-85 85c-10 11-23 11-34 0l-85-86a22 22 0 0123-37 28 28 0 018 6l44 44a31 31 0 013 5zM200 400H24c-17 0-28-14-23-29 3-10 12-15 23-16h351c12 0 21 6 24 16 5 15-6 29-22 29H200z"/></g></svg>
                                    </li>
                                </ul>
                                <input type="hidden" name="download_icon" value="<?php echo esc_attr($selected); ?>" />
                                <?php wpfdRenderColor('download_icon_color', WpfdBase::loadValue($singleFileParams, 'download_icon_color', '#ffffff'), esc_html__('Icon color', 'wpfd')); ?>
                                <?php wpfdRenderSlider('download_icon_size', esc_html__('Icon size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'download_icon_size', 38)); ?>

                                <?php wpfdRenderLeftRightSwitcher('download_icon_position', esc_html__('Icon position', 'wpfd'), WpfdBase::loadValue($singleFileParams, 'download_icon_position', 'left')); ?>
                                <?php wpfdRenderSlider('download_icon_spacing', esc_html__('Icon spacing', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'download_icon_spacing', 10)); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- .Download button -->

                <!-- Preview button -->
                <div class="wpfd-card wpfd-collapse">
                    <div class="wpfd-card-header">
                        <span class="material-icons wpfd-collapse--icon">expand_more</span>
                        <span class="card-title"><?php esc_html_e('Preview button', 'wpfd'); ?></span>
                        <div class="ju-switch-button">
                            <?php esc_html_e('Enable', 'wpfd'); ?>
                            <label class="switch">
                                <?php $checked = WpfdBase::loadValue($singleFileParams, 'preview_button', true) ? 'checked="checked"' : ''; ?>
                                <input type="checkbox" name="ref_preview_button" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                                <span class="slider"></span>
                            </label>
                            <input type="hidden" name="preview_button" value="<?php echo (WpfdBase::loadValue($singleFileParams, 'preview_button', true) ? '1' : '0'); ?>"/>
                        </div>
                    </div>
                    <div class="wpfd-card-body" style="display:none;">
                        <div class="wpfd-flex">
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSpacingBox('preview_margin', esc_html__('Margin', 'wpfd'), $singleFileParams); ?>
                                <?php wpfdRenderSlider('preview_width', esc_html__('Button width', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'preview_width', 250), 0, 500); ?>
                                <?php wpfdRenderSlider('preview_font_size', esc_html__('Font size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'preview_font_size', 20)); ?>
                                <?php wpfdRenderBoxShadow('preview_boxshadow', esc_html__('Box shadow', 'wpfd'), $singleFileParams); ?>
                            </div>
                            <div class="wpfd-col-flex flex-half">
                                <?php wpfdRenderSpacingBox('preview_padding', esc_html__('Padding', 'wpfd'), $singleFileParams); ?>
                                <?php wpfdRenderSlider('preview_border_radius', esc_html__('Border radius', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'preview_border_radius', 20)); ?>
                                <?php wpfdRenderSlider('preview_border_size', esc_html__('Border size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'preview_border_size', 0), 0, 30); ?>
                                <?php wpfdRenderColor('preview_border_color', WpfdBase::loadValue($singleFileParams, 'preview_border_color', '#ff891b'), esc_html__('Border color', 'wpfd')); ?>
                            </div>
                            <div class="wpfd-col-flex flex-half">
                                <div class="wpfd-button-colors">
                                    <div class="wpfd-color-tabs">
                                        <div class="wpfd-color-tab active">
                                            <?php esc_html_e('Normal', 'wpfd'); ?>
                                        </div>
                                        <div class="wpfd-color-tab">
                                            <?php esc_html_e('Hover', 'wpfd'); ?>
                                        </div>
                                    </div>
                                    <div class="wpfd-color-contents">
                                        <div class="wpfd-color-content active">
                                            <?php wpfdRenderColor('preview_font_color', WpfdBase::loadValue($singleFileParams, 'preview_font_color', '#ffffff'), esc_html__('Font color', 'wpfd')); ?>
                                            <?php wpfdRenderGradientSwitcher('preview_background', esc_html__('Button color type', 'wpfd'), WpfdBase::loadValue($singleFileParams, 'preview_background', 'solid')); ?>
                                            <?php wpfdRenderColor('preview_background_start', WpfdBase::loadValue($singleFileParams, 'preview_background_start', '#ff891b'), esc_html__('Button gradient start', 'wpfd')); ?>
                                            <?php wpfdRenderColor('preview_background_end', WpfdBase::loadValue($singleFileParams, 'preview_background_end', '#ff891b'), esc_html__('Button gradient end', 'wpfd')); ?>
                                            <?php wpfdRenderColor('preview_background_solid', WpfdBase::loadValue($singleFileParams, 'preview_background_solid', '#ff891b'), esc_html__('Button background color', 'wpfd')); ?>
                                        </div>
                                        <div class="wpfd-color-content">
                                            <?php wpfdRenderColor('preview_hover_font_color', WpfdBase::loadValue($singleFileParams, 'preview_hover_font_color', '#ffffff'), esc_html__('Hover font color', 'wpfd')); ?>
                                            <?php wpfdRenderGradientSwitcher('preview_hover_background', esc_html__('Hover background type', 'wpfd'), WpfdBase::loadValue($singleFileParams, 'preview_hover_background', 'solid')); ?>
                                            <?php wpfdRenderColor('preview_hover_background_start', WpfdBase::loadValue($singleFileParams, 'preview_hover_background_start', '#ff891b'), esc_html__('Hover gradient start', 'wpfd')); ?>
                                            <?php wpfdRenderColor('preview_hover_background_end', WpfdBase::loadValue($singleFileParams, 'preview_hover_background_end', '#ff891b'), esc_html__('Hover gradient end', 'wpfd')); ?>
                                            <?php wpfdRenderColor('preview_hover_background_solid', WpfdBase::loadValue($singleFileParams, 'preview_hover_background_solid', '#ff891b'), esc_html__('Hover background color', 'wpfd')); ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="wpfd-col-flex flex-half">
                                <div class="wpfd-block-title wpfd-flex wpfd-block-title--small">
                                    <h3><?php esc_html_e('Preview Icon', 'wpfd'); ?></h3>
                                    <div class="ju-switch-button">
                                        <?php esc_html_e('Visible', 'wpfd'); ?>
                                        <label class="switch">
                                            <?php $checked = WpfdBase::loadValue($singleFileParams, 'preview_icon_active', true) ? 'checked="checked"' : ''; ?>
                                            <input type="checkbox" name="ref_preview_icon_active" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                                            <span class="slider"></span>
                                        </label>
                                        <input type="hidden" name="preview_icon_active" value="<?php echo (WpfdBase::loadValue($singleFileParams, 'preview_icon_active', true) ? '1' : '0'); ?>"/>
                                    </div>
                                </div>

                                <ul class="wpfd-frame-list" style="display: flex;flex: auto;flex-wrap: nowrap;">
                                    <?php $selected = WpfdBase::loadValue($singleFileParams, 'preview_icon', 'preview-icon1'); ?>
                                    <li data-icon-id="preview-icon1"<?php echo ($selected === 'preview-icon1' ? ' class="selected"' : ''); ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M200 325c-36 0-70-11-101-30a356 356 0 01-92-80c-9-11-9-19 0-29 37-44 79-79 131-99 51-20 102-14 151 12 41 22 76 52 106 89 6 8 7 16 1 23-39 48-85 85-141 105a167 167 0 01-55 9zm0-47c41 0 75-36 75-81s-34-81-75-81c-42 0-75 36-75 81s34 81 75 81z"/><path d="M200 159c21 0 38 17 38 38 0 20-17 37-38 37s-38-17-38-37c0-21 17-38 38-38z"/></g></svg>
                                    </li>
                                    <li data-icon-id="preview-icon2"<?php echo ($selected === 'preview-icon2' ? ' class="selected"' : ''); ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M0 195c2-10 9-18 16-26 26-28 54-52 89-70a203 203 0 01202 6c33 20 61 46 86 75 3 4 5 10 7 15v10c-2 10-9 18-16 26-24 26-51 50-84 67a206 206 0 01-207-3c-31-18-57-42-81-69-5-6-10-13-12-21zm199 99c30 0 57-8 82-21 33-18 60-43 84-70 2-2 2-4 0-6-21-24-45-47-74-64-27-16-56-26-88-27-28 0-54 6-79 19-35 17-64 43-89 72-2 2-2 4 0 6 21 24 45 47 74 64 27 17 58 27 90 27z"/><path d="M202 276c-45 1-82-32-84-73-2-42 34-77 79-79s83 31 85 74c1 42-34 76-80 78zm-2-30c27-1 49-21 49-46s-22-46-49-45c-27 0-49 20-49 45s22 46 49 46z"/></g></svg>
                                    </li>
                                    <li data-icon-id="preview-icon3"<?php echo ($selected === 'preview-icon3' ? ' class="selected"' : ''); ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M201 0l12 12 80 76a9 9 0 012 6v98c0 3 1 5 3 6a83 83 0 0133 122l-2 3 61 59-19 18-60-59-15 8-1 3v19H10v-5V4 0zm68 108h-86V25H36v321h176l-2-2a83 83 0 01-33-38c-1-3-3-4-6-4H66v-25h103l5-33H66v-25h121a8 8 0 005-2c19-19 41-29 68-28h9zm-11 225c34 0 62-27 62-59 0-34-28-61-62-61s-62 27-62 59c0 34 27 61 62 61zm-9-250l-40-39v39z"/></g></svg>
                                    </li>
                                    <li data-icon-id="preview-icon4"<?php echo ($selected === 'preview-icon4' ? ' class="selected"' : ''); ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 400 400"><g fill="#9a9999"><path d="M0 200V55C0 28 16 8 41 2a63 63 0 0115-2h289c32 0 55 23 55 54v248c0 10-6 16-15 16-8 0-14-6-14-16V56c0-16-11-27-28-27H57c-18 0-28 9-28 28v286c0 19 10 28 28 28h243c11 0 18 6 18 15s-7 14-18 14H56c-28 0-49-16-55-41a67 67 0 01-1-15V200z"/><path d="M314 302l15 15 52 51c7 7 7 14 2 20-6 6-13 5-20-2l-67-65a11 11 0 00-1-1 104 104 0 01-149-26c-29-43-20-99 20-133 39-33 99-31 137 4 38 34 45 93 11 137zm-159-64c-1 42 34 77 77 77 42 1 78-33 78-75 1-42-34-77-75-77-44-1-79 32-80 75z"/></g></svg>
                                    </li>
                                </ul>
                                <input type="hidden" name="preview_icon" value="<?php echo esc_attr($selected); ?>" />
                                <?php wpfdRenderColor('preview_icon_color', WpfdBase::loadValue($singleFileParams, 'preview_icon_color', '#ffffff'), esc_html__('Icon color', 'wpfd')); ?>
                                <?php wpfdRenderSlider('preview_icon_size', esc_html__('Icon size', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'preview_icon_size', 38)); ?>

                                <?php wpfdRenderLeftRightSwitcher('preview_icon_position', esc_html__('Icon position', 'wpfd'), WpfdBase::loadValue($singleFileParams, 'preview_icon_position', 'left')); ?>
                                <?php wpfdRenderSlider('preview_icon_spacing', esc_html__('Icon spacing', 'wpfd'), 'px', WpfdBase::loadValue($singleFileParams, 'preview_icon_spacing', 10)); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- .Download button -->
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
return $content;

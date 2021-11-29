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
<div id="svg-icons-list" class="svg-icons-list">
    <div class="wpfd-icons-header">
        <input type="text" placeholder="<?php esc_html_e('Search for file type, ex: xls', 'wpfd'); ?>" data-search="svg-table"  class="js-typeFilter-trigger ju-input" />
        <div class="ju-switch-button" title="<?php esc_html_e('The icons set use in themes', 'wpfd'); ?>">
            <?php esc_html_e('Set as default', 'wpfd'); ?>
            <label class="switch">
                <?php $checked = ($currentIconSet === 'svg') ? 'checked="checked"' : ''; ?>
                <input type="checkbox" name="ref_active_svg_set" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                <span class="slider"></span>
            </label>
            <input type="hidden" name="active_svg_set" value="<?php echo ($currentIconSet === 'svg') ? '1' : '0'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK  ?>"/>
        </div>
    </div>
    <div class="wpfd-scroll-wrapper">
        <table data-icon-set="svg" data-search-ref="svg-table" class="wpfd-icons-table">
        <thead>
            <tr>
                <th class="wpfd-extension"><?php esc_html_e('TYPE', 'wpfd'); ?></th>
                <th class="wpfd-default-icon"><?php esc_html_e('ICON', 'wpfd'); ?></th>
                <th class="wpfd-uploaded-icon"><?php esc_html_e('OVERIDE ICON', 'wpfd'); ?></th>
                <th class="wpfd-actions"><?php esc_html_e('ACTION', 'wpfd'); ?></th>
            </tr>
        </thead>
            <tbody>
            <?php foreach ($extensions['svg'] as $extension => $icon) : ?>
                <?php if ($icon) : ?>
                    <tr data-extension="<?php echo esc_attr($extension); ?>">
                        <td class="wpfd-extension">.<?php echo esc_attr($extension); ?></td>
                        <td class="wpfd-default-icon">
                            <?php if (isset($icon['default']) && $icon['default'] !== '') : ?>
                                <img class="wpfdsvg" width="70" src="<?php echo esc_url($icon['default']); ?>" />
                            <?php endif; ?>
                        </td>
                        <td class="wpfd-uploaded-icon">
                            <?php if (isset($icon['uploaded']) && $icon['uploaded'] !== '') : ?>
                                <img <?php echo isset($icon['css']) ? $icon['css'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?> class="wpfdsvg" width="70" src="<?php echo esc_url($icon['uploaded']); ?>" />
                            <?php endif; ?>
                        </td>
                        <td class="wpfd-actions">
                            <button data-action="edit" data-icon-set="svg" data-extension="<?php echo esc_attr($extension); ?>" class="js-SvgEdit-trigger ju-button ju-button-sm gray-outline-button">
                                <?php esc_html_e('Edit Icon', 'wpfd'); ?>
                            </button>

                            <?php
                            $style = '';
                            if (isset($icon['uploaded']) && $icon['uploaded'] === '') :
                                $style = ' style="display:none"';
                            endif; ?>

                            <button<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?> data-action="delete" data-icon-set="svg" data-extension="<?php echo esc_attr($extension); ?>" class="js-SvgDelete-trigger ju-button ju-button-sm gray-outline-button">
                                <?php esc_html_e('Delete overide', 'wpfd'); ?>
                            </button>
                            <button data-action="applyall" data-icon-set="svg" data-extension="<?php echo esc_attr($extension); ?>" class="js-SvgApplyAll-trigger ju-button ju-button-sm gray-outline-button ju-sm-text">
                                <?php esc_html_e('Style apply for all icons', 'wpfd'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>
    <div class="wpfd-big-card file-unknown">
        <div class="wpfd-big-card--heading">
            <h3><?php esc_html_e('RESET TO DEFAULT', 'wpfd'); ?></h3>
            <p><?php esc_html_e('You will reset all icons to the original set', 'wpfd'); ?></p>
        </div>
        <div class="wpfd-big-card--content wpfd-flex wpfd-flex-around">
            <button class="js-SvgResetOriginal-trigger ju-button ju-button-sm gray-outline-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 1000 1000">
                    <path d="M430.75 14.31c-15.95 2.1-29.8 4.62-30.64 5.46-.84.84-21.83 134.31-21.83 139.35 0 .42-15.53 7.97-34.42 17.21-19.31 9.23-43.65 23.5-54.56 31.9-10.91 8.39-21.4 15.11-23.5 15.11-2.1 0-31.9-11.33-66.32-24.76l-62.54-24.78-9.23 8.81c-25.6 23.5-87.3 130.95-90.24 156.98-.84 7.55 9.23 18.05 50.79 51.2l52.47 41.97.42 67.58.42 67.57-54.16 41.99c-61.28 48.27-58.34 39.03-30.22 99.06 18.47 39.45 53.31 93.6 70.51 109.13l9.23 8.81 62.54-24.77c34.43-13.43 64.23-24.76 66.33-24.76 2.1 0 12.59 6.71 23.5 15.11 10.91 8.4 35.26 22.67 54.56 31.9 18.89 9.23 34.42 16.79 34.42 17.21s4.62 30.64 10.07 67.16c5.04 36.52 11.33 68 13.85 70.51 8.39 8.39 95.28 13.85 144.81 8.82 25.6-2.52 48.69-6.72 50.78-8.82 2.52-2.52 22.67-117.1 23.92-137.67 0-.84 12.59-6.71 27.71-13.85 15.11-6.72 39.45-20.99 54.56-31.48 15.11-10.49 28.96-18.89 30.64-18.89s31.48 11.33 65.89 24.76l62.54 25.18 14.27-15.53c20.99-23.08 49.53-68.42 67.57-107.03 25.6-54.99 28.54-46.59-32.31-94.44l-53.73-42.4V432.76l52.47-41.55c41.14-33.16 52.05-44.07 51.21-51.21-3.36-26.86-64.64-133.89-90.24-157.4l-9.24-8.81-62.95 24.78c-34.41 13.43-64.64 24.76-66.31 24.76-2.1 0-12.17-6.72-23.08-15.11-10.92-8.39-35.26-22.66-54.15-31.9-30.64-14.69-34.84-18.05-36.94-29.8-1.25-7.56-5.87-39.04-10.91-70.09-4.62-31.06-8.81-56.66-9.23-57.08-.42-.42-16.79-2.94-36.1-5.46-41.13-5.46-91.08-5.04-132.63.42zm133.05 353.4c28.96 14.69 53.72 39.04 69.25 69.67 10.49 20.57 11.75 27.7 11.75 62.96 0 35.26-1.25 42.39-11.75 62.96-15.53 30.22-40.3 54.98-69.25 69.67-21.41 10.92-28.13 12.17-63.8 12.17-35.26 0-42.81-1.26-63.38-11.75-28.12-13.43-59.6-45.75-72.19-73.45-13.01-28.12-13.01-91.08-.42-118.78 9.65-20.99 33.58-49.11 52.89-62.96 38.61-27.69 102.41-32.31 146.9-10.49z"/>
                </svg>
                <?php esc_html_e('Reset to original set', 'wpfd'); ?>
            </button>
            <div class="original-set">
                <img src="<?php echo esc_url(WPFD_PLUGIN_URL . '/app/admin/assets/images/icons.png'); ?>" width="133" />
            </div>
        </div>
    </div>
</div>
<div id="svg-icons-editor" class="svg-icons-editor" style="display: none;">
    <div class="wpfd-icons-loading"></div>
    <div class="wpfd-svg-editor-wrapper">
        <!-- Preview -->
        <div class="wpfd-editor-preview wpfd-card wpfd-card-nobg wpfd-flex">
            <div class="svg_placeholder"></div>
            <div class="wpfd-editor-actions wpfd-flex wpfd-flex-column">
                <button class="js-SvgSave-trigger ju-button ju-button-sm orange-outline-button">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="20"><path d="M0 0h24v24H0z" fill="none"/><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                    <?php esc_html_e('Save', 'wpfd'); ?>
                </button>
                <button class="js-SvgBack-trigger ju-button ju-button-sm gray-outline-button">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 24 24">
                        <path fill="none" d="M0 0h24v24H0z"/>
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                    <?php esc_html_e('Back', 'wpfd'); ?>
                </button>
            </div>
        </div>
        <!-- .Preview -->
            <div class="wpfd-scroll-wrapper">
            <!-- Icon -->
            <div class="wpfd-card wpfd-collapse" data-close-group="extension_name,frame_settings,wrapper" data-collapse-name="icon">
                <div class="wpfd-card-header">
                    <span class="material-icons wpfd-collapse--icon">expand_more</span>
                    <span class="wpfd-flex wpfd-flex-column">
                        <span class="card-title"><?php esc_html_e('ICON', 'wpfd'); ?></span>
                        <span class="card-description"><?php esc_html_e('Change the icon inside the icon', 'wpfd'); ?></span>
                    </span>
                    <div class="ju-switch-button">
                        <?php esc_html_e('Visible', 'wpfd'); ?>
                        <label class="switch">
                            <?php $checked = intval($svgIconParams['icon-active']) === 1 ? 'checked="checked"' : ''; ?>
                            <input type="checkbox" name="ref_icon-active" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                            <span class="slider"></span>
                        </label>
                        <input type="hidden" name="icon-active" value="<?php echo esc_attr($svgIconParams['icon-active']); ?>"/>
                    </div>
                </div>
                <div class="wpfd-card-body">
                    <div class="wpfd-flex">
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderColor('icon-color', $svgIconParams['icon-color'], 'Icon color'); ?>
                            <?php wpfdRenderSlider('icon-size', esc_html__('Icon size', 'wpfd'), 'px', esc_attr($svgIconParams['icon-size']), 0, 400); ?>
                        </div>
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderIconBox($svgIconParams['icon']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- .Icon -->
            <!-- Extension name -->
            <div class="wpfd-card wpfd-collapse" data-close-group="icon,frame_settings,wrapper" data-collapse-name="extension_name">
                <div class="wpfd-card-header">
                    <span class="material-icons wpfd-collapse--icon">expand_less</span>
                    <span class="wpfd-flex wpfd-flex-column">
                        <span class="card-title"><?php esc_html_e('Extension name', 'wpfd'); ?></span>
                        <span class="card-description"><?php esc_html_e('Set the style of the extension name', 'wpfd'); ?></span>
                    </span>
                    <div class="ju-switch-button">
                        <?php esc_html_e('Visible', 'wpfd'); ?>
                        <label class="switch">
                            <?php $checked = intval($svgIconParams['extension-name-active']) === 1 ? 'checked="checked"' : ''; ?>
                            <input type="checkbox" name="ref_extension-name-active" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                            <span class="slider"></span>
                        </label>
                        <input type="hidden" name="extension-name-active" value="<?php echo esc_attr($svgIconParams['extension-name-active']); ?>"/>
                    </div>
                </div>
                <div style="display:none" class="wpfd-card-body">
                    <div class="wpfd-flex">
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderText('icon-text', $svgIconParams['icon-text'], esc_html__('Extension', 'wpfd'), esc_html__('Extension', 'wpfd')); ?>
                            <div class="ju-option-group">
                                <label class="wpfd-label" for="font-family"><?php esc_html_e('Font family', 'wpfd'); ?></label>
                                <select data-element-id="font-family" name="font-family" class="ju-input full-width mb10">
                                    <option value="arial" <?php $svgIconParams['font-family'] === 'arial' ? 'selected="selected"' : ''; ?>>Arial</option>
                                    <option value="serif" <?php $svgIconParams['font-family'] === 'serif' ? 'selected="selected"' : ''; ?>>Serif</option>
                                    <option value="sans-serif" <?php $svgIconParams['font-family'] === 'sans-serif' ? 'selected="selected"' : ''; ?>>Sans-serif</option>
                                </select>
                            </div>
                        </div>
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderSlider('font-size', esc_html__('Font size', 'wpfd'), 'px', esc_attr($svgIconParams['font-size'])); ?>
                            <?php wpfdRenderColor('text-color', $svgIconParams['text-color'], 'Text color'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- .Extension name -->

            <!-- Frame setting -->
            <div class="wpfd-card wpfd-collapse" data-close-group="icon,extension_name,wrapper" data-collapse-name="frame_settings">
                <div class="wpfd-card-header">
                    <span class="material-icons wpfd-collapse--icon">expand_less</span>
                    <span class="wpfd-flex wpfd-flex-column">
                        <span class="card-title"><?php esc_html_e('Frame setting', 'wpfd'); ?></span>
                        <span class="card-description"><?php esc_html_e('This is a shape around the icon and extension name', 'wpfd'); ?></span>
                    </span>
                    <div class="ju-switch-button">
                        <?php esc_html_e('Visible', 'wpfd'); ?>
                        <label class="switch">
                            <?php $checked = intval($svgIconParams['frame-active']) === 1 ? 'checked="checked"' : ''; ?>
                            <input type="checkbox" name="ref_frame-active" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                            <span class="slider"></span>
                        </label>
                        <input type="hidden" name="frame-active" value="<?php echo esc_attr($svgIconParams['frame-active']); ?>"/>
                    </div>
                </div>
                <div style="display:none" class="wpfd-card-body">
                    <div class="wpfd-flex">
                        <div class="wpfd-col-flex flex-full">
                            <ul class="wpfd-frame-list" style="display: flex;flex: auto;flex-wrap: nowrap;">
                                <li data-id="0" <?php echo ((intval($svgIconParams['svg-frame']) === 0) ? 'class="selected"' : ''); ?>>
                                    <svg width="100" height="100" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                                        <text x="50" y="325" stroke-width="0" font-family="sans-serif" font-size="100" style="line-height:1.25" fill="currentColor">
                                            <tspan x="50" y="325">NONE</tspan>
                                        </text>
                                    </svg>
                                </li>
                                <li data-id="1" <?php echo ((intval($svgIconParams['svg-frame']) === 1) ? 'class="selected"' : ''); ?>>
                                    <svg width="100" height="100" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(74 240)">
                                            <rect width="240" height="100" rx="20" fill="none" stroke="currentColor" stroke-miterlimit="0" stroke-width="5" />
                                        </g>
                                    </svg>
                                </li>
                                <li data-id="2" <?php echo ((intval($svgIconParams['svg-frame']) === 2) ? 'class="selected"' : ''); ?>>
                                    <svg width="100" height="100" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(74 44)">
                                            <rect width="240" height="300" rx="20" transform="translate(0.606)" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="5"></rect>
                                            <line x2="240" transform="translate(0 200)" fill="none" stroke="currentColor" stroke-linecap="butt" stroke-linejoin="round" stroke-width="5"></line>
                                        </g>
                                    </svg>
                                </li>
                                <li data-id="3" <?php echo ((intval($svgIconParams['svg-frame']) === 3) ? 'class="selected"' : ''); ?>>
                                    <svg width="100" height="100" version="1.1" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(74 44)" fill="none" stroke="currentColor" stroke-width="5">
                                            <path d="m20,0a20,20 0 0 0 -20,20l0,260a20,20 0 0 0 20,20l200,0a20,20 0 0 0 20,-20l0,-220l-60,-60zm0,0m160,0l60,60l-60,0l0,-60z" data-path-raw="m20,0a20,20 0 0 0 -20,20l0,260a20,20 0 0 0 20,20l{frame-bottom-width},0a20,20 0 0 0 20,-20l0,-220l-60,-60zm0,0m{frame-top-width},0l60,60l-60,0l0,-60z" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x2="240" transform="translate(0 200)" fill="none" stroke="currentColor" stroke-linecap="butt" stroke-linejoin="round" stroke-width="5"></line>
                                        </g>
                                    </svg>
                                </li>
                                <li data-id="4" <?php echo ((intval($svgIconParams['svg-frame']) === 4) ? 'class="selected"' : ''); ?>>
                                    <svg width="100" height="100" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(74 44)">
                                            <rect width="240" height="300" rx="0" transform="translate(0.606)" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="5"></rect>
                                            <line x2="240" transform="translate(0 200)" fill="none" stroke="currentColor" stroke-linecap="butt" stroke-linejoin="round" stroke-width="5"></line>
                                        </g>
                                    </svg>
                                </li>
                                <li data-id="5" <?php echo ((intval($svgIconParams['svg-frame']) === 5) ? 'class="selected"' : ''); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 400 400">
                                        <g fill="none" stroke-width="5" stroke-dasharray="60">
                                            <circle cx="200" cy="200" r="140" fill="none" stroke="currentColor" />
                                        </g>
                                    </svg>
                                </li>
                                <li data-id="6" <?php echo ((intval($svgIconParams['svg-frame']) === 6) ? 'class="selected"' : ''); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 400 400">
                                        <g fill="none" stroke-width="5">
                                            <circle cx="200" cy="200" r="140" fill="none" stroke="currentColor"/>
                                        </g>
                                    </svg>
                                </li>
                                <li data-id="7" <?php echo ((intval($svgIconParams['svg-frame']) === 7) ? 'class="selected"' : ''); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 400 400">
                                        <g fill="none" stroke-width="5" transform="translate(60 240)">
                                            <rect x="0" y="0" width="280" height="80" rx="40" fill="none" stroke="currentColor"/>
                                        </g>
                                    </svg>
                                </li>
                                <li data-id="8" <?php echo ((intval($svgIconParams['svg-frame']) === 8) ? 'class="selected"' : ''); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 400 400">
                                        <g transform="translate(60 240)">
                                            <line x2="280" fill="none" stroke="currentColor" stroke-width="5"/>
                                        </g>
                                    </svg>
                                </li>
                            </ul>
                            <input type="hidden" name="svg-frame" value="<?php echo esc_attr($svgIconParams['svg-frame']); ?>" />
                        </div>
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderColor('frame-color', $svgIconParams['frame-color'], esc_html__('Frame color', 'wpfd')); ?>
                        </div>
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderSlider('frame-width', esc_html__('Size', 'wpfd'), 'px', esc_attr($svgIconParams['frame-width']), 0, 400); ?>
                            <?php wpfdRenderSlider('frame-stroke', esc_html__('Thickness', 'wpfd'), 'px', esc_attr($svgIconParams['frame-stroke'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- .Frame setting -->

            <!-- Wrapper -->
            <div class="wpfd-card wpfd-collapse" data-close-group="icon,extension_name,frame_settings" data-collapse-name="wrapper">
                <div class="wpfd-card-header">
                    <span class="material-icons wpfd-collapse--icon">expand_less</span>
                    <span class="wpfd-flex wpfd-flex-column">
                        <span class="card-title"><?php esc_html_e('Wrapper', 'wpfd'); ?></span>
                        <span class="card-description"><?php esc_html_e('Content of the icon', 'wpfd'); ?></span>
                    </span>
                    <div class="ju-switch-button">
                        <?php esc_html_e('Visible', 'wpfd'); ?>
                        <label class="switch">
                            <?php $checked = intval($svgIconParams['wrapper-active']) === 1 ? 'checked="checked"' : ''; ?>
                            <input type="checkbox" name="ref_wrapper-active" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
                            <span class="slider"></span>
                        </label>
                        <input type="hidden" name="wrapper-active" value="<?php echo esc_attr($svgIconParams['wrapper-active']); ?>"/>
                    </div>
                </div>
                <div style="display:none" class="wpfd-card-body">
                    <div class="wpfd-flex">
                        <div class="wpfd-col-flex flex-full">
                            <h3 class="wpfd-sub-title"><?php esc_html_e('Border', 'wpfd'); ?></h3>
                        </div>
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderSlider('border-radius', esc_html__('Border radius', 'wpfd'), '%', esc_attr($svgIconParams['border-radius'])); ?>
                            <?php wpfdRenderColor('border-color', esc_attr($svgIconParams['border-color']), esc_html__('Border color', 'wpfd')); ?>
                        </div>
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderSlider('border-size', esc_html__('Border size', 'wpfd'), 'px', esc_attr($svgIconParams['border-size']), 0, 30); ?>
                        </div><div class="wpfd-col-flex flex-full">
                            <h3 class="wpfd-sub-title"><?php esc_html_e('Background', 'wpfd'); ?></h3>
                        </div>
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderColor('background-color', esc_attr($svgIconParams['background-color']), esc_html__('Background color', 'wpfd')); ?>
                        </div>
                        <div class="wpfd-col-flex flex-full">
                            <h3 class="wpfd-sub-title"><?php esc_html_e('Shadow', 'wpfd'); ?></h3>
                        </div>
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderSlider('vertical-position', esc_html__('Vertical position', 'wpfd'), 'px', esc_attr($svgIconParams['vertical-position'])); ?>
                            <?php wpfdRenderSlider('blur-radius', esc_html__('Blur radius', 'wpfd'), 'px', esc_attr($svgIconParams['blur-radius']), 0, 20); ?>
                            <?php wpfdRenderColor('shadow-color', esc_attr($svgIconParams['blur-radius']), esc_html__('Shadow color', 'wpfd')); ?>
                        </div>
                        <div class="wpfd-col-flex flex-half">
                            <?php wpfdRenderSlider('horizontal-position', esc_html__('Horizontal position', 'wpfd'), 'px', esc_attr($svgIconParams['horizontal-position'])); ?>
                            <?php wpfdRenderSlider('spread-radius', esc_html__('Spread radius', 'wpfd'), 'px', esc_attr($svgIconParams['spread-radius'])); ?>

                        </div>
                    </div>
                </div>
            </div>
            <!-- .Wrapper -->
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
return $content;


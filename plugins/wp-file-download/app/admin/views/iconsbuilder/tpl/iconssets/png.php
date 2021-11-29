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
<div class="wpfd-icons-header">
    <input type="text" placeholder="<?php esc_html_e('Search for file type, ex: xls', 'wpfd'); ?>" data-search="png-table"  class="js-typeFilter-trigger ju-input" />
    <div class="ju-switch-button" title="<?php esc_html_e('The icons set use in themes', 'wpfd'); ?>">
        <?php esc_html_e('Set as default', 'wpfd'); ?>
        <label class="switch">
            <?php $checked = ($currentIconSet === 'png') ? 'checked="checked"' : ''; ?>
            <input type="checkbox" name="ref_active_png_set" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?>/>
            <span class="slider"></span>
        </label>
        <input type="hidden" name="active_png_set" value="<?php echo ($currentIconSet === 'png') ? '1' : '0'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK  ?>"/>
    </div>
</div>
<div class="wpfd-scroll-wrapper">
    <table data-icon-set="png" data-search-ref="png-table" class="wpfd-icons-table">
<thead>
    <tr>
        <th class="wpfd-extension"><?php esc_html_e('TYPE', 'wpfd'); ?></th>
        <th class="wpfd-default-icon"><?php esc_html_e('ICON', 'wpfd'); ?></th>
        <th class="wpfd-uploaded-icon"><?php esc_html_e('OVERIDE ICON', 'wpfd'); ?></th>
        <th class="wpfd-icon-size"><?php esc_html_e('SIZE', 'wpfd'); ?></th>
        <th class="wpfd-actions"><?php esc_html_e('ACTION', 'wpfd'); ?></th>
    </tr>
</thead>
<tbody>
<?php
if (isset($extensions['png']) && count($extensions['png'])) :
    foreach ($extensions['png'] as $extension => $icon) : ?>
        <?php if ($icon) : ?>
        <tr data-extension="<?php echo esc_attr($extension); ?>">
            <td class="wpfd-extension">.<?php echo esc_attr($extension); ?></td>
            <td class="wpfd-default-icon">
                <?php if (isset($icon['default']) && $icon['default'] !== '') : ?>
                    <img width="70" src="<?php echo esc_url($icon['default']); ?>" />
                <?php endif; ?>
            </td>
            <td class="wpfd-uploaded-icon">
                <?php if (isset($icon['uploaded']) && $icon['uploaded'] !== '') : ?>
                    <img width="70" src="<?php echo esc_url($icon['uploaded']); ?>" />
                <?php endif; ?>
            </td>
            <td class="wpfd-icon-size">N/A</td>
            <td class="wpfd-actions">
                <button data-action="upload" data-icon-set="png" data-extension="<?php echo esc_attr($extension); ?>" class="js-PngUpload-trigger ju-button ju-button-sm gray-outline-button">
                    <?php esc_html_e('Upload Icon', 'wpfd'); ?>
                </button>
                <?php
                $style = '';
                if (isset($icon['uploaded']) && $icon['uploaded'] === '') :
                    $style = ' style="display:none"';
                endif; ?>
                <button<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?> data-action="delete" data-icon-set="png" data-extension="<?php echo esc_attr($extension); ?>" class="js-PngDelete-trigger ju-button ju-button-sm gray-outline-button">
                    <?php esc_html_e('Delete overide', 'wpfd'); ?>
                </button>
            </td>
        </tr>
        <?php endif; ?>
    <?php endforeach;
endif;
?>
<?php if (isset($missingExtension['png']) && count($missingExtension['png'])) : ?>
    <?php foreach ($missingExtension['png'] as $extension => $icon) : ?>
        <?php if ($icon) : ?>
        <tr data-extension="<?php echo esc_attr($extension); ?>">
            <td class="wpfd-extension">.<?php echo esc_attr($extension); ?></td>
            <td class="wpfd-default-icon">
                <?php if (isset($icon['default']) && $icon['default'] !== '') : ?>
                    <img width="70" src="<?php echo esc_url($unknownPng['default']); ?>" />
                <?php endif; ?>
            </td>
            <td class="wpfd-uploaded-icon">
                <?php if (isset($icon['uploaded']) && $icon['uploaded'] !== '') : ?>
                    <img width="70" src="<?php echo esc_url($unknownPng['uploaded']); ?>" />
                <?php endif; ?>
            </td>
            <td class="wpfd-icon-size">N/A</td>
            <td class="wpfd-actions">
                <button data-action="upload" data-icon-set="png" data-extension="<?php echo esc_attr($extension); ?>" class="js-PngUpload-trigger ju-button ju-button-sm gray-outline-button">
                    <?php esc_html_e('Upload Icon', 'wpfd'); ?>
                </button>
                <?php
                $style = '';
                if (isset($icon['uploaded']) && $icon['uploaded'] === '') :
                    $style = ' style="display:none"';
                endif; ?>
                <button<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK ?> data-action="delete" data-icon-set="png" data-extension="<?php echo esc_attr($extension); ?>" class="js-PngDelete-trigger ju-button ju-button-sm gray-outline-button">
                    <?php esc_html_e('Delete overide', 'wpfd'); ?>
                </button>
            </td>
        </tr>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
<div class="wpfd-big-card file-unknown png">
    <table class="wpfd-icons-table">
        <tbody>
            <tr data-extension="unknown">
                <td class="wpfd-extension">
                    <div class="wpfd-big-card--heading">
                        <h3><?php esc_html_e('FILE UNKNOWN', 'wpfd'); ?></h3>
                        <p><?php esc_html_e('This icon will be automatically used when no icon is available for file type', 'wpfd'); ?></p>
                    </div>
                </td>
                <td class="wpfd-default-icon">
                    <?php if (isset($unknownPng['default']) && $unknownPng['default'] !== '') : ?>
                        <img width="70" src="<?php echo esc_url($unknownPng['default']); ?>" />
                    <?php endif; ?>
                </td>
                <td class="wpfd-uploaded-icon">
                    <?php if (isset($unknownPng['uploaded']) && $unknownPng['uploaded'] !== '') : ?>
                        <img width="70" src="<?php echo esc_url($unknownPng['uploaded']); ?>" />
                    <?php endif; ?>
                </td>
                <td class="wpfd-icon-size">N/A</td>
                <td class="wpfd-actions">
                    <button data-action="upload" data-icon-set="png" data-extension="unknown" class="js-PngUpload-trigger ju-button ju-button-sm gray-outline-button">
                        <?php esc_html_e('Upload Icon', 'wpfd'); ?>
                    </button>
                    <?php
                    $style = '';
                    if (isset($unknownPng['uploaded']) && $unknownPng['uploaded'] === '') :
                        $style = ' style="display:none"';
                    endif; ?>
                    <button<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's OK?> data-action="delete" data-icon-set="png" data-extension="unknown" class="js-PngDelete-trigger ju-button ju-button-sm gray-outline-button">
                        <?php esc_html_e('Delete overide', 'wpfd'); ?>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
return $content;

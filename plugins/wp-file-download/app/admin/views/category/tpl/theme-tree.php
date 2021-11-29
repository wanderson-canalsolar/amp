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
<fieldset id="category-layout">
    <legend><?php esc_html_e('Category layout', 'wpfd'); ?></legend>
    <?php
    $this->renderSwitcher('showcategorytitle', esc_html__('Show category title', 'wpfd'));
    $this->renderSwitcher('showsubcategories', esc_html__('Show subcategories', 'wpfd'));
    $this->renderSwitcher('download_popup', esc_html__('Download popup', 'wpfd'));
    ?>
</fieldset>
<fieldset id="file-layout">
    <legend><?php esc_html_e('File layout', 'wpfd'); ?></legend>
    <?php
    $this->renderSwitcher('showtitle', esc_html__('Show title', 'wpfd'));
    $this->renderText('croptitle', esc_html__('Crop titles', 'wpfd'));
    $this->renderSwitcher('showdescription', esc_html__('Show description', 'wpfd'));
    $this->renderSwitcher('showsize', esc_html__('Show file size', 'wpfd'));
    $this->renderSwitcher('showversion', esc_html__('Show version', 'wpfd'));
    $this->renderSwitcher('showhits', esc_html__('Show hits', 'wpfd'));
    $this->renderSwitcher('showdownload', esc_html__('Show download link', 'wpfd'));
    $this->renderSwitcher('showdateadd', esc_html__('Show date added', 'wpfd'));
    $this->renderSwitcher('showdatemodified', esc_html__('Show date modified', 'wpfd'));
    $this->renderColor('bgdownloadlink', esc_html__('Background download link', 'wpfd'));
    $this->renderColor('colordownloadlink', esc_html__('Color download link', 'wpfd'));
    ?>
</fieldset>


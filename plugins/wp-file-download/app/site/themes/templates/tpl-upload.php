<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0.3
 */

defined('ABSPATH') || die();
?>
<div class="file-upload-content clearfix row-fluid wpfdUploadForm-<?php echo esc_attr($formId); ?>" style="display: block;">
    <div class="wpreview border jsWpfdFrontUpload">
        <div class="file-upload-top clearfix">
            <div class="pull-center"><strong><?php esc_html_e('File Upload', 'wpfd'); ?></strong></div>
        </div>
        <div id="preview" class="wpfd_preview has-wpfd ui-sortable">
            <div class="wpdf_dropbox">
                <span class="message"><?php esc_html_e('Drag & Drop your Document here', 'wpfd'); ?></span>
                <input class="hide upload_input" type="file" id="upload_input<?php echo esc_attr($formId); ?>" multiple="">
                <input type="hidden" id="id_category" name="id_category" value="<?php echo esc_attr($category_id); ?>">
                <span href="#" id="upload_button" class="button button-primary button-big">
                    <?php esc_html_e('Select files', 'wpfd'); ?>
                </span>
            </div>
            <div class="clr"></div>
        </div>
    </div>
    <div id="mybootstrap" style="margin:0"></div>
</div>

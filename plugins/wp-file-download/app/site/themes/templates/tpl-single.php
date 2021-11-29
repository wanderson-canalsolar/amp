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

if (!empty($file)) :
    $fileTitle = isset($file->description) ? wp_strip_all_tags($file->description) : $file->title;
    $isProduct = isset($file->show_add_to_cart) ? $file->show_add_to_cart : false;
    ?>

    <div class="wpfd-file wpfd-single-file" data-file="<?php echo esc_attr($file->ID); ?>">
        <div class="wpfd-file-link">
            <?php
            if ($isProduct) :
                $data = isset($file->product_id) ? ' data-product_id="' . esc_attr($file->product_id) . '"' : '';
                ?>
            <a class="wpfd_downloadlink" href="<?php echo esc_url($file->linkdownload) ?>" <?php echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above?>>
                <i style="font-size: 50px;" class="zmdi zmdi-shopping-cart-plus wpfd-add-to-cart"></i>
            </a>
            <?php else : ?>
            <a class="noLightbox wpfd-single-file-downloadicon"
               href="<?php echo esc_url($file->linkdownload) ?>"
               data-id="<?php echo esc_attr($file->ID); ?>"
               title="<?php echo esc_html($fileTitle); ?>">
                <img src="<?php echo esc_url(WPFD_PLUGIN_URL . 'app/site/assets/images/theme/download.png'); ?>" />
            </a>
            <?php endif; ?>
            <a class="noLightbox"
               href="<?php echo esc_url($file->linkdownload) ?>"
               data-id="<?php echo esc_attr($file->ID); ?>"
               title="<?php echo esc_html($fileTitle); ?>">
                <span class="droptitle">
                    <?php echo esc_html($nameDisplay); ?>
                </span>
                <br/>
                <?php
                /**
                 * Action fire before file info in single file display
                 *
                 * @param object File object
                 */
                do_action('wpfd_before_single_file_info', $file);
                ?>
                <span class="dropinfos">
                    <?php if ($showsize) : ?>
                        <b><?php esc_html_e('Size', 'wpfd'); ?>: </b>
                        <?php echo esc_html((strtolower($file->size) === 'n/a' || $file->size <= 0) ? 'N/A' : WpfdHelperFile::bytesToSize($file->size)); ?>
                    <?php endif; ?>
                    <b><?php esc_html_e('Format', 'wpfd'); ?> : </b>
                    <?php echo esc_html(strtoupper($file->ext)); ?>
                </span>
                <?php
                /**
                 * Action fire after file info in single file display
                 *
                 * @param object File object
                 */
                do_action('wpfd_after_single_file_info', $file);
                ?>
            </a>
            <div class="wpfd_single_footer">
                <?php if ($isProduct) : ?>
                <a class="wpfd_single_add_to_cart wpfd_downloadlink" href="<?php echo esc_url($file->linkdownload); ?>" <?php echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above ?>>
                    <?php esc_html_e('Add to cart', 'wpfd'); ?> <i class="zmdi zmdi-shopping-cart-plus wpfd-add-to-cart"></i>
                </a>
                <a class="wpfd_single_view_product" href="<?php echo esc_url($file->viewerlink); ?>" target="_blank">
                    <?php esc_html_e('View product', 'wpfd'); ?>
                </a>
                <?php else : ?>
                    <?php if (isset($file->openpdflink)) {
                        $classes = array();
                        $target = '';
                        if ($previewType === 'lightbox') {
                            $classes[] = 'wpfdlightbox';
                        } elseif ($previewType === 'tab') {
                            $target = ' target="_blank"';
                        } else {
                            $classes[] = 'noLightbox';
                        }
                        $classes = implode(' ', $classes);
                        ?>
                        <a href="<?php echo esc_url($file->openpdflink); ?>" class="<?php echo esc_attr($classes); ?>"<?php echo $target; // phpcs:ignore?>>
                            <?php esc_html_e('Preview', 'wpfd'); ?> </a>
                    <?php } elseif (isset($file->viewerlink)) { ?>
                        <?php
                        $classes = array('openlink', 'wpfd_previewlink');
                        $target = '';
                        if ($previewType === 'lightbox') {
                            $classes[] = 'wpfdlightbox';
                        } elseif ($previewType === 'tab') {
                            $target = ' target="_blank"';
                        }

                        $classes = implode(' ', $classes);
                        ?>
                        <a data-id="<?php echo esc_attr($file->ID); ?>" data-catid="<?php echo esc_attr($file->catid); ?>"
                           data-file-type="<?php echo esc_attr($file->ext); ?>"
                           class="<?php echo esc_attr($classes); ?>"
                           href="<?php echo esc_url($file->viewerlink); ?>"<?php echo $target; // phpcs:ignore?>>
                            <?php esc_html_e('Preview', 'wpfd'); ?></a>
                    <?php } ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

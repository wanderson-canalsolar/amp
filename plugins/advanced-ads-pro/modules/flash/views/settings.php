<input name="<?php echo Advanced_Ads_Pro::OPTION_KEY; ?>[flash][enabled]" id="advanced-ads-pro-flash-enabled" type="checkbox" value="1" <?php checked( $check ); ?> />
<label for="advanced-ads-pro-flash-enabled" class="description"><?php _e('Activate <em>flash</em> module.', 'advanced-ads-pro'); ?></label>
<p><?php Advanced_Ads_Pro_Admin::show_deprecated_notice( 'flash' ); ?></p>
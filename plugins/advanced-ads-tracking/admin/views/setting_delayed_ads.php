<?php
/**
 * Markup for delayed ads settings.
 */

$options = $this->plugin->options();
$use_ajax = isset( $options['delayed-ads'] )? true : false;

?>
<label><input type="checkbox" value="1" name="<?php echo $this->plugin->options_slug; ?>[delayed-ads]" <?php checked( $use_ajax ); ?> /></label>
<p class="description"><?php esc_html_e( 'Tracks delayed ads only when they show up. This applies to ads set up with the PopUp or Sticky Ads add-on using a trigger.', 'advanced-ads-tracking' ); ?></p>
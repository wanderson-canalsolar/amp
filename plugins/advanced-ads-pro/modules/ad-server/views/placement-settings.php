<?php
/**
 * Show placement related options
 *
 * @var string $public_slug URL where the ad placement can be accessed directly.
 * @var string $_placement_slug placement ID.
 */
?>
<input type="text" id="advanced-ads-pro-placement-server-slug" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][ad-server-slug]" value="<?php echo esc_attr( $public_slug ); ?>" />
<span id="advanced-ads-pro-placement-server-slug-update-message" class="advads-error-message hidden"><?php esc_html_e( 'Save the page to update the usage code below.', 'advanced-ads-pro' ); ?></span>
<p class="description"><?php esc_html_e( 'The name of the placement that appears in the URL and injection code.', 'advanced-ads-pro' ); ?></p>
<script>
    jQuery( document ).ready( function() {
        jQuery( '#advanced-ads-pro-placement-server-slug' ).change( function(){
            jQuery( '#advanced-ads-pro-placement-server-slug-update-message' ).show();
        });
    });
</script>
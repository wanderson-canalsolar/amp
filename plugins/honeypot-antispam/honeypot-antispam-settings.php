<?php
/*
Anti-spam settings code
used WordPress Settings API - http://codex.wordpress.org/Settings_API
*/

if ( ! defined( 'ABSPATH' ) ) { // Avoid direct calls to this file and prevent full path disclosure
	exit;
}


function antispam_menu() { // add menu item
	add_options_page( 'Honeypot Antispam', 'Honeypot Antispam', 'manage_options', 'honeypot-antispam', 'antispam_settings' );
}

add_action( 'admin_menu', 'antispam_menu' );


function antispam_admin_init() {
	register_setting( 'antispam_settings_group', 'antispam_settings', 'antispam_settings_validate' );

	add_settings_section( 'antispam_settings_automatic_section', '', 'antispam_section_callback', 'antispam_automatic_page' );

	add_settings_field( 'save_spam_comments', __( 'Save spam comments', 'honeypot-antispam' ), 'antispam_field_save_spam_comments_callback', 'antispam_automatic_page', 'antispam_settings_automatic_section' );

}

add_action( 'admin_init', 'antispam_admin_init' );


function antispam_settings_init() { // set default settings
	global $antispam_settings;
	$antispam_settings = antispam_get_settings();
	update_option( 'antispam_settings', $antispam_settings );
}

add_action( 'admin_init', 'antispam_settings_init' );


function antispam_settings_validate( $input ) {
	$default_settings = antispam_get_settings();

	// checkbox
	$output['save_spam_comments'] = $input['save_spam_comments'];

	return $output;
}


function antispam_section_callback() { // Anti-spam settings description
	echo '';
}


function antispam_field_save_spam_comments_callback() {
	$settings = antispam_get_settings();
	echo '<label><input type="checkbox" name="antispam_settings[save_spam_comments]" ' . checked( 1, $settings['save_spam_comments'], false ) . ' value="1" />';
	echo __( 'Save spam comments into spam section', 'honeypot-antispam' ) . '</label>';
	echo '<p class="description">' . __( 'Useful for testing how the plugin works', 'honeypot-antispam' ) . '. <a href="' . admin_url( 'edit-comments.php?comment_status=spam' ) . '">' . __( 'View spam section', 'honeypot-antispam' ) . '</a>.</p>';
}


function antispam_settings() {
	$blocked_total  = 0; // show 0 by default
	$antispam_stats = get_option( 'antispam_stats', array() );
	if ( isset( $antispam_stats['blocked_total'] ) ) {
		$blocked_total = $antispam_stats['blocked_total'];
	}
	?>
    <div class="wrap">

        <h2><span class="dashicons dashicons-admin-generic"></span> Honeypot Antispam</h2>

        <div class="antispam-panel-info">
            <p style="margin: 0;">
                <span class="dashicons dashicons-chart-bar"></span>
                <strong><?php echo $blocked_total; ?></strong> <?php echo __( 'spam comments were blocked by', 'honeypot-antispam' ); ?>
                <a href="https://wordpress.org/plugins/anti-spam/" target="_blank">Honeypot
                    Antispam</a> <?php echo __( 'plugin so far', 'honeypot-antispam' ); ?>.
            </p>
        </div>

        <form method="post" action="options.php">
			<?php settings_fields( 'antispam_settings_group' ); ?>
            <div class="antispam-group-automatic">
				<?php do_settings_sections( 'antispam_automatic_page' ); ?>
            </div>
			<?php submit_button(); ?>
        </form>

    </div>
	<?php
}

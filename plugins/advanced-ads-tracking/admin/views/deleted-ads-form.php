<?php

if ( !current_user_can( advanced_ads_tracking_db_cap() ) ) {
	return;
}

$dbop_page_url = admin_url( 'admin.php?page=advads-tracking-db-page' );
$log_file = WP_CONTENT_DIR . '/advanced-ads-tracking.csv';
$delete_debug_link = admin_url( 'admin.php?page=advads-tracking-db-page&delete-debug-nonce=' . $_request['delete-debug-nonce'] );

$fs_method = get_filesystem_method();

if ( 'direct' == $fs_method ) {
	
	unlink( $log_file );
	echo '<script type="text/javascript">document.location.href = "' . $dbop_page_url . '";</script>';
	
} else {
	
	$_POST['delete-debug-nonce'] = $_request['delete-debug-nonce'];
	$extra_fields = array( 'delete-debug-nonce' );
	$method = '';
	
	echo '<style type="text/css">';
	include AAT_BASE_PATH . 'admin/assets/css/filesystem-form.css';
	echo '</style>';
	
	if ( false === ( $creds = request_filesystem_credentials( $delete_debug_link, $method, false, false, $extra_fields ) ) ) {
		return;
	}
	
	if ( ! WP_Filesystem($creds) ) {
		// our credentials were no good, ask the user for them again
		request_filesystem_credentials( $delete_debug_link, $method, false, false, $extra_fields );
		return;
	}
	
	global $wp_filesystem;
	$log_file = trailingslashit( $wp_filesystem->wp_content_dir() ) . 'advanced-ads-tracking.csv';
	if ( ! $wp_filesystem->delete( $log_file ) ) {
		_e( 'Failing to delete the log file.', 'advanced-ads-tracking' );
	} else {
		echo '<script type="text/javascript">document.location.href = "' . $dbop_page_url . '";</script>';
	}
	
}

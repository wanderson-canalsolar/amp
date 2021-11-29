<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
if ( !current_user_can( advanced_ads_tracking_db_cap() ) ) {
	return;
}

$_request = wp_unslash( $_REQUEST );
if ( isset( $_request['delete-debug-nonce'] ) ) {
	if ( false !== wp_verify_nonce( $_request['delete-debug-nonce'], 'delete-debug-log' ) ) {
		if ( file_exists( WP_CONTENT_DIR . '/advanced-ads-tracking.csv' ) ) {
			require_once AAT_BASE_PATH . 'admin/views/deleted-ads-form.php';
			return;
		}
	}
}

$vars = array(
	'nonce' => wp_create_nonce( 'advads_tracking_dbop' ),
	'adminImageUrl' => admin_url( '/images/' ),
);
$impressions_table = Advanced_Ads_Tracking_Util::get_instance()->get_impression_table();
$clicks_table = Advanced_Ads_Tracking_Util::get_instance()->get_click_table();
$db_size = Advanced_Ads_Tracking_Dbop::get_instance()->get_db_size();
Advanced_Ads_Tracking_Dbop::get_instance()->db_size_transient( $db_size );
$date_format = get_option( 'date_format' );

$db_warn = array();
if ( get_transient( Advanced_Ads_Tracking_Dbop::SIZE_TRANS ) ) {
	$db_warn[] = Advanced_Ads_Tracking_Dbop::get_instance()->get_warning_message();
}
$deleted_ads = Advanced_Ads_Tracking_Dbop::get_instance()->get_deleted_ads();

$debug_option = get_option( Advanced_Ads_Tracking_Util::DEBUG_OPT, false );

$debug_ad = false;
$debug_time = array(
	'hours' => 0,
	'mins' => 0,
);

if ( $debug_option ) {
	$rem_time = $debug_option['time'] + ( 24 * 3600 ) - time() ;
	$debug_time['hours'] = floor( $rem_time / 3600 );
	$debug_time['mins'] = floor( ( $rem_time - ( 3600 * $debug_time['hours'] ) ) / 60 );
}

if ( $debug_option && is_numeric( $debug_option['id'] ) ) {
	$debug_ad = get_post( $debug_option['id'] );
}

?><style type="text/css">
#db-warnings { background:#fff; border-left:4px solid #fff; box-shadow:0 1px 1px 0 rgba(0,0,0,.1);padding:1px 12px;border-left-color:#dc3232;margin: 5px 0 15px;}
#db-warnings > p {margin:.5em 0;padding:2px;}
.dbop-spinner { vertical-align: middle }
</style>
<script type="text/javascript">
var advadsTrackingDbopVars = <?php echo json_encode( $vars ); ?>;
</script>
<div class="wrap">
	<div id="dbop-modal"></div>
	<h1><?php _e( 'Tracking database', 'advanced-ads-tracking' ); ?></h1>
	<table class="widefat">
		<thead><tr>
			<th><?php _e( 'Table', 'advanced-ads-tracking' ); ?></th>
			<th><?php _e( 'Row count', 'advanced-ads-tracking' ); ?></th>
			<th><?php _e( 'Data size ( in kilobytes )', 'advanced-ads-tracking' ); ?></th>
			<th><?php _e( 'Oldest record', 'advanced-ads-tracking' ); ?></th>
		</tr></thead>
		<?php if ( $db_size['first_impression'] ) : ?>
		<tfoot><tr><th colspan="4" style="background-color:#fffcdd;color:#ff541e;text-align:center;"><strong><?php _e( 'Always perform a backup of your stats tables before performing any of the operations on this page.', 'advanced-ads-tracking' ); ?></strong></th></tr></tfoot>
		<?php endif; ?>
		<tbody>
		<tr class="alternate">
			<td><strong><?php _e( 'impressions', 'advanced-ads-tracking' ); ?></strong>&nbsp;(<code><?php echo $impressions_table; ?></code>)</td>
			<td <?php if ( Advanced_Ads_Tracking_Dbop::row_count_limit() && Advanced_Ads_Tracking_Dbop::row_count_limit() < $db_size['impression_row_count'] ) echo 'style="color:red;"' ?>><?php echo $db_size['impression_row_count']; ?></td>
			<td><?php echo $db_size['impression_in_kb']; ?></td>
			<td><code><?php echo ( $db_size['first_impression'] )? date_i18n( $date_format, $db_size['first_impression'] ) : 'N/A'; ?></code></td>
		</tr>
		<tr>
			<td><strong><?php _e( 'clicks', 'advanced-ads-tracking' ); ?></strong>&nbsp;(<code><?php echo $clicks_table; ?></code>)</td>
			<td <?php if ( Advanced_Ads_Tracking_Dbop::row_count_limit() && Advanced_Ads_Tracking_Dbop::row_count_limit() < $db_size['click_row_count'] ) echo 'style="color:red;"' ?>><?php echo $db_size['click_row_count']; ?></td>
			<td><?php echo $db_size['click_in_kb']; ?></td>
			<td><code><?php echo ( $db_size['first_click'] )? date_i18n( $date_format, $db_size['first_click'] ) : 'N/A'; ?></code></td>
		</tr>
		</tbody>
	</table>
	
	<?php if ( $db_size['first_impression'] ) : ?>
		<?php
		if ( $db_warn ) {
			echo '<br /><div id="db-warnings">';
			foreach( $db_warn as $message ) {
				echo "<p><i class='dashicons dashicons-info'></i> $message</p>";
			}
			echo '</div>';
		}
		$compress_periods_args = array(
			'custom' => false,
			'period-options' => Advanced_Ads_Tracking_Dbop::get_instance()->get_compress_periods(),
		);
		$export_periods_args = array(
			'period-options' => Advanced_Ads_Tracking_Dbop::get_instance()->get_export_periods(),
		);
		?>
		<br/>
		<div class="form-wrap">
			<label><strong><?php _e( 'Export stats', 'advanced-ads-tracking' ); ?></strong></label>
			<div class="form-field">
				<form id="export-stats-form" action="<?php echo admin_url( 'admin.php?page=advads-tracking-db-page' ) ?>" method="post">
				<?php Advanced_Ads_Tracking_Dbop::period_select_inputs( $export_periods_args ); ?>
				<button class="button button-primary"><?php _e( 'download', 'advanced-ads-tracking' ); ?></button>
				</form>
				<p class="description"><?php _e( 'Export stats as CSV so you can review them later by uploading the file.', 'advanced-ads-tracking' ); ?></p>
				<p class="description advads-error-message" id="export-period-error" style="display:none;"><?php _e( 'The period you have chosen is not consistent', 'advanced-ads-tracking' ); ?></p>
			</div>
		</div>
		<?php
		$remove_periods_args = array(
			'custom' => false,
			'period-options' => Advanced_Ads_Tracking_Dbop::get_instance()->get_remove_periods(),
		);
		?>
		<div class="form-wrap">
			<form id="remove-stats-form" action="<?php echo admin_url( 'admin.php?page=advads-tracking-db-page' ) ?>" method="post">
			<label><strong><?php _e( 'Remove old stats', 'advanced-ads-tracking' ); ?></strong></label>
			<div class="form-field">
				<?php Advanced_Ads_Tracking_Dbop::period_select_inputs( $remove_periods_args ); ?>
				<button class="button button-primary"><?php _e( 'remove', 'advanced-ads-tracking' ); ?></button>
				</form>
				<p class="description"><?php _e( 'Remove old stats to reduce the size of the database.', 'advanced-ads-tracking' ); ?></p>
				<p id="remove-error-notice" class="advads-error-message"></p>
			</div>
		</div>
		<div class="form-wrap">
			<label><strong><?php _e( 'Reset Stats', 'advanced-ads-tracking' ); ?></strong></label>
			<div class="form-field">
				<form id="reset-stats-form" action="<?php echo admin_url( 'admin.php?page=advads-tracking-db-page' ) ?>" method="post">
					<?php $all_ads = Advanced_Ads::get_ads( array( 'post_status' => array( 'publish', 'future', 'draft', 'pending' ), 'orderby' => 'title', 'order' => 'ASC' ) ); ?>
					<select id="reset-stats-adID">
					<?php if ( !empty( $all_ads ) ) : ?>
						<option value=""><?php _e('(please choose the ad)', 'advanced-ads-tracking'); ?></option>
					<?php endif; ?>
						<option value="all-ads"><?php _e( '--all ads--', 'advanced-ads-tracking' ); ?></option>
					<?php if ( ! empty( $deleted_ads['impressions'] ) || ! empty( $deleted_ads['impressions'] ) ) : ?>
						<option value="deleted-ads"><?php esc_html_e( '--deleted ads--', 'advanced-ads-tracking' ); ?></option>
					<?php endif; ?>
					<?php foreach ( $all_ads as $ad ) : ?>
							<option value="<?php echo $ad->ID; ?>"><?php echo $ad->post_title; ?></option>
					<?php endforeach; ?>
					</select>
					<button class="button button-primary"><?php _e( 'reset', 'advanced-ads-tracking' ); ?></button>
				</form>
				<p class="description"><?php _e( 'Use this form to remove the stats for one or all ads.', 'advanced-ads-tracking' ); ?></p>
				<p id="reset-error-notice" class="advads-error-message"></p>
			</div>
		</div>

		<?php $all_ads = Advanced_Ads::get_ads( array( 'post_status' => array( 'publish' ), 'orderby' => 'title', 'order' => 'ASC' ) ); ?>
		<?php if ( !empty( $all_ads ) ) : ?>
		
		<div class="form-wrap">
			<label><strong><?php _e( 'Debug mode', 'advanced-ads-tracking' ); ?></strong></label>
			<div class="form-field">
				<form id="debug-mode-form" action="<?php echo admin_url( 'admin.php?page=advads-tracking-db-page' ) ?>" method="post">
					
					<?php if ( $debug_option ) : ?>
						
						<?php if ( true === $debug_option['id'] ) {
							$the_ad = esc_html__( '--all ads--', 'advanced-ads-tracking' );
						} else {
							$the_ad = '"' . $debug_ad->post_title . '"';
						}
						?>
						
						<p><strong style="color:#ff5c1e;"><?php
							esc_html( printf(
								__( 'Debugging %s for another %s hour(s) %s minutes(s).', 'advanced-ads-tracking' ),
								$the_ad,
								$debug_time['hours'],
								$debug_time['mins']
							) );
						?></strong></p>
						<input type="hidden" id="debug-mode-adID" value="cancel" />
						<button class="button button-secondary"><?php _e( 'disable', 'advanced-ads-tracking' ); ?></button>
						
					<?php else : ?>
					
						<select id="debug-mode-adID">
							<option value="all"><?php _e( '--all ads--', 'advanced-ads-tracking' ); ?></option>
							<?php foreach ( $all_ads as $ad ) : ?>
									<option value="<?php echo esc_attr( $ad->ID ); ?>"><?php echo esc_html( $ad->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
						<button class="button button-primary"><?php _e( 'enable', 'advanced-ads-tracking' ); ?></button>
						<p class="description"><?php _e( 'Logs more information about tracked data for 24 hours.', 'advanced-ads-tracking' ); ?></p>
						
						
					<?php endif;?>
					
				</form>
			</div>
		</div>
		
		<?php endif; ?>
		
	<?php endif; ?>
	
	<?php if ( file_exists( WP_CONTENT_DIR . '/advanced-ads-tracking.csv' ) ) : ?>
	<p><?php 
	printf(
		__( 'View the tracking %sdebug log file%s', 'advanced-ads-tracking' ),
		'<strong><a target="_blank" href="' . content_url( 'advanced-ads-tracking.csv' ). '">',
		'</a></strong>'
	);
	?>&nbsp;|&nbsp;<?php
	
	$delete_debug_nonce = wp_create_nonce( 'delete-debug-log' );
	$delete_debug_link = admin_url( 'admin.php?page=advads-tracking-db-page&delete-debug-nonce=' . $delete_debug_nonce );
	
	echo '<strong><a href="' . $delete_debug_link. '">';
	esc_html_e( 'delete the file', 'advanced-ads-tracking' );
	echo '</a></strong>';
	?></p>
	<?php Advanced_Ads_Filesystem::get_instance()->print_request_filesystem_credentials_modal(); ?>
	<?php endif; ?>
	
	<iframe frameborder="0" hspace="0" src="" id="stats-download-frame" style="width:1px;height:1px;"></iframe>
	<?php
	// display current time
	$timeFormat = _x('Y-m-d H:i:s', 'current time format on stats page', 'advanced-ads-tracking');
	$timeWp = get_date_from_gmt(gmdate('Y-m-d H:i:s'), $timeFormat);
	$util = Advanced_Ads_Tracking_Util::get_instance();
	$timeDb = $util->get_date_from_db($util->get_timestamp(), $timeFormat);
	$timeUtc = gmdate($timeFormat);
	?>
	<h2><?php _e('Time setup', 'advanced-ads-tracking'); ?></h2>
	<p><?php printf(__('If you notice a shift between your own time and stats, please check if the highlighted time is your local time. If not, please check if your <a href="%s">time zone</a> is set correctly.', 'advanced-ads-tracking'), admin_url('/options-general.php')); ?></p>
	<div class="advanaced-ads-stats-time"><ul>
	<li><strong><?php echo $timeWp; ?> (WordPress)</strong></li>
	<li><span><?php echo $timeUtc; ?> (UTC)</span></li>
	<li><span><?php echo $timeDb; ?> (DB)</span></li>
	</ul></div>
</div>
<style type="text/css">
#dbop-modal {
	background-color: rgba( 255, 255, 255, .7);
	position: absolute;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	display: none;
}
</style>
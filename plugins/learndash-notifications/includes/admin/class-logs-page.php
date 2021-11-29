<?php
if ( class_exists( 'LearnDash_Settings_Page' ) ) :
	class LearnDash_Notifications_Logs_Page extends LearnDash_Settings_Page {
		public function __construct() {
			$this->parent_menu_page_url  = 'edit.php?post_type=ld-notification';
			$this->menu_page_capability  = LEARNDASH_ADMIN_CAPABILITY_CHECK;
			$this->settings_page_id      = 'ld-notifications-logs';
			$this->settings_page_title   = __( 'LearnDash Notifications Logs', 'learndash-notifications' );
			$this->settings_tab_title    = __( 'Logs', 'learndash-notifications' );
			$this->settings_tab_priority = 4;
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_tools_scripts' ) );
			$this->show_settings_page_function = array( $this, 'show_settings_page' );
			add_action( 'wp_loaded', array( &$this, 'empty_logs' ) );
			parent::__construct();
		}

		public function enqueue_tools_scripts() {
			$screen = get_current_screen();

			if ( $screen->id != 'admin_page_ld-notifications-logs' ) {
				return;
			}

			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_style( 'jquery-ui', LEARNDASH_NOTIFICATIONS_PLUGIN_URL . 'assets/css/jquery-ui.min.css' );
			wp_enqueue_style( 'ld-notifications-logs', LEARNDASH_NOTIFICATIONS_PLUGIN_URL . 'assets/css/logs.css' );
		}

		/**
		 * Empty a log or all
		 */
		public function empty_logs() {
			if ( ! current_user_can( LEARNDASH_ADMIN_CAPABILITY_CHECK ) ) {
				return;
			}

			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ld_notifications_clear_logs' ) ) {
				return;
			}

			$trigger = isset( $_POST['trigger'] ) ? $_POST['trigger'] : '*';
			$log_dir = wp_upload_dir( null, true );
			$log_dir = $log_dir['basedir'] . DIRECTORY_SEPARATOR . 'learndash-notifications' . DIRECTORY_SEPARATOR;
			if ( $trigger === '*' ) {
				foreach ( learndash_notifications_get_triggers() as $key => $trigger ) {
					$file_name = hash( 'sha256', sanitize_file_name( $key ) . AUTH_SALT );
					if ( file_exists( $log_dir . $file_name ) ) {
						@unlink( $log_dir . $file_name );
					}
				}
			} else {
				$file_name = hash( 'sha256', sanitize_file_name( $trigger ) . AUTH_SALT );
				if ( file_exists( $log_dir . $file_name ) ) {
					@unlink( $log_dir . $file_name );
				}
			}
		}

		public function show_settings_page() {
			$logs    = [];
			$log_dir = wp_upload_dir( null, true );
			$log_dir = $log_dir['basedir'] . DIRECTORY_SEPARATOR . 'learndash-notifications' . DIRECTORY_SEPARATOR;
			foreach ( learndash_notifications_get_triggers() as $key => $trigger ) {
				$file_name = hash( 'sha256', sanitize_file_name( $key ) . AUTH_SALT );
				$log       = '';
				if ( file_exists( $log_dir . $file_name ) ) {
					$log          = file_get_contents( $log_dir . $file_name );
					$logs[ $key ] = [
						'name' => $trigger,
						'log'  => $log
					];
				}
			}
			include_once LEARNDASH_NOTIFICATIONS_PLUGIN_PATH . 'templates/admin/logs-page.php';
		}
	}

	add_action( 'learndash_settings_pages_init', function () {
		LearnDash_Notifications_Logs_Page::add_page_instance();
	} );
endif;
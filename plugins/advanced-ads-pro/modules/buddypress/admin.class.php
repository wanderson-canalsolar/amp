<?php
/**
 * Class Advanced_Ads_Pro_Module_BuddyPress_Admin
 * Manage backend-facing logic for BuddyPress/BuddyBoss integration
 */
class Advanced_Ads_Pro_Module_BuddyPress_Admin {

	/**
	 * Advanced_Ads_Pro_Module_BuddyPress_Admin constructor.
	 */
	public function __construct() {
		// stop, if main plugin doesn’t exist
		if ( ! class_exists( 'Advanced_Ads', false ) ) {
			return;
		}

		// stop if BuddyPress isn't activated
		if ( ! class_exists( 'BuddyPress', false ) ) {
			return;
		}

		// add sticky placement
		add_action( 'advanced-ads-placement-types', array( $this, 'add_placement' ) );
		// content of sticky placement
		add_action( 'advanced-ads-placement-options-after', array( $this, 'placement_options' ), 10, 2 );
	}

	/**
	 * Register the BuddyPress/BuddyBoss placement
	 *
	 * @param array $types registered placement types.
	 *
	 * @return array
	 */
	public function add_placement( $types ) {
		// ad injection on a BuddyPress/BuddyBoss activity-stream
		if ( Advanced_Ads_Pro_Module_BuddyPress::is_buddyboss() ) {
			$types['buddypress'] = array(
				'title'       => __( 'BuddyBoss Content', 'advanced-ads-pro' ),
				'description' => __( 'Display ads on BuddyBoss related pages.', 'advanced-ads-pro' ),
				'image'       => AAP_BASE_URL . 'modules/buddypress/assets/img/buddyboss.png',
				'order'       => 31,
			);
		} else {
			$types['buddypress'] = array(
				'title'       => __( 'BuddyPress Content', 'advanced-ads-pro' ),
				'description' => __( 'Display ads on BuddyPress related pages.', 'advanced-ads-pro' ),
				'image'       => AAP_BASE_URL . 'modules/buddypress/assets/img/buddypress-icon.png',
				'order'       => 31,
			);
		}
		return $types;
	}

	/**
	 * Register options for the BuddyPress placement
	 *
	 * @param string $placement_slug slug of the placement.
	 * @param array  $placement options of the placement.
	 */
	public function placement_options( $placement_slug = '', $placement = array() ) {
		if ( 'buddypress' === $placement['type'] ) {
			$buddypress_positions = $this->get_buddypress_hooks();
			$current              = isset( $placement['options']['buddypress_hook'] ) ? $placement['options']['buddypress_hook'] : '';
			$index                = ( isset( $placement['options']['pro_buddypress_pages_index'] ) ) ? Advanced_Ads_Pro_Utils::absint( $placement['options']['pro_buddypress_pages_index'], 1 ) : 1;
			require AAP_BASE_PATH . 'modules/buddypress/views/position-option.php';
		}
	}

	/**
	 * Load the hooks relevant for BuddyPress/BuddyBoss
	 *
	 * @return array list of hooks for BuddyPress depending on the BP theme
	 */
	public function get_buddypress_hooks() {

		// return legacy hooks
		if ( Advanced_Ads_Pro_Module_BuddyPress::is_legacy_theme() ) {
				return array(
					__( 'Activity Entry', 'advanced-ads-pro' ) => array(
						'before activity entry',
						'activity entry content',
						'after activity entry',
						'before activity entry comments',
						'activity entry comments',
						'after activity entry comments',
					),
					__( 'Group List', 'advanced-ads-pro' ) => array(
						'directory groups item',
					),
					__( 'Member List', 'advanced-ads-pro' ) => array(
						'directory members item',
					),
				);
		} else {
			return array(
				'bp_after_activity_entry' => __( 'Stream', 'advanced-ads-pro' ),
			);
		}

	}
}


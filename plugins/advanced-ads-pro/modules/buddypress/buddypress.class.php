<?php

/**
 * Class Advanced_Ads_Pro_Module_BuddyPress
 * Add placement and visitor condition for BuddyPress/BuddyBoss users
 */
class Advanced_Ads_Pro_Module_BuddyPress {

	/**
	 * Advanced_Ads_Pro_Module_BuddyPress constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded_ad_actions' ), 20 );
		add_filter( 'advanced-ads-visitor-conditions', array( $this, 'visitor_conditions' ) );
	}

	/**
	 * Register relevant hooks
	 */
	public function wp_plugins_loaded_ad_actions() {
		// stop, if main plugin doesn’t exist
		if ( ! class_exists( 'Advanced_Ads', false ) ) {
			return;
		}

		// stop if BuddyPress isn't activated
		if ( ! class_exists( 'BuddyPress', false ) ) {
			return;
		}

		// load BuddyPress hooks

		// get placements
		$placements = get_option( 'advads-ads-placements', array() );

		if ( is_array( $placements ) ) {
			foreach ( $placements as $_placement_id => $_placement ) {
				if ( isset( $_placement['type'] ) && 'buddypress' === $_placement['type'] && isset( $_placement['options']['buddypress_hook'] ) ) {
					$hook = self::get_hook_from_placement_options( $_placement );
					add_action( $hook, array( $this, 'execute_hook' ) );
				}
			}
		}
	}

	/**
	 * Execute frontend hooks
	 */
	public function execute_hook() {
		// get placements
		$placements = get_option( 'advads-ads-placements', array() );
		// look for the current hook in the placements
		$hook = current_filter();
		if ( is_array( $placements ) ) {
			foreach ( $placements as $_placement_id => $_placement ) {
				$hook_from_option = self::get_hook_from_placement_options( $_placement );
				if ( isset( $_placement['type'] ) && 'buddypress' === $_placement['type']
				&& isset( $_placement['options']['pro_buddypress_pages_index'] )
				&& isset( $_placement['options']['buddypress_hook'] )
				&& $hook === $hook_from_option ) {
					if ( did_action( $hook ) == $_placement['options']['pro_buddypress_pages_index'] ) {
						the_ad_placement( $_placement_id );
					}
				}
			}
		}
	}

	/**
	 * Add visitor condition for BuddyPress profile fields
	 *
	 * @param array $conditions visitor conditions of the main plugin.
	 * @return array $conditions new global visitor conditions
	 */
	public function visitor_conditions( $conditions ) {

		// stop if BuddyPress isn't activated
		if ( ! class_exists( 'BuddyPress', false ) || ! function_exists( 'bp_profile_get_field_groups' ) ) {
			return $conditions;
		}

		$conditions['buddypress_profile_field'] = array(
			'label'        => __( 'BuddyPress profile field', 'advanced-ads-pro' ),
			'description'  => __( 'Display ads based on BuddyPress profile fields', 'advanced-ads-pro' ),
			'metabox'      => array( 'Advanced_Ads_Pro_Module_BuddyPress', 'xprofile_metabox' ),
			'check'        => array( 'Advanced_Ads_Pro_Module_BuddyPress', 'check_xprofile' ),
			'passive_info' => array(
				'hash_fields' => 'field',
				'remove'      => 'login',
				'function'    => array( 'Advanced_Ads_Pro_Module_BuddyPress', 'get_passive' ),
			),
		);

		// update condition labels when BuddyBoss is used
		if ( self::is_buddyboss() ) {
			$conditions['buddypress_profile_field']['label']       = __( 'BuddyBoss profile field', 'advanced-ads-pro' );
			$conditions['buddypress_profile_field']['description'] = __( 'Display ads based on BuddyBoss profile fields', 'advanced-ads-pro' );
		}

		return $conditions;
	}

	/**
	 * Frontend check for the xprofile condition
	 *
	 * @param array $options condition options.
	 *
	 * @return bool
	 */
	public static function check_xprofile( $options = array() ) {
		if ( ! isset( $options['operator'] ) || ! isset( $options['value'] ) || ! isset( $options['field'] ) ) {
				return true;
		}
		$user     = wp_get_current_user();
		$operator = $options['operator'];
		$value    = trim( $options['value'] );
		$field    = trim( $options['field'] );
		if ( ! $user ) {
			return true;
		}

		$args    = array(
			'field'   => $field, // should be field ID
			'user_id' => $user->ID,
		);
		$profile = bp_get_profile_field_data( $args );

		$trimmed_options = array(
			'operator' => $operator,
			'value'    => $value,
		);

		$condition = Advanced_Ads_Visitor_Conditions::helper_check_string( $profile, $trimmed_options );
		return $condition;
	}

	/**
	 * Get information to use in passive cache-busting.
	 *
	 * @param array $options condition options.
	 */
	public static function get_passive( $options = array() ) {
		$user_id = get_current_user_id();
		if ( ! isset( $options['field'] ) ) {
			return;
		}
		$field = trim( $options['field'] );
		if ( ! $user_id ) {
			return;
		}

		$args    = array(
			'field'   => $field, // should be field ID
			'user_id' => $user_id,
		);
		$profile = bp_get_profile_field_data( $args );

		return array(
			'field' => $options['field'],
			'data'  => $profile,
		);
	}

	/**
	 * Render xprofile visitor condition option
	 *
	 * @param array  $options condition options.
	 * @param int    $index index of the option.
	 * @param string $form_name name of the form.
	 */
	public static function xprofile_metabox( $options, $index = 0, $form_name = '' ) {
		if ( ! isset( $options['type'] ) || '' === $options['type'] ) {
			return; }

		$type_options = Advanced_Ads_Visitor_Conditions::get_instance()->conditions;

		if ( ! isset( $type_options[ $options['type'] ] ) ) {
			return;
		}

		$groups = bp_profile_get_field_groups();

		// form name basis
		$name = Advanced_Ads_Pro_Module_Advanced_Visitor_Conditions::get_form_name_with_index( $form_name, $index );

		$value = isset( $options['value'] ) ? $options['value'] : '';

		// options
		$field    = isset( $options['field'] ) ? $options['field'] : '';
		$value    = isset( $options['value'] ) ? $options['value'] : '';
		$operator = isset( $options['operator'] ) ? $options['operator'] : 'is_equal';

		require AAP_BASE_PATH . 'modules/buddypress/views/xprofile-condition.php';
	}

	/**
	 * Check if we are using BuddyPress legacy theme
	 *
	 * @return bool 1 if the site uses the legacy theme
	 */
	public static function is_legacy_theme() {
		return function_exists( 'bp_get_theme_package_id' ) && 'legacy' === bp_get_theme_package_id();
	}

	/**
	 * Return the hook from the selected option
	 * the legacy method used another format, the new version stores the hooks in the option
	 *
	 * @param array $placement options of a single placement.
	 * @return null|string hook name
	 */
	public static function get_hook_from_placement_options( $placement ) {

		if ( empty( $placement['options']['buddypress_hook'] ) ) {
			return null;
		}

		return ( self::is_legacy_theme() )
			? str_replace( ' ', '_', 'bp_' . $placement['options']['buddypress_hook'] )
			: esc_attr( $placement['options']['buddypress_hook'] );
	}

	/**
	 * Check if BuddyBoss is installed instead of BuddyPress
	 *
	 * @return bool true if BuddyBoss is installed and used instead of BuddyPress
	 */
	public static function is_buddyboss() {
		return defined( 'BP_PLATFORM_VERSION' );
	}
}


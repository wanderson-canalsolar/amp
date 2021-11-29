<?php

namespace LearnDash_Notification;

/**
 *  A base class for handling database relates stuff in a notification
 *
 * Class Notification
 *
 * @package LearnDash_Notification
 */
class Notification {
	/**
	 * Self ID, it is a WP post type ID
	 *
	 * @var int
	 */
	public $id;
	/**
	 * The event name
	 *
	 * @var string
	 */
	public $trigger;

	/**
	 * The course ID that this should be listen on
	 * If this is 0, then it means any course will work
	 *
	 * @var int
	 */
	public $course_id;

	/**
	 * The group ID this should be listen on
	 * If this is 0, then it means any
	 *
	 * @var int
	 */
	public $group_id;

	/**
	 * @var int
	 */
	public $lesson_id;

	/**
	 * @var int
	 */
	public $topic_id;

	/**
	 * @var int
	 */
	public $quiz_id;

	/**
	 * @var int
	 */
	public $login_reminder_after;

	/**
	 * @var int
	 */
	public $before_course_expiry;

	/**
	 * @var int
	 */
	public $after_course_expiry;

	/**
	 * The recipients that we should send the user
	 *
	 * @var array
	 */
	public $recipients = array();

	/**
	 * Addition recipients, separate by commas
	 *
	 * @var string
	 */
	public $addition_recipients;

	/**
	 * A value for delay the notification, unit is days
	 *
	 * @var int
	 */
	public $delay;

	/**
	 * @var string
	 */
	public $delay_unit;

	/**
	 * @var bool
	 */
	public $only_one_time = 1;

	/**
	 * Contains the Notification post type
	 *
	 * @var \WP_Post
	 */
	public $post;

	public function __construct( $post ) {
		if ( ! $post instanceof \WP_Post ) {
			$post = get_post( $post );
		}
		$this->post                = $post;
		$this->trigger             = get_post_meta( $post->ID, '_ld_notifications_trigger', true );
		$this->recipients          = get_post_meta( $post->ID, '_ld_notifications_recipient', true );
		$this->addition_recipients = get_post_meta( $post->ID, '_ld_notifications_bcc', true );
		$this->delay               = get_post_meta( $post->ID, '_ld_notifications_delay', true );
		$this->delay_unit          = get_post_meta( $post->ID, '_ld_notifications_delay_unit', true );
		if ( ! in_array( $this->delay_unit, array( 'days', 'hours', 'minutes', 'seconds' ) ) ) {
			// fallback
			$this->delay_unit = 'days';
		}
		$this->course_id            = absint( get_post_meta( $post->ID, '_ld_notifications_course_id', true ) );
		$this->group_id             = absint( get_post_meta( $post->ID, '_ld_notifications_group_id', true ) );
		$this->lesson_id            = absint( get_post_meta( $post->ID, '_ld_notifications_lesson_id', true ) );
		$this->topic_id             = absint( get_post_meta( $post->ID, '_ld_notifications_topic_id', true ) );
		$this->quiz_id              = absint( get_post_meta( $post->ID, '_ld_notifications_quiz_id', true ) );
		$this->login_reminder_after = absint( get_post_meta( $post->ID, '_ld_notifications_not_logged_in_days', true ) );
		$this->before_course_expiry = absint( get_post_meta( $post->ID, '_ld_notifications_course_expires_days', true ) );
		$this->after_course_expiry  = absint( get_post_meta( $post->ID, '_ld_notifications_course_expires_after_days', true ) );
		$this->only_one_time        = get_post_meta( $post->ID, '_ld_notifications_send_only_once', true );
		if ( strlen( $this->only_one_time ) === 0 ) {
			// backward compatibility, this is on by default
			$this->only_one_time = 1;
		} else {
			$this->only_one_time = absint( $this->only_one_time );
		}
	}

	/**
	 * Populate the shortcode data
	 *
	 * @param array $args
	 */
	public function populate_shortcode_data( $args = array() ) {
		global $ld_notifications_shortcode_data;
		$args['notification_id']         = $this->post->ID;
		$ld_notifications_shortcode_data = $args;
	}

	/**
	 * Check if the email is already sent
	 *
	 * @param int $user_id
	 * @param mixed ...$args
	 *
	 * @return bool
	 */
	public function is_sent( $user_id, ...$args ) {
		$args = array_map( 'sanitize_title', $args );
		$meta = 'ld_sent_notification_' . implode( '_', $args );
		$sent = get_user_meta( $user_id, $meta, true );

		// if it was sent, then should be a timestamp
		return filter_var( $sent, FILTER_VALIDATE_INT );
	}

	/**
	 * Adding a flag when an email is sent/queued, preventing duplicate email send
	 *
	 * @param $user_id
	 * @param mixed ...$args
	 *
	 * @return bool
	 */
	public function mark_sent( $user_id, ...$args ) {
		$args = array_map( 'sanitize_title', $args );
		$meta = 'ld_sent_notification_' . implode( '_', $args );

		return update_user_meta( $user_id, $meta, time() );
	}

	/**
	 * @param $user_id
	 * @param mixed ...$args
	 *
	 * @return bool
	 */
	public function mark_unsent( $user_id, ...$args ) {
		$args = array_map( 'sanitize_title', $args );
		$meta = 'ld_sent_notification_' . implode( '_', $args );

		return delete_user_meta( $user_id, $meta );
	}

	/**
	 * Base on the condition of the settings, we mayb get
	 * 1. The current user's email
	 * 2. Group owners' emails
	 * 3. Admin's emails
	 *
	 * @param int $user_id
	 * @param int $course_id
	 * @param int $group_id
	 *
	 * @return array
	 */
	public function gather_emails( $user_id, $course_id = null, $group_id = null ) {
		$emails = explode( ',', $this->addition_recipients );
		foreach ( $this->recipients as $recipient ) {
			switch ( $recipient ) {
				case 'user':
					$user = get_user_by( 'id', $user_id );
					if ( is_object( $user ) ) {
						$emails[] = $user->user_email;
					}
					break;
				case 'group_leader':
					/**
					 * In this context, a group leaders should be the lader of this user, if any
					 */
					$group_ids = array();
					if ( ! is_null( $group_id ) ) {
						$group_ids[] = absint( $group_id );
					}
					if ( ! is_null( $course_id ) ) {
						$course_group_ids = learndash_get_course_groups( $course_id );
						// we have a list of groups, but it can be different from the current user, so need a check
						foreach ( $course_group_ids as $key => $course_group_id ) {
							if ( ! learndash_is_user_in_group( $user_id, $course_group_id ) ) {
								unset( $course_group_ids[ $key ] );
							}
						}
						$group_ids = array_merge( $group_ids, $course_group_ids );
					}
					$group_ids = array_unique( $group_ids );
					$group_ids = array_filter( $group_ids );
					foreach ( $group_ids as $group_id ) {
						$user_ids = $this->get_group_leaders( $group_id );
						foreach ( $user_ids as $user_id ) {
							$user = get_user_by( 'id', $user_id );
							if ( is_object( $user ) ) {
								$emails[] = $user->user_email;
							}
						}
					}
					break;
				case 'admin':
					$users = get_users(
						array(
							'role' => 'administrator',
						)
					);
					foreach ( $users as $user ) {
						$emails[] = $user->user_email;
					}
					break;
			}
		}
		$emails = array_unique( $emails );
		$emails = array_filter( $emails );
		// have to validate the emails as it user input
		foreach ( $emails as $key => $email ) {
			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				unset( $emails[ $key ] );
			}
		}

		return $emails;
	}

	/**
	 * @param $group_id
	 *
	 * @return array
	 */
	protected function get_group_leaders( $group_id ) {
		global $wpdb;
		$query = "SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = 'learndash_group_leaders_%d'";

		return $wpdb->get_col( $wpdb->prepare( $query, $group_id ) );
	}

	/**
	 * @param $message
	 * @param $category
	 */
	public function log( $message, $category ) {
		$log_dir = wp_upload_dir( null, true );
		$log_dir = $log_dir['basedir'] . DIRECTORY_SEPARATOR . 'learndash-notifications' . DIRECTORY_SEPARATOR;
		if ( ! is_dir( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}
		$message = sprintf( date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . ': %s', $message );
		file_put_contents( $log_dir . sanitize_file_name( $category ), $message . PHP_EOL, FILE_APPEND );
	}
}

<?php

namespace LearnDash_Notification\Trigger;

use LearnDash_Notification\Notification;
use LearnDash_Notification\Trigger;

/**
 * Listen when a lesson is available for the enrolled users, then send email for notification
 * The cron will base on per lesson, each lesson have each own cron for dispatching emails.
 * 1. Cron will start when a new drip lesson created
 * 2. When an user enroll to a course that have drip lesson, check if the cron for that lesson exist, if not, create new, if yes
 * do nothing, as both option, the cron for that user surely will start later than existing
 * 3. When a notification get created, make sure all the drip lesson already have a cron monitoring
 * Class Drip_Lesson_Available
 * @package LearnDash_Notification\Trigger
 */
class Drip_Lesson_Available extends Trigger {
	protected $trigger = 'lesson_available';

	protected $hook_name = 'learndash_notifications_drip_lesson';

	/**
	 * Listen for the signal when a new lesson is added or updated, then we going to queue an event for further processing
	 *
	 * @param $meta_id
	 * @param $object_id
	 * @param $meta_key
	 * @param $_meta_value
	 */
	public function monitor( $meta_id, $object_id, $meta_key, $_meta_value ) {
		if ( ! function_exists( 'learndash_get_post_type_slug' ) ) {
			// this mean the LD core is not on
			return;
		}
		$post_type = learndash_get_post_type_slug( 'lesson' );
		//not this, return
		if ( $meta_key !== '_' . $post_type ) {
			return;
		}
		//make sure the values fully provided
		$_meta_value = maybe_unserialize( $_meta_value );
		if (
			( ! isset( $_meta_value['sfwd-lessons_visible_after'] ) || empty( $_meta_value['sfwd-lessons_visible_after'] ) ) &&
			( ! isset( $_meta_value['sfwd-lessons_visible_after_specific_date'] ) || empty( $_meta_value['sfwd-lessons_visible_after_specific_date'] ) )
		) {
			return;
		}

		$args      = [
			$object_id
		];
		$timestamp = $this->get_next_send( $object_id );
		if ( $timestamp === false ) {
			//no user enroll to this course, so do nothing
			return;
		}
		if ( wp_next_scheduled( $this->hook_name, $args ) ) {
			$this->log( 'Restart the schedule', $this->trigger );
			//if this is queued, means something was changed, so remove and start over
			wp_clear_scheduled_hook( $this->hook_name, $args );
		}
		//$this->log( sprintf( 'Next check at: %s - unix timestamp: %s - lesson ID: %d', $this->get_current_time_from( $timestamp ), $timestamp, $object_id ) );
		wp_schedule_single_event( $timestamp, $this->hook_name, $args );
	}

	/**
	 * Get the nearest time this lesson available for the users
	 *
	 * @param $lesson_id
	 *
	 * @return int|bool
	 */
	protected function get_next_send( $lesson_id ) {
		$course_id = learndash_get_course_id( $lesson_id );
		$user_ids  = $this->get_users( $course_id );
		if ( empty( $user_ids ) ) {
			return false;
		}
		$timestamps = [];
		$current    = time();
		if ( php_sapi_name() === 'cli' && function_exists( 'learndash_notification_time' ) ) {
			$current = learndash_notification_time();
		}
		foreach ( $user_ids as $user_id ) {
			$timestamp = $this->ld_lesson_access_from( $lesson_id, $user_id, $course_id );
			if ( $current > $timestamp ) {
				//this is already done, moving one
				continue;
			}
			$timestamps[] = $timestamp;
		}

		$timestamps = array_unique( $timestamps );
		$timestamps = array_filter( $timestamps );
		if ( empty( $timestamps ) ) {
			return false;
		}

		return min( $timestamps );
	}

	/**
	 *
	 * @param $lesson_id
	 */
	public function maybe_dispatch_emails( $lesson_id ) {
		$models = $this->get_notifications( $this->trigger );
		if ( empty( $models ) ) {
			return;
		}
		$course_id = learndash_get_course_id( $lesson_id );
		$user_ids  = $this->get_users( $course_id );
		$this->log( '====Cron Start====' );
		foreach ( $user_ids as $user_id ) {
			$user_id = absint( $user_id );
			if ( ! $this->should_send( $user_id, $lesson_id, $course_id ) ) {
				continue;
			}
			$timestamp = $this->ld_lesson_access_from( $lesson_id, $user_id, $course_id );
			$this->log( sprintf( 'Expected to send a notification for the lesson %d at %s for the user %d', $lesson_id, $this->get_current_time_from( $timestamp ), $user_id ) );
			//if timestamp is empty, then the user can access
			$current = $this->get_timestamp();
			if ( ! empty( $timestamp ) && $current < $timestamp ) {
				$this->log( 'Cron was trigger manually, however, the time was not right' );
				//this is not touch yet
				continue;
			}
			foreach ( $models as $model ) {
				if ( $model->is_sent( $user_id, $this->trigger, $model->post->ID, $lesson_id ) ) {
					continue;
				}

				if ( $model->course_id !== 0 && $model->course_id !== absint( $course_id ) ) {
					continue;
				}

				if ( $model->lesson_id !== 0 && $model->lesson_id !== absint( $lesson_id ) ) {
					continue;
				}

				$emails = $model->gather_emails( $user_id, $course_id );
				$args   = array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
					'lesson_id' => $lesson_id,
				);
				if ( absint( $model->delay ) ) {
					$this->queue_use_db( $emails, $model, $args );
				} else {
					$this->send( $emails, $model, $args );
					$model->mark_sent( $user_id, $this->trigger, $model->post->ID, $lesson_id );
				}
			}
		}
		$timestamp = $this->get_next_send( $lesson_id );
		if ( $timestamp !== false ) {
			//$this->log( sprintf( 'Next check at: %s - unix timestamp: %s - lesson ID: %d', $this->get_current_time_from( $timestamp ), $timestamp, $lesson_id ) );
			wp_clear_scheduled_hook( $this->hook_name, array( $lesson_id ) );
			wp_schedule_single_event( $timestamp, $this->hook_name, array( $lesson_id ) );
		} else {
			wp_clear_scheduled_hook( $this->hook_name, array( $lesson_id ) );
			$this->log( 'All sent' );
		}
		$this->log( '====Cron End====' );
	}

	/**
	 * Determine if this should be send to the user, this mostly for backward compatibility, we should not
	 * send the email again
	 *
	 * @param $user_id
	 * @param $lesson_id
	 * @param $course_id
	 */
	protected function should_send( $user_id, $lesson_id, $course_id ) {
		if ( learndash_is_lesson_complete( $user_id, $lesson_id ) || ld_course_access_expired( $course_id, $user_id ) ) {
			//the user already finish this, do nothing
			$this->log( sprintf( 'ERR_LC_%s_%s', $user_id, $course_id ) );

			return false;
		}

		if ( ! ld_course_check_user_access( $course_id, $user_id ) ) {
			$this->log( sprintf( 'ERR_UA_%s_%s', $user_id, $course_id ) );

			return false;
		}

		$timestamp = $this->ld_lesson_access_from( $lesson_id, $user_id, $course_id );
		//if the timestamp is smaller than upgrade time then do nothing, this is for prevent double email send from older version
		$init_time = get_option( 'ld_notifications_init' );
		if ( $init_time && $timestamp < $init_time ) {
			$this->log( sprintf( 'ERR_Expire_%s_%s_%s', $user_id, $course_id, $timestamp ) );

			return false;
		}

		return true;
	}

	/**
	 * When an user enroll to a course, and if the course having a drip lesson, then restart the cron
	 *
	 * @param $user_id
	 * @param $course_id
	 * @param $access_list
	 * @param $remove
	 */
	public function maybe_kick_start( $user_id, $course_id, $access_list, $remove ) {
		if ( $remove ) {
			$this->clear_queued_notifications( $user_id, $course_id );

			return;
		}

		$lesson_ids = learndash_get_lesson_list( $course_id, array( 'num' => 0 ) );
		foreach ( $lesson_ids as $lesson_id ) {
			if ( is_object( $lesson_id ) ) {
				$lesson_id = $lesson_id->ID;
			}
			if ( ! $this->is_dripped_lesson( $lesson_id ) ) {
				continue;
			}
			//if the lesson is not able to access now for this user, means this is queued for future
			$timestamp = $this->ld_lesson_access_from( $lesson_id, $user_id );
			if ( absint( $timestamp ) > 0 && ! wp_next_scheduled( $this->hook_name, [ $lesson_id ] ) ) {
				//this is drip and no cron for it, start now
				$timestamp = $this->get_next_send( $lesson_id );
				//$this->log( sprintf( 'Next check at: %s - unix timestamp: %s - lesson ID: %d', $this->get_current_time_from( $timestamp ), $timestamp, $lesson_id ) );
				wp_schedule_single_event( $timestamp, $this->hook_name, [ $lesson_id ] );
			}
		}
	}

	private function make_detail_log( $lesson_id ) {
		$course_id = learndash_get_course_id( $lesson_id );
		$user_ids  = $this->get_users( $course_id );
		if ( empty( $user_ids ) ) {
			return;
		}
		$strings = [ sprintf( 'Lesson %d:', $lesson_id ) ];
		foreach ( $user_ids as $user_id ) {
			$timestamp = $this->ld_lesson_access_from( $lesson_id, $user_id );
			if ( $timestamp > $this->get_timestamp() ) {
				//this one will be send in the future
				$strings[ $timestamp ] = sprintf( '- User: %d will be notified at %s', $user_id, $this->get_current_time_from( $timestamp ) );
			}
		}
		ksort( $strings );
		$strings = implode( PHP_EOL, $strings );
		$this->log( $strings );
	}

	/**
	 * Clear the single schedule if the course have drip lesson and no one enroll into it
	 *
	 * @param $user_id
	 * @param $course_id
	 */
	private function clear_queued_notifications( $user_id, $course_id ) {
		$lesson_ids = learndash_get_lesson_list( $course_id, array( 'num' => 0 ) );
		$user_ids   = $this->get_users( $course_id );
		$init_time  = get_option( 'ld_notifications_init' );

		foreach ( $lesson_ids as $lesson_id ) {
			if ( is_object( $lesson_id ) ) {
				$lesson_id = $lesson_id->ID;
			}
			if ( ! $this->is_dripped_lesson( $lesson_id ) ) {
				continue;
			}
			$remove = true;
			foreach ( $user_ids as $user_id ) {
				$time_access = $this->ld_lesson_access_from( $lesson_id, $user_id );
				if ( $time_access > $init_time && $time_access > $this->get_timestamp() ) {
					//this mean the queue still going on
					$remove = false;
					break;
				}
			}
			if ( $remove && wp_next_scheduled( $this->hook_name, [ $lesson_id ] ) ) {
				$this->log( sprintf( 'Clear the queue for lesson %d', $lesson_id ) );
				wp_clear_scheduled_hook( $this->hook_name, [ $lesson_id ] );
			}
		}
	}

	/**
	 *
	 * @param $course_id
	 *
	 * @return array
	 */
	protected function get_users( $course_id ) {
		$query = learndash_get_users_for_course( $course_id );
		if ( $query instanceof \WP_User_Query ) {
			return $query->get_results();
		}

		return [];
	}

	/**
	 * A base point for monitoring the events
	 *
	 * So when a drip lesson was created, or update, we going to schedule an event at nearest time
	 *
	 * @return mixed
	 */
	function listen() {
		add_action( "updated_postmeta", [ &$this, 'monitor' ], 99, 4 );
		add_action( 'learndash_update_course_access', [ &$this, 'maybe_kick_start' ], 10, 4 );
		add_action( $this->hook_name, [ &$this, 'maybe_dispatch_emails' ] );
		add_action( 'leanrdash_notifications_send_delayed_email', [ &$this, 'send_db_delayed_email' ] );
		if ( get_option( 'learndash_notifications_drips_check' ) ) {
			$this->ensure_cron_queued();
			delete_option( 'learndash_notifications_drips_check' );
		}
	}

	/**
	 * @param Notification $model
	 * @param $args
	 *
	 * @return bool
	 */
	protected function can_send_delayed_email( Notification $model, $args ) {
		$user_id   = $args['user_id'];
		$course_id = $args['course_id'];
		$lesson_id = $args['lesson_id'];

		if ( ! ld_course_check_user_access( $course_id, $user_id ) ) {
			//this user not in this course anymore
			$this->log( sprintf( 'Won\' send because user not in the course anymore, course id: %d', $course_id ) );

			return false;
		}

		$lesson = get_post( $lesson_id );
		if ( ! is_object( $lesson ) ) {
			$this->log( 'Won\' send because lesson doesn\'t exist anymore' );

			return false;
		}

		if ( $model->lesson_id !== 0 && $model->lesson_id !== $lesson_id ) {
			//specific course and this is not the one, return
			$this->log( sprintf( "Won't send cause the ID is different from the settings. Expected: %d - Current:%d", $model->lesson_id, $lesson_id ) );

			return false;
		}

		/**
		 * Because, this email can be created by legacy version, and it maybe sent before it reach into this,
		 * so we have to check
		 */
		if ( $model->is_sent( $user_id, $this->trigger, $model->post->ID, $lesson_id ) ) {
			$this->log( 'The email already sent.' );

			return false;
		}


		return true;
	}

	/**
	 * When plugin activated, we need to check if any cron missing
	 */
	public function ensure_cron_queued() {
		$models = $this->get_notifications( $this->trigger );
		if ( empty( $models ) ) {
			//nothing to do
			return;
		}

		$lesson_ids = $this->get_all_lessons();
		foreach ( $lesson_ids as $lesson_id ) {
			if ( ! $this->is_dripped_lesson( $lesson_id ) ) {
				continue;
			}
			$timestamp = $this->get_next_send( $lesson_id );
			if ( ! $timestamp ) {
				continue;
			}
			$this->make_detail_log( $lesson_id );
			if ( ! wp_next_scheduled( $this->hook_name, [ $lesson_id ] ) ) {
				wp_schedule_single_event( $timestamp, $this->hook_name, [ $lesson_id ] );
			}
		}
	}

	/**
	 * @param Notification $model
	 * @param $args
	 */
	protected function after_email_sent( Notification $model, $args ) {
		$user_id   = $args['user_id'];
		$lesson_id = $args['lesson_id'];
		$model->mark_sent( $user_id, $this->trigger, $model->post->ID, $lesson_id );
	}

	/**
	 * @param $lesson_id
	 *
	 * @return bool
	 */
	private function is_dripped_lesson( $lesson_id ) {
		$visible_after               = learndash_get_setting( $lesson_id, 'visible_after' );
		$visible_after_specific_date = learndash_get_setting( $lesson_id, 'visible_after_specific_date' );
		if ( empty( $visible_after ) && empty( $visible_after_specific_date ) ) {
			//this is not a drip lesson
			return false;
		}

		return true;
	}

	/**
	 * Gets the timestamp of when a user can access the lesson.
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id User ID.
	 * @param int|null $course_id Optional. Course ID. Default null.
	 * @param boolean $bypass_transient Optional. Whether to bypass transient cache. Default false.
	 *
	 * @return int|void The timestamp of when the user can access the lesson.
	 * @since 2.1.0
	 *
	 */
	function ld_lesson_access_from( $lesson_id, $user_id, $course_id = null, $bypass_transient = false ) {
		$return = null;

		if ( is_null( $course_id ) ) {
			$course_id = learndash_get_course_id( $lesson_id );
		}

		$courses_access_from = ld_course_access_from( $course_id, $user_id );
		if ( empty( $courses_access_from ) ) {
			$courses_access_from = learndash_user_group_enrolled_to_course_from( $user_id, $course_id, $bypass_transient );
		}

		$visible_after = learndash_get_setting( $lesson_id, 'visible_after' );
		if ( $visible_after > 0 ) {
			// Adjust the Course acces from by the number of days. Use abs() to ensure no negative days.
			$lesson_access_from = $courses_access_from + abs( $visible_after ) * 24 * 60 * 60;
			/**
			 * Filters the timestamp of when lesson will be visible after.
			 *
			 * @param int $lesson_access_from The timestamp of when the lesson will be available after a specific date.
			 * @param int $lesson_id Lesson ID.
			 * @param int $user_id User ID.
			 */
			$lesson_access_from = apply_filters( 'ld_lesson_access_from__visible_after', $lesson_access_from, $lesson_id, $user_id );

			$return = $lesson_access_from;

		} else {
			$visible_after_specific_date = learndash_get_setting( $lesson_id, 'visible_after_specific_date' );
			if ( ! empty( $visible_after_specific_date ) ) {
				if ( ! is_numeric( $visible_after_specific_date ) ) {
					// If we a non-numberic value like a date stamp Y-m-d hh:mm:ss we want to convert it to a GMT timestamp
					$visible_after_specific_date = learndash_get_timestamp_from_date_string( $visible_after_specific_date, true );
				}
				$return = apply_filters( 'ld_lesson_access_from__visible_after_specific_date', $visible_after_specific_date, $lesson_id, $user_id );
			}
		}

		/**
		 * Filters the timestamp of when the user will have access to the lesson.
		 *
		 * @param int $timestamp The timestamp of when the lesson can be accessed.
		 * @param int $lesson_id Lesson ID.
		 * @param int $user_id User ID.
		 */
		return apply_filters( 'ld_lesson_access_from', $return, $lesson_id, $user_id );
	}
}
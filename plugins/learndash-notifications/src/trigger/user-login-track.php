<?php

namespace LearnDash_Notification\Trigger;

use LearnDash_Notification\Notification;
use LearnDash_Notification\Trigger;

class User_Login_Track extends Trigger {
	protected $trigger = 'not_logged_in';

	protected $meta_key = '_ld_notifications_last_login';

	/**
	 * @param $login
	 * @param $user
	 */
	public function track_logged_time( $login, $user ) {
		update_user_meta( $user->ID, $this->meta_key, time() );
	}

	/**
	 * Loop through all the course and find all users enrolled, then check for how long they did not logged in
	 */
	public function maybe_send_reminder() {
		$models = $this->get_notifications( $this->trigger );
		if ( empty( $models ) ) {
			return;
		}
		foreach ( $models as $model ) {
			if ( $model->login_reminder_after <= 0 ) {
				continue;
			}
			$courses = [];
			if ( $model->course_id ) {
				$courses[] = $model->course_id;
			} else {
				$courses = $this->get_all_course();
			}
			foreach ( $courses as $id ) {
				//get all the users from this course
				$query = learndash_get_users_for_course( $id );
				if ( ! $query instanceof \WP_User_Query ) {
					//something was wrong
					continue;
				}
				$user_ids = $query->get_results();
				//now check the time this one last login
				foreach ( $user_ids as $user_id ) {
					if ( learndash_course_completed( $user_id, $id ) || ld_course_access_expired( $id, $user_id ) ) {
						continue;
					}
					
					$last_login          = get_user_meta( $user_id, $this->meta_key, true );
					$last_login_notified = get_user_meta( $user_id, '_ld_notifications_last_login_notified', true );
					if ( $last_login_notified > 0 && 1 === $model->only_one_time ) {
						// the user already sent, and we don't want to send more
						continue;
					}

					if ( empty( $last_login ) ) {
						//this one is never in process, update the meta and continue
						update_user_meta( $user_id, $this->meta_key, $this->get_timestamp() );
						continue;
					}
					$unit = apply_filters( 'learndash_notifications_user_login_track_unit', 'days' );
					if ( strtotime( '+' . $model->login_reminder_after . ' ' . $unit, $last_login ) <= $this->get_timestamp() ) {
						//send email
						$emails = $model->gather_emails( $user_id, $id );
						$args   = [
							'user_id'   => $user_id,
							'course_id' => $id
						];
						$this->send( $emails, $model, $args );
						//reset the time so it won't send until next cycle
						update_user_meta( $user_id, $this->meta_key, $this->get_timestamp() );
						update_user_meta( $user_id, '_ld_notifications_last_login_notified', $this->get_timestamp() );
					}
				}
			}
		}
	}

	/**
	 * A base point for monitoring the events
	 * @return void
	 */
	function listen() {
		add_action( 'wp_login', [ &$this, 'track_logged_time' ], 10, 2 );
		add_action( 'learndash_notifications_cron', [ &$this, 'maybe_send_reminder' ] );
	}

	/**
	 * This one have no delay
	 *
	 * @param Notification $model
	 * @param $args
	 *
	 * @return bool
	 */
	protected function can_send_delayed_email( Notification $model, $args ) {
		return false;
	}
}
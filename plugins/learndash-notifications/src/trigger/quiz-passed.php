<?php

namespace LearnDash_Notification\Trigger;

use LearnDash_Notification\Notification;
use LearnDash_Notification\Trigger;

class Quiz_Passed extends Trigger {
	protected $trigger = 'pass_quiz';

	public function monitor( $quiz_data, $user ) {
		if ( ! $this->is_process( $quiz_data ) ) {
			return;
		}
		$quiz = $quiz_data['quiz'];
		if ( ! is_object( $quiz ) && filter_var( $quiz, FILTER_VALIDATE_INT ) ) {
			$quiz = get_post( $quiz );
		}
		$course = $quiz_data['course'];
		$lesson = $quiz_data['lesson'];
		$models = $this->get_notifications( $this->trigger );
		if ( empty( $models ) ) {
			return;
		}
		$this->log( '==========Job start========' );
		$this->log( sprintf( 'Process %d notifications', count( $models ) ) );
		foreach ( $models as $model ) {
			if ( $model->course_id !== 0 && absint( $course->ID ) !== $model->course_id ) {
				continue;
			}

			if ( $model->lesson_id !== 0 && absint( $lesson->ID ) !== $model->lesson_id ) {
				continue;
			}
			if ( $model->quiz_id !== 0 && $model->quiz_id !== absint( $quiz->ID ) ) {
				//specific course and this is not the one, return
				continue;
			}

			$emails                       = $model->gather_emails( $user->ID, $course->ID );
			$ld_notifications_quiz_result = array(
				'cats'       => isset( $_POST['results']['comp']['cats'] ) ? $_POST['results']['comp']['cats'] : null,
				'pro_quizid' => $quiz_data['pro_quizid']
			);
			$args                         = [
				'user_id'     => $user->ID,
				'course_id'   => $course->ID,
				'quiz_id'     => $quiz->ID,
				'lesson_id'   => $lesson->ID,
				'topic_id'    => $quiz_data['topic'],
				'quiz_result' => $ld_notifications_quiz_result
			];
			$model->populate_shortcode_data( $args );
			if ( absint( $model->delay ) ) {
				$this->queue_use_db( $emails, $model, $args );
			} else {
				$this->send( $emails, $model, $args );
				$this->log( 'Done, moving next if any' );
			}
		}
		$this->log( '==========Job end========' );
	}

	/**
	 * User should pass the quiz for this to be working
	 *
	 * @param $quiz_data
	 *
	 * @return bool
	 */
	protected function is_process( $quiz_data ) {
		return absint( $quiz_data['pass'] ) === 1;
	}

	/**
	 * A base point for monitoring the events
	 * @return void
	 */
	function listen() {
		add_action( 'learndash_quiz_completed', [ &$this, 'monitor' ], 10, 2 );
		add_action( 'leanrdash_notifications_send_delayed_email', [ &$this, 'send_db_delayed_email' ] );
	}

	/**
	 * @param Notification $model
	 * @param $args
	 *
	 * @return bool
	 */
	protected function can_send_delayed_email( Notification $model, $args ) {
		$quiz_id = $args['quiz_id'];
		if ( $model->quiz_id !== 0 && $model->quiz_id !== $quiz_id ) {
			//specific course and this is not the one, return
			$this->log( sprintf( "Won't send cause the ID is different from the settings. Expected: %d - Current:%d", $model->quiz_id, $quiz_id ) );

			return false;
		}

		return true;
	}
}
<?php

namespace LearnDash_Notification\Trigger;

class Quiz_Submitted extends Quiz_Passed {
	protected $trigger = 'submit_quiz';

	public function listen() {
		add_action( 'learndash_quiz_submitted', [ &$this, 'monitor' ], 10, 2 );
		add_action( 'leanrdash_notifications_send_delayed_email', [ &$this, 'send_db_delayed_email' ] );
	}
}
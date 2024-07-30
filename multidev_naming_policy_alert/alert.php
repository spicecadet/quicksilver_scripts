<?php
/** QuickSilver script to notify NUS in the event of a multidev naming policy breach
 *
 * https://github.com/pantheon-systems/quicksilver-examples/blob/main/google_chat_notification/google_chat_notification.php
 *
 * @package Multidev_policy_alert
 */

new MultidevPolicyAlert();

class MultidevPolicyAlert {

	public $site_name;
	public $site_id;
	public $site_env;

	public function __construct() {
		$this->setQuicksilverVariables();
	}

	/**
	 * Get the Pantheon site name.
	 *
	 * @return string|null
	 */
	public function getPantheonSiteName(): ?string {
		return ! empty( $_ENV['PANTHEON_SITE_NAME'] ) ? $_ENV['PANTHEON_SITE_NAME'] : null;
	}

	/**
	 * Get the Pantheon environment.
	 *
	 * @return string|null
	 */
	public function getPantheonEnvironment(): ?string {
		return ! empty( $_ENV['PANTHEON_ENVIRONMENT'] ) ? $_ENV['PANTHEON_ENVIRONMENT'] : null;
	}

	/**
	 * Check if in the Quicksilver context.
	 *
	 * @return bool|void
	 */
	public function isQuicksilver() {
		if ( $this->isPantheon() && ! empty( $_POST['wf_type'] ) ) {
			return true;
		}
		die( 'No Pantheon Quicksilver environment detected.' );
	}

	/**
	 * Set Quicksilver variables from POST data.
	 *
	 * @return void
	 */
	public function setQuicksilverVariables() {
		$this->site_name = $this->getPantheonSiteName();
		$this->site_id   = $this->getPantheonSiteId();
		$this->site_env  = $this->getPantheonEnvironment();
	}

	public function mailTest() {
		if ( function_exists( 'mail' ) ) {
			echo 'mail() is available';
		} else {
			echo 'mail() has been disabled';
		}
	}
}

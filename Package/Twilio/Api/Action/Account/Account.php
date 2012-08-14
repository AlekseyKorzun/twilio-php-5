<?php
namespace Twilio\Api\Action\Account;

use Twilio\Api\Resource\Instance;
use Twilio\Api\Action\Account\Sandbox;

/**
 * Twilio account instance
 *
 * @package Library
 * @subpackage Twilio\Api\Action\Account
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Account extends Instance
{
	/**
	 * Initializer
	 *
	 * @return void
	 */
	protected function init()
	{
		// Setup actions
		$this->setupActions(
			'applications',
			'available_phone_numbers',
			'outgoing_caller_ids',
			'calls',
			'conferences',
			'incoming_phone_numbers',
			'notifications',
			'outgoing_callerids',
			'recordings',
			'sms_messages',
			'transcriptions',
			'connect_apps',
			'authorized_connect_apps'
		);

		// Initialize sandbox
		$this->sandbox = new Sandbox();
		$this->sandbox->setUri($this->uri() . '/Sandbox');
	}
}


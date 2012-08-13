<?php
namespace Library\Twilio\Api\Action;

use Library\Twilio\Api\Resource\Listing;

/**
 * Twilio SMS message resource
 *
 * @package Library
 * @subpackage Twilio\Api\Action
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class SmsMessage extends Listing
{
	/**
	 * Initializer
	 *
	 * @return void
	 */
	protected function init()
	{
		$this->setUri(preg_replace('#SmsMessages#', 'SMS/Messages', $this->uri()));
	}

	/**
	 * Method overwrite
	 *
	 * @param string $from phone number to send SMS from
	 * @param string $to phone number to send SMS to
	 * @param string $body message body
	 * @param array $parameters
	 * @return Instance
	 */
	public function create($from, $to, $body, array $parameters = array())
	{
		return parent::create(
			array(
					'From' => $from,
					'To' => $to,
					'Body' => $body
				 ) + $parameters
		);
	}
}


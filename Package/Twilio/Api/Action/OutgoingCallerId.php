<?php
namespace Library\Twilio\Api\Action;
use Library\Twilio\Api\Resource\Listing;

/**
 * Twilio outgoing caller id resource
 *
 * @package Library
 * @subpackage Twilio\Api\Action
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class OutgoingCallerId extends Listing
{
	/**
	 * Method overwrite
	 *
	 * @param string $phoneNumber
	 * @param array $parameters
	 * @return Instance
	 */
	public function create($phoneNumber, array $parameters = array())
	{
		return parent::_create(array(
										'PhoneNumber' => $phoneNumber,
									) + $parameters);
	}
}

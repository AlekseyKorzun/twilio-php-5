<?php
namespace Twilio\Api\Action;

use Twilio\Api\Resource\Listing;

/**
 * Twilio application resource
 *
 * @package Library
 * @subpackage Twilio\Api\Action
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Application extends Listing
{
	/**
	 * Method overwrite
	 *
	 * @param string $name
	 * @param array $parameters
	 * @return Instance
	 */
	public function create($name, array $parameters = array())
	{
		return parent::create(
			array(
				'FriendlyName' => $name
				) + $parameters
		);
	}
}


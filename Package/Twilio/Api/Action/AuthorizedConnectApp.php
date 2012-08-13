<?php
namespace Library\Twilio\Api\Action;
use \BadMethodCallException;
use Library\Twilio\Api\Resource\Listing;

/**
 * Twilio authorized connect application resource
 *
 * @package Library
 * @subpackage Twilio\Api\Action
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class AuthorizedConnectApp extends Listing
{
	/**
	 * Method overwrite
	 *
	 * @throws BadMethodCallException
	 * @param mixed $name
	 * @param array $parameters
	 * @return void
	 */
	public function create($name, array $parameters = array())
	{
		throw new BadMethodCallException('Not allowed');
	}
}

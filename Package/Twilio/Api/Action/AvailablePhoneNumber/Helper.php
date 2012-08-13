<?php
namespace Library\Twilio\Api\Action\AvailablePhoneNumber;
use \Exception;

/**
 * Helper class to wrap an object with a modified interface created by
 * a partial application of its existing methods.
 *
 * @package Library
 * @subpackage Twilio\Api\Action\AvailablePhoneNumber
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Helper
{
	/**
	 * Storage for our call back methods
	 *
	 * @var array
	 */
	private $_callbacks = array();

	/**
	 * Set-up a new call back method for internal storage
	 *
	 * @param string $method name of internal method
	 * @param string $callback function that we should associate with internal call back
	 * @param array $arguments additional arguments
	 * @return bool|void
	 */
	public function set($method, $callback, array $arguments)
	{
			// If we are unable to execute callback, skip it
		if (!is_callable($callback)) {
			return false;
		}

		$this->_callbacks[$method] = array($callback, $arguments);
	}

	/**
	 * Magic call wrapper that checks against internal callback storage for
	 * requested methods
	 *
	 * @throws Exception
	 * @param string $method method that user is attempting to call
	 * @param array $arguments optional additional arguments
	 * @return mixed
	 */
	public function __call($method, array $arguments = null)
	{
		if (!isset($this->_callbacks[$method])) {
			throw new Exception('Method was not found within internal storage: ' . $method);
		}

		list($callback, $callbackArguments) = $this->_callbacks[$method];

		return call_user_func_array(
										$callback,
										array_merge($callbackArguments, $arguments)
									);
	}
}
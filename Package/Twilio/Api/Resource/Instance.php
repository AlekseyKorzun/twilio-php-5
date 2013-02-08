<?php
namespace Twilio\Api\Resource;

use Twilio\Api\Resource as Resource;

/**
 * Abstraction of an instance resource from the Twilio API.
 *
 * @package Library
 * @subpackage Twilio\Api\Resource
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
abstract class Instance extends Resource
{
	/**
	 * Flag that indicates that instance was already initialized
	 *
	 * @var bool
	 */
	protected $isInitialized = false;

	/**
	 * Update instance parameters
	 *
	 * @param mixed $parameters array of updates, or a property name
	 * @param mixed $value a value with which to update the resource
	 * @return void
	 */
	public function update($parameters, $value = null)
	{
		if (!is_array($parameters)) {
			$parameters = array($parameters => $value);
		}

		$this->updateAttributes(self::client()->createData($this->uri(), $parameters));
	}

	/**
	 * Add all properties from an associative array (the JSON response body) as
	 * properties on this instance resource
	 *
	 * @param mixed $parameters
	 * @return void
	 */
	public function updateAttributes($parameters)
	{
		if ($parameters) {
			foreach ((array) $parameters as $name => $value) {
				$this->$name = $value;
			}
		}
	}

	/**
	 * Get the value of a property on this resource.
	 *
	 * To help with lazy HTTP requests, we don't actually retrieve an object
	 * from the API unless you really need it. Hence, this function may make
	 * API requests if the property you're requesting isn't available on the
	 * resource.
	 *
	 * @param string $name property name
	 * @return mixed
	 */
	public function __get($name)
	{
		// If we did not initialize, do so now
		if (!$this->isInitialized) {
			$this->initialize();
		}

		if ($name) {
			$action = $this->getActions($name);
			if ($action) {
				return $action;
			}
		}

		if (!isset($this->$name)) {
			$this->updateAttributes(self::client()->retrieveData($this->uri()));
		}

		return $this->$name;
	}

	/**
	 * Initialize instance
	 *
	 * @return void
	 */
	protected function initialize()
	{
		if (method_exists($this, 'init')) {
			$this->init();
		}

		$this->isInitialized = true;
	}
}


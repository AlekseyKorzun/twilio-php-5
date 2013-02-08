<?php
namespace Twilio\Api;

use Twilio\Api\Resource\Listing;

/**
 * Twilio API resource handler
 *
 * @package Library
 * @subpackage Twilio\Api
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
abstract class Resource
{
	/**
	 * Instance of client
	 *
	 * @var Client
	 */
	protected static $client;

	/**
	 * Resource instance
	 *
	 * @var mixed
	 */
	protected $resource;

	/**
	 * Storage of sub resources a resource might have
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Resource URI
	 *
	 * @var string
	 */
	protected $uri;

	/**
	 * Class constructor
	 *
	 * @param array $parameters
	 * @return void
	 */
	public function __construct($parameters = array())
	{
		if ($parameters) {
			foreach ($parameters as $name => $parameter) {
				$this->$name = $parameter;
			}
		}
	}

	/**
	 * Retrieve currently set URI
	 *
	 * @return string
	 */
	public function uri()
	{
		return $this->uri;
	}

	/**
	 * Set new URI
	 *
	 * @param string $uri new address
	 * @param bool|string optional action end point to append
	 * @return void
	 */
	public function setUri($uri, $action = false)
	{
		$this->uri = (string) $uri;
		if ($action) {
			$this->uri .= '/' . (string) $action;
		}
	}

	/**
	 * Retrieve instance of a client
	 *
	 * @return Client
	 */
	public static function client()
	{
		return self::$client;
	}

	/**
	 * Set a new client
	 *
	 * @param object $client
	 * @return void
	 */
	public static function setClient($client)
	{
		self::$client = $client;
	}

	/**
	 * Retrieve sub-resources
	 *
	 * @param string $name
	 * @return array
	 */
	public function getActions($name = null)
	{
		if (isset($name)) {
			return isset($this->actions[$name])
							? $this->actions[$name]
							: null;
		}

		return $this->actions;
	}

	/**
	 * Setup sub-resources
	 *
	 * @return void
	 */
	protected function setupActions()
	{
		$actions = func_get_args();
		if ($actions) {
			foreach ($actions as $action) {
				// Action name (for backwards compatibility with Twilio docs)
				$name = str_replace(' ', null, ucwords(str_replace('_', ' ', rtrim($action, 's'))));

				$class = __NAMESPACE__ . '\\Action\\' . $name;
				$this->actions[$action] = new $class;

				if ($this->actions[$action] instanceof Listing) {
					$name = $name . 's';
				}
				$this->actions[$action]->setUri(str_replace('.json', null, $this->uri()), $name);

				// Initiliaze if action contains init method
				if (method_exists($this->actions[$action], 'init')) {
					$this->actions[$action]->init();
				}
			}
		}
	}
}


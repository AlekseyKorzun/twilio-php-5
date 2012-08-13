<?php
namespace Library\Twilio\Api;
use Library\Twilio\Api\Resource\Listing;

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
	protected static $_client;

	/**
	 * Resource instance
	 *
	 * @var mixed
	 */
	protected $_resource;

	/**
	 * Storage of sub resources a resource might have
	 *
	 * @var array
	 */
	protected $_actions = array();

	/**
	 * Resource URI
	 *
	 * @var string
	 */
	protected $_uri;

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
				$this->$name = $param;
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
		return $this->_uri;
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
		$this->_uri = (string) $uri;
		if ($action) {
			$this->_uri .= '/' . (string) $action;
		}
	}

	/**
	 * Retrieve instance of a client
	 *
	 * @return Client
	 */
	public static function client()
	{
		return self::$_client;
	}

	/**
	 * Set a new client
	 *
	 * @param object $client
	 * @return void
	 */
	public static function setClient($client)
	{
		self::$_client = $client;
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
			return isset($this->_actions[$name])
							? $this->_actions[$name]
							: null;
		}

		return $this->_actions;
	}

	/**
	 * Setup sub-resources
	 *
	 * @return void
	 */
	protected function _setupActions()
	{
		$actions = func_get_args();
		if ($actions) {
			foreach ($actions as $action) {
				// Action name (for backwards compatibility with Twilio docs)
				$name = str_replace(' ', null, ucwords(str_replace('_', ' ', rtrim($action, 's'))));

				$class = __NAMESPACE__ . '\\Action\\' . $name;
				$this->_actions[$action] = new $class;

				if ($this->_actions[$action] instanceof Listing) {
					$name = $name . 's';
				}
				$this->_actions[$action]->setUri(str_replace('.json', null, $this->uri()), $name);

				// Initiliaze if action contains init method
				if (method_exists($this->_actions[$action], '_init')) {
					$this->_actions[$action]->_init();
				}
			}
		}
	}
}
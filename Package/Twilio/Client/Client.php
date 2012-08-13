<?php
namespace Library\Twilio\Client;

/**
 * Abstract client class
 *
 * @package Library
 * @subpackage Twilio\Client
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
abstract class Client
{
	/**
	 * Default connection time out in seconds, adjust if you are connecting
	 * to a local network or have a slow up-link
	 *
	 * @var int
	 */
	const TIMEOUT = 60;

	/**
	 * Secure identifier to use when prompted for authentication
	 *
	 * @var string
	 */
	protected $_identifier;

	/**
	 * Token to use when prompted for authentication
	 *
	 * @var string
	 */
	protected $_token;

	/**
	 * Various options for cURL
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * Debugging switch
	 *
	 * @var bool
	 */
	protected $_isDebug = false;

	/**
	 * Constructor that setups basic options
	 *
	 * @param string|bool $uri
	 * @param array $parameters
	 * @return void
	 */
	abstract public function __construct($uri = false, $parameters = array());

	/**
	 * Magic call method
	 *
	 * @throws TinyHttp_Exception
	 * @param string $method
	 * @param array $arguments
	 * @return array
	 */
	abstract public function __call($method, $arguments);

	/**
	 * Get identifier we use for authentication
	 *
	 * @return string
	 */
	public function identifier()
	{
		return (string) $this->_identifier;
	}

	/**
	 * Set identifier to use when making a request that requires authentication
	 *
	 * @param string $identifier
	 * @return void
	 */
	public function setIdentifier($identifier)
	{
		$this->_identifier = (string) $identifier;
	}

	/**
	 * Set token to use when making a request that requires authentication
	 *
	 * @param string $token
	 * @return void
	 */
	public function setToken($token)
	{
		$this->_token = (string) $token;
	}
}
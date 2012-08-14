<?php
namespace Twilio\Utility\Capability;

use \UnexpectedValueException;

/**
 * Scope URI implementation
 *
 * Simple way to represent configurable privileges in an OAuth
 * friendly way. For our case, they look like this:
 *
 * scope:<service>:<privilege>?<params>
 *
 * For example:
 * scope:client:incoming?name=jonas
 *
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 * @package Library
 * @subpackage Twilio\Utility\Capability
 */
class Scope
{
	/**
	 * Storage for service
	 *
	 * @var string
	 */
	public $service;

	/**
	 * Storage for privilege
	 *
	 * @var string
	 */
	public $privilege;

	/**
	 * Storage for parameters
	 *
	 * @var array
	 */
	public $parameters;

	/**
	 * Class constructor
	 *
	 * @param string $service
	 * @param string $privilege
	 * @param array $parameters
	 */
	public function __construct($service, $privilege, $parameters = array())
	{
		$this->service = $service;
		$this->privilege = $privilege;
		$this->parameters = $parameters;
	}

	/**
	 * Ouput scope
	 *
	 * @return string
	 */
	public function toString()
	{
		$uri = "scope:{$this->service}:{$this->privilege}";
		if (count($this->params)) {
			$uri .= "?".http_build_query($this->params);
		}
		return $uri;
	}

	/**
	 * Parse a scope URI into a ScopeURI object
	 *
	 * @throws UnexpectedValueException
	 * @param string $uri the scope URI
	 * @return Scope the parsed scope uri
	 */
	public static function parse($uri)
	{
		if (strpos($uri, 'scope:') !== 0) {
			throw new UnexpectedValueException('Not a scope URI according to scheme');
		}

		$parts = explode('?', $uri, 1);
		$parameters = null;

		if (count($parts) > 1) {
			parse_str($parts[1], $parameters);
		}

		$parts = explode(':', $parts[0], 2);

		if (count($parts) != 3) {
			throw new UnexpectedValueException('Not enough parts for scope URI');
		}

		list($scheme, $service, $privilege) = $parts;

		return new Scope($service, $privilege, $parameters);
	}
}


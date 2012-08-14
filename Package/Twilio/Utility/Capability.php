<?php
namespace Twilio\Utility;

use \InvalidArgumentException;
use Twilio\Utility\Capability\Scope;
use Twilio\Utility\Capability\Jwt;

/**
 * Twilio capability token generator
 *
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 * @package Library
 * @subpackage Twilio\Utility
 */
class Capability
{
	/**
	 * Default time to live
	 *
	 * @var int
	 */
	const DEFAULT_TTL = 3600;

	/**
	 * Storage for identifier
	 *
	 * @var string
	 */
	public $identifier;

	/**
	 * Storage for token
	 *
	 * @var string
	 */
	public $token;

	/**
	 * Storage for scopes
	 *
	 * @var array
	 */
	public $scopes;

	/**
	 * Name of a client
	 *
	 * @var string
	 */
	public $clientName;

	/**
	 * Create a new TwilioCapability with zero permissions. Next steps are to
	 * grant access to resources by configuring this token through the
	 * functions allowXXXX.
	 *
	 * @param string $identifier the account secure identifier to which this token is granted access
	 * @param string $token the secret key used to sign the token.
	 * @return void
	 */
	public function __construct($identifier, $token)
	{
		$this->identifier = $identifier;
		$this->token = $token;
	}

	/**
	 * If the user of this token should be allowed to accept incoming
	 * connections then configure the TwilioCapability through this method and
	 * specify the client name.
	 *
	 * @throws InvalidArgumentException
	 * @param string $name
	 * @return void
	 */
	public function allowClientIncoming($name)
	{
		// Name must be a non-zero length alphanumeric string
		if (preg_match('/\W/', $name)) {
			throw new InvalidArgumentException(
				'Only alphanumeric characters allowed in client name.'
			);
		}

		if (strlen($name) == 0) {
			throw new InvalidArgumentException(
				'Client name must not be a zero length string.'
			);
		}

		$this->clientName = $name;
		$this->allow(
			'client',
			'incoming',
			array('clientName' => $this->clientName)
		);
	}

	/**
	 * Allow the user of this token to make outgoing connections.
	 *
	 * @param int $applicationSid the application to which this token grants access
	 * @param array $parameters signed parameters that the user of this token cannot
	 * overwrite.
	 * @return void
	 */
	public function allowClientOutgoing($applicationSid, array $parameters = array())
	{
		$this->allow(
			'client',
			'outgoing',
			array(
					'appSid' => $applicationSid,
					'appParams' => http_build_query($parameters))
		);
	}

	/**
	 * Allow the user of this token to access their event stream.
	 *
	 * @param array $filters key/value filters to apply to the event stream
	 * @return void
	 */
	public function allowEventStream(array $filters = array())
	{
		$this->allow(
			'stream',
			'subscribe',
			array(
					'path' => '/2010-04-01/Events',
					'params' => http_build_query($filters))
		);
	}

	/**
	 * Generates a new token based on the credentials and permissions that
	 * previously has been granted to this token.
	 *
	 * @param int $ttl the expiration time of the token (in seconds).
	 * @return string the newly generated token that is valid for specified
	 * number of seconds
	 */
	public function generateToken($ttl = self::DEFAULT_TTL)
	{
		$payload = array(
							'scope' => array(),
							'iss' => $this->identifier,
							'exp' => time() + $ttl,
						);

		$scopeStrings = array();
		if ($this->scopes) {
			foreach ($this->scopes as $scope) {
				if ($scope->privilege == 'outgoing' && $this->clientName) {
					$scope->params['clientName'] = $this->clientName;
				}

				$scopeStrings[] = $scope->toString();
			}
		}

		$payload['scope'] = implode(' ', $scopeStrings);

		return Jwt::encode($payload, $this->token, 'HS256');
	}

	/**
	 * Create a new scope
	 *
	 * @param string $service
	 * @param string $privilege
	 * @param array $parameters
	 * @return void
	 */
	protected function allow($service, $privilege, $parameters)
	{
		$this->scopes[] = new Scope($service, $privilege, $parameters);
	}
}


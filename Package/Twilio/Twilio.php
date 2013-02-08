<?php
namespace Twilio;

use \Exception;
use Twilio\Api\Action\Account;
use Twilio\Api\Exception\Response as ResponseException;
use Twilio\Client\Client;
use Twilio\Client\TinyHttp;

/**
 * Twilio API client
 *
 * @package Library
 * @subpackage Twilio
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Twilio
{
	/**
	 * API end point
	 *
	 * @var string
	 */
	const API_URI = 'https://api.twilio.com';

	/**
	 * User agent requests will identify as
	 *
	 * @var string
	 */
	const USER_AGENT = 'twilio-php5/1.0';

	/**
	 * Instance of Account resource
	 *
	 * @var Account
	 */
	public $account;

	/**
	 * Instance of client
	 *
	 * @var object
	 */
	protected $client;

	/**
	 * Current API version
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Known API versions
	 *
	 * @var array
	 */
	protected $versions = array('2008-08-01', '2010-04-01');

	/**
	 * Twilio seervice onstructor
	 *
	 * @param string $identifier account sid
	 * @param string $token OAuth token
	 * @param string $version API version
	 * @param Client $client optional HTTP client
	 * @return void
	 */
	public function __construct($identifier, $token, $version = null, Client $client = null)
	{
		// Switch strict reporting off
		error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

		// Throw an error on empty identifier/token
		if (!$identifier || !$token) {
			throw new Exception('You must pass identifier (SID) and token provided to you by Twilio!');
		}

		// If custom client was not passed, use build in version
		if (!$client) {
			// Setup client parameters
			$parameters = array(
								'curl' => array(
												CURLOPT_USERAGENT => self::USER_AGENT,
												CURLOPT_HTTPHEADER => array('Accept-Charset: utf-8'),
												CURLOPT_CAINFO => dirname(__FILE__) . '/Certificate/Twilio.crt')
												);

			$client = new TinyHttp(self::API_URI, $parameters);
		}

		// Set-up authentication
		$client->setIdentifier($identifier);
		$client->setToken($token);

		// Initialize
		$this->client = $client;
		$this->version = in_array($version, $this->versions) ? $version : end($this->versions);

		// Authenticate
		$this->authenticate();
	}

	/**
	 * Get the API version used by the REST client
	 *
	 * @return string the API version in use
	 */
	public function version()
	{
		return $this->version;
	}

	/**
	 * GET the resource at the specified path.
	 *
	 * @param string $uri uri to the resource
	 * @param array $parameters query string parameters
	 * @param bool $isFullUri indicates if passed URI contains full path to the resource
	 * @return stdClass|bool
	 */
	public function retrieveData($uri, array $parameters = array(), $isFullUri = false)
	{
		return $this->processResponse($this->client->get(self::getRequestUri($uri, $parameters, $isFullUri)));
	}

	/**
	 * DELETE the resource at the specified path.
	 *
	 * @param string $uri uri to the resource
	 * @param array $parameters query string parameters
	 * @return stdClass|bool
	 */
	public function deleteData($uri, array $parameters = array())
	{
		return $this->processResponse($this->client->delete(self::getRequestUri($uri, $parameters)));
	}

	/**
	 * POST to the resource at the specified path.
	 *
	 * @param string $uri uri to the resource
	 * @param array $parameters query string parameters
	 * @return stdClass|bool
	 */
	public function createData($uri, array $parameters = array())
	{
		$headers = array('Content-Type' => 'application/x-www-form-urlencoded');

		$response = $this->client->post(
			self::getRequestUri($uri),
			$headers,
			http_build_query($parameters, '', '&')
		);

		return $this->processResponse($response);
	}

	/**
	 * Convert the JSON encoded resource into a PHP object.
	 *
	 * @throws DomainException
	 * @param array $response 3-tuple containing status, headers, and body
	 * @return object PHP object decoded from JSON
	 */
	private function processResponse(array $response)
	{
		list($status, $headers, $body) = $response;

		if ($status == 204) {
			return true;
		}

		if (!is_array($headers) || empty($headers['Content-Type'])) {
			throw new DomainException('Response header is missing Content-Type');
		}

		return $this->processJsonResponse($status, $headers, $body);
	}

	/**
	 * Process JSON response
	 *
	 * @throws DomainException
	 * @throws RestException
	 * @param int $status response code
	 * @param array $headers response headers
	 * @param string $body response body
	 * @return stdClass|bool
	 */
	private function processJsonResponse($status, $headers, $body)
	{
		$decoded = json_decode($body);

		if ($decoded) {
			if (200 <= $status && $status < 300) {
				return $decoded;
			}

			throw new ResponseException(
				(int) $decoded->status,
				$decoded->message,
				isset($decoded->code) ? $decoded->code : null,
				isset($decoded->more_info) ? $decoded->more_info : null
			);
		}

		throw new DomainException('Response did not contain valid JSON data-set.');
	}

	/**
	 * Construct a URI based on initial path, query params, and paging
	 * information
	 *
	 * @param string $uri uri to the resource
	 * @param array $parameters parameters to use with current request
	 * @param bool $isFullUri indicates if passed URI contains full path to the resource
	 * @return string
	 */
	protected static function getRequestUri($uri, array $parameters = array(), $isFullUri = false)
	{
		if ($uri) {
			$uri = $isFullUri ? $uri : "$uri.json";

			if (!$isFullUri && !empty($parameters)) {
				return $uri . '?' . http_build_query($parameters, '', '&');
			}
		}

		return $uri;
	}

	/**
	 * Authenticate with API
	 *
	 * @return void
	 */
	protected function authenticate()
	{
		$resource = new Account();
		$resource->setClient($this);
		$resource->setUri('/' . $this->version() . '/Accounts');

		// Initialize our parent resource
		$this->account = $resource->get($this->client->identifier());
	}
}


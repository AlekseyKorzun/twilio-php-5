<?php
namespace Library;
use Library\Twilio\Api\Action\Account;
use Library\Twilio\Api\Exception\Response as ResponseException;
use Library\Twilio\Client\Client;
use Library\Twilio\Client\TinyHttp;

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
	protected $_client;

	/**
	 * Current API version
	 *
	 * @var string
	 */
	protected $_version;

	/**
	 * Known API versions
	 *
	 * @var array
	 */
	protected $_versions = array('2008-08-01', '2010-04-01');

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

		// If custom client was not passed, use build in version
		if (!$client) {
			$client = new TinyHttp(self::API_URI, array('curl' => array(
																		CURLOPT_USERAGENT => self::USER_AGENT,
																		CURLOPT_HTTPHEADER => array('Accept-Charset: utf-8'),
																		CURLOPT_CAINFO => dirname(__FILE__) . '/Twilio/Certificate/Twilio.crt',
																	)));
		}

		// Set-up authentication
		$client->setIdentifier($identifier);
		$client->setToken($token);

		// Initialize
		$this->_client = $client;
		$this->_version = in_array($version, $this->_versions) ? $version : end($this->_versions);

		// Authenticate
		$this->_authenticate();
	}

	/**
	 * Get the API version used by the REST client
	 *
	 * @return string the API version in use
	 */
	public function version()
	{
		return $this->_version;
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
		return $this->_processResponse($this->_client->get(self::_getRequestUri($uri, $parameters, $isFullUri)));
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
		return $this->_processResponse($this->_client->delete(self::_getRequestUri($uri, $parameters)));
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

		return $this->_processResponse($this->_client->post(self::_getRequestUri($uri), $headers, http_build_query($parameters, '', '&')));
	}

	/**
	 * Convert the JSON encoded resource into a PHP object.
	 *
	 * @throws DomainException
	 * @param array $response 3-tuple containing status, headers, and body
	 * @return object PHP object decoded from JSON
	 */
	private function _processResponse(array $response)
	{
		list($status, $headers, $body) = $response;

		if ($status == 204) {
			return true;
		}

		if (!is_array($headers) || empty($headers['Content-Type'])) {
			throw new DomainException('Response header is missing Content-Type');
		}

		return $this->_processJsonResponse($status, $headers, $body);
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
	private function _processJsonResponse($status, $headers, $body)
	{
		$decoded = json_decode($body);

		if ($decoded) {
			if (200 <= $status && $status < 300) {
				return $decoded;
			}

			throw new ResponseException(
				(int) $decoded->status, $decoded->message, isset($decoded->code) ? $decoded->code : null,
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
	protected static function _getRequestUri($uri, array $parameters = array(), $isFullUri = false) {
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
	protected function _authenticate() {
		$resource = new Account();
		$resource->setClient($this);
		$resource->setUri('/' . $this->version() . '/Accounts');

		// Initialize our parent resource
		$this->account = $resource->get($this->_client->identifier());
	}
}
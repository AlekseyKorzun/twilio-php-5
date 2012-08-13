<?php
namespace Library\Twilio\Client;
use Library\Twilio\Client\Exception\TinyHttp as TinyHttpException;

/**
 * TinyHttp client
 *
 * @package Library
 * @subpackage Twilio\Client
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class TinyHttp extends Client
{
	/**
	 * URI Scheme (http/https/ftp/etc)
	 *
	 * @var string
	 */
	public $scheme;

	/**
	 * Host of the remote server we are attempting to send request to
	 *
	 * @var string
	 */
	public $host;

	/**
	 * Port of the remote server we are attempting to send request to
	 *
	 * @var int
	 */
	public $port;

	/**
	 * Constructor that setups basic options
	 *
	 * @param string|bool $uri
	 * @param array $parameters
	 * @return void
	 */
	public function __construct($uri = false, $parameters = array())
	{
		// Sanity check to ensure that cURL is present on this system
		if (!in_array('curl', get_loaded_extensions())) {
			throw new TinyHttpException('Curl was not found on this system, aborting.');
		}

		// If URI is passed, set parameters
		if ($uri) {
			foreach (parse_url($uri) as $name => $value) {
				$this->$name = $value;
			}
		}

		// Process additional parameters
		if ($parameters) {
			if (isset($parameters['debug'])) {
				$this->_isDebug = true;
			}

			if (isset($parameters['curl'])) {
				$this->_options = $parameters['curl'];
			}
		}
	}

	/**
	 * Magic call method
	 *
	 * @throws TinyHttp_Exception
	 * @param string $method
	 * @param array $arguments
	 * @return array
	 */
	public function __call($method, $arguments)
	{
		list($resource, $headers, $body) = $arguments + array(0, array(), '');

		$options = $this->_options + array(
										CURLOPT_URL => "$this->scheme://$this->host$resource",
										CURLOPT_HEADER => true,
										CURLOPT_RETURNTRANSFER => true,
										CURLOPT_INFILESIZE => -1,
										CURLOPT_POSTFIELDS => null,
										CURLOPT_TIMEOUT => self::TIMEOUT,
										);

		if ($headers) {
			foreach ($headers as $header => $value) {
				$options[CURLOPT_HTTPHEADER][] = "$header: $value";
			}
		}

		if ($this->port) {
			$options[CURLOPTport] = $this->port;
		}

		if ($this->_isDebug) {
			$options[CURLINFO_HEADER_OUT] = true;
		}

		if ($this->_identifier && $this->_token) {
			$options[CURLOPT_USERPWD] = "$this->_identifier:$this->_token";
		}

		// Handle different request methods
		switch (strtolower($method)) {
			case 'get':
				$options[CURLOPT_HTTPGET] = true;
				break;
			case 'post':
				$options[CURLOPT_POST] = true;
				if ($body) {
					$options[CURLOPT_POSTFIELDS] = $body;
				}
				break;
			case 'put':
				$options[CURLOPT_PUT] = true;
				if (strlen($body)) {
					$buffer = fopen('php://memory', 'w+');
					if (!$buffer) {
						throw new TinyHttpException('Unable to allocate memory for writing');
					}

					fwrite($buffer, $body);
					fseek($buffer, 0);
					$options[CURLOPT_INFILE] = $buffer;
					$options[CURLOPT_INFILESIZE] = strlen($body);
				}
				break;
			case 'head':
				$options[CURLOPT_NOBODY] = true;
				break;
			default:
				$options[CURLOPT_CUSTOMREQUEST] = strtoupper($name);
		}

		// Process request
		try {
			$curl = curl_init();
			if (!$curl) {
				throw new TinyHttpException('Unable to initialize cURL');
			}

			// Transfer options to cURL instance
			if (!curl_setopt_array($curl, $options)) {
				throw new TinyHttpException(curl_error($curl));
			}

			// Perform request
			$response = curl_exec($curl);
			if (!$response) {
				throw new TinyHttpException(curl_error($curl));
			}

			$parts = explode("\r\n\r\n", $response, 3);


			list($header, $body) = ($parts[0] == 'HTTP/1.1 100 Continue') ? array($parts[1], $parts[2]) : array($parts[0], $parts[1]);

			// Process headers
			$headers = explode("\r\n", $header);
			if ($headers) {
				array_shift($headers);

				$temporary = array();

				foreach ($headers as $header) {
					list($key, $value) = explode(":", $header, 2);
					$temporary[$key] = trim($value);
				}

				// Exchange array
				$headers = $temporary;
			}

			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			// If debugging is enabled, let's log request and response to our error log
			if ($this->_isDebug) {
				error_log(curl_getinfo($curl, CURLINFO_HEADER_OUT) . $body);
			}

			// Clean up connections and buffers
			curl_close($curl);

			if (isset($buffer) && is_resource($buffer)) {
				fclose($buffer);
			}

			// Return constructed response
			return array($status, $headers, $body);
		} catch (TinyHttpException $exception) {
			// Clean up
			if (is_resource($curl)) {
				curl_close($curl);
			}

			if (isset($buffer) && is_resource($buffer)) {
				fclose($buf);
			}

			throw $exception;
		}
	}
}
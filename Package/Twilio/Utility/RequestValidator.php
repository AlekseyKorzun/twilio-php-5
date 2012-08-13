<?php
namespace Library\Twilio\Utility;

/**
 * An important part of any secure Twilio application is correctly performing
 * request validation.
 *
 * For a complete description of how request validation works, see the Twilio
 * security documentation.
 *
 * The basic idea is that Twilio builds a string based on the parameters sent to
 * your server and then creates a hash of this string using your account's
 * AuthToken (a shared secret).
 *
 * Twilio sends this hash to your server as a header in its request.
 *
 * You can then build the same string and create the same hash as Twilio did,
 * and compare yours to the one Twilio sent to determine the authenticity of
 * the request.
 *
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 * @package Library
 * @subpackage Twilio\Utility
 * @link http://www.twilio.com/docs/security#validating-requests
 */
class RequestValidator
{
	/**
	 * Storage for token
	 *
	 * @var string
	 */
	protected $_token;

	/**
	 * Class constructor
	 *
	 * @param string $token the secret key used to sign the token.
	 * @return void
	 */
	function __construct($token)
	{
		$this->_token = $token;
	}

	/**
	 * Compute signature
	 *
	 * @param string $url
	 * @param array $data
	 * @return string
	 */
	public function computeSignature($url, $data = array())
	{
		// Sort the array by keys
		ksort($data);

		// Append them to the data string in order without delimiters
		foreach($data as $key => $value) {
			$url .= "$key$value";
		}

		// This function calculates the HMAC hash of the data with the key
		// passed in
		return base64_encode(hash_hmac('sha1', $url, $this->_token, true));
	}

	/**
	 * Validate signature
	 *
	 * @param string $signature expected signature
	 * @param string $url
	 * @param array $data
	 * @return bool
	 */
	public function validate($signature, $url, $data = array())
	{
		return (bool) ($this->computeSignature($url, $data) == $signature);
	}
}
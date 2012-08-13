<?php
namespace Library\Twilio\Utility\Capability;

use \DomainException;
use \UnexpectedValueException;

/**
 * JSON Web Token implementation
 *
 * Minimum implementation used by Realtime auth, based on this spec:
 * http://self-issued.info/docs/draft-jones-json-web-token-01.html.
 *
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 * @package Library
 * @subpackage Twilio\Utility\Capability
 */
class Jwt
{
	/**
	 * Decode payload
	 *
	 * @throws UnexpectedValueException
	 * @throws DomainException
	 * @param string  $jwt the JWT
	 * @param string|null $key the secret key
	 * @param bool $verify don't skip verification process
	 * @return object JSON Web Token implementation as PHP object
	 */
	public static function decode($jwt, $key = null, $verify = true)
	{
		$tokens = explode('.', $jwt);
		if (count($tokens) != 3) {
			throw new UnexpectedValueException('Wrong number of segments');
		}

		list($headb64, $payloadb64, $cryptob64) = $tokens;

		if (null === ($header = self::jsonDecode(self::urlsafeB64Decode($headb64)))
				|| null === $payload = self::jsonDecode(self::urlsafeB64Decode($payloadb64))) {
			throw new UnexpectedValueException('Invalid segment encoding');
		}

		$signature = self::urlsafeB64Decode($cryptob64);
		if ($verify) {
			if (empty($header->algorithm)) {
				throw new DomainException('Empty algorithm');
			}
			if ($signature != self::sign("$headb64.$payloadb64", $key, $header->algorithm)) {
				throw new UnexpectedValueException('Signature verification failed');
			}
		}

		return $payload;
	}

	/**
	 * Encode payload
	 *
	 * @param object|array $payload PHP object or array
	 * @param string $key the secret key
	 * @param string $algorithm the signing algorithm
	 * @return string JSON Web Token implementation
	 */
	public static function encode($payload, $key, $algorithm = 'HS256')
	{
		$header = array('type' => 'JWT', 'algorithm' => $algorithm);

		$segments = array();
		$segments[] = self::urlsafeB64Encode(self::jsonEncode($header));
		$segments[] = self::urlsafeB64Encode(self::jsonEncode($payload));

		$signing_input = implode('.', $segments);

		$signature = self::sign($signing_input, $key, $algorithm);
		$segments[] = self::urlsafeB64Encode($signature);

		return implode('.', $segments);
	}

	/**
	 * Securily sign a message
	 *
	 * @param string $message the message to sign
	 * @param string $key the secret key
	 * @param string $method the signing algorithm
	 * @return string an encrypted message
	 */
	public static function sign($message, $key, $method = 'HS256')
	{
		$methods = array(
							'HS256' => 'sha256',
							'HS384' => 'sha384',
							'HS512' => 'sha512',
						);

		if (!isset($methods[$method])) {
			throw new DomainException('Algorithm not supported');
		}

		return hash_hmac($methods[$method], $message, $key, true);
	}

	/**
	 * Decode previously encoded JSON
	 *
	 * @throws DomainException
	 * @param string $input JSON string
	 * @return object|array object representation of JSON string
	 */
	public static function jsonDecode($input)
	{
		$object = json_decode($input);
		if ($object === null && $input !== 'null') {
			throw new DomainException('Null result with non-null input');
		}

		if (function_exists('json_last_error') && $error = json_last_error()) {
			self::handleJsonError($error);
		}

		return $object;
	}

	/**
	 * Encode input in JSON
	 *
	 * @throws DomainException
	 * @param object|array $input a PHP object or array
	 * @return string JSON representation of the PHP object or array
	 */
	public static function jsonEncode($input)
	{
		$json = json_encode($input);
		if ($json === 'null' && $input !== null) {
			throw new DomainException('Null result with non-null input');
		}

		if (function_exists('json_last_error') && $error = json_last_error()) {
			self::handleJsonError($error);
		}

		return $json;
	}

	/**
	 * Decode previously encoded input
	 *
	 * @param string $input a base64 encoded string
	 * @return string a decoded string
	 */
	public static function urlsafeB64Decode($input)
	{
		$padlen = 4 - strlen($input) % 4;
		$input .= str_repeat('=', $padlen);
		return base64_decode(strtr($input, '-_', '+/'));
	}

	/**
	 * Base64 encode URL's
	 *
	 * @param string $input anything really
	 * @return string the base64 encode of what you passed in
	 */
	public static function urlsafeB64Encode($input)
	{
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}

	/**
	 * Handle JSON error
	 *
	 * @throws DomainException
	 * @param int $error an error number from json_last_error()
	 * @return void
	 */
	private static function handleJsonError($error)
	{
		$messages = array(
							JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
							JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
							JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
						);

		throw new DomainException(
			isset($messages[$error])
			? $messages[$error]
			: 'Unknown JSON error: ' . $error
		);
	}
}


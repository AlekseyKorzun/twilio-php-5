<?php
namespace Library\Twilio\Api\Action;
use Library\Twilio\Api\Resource\Listing;

/**
 * Twilio call resource
 *
 * @package Library
 * @subpackage Twilio\Api\Action
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Call extends Listing
{
	/**
	 * Check if application SID is valid
	 *
	 * @param $id application serial identifier
	 * @return bool
	 */
	public static function isApplicationSid($id)
	{
		return (bool) (strlen($id) == 34 && !(strpos($id, 'AP') === false));
	}

	/**
	 * Create method overwrite
	 *
	 * @param string $from number you are calling from
	 * @param string $to number you are calling to
	 * @param string $url url or application serial identifier
	 * @param array $parameters
	 * @return Instance
	 */
	public function create($from, $to, $url, array $parameters = array())
	{
		$parameters['To'] = $to;
		$parameters['From'] = $from;

		if (self::isApplicationSid($url)) {
			$parameters['ApplicationSid'] = $url;
		} else {
			$parameters['Url'] = $url;
		}

		return parent::_create($parameters);
	}
}

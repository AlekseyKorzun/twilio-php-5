<?php
namespace Library\Twilio\Api\Action;
use Library\Twilio\Api\Action\AvailablePhoneNumber\Helper;
use Library\Twilio\Api\Resource\Listing;

/**
 * Twilio available phone number resource
 *
 * @package Library
 * @subpackage Twilio\Api\Action
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class AvailablePhoneNumber extends Listing
{
	/**
	 * Local number identifier
	 *
	 * @var string
	 */
	const TYPE_LOCAL = 'Local';

	/**
	 * Toll free number identifier
	 *
	 * @var string
	 */
	const TYPE_TOLL_FREE = 'TollFree';

	/**
	 * Get local numbers
	 *
	 * @param string country The 2-digit country code you'd like to search for
	 * numbers e.g. ('US', 'CA', 'GB')
	 * @return Helper
	 */
	public function getLocal($country)
	{
		$helper = new Helper();
		$helper->set(
						'getList',
						array($this, 'getList'),
						array($country, self::TYPE_LOCAL)
					);
		return $helper;
	}

	/**
	 * Get toll free numbers
	 *
	 * @param string country The 2-digit country code you'd like to search for
	 * numbers e.g. ('US', 'CA', 'GB')
	 * @return Helper
	 */
	public function getTollFree($country)
	{
		$helper = new Helper();
		$helper->set(
						'getList',
						array($this, 'getList'),
						array($country,  self::TYPE_TOLL_FREE)
					);
		return $helper;
	}

	/**
	 * Get a list of all available phone numbers.
	 *
	 * @param string country The 2-digit country code you'd like to search for
	 * numbers e.g. ('US', 'CA', 'GB')
	 * @param string type number type
	 * @param array $parameters
	 * @return Instance
	 */
	public function getList($country, $type, array $parameters = array())
	{
		return self::client()->retrieveData($this->uri() . "/$country/$type", $parameters);
	}

	/**
	 * Get resource name
	 *
	 * @return string
	 */
	public function getResourceName()
	{
		return 'Country';
	}
}
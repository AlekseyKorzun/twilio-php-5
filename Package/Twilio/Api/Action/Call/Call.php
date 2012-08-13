<?php
namespace Library\Twilio\Api\Action\Call;
use Library\Twilio\Api\Resource\Instance;

/**
 * Twilio call instance
 *
 * @package Library
 * @subpackage Twilio\Api\Action\Call
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Call extends Instance
{
	/**
	 * Initializer
	 *
	 * @return void
	 */
	protected function _init()
	{
		$this->_setupActions(
								'notifications',
								'recordings'
							);
	}

	/**
	 * Hang up the call
	 *
	 * @return void
	 */
	public function hangup()
	{
		$this->update('Status', 'completed');
	}

	/**
	 * Route call
	 *
	 * @param string $url
	 * @return void
	 */
	public function route($url) {
		$this->update('Url', $url);
	}
}
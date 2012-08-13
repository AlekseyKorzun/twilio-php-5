<?php
namespace Library\Twilio\Api\Action\Conference;
use Library\Twilio\Api\Resource\Instance;

/**
 * Twilio conference instance
 *
 * @package Library
 * @subpackage Twilio\Api\Action\Conference
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Conference extends Instance
{
	/**
	 * Initializer
	 *
	 * @return void
	 */
	protected function _init()
	{
		$this->_setupActions(
								'participants'
							);
	}
}

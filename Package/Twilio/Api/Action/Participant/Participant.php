<?php
namespace Library\Twilio\Api\Action\Participant;

use Library\Twilio\Api\Resource\Instance;

/**
 * Twilio participant instance
 *
 * @package Library
 * @subpackage Twilio\Api\Action\Participant
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Participant extends Instance
{
	/**
	 * Mute participant
	 *
	 * @return void
	 */
	public function mute()
	{
		$this->update('Muted', 'true');
	}
}


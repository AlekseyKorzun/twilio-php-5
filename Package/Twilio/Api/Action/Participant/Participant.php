<?php
namespace Twilio\Api\Action\Participant;

use Twilio\Api\Resource\Instance;

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
     */
    public function mute()
    {
        $this->update('Muted', 'true');
    }
}


<?php
namespace Twilio\Api\Action\Call;

use Twilio\Api\Resource\Instance;

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
     */
    protected function init()
    {
        $this->setupActions(
            'notifications',
            'recordings'
        );
    }

    /**
     * Hang up the call
     */
    public function hangup()
    {
        $this->update('Status', 'completed');
    }

    /**
     * Route call
     *
     * @param string $url
     */
    public function route($url)
    {
        $this->update('Url', $url);
    }
}


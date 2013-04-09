<?php
namespace Twilio\Api\Action;

use \BadMethodCallException;
use Twilio\Api\Resource\Listing;

/**
 * Twilio connect app resource
 *
 * @package Library
 * @subpackage Twilio\Api\Action
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class ConnectApp extends Listing
{
    /**
     * Method overwrite
     *
     * @throws BadMethodCallException
     * @param mixed $name
     * @param string[] $parameters
     */
    public function create($name, array $parameters = array())
    {
        throw new BadMethodCallException('Not allowed');
    }
}


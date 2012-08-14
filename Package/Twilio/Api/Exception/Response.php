<?php
namespace Twilio\Api\Exception;

use \Exception;

/**
 * Exception handler for unsuccessful service responses
 *
 * @package Library
 * @subpackage Twilio\Api\Exception
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Response extends Exception
{
	/**
	 * Returned status
	 *
	 * @var string
	 */
	protected $status;

	/**
	 * Additional information
	 *
	 * @var string
	 */
	protected $info;

	/**
	 * Constructor
	 *
	 * @param string $status returned status
	 * @param string $message returned message
	 * @param int $code returned code
	 * @param string $info additional information
	 * @return void
	 */
	public function __construct($status, $message, $code = 0, $info = '')
	{
		$this->status = $status;
		$this->info = $info;

		parent::__construct($message, $code);
	}

	/**
	 * Retrieve status
	 *
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Retrieve information
	 *
	 * @return string
	 */
	public function getInfo()
	{
		return $this->info;
	}
}


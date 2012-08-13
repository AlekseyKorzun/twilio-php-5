<?php
namespace Library\Twilio\Api\Resource\Listing;
use \Iterator;
use \BadMethodCallException;
use Library\Twilio\Api\Exception\Response as ResponseException;

/**
 * Paginator for pages returned via API results
 *
 * @package Library
 * @subpackage Twilio\Api\Resource\Listing
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Paginator implements Iterator
{
	/**
	 * Default size of pages
	 *
	 * @var int
	 */
	const DEFAULT_PAGE_SIZE = 50;

	/**
	 * Current items
	 *
	 * @var array
	 */
	protected $_items = array();

	/**
	 * Current page
	 *
	 * @var int
	 */
	protected $_page;

	/**
	 * Current page size
	 *
	 * @var int
	 */
	protected $_size;

	/**
	 * Filters
	 *
	 * @var array
	 */
	protected $_filters;

	/**
	 * Method to generate pages
	 *
	 * @var mixed
	 */
	protected $_generator;

	/**
	 * Snapshot of current state
	 *
	 * @var array
	 */
	protected $_snapshot;

	/**
	 * Storage for URI for the next page returned via generator for
	 * each iteration
	 *
	 * @var string
	 */
	protected $_nextUri;

	/**
	 * Class constructor
	 *
	 * @param mixed $generator
	 * @param int $page
	 * @param int $size
	 * @param array $filters
	 * @return void
	 */
	public function __construct($generator, $page, $size = self::DEFAULT_PAGE_SIZE, $filters)
	{
		$this->_generator = $generator;
		$this->_page = $page;
		$this->_size = $size;
		$this->_filters = $filters;

		// Save current state for rewind()
		$this->_snapshot = array(
									'page' => $page,
									'size' => $size,
									'filters' => $filters,
								);
	}

	/**
	 * Current item
	 *
	 * @return mixed
	 */
	public function current()
	{
		return current($this->_items);
	}

	/**
	 * Current key
	 *
	 * @return mixed
	 */
	public function key()
	{
		return key($this->_items);
	}

	/**
	 * Return the next item in the list, making another HTTP call to the next
	 * page of resources if necessary.
	 *
	 * @return void
	 */
	public function next()
	{
		$this->_loadIfNecessary();
		return next($this->_items);
	}

	/**
	 * Restore everything to the way it was before we began paging. This gets
	 * called at the beginning of any foreach() loop
	 *
	 * @return void
	 */
	public function rewind()
	{
		if ($this->_snapshot) {
			foreach ($this->_snapshot as $snapshot => $value) {
				$this->$snapshot = $value;
			}
		}

		$this->_items = array();
		$this->_nextUri = null;
	}

	/**
	 * Disable build in method by overwriting it
	 *
	 * @throws BadMethodCallException
	 * @return void
	 */
	public function count()
	{
		throw new BadMethodCallException('Not allowed');
	}

	/**
	 * Overwrites default method to check if current position is valid
	 *
	 * @return void
	 */
	public function valid()
	{
		if ($this->_loadIfNecessary()) {
			return key($this->_items) !== null;
		}

		return false;
	}

	/**
	 * Fill $this->items with a new page from the API, if necessary.
	 *
	 * @return bool
	 */
	protected function _loadIfNecessary()
	{
		if (!$this->_items || key($this->_items) === null) {
			try {
				$page = call_user_func_array($this->_generator,
																array(
																		$this->_page,
																		$this->_size,
																		$this->_filters,
																		$this->_nextUri,
																	));

				$this->_nextUri = $page->_nextUri;
				$this->_items = (array) $page->items();
				$this->_page++;
			} catch (ResponseException $exception) {
				// 20006 is an out of range paging error, everything else is valid
				if ($exception->getCode() != 20006) {
					throw $exception;
				}

				return false;
			}
		}

		return true;
	}
}
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
	protected $items = array();

	/**
	 * Current page
	 *
	 * @var int
	 */
	protected $page;

	/**
	 * Current page size
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * Filters
	 *
	 * @var array
	 */
	protected $filters;

	/**
	 * Method to generate pages
	 *
	 * @var mixed
	 */
	protected $generator;

	/**
	 * Snapshot of current state
	 *
	 * @var array
	 */
	protected $snapshot;

	/**
	 * Storage for URI for the next page returned via generator for
	 * each iteration
	 *
	 * @var string
	 */
	protected $nextUri;

	/**
	 * Class constructor
	 *
	 * @param mixed $generator
	 * @param int $page
	 * @param array $filters
	 * @param int $size
	 * @return void
	 */
	public function __construct($generator, $page, $filters, $size = self::DEFAULT_PAGE_SIZE)
	{
		$this->generator = $generator;
		$this->page = $page;
		$this->size = $size;
		$this->filters = $filters;

		// Save current state for rewind()
		$this->snapshot = array(
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
		return current($this->items);
	}

	/**
	 * Current key
	 *
	 * @return mixed
	 */
	public function key()
	{
		return key($this->items);
	}

	/**
	 * Return the next item in the list, making another HTTP call to the next
	 * page of resources if necessary.
	 *
	 * @return void
	 */
	public function next()
	{
		$this->loadIfNecessary();
		return next($this->items);
	}

	/**
	 * Restore everything to the way it was before we began paging. This gets
	 * called at the beginning of any foreach() loop
	 *
	 * @return void
	 */
	public function rewind()
	{
		if ($this->snapshot) {
			foreach ($this->snapshot as $snapshot => $value) {
				$this->$snapshot = $value;
			}
		}

		$this->items = array();
		$this->nextUri = null;
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
		if ($this->loadIfNecessary()) {
			return key($this->items) !== null;
		}

		return false;
	}

	/**
	 * Fill $this->items with a new page from the API, if necessary.
	 *
	 * @return bool
	 */
	protected function loadIfNecessary()
	{
		if (!$this->items || key($this->items) === null) {
			try {
				$page = call_user_func_array(
					$this->generator,
					array(
							$this->page,
							$this->size,
							$this->filters,
							$this->nextUri,
						)
				);

				$this->nextUri = $page->nextUri;
				$this->items = (array) $page->items();
				$this->page++;
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


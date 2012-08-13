<?php
namespace Library\Twilio\Api\Resource\Listing;
use \IteratorAggregate;

/**
 * Page containing items
 *
 * @package Library
 * @subpackage Twilio\Api\Resource\Listing
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Page implements IteratorAggregate {
	/**
	 * Page
	 *
	 * @var array
	 */
	protected $_page;

	/**
	 * List of items within this page
	 *
	 * @var array
	 */
	protected $_items;

	/**
	 * Storage for URI for the next page
	 *
	 * @var string
	 */
	protected $_nextUri;

	/**
	 * Class constructor
	 *
	 * @param object $page object returned from server
	 * @param string $name key of the item list
	 * @param string $nextUri
	 * @return void
	 */
	public function __construct($page, $name, $nextUri = null)
	{
		$this->_page = $page;
		print_r($page);
		$this->_items = $page->{$name};
		$this->_nextUri = $nextUri;
	}

	/**
	 * The item list of the page
	 *
	 * @return array items
	 */
	public function items()
	{
		return $this->_items;
	}

	/**
	 * Magic method to allow retrieving the properties of the wrapped page.
	 *
	 * @param string $property name of the property to retrieve
	 * @return mixed
	 */
	public function __get($property)
	{
		return $this->_page->$property;
	}

	/**
	 * Implementation of IteratorAggregate::getIterator().
	 *
	 * @return array
	 */
	public function getIterator()
	{
		return $this->getItems();
	}
}
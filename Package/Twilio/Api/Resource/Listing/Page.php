<?php
namespace Twilio\Api\Resource\Listing;

use \IteratorAggregate;

/**
 * Page containing items
 *
 * @package Library
 * @subpackage Twilio\Api\Resource\Listing
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
class Page implements IteratorAggregate
{
    /**
     * Page
     *
     * @var object
     */
    protected $page;

    /**
     * List of items within this page
     *
     * @var string[]
     */
    protected $items;

    /**
     * Storage for URI for the next page
     *
     * @var string
     */
    protected $nextUri;

    /**
     * Class constructor
     *
     * @param object $page object returned from server
     * @param string $name key of the item list
     * @param string $nextUri
     */
    public function __construct($page, $name, $nextUri = null)
    {
        $this->page = $page;
        $this->items = $page->{$name};
        $this->nextUri = $nextUri;
    }

    /**
     * The item list of the page
     *
     * @return string[] items
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * Magic method to allow retrieving the properties of the wrapped page.
     *
     * @param string $property name of the property to retrieve
     * @return mixed
     */
    public function __get($property)
    {
        return $this->page->$property;
    }

    /**
     * Implementation of IteratorAggregate::getIterator().
     *
     * @return string[]
     */
    public function getIterator()
    {
        return $this->getItems();
    }
}


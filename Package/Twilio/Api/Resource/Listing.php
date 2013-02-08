<?php
namespace Twilio\Api\Resource;

use \IteratorAggregate;
use Twilio\Api\Resource;
use Twilio\Api\Resource\Instance;
use Twilio\Api\Resource\Listing\Page;
use Twilio\Api\Resource\Listing\Paginator;

/**
 * Abstrresource of a list resource from the Twilio API
 *
 * @package Library
 * @subpackage Twilio\Api\Resource
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 */
abstract class Listing extends Resource implements IteratorAggregate
{
	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		if (!isset($this->resource)) {
            $paths = explode('\\', get_class($this));
			$this->resource = get_class($this) . '\\' . array_pop($paths);
		}

		parent::__construct();
	}

	/**
	 * Gets a resource from this list.
	 *
	 * @param string $identifier resource identifier
	 * @return Instance
	 */
	public function get($identifier)
	{
		$resource = new $this->resource();
		$resource->setUri($this->uri(), $identifier);
		$resource->identifier = $identifier;
		return $resource;
	}

	/**
	 * Construct an InstanceResource with the specified params.
	 *
	 * @param array $parameters usually a JSON HTTP response from the API
	 * @return Instance
	 */
	public function getObjectFromJson($parameters)
	{
		$uri = $this->uri();

		if (isset($parameters->identifier)) {
			$uri .=  '/' . $parameters->identifier;
		}

		$resource = new $this->resource();
		$resource->setUri($uri);
		return $resource;
	}

	/**
	 * Deletes a resource from this list.
	 *
	 * @param string $identifier resource identifier
	 * @param array $parameters
	 * @return void
	 */
	public function delete($identifier, array $parameters = array())
	{
		self::client()->deleteData($this->uri() . '/' . $identifier, $parameters);
	}

	/**
	 * Create a resource on the list and then return its representation as an
	 * InstanceResource.
	 *
	 * @param array $parameters The parameters with which to create the resource
	 * @return Instance
	 */
	protected function create(array $parameters)
	{
		$uri = $this->uri();

		$parameters = self::client()->createData($uri, $parameters);
		if (isset($parameters->identifier)) {
			$uri .=  '/' . $parameters->identifier;
		}

		$resource = new $this->resource($parameters);
		$resource->setUri($uri);
		return $resource;
	}

	/**
	 * Returns a page of InstanceResources from this list.
	 *
	 * @param int $page start page
	 * @param int $size number of items per page
	 * @param array $filters optional filters
	 * @param string $pagingUri if provided, the $page and $size parameters
	 * will be ignored and this URI will be requested directly.
	 * @return Page
	 */
	public function getPage($page = 0, $size = Paginator::DEFAULT_PAGE_SIZE, array $filters = array(), $pagingUri = null)
	{
		if (!is_null($pagingUri)) {
			$page = self::client()->retrieveData($pagingUri, $filters, true);
		} else {
			$page = self::client()->retrieveData(
				$this->uri(),
				array(
						'Page' => $page,
						'PageSize' => $size,
					) + $filters
			);
		}

		// Retrieve resource alias
        $resource = array_keys((array) $page);
        $resource = array_shift($resource);

		// Create a new PHP object for each JSON object in the API response
		$page->$resource = array_map(
			array($this, 'getObjectFromJson'),
			$page->$resource
		);

		$next_page_uri = null;
		if (isset($page->next_page_uri)) {
			$next_page_uri = $page->next_page_uri;
		}

		return new Page($page, $resource, $next_page_uri);
	}

	/**
	 * Returns an iterable list of InstanceResources
	 *
	 * @param int $page start page
	 * @param int $size number of items per page
	 * @param array $filters optional filters
	 *
	 * The filter array can accept full datetimes when StartTime or DateCreated
	 * are used. Inequalities should be within the key portion of the array and
	 * multiple filter parameters can be combined for more specific searches.
	 *
	 * eg.
	 *   array('DateCreated>' => '2011-07-05 08:00:00', 'DateCreated<' => '2011-08-01')
	 * or
	 *   array('StartTime<' => '2011-07-05 08:00:00')
	 * @return Paginator
	 */
	public function paginator($page = 0, $size = Paginator::DEFAULT_PAGE_SIZE, array $filters = array())
	{
		return new Paginator(
			array($this, 'getPage'),
			$page,
			$filters,
			$size
		);
	}

	/**
	 * Implementing placeholder method for IteratorAggregate
	 *
	 * @see IteratorAggregate
	 * @return Paginator
	 */
	public function getIterator()
	{
		return $this->paginator();
	}
}


<?php
namespace Library\Twilio\Utility;
use \SimpleXmlElement;
use Library\Twilio\Utility\Exception\Twiml as TwimlException;

/**
 * Twilio capability token generator
 *
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 * @package Library
 * @subpackage Twilio\Utility
 */
class Twiml
{
	/**
	 * Storage for parsed element
	 *
	 * @var SimpleXmlElement
	 */
	protected $_element;

	/**
	 * Constructs a Twiml response
	 *
	 * @throws TwimlException
	 * @param SimpleXmlElement|array $argument the element to wrap/attributes
	 * to add to the element. If nothing is passed we will initialize an empty element
	 * named 'Response'.
	 * @return void
	 */
	public function __construct($argument = null)
	{
		switch (true) {
			case $argument instanceof SimpleXmlElement:
				$this->_element = $argument;
				break;
			case $argument === null:
				$this->_element = new SimpleXmlElement('<Response/>');
				break;
			case is_array($argument):
				$this->_element = new SimpleXmlElement('<Response/>');
				foreach ($argument as $name => $value) {
					$this->_element->addAttribute($name, $value);
				}
				break;
			default:
				throw new TwimlException('Invalid argument');
		}
	}

	/**
	 * Converts method calls into Twiml verbs.
	 *
	 * A basic example:
	 *
	 *     php> print $this->say('hello');
	 *     <Say>hello</Say>
	 *
	 * An example with attributes:
	 *
	 *     php> print $this->say('hello', array('voice' => 'woman'));
	 *     <Say voice="woman">hello</Say>
	 *
	 * You could even just pass in an attributes array, omitting the noun:
	 *
	 *     php> print $this->gather(array('timeout' => '20'));
	 *     <Gather timeout="20"/>
	 *
	 * @param string $verb The Twiml verb.
	 * @param array|string $attributes
	 * @return SimpleXmlElement
	 */
	public function __call($verb, array $attributes)
	{
		list($noun, $attributes) = $attributes + array('', array());

		if (is_array($noun)) {
			list($attributes, $noun) = array($noun, '');
		}

		/* addChild does not escape XML, while addAttribute does. This means if
		 * you pass unescaped ampersands ("&") to addChild, you will generate
		 * an error.
		 *
		 * Some inexperienced developers will pass in unescaped ampersands, and
		 * we want to make their code work, by escaping the ampersands for them
		 * before passing the string to addChild. (with htmlentities)
		 *
		 * However other people will know what to do, and their code
		 * already escapes ampersands before passing them to addChild. We don't
		 * want to break their existing code by turning their &amp;'s into
		 * &amp;amp;
		 *
		 * So we end up with the following matrix:
		 *
		 * We want & to turn into &amp; before passing to addChild
		 * We want &amp; to stay as &amp; before passing to addChild
		 *
		 * The following line accomplishes the desired behavior.
		 */
		$normalized = htmlentities($noun, null, null, false);

		// Then escape it again
		$child = empty($noun)
			? $this->_element->addChild(ucfirst($verb))
			: $this->_element->addChild(ucfirst($verb), $normalized);


		foreach ($attributes as $name => $value) {
			/* Note that addAttribute escapes raw ampersands by default, so we
			 * haven't touched its implementation. So this is the matrix for
			 * addAttribute:
			 *
			 * & turns into &amp;
			 * &amp; turns into &amp;amp;
			 */
			if (is_bool($value)) {
				$value = ($value === true) ? 'true' : 'false';
			}

			$child->addAttribute($name, $value);
		}

		return new self($child);
	}

	/**
	 * Returns the object as XML.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$xml = $this->_element->asXml();

		return str_replace(
			'<?xml version="1.0"?>',
			'<?xml version="1.0" encoding="UTF-8"?>', $xml);
	}
}
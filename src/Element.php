<?php

declare( strict_types=1 );

namespace Akdr\Selma;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebElement;
use JBZoo\Utils\Filter;
use ReflectionClass;
use function array_walk;

class Element {
	/**
	 * @var Navigation
	 */
	private $navigation;

	/**
	 * @var RemoteWebElement
	 */
	public $element;

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @var string
	 */
	private $selector;

	/**
	 * @var bool
	 */
	public $hasClass;

	/**
	 * Element constructor.
	 *
	 * @param Navigation $navigation
	 * @param array $options
	 */
	public function __construct( Navigation $navigation, array $options ) {
		$this->navigation = $navigation;
		// Walk through the options array and set variables in the class.
		// Selector must be the first option in the array.
		array_walk( $options, array( $this, 'resolveOptions' ) );

		return $this;
	}

	/**
	 * Keys are: selector (non-optional), element, attribute, click, class, input, pressKey, delay
	 * Set also resets saved values.
	 * @return Element
	 * @var array $options Array will be initiated in the order presented.
	 */
	public function set( array $options ): Element {
		$this->element = $this->value = $this->hasClass = $this->selector = null;

		array_walk( $options, array( $this, 'resolveOptions' ) );

		return $this;
	}

	private function resolveOptions( $value, $key ) {
		switch ( $key ) {
			case 'selector':
				$this->selector = $value;
				$this->getElementBySelector( $value );
				break;

			case 'element':
				$this->element = $value;
				break;

			case 'attribute':
				if ( $this->element == null ) {
					$this->value = null;
				} else {
					$this->value = ( $value == 'text' ) ? $this->element->getText() : $this->element->getAttribute( $value );
				}
				break;

			case 'click':
				if ( $this->element == null && $value === true ) {
					error_log( 'Could not click on element because it is not selected. Selector: ' . $this->selector );
					break;
				}
				( $value === true ) ? $this->element->click() : null;
				break;

			case 'class':
				$classes        = $this->element->getAttribute( 'class' );
				$this->hasClass = in_array( $value, explode( ' ', $classes ) );
				break;

			case 'input':
				$this->element->sendKeys( $value );
				break;

			case 'pressKey':
				$ref = new ReflectionClass( 'Facebook\WebDriver\WebDriverKeys' );
				$this->element->sendKeys( $ref->getConstant( $value ) );
				break;

			case 'delay':
				$this->navigation->sleep( $value );
				break;

			case 'waitForElement':
				$i = 0;
				while ( $this->element == null ) {
					$i ++;
					if ( $i > $value ) {
						break;
					}
					$this->navigation->sleep( 10000 );
					$this->getElementBySelector( $this->selector );
				}
				break;
		}

		return;
	}

	private function getElementBySelector( string $selector ): bool {
		try {
			if ( $this->element == null ) {
				$this->element = $this->navigation->webDriver->findElement( WebDriverBy::cssSelector( $selector ) );
			} else {
				$this->element = $this->element->findElement( WebDriverBy::cssSelector( $selector ) );
			}

			return true;
		} catch ( Exception $e ) {
			$this->element = null;

		}

		return false;
	}

	public function getValue( $returnType = null ) {
		if ( is_null( $this->value ) ) {
			return null;
		}

		switch ( $returnType ) {
			case 'int':
				return Filter::int( (string) $this->value );
			case 'float':
				return Filter::float( (string) $this->value );
			case null:
			default:
				return $this->value;
		}
	}

	public function findElement( $selector ): ?RemoteWebElement {
		try {
			$remoteWebElement = $this->navigation->webDriver->findElement( WebDriverBy::cssSelector( $selector ) );
		} catch ( Exception $e ) {
			$remoteWebElement = null;
		}

		return $remoteWebElement;
	}

	public function findElements( $selector ): ?array {
		try {
			$remoteWebElements = $this->navigation->webDriver->findElements( WebDriverBy::cssSelector( $selector ) );
		} catch ( Exception $e ) {
			$remoteWebElements = null;
		}

		return $remoteWebElements;
	}
}
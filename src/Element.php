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
	private Navigation $navigation;

	/**
	 * @var ?RemoteWebElement
	 */
	public ?RemoteWebElement $element;

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @var ?string
	 */
	private ?string $selector;

	/**
	 * @var ?bool
	 */
	private ?bool $hasClass;

	/**
	 * Element constructor.
	 *
	 * @param Navigation $navigation
	 * @param array $options
	 */
	public function __construct( Navigation $navigation, array $options = [] ) {
		$this->navigation = $navigation;
		// Walk through the options array and set variables in the class.
		// Selector must be the first option in the array.
		array_walk( $options, array( $this, 'resolveOptions' ) );

		return $this;
	}


	/**
	 * @param string|null $cssSelector The CSS selector for the element as a string ex. '.header ul li'
	 * @param RemoteWebElement|null $domElement RemoteWebElement, same as DOM element in the browser
	 * @param string|null $attribute Attribute of the selected element
	 * @param bool|null $doClick If true - Click the selected element
	 * @param string|null $hasClass Sets ->hasClass to true or false if the selected element has the class
	 * @param string|null $insertTextIntoInput Inserts a string into the selected element if its a input or textfield
	 * @param string|null $keyboardInput Inputs a key into the browser.
	 * See https://php-webdriver.github.io/php-webdriver/master/Facebook/WebDriver/WebDriverKeys.html for more information
	 * @param int|null $waitForElementToAppearInMs Wait for an element to appear in the browser, then proceed
	 * @param bool $isOptional If the element is optional, we silently abort if something goes wrong
	 * @param bool|null $onlyVisible
	 * @param string|null $return Specifies what should be returned. 'element', 'value', 'hasClass',
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function setActions(
		?string $cssSelector = null,
		?RemoteWebElement $domElement = null,
		?string $attribute = null,
		?bool $doClick = null,
		?string $hasClass = null,
		?string $insertTextIntoInput = null,
		?string $keyboardInput = null,
		?int $waitForElementToAppearInMs = null,
		bool $isOptional = false,
		?bool $onlyVisible = null,
		?string $return = null,
	): mixed {
		$this->element = $this->value = $this->hasClass = $this->selector = null;

		if(is_null($cssSelector) && is_null($domElement)){
			if(!$isOptional){
				throw new Exception('No valid cssSelector or Element is set');
			} else {
				return null;
			}
		}

		if(!is_null($domElement)){
			$this->element = $domElement;
		}

		if(!is_null($cssSelector) && is_null($waitForElementToAppearInMs)){
			$this->selector = $cssSelector;

			try {
				if(is_null($this->element)) {
					$this->element = $this->navigation->webDriver->findElement( WebDriverBy::cssSelector( $cssSelector ) );
				} else {
					$this->element = $this->element->findElement( WebDriverBy::cssSelector( $cssSelector ) );
				}
			} catch (Exception $e){
				if($isOptional) {
					return null;
				} else {
					throw new Exception( 'Could not locate child element with selector ' . $cssSelector );
				}
			}
		}

		if(!is_null($waitForElementToAppearInMs)) {
			$startTime = microtime(true);
			$msInFloat = $waitForElementToAppearInMs / 1_000_000;
			do {
				$timeSinceStart = microtime(true) - $startTime;
				if ( $timeSinceStart > $msInFloat ) {
					break;
				}

				usleep(1000);
				try {
					$this->element = $this->navigation->webDriver->findElement( WebDriverBy::cssSelector( $cssSelector ) );
					if(!$this->element->isDisplayed()){
						$this->element = null;
					}
				} catch (Exception $e){
					$this->element = null;
				}

				$waitingElement = $this->element;
			} while ( is_null($waitingElement) );

			$endTime = microtime(true);
			$status = (is_null($this->element)) ? 'Not found' : 'Found';
			$this->navigation->cli('##### WaitingForElement Info ######');
			$this->navigation->cli('Waited for: ' . ($endTime - $startTime) . ' seconds.');
			$this->navigation->cli('Total waiting time approved: ' . $msInFloat . ' seconds.');
			$this->navigation->cli('cssSelector: ' . $cssSelector);
			$this->navigation->cli('Status: ' . $status);
			$this->navigation->cli('#########################');
		}

		if(is_null($this->element)){
			if($isOptional){
				error_log('Element with cssSelector ' . $cssSelector . ' is not set, it is not found or invalid. Skipping.');
				return null;
			} else {
				error_log('Element with cssSelector ' . $cssSelector . ' is not set, it is not found or invalid');
				return null;
			}
		}

		if($onlyVisible === true){
			if($this->element->isDisplayed() === false){
				$this->element = null;
				error_log('Element is not being displayed');
				return null;
			}
		}

		if(!is_null($attribute)){
			$this->value = ( $attribute == 'text' ) ? $this->element->getText() : $this->element->getAttribute( $attribute );
		}

		if(!is_null($hasClass)){
			$classes = $this->element->getAttribute( 'class' );
			$this->hasClass = in_array( $hasClass, explode( ' ', $classes ) );
		}

		if(!is_null($insertTextIntoInput)){
			$this->navigation->cli('Inserting text string into input field: ' . $insertTextIntoInput);
			$this->element->sendKeys( $insertTextIntoInput );
		}

		if(!is_null($keyboardInput)){
			$ref = new ReflectionClass( 'Facebook\WebDriver\WebDriverKeys' );
			$this->element->sendKeys( $ref->getConstant( $keyboardInput ) );
		}

		if(!is_null($doClick)){
			if(is_null($this->element) && $doClick === true ){
				throw new Exception('Could not click on element because it is not selected. Selector: ' . $cssSelector);
			}

			if(!$this->element->isDisplayed() && $doClick === true){
				throw new Exception('Trying to click and invisible DOM element. Aborting. Selector: ' . $cssSelector);
			}

			try {
				( $doClick === true ) ? $this->element->click() : null;
			} catch ( Exception $e ) {
				if(!$isOptional){
					throw new Exception( 'Element is probably not available. Crashing.' );
				} else {
					return null;
				}
			}
		}

		if(!is_null($return)){
			return match ( $return ) {
				'element' => $this->element,
				'hasClass' => $this->hasClass,
				default => $this->value,
			};
		}

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

		$this->options = [];
		$this->options = $options;
		array_filter($this->options, function($option){
			return !is_null($option);
		});

		array_walk( $this->options, array( $this, 'resolveOptions' ) );

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
					die();
				}
				try {
					( $value === true ) ? $this->element->click() : null;
				} catch ( Exception $e ) {
					error_log( 'Could not click selector: ' . $this->selector . '. Crashing.' );
					die();
				}
				break;

			case 'class':
			case 'hasClass':
				$classes        = $this->element->getAttribute( 'class' );
				$this->hasClass = in_array( $value, explode( ' ', $classes ) );
				break;

			case 'input':
			case 'inputKeys':
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

				$value = $value / 1000;

				$waitingElement = $this->element;

				while ( $waitingElement == null ) {

					$i ++;

					if ( $i > $value ) {
						error_log( 'Waited ' . ( $value ) . ' seconds. Can not find it, exiting.' );
						break;
					}

					$this->navigation->sleep( 1000 );

					$this->getElementBySelector( $this->selector );

					$waitingElement = $this->element;
				}
				break;
		}
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

	public function grabSelectorValue( $selector, $returnType = null, $attribute = null ) {
		$this->selector = $selector;
		$element        = $this->navigation->webDriver->findElement( WebDriverBy::cssSelector( $this->selector ) );
		$this->value    = ( $attribute == 'text' || is_null( $attribute ) ) ? $element->getText() : $element->getAttribute( $attribute );

		return $this->getValue( $returnType );
	}
}
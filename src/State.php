<?php

declare( strict_types=1 );

namespace Akdr\Selma;

class State {
	/**
	 * @var Navigation
	 */
	private $navigation;

	/**
	 * @var Element
	 */
	private $element;

	/**
	 * @var mixed
	 */
	public $originalValue;

	/**
	 * @var array
	 */
	public $originalValues;

	/**
	 * Element constructor.
	 *
	 * @param Navigation $navigation
	 * @param null|string $originalValue
	 */
	public function __construct( Navigation $navigation, $originalValue = null ) {
		$this->navigation = $navigation;
		// Start the element class to be able to retrieve data
		$this->element = new Element($navigation, []);
		$this->originalValue = $originalValue;
		$this->originalValues = [];
		return $this;
	}

	/**
	 * @param $selector
	 * @param string $attribute
	 * @param int $waitForInSeconds
	 * @param null $element
	 *
	 * @return bool|null
	 */
	public function observeAttributeChange($selector, $attribute = 'text', $waitForInSeconds = 30, $element = null): ?bool
	{
		if($selector === null || !$this->element->findElement($selector) )
		{
			return null;
		}

		if(!$this->originalValue){
			error_log('You must set $originalValue before executing the method');
			return null;
		}

		$currentTime = microtime(true);
		$exitTime = $currentTime + $waitForInSeconds;

		$argumentList = array(
			'selector' => $selector,
			'attribute' => $attribute
		);

		if(!is_null($element)){
			$argumentList = array('element' => $element) + $argumentList;
		}

		$observedElement = $this->element->set($argumentList);

		$originalState = $this->originalValue;

		$changedState = $observedElement->getValue();

		while($originalState === $changedState)
		{
			$currentTime = microtime(true);
			if ( $currentTime > $exitTime ) {
				error_log( '(' .$waitForInSeconds . ' sec). No change was in the attribute ' . $attribute . ' for ' . $selector);
				break;
			}

			usleep(10000);
			$observedElement = $this->element->set($argumentList);

			$changedState = $observedElement->getValue();
		}

		return $originalState !== $changedState;
	}

	/**
	 * @param $selector
	 * @param int $waitForInSeconds
	 *
	 * @return bool|null
	 */
	public function observeDOMChange($selector, $waitForInSeconds = 30): ?bool
	{
		if($selector === null )
		{
			return null;
		}

		$currentTime = microtime(true);
		$exitTime = $currentTime + $waitForInSeconds;

		$observedElement = $this->element->findElement($selector);

		$originalState = $observedElement;
		$observedState = $originalState;
		while($originalState === $observedState)
		{
			$currentTime = microtime(true);
			if ( $currentTime > $exitTime ) {
				error_log( '(' .$waitForInSeconds . ' sec). No change in the DOM detected for ' . $selector);
				break;
			}
			usleep(10000);
			$observedState = $this->element->findElement($selector);
		}

		return $originalState !== $observedState;
	}


	/**
	 * @param string $originalString
	 * @param callable $newStringFunction
	 * @param callable $returnFunction
	 * @param int $waitForInSeconds
	 *
	 * @return bool
	 */
	public function observeStringChange(string $originalString,
		callable $newStringFunction,
		callable $returnFunction,
		int $waitForInSeconds = 30): bool {

		// Get the function calling for the new value that we compare with the original string.
		$newString = $newStringFunction();

		// Get the current time in seconds
		$currentTime = microtime(true);

		// Add the time specified in $waitForInSeconds
		$exitTime = $currentTime + $waitForInSeconds;


		// While the original string is the same as the new, we continue the loop
		// If the time has passed specified in $waitForInSeconds, abort and return the comparison
		// at the bottom
		while($originalString === $newString){
			// Check the time again
			$currentTime = microtime(true);

			// Compare the time
			if ( $currentTime > $exitTime ) {
				// Input a error message to the terminal
				error_log( 'No update for the string ' . $originalString
				           . ' was found in ' . $waitForInSeconds . ' seconds.');
				break;
			}
			// Update the new string value
			$newString = $newStringFunction();
		}

		// Return true or false
		return $returnFunction($originalString, $newString);
	}

	/**
	 * @param $originalState
	 * @param int $waitForInSeconds
	 *
	 * @return bool|null
	 */
	public function observeURLChange($originalState, $waitForInSeconds = 30): ?bool
	{
		if($originalState === null )
		{
			return null;
		}

		$currentTime = microtime(true);
		$exitTime = $currentTime + $waitForInSeconds;

		$observedState = $this->originalValue;
		while($observedState === $originalState)
		{
			$currentTime = microtime(true);
			if ( $currentTime > $exitTime ) {
				error_log( '(' .$waitForInSeconds . 'sec). No change in the DOM detected for ' . $originalState);
				break;
			}

			usleep(10000);
			$observedState = $this->navigation->currentUrl();

		}
		return $observedState !== $originalState;
	}

	/**
	 * @param array $selectorArray
	 */
	public function setMultipleDomValues(array $selectorArray): void
	{
		foreach($selectorArray as $selector){
			$this->originalValues[$selector] = $this->element->findElement($selector);
		}
		return;
	}

	/**
	 * @param array $selectorArray
	 * @param int $waitForInSeconds
	 *
	 * @return string|null
	 */
	public function observeMultipleDOM(array $selectorArray, int $waitForInSeconds): ?string
	{
		if(empty($selectorArray))
			return null;

		$currentTime = microtime(true);
		$exitTime = $currentTime + $waitForInSeconds;
		$abort = false;

		while($abort === false)
		{
			$currentTime = microtime(true);
			if ( $currentTime > $exitTime ) {
				error_log( '(' .$waitForInSeconds . ' sec). No change in the DOM detected for selectorArray');
				break;
			}

			foreach($selectorArray as $selector)
			{
				$elementBeingObserved = $this->element->findElement($selector);
				if($elementBeingObserved !== $this->originalValues[$selector])
				{
					return $selector;
				}
			}

			usleep(10000);
		}
		return null;

	}
}
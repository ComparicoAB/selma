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
	 */
	public function __construct( Navigation $navigation, $originalValue = null ) {
		$this->navigation = $navigation;
		// Start the element class to be able to retrieve data
		$this->element = new Element($navigation, []);
		$this->originalValue = $originalValue;
		$this->originalValues = [];
		return $this;
	}

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

	public function observeDOMChange($selector, $waitForInSeconds = 30): ?bool
	{
		if($selector === null )
		{
			return null;
		}

		$currentTime = microtime(true);
		$exitTime = $currentTime + $waitForInSeconds;

		$observedElement = $this->element->findElement($selector);

		$originalState = serialize($observedElement);
		$observedState = $originalState;
		while($originalState === $observedState)
		{
			$currentTime = microtime(true);
			if ( $currentTime > $exitTime ) {
				error_log( '(' .$waitForInSeconds . ' sec). No change in the DOM detected for ' . $selector);
				break;
			}
			usleep(10000);
			$observedState = serialize($this->element->findElement($selector));
		}

		return $originalState !== $observedState;
	}

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

	public function setMultipleDomValues(array $selectorArray): void
	{
		foreach($selectorArray as $selector){
			$this->originalValues[$selector] = serialize($this->element->findElement($selector));
		}
		return;
	}

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
				if(serialize($this->element->findElement($selector)) !== $this->originalValues[$selector])
				{
					return $selector;
				}
			}

			usleep(10000);
		}
		return null;

	}
}
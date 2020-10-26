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
	 * Element constructor.
	 *
	 * @param Navigation $navigation
	 */
	public function __construct( Navigation $navigation, $originalValue = null ) {
		$this->navigation = $navigation;
		// Start the element class to be able to retrieve data
		$this->element = new Element($navigation, []);
		$this->originalValue = $originalValue;
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

		$i = 0;

		$waitForInMilliseconds = $waitForInSeconds * 10;

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
			$i++;
			if ( $i > $waitForInMilliseconds ) {
				error_log( '(' .$waitForInSeconds . 's) No change was in the attribute ' . $attribute . ' for ' . $selector);
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

		$i = 0;
		$waitForInMilliseconds = $waitForInSeconds * 100;

		$observedElement = $this->element->findElement($selector);

		$originalState = serialize($observedElement);
		$observedState = $originalState;

		while($originalState === $observedState)
		{
			$i++;
			if ( $i > $waitForInMilliseconds ) {
				error_log( '(' .$waitForInSeconds . 's). No change in the DOM detected for ' . $selector);
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

		$i = 0;

		$waitForInMilliseconds = $waitForInSeconds * 100;

		$changeIsMet = false;

		$observedState = $this->originalValue;

		while($observedState === $originalState)
		{
			$i++;
			if ( $i > $waitForInMilliseconds ) {
				error_log( '(' .$waitForInSeconds . 's). No change in the DOM detected for ' . $originalState);
				break;
			}

			usleep(10000);
			$observedState = $this->navigation->currentUrl();

		}

		return $changeIsMet;
	}

}
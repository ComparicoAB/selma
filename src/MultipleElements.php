<?php

declare( strict_types=1 );

namespace Akdr\Selma;

use Exception;
use Facebook\WebDriver\WebDriverBy;


class MultipleElements {
	/**
	 * @var Navigation
	 */
	private Navigation $navigation;

	public function __construct( Navigation $navigation ) {
		$this->navigation = $navigation;
		return $this;
	}

	/**
	 * @param string|null $cssSelector The CSS selector for the elements as a string ex. '.header ul li'
	 * @param array|null $domElements
	 * @param string|null $attribute Attribute of the selected element
	 * @param bool|null $onlyVisible
	 * @param int|null $waitForElementToAppearInMs Wait for an element to appear in the browser, then proceed
	 * @param bool $isOptional If the element is optional, we silently abort if something goes wrong
	 * @param string|null $return Specifies what should be returned. 'element', 'value', 'hasClass',
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public function setActions(
		?string $cssSelector = null,
		?array $domElements = [],
		?string $attribute = null,
		?bool $onlyVisible = null,
		?int $waitForElementToAppearInMs = null,
		bool $isOptional = false,
		?string $return = null,
	): ?array {
		$elements = $values = [];
		if(is_null($cssSelector) && is_null($domElements)){
			if(!$isOptional){
				throw new Exception('No valid cssSelector or Element is set');
			} else {
				return null;
			}
		}

		if(!empty($domElements)){
			$elements = $domElements;
		}

		if(!is_null($cssSelector) && is_null($waitForElementToAppearInMs)){
			$webdriverSelector = WebDriverBy::cssSelector( $cssSelector );
			error_log('Finding ' . $cssSelector);
			$elements = $this->navigation->webDriver->findElements( $webdriverSelector );
			error_log('Found ' . count($elements) . ' elements on the page');
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

		if(!is_null($waitForElementToAppearInMs)) {
			$waitingElements = [];
			$startTime = microtime(true);
			$msInFloat = $waitForElementToAppearInMs / 1_000_000;

			do {
				$timeSinceStart = microtime(true) - $startTime;
				if ( $timeSinceStart > $msInFloat ) {
					break;
				}

				usleep(1000);
				try {
					$elements = $this->navigation->webDriver->findElements( WebDriverBy::cssSelector( $cssSelector ) );
				} catch (Exception $e){
					$elements = [];
				}

				$waitingElements = $elements;
			} while ( empty($waitingElements) );

			$endTime = microtime(true);
			$status = (empty($elements)) ? 'Not found' : 'Found ' . count($elements) . ' elements';
			$this->navigation->cli('##### MultipleElements WaitingForElement Info ######');
			$this->navigation->cli('Waited for: ' . ($endTime - $startTime) . ' seconds.');
			$this->navigation->cli('Total waiting time approved: ' . $msInFloat . ' seconds.');
			$this->navigation->cli('cssSelector: ' . $cssSelector);
			$this->navigation->cli('Status: ' . $status);
			$this->navigation->cli('#########################');
		}

		if(empty( $elements )){
			error_log( 'Elements with selector ' . $cssSelector . ' could not be found.');
			return [];
		} else {
			if($onlyVisible){
				$elementArray = [];
				foreach( $elements as $element){
					if($element->isDisplayed()){
						$elementArray[] = $element;
					} else {
						error_log('One of ' . $cssSelector . ' is not visible');
					}
				}

				if(empty($elementArray)){
					return [];
				}

				$elements = $elementArray;
			}
		}


		if(!is_null($attribute)){
			foreach( $elements as $element){
				$values[] = ( $attribute == 'text' ) ? $element->getText() : $element->getAttribute( $attribute );
			}
		}

		if(!is_null($return)){
			return match ( $return ) {
				'elements', 'element' => $elements,
				default => $values,
			};
		}

		return [];
	}
}
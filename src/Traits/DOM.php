<?php

declare(strict_types=1);

namespace Akdr\Selma\Traits;

use Akdr\Selma\Collection;
use Akdr\Selma\Element;
use Facebook\WebDriver\WebDriverBy;

trait DOM
{
    /**
     * Select multiple elements with CSS-Selector ex. "table.results > tr"
     *
     * @param string $selector
     *
     * @return null|Collection
     */
    public function selectElements(string $selector): ?Collection
    {
        $elements = $this->webDriver->findElements(WebDriverBy::cssSelector($selector));

        if (count($elements) > 0)
            $this->collection = new Collection($elements, $selector);

        if (count($elements) == 0)
        {
            $this->cli('No elements found, Elements is set to empty array', 'white');
            $this->collection = new Collection([], $selector);
        }
            
        return $this->collection;
    }

    /**
     * Select a single element with CSS-Selector ex. "table.results > tr". If multiple is found, the first is returned.
     *
     * @param string $selector
     *
     * @return null|Element
     */
    public function selectElement(string $selector): ?Element
    {
        $this->element = null;
        try {
            $element = $this->webDriver->findElement(WebDriverBy::cssSelector($selector));
            $this->element = new Element($element, $selector);
        } catch (\Exception $e) {
            $this->cli("No element found, Element is set to null. Selector: $selector", "white");
        }

        return $this->element;
    }
}

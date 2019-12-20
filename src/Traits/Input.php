<?php

declare(strict_types=1);

namespace Akdr\Selma\Traits;

use Akdr\Selma\Element;
use Facebook\WebDriver\WebDriverKeys;

trait Input
{
    /**
     * Insert a string into a inputfield
     *
     * @param string $inputString
     *
     * @return Element
     */
    public function insertStringIntoElement(string $inputString): Element
    {
        $this->element->sendKeys($inputString);

        return $this->element;
    }

    public function pressEnter(): Element
    {
        $this->element->pressKey(WebDriverKeys::ENTER); 
        
        return $this->element;
    }
}

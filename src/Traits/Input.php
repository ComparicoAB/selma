<?php

declare(strict_types=1);

namespace Akdr\Selma\Traits;

use Akdr\Selma\Element;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\Remote\RemoteWebElement;

trait Input
{
    /**
     * Insert a string into a inputfield
     *
     * @param string $inputString
     *
     * @return RemoteWebElement
     */
    public function insertStringIntoElement(string $inputString): RemoteWebElement
    {
        $this->element->sendKeys($inputString);

        return $this->element;
    }

    public function pressEnter(): RemoteWebElement
    {
        $this->element->sendKeys(WebDriverKeys::ENTER); 
        
        return $this->element;
    }
}

<?php

declare(strict_types=1);

namespace Akdr\Selma\Traits;

use Akdr\Selma\Element;
use ReflectionClass;

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

        return $this;
    }

    /**
     * Press a key on the current element
     *
     * @param string $inputString
     *
     * @return Element
     */
    public function pressKey(string $WebDriverKeyConstant): Element
    {
        $ref = new ReflectionClass('Facebook\WebDriver\WebDriverKeys');
        $this->element->sendKeys($ref->getConstant($WebDriverKeyConstant)); 
        
        return $this;
    }
}

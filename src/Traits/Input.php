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
     * @return Element|Input
     */
    public function insertStringIntoElement(string $inputString)
    {
        $this->element->sendKeys($inputString);

        return $this;
    }

    /**
     * Press a key on the current element
     *
     * @param string $WebDriverKeyConstant
     * @return Element|Input
     */
    public function pressKey(string $WebDriverKeyConstant)
    {
        $ref = new ReflectionClass('Facebook\WebDriver\WebDriverKeys');
        $this->element->sendKeys($ref->getConstant($WebDriverKeyConstant)); 
        
        return $this;
    }
}

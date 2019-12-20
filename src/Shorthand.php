<?php

declare(strict_types=1);

namespace Akdr\Selma;

class Shorthand
{
    /**
     * @var Navigation
     */
    private $navigation;

    /**
     * @var Navigation
     */
    public function __construct(Navigation $navigation)
    {
        $this->navigation = $navigation;
    }

    /**
     * 
     * @param string $selector 
     * @param string $attribute 
     * @return array 
     */
    public function selectElementsAndGetAttribute(string $selector, string $attribute)
    {
        $attributeValue = null;

        if ($attribute == 'text') {
            $getFunction = 'getText';
        } else {
            $getFunction = 'getAttribute';
            $attributeValue = $attribute;
        }

        $elements = $this->navigation->selectElements($selector);

        return $elements->{$getFunction}($attributeValue)->returnElementAndValue();
    }

    /**
     * 
     * @param string $selector 
     * @param string $attribute 
     * @return array 
     */
    public function selectElementsGetAttributeAndConvertToInt(string $selector, string $attribute)
    {
        $attributeValue = null;

        if ($attribute == 'text') {
            $getFunction = 'getText';
        } else {
            $getFunction = 'getAttribute';
            $attributeValue = $attribute;
        }

        $elements = $this->navigation->selectElements($selector);

        return $elements->{$getFunction}($attributeValue)->convertToInt()->returnElementAndValue();
    }

    /**
     * 
     * @param string $selector 
     * @param string|null $attribute 
     * @return array 
     */
    public function selectElementsGetAttributeAndConvertToFloat(string $selector, ?string $attribute = null)
    {
        $attributeValue = null;

        if ($attribute == 'text') {
            $getFunction = 'getText';
        } else {
            $getFunction = 'getAttribute';
            $attributeValue = $attribute;
        }

        $elements = $this->navigation->selectElements($selector);

        return $elements->{$getFunction}($attributeValue)->convertToFloat()->returnElementAndValue();
    }
}

<?php

declare(strict_types=1);

namespace Akdr\Selma;

use Akdr\Selma\Traits\Input;
use Facebook\WebDriver\Remote\RemoteWebElement;
use JBZoo\Utils\Filter;

class Element
{
    use Input;

    /**
     * @var RemoteWebElement
     */
    public $element;

    /**
     * @var string
     */
    private $selector;

    /**
     * Value is set by calling method getAttribute() or getText()
     * Value can be transformed to Float or Int by calling method convertToInt() or convertToFloat()
     * ex. Element->getText()->convertToInt()->value;
     * @var string|int|float|null
     */
    public $value;

    public function __construct(RemoteWebElement $element, string $selector)
    {
        $this->element = $element;
        $this->selector = $selector;
    }

    /**
     * @return string|int|float|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get an attribute from the RemoteWebElement. 
     * ex. Element->getAttribute('href');
     * @param string $attribute
     *
     * @return Element
     */
    public function getAttribute(string $attribute): Element
    {
        $this->value = (!empty($this->element->getAttribute($attribute)))
            ? $this->element->getAttribute($attribute) : null;

        return $this;
    }

    /**
     * Get the innerHTML from the RemoteWebElement. 
     * ex. Element->getText();
     *
     * @return Element
     */
    public function getText(): Element
    {
        $this->value = null;
        try {
            $this->value = $this->element->getText();
        } catch (\Exception $e) {
            $this->cli('Text could not be found in selector: ' . $this->selector, 'white');
        }

        return $this;
    }

    /**
     * Convert a String or Float to Int. Will remove everything except 0-9.
     * Access the value with Element->value.
     * ex. Element->getAttribute('title')->convertToInt();
     *
     * @return Element
     */
    public function convertToInt(): Element
    {
        $this->value = (!is_null($this->value)) ? Filter::int((string) $this->value) : null;
        return $this;
    }

    /**
     * Convert a String or Int to Float. Will remove everything except 0-9 and period (.).
     * Access the value with Element->value.
     * ex. Element->getAttribute('data-item')->convertToInt();
     *
     * @return Element
     */
    public function convertToFloat(): Element
    {
        $this->value = (!is_null($this->value)) ? Filter::float((string) $this->value) : null;
        return $this;
    }

    /**
     * Clicks the Element.
     *
     * @return Element
     */
    public function click(): Element
    {
        try {
            $this->element->click();
        } catch (\Exception $e) {
            $this->cli('Could not click Element with value ' . $this->value . ' and selector ' . $this->selector, 'red');
        }

        return $this;
    }

    /**
     * Checks if an element has a class.
     * @param string $className
     * @return bool|null
     */
    public function hasClass(string $className): ?bool
    {
        $this->element->getAttribute('class');
        $classes = (string) $this->value;

        if (!empty($classes)) {
            $classArray = explode(' ', $classes);
            return in_array($className, $classArray);
        }
        return false;
    }

    /**
     * Prints {message} to the CLI with the {color} and lineending.
     *
     * @param  string $message
     * @param  string $color
     *
     * @return Element
     */
    public function cli(string $message, string $color = 'green'): Element
    {
        error_log($message);
        return $this;
    }
}

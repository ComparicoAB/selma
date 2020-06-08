<?php

declare(strict_types=1);

namespace Akdr\Selma\Object;

use Akdr\Selma\Navigation;
use Exception;
use Facebook\WebDriver\WebDriverBy;
use JBZoo\Utils\Filter;
use ReflectionClass;
use function array_walk;

class Element {
    /**
     * @var Navigation
     */
    private $navigation;

    /**
     * @var \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public $element;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $hasClass;

    public function __construct(Navigation $navigation, array $options)
    {
        $this->navigation = $navigation;
        // Walk through the options array and set variables in the class.
        // Selector must be the first option in the array.
        array_walk($options, array($this, 'resvoleOptions'));

        return $this;
    }

    /**
     * Keys are: selector (non-optional), element, attribute, click, class, input, pressKey, delay
     * Set also resets saved values.
     * @var array $options Array will be initiated in the order presented.
     */
    public function set(array $options): Element
    {
        $this->element = $this->value = $this->hasClass = null;

        array_walk($options, array($this, 'resvoleOptions'));

        return $this;
    }

    private function resvoleOptions($value, $key)
    {
        switch($key){
            case 'selector':
                try {
                    if($this->element == null) {
                        $this->element = $this->navigation->webDriver->findElement(WebDriverBy::cssSelector($value));
                    } else {
                        $this->element = $this->element->findElement(WebDriverBy::cssSelector($value));
                    }
                } catch (Exception $e) {
                    $this->element = null;
                }
            break;

            case 'element':
                $this->element = $value;
            break;

            case 'attribute':
                if($this->element == null)
                {
                    $this->value = null;
                } else {
                    $this->value = ($value == 'text') ? $this->element->getText() : $this->element->getAttribute($value);
                }
            break;

            case 'click':
                ($value === true) ? $this->element->click() : null;
            break;

            case 'class':
                $classes = $this->element->getAttribute('class');
                $this->hasClass = in_array($value, explode(' ', $classes));
            break;

            case 'input':
                $this->element->sendKeys($value);
            break;

            case 'pressKey':
                $ref = new ReflectionClass('Facebook\WebDriver\WebDriverKeys');
                $this->element->sendKeys($ref->getConstant($value));
            break;

            case 'delay':
                $this->navigation->sleep($value);
            break;
        }

        return null;
    }

    public function getValue($returnType = null)
    {
        if(is_null($this->value)){
            return null;
        }

        switch($returnType){
            case 'int':
                return Filter::int((string) $this->value);
            break;

            case 'float':
                return Filter::float((string) $this->value);
            break;

            case null:
            default:
                return $this->value;
            break;
        }
    }
}
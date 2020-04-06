<?php

declare(strict_types=1);

namespace Akdr\Selma;

use Exception;

class Collection implements \Iterator
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var Element[]
     */
    private $collection;

    /**
     * Values is set by calling method getAttribute() or getText()
     * Values can be transformed to Float or Int by calling method convertToInt() or convertToFloat()
     * ex. Collection->getText()->convertToInt()->mergeValueWithElement()->mergedCollection;
     * @var string[]|int[]|float[]|array
     */
    private $values;

    /**
     * @var array<RemoteWebElement>
     * @var string
     */
    public function __construct(array $elements, string $selector)
    {
        $this->position = 0;
        $this->collection = [];

        if (!empty($elements)) {
            foreach ($elements as $element) {
                $this->collection[] = new Element($element, $selector);
            }
        }
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): Element
    {
        return $this->collection[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->collection[$this->position]);
    }

    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * byAttribute
     *
     * @param string $attribute
     *
     * @return Collection
     */
    public function getAttribute(string $attribute): Collection
    {
        $this->values = array_map(function ($element) use ($attribute) {
            return $element->getAttribute($attribute)->getValue();
        }, $this->collection);

        return $this;
    }

    /**
     * getText
     *
     * @return Collection
     */
    public function getText(): Collection
    {
        $this->values = array_map(function ($element) {
            return $element->getText()->getValue();
        }, $this->collection);
        return $this;
    }

    /**
     * convertToInt
     *
     * @return Collection
     */
    public function convertToInt(): Collection
    {
        $this->values = array_map(function ($element) {
            return $element->convertToInt()->getValue();
        }, $this->collection);

        return $this;
    }

    /**
     * convertToFloat
     *
     * @return Collection
     */
    public function convertToFloat(): Collection
    {
        $this->values = array_map(function ($element) {
            return $element->convertToInt()->getValue();
        }, $this->collection);

        return $this;
    }

    /**
     * Returns an array with ['element'] => $element, ['value'] => $value.
     * Values can be transformed to Float or Int by calling method convertToInt() or convertToFloat()
     * ex. Collection->getText()->convertToInt()->returnElementAndValue();
     * @var string[]|int[]|float[]|array
     * @return array
     */
    public function returnElementAndValue() : array
    {
        if (empty($this->values))
            throw new Exception("Values is empty. Values must be set to a valid target first.");

        if (empty($this->collection))
            throw new Exception("Collection is empty. Collection must be set to a valid target first.");

        $collectionLength = ($this->count() <= count($this->values)) ? $this->count() : count($this->values);

        $returnArray = [];

        for ($i = 0; $i < $collectionLength; ++$i) {
            $returnArray[$i] = [
                'element' => $this->collection[$i],
                'value' => $this->values[$i]
            ];
        }

        return $returnArray;
    }

    /**
     * cli
     *
     * @param string $message
     * @param string $color
     *
     * @return Collection
     */
    public function cli(string $message, string $color = 'green'): Collection
    {
        error_log($message);
        return $this;
    }
}

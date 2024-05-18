<?php

namespace PikaJew002\Handrolled\Support;

// This whole class is pretty much adopted from the Laravel framework v8.x
// Putting together the Collection class and a method from the Arr class
// https://github.com/laravel/framework/blob/8.x/src/Illuminate/Collections/Collection.php
// https://github.com/laravel/framework/blob/8.x/src/Illuminate/Collections/Arr.php

class Collection
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Create a new collection.
     *
     * @param  array  $items
     * @return void
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param  callable|mixed  $callback
     * @return static
     */
    public function reject($callback = true)
    {
        $useAsCallable = $this->useAsCallable($callback);

        return $this->filter(function($value, $key) use ($callback, $useAsCallable) {
            return $useAsCallable ? !$callback($value, $key) : $value != $callback;
        });
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function useAsCallable($value): bool
    {
        return !is_string($value) && is_callable($value);
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function filter(?callable $callback = null)
    {
        if($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Run an associative map over each of the items.
     *
     * The callback should return an associative array with a single key/value pair.
     *
     * @param  callable  $callback
     * @return static
     */
    public function mapWithKeys(callable $callback)
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            $assoc = $callback($value, $key);
            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return new static($result);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }
}

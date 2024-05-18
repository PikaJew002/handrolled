<?php

namespace PikaJew002\Handrolled\Support;

// This whole class is pretty much adopted from the Laravel framework v8.x
// Putting together the Config/Repository class and methods from the Arr class
// https://github.com/laravel/framework/blob/8.x/src/Illuminate/Config/Repository.php
// https://github.com/laravel/framework/blob/8.x/src/Illuminate/Collections/Arr.php

class Repository
{
    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Create a new configuration repository.
     *
     * @param  array  $items
     * @return void
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Get the specified configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->getDeep($key, $default);
    }

    private function getDeep($key, $default = null)
    {
        if(is_null($key)) {
            return $this->items;
        }
        if(array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }
        if(strpos($key, '.') === false) {
            return $this->items[$key] ?? $default;
        }
        $array = $this->items;
        foreach(explode('.', $key) as $segment) {
            if(is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];
        foreach($keys as $key => $value) {
            $this->setDeep($this->items, $key, $value);
        }
    }

    private function setDeep(&$array, $key, $value): array
    {
        if(is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        foreach($keys as $i => $key) {
            if(count($keys) === 1) {
                break;
            }
            unset($keys[$i]);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if(!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }
}

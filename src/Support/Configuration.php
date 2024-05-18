<?php

namespace PikaJew002\Handrolled\Support;

class Configuration
{
    private Repository $config;

    public function __construct()
    {
        $this->config = new Repository();
    }

    public function getOrSet($input, $default = null)
    {
        if(is_array($input)) {
            return $this->set($input);
        }

        return $this->get($input, $default);
    }

    public function get($input, $default = null)
    {
        return $this->config->get($input, $default);
    }

    public function set(array $input): void
    {
        foreach($input as $key => $value) {
            $this->config->set($key, $value);
        }
    }
}

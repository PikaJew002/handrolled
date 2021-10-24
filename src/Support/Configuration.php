<?php

namespace PikaJew002\Handrolled\Support;

use Illuminate\Config\Repository;

class Configuration
{
    private Repository $config;

    public function __construct()
    {
        $this->config = new Repository();
    }

    public function getOrSet($input)
    {
        if(is_array($input)) {
            $this->set($input);
        } else {
            return $this->get($input);
        }
    }

    public function get($input)
    {
        return $this->config->get($input);
    }

    public function set(array $input): void
    {
        foreach($input as $key => $value) {
            $this->config->set($key, $value);
        }
    }
}

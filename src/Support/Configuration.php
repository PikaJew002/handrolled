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

    public function getOrSet($input) {
        if(is_array($input)) {
            foreach($input as $key => $value) {
                $this->config->set($key, $value);
            }
            return true;
        } else {
            return $this->config->get($input);
        }
    }
}

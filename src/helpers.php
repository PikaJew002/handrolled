<?php

if(!function_exists('env')) {
    function env(string $key, $default = null) {
        return isset($_ENV[$key]) ? $_ENV[$key] : $default;
    }
}

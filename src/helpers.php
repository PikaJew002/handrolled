<?php

if(!function_exists('env')) {
    function env(string $key, $default = null)
    {
        if(isset($_ENV[$key])) {
            if(strtolower($_ENV[$key]) === 'true') {
                return true;
            }
            if(strtolower($_ENV[$key]) === 'false') {
                return false;
            }

            return $_ENV[$key];
        }

        return $default;
    }
}

if(!function_exists('random_str')) {
    // credit to Scott Arciszewski from StackOverflow
    // https://stackoverflow.com/questions/4356289/php-random-string-generator/31107425#31107425
    function random_str(int $length = 64, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        if($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}

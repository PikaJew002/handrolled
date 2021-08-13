<?php

namespace PikaJew002\Handrolled\Http;

class Request
{
    public string $method;
    public string $uri;
    public string $query;
    public array $inputs;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $this->query = '';
        if(false !== $pos = strpos($uri, '?')) {
          $this->query = substr($uri, $pos);
          $uri = substr($uri, 0, $pos);
        }
        $this->uri = rawurldecode($uri);
        if($this->method === 'POST') {
            $this->inputs = $_POST;
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function input($key, $default = null)
    {
        return isset($this->inputs[$key]) ? $this->inputs[$key] : $default;
    }
}

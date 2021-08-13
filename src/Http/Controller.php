<?php

namespace PikaJew002\Handrolled\Http;

use PikaJew002\Handrolled\Http\Request;

class Controller
{
    public Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}

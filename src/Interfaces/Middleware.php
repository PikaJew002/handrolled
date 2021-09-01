<?php

namespace PikaJew002\Handrolled\Interfaces;

use PikaJew002\Handrolled\Http\Request;

interface Middleware
{
    public function handler(Request $request, callable $next);
}

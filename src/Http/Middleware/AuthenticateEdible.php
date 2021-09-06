<?php

namespace PikaJew002\Handrolled\Http\Middleware;

use PikaJew002\Handrolled\Auth\Manager as AuthManager;
use PikaJew002\Handrolled\Exceptions\Http\HttpException;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Interfaces\Middleware;

class AuthenticateEdible implements Middleware
{
    protected AuthManager $auth;

    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    public function handler(Request $request, callable $next)
    {
        $userClass = $this->auth->userClass;
        if($userClass::hasAuthEdible($request)) {
            $user = $userClass::matchesAuthEdible($request);
            if(!is_null($user)) {
                $request->setUser($user);
                return $next($request);
            }
        }
        throw new HttpException(401, 'Unauthorized');
    }
}

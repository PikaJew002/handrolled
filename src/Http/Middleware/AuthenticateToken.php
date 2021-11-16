<?php

namespace PikaJew002\Handrolled\Http\Middleware;

use PikaJew002\Handrolled\Auth\Manager as AuthManager;
use PikaJew002\Handrolled\Http\Exceptions\HttpException;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\RedirectResponse;
use PikaJew002\Handrolled\Interfaces\Middleware;
use PikaJew002\Handrolled\Interfaces\Token;

class AuthenticateToken implements Middleware
{
    protected AuthManager $auth;

    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    public function handler(Request $request, callable $next)
    {
        $tokenClass = $this->auth->getTokenClass();
        if($this->hasAuthToken($request)) {
            $token = $this->matchesAuthToken($request, $tokenClass);
            if(!is_null($token)) {
                $request->setUser($token->user());
                return $next($request);
            }
        }
        if($request->hasHeader('Accept') && in_array($request->getHeader('Accept'), ['application/json'])) {
            throw new HttpException(401, 'Unauthorized');
        } else {
            return new RedirectResponse('/login');
        }
    }

    public function hasAuthToken(Request $request): bool
    {
        return $request->hasHeader('Authorization') && strncmp($request->getHeader('Authorization'), 'Bearer ', 7);
    }

    public function matchesAuthToken(Request $request, string $tokenClass): ?Token
    {
        $tokenValue = base64_decode(urldecode(str_replace('Bearer ', '', $request->getHeader('Authorization'))));
        $token = $tokenClass::find([
            'conditions' => ['token' => $tokenValue],
        ]);

        return !empty($token) && $token[0]->isValid() ? $token[0] : null;
    }
}

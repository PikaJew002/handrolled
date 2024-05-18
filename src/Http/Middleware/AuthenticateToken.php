<?php

namespace PikaJew002\Handrolled\Http\Middleware;

use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\UnauthorizedResponse;
use PikaJew002\Handrolled\Interfaces\Middleware;
use PikaJew002\Handrolled\Interfaces\Token;
use PikaJew002\Handrolled\Support\Configuration;

class AuthenticateToken implements Middleware
{
    protected Configuration $config;

    protected UnauthorizedResponse $response;

    public function __construct(Configuration $config, UnauthorizedResponse $response)
    {
        $this->config = $config;
        $this->response = $response;
    }

    public function handler(Request $request, callable $next)
    {
        // $tokenClass = $this->config->get('auth.drivers.token.class');
        if($this->hasAuthToken($request)) {
            $token = $this->matchesAuthToken($request, $this->config->get('auth.drivers.token.class'));
            if(!is_null($token)) {
                $request->setUser($token->user());
                return $next($request);
            }
        }

        return $this->response;
    }

    public function hasAuthToken(Request $request): bool
    {
        return $request->hasHeader('Authorization') && strncmp($request->getHeader('Authorization'), 'Bearer ', 7);
    }

    public function matchesAuthToken(Request $request, string $tokenClass): ?Token
    {
        $tokenValue = base64_decode(urldecode(str_replace('Bearer ', '', $request->getHeader('Authorization'))));
        $token = $tokenClass::where('token', $tokenValue)->first();
        if(is_null($token) || !$token->isValid()) {
            return null;
        }

        return $token;
    }
}

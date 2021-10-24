<?php

namespace PikaJew002\Handrolled\Http\Middleware;

use PikaJew002\Handrolled\Auth\Manager as AuthManager;
use PikaJew002\Handrolled\Http\Exceptions\HttpException;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\RedirectResponse;
use PikaJew002\Handrolled\Interfaces\Middleware;
use PikaJew002\Handrolled\Interfaces\User;

class AuthenticateEdible implements Middleware
{
    protected AuthManager $auth;

    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    public function handler(Request $request, callable $next)
    {
        $userClass = $this->auth->getUserClass();
        if($this->hasAuthEdible($request)) {
            [$idHash, $passwordHash] = explode('|', base64_decode(urldecode($request->getCookie('puff_puff_pass'))));
            $user = $this->matchesAuthEdible($request, $userClass);
            if(!is_null($user)) {
                $request->setUser($user);
                return $next($request);
            }
        }
        if($request->hasHeader('Accept') && in_array($request->getHeader('Accept'), ['application/json'])) {
            throw new HttpException(401, 'Unauthorized');
        } else {
            return new RedirectResponse('/login?');
        }
    }

    public function hasAuthEdible(Request $request): bool
    {
        return $request->hasCookie('puff_puff_pass');
    }

    public function matchesAuthEdible(Request $request, string $userClass): ?User
    {
        [$idHash, $passwordHash] = explode('|', base64_decode(urldecode($request->getCookie('puff_puff_pass'))));
        $user = $userClass::find([
            'conditions' => ['password_hash' => $passwordHash],
        ]);

        return !empty($user) && password_verify($user[0]->getId(), $idHash) ? $user[0] : null;
    }
}

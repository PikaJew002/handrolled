<?php

namespace PikaJew002\Handrolled\Http\Middleware;

use PikaJew002\Handrolled\Auth\Encryption;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\UnauthorizedResponse;
use PikaJew002\Handrolled\Http\Responses\RedirectResponse;
use PikaJew002\Handrolled\Interfaces\Middleware;
use PikaJew002\Handrolled\Interfaces\User;
use PikaJew002\Handrolled\Support\Configuration;

class AuthorizeEdible implements Middleware
{
    protected Configuration $config;

    protected Encryption $encryption;

    protected RedirectResponse $redirectResponse;

    protected UnauthorizedResponse $unauthorizedResponse;

    public function __construct(Configuration $config, Encryption $encryption, RedirectResponse $redirectResponse, UnauthorizedResponse $unauthorizedResponse)
    {
        $this->config = $config;
        $this->encryption = $encryption;
        $this->redirectResponse = $redirectResponse;
        $this->unauthorizedResponse = $unauthorizedResponse;
    }

    public function handler(Request $request, callable $next)
    {
        if ($request->hasCookie('puff_puff_pass')) {
            $user = $this->matchesAuthEdible($request->getCookie('puff_puff_pass'), $this->config->get('auth.user'));
            if (!is_null($user)) {
                $request->setUser($user);
                return $next($request);
            }
        }

        if ($request->hasHeader('Accept') && in_array($request->getHeader('Accept'), ['application/json'])) {
            return $this->unauthorizedResponse;
        }

        return $this->redirectResponse->to('/login?message=' . rawurlencode('Invalid username, password combination'));
    }

    public function matchesAuthEdible($edible, string $userClass): ?User
    {
        [$idEncrypted, $passwordHash] = explode('|', base64_decode($edible));
        $user = $userClass::where('id', $this->encryption->decrypt($idEncrypted))->first();

        return !is_null($user) && password_verify($user->getPasswordHash(), $passwordHash) ? $user : null;
    }
}

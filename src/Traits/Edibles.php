<?php

namespace PikaJew002\Handrolled\Traits;

use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Auth\Manager as AuthManager;

trait Edibles
{
    public static function hasAuthEdible(Request $request): bool
    {
        return isset($request->cookies['puff_puff_pass']) && !is_null($request->cookies['puff_puff_pass']);
    }

    public static function matchesAuthEdible(Request $request): ?self
    {
        [$idHash, $passwordHash] = explode('|', base64_decode(urldecode($request->cookies['puff_puff_pass'])));

        $user = self::find([
            'conditions' => ['password_hash' => $passwordHash],
        ]);
        if(!empty($user) && password_verify($user[0]->getId(), $idHash)) {
            return $user[0];
        }
        return null;
    }

    public function setAuthEdible(Request $request, AuthManager $auth): void
    {
        $cookieConfig = $auth->config->getOrSet('auth.drivers.cookies');
        setcookie(
            'puff_puff_pass',
            base64_encode(password_hash($this->getId(), PASSWORD_DEFAULT).'|'.$this->getPasswordHash()),
            time() + $auth->config->getOrSet('auth.drivers.cookies.length'),
            '/',
            '',
            $auth->config->getOrSet('auth.drivers.cookies.secure'),
            $auth->config->getOrSet('auth.drivers.cookies.http_only')
        );
    }

    public static function invalidateAuthEdible(AuthManager $auth)
    {
        setcookie(
            'puff_puff_pass',
            '',
            time() - 3600,
            '/',
            '',
            $auth->config->getOrSet('auth.drivers.cookies.secure'),
            $auth->config->getOrSet('auth.drivers.cookies.http_only')
        );
    }
}

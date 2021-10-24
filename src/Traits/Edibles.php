<?php

namespace PikaJew002\Handrolled\Traits;

use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Auth\Manager as AuthManager;

trait Edibles
{
    public function setAuthEdible(AuthManager $auth): void
    {
        $cookieConfig = $auth->config->get('auth.drivers.cookies');
        setcookie(
            'puff_puff_pass',
            base64_encode(password_hash($this->getId(), PASSWORD_DEFAULT).'|'.$this->getPasswordHash()),
            time() + $auth->config->get('auth.drivers.cookies.length'),
            '/',
            '',
            $auth->config->get('auth.drivers.cookies.secure'),
            $auth->config->get('auth.drivers.cookies.http_only')
        );
    }

    public static function invalidateAuthEdible(AuthManager $auth): void
    {
        setcookie(
            'puff_puff_pass',
            '',
            time() - 3600,
            '/',
            '',
            $auth->config->get('auth.drivers.cookies.secure'),
            $auth->config->get('auth.drivers.cookies.http_only')
        );
    }
}

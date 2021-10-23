<?php

namespace PikaJew002\Handrolled\Application\Services;

use PikaJew002\Handrolled\Application\Service;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;
use PikaJew002\Handrolled\Interfaces\Token;
use PikaJew002\Handrolled\Interfaces\User;

class AuthService extends Service implements ServiceInterface
{
    public function boot(): void
    {
        $this->app->setAlias(User::class, $this->app->config('auth.user'));
        $this->app->setAlias(Token::class, $this->app->config('auth.drivers.token.class'));
    }
}

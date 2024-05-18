<?php

namespace PikaJew002\Handrolled\Auth;

use PikaJew002\Handrolled\Interfaces\User;
use PikaJew002\Handrolled\Support\Configuration;

class Edible
{
    protected Configuration $config;

    protected Encryption $encryption;

    public function __construct(Configuration $config, Encryption $encryption)
    {
        $this->config = $config;
        $this->encryption = $encryption;
    }

    public function login(User $user): void
    {
        setcookie(
            'puff_puff_pass',
            base64_encode($this->encryption->encrypt($user->getId()) . '|' . $user->getPasswordHash()),
            time() + $this->config->get('auth.drivers.cookies.length'),
            '/',
            '',
            $this->config->get('auth.drivers.cookies.secure'),
            $this->config->get('auth.drivers.cookies.http_only')
        );
    }

    public function logout(): void
    {
        setcookie(
            'puff_puff_pass',
            '',
            time() - 3600,
            '/',
            '',
            $this->config->get('auth.drivers.cookies.secure'),
            $this->config->get('auth.drivers.cookies.http_only')
        );
    }
}

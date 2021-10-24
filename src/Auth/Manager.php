<?php

namespace PikaJew002\Handrolled\Auth;

use PikaJew002\Handrolled\Interfaces\User;
use PikaJew002\Handrolled\Support\Configuration;

class Manager
{
    public Configuration $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    public function getUserClass(): ?string
    {
        return $this->config->get('auth.user');
    }

    public function getTokenClass(): ?string
    {
        return $this->config->get('auth.drivers.token.class');
    }
}

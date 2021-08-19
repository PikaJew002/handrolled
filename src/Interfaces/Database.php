<?php

namespace PikaJew002\Handrolled\Interfaces;

interface Database
{
    public function makeDSN(string $host, string $database, string $username, string $password, ?string $port);
}

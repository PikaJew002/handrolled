<?php

namespace PikaJew002\Handrolled\Interfaces;

use DateTime;

interface Token
{
    public function getUserId();
    public function getToken();
    public function getExpiresAt(): DateTime;
    public function user(): ?User;
    public function isValid(): bool;
}

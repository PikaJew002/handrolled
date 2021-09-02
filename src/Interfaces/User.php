<?php

namespace PikaJew002\Handrolled\Interfaces;

interface User
{
    public function getId();
    public function getUsername();
    public function getPasswordHash();
}

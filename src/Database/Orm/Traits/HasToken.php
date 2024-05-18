<?php

namespace PikaJew002\Handrolled\Database\Orm\Traits;

use DateTime;
use DateInterval;
use PikaJew002\Handrolled\Interfaces\Token;
use PikaJew002\Handrolled\Support\Configuration;
use ReflectionClass;

trait HasToken
{
    abstract public function getId(?ReflectionClass $classReflect = null);

    public function makeAuthToken(Configuration $config): Token
    {
        $tokenConfig = $config->get('auth.drivers.token');
        $tokenClass = $tokenConfig['class'];
        $token = $tokenClass::where('user_id', $this->getId())->first();
        if(is_null($token)) {
            $token = new $tokenClass();
            $token->user_id = $this->getId();
        }
        $token->token = random_str();
        $token->expires_at = (new DateTime())->add(new DateInterval('PT'.$tokenConfig['length'].'S'))->format('Y-m-d H:i:s');
        $token->save();

        return $token;
    }

    public function invalidateAuthToken(Configuration $config)//: ?Token
    {
        // return
    }
}

<?php

namespace PikaJew002\Handrolled\Traits;

use DateTime;
use DateInterval;
use PikaJew002\Handrolled\Auth\Manager as AuthManager;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Interfaces\Token;

trait Tokens
{
    public function makeAuthToken(AuthManager $auth): Token
    {
        $tokenConfig = $auth->config->get('auth.drivers.token');
        $tokenClass = $tokenConfig['class'];
        $token = $this->checkForAuthToken($tokenClass);
        if(is_null($token)) {
            $token = new $tokenClass();
            $token->user_id = $this->getId();
        }
        $token->token = random_str();
        $token->expires_at = (new DateTime())->add(new DateInterval('PT'.$tokenConfig['length'].'S'))->format('Y-m-d H:i:s');
        $token->save();

        return $token;
    }

    public function checkForAuthToken(string $tokenClass): ?Token
    {
        $token = $tokenClass::find([
            'conditions' => ['user_id' => $this->getId()],
        ]);

        return !empty($token) ? $token[0] : null;
    }

    public function invalidateAuthToken(AuthManager $auth)//: ?Token
    {
        // return
    }
}

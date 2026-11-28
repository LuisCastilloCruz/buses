<?php

namespace App\Auth;

use Illuminate\Auth\TokenGuard;

/** Reject typed JSON credentials before they reach a coercing database comparison. */
class StrictTokenGuard extends TokenGuard
{
    public function getTokenForRequest()
    {
        $token = parent::getTokenForRequest();

        return is_string($token) ? $token : null;
    }

    public function validate(array $credentials = [])
    {
        if (!isset($credentials[$this->inputKey]) || !is_string($credentials[$this->inputKey])) {
            return false;
        }

        return parent::validate($credentials);
    }
}

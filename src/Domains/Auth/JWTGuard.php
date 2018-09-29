<?php

namespace SuperV\Platform\Domains\Auth;

use Current;
use Tymon\JWTAuth\JWTGuard as BaseJWTGuard;

class JWTGuard extends BaseJWTGuard
{
    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if ($this->jwt->setRequest($this->request)->getToken() &&
            ($payload = $this->jwt->check(true)) &&
            $this->validateSubject()
        ) {
            if (! $payload->get('port') || $payload->get('port') === Current::port()->slug()) {
                return $this->user = $this->provider->retrieveById($payload['sub']);
            }
        }
    }
}
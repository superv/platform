<?php namespace SuperV\Platform\Domains\Auth\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Feature\Feature;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTAuth;

class GetUserFromToken
{
    /**
     * @var Feature
     */
    private $feature;

    public function __construct(Feature $feature)
    {
        $this->feature = $feature;
    }

    public function handle(Request $request, JWTAuth $auth)
    {
        if ($token = $auth->setRequest($request)->getToken()) {

               try {
                   if ($user = $auth->authenticate($token)) {
                       if ($this->feature) {
                           $this->feature->user = $user;
                       }
                       return $user;
                   }

               } catch (TokenExpiredException $e) {
                   return null;
               } catch (JWTException $e) {
                   return null;
               }
           }

           return null;
    }
}
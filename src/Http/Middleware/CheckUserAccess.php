<?php

namespace Paharok\Laravelfiles\Http\Middleware;

use Closure;

class CheckUserAccess
{
    /**
     * Restricts access based on config('laravelfiles.users_access'):
     * ['field' => [allowed values]] — the authenticated user's $field must be
     * strictly equal to one of the allowed values, for every field listed (AND).
     * An empty/missing config means no extra restriction (just needs to pass
     * auth_middleware, if any).
     */
    public function handle($request, Closure $next)
    {
        $rules = config('laravelfiles.users_access', []);

        if(!empty($rules)){
            $user = $request->user();
            if(!$user){
                abort(403);
            }

            foreach ($rules as $field => $allowedValues){
                if(!in_array($user->{$field}, $allowedValues, true)){
                    abort(403);
                }
            }
        }

        return $next($request);
    }
}

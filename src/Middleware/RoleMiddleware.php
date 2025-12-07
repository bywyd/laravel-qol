<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return $this->unauthorized($request);
        }

        $roles = is_array($role) ? $role : explode('|', $role);

        if (!Auth::user()->hasRole($roles)) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access.
     *
     * @param Request $request
     * @return mixed
     */
    protected function unauthorized(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthorized. Insufficient role privileges.'
            ], 403);
        }

        abort(403, 'Unauthorized. Insufficient role privileges.');
    }
}

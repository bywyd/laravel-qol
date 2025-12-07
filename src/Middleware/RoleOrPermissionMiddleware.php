<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleOrPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roleOrPermission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $roleOrPermission)
    {
        if (!Auth::check()) {
            return $this->unauthorized($request);
        }

        $items = is_array($roleOrPermission) ? $roleOrPermission : explode('|', $roleOrPermission);

        $hasAccess = Auth::user()->hasRole($items) || Auth::user()->hasPermission($items);

        if (!$hasAccess) {
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
                'message' => 'Unauthorized. Insufficient privileges.'
            ], 403);
        }

        abort(403, 'Unauthorized. Insufficient privileges.');
    }
}

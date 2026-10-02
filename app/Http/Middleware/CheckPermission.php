<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Utils\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:
     *   ->middleware('permission:user.view')
     *   ->middleware('permission:user.view,user.edit')   // requires ALL listed
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized('Authentication required.');
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                return ApiResponse::forbidden(
                    "You do not have permission to perform this action: [{$permission}]."
                );
            }
        }

        return $next($request);
    }
}

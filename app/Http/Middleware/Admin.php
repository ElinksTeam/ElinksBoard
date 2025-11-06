<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Closure;
use App\Models\User;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var User|null $user */
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // Check admin access using hybrid approach
        if (!$this->checkAdminAccess($user)) {
            Log::warning('Admin access denied', [
                'user_id' => $user->id,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'logto_roles' => $user->logto_roles,
            ]);
            
            return response()->json(['message' => 'Forbidden - Admin access required'], 403);
        }
        
        return $next($request);
    }

    /**
     * Check if user has admin access
     * Uses hybrid approach: Logto roles (priority) + local is_admin (fallback)
     *
     * @param User $user
     * @return bool
     */
    protected function checkAdminAccess(User $user): bool
    {
        // Priority 1: Check Logto roles (if user authenticated via Logto)
        if ($user->auth_provider === 'logto' && $user->logto_roles) {
            $hasAdminRole = in_array('admin', $user->logto_roles);
            
            if ($hasAdminRole) {
                Log::debug('Admin access granted via Logto role', [
                    'user_id' => $user->id,
                    'roles' => $user->logto_roles,
                ]);
                return true;
            }
        }
        
        // Priority 2: Fallback to local is_admin flag
        if ($user->is_admin) {
            Log::debug('Admin access granted via local is_admin flag', [
                'user_id' => $user->id,
            ]);
            return true;
        }
        
        return false;
    }
}

<?php

namespace App\Http\Controllers\V1\Passport;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\LogtoAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogtoAuthController extends Controller
{
    protected LogtoAuthService $logtoService;

    public function __construct(LogtoAuthService $logtoService)
    {
        $this->logtoService = $logtoService;
    }

    /**
     * Initiate sign-in with Logto
     * 
     * Returns the sign-in URL that the frontend should redirect to
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signIn(Request $request)
    {
        try {
            $redirectUri = $request->input('redirect_uri', config('logto.redirect_uri'));
            $signInUrl = $this->logtoService->getSignInUrl($redirectUri);
            
            Log::info('Logto sign-in initiated', [
                'redirect_uri' => $redirectUri,
            ]);
            
            return $this->success([
                'sign_in_url' => $signInUrl,
                'message' => 'Redirect to this URL to sign in with Logto',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to initiate Logto sign-in', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->fail([500, 'Failed to initiate sign-in: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle callback from Logto after authentication
     * 
     * This endpoint processes the OIDC callback and creates/updates the local user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        try {
            // Handle the OIDC callback
            $this->logtoService->handleCallback();
            
            // Sync user to local database
            $user = $this->logtoService->syncUser();
            
            if (!$user) {
                Log::error('Failed to sync user from Logto');
                return $this->fail([500, 'Failed to create or update user']);
            }

            // Check if user is banned
            if ($user->banned) {
                Log::warning('Banned user attempted to login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                return $this->fail([403, __('Your account has been suspended')]);
            }

            // Check if this is the first user (admin)
            $isFirstUser = \App\Models\User::count() === 1 && $user->is_admin;
            
            // Generate Sanctum token for API access
            $authService = new AuthService($user);
            $authData = $authService->generateAuthData();
            
            Log::info('User authenticated via Logto', [
                'user_id' => $user->id,
                'email' => $user->email,
                'logto_sub' => $user->logto_sub,
                'is_admin' => $user->is_admin,
                'is_first_user' => $isFirstUser,
            ]);
            
            $message = 'Authentication successful';
            if ($isFirstUser) {
                $message = 'Welcome! You are the first user and have been granted administrator privileges.';
            }
            
            return $this->success([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'uuid' => $user->uuid,
                    'is_admin' => $user->is_admin,
                    'is_staff' => $user->is_staff,
                    'avatar' => $user->avatar ?? null,
                    'balance' => $user->balance,
                    'commission_balance' => $user->commission_balance,
                    'transfer_enable' => $user->transfer_enable,
                    'expired_at' => $user->expired_at,
                ],
                'auth_data' => $authData['auth_data'],
                'token' => $authData['token'],
                'is_admin' => $authData['is_admin'],
                'is_first_user' => $isFirstUser,
                'message' => $message,
            ]);
            
        } catch (\Throwable $e) {
            Log::error('Logto callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query' => $request->query(),
            ]);
            
            return $this->fail([500, 'Authentication failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Initiate sign-out with Logto
     * 
     * Returns the sign-out URL and clears local tokens
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signOut(Request $request)
    {
        try {
            // Get sign-out URL from Logto
            $signOutUrl = $this->logtoService->getSignOutUrl();
            
            // Clear local Sanctum tokens
            if ($user = $request->user()) {
                $user->tokens()->delete();
                
                Log::info('User signed out', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
            
            return $this->success([
                'sign_out_url' => $signOutUrl,
                'message' => 'Redirect to this URL to complete sign-out',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to sign out', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->fail([500, 'Failed to sign out: ' . $e->getMessage()]);
        }
    }

    /**
     * Get current user information from Logto
     * 
     * Returns both local user data and Logto user info
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userInfo(Request $request)
    {
        try {
            if (!$this->logtoService->isAuthenticated()) {
                return $this->fail([401, 'Not authenticated with Logto']);
            }

            // Get user from local database
            $user = $this->logtoService->syncUser();
            
            if (!$user) {
                return $this->fail([404, 'User not found']);
            }

            // Get user info from Logto
            $logtoUser = $this->logtoService->getUserInfo();
            
            return $this->success([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'uuid' => $user->uuid,
                    'is_admin' => $user->is_admin,
                    'is_staff' => $user->is_staff,
                    'avatar' => $user->avatar ?? null,
                    'balance' => $user->balance,
                    'commission_balance' => $user->commission_balance,
                    'transfer_enable' => $user->transfer_enable,
                    'expired_at' => $user->expired_at,
                ],
                'logto_user' => $logtoUser,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to get user info', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->fail([500, 'Failed to get user info: ' . $e->getMessage()]);
        }
    }

    /**
     * Check authentication status
     * 
     * Returns whether the user is authenticated with Logto
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAuth(Request $request)
    {
        try {
            $isAuthenticated = $this->logtoService->isAuthenticated();
            
            $data = [
                'is_authenticated' => $isAuthenticated,
            ];
            
            if ($isAuthenticated) {
                $user = $this->logtoService->syncUser();
                if ($user) {
                    $data['user_id'] = $user->id;
                    $data['email'] = $user->email;
                    $data['is_admin'] = $user->is_admin;
                }
            }
            
            return $this->success($data);
        } catch (\Throwable $e) {
            Log::error('Failed to check auth status', [
                'error' => $e->getMessage(),
            ]);
            
            return $this->fail([500, 'Failed to check authentication status']);
        }
    }
}

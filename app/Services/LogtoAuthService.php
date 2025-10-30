<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Logto\Sdk\LogtoClient;
use Logto\Sdk\LogtoConfig;
use Logto\Sdk\Constants\UserScope;

class LogtoAuthService
{
    private LogtoClient $client;

    public function __construct()
    {
        // Get configuration from database settings (with fallback to config file)
        $endpoint = admin_setting('logto_endpoint', config('logto.endpoint'));
        $appId = admin_setting('logto_app_id', config('logto.app_id'));
        $appSecret = admin_setting('logto_app_secret', config('logto.app_secret'));
        
        $this->client = new LogtoClient(
            new LogtoConfig(
                endpoint: $endpoint,
                appId: $appId,
                appSecret: $appSecret,
                scopes: config('logto.scopes', [
                    UserScope::openid,
                    UserScope::profile,
                    UserScope::email,
                    UserScope::phone,
                    UserScope::offlineAccess,
                ]),
                resources: config('logto.resources', []),
            )
        );
    }

    /**
     * Get the sign-in URL to redirect users to Logto
     *
     * @param string|null $redirectUri Custom redirect URI
     * @return string Sign-in URL
     */
    public function getSignInUrl(?string $redirectUri = null): string
    {
        $redirectUri = $redirectUri ?? config('logto.redirect_uri');
        return $this->client->signIn($redirectUri);
    }

    /**
     * Handle the callback from Logto after authentication
     *
     * @return void
     * @throws \Throwable
     */
    public function handleCallback(): void
    {
        $this->client->handleSignInCallback();
    }

    /**
     * Get the sign-out URL to redirect users to Logto
     *
     * @return string Sign-out URL
     */
    public function getSignOutUrl(): string
    {
        return $this->client->signOut(config('logto.post_logout_redirect_uri'));
    }

    /**
     * Check if the user is authenticated with Logto
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->client->isAuthenticated();
    }

    /**
     * Get user information from Logto userinfo endpoint
     *
     * @return object|null User information object
     */
    public function getUserInfo(): ?object
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            return $this->client->fetchUserInfo();
        } catch (\Throwable $e) {
            \Log::error('Failed to fetch Logto user info', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get ID token claims (basic user info from ID token)
     *
     * @return object|null ID token claims
     */
    public function getIdTokenClaims(): ?object
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            return $this->client->getIdTokenClaims();
        } catch (\Throwable $e) {
            \Log::error('Failed to get ID token claims', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Synchronize or create local user from Logto user information
     *
     * @return User|null Local user instance
     */
    public function syncUser(): ?User
    {
        $logtoUser = $this->getUserInfo();
        if (!$logtoUser || !isset($logtoUser->sub)) {
            \Log::warning('Cannot sync user: Logto user info is invalid');
            return null;
        }

        $autoCreate = (bool) admin_setting('logto_auto_create_user', config('logto.user_sync.auto_create', true));
        $autoUpdate = (bool) admin_setting('logto_auto_update_user', config('logto.user_sync.auto_update', true));

        // Find existing user by Logto sub
        $user = User::where('logto_sub', $logtoUser->sub)->first();

        if (!$user && $autoCreate) {
            // Create new user
            $user = $this->createUserFromLogto($logtoUser);
        } elseif ($user && $autoUpdate) {
            // Update existing user
            $this->updateUserFromLogto($user, $logtoUser);
        }

        return $user;
    }

    /**
     * Create a new local user from Logto user information
     *
     * @param object $logtoUser Logto user information
     * @return User Created user instance
     */
    protected function createUserFromLogto(object $logtoUser): User
    {
        $defaults = config('logto.user_sync.defaults', []);
        
        $email = $logtoUser->email ?? $logtoUser->sub . '@logto.local';
        
        // Check if email already exists (from local auth)
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            // Link existing user to Logto
            $existingUser->logto_sub = $logtoUser->sub;
            $existingUser->auth_provider = 'logto';
            $existingUser->last_login_at = time();
            $existingUser->save();
            
            \Log::info('Linked existing user to Logto', [
                'user_id' => $existingUser->id,
                'logto_sub' => $logtoUser->sub,
            ]);
            
            return $existingUser;
        }

        // Check if this is the first user in the system
        $isFirstUser = User::count() === 0;

        $user = new User();
        $user->logto_sub = $logtoUser->sub;
        $user->email = $email;
        $user->uuid = Str::uuid()->toString();
        $user->token = Str::random(32);
        $user->auth_provider = 'logto';
        
        // Password fields (not used for Logto users)
        $user->password = null;
        $user->password_algo = 'logto';
        $user->password_salt = null;
        
        // First user becomes admin automatically
        if ($isFirstUser) {
            $user->is_admin = 1;
            \Log::info('First user registered - granted admin privileges', [
                'logto_sub' => $logtoUser->sub,
                'email' => $email,
            ]);
        }
        
        // Set default business fields
        foreach ($defaults as $key => $value) {
            if (property_exists($user, $key)) {
                $user->$key = $value;
            }
        }
        
        // Set user profile from Logto
        if (isset($logtoUser->name)) {
            $user->name = $logtoUser->name;
        }
        if (isset($logtoUser->username)) {
            $user->username = $logtoUser->username;
        }
        if (isset($logtoUser->picture)) {
            $user->avatar = $logtoUser->picture;
        }
        if (isset($logtoUser->phone_number)) {
            $user->phone = $logtoUser->phone_number;
        }
        
        $user->last_login_at = time();
        $user->save();

        \Log::info('Created new user from Logto', [
            'user_id' => $user->id,
            'logto_sub' => $logtoUser->sub,
            'email' => $email,
            'is_admin' => $user->is_admin,
            'is_first_user' => $isFirstUser,
        ]);

        return $user;
    }

    /**
     * Update existing local user from Logto user information
     *
     * @param User $user Local user instance
     * @param object $logtoUser Logto user information
     * @return void
     */
    protected function updateUserFromLogto(User $user, object $logtoUser): void
    {
        $updated = false;

        // Update email if changed
        if (isset($logtoUser->email) && $user->email !== $logtoUser->email) {
            $user->email = $logtoUser->email;
            $updated = true;
        }

        // Update name if provided
        if (isset($logtoUser->name) && $user->name !== $logtoUser->name) {
            $user->name = $logtoUser->name;
            $updated = true;
        }

        // Update username if provided
        if (isset($logtoUser->username) && $user->username !== $logtoUser->username) {
            $user->username = $logtoUser->username;
            $updated = true;
        }

        // Update avatar if provided
        if (isset($logtoUser->picture) && $user->avatar !== $logtoUser->picture) {
            $user->avatar = $logtoUser->picture;
            $updated = true;
        }

        // Update phone if provided
        if (isset($logtoUser->phone_number) && $user->phone !== $logtoUser->phone_number) {
            $user->phone = $logtoUser->phone_number;
            $updated = true;
        }

        // Always update last login time
        $user->last_login_at = time();
        $updated = true;

        if ($updated) {
            $user->save();
            \Log::debug('Updated user from Logto', [
                'user_id' => $user->id,
                'logto_sub' => $logtoUser->sub,
            ]);
        }
    }

    /**
     * Get access token for API resource
     *
     * @param string|null $resource API resource identifier
     * @return string|null Access token
     */
    public function getAccessToken(?string $resource = null): ?string
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            $resource = $resource ?? (config('logto.resources')[0] ?? null);
            return $resource ? $this->client->getAccessToken($resource) : null;
        } catch (\Throwable $e) {
            \Log::error('Failed to get access token', [
                'error' => $e->getMessage(),
                'resource' => $resource,
            ]);
            return null;
        }
    }

    /**
     * Get the Logto client instance
     *
     * @return LogtoClient
     */
    public function getClient(): LogtoClient
    {
        return $this->client;
    }
}

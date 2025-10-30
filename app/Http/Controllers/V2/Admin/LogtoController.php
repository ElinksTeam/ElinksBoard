<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Services\LogtoAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LogtoController extends Controller
{
    /**
     * Get current Logto configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig(Request $request)
    {
        try {
            $config = [
                'logto_endpoint' => admin_setting('logto_endpoint', config('logto.endpoint')),
                'logto_app_id' => admin_setting('logto_app_id', config('logto.app_id')),
                'logto_app_secret' => admin_setting('logto_app_secret') ? '••••••••' : '', // Mask secret
                'logto_redirect_uri' => admin_setting('logto_redirect_uri', config('logto.redirect_uri')),
                'logto_post_logout_redirect_uri' => admin_setting('logto_post_logout_redirect_uri', config('logto.post_logout_redirect_uri')),
                'logto_auto_create_user' => (bool) admin_setting('logto_auto_create_user', config('logto.user_sync.auto_create', true)),
                'logto_auto_update_user' => (bool) admin_setting('logto_auto_update_user', config('logto.user_sync.auto_update', true)),
            ];

            return $this->success($config);
        } catch (\Throwable $e) {
            Log::error('Failed to get Logto config', [
                'error' => $e->getMessage(),
            ]);
            return $this->fail([500, 'Failed to get configuration']);
        }
    }

    /**
     * Update Logto configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveConfig(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'logto_endpoint' => 'required|url',
                'logto_app_id' => 'required|string|min:10',
                'logto_app_secret' => 'nullable|string|min:20',
                'logto_redirect_uri' => 'nullable|url',
                'logto_post_logout_redirect_uri' => 'nullable|url',
                'logto_auto_create_user' => 'nullable|boolean',
                'logto_auto_update_user' => 'nullable|boolean',
            ], [
                'logto_endpoint.required' => 'Logto Endpoint 不能为空',
                'logto_endpoint.url' => 'Logto Endpoint 必须是有效的 URL',
                'logto_app_id.required' => 'App ID 不能为空',
                'logto_app_id.min' => 'App ID 长度不能少于 10 个字符',
                'logto_app_secret.min' => 'App Secret 长度不能少于 20 个字符',
                'logto_redirect_uri.url' => 'Redirect URI 必须是有效的 URL',
                'logto_post_logout_redirect_uri.url' => 'Post Logout Redirect URI 必须是有效的 URL',
            ]);

            if ($validator->fails()) {
                return $this->fail([422, $validator->errors()->first()]);
            }

            $settings = [
                'logto_endpoint' => $request->input('logto_endpoint'),
                'logto_app_id' => $request->input('logto_app_id'),
                'logto_redirect_uri' => $request->input('logto_redirect_uri', config('logto.redirect_uri')),
                'logto_post_logout_redirect_uri' => $request->input('logto_post_logout_redirect_uri', config('logto.post_logout_redirect_uri')),
                'logto_auto_create_user' => $request->input('logto_auto_create_user', true) ? 1 : 0,
                'logto_auto_update_user' => $request->input('logto_auto_update_user', true) ? 1 : 0,
            ];

            // Only update secret if provided (not masked)
            if ($request->filled('logto_app_secret') && $request->input('logto_app_secret') !== '••••••••') {
                $settings['logto_app_secret'] = $request->input('logto_app_secret');
            }

            admin_setting($settings);

            Log::info('Logto configuration updated', [
                'admin_id' => $request->user()->id,
                'endpoint' => $settings['logto_endpoint'],
            ]);

            return $this->success([
                'message' => 'Logto 配置已更新',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save Logto config', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->fail([500, 'Failed to save configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * Test Logto connection
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'logto_endpoint' => 'required|url',
                'logto_app_id' => 'required|string',
                'logto_app_secret' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->fail([422, $validator->errors()->first()]);
            }

            // Temporarily set config for testing
            $originalEndpoint = config('logto.endpoint');
            $originalAppId = config('logto.app_id');
            $originalAppSecret = config('logto.app_secret');

            config([
                'logto.endpoint' => $request->input('logto_endpoint'),
                'logto.app_id' => $request->input('logto_app_id'),
                'logto.app_secret' => $request->input('logto_app_secret'),
            ]);

            try {
                // Try to create a Logto client and get OIDC config
                $client = new \GuzzleHttp\Client();
                $response = $client->get($request->input('logto_endpoint') . '/oidc/.well-known/openid-configuration', [
                    'timeout' => 10,
                ]);

                if ($response->getStatusCode() === 200) {
                    $config = json_decode($response->getBody(), true);
                    
                    return $this->success([
                        'message' => 'Logto 连接测试成功',
                        'issuer' => $config['issuer'] ?? null,
                        'authorization_endpoint' => $config['authorization_endpoint'] ?? null,
                    ]);
                }

                return $this->fail([500, 'Logto 连接失败：无效的响应']);
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                return $this->fail([500, 'Logto 连接失败：' . $e->getMessage()]);
            } finally {
                // Restore original config
                config([
                    'logto.endpoint' => $originalEndpoint,
                    'logto.app_id' => $originalAppId,
                    'logto.app_secret' => $originalAppSecret,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to test Logto connection', [
                'error' => $e->getMessage(),
            ]);
            return $this->fail([500, 'Connection test failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Get Logto setup instructions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInstructions(Request $request)
    {
        $appUrl = admin_setting('app_url', config('app.url'));
        
        return $this->success([
            'redirect_uri' => $appUrl . '/api/v1/passport/auth/logto/callback',
            'post_logout_redirect_uri' => $appUrl,
            'instructions' => [
                'step1' => [
                    'title' => '创建 Logto 应用',
                    'content' => '访问 Logto Console (https://cloud.logto.io 或你的自托管实例)，创建一个新的 Traditional Web Application。',
                ],
                'step2' => [
                    'title' => '配置 Redirect URIs',
                    'content' => '在应用设置中添加以下 Redirect URIs：',
                    'uris' => [
                        $appUrl . '/api/v1/passport/auth/logto/callback',
                    ],
                ],
                'step3' => [
                    'title' => '配置 Post Sign-out Redirect URIs',
                    'content' => '在应用设置中添加以下 Post Sign-out Redirect URIs：',
                    'uris' => [
                        $appUrl,
                    ],
                ],
                'step4' => [
                    'title' => '复制凭据',
                    'content' => '从 Logto 应用详情页复制 App ID、App Secret 和 Endpoint，填写到下方表单中。',
                ],
            ],
        ]);
    }

    /**
     * Get Logto statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        try {
            $totalUsers = \App\Models\User::count();
            $logtoUsers = \App\Models\User::where('auth_provider', 'logto')->count();
            $localUsers = \App\Models\User::where('auth_provider', 'local')->count();

            return $this->success([
                'total_users' => $totalUsers,
                'logto_users' => $logtoUsers,
                'local_users' => $localUsers,
                'logto_percentage' => $totalUsers > 0 ? round(($logtoUsers / $totalUsers) * 100, 2) : 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to get Logto stats', [
                'error' => $e->getMessage(),
            ]);
            return $this->fail([500, 'Failed to get statistics']);
        }
    }
}

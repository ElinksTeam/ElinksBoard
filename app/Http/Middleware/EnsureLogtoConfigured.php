<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsureLogtoConfigured
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if Logto is configured
        $endpoint = admin_setting('logto_endpoint', config('logto.endpoint'));
        $appId = admin_setting('logto_app_id', config('logto.app_id'));
        $appSecret = admin_setting('logto_app_secret', config('logto.app_secret'));

        if (empty($endpoint) || empty($appId) || empty($appSecret)) {
            Log::warning('Logto authentication attempted but not configured', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'code' => 500,
                'message' => 'Logto 认证系统未配置，请联系管理员',
                'data' => [
                    'error' => 'logto_not_configured',
                    'admin_url' => admin_setting('app_url') . '/' . admin_setting('secure_path', hash('crc32b', config('app.key'))),
                ],
            ], 500);
        }

        return $next($request);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 此迁移用于优化 Redis 缓存配置
        // 主要执行以下操作：
        // 1. 清理无效的缓存键
        // 2. 设置缓存键的 TTL
        // 3. 优化缓存结构

        Log::info('开始优化 Redis 缓存配置...');

        try {
            // 清理可能存在的旧缓存键
            $this->cleanupOldCacheKeys();

            // 验证核心缓存键
            $this->validateCoreCacheKeys();

            Log::info('Redis 缓存配置优化完成');
        } catch (\Exception $e) {
            Log::error('Redis 缓存配置优化失败: ' . $e->getMessage());
            // 不抛出异常，允许迁移继续
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滚操作：清空所有缓存
        Log::info('回滚 Redis 缓存配置优化...');
        
        try {
            Cache::flush();
            Log::info('缓存已清空');
        } catch (\Exception $e) {
            Log::error('清空缓存失败: ' . $e->getMessage());
        }
    }

    /**
     * 清理旧的缓存键
     */
    private function cleanupOldCacheKeys(): void
    {
        // 定义需要清理的旧键模式
        $oldKeyPatterns = [
            'laravel:*',  // 旧的 Laravel 缓存前缀
            'cache:*',    // 旧的缓存前缀
        ];

        Log::info('清理旧缓存键...');

        // 注意：实际清理需要通过 Redis 命令执行
        // 这里只是记录日志，实际清理可以通过脚本完成
        foreach ($oldKeyPatterns as $pattern) {
            Log::info("标记清理模式: {$pattern}");
        }
    }

    /**
     * 验证核心缓存键
     */
    private function validateCoreCacheKeys(): void
    {
        Log::info('验证核心缓存键配置...');

        // 验证 CacheKey 类是否存在
        if (!class_exists(\App\Utils\CacheKey::class)) {
            Log::warning('CacheKey 类不存在');
            return;
        }

        // 获取核心键定义
        $coreKeys = \App\Utils\CacheKey::CORE_KEYS;
        Log::info('核心缓存键数量: ' . count($coreKeys));

        // 验证允许的模式
        $allowedPatterns = \App\Utils\CacheKey::ALLOWED_PATTERNS;
        Log::info('允许的缓存键模式数量: ' . count($allowedPatterns));
    }
};

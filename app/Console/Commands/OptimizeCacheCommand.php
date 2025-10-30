<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Utils\CacheKey;

class OptimizeCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:optimize
                            {--analyze : 分析缓存使用情况}
                            {--cleanup : 清理过期和无效的缓存}
                            {--report : 生成缓存报告}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '优化 Redis 缓存性能';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('开始缓存优化...');

        if ($this->option('analyze')) {
            $this->analyzeCacheUsage();
        }

        if ($this->option('cleanup')) {
            $this->cleanupCache();
        }

        if ($this->option('report')) {
            $this->generateReport();
        }

        if (!$this->option('analyze') && !$this->option('cleanup') && !$this->option('report')) {
            // 默认执行所有操作
            $this->analyzeCacheUsage();
            $this->cleanupCache();
            $this->generateReport();
        }

        $this->info('缓存优化完成！');
        return Command::SUCCESS;
    }

    /**
     * 分析缓存使用情况
     */
    private function analyzeCacheUsage(): void
    {
        $this->info('分析缓存使用情况...');

        try {
            $redis = Redis::connection();
            
            // 获取数据库信息
            $info = $redis->info('keyspace');
            $this->line('键空间信息:');
            foreach ($info as $key => $value) {
                $this->line("  {$key}: {$value}");
            }

            // 获取内存信息
            $memoryInfo = $redis->info('memory');
            $usedMemory = $memoryInfo['used_memory_human'] ?? 'N/A';
            $peakMemory = $memoryInfo['used_memory_peak_human'] ?? 'N/A';
            
            $this->line('');
            $this->line('内存使用:');
            $this->line("  当前: {$usedMemory}");
            $this->line("  峰值: {$peakMemory}");

            // 获取统计信息
            $stats = $redis->info('stats');
            $totalCommands = $stats['total_commands_processed'] ?? 0;
            $opsPerSec = $stats['instantaneous_ops_per_sec'] ?? 0;
            
            $this->line('');
            $this->line('性能统计:');
            $this->line("  总命令数: {$totalCommands}");
            $this->line("  每秒操作数: {$opsPerSec}");

            // 计算缓存命中率
            $hits = $stats['keyspace_hits'] ?? 0;
            $misses = $stats['keyspace_misses'] ?? 0;
            $total = $hits + $misses;
            
            if ($total > 0) {
                $hitRate = round(($hits / $total) * 100, 2);
                $this->line("  缓存命中率: {$hitRate}%");
                
                if ($hitRate < 80) {
                    $this->warn('  ⚠️  缓存命中率较低，建议检查缓存策略');
                }
            }

        } catch (\Exception $e) {
            $this->error('分析失败: ' . $e->getMessage());
        }
    }

    /**
     * 清理缓存
     */
    private function cleanupCache(): void
    {
        $this->info('清理过期和无效的缓存...');

        try {
            $redis = Redis::connection();
            
            // 获取所有键（使用 SCAN 避免阻塞）
            $cursor = 0;
            $cleanedCount = 0;
            $scannedCount = 0;

            do {
                $result = $redis->scan($cursor, ['MATCH' => '*', 'COUNT' => 100]);
                $cursor = $result[0];
                $keys = $result[1] ?? [];

                foreach ($keys as $key) {
                    $scannedCount++;
                    
                    // 检查 TTL
                    $ttl = $redis->ttl($key);
                    
                    // 删除已过期但未被自动清理的键
                    if ($ttl === -2) {
                        $redis->del($key);
                        $cleanedCount++;
                    }
                    
                    // 检查是否为空值
                    $type = $redis->type($key);
                    if ($type === 'string') {
                        $value = $redis->get($key);
                        if (empty($value) || $value === 'null') {
                            $redis->del($key);
                            $cleanedCount++;
                        }
                    }
                }

            } while ($cursor != 0);

            $this->line("扫描键数: {$scannedCount}");
            $this->line("清理键数: {$cleanedCount}");

        } catch (\Exception $e) {
            $this->error('清理失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成缓存报告
     */
    private function generateReport(): void
    {
        $this->info('生成缓存报告...');

        try {
            $redis = Redis::connection();
            
            // 统计不同类型的键
            $keyTypes = [];
            $cursor = 0;
            $sampleSize = 1000;
            $sampledCount = 0;

            do {
                $result = $redis->scan($cursor, ['MATCH' => '*', 'COUNT' => 100]);
                $cursor = $result[0];
                $keys = $result[1] ?? [];

                foreach ($keys as $key) {
                    if ($sampledCount >= $sampleSize) {
                        break 2;
                    }

                    $type = $redis->type($key);
                    $keyTypes[$type] = ($keyTypes[$type] ?? 0) + 1;
                    $sampledCount++;
                }

            } while ($cursor != 0 && $sampledCount < $sampleSize);

            $this->line('');
            $this->line('键类型分布 (样本: ' . $sampledCount . '):');
            foreach ($keyTypes as $type => $count) {
                $percentage = round(($count / $sampledCount) * 100, 2);
                $this->line("  {$type}: {$count} ({$percentage}%)");
            }

            // 显示核心缓存键信息
            $this->line('');
            $this->line('核心缓存键配置:');
            $coreKeys = CacheKey::CORE_KEYS;
            $this->line('  核心键数量: ' . count($coreKeys));
            
            $allowedPatterns = CacheKey::ALLOWED_PATTERNS;
            $this->line('  允许的模式数量: ' . count($allowedPatterns));

        } catch (\Exception $e) {
            $this->error('生成报告失败: ' . $e->getMessage());
        }
    }
}

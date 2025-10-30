# Redis 缓存管理指南

本文档提供 XBoard 项目中 Redis 缓存的完整管理指南，包括配置、监控、备份和优化。

## 目录

- [概述](#概述)
- [架构设计](#架构设计)
- [配置说明](#配置说明)
- [使用指南](#使用指南)
- [监控与维护](#监控与维护)
- [备份与恢复](#备份与恢复)
- [性能优化](#性能优化)
- [故障排查](#故障排查)
- [最佳实践](#最佳实践)

## 概述

XBoard 使用 Redis 作为主要的缓存和队列存储系统。本项目实现了标准化的缓存键管理方案，确保缓存使用的一致性和可维护性。

### 核心特性

- ✅ 标准化的缓存键管理（`CacheKey` 工具类）
- ✅ Unix Socket 连接（高性能）
- ✅ 自动化备份和恢复
- ✅ 实时监控和告警
- ✅ 性能优化工具
- ✅ 完整的文档和脚本

## 架构设计

### 缓存键管理

项目使用 `App\Utils\CacheKey` 类统一管理所有缓存键：

```php
use App\Utils\CacheKey;

// 使用核心键
$key = CacheKey::get('EMAIL_VERIFY_CODE', $email);

// 使用模式键
$key = CacheKey::get('SERVER_VMESS_ONLINE_USER', $serverId);
```

### 核心缓存键

| 键名 | 说明 | 示例 |
|------|------|------|
| `EMAIL_VERIFY_CODE` | 邮箱验证码 | `EMAIL_VERIFY_CODE_user@example.com` |
| `TEMP_TOKEN` | 临时令牌 | `TEMP_TOKEN_abc123` |
| `USER_SESSIONS` | 用户会话 | `USER_SESSIONS_12345` |
| `REGISTER_IP_RATE_LIMIT` | 注册频率限制 | `REGISTER_IP_RATE_LIMIT_192.168.1.1` |
| `PASSWORD_ERROR_LIMIT` | 密码错误限制 | `PASSWORD_ERROR_LIMIT_user@example.com` |

### 允许的键模式

- `SERVER_*_ONLINE_USER` - 节点在线用户
- `MULTI_SERVER_*_ONLINE_USER` - 多服务器在线用户
- `SERVER_*_LAST_CHECK_AT` - 节点最后检查时间
- `SERVER_*_LAST_PUSH_AT` - 节点最后推送时间
- `SERVER_*_LOAD_STATUS` - 节点负载状态
- `SERVER_*_LAST_LOAD_AT` - 节点最后负载提交时间

## 配置说明

### 环境变量

在 `.env` 文件中配置 Redis：

```env
# Redis 配置
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# 缓存驱动
CACHE_DRIVER=redis

# 队列连接
QUEUE_CONNECTION=redis
```

### Docker Compose 配置

Redis 服务配置（`compose.sample.yaml`）：

```yaml
redis:
  image: redis:7-alpine
  command: redis-server --unixsocket /data/redis.sock --unixsocketperm 777
  restart: unless-stopped
  volumes:
    - ./.docker/.data/redis:/data
  sysctls:
    net.core.somaxconn: 1024
```

### Laravel 缓存配置

在 `config/cache.php` 中，Redis 配置使用 Unix Socket：

```php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
],
```

## 使用指南

### 基本操作

#### 存储缓存

```php
use Illuminate\Support\Facades\Cache;
use App\Utils\CacheKey;

// 存储验证码（5分钟过期）
$key = CacheKey::get('EMAIL_VERIFY_CODE', $email);
Cache::put($key, $code, now()->addMinutes(5));

// 存储临时令牌（1小时过期）
$key = CacheKey::get('TEMP_TOKEN', $token);
Cache::put($key, $data, now()->addHour());
```

#### 读取缓存

```php
$key = CacheKey::get('EMAIL_VERIFY_CODE', $email);
$code = Cache::get($key);

// 带默认值
$code = Cache::get($key, 'default_value');
```

#### 删除缓存

```php
$key = CacheKey::get('EMAIL_VERIFY_CODE', $email);
Cache::forget($key);
```

#### 检查缓存是否存在

```php
$key = CacheKey::get('EMAIL_VERIFY_CODE', $email);
if (Cache::has($key)) {
    // 缓存存在
}
```

### 高级操作

#### 原子操作

```php
// 递增
$key = CacheKey::get('PASSWORD_ERROR_LIMIT', $email);
Cache::increment($key);

// 递减
Cache::decrement($key);

// 带步长
Cache::increment($key, 5);
```

#### 记忆化（Remember）

```php
$key = CacheKey::get('USER_SESSIONS', $userId);
$sessions = Cache::remember($key, 3600, function () use ($userId) {
    return DB::table('sessions')->where('user_id', $userId)->get();
});
```

#### 永久缓存

```php
$key = CacheKey::get('SCHEDULE_LAST_CHECK_AT');
Cache::forever($key, now());
```

## 监控与维护

### 使用监控脚本

基本监控：

```bash
./scripts/monitor-redis.sh
```

详细监控（包含慢查询和键空间分析）：

```bash
./scripts/monitor-redis.sh -v
```

### 使用 Artisan 命令

分析缓存使用情况：

```bash
php artisan cache:optimize --analyze
```

清理过期缓存：

```bash
php artisan cache:optimize --cleanup
```

生成缓存报告：

```bash
php artisan cache:optimize --report
```

执行完整优化：

```bash
php artisan cache:optimize
```

### 监控指标

关键指标：

- **内存使用率**：应保持在 80% 以下
- **缓存命中率**：应保持在 80% 以上
- **连接数**：监控异常连接增长
- **键数量**：避免键数量过多（建议 < 100,000）
- **每秒操作数**：监控性能瓶颈

### Prometheus 监控

如果使用 Prometheus，可以配置 Redis Exporter：

```yaml
# docker-compose.yml 添加
redis-exporter:
  image: oliver006/redis_exporter:latest
  ports:
    - "9121:9121"
  environment:
    REDIS_ADDR: "unix:///data/redis.sock"
  volumes:
    - ./.docker/.data/redis:/data
```

配置文件位于：`config/monitoring/redis-exporter.yml`

## 备份与恢复

### 自动备份

创建备份：

```bash
./scripts/backup-redis.sh
```

使用环境变量自定义：

```bash
# 自定义备份目录
BACKUP_DIR=/path/to/backups ./scripts/backup-redis.sh

# 自定义保留天数
RETENTION_DAYS=30 ./scripts/backup-redis.sh
```

### 恢复备份

恢复最新备份：

```bash
./scripts/restore-redis.sh latest
```

恢复指定备份：

```bash
./scripts/restore-redis.sh redis_backup_20240101_120000.tar.gz
```

查看可用备份：

```bash
./scripts/restore-redis.sh
```

### 定时备份

添加到 crontab：

```bash
# 每天凌晨 2 点备份
0 2 * * * cd /path/to/xboard && ./scripts/backup-redis.sh >> /var/log/redis-backup.log 2>&1
```

## 性能优化

### 运行优化迁移

```bash
php artisan migrate
```

这将运行 `2025_10_30_000001_optimize_redis_cache_configuration.php` 迁移。

### 优化建议

#### 1. 使用合适的 TTL

```php
// 短期数据（验证码）
Cache::put($key, $value, now()->addMinutes(5));

// 中期数据（会话）
Cache::put($key, $value, now()->addHours(24));

// 长期数据（配置）
Cache::put($key, $value, now()->addDays(7));
```

#### 2. 批量操作

```php
// 使用 Pipeline
Redis::pipeline(function ($pipe) {
    for ($i = 0; $i < 1000; $i++) {
        $key = CacheKey::get('SERVER_VMESS_ONLINE_USER', $i);
        $pipe->set($key, $data[$i]);
    }
});
```

#### 3. 避免大键

```php
// 不好：存储大量数据在单个键
Cache::put('all_users', $allUsers); // 可能有数万条记录

// 好：分片存储
foreach ($users as $user) {
    $key = CacheKey::get('USER_SESSIONS', $user->id);
    Cache::put($key, $user->sessions);
}
```

#### 4. 使用合适的数据结构

```php
// 使用 Hash 存储相关数据
Redis::hset('user:1000', 'name', 'John');
Redis::hset('user:1000', 'email', 'john@example.com');

// 使用 Set 存储唯一值
Redis::sadd('online_users', $userId);

// 使用 Sorted Set 存储排序数据
Redis::zadd('leaderboard', $score, $userId);
```

### 性能监控

定期检查慢查询：

```bash
# 进入 Redis 容器
docker compose exec redis redis-cli

# 查看慢查询
SLOWLOG GET 10

# 查看慢查询配置
CONFIG GET slowlog-*
```

## 故障排查

### 常见问题

#### 1. Redis 连接失败

**症状**：应用无法连接到 Redis

**排查步骤**：

```bash
# 检查 Redis 容器状态
docker compose ps redis

# 检查 Redis 日志
docker compose logs redis

# 测试连接
docker compose exec redis redis-cli ping
```

**解决方案**：

```bash
# 重启 Redis
docker compose restart redis

# 检查 Unix Socket 权限
ls -la ./.docker/.data/redis/redis.sock
```

#### 2. 内存不足

**症状**：Redis 内存使用率过高

**排查步骤**：

```bash
# 检查内存使用
./scripts/monitor-redis.sh

# 查看大键
docker compose exec redis redis-cli --bigkeys
```

**解决方案**：

```bash
# 清理过期键
php artisan cache:optimize --cleanup

# 清空所有缓存（谨慎使用）
php artisan cache:clear
```

#### 3. 缓存命中率低

**症状**：缓存命中率低于 80%

**排查步骤**：

```bash
# 分析缓存使用
php artisan cache:optimize --analyze

# 查看键空间
./scripts/monitor-redis.sh -v
```

**解决方案**：

- 检查 TTL 设置是否合理
- 增加常用数据的缓存
- 优化缓存键的设计

#### 4. 键数量过多

**症状**：Redis 键数量超过 100,000

**排查步骤**：

```bash
# 生成报告
php artisan cache:optimize --report

# 分析键类型
./scripts/monitor-redis.sh -v
```

**解决方案**：

- 设置合理的 TTL
- 清理无用的键
- 优化数据结构

### 日志分析

查看 Laravel 日志：

```bash
tail -f storage/logs/laravel.log | grep -i redis
```

查看 Redis 日志：

```bash
docker compose logs -f redis
```

## 最佳实践

### 1. 始终使用 CacheKey 工具类

```php
// ✅ 好
$key = CacheKey::get('EMAIL_VERIFY_CODE', $email);

// ❌ 不好
$key = 'email_verify_code_' . $email;
```

### 2. 设置合理的 TTL

```php
// ✅ 好：明确的过期时间
Cache::put($key, $value, now()->addMinutes(5));

// ❌ 不好：永久缓存
Cache::forever($key, $value);
```

### 3. 处理缓存失败

```php
// ✅ 好：优雅降级
try {
    $value = Cache::get($key);
    if (!$value) {
        $value = $this->fetchFromDatabase();
        Cache::put($key, $value, 3600);
    }
} catch (\Exception $e) {
    Log::error('Cache error: ' . $e->getMessage());
    $value = $this->fetchFromDatabase();
}

// ❌ 不好：不处理异常
$value = Cache::get($key);
```

### 4. 避免缓存穿透

```php
// ✅ 好：缓存空值
$value = Cache::remember($key, 300, function () use ($id) {
    $data = DB::table('users')->find($id);
    return $data ?: 'null'; // 缓存空值
});

if ($value === 'null') {
    return null;
}
```

### 5. 使用标签（如果需要）

```php
// 按标签分组缓存
Cache::tags(['users', 'premium'])->put($key, $value, 3600);

// 清除特定标签的缓存
Cache::tags(['users'])->flush();
```

### 6. 监控和告警

- 设置 Prometheus 告警规则
- 定期运行监控脚本
- 配置日志告警

### 7. 定期备份

- 配置自动备份（cron）
- 测试恢复流程
- 保留多个备份版本

### 8. 性能测试

```php
// 使用 Laravel Telescope 监控缓存性能
// 或使用自定义性能测试

$start = microtime(true);
$value = Cache::get($key);
$duration = microtime(true) - $start;

if ($duration > 0.1) {
    Log::warning("Slow cache operation: {$duration}s");
}
```

## 相关资源

- [Laravel Cache 文档](https://laravel.com/docs/cache)
- [Redis 官方文档](https://redis.io/documentation)
- [Redis 最佳实践](https://redis.io/topics/best-practices)
- [XBoard 实施状态](../IMPLEMENTATION_STATUS.md)

## 维护清单

### 日常维护

- [ ] 检查 Redis 运行状态
- [ ] 监控内存使用率
- [ ] 检查缓存命中率
- [ ] 查看错误日志

### 周维护

- [ ] 运行性能分析
- [ ] 清理过期缓存
- [ ] 检查慢查询日志
- [ ] 验证备份完整性

### 月维护

- [ ] 生成性能报告
- [ ] 优化缓存策略
- [ ] 更新监控规则
- [ ] 测试恢复流程

## 支持

如有问题，请：

1. 查看本文档的故障排查部分
2. 检查 Laravel 日志和 Redis 日志
3. 运行诊断脚本：`./scripts/monitor-redis.sh -v`
4. 提交 Issue 到项目仓库

---

**最后更新**：2025-10-30  
**版本**：1.0.0

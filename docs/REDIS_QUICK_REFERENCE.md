# Redis 缓存快速参考

快速查找常用的 Redis 缓存操作和命令。

## 快速命令

### 监控

```bash
# 基本监控
./scripts/monitor-redis.sh

# 详细监控
./scripts/monitor-redis.sh -v

# 性能分析
php artisan cache:optimize --analyze

# 生成报告
php artisan cache:optimize --report
```

### 备份与恢复

```bash
# 创建备份
./scripts/backup-redis.sh

# 恢复最新备份
./scripts/restore-redis.sh latest

# 恢复指定备份
./scripts/restore-redis.sh redis_backup_20240101_120000.tar.gz
```

### 优化

```bash
# 完整优化
php artisan cache:optimize

# 仅清理
php artisan cache:optimize --cleanup

# 清空所有缓存
php artisan cache:clear
```

### Docker 操作

```bash
# 查看 Redis 状态
docker compose ps redis

# 查看 Redis 日志
docker compose logs -f redis

# 重启 Redis
docker compose restart redis

# 进入 Redis CLI
docker compose exec redis redis-cli
```

## 代码示例

### 基本操作

```php
use Illuminate\Support\Facades\Cache;
use App\Utils\CacheKey;

// 存储
$key = CacheKey::get('EMAIL_VERIFY_CODE', $email);
Cache::put($key, $code, now()->addMinutes(5));

// 读取
$code = Cache::get($key);

// 删除
Cache::forget($key);

// 检查存在
if (Cache::has($key)) { }
```

### 高级操作

```php
// 记忆化
$data = Cache::remember($key, 3600, function () {
    return DB::table('users')->get();
});

// 原子操作
Cache::increment($key);
Cache::decrement($key);

// 永久缓存
Cache::forever($key, $value);

// 批量操作
Redis::pipeline(function ($pipe) {
    $pipe->set('key1', 'value1');
    $pipe->set('key2', 'value2');
});
```

## Redis CLI 命令

```bash
# 进入 CLI
docker compose exec redis redis-cli

# 基本命令
PING                    # 测试连接
INFO                    # 查看信息
DBSIZE                  # 键数量
KEYS *                  # 列出所有键（慎用）
SCAN 0 COUNT 100        # 扫描键（推荐）

# 键操作
GET key                 # 获取值
SET key value           # 设置值
DEL key                 # 删除键
TTL key                 # 查看过期时间
EXPIRE key 3600         # 设置过期时间

# 性能分析
SLOWLOG GET 10          # 慢查询日志
CLIENT LIST             # 客户端列表
MEMORY USAGE key        # 键内存使用

# 清理
FLUSHDB                 # 清空当前数据库
FLUSHALL                # 清空所有数据库（危险）
```

## 核心缓存键

| 键名 | 用途 | TTL |
|------|------|-----|
| `EMAIL_VERIFY_CODE` | 邮箱验证码 | 5分钟 |
| `TEMP_TOKEN` | 临时令牌 | 1小时 |
| `USER_SESSIONS` | 用户会话 | 24小时 |
| `REGISTER_IP_RATE_LIMIT` | 注册限制 | 1小时 |
| `PASSWORD_ERROR_LIMIT` | 密码错误限制 | 15分钟 |
| `SERVER_*_ONLINE_USER` | 在线用户 | 动态 |

## 监控指标阈值

| 指标 | 正常范围 | 告警阈值 |
|------|----------|----------|
| 内存使用率 | < 70% | > 80% |
| 缓存命中率 | > 85% | < 80% |
| 连接数 | < 50 | > 100 |
| 键数量 | < 50,000 | > 100,000 |
| 每秒操作数 | < 1000 | > 5000 |

## 故障排查流程

1. **检查服务状态**
   ```bash
   docker compose ps redis
   ```

2. **查看日志**
   ```bash
   docker compose logs redis --tail=100
   ```

3. **运行诊断**
   ```bash
   ./scripts/monitor-redis.sh -v
   ```

4. **检查连接**
   ```bash
   docker compose exec redis redis-cli PING
   ```

5. **分析性能**
   ```bash
   php artisan cache:optimize --analyze
   ```

## 环境变量

```env
# Redis 连接
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# 缓存配置
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# 备份配置
BACKUP_DIR=./.docker/.backups/redis
RETENTION_DAYS=7

# 告警阈值
ALERT_MEMORY_THRESHOLD=80
ALERT_KEYS_THRESHOLD=100000
```

## 定时任务

```bash
# 添加到 crontab
crontab -e

# 每天凌晨 2 点备份
0 2 * * * cd /path/to/xboard && ./scripts/backup-redis.sh

# 每小时监控
0 * * * * cd /path/to/xboard && ./scripts/monitor-redis.sh >> /var/log/redis-monitor.log

# 每天清理过期缓存
0 3 * * * cd /path/to/xboard && php artisan cache:optimize --cleanup
```

## 常见错误

### Connection refused

```bash
# 检查 Redis 是否运行
docker compose ps redis

# 重启 Redis
docker compose restart redis
```

### Out of memory

```bash
# 清理缓存
php artisan cache:optimize --cleanup

# 或清空所有
php artisan cache:clear
```

### Permission denied (socket)

```bash
# 检查 socket 权限
ls -la ./.docker/.data/redis/redis.sock

# 修复权限
chmod 777 ./.docker/.data/redis/redis.sock
```

## 性能优化技巧

1. **使用 Pipeline**：批量操作减少网络往返
2. **设置合理 TTL**：避免内存浪费
3. **避免大键**：单个键不超过 1MB
4. **使用 Hash**：相关数据存储在一起
5. **监控慢查询**：定期检查 SLOWLOG

## 相关文档

- [完整指南](./REDIS_CACHE_GUIDE.md)
- [实施状态](../IMPLEMENTATION_STATUS.md)
- [安装指南](../INSTALLATION_GUIDE.md)

---

**提示**：将此文档加入书签以便快速查找！

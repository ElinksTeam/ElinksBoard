#!/bin/bash

# Redis 监控脚本
# 监控 Redis 性能指标和缓存使用情况

set -e

# 配置
REDIS_SOCKET="${REDIS_SOCKET:-./.docker/.data/redis/redis.sock}"
ALERT_MEMORY_THRESHOLD="${ALERT_MEMORY_THRESHOLD:-80}"  # 内存使用率告警阈值 (%)
ALERT_KEYS_THRESHOLD="${ALERT_KEYS_THRESHOLD:-100000}"  # 键数量告警阈值

# 颜色输出
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_metric() {
    echo -e "${BLUE}[METRIC]${NC} $1"
}

# 检查 Redis 是否可用
check_redis() {
    if command -v docker &> /dev/null; then
        if ! docker ps --format '{{.Names}}' | grep -q redis; then
            log_error "Redis 容器未运行"
            return 1
        fi
    fi
    return 0
}

# 获取 Redis 信息
get_redis_info() {
    if command -v docker &> /dev/null; then
        docker compose exec -T redis redis-cli INFO 2>/dev/null || echo ""
    else
        echo ""
    fi
}

# 获取 Redis 统计信息
get_redis_stats() {
    local info=$(get_redis_info)
    
    if [ -z "$info" ]; then
        log_error "无法获取 Redis 信息"
        return 1
    fi
    
    # 解析信息
    local used_memory=$(echo "$info" | grep "^used_memory:" | cut -d: -f2 | tr -d '\r')
    local used_memory_human=$(echo "$info" | grep "^used_memory_human:" | cut -d: -f2 | tr -d '\r')
    local used_memory_peak_human=$(echo "$info" | grep "^used_memory_peak_human:" | cut -d: -f2 | tr -d '\r')
    local total_keys=$(echo "$info" | grep "^db0:" | sed 's/.*keys=\([0-9]*\).*/\1/')
    local connected_clients=$(echo "$info" | grep "^connected_clients:" | cut -d: -f2 | tr -d '\r')
    local uptime_days=$(echo "$info" | grep "^uptime_in_days:" | cut -d: -f2 | tr -d '\r')
    local total_commands=$(echo "$info" | grep "^total_commands_processed:" | cut -d: -f2 | tr -d '\r')
    local ops_per_sec=$(echo "$info" | grep "^instantaneous_ops_per_sec:" | cut -d: -f2 | tr -d '\r')
    local hit_rate=$(echo "$info" | grep "^keyspace_hits:" | cut -d: -f2 | tr -d '\r')
    local miss_rate=$(echo "$info" | grep "^keyspace_misses:" | cut -d: -f2 | tr -d '\r')
    
    # 计算缓存命中率
    local cache_hit_ratio="N/A"
    if [ -n "$hit_rate" ] && [ -n "$miss_rate" ] && [ "$hit_rate" != "0" ]; then
        local total=$((hit_rate + miss_rate))
        if [ $total -gt 0 ]; then
            cache_hit_ratio=$(awk "BEGIN {printf \"%.2f\", ($hit_rate / $total) * 100}")
        fi
    fi
    
    # 显示统计信息
    echo ""
    log_info "=== Redis 监控报告 ==="
    echo ""
    log_metric "运行时间: ${uptime_days} 天"
    log_metric "内存使用: ${used_memory_human} (峰值: ${used_memory_peak_human})"
    log_metric "总键数: ${total_keys:-0}"
    log_metric "连接客户端: ${connected_clients}"
    log_metric "总命令数: ${total_commands}"
    log_metric "每秒操作数: ${ops_per_sec}"
    log_metric "缓存命中率: ${cache_hit_ratio}%"
    echo ""
    
    # 告警检查
    local has_alert=false
    
    # 检查键数量
    if [ -n "$total_keys" ] && [ "$total_keys" -gt "$ALERT_KEYS_THRESHOLD" ]; then
        log_warn "告警: 键数量过多 (${total_keys} > ${ALERT_KEYS_THRESHOLD})"
        has_alert=true
    fi
    
    # 检查缓存命中率
    if [ "$cache_hit_ratio" != "N/A" ]; then
        local hit_ratio_int=$(echo "$cache_hit_ratio" | cut -d. -f1)
        if [ "$hit_ratio_int" -lt 80 ]; then
            log_warn "告警: 缓存命中率较低 (${cache_hit_ratio}% < 80%)"
            has_alert=true
        fi
    fi
    
    if [ "$has_alert" = false ]; then
        log_info "✓ 所有指标正常"
    fi
    echo ""
}

# 获取慢查询日志
get_slow_log() {
    if command -v docker &> /dev/null; then
        log_info "=== 慢查询日志 (最近10条) ==="
        docker compose exec -T redis redis-cli SLOWLOG GET 10 2>/dev/null || log_warn "无法获取慢查询日志"
        echo ""
    fi
}

# 获取键空间分析
analyze_keyspace() {
    if command -v docker &> /dev/null; then
        log_info "=== 键空间分析 ==="
        
        # 获取样本键
        local sample_keys=$(docker compose exec -T redis redis-cli --scan --count 100 2>/dev/null | head -20)
        
        if [ -n "$sample_keys" ]; then
            echo "样本键 (前20个):"
            echo "$sample_keys" | while read key; do
                if [ -n "$key" ]; then
                    local ttl=$(docker compose exec -T redis redis-cli TTL "$key" 2>/dev/null | tr -d '\r')
                    local type=$(docker compose exec -T redis redis-cli TYPE "$key" 2>/dev/null | tr -d '\r')
                    echo "  - $key (类型: $type, TTL: ${ttl}s)"
                fi
            done
        else
            log_info "没有找到键"
        fi
        echo ""
    fi
}

# 主函数
main() {
    log_info "开始 Redis 监控..."
    
    if ! check_redis; then
        exit 1
    fi
    
    get_redis_stats
    
    # 如果指定了详细模式
    if [ "$1" = "-v" ] || [ "$1" = "--verbose" ]; then
        get_slow_log
        analyze_keyspace
    fi
    
    log_info "监控完成"
}

# 显示帮助
if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    echo "用法: $0 [选项]"
    echo ""
    echo "选项:"
    echo "  -v, --verbose    显示详细信息（慢查询、键空间分析）"
    echo "  -h, --help       显示此帮助信息"
    echo ""
    echo "环境变量:"
    echo "  ALERT_MEMORY_THRESHOLD    内存使用率告警阈值 (默认: 80%)"
    echo "  ALERT_KEYS_THRESHOLD      键数量告警阈值 (默认: 100000)"
    exit 0
fi

main "$@"

#!/bin/bash

# Redis 缓存恢复脚本
# 用于从备份恢复 Redis 数据

set -e

# 配置
BACKUP_DIR="${BACKUP_DIR:-./.docker/.backups/redis}"
REDIS_DATA_DIR="${REDIS_DATA_DIR:-./.docker/.data/redis}"

# 颜色输出
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
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

# 显示使用说明
show_usage() {
    echo "用法: $0 <backup_file>"
    echo ""
    echo "示例:"
    echo "  $0 redis_backup_20240101_120000.tar.gz"
    echo "  $0 latest  # 恢复最新的备份"
    echo ""
    echo "可用的备份文件:"
    if [ -d "${BACKUP_DIR}" ]; then
        ls -lh "${BACKUP_DIR}"/redis_backup_*.tar.gz 2>/dev/null | awk '{print "  " $9 " (" $5 ", " $6 " " $7 ")"}'
    else
        echo "  备份目录不存在: ${BACKUP_DIR}"
    fi
}

# 检查参数
if [ $# -eq 0 ]; then
    log_error "请指定要恢复的备份文件"
    echo ""
    show_usage
    exit 1
fi

BACKUP_FILE="$1"

# 处理 "latest" 参数
if [ "${BACKUP_FILE}" = "latest" ]; then
    BACKUP_FILE=$(ls -t "${BACKUP_DIR}"/redis_backup_*.tar.gz 2>/dev/null | head -1)
    if [ -z "${BACKUP_FILE}" ]; then
        log_error "没有找到备份文件"
        exit 1
    fi
    log_info "使用最新备份: $(basename ${BACKUP_FILE})"
fi

# 如果只提供了文件名，添加完整路径
if [ ! -f "${BACKUP_FILE}" ]; then
    BACKUP_FILE="${BACKUP_DIR}/${BACKUP_FILE}"
fi

# 检查备份文件是否存在
if [ ! -f "${BACKUP_FILE}" ]; then
    log_error "备份文件不存在: ${BACKUP_FILE}"
    echo ""
    show_usage
    exit 1
fi

log_warn "警告: 此操作将覆盖当前的 Redis 数据！"
log_info "备份文件: ${BACKUP_FILE}"
log_info "目标目录: ${REDIS_DATA_DIR}"
echo ""
read -p "确认继续? (yes/no): " CONFIRM

if [ "${CONFIRM}" != "yes" ]; then
    log_info "操作已取消"
    exit 0
fi

# 停止 Redis 容器（如果正在运行）
log_info "检查 Redis 容器状态..."
if command -v docker &> /dev/null; then
    if docker ps --format '{{.Names}}' | grep -q redis; then
        log_warn "停止 Redis 容器..."
        docker compose stop redis || true
        sleep 2
    fi
fi

# 备份当前数据（以防万一）
if [ -d "${REDIS_DATA_DIR}" ]; then
    SAFETY_BACKUP="${REDIS_DATA_DIR}_before_restore_$(date +%Y%m%d_%H%M%S)"
    log_info "创建安全备份: ${SAFETY_BACKUP}"
    cp -r "${REDIS_DATA_DIR}" "${SAFETY_BACKUP}"
fi

# 清空目标目录
log_info "清空目标目录..."
rm -rf "${REDIS_DATA_DIR}"
mkdir -p "$(dirname ${REDIS_DATA_DIR})"

# 恢复备份
log_info "恢复备份数据..."
tar -xzf "${BACKUP_FILE}" -C "$(dirname ${REDIS_DATA_DIR})" || {
    log_error "恢复失败"
    if [ -d "${SAFETY_BACKUP}" ]; then
        log_info "恢复安全备份..."
        mv "${SAFETY_BACKUP}" "${REDIS_DATA_DIR}"
    fi
    exit 1
}

# 设置权限
log_info "设置文件权限..."
chmod -R 755 "${REDIS_DATA_DIR}"

log_info "恢复完成！"

# 启动 Redis 容器
if command -v docker &> /dev/null; then
    log_info "启动 Redis 容器..."
    docker compose start redis || log_warn "请手动启动 Redis 容器"
fi

log_info "Redis 数据已恢复"
if [ -d "${SAFETY_BACKUP}" ]; then
    log_info "安全备份保存在: ${SAFETY_BACKUP}"
    log_info "确认恢复成功后可以删除: rm -rf ${SAFETY_BACKUP}"
fi

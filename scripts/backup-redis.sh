#!/bin/bash

# Redis 缓存备份脚本
# 用于备份 Redis 数据和配置

set -e

# 配置
BACKUP_DIR="${BACKUP_DIR:-./.docker/.backups/redis}"
REDIS_DATA_DIR="${REDIS_DATA_DIR:-./.docker/.data/redis}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="redis_backup_${TIMESTAMP}"
RETENTION_DAYS="${RETENTION_DAYS:-7}"

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

# 创建备份目录
mkdir -p "${BACKUP_DIR}"

log_info "开始 Redis 备份..."
log_info "备份目录: ${BACKUP_DIR}"
log_info "数据目录: ${REDIS_DATA_DIR}"

# 检查 Redis 数据目录是否存在
if [ ! -d "${REDIS_DATA_DIR}" ]; then
    log_error "Redis 数据目录不存在: ${REDIS_DATA_DIR}"
    exit 1
fi

# 创建备份
BACKUP_PATH="${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"

log_info "创建备份文件: ${BACKUP_NAME}.tar.gz"

# 备份 Redis 数据
tar -czf "${BACKUP_PATH}" -C "$(dirname ${REDIS_DATA_DIR})" "$(basename ${REDIS_DATA_DIR})" 2>/dev/null || {
    log_error "备份失败"
    exit 1
}

# 获取备份文件大小
BACKUP_SIZE=$(du -h "${BACKUP_PATH}" | cut -f1)
log_info "备份完成，大小: ${BACKUP_SIZE}"

# 清理旧备份
log_info "清理 ${RETENTION_DAYS} 天前的旧备份..."
find "${BACKUP_DIR}" -name "redis_backup_*.tar.gz" -type f -mtime +${RETENTION_DAYS} -delete 2>/dev/null || true

# 列出当前备份
BACKUP_COUNT=$(find "${BACKUP_DIR}" -name "redis_backup_*.tar.gz" -type f | wc -l)
log_info "当前保留 ${BACKUP_COUNT} 个备份文件"

log_info "备份完成！"
log_info "备份文件: ${BACKUP_PATH}"

# 输出恢复命令提示
echo ""
log_info "恢复命令:"
echo "  tar -xzf ${BACKUP_PATH} -C ./.docker/.data/"

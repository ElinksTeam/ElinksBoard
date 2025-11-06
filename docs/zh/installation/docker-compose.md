# Docker Compose 安装指南

## 📋 概述

Docker Compose 是最简单、最推荐的安装方式，适合：
- ✅ 新手用户
- ✅ 快速部署
- ✅ 开发测试环境
- ✅ 生产环境

**预计时间：** 10-15 分钟

---

## 🎯 系统要求

### 硬件要求
- **CPU**: 1核心（推荐 2核心+）
- **内存**: 1GB（推荐 2GB+）
- **存储**: 10GB（推荐 20GB+）

### 软件要求
- **操作系统**: Linux (Ubuntu 20.04+, Debian 10+, CentOS 8+)
- **Docker**: 20.10+
- **Docker Compose**: 2.0+

---

## 📦 步骤 1：安装 Docker

### Ubuntu/Debian

```bash
# 更新软件包
sudo apt update

# 安装依赖
sudo apt install -y ca-certificates curl gnupg lsb-release

# 添加 Docker 官方 GPG 密钥
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# 添加 Docker 仓库
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# 安装 Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# 启动 Docker
sudo systemctl start docker
sudo systemctl enable docker

# 验证安装
docker --version
docker compose version
```

### CentOS/RHEL

```bash
# 安装依赖
sudo yum install -y yum-utils

# 添加 Docker 仓库
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo

# 安装 Docker
sudo yum install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# 启动 Docker
sudo systemctl start docker
sudo systemctl enable docker

# 验证安装
docker --version
docker compose version
```

### 添加当前用户到 docker 组（可选）

```bash
# 添加用户到 docker 组
sudo usermod -aG docker $USER

# 重新登录或运行
newgrp docker

# 测试（无需 sudo）
docker ps
```

---

## 🚀 步骤 2：下載 ElinksBoard

```bash
# 克隆仓库（compose 分支）
git clone -b compose --depth 1 https://github.com/ElinksTeam/ElinksBoard.git

# 进入目录
cd Xboard

# 查看文件
ls -la
```

**目录结构：**
```
Xboard/
├── compose.sample.yaml    # Docker Compose 配置示例
├── .env.example          # 环境变量示例
├── app/                  # 应用代码
├── config/               # 配置文件
├── database/             # 数据库迁移
├── docs/                 # 文档
└── scripts/              # 脚本工具
```

---

## ⚙️ 步骤 3：配置环境

### 复制配置文件

```bash
# 复制 Docker Compose 配置
cp compose.sample.yaml docker-compose.yml

# 复制环境变量文件
cp .env.example .env
```

### 编辑 docker-compose.yml（可选）

```bash
nano docker-compose.yml
```

**常用配置：**

```yaml
services:
  web:
    image: ghcr.io/elinksteam/elinksboard:latest
    ports:
      - "7001:7001"  # 修改端口（可选）
    volumes:
      - ./.docker/.data/redis/:/data/
      - ./:/www/
    environment:
      - docker=true
    depends_on:
      - redis
    command: php artisan octane:start --port=7001 --host=0.0.0.0
    restart: always

  horizon:
    image: ghcr.io/elinksteam/elinksboard:latest
    volumes:
      - ./.docker/.data/redis/:/data/
      - ./:/www/
    restart: always
    command: php artisan horizon
    depends_on:
      - redis

  redis:
    image: redis:7-alpine
    command: redis-server --unixsocket /data/redis.sock --unixsocketperm 777
    restart: unless-stopped
    volumes:
      - ./.docker/.data/redis:/data
    sysctls:
      net.core.somaxconn: 1024
```

---

## 🔧 步骤 4：运行安装向导

### 使用 SQLite（推荐新手）

```bash
docker compose run -it --rm \
    -e ENABLE_SQLITE=true \
    -e ENABLE_REDIS=true \
    web # Installation wizard removed - configure via .env and admin panel
```

### 使用 MySQL

```bash
docker compose run -it --rm \
    -e ENABLE_REDIS=true \
    web # Installation wizard removed - configure via .env and admin panel
```

### 安装向导流程

#### 1. 数据库配置

**选择 SQLite：**
```
请选择数据库类型:
  [0] SQLite (推荐用于测试)
  [1] MySQL
  [2] PostgreSQL
> 0

✓ SQLite 数据库配置完成
```

**选择 MySQL：**
```
请选择数据库类型:
  [0] SQLite
  [1] MySQL
  [2] PostgreSQL
> 1

请输入 MySQL 主机 [127.0.0.1]:
> mysql

请输入 MySQL 端口 [3306]:
> 3306

请输入数据库名:
> xboard

请输入数据库用户名:
> root

请输入数据库密码:
> your_password

✓ 数据库连接测试成功
```

#### 2. Redis 配置

```
配置 Redis 缓存

请输入 Redis 主机 [127.0.0.1]:
> redis

请输入 Redis 端口 [6379]:
> 6379

请输入 Redis 密码（可选）:
> 

✓ Redis 连接测试成功
```

#### 3. Logto 配置 ⭐ **重要**

```
🔐 配置 Logto 认证系统
Logto 是现代化的身份认证服务，支持 SSO、MFA、社交登录等功能

请输入 Logto Endpoint (例如: https://your-tenant.logto.app):
> https://your-tenant.logto.app

请输入 Logto App ID:
> your_app_id_here

请输入 Logto App Secret:
> your_app_secret_here

✓ Logto 配置已保存
✓ 正在测试 Logto 连接...
✓ Logto 连接测试成功
```

**如何获取 Logto 凭据：**
1. 访问 [Logto Cloud](https://cloud.logto.io)
2. 创建 **Traditional Web Application**
3. 复制 **Endpoint**、**App ID**、**App Secret**

#### 4. 安装完成

```
🎉：一切就绪

📋 重要信息：

1. 管理面板地址：
   http://your-domain.com/abc123def

2. 首次登录用户将自动成为管理员
   - 使用 Logto 完成首次登录
   - 系统自动授予管理员权限
   - 后续用户为普通用户

3. Logto Console 配置：
   Redirect URI: http://your-domain.com/api/v1/passport/auth/logto/callback
   Post Logout URI: http://your-domain.com

⚠️  安全提示：
请立即完成首次登录以获取管理员权限！
首次登录后，其他用户将只能获得普通用户权限。
```

---

## 🎯 步骤 5：配置 Logto Console

**在启动服务前，必须先配置 Logto Console！**

1. 登录 [Logto Console](https://cloud.logto.io)
2. 进入您的应用程序设置
3. 添加 **Redirect URI**：
   ```
   http://your-domain.com/api/v1/passport/auth/logto/callback
   ```
4. 添加 **Post Sign-out Redirect URI**：
   ```
   http://your-domain.com
   ```
5. 保存更改

---

## 🚀 步骤 6：启动服务

```bash
# 启动所有服务
docker compose up -d

# 查看服务状态
docker compose ps

# 查看日志
docker compose logs -f
```

**预期输出：**
```
NAME                COMMAND                  SERVICE   STATUS    PORTS
xboard-web-1        "php artisan octane:…"   web       running   0.0.0.0:7001->7001/tcp
xboard-horizon-1    "php artisan horizon"    horizon   running
xboard-redis-1      "redis-server --unix…"   redis     running
```

---

## 🔐 步骤 7：完成首次登录 ⚠️ **关键**

**首次登录的用户将自动成为管理员！**

1. 访问您的站点：`http://服务器IP:7001`
2. 点击"使用 Logto 登录"
3. 完成 Logto 认证
4. 您将被重定向回来并获得管理员权限

**安全警告：**
- 安装后**立即**完成此步骤
- 任何完成首次登录的人都将成为管理员
- 后续用户将是普通用户

---

## ✅ 步骤 8：验证安装

### 检查服务状态

```bash
# 查看所有容器
docker compose ps

# 查看 Web 服务日志
docker compose logs web

# 查看 Horizon 日志
docker compose logs horizon

# 查看 Redis 日志
docker compose logs redis
```

### 测试网站访问

```bash
# 测试主页
curl http://localhost:7001

# 测试 API
curl http://localhost:7001/api/v1/passport/auth/logto/sign-in
```

### 检查 Redis

```bash
# 进入 Redis 容器
docker compose exec redis redis-cli

# 测试连接
PING
# 应返回: PONG

# 查看键数量
DBSIZE

# 退出
exit
```

### 访问管理面板

1. 使用安装时显示的管理员路径
2. 使用 Logto 登录
3. 验证管理员权限

---

## 🔧 常用操作

### 重启服务

```bash
# 重启所有服务
docker compose restart

# 重启单个服务
docker compose restart web
docker compose restart horizon
docker compose restart redis
```

### 查看日志

```bash
# 实时查看所有日志
docker compose logs -f

# 查看特定服务日志
docker compose logs -f web

# 查看最近 100 行日志
docker compose logs --tail=100 web
```

### 进入容器

```bash
# 进入 Web 容器
docker compose exec web bash

# 进入 Redis 容器
docker compose exec redis sh
```

### 运行 Artisan 命令

```bash
# 清理缓存
docker compose exec web php artisan cache:clear

# 清理配置缓存
docker compose exec web php artisan config:clear

# 查看路由
docker compose exec web php artisan route:list

# 运行队列
docker compose exec web php artisan queue:work

# 优化缓存
docker compose exec web php artisan cache:optimize
```

### 备份数据

```bash
# 备份 SQLite 数据库
cp database/database.sqlite database/database.sqlite.backup

# 备份 Redis
./scripts/backup-redis.sh

# 备份配置
cp .env .env.backup
```

### 更新 Xboard

```bash
# 停止服务
docker compose down

# 备份数据
cp database/database.sqlite database/database.sqlite.backup
./scripts/backup-redis.sh

# 拉取最新代码
git pull

# 拉取最新镜像
docker compose pull

# 运行迁移
docker compose run --rm web php artisan migrate --force

# 启动服务
docker compose up -d
```

---

## 🐛 故障排查

### 问题 1：端口被占用

**错误信息：**
```
Error: bind: address already in use
```

**解决方案：**
```bash
# 查看端口占用
sudo lsof -i :7001

# 修改端口
nano docker-compose.yml
# 修改 ports: - "8001:7001"

# 重启服务
docker compose up -d
```

### 问题 2：容器无法启动

**错误信息：**
```
Error: container exited with code 1
```

**解决方案：**
```bash
# 查看详细日志
docker compose logs web

# 检查配置文件
cat .env

# 重新运行安装
docker compose run --rm web # Installation wizard removed - configure via .env and admin panel

# 重启服务
docker compose restart
```

### 问题 3：无法访问网站

**检查步骤：**

1. **检查服务状态**
   ```bash
   docker compose ps
   ```

2. **检查防火墙**
   ```bash
   # Ubuntu/Debian
   sudo ufw allow 7001
   
   # CentOS/RHEL
   sudo firewall-cmd --add-port=7001/tcp --permanent
   sudo firewall-cmd --reload
   ```

3. **检查 SELinux（CentOS/RHEL）**
   ```bash
   sudo setenforce 0
   ```

4. **检查日志**
   ```bash
   docker compose logs -f web
   ```

### 问题 4：Logto 认证失败

**错误信息：**
```
Invalid redirect URI
```

**解决方案：**
1. 检查 Logto Console 中的 Redirect URI 是否正确
2. 确保 URI 完全匹配（包括协议、域名、路径）
3. 检查 `.env` 中的 `APP_URL` 是否正确
4. 重新测试连接：
   ```bash
   docker compose exec web php artisan config:clear
   ```

### 问题 5：Redis 连接失败

**错误信息：**
```
Connection refused [tcp://redis:6379]
```

**解决方案：**
```bash
# 检查 Redis 容器状态
docker compose ps redis

# 重启 Redis
docker compose restart redis

# 检查 Redis 日志
docker compose logs redis

# 测试 Redis 连接
docker compose exec redis redis-cli ping
```

---

## 🔒 安全加固

### 1. 使用 HTTPS

```bash
# 安装 Certbot
sudo apt install certbot

# 获取证书
sudo certbot certonly --standalone -d your-domain.com

# 配置 Nginx 反向代理（推荐）
# 参见下一节
```

### 2. 配置 Nginx 反向代理

创建 `/etc/nginx/sites-available/xboard`：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    location / {
        proxy_pass http://127.0.0.1:7001;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

启用配置：
```bash
sudo ln -s /etc/nginx/sites-available/xboard /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 3. 更新 Logto 配置

更新 `.env`：
```env
APP_URL=https://your-domain.com
```

更新 Logto Console 的 Redirect URI：
```
https://your-domain.com/api/v1/passport/auth/logto/callback
```

重启服务：
```bash
docker compose restart
```

### 4. 限制访问

```bash
# 修改 docker-compose.yml
# 将端口绑定到本地
ports:
  - "127.0.0.1:7001:7001"
```

### 5. 定期备份

创建备份脚本 `/root/backup-xboard.sh`：

```bash
#!/bin/bash
BACKUP_DIR="/root/xboard-backups"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# 备份数据库
cp /path/to/Xboard/database/database.sqlite $BACKUP_DIR/db_$DATE.sqlite

# 备份 Redis
cd /path/to/Xboard
./scripts/backup-redis.sh

# 备份配置
cp .env $BACKUP_DIR/env_$DATE

# 清理旧备份（保留 7 天）
find $BACKUP_DIR -mtime +7 -delete

echo "Backup completed: $DATE"
```

添加到 crontab：
```bash
crontab -e

# 每天凌晨 2 点备份
0 2 * * * /root/backup-xboard.sh >> /var/log/xboard-backup.log 2>&1
```

---

## 📊 性能优化

### 1. 调整 Octane 配置

编辑 `config/octane.php`：

```php
'swoole' => [
    'options' => [
        'worker_num' => 4,  // CPU 核心数
        'task_worker_num' => 2,
        'max_request' => 1000,
    ],
],
```

### 2. 优化 Redis

编辑 `docker-compose.yml`：

```yaml
redis:
  image: redis:7-alpine
  command: >
    redis-server
    --unixsocket /data/redis.sock
    --unixsocketperm 777
    --maxmemory 256mb
    --maxmemory-policy allkeys-lru
```

### 3. 启用 OPcache

已在 Docker 镜像中默认启用。

### 4. 监控性能

```bash
# 监控 Redis
./scripts/monitor-redis.sh -v

# 查看容器资源使用
docker stats

# 优化缓存
docker compose exec web php artisan cache:optimize
```

---

## 📚 下一步

安装完成后，您可以：

1. **配置系统设置**
   - 登录管理面板
   - 设置站点名称、描述
   - 配置邮件服务
   - 添加支付方式

2. **自定义 Logto**
   - 添加社交登录（Google、GitHub 等）
   - 启用 MFA
   - 自定义登录页面

3. **部署前端**
   - 参见 [前端集成指南](../../FRONTEND_LOGTO_INTEGRATION.md)

4. **配置监控**
   - 设置 Redis 监控
   - 配置日志告警

---

## 🆘 获取帮助

- **文档**: [完整文档](../../INSTALLATION_GUIDE.md)
- **Telegram**: [XboardOfficial](https://t.me/XboardOfficial)
- **GitHub**: [提交 Issue](https://github.com/ElinksTeam/ElinksBoard/issues)

---

**安装完成！** 🎉

记得立即完成首次登录以确保管理员访问权限。

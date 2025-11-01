# 手动安装指南

## 📋 概述

手动安装适合高级用户和需要完全控制环境的场景。

**适用场景：**
- ✅ 高级用户
- ✅ 自定义环境
- ✅ 性能优化需求
- ✅ 特殊配置要求

**预计时间：** 30-60 分钟

---

## 🎯 系统要求

### 硬件要求
- **CPU**: 2核心+
- **内存**: 2GB+
- **存储**: 20GB+

### 软件要求
- **操作系统**: Ubuntu 20.04+, Debian 10+
- **PHP**: 8.2+
- **MySQL**: 5.7+ / PostgreSQL / SQLite
- **Redis**: 6.0+
- **Nginx**: 1.18+
- **Composer**: 2.0+
- **Node.js**: 18+ (用于前端构建)

---

## 📦 步骤 1：安装系统依赖

### Ubuntu/Debian

```bash
# 更新系统
sudo apt update && sudo apt upgrade -y

# 安装基础工具
sudo apt install -y git curl wget unzip software-properties-common

# 添加 PHP 仓库
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# 安装 PHP 8.2 和扩展
sudo apt install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-sqlite3 \
    php8.2-redis \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-gd \
    php8.2-intl \
    php8.2-opcache

# 安装 MySQL
sudo apt install -y mysql-server

# 安装 Redis
sudo apt install -y redis-server

# 安装 Nginx
sudo apt install -y nginx

# 安装 Composer
curl -sS https://getcomposer.com/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 安装 Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 🚀 步骤 2：配置 MySQL

```bash
# 启动 MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# 安全配置
sudo mysql_secure_installation

# 创建数据库和用户
sudo mysql -u root -p
```

在 MySQL 中执行：

```sql
CREATE DATABASE xboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'xboard'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON xboard.* TO 'xboard'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 🔧 步骤 3：配置 Redis

```bash
# 编辑 Redis 配置
sudo nano /etc/redis/redis.conf
```

修改以下配置：

```conf
# 绑定到本地
bind 127.0.0.1

# 设置密码（可选）
requirepass your_redis_password

# 启用持久化
save 900 1
save 300 10
save 60 10000

# 最大内存
maxmemory 256mb
maxmemory-policy allkeys-lru
```

重启 Redis：

```bash
sudo systemctl restart redis
sudo systemctl enable redis
```

---

## 📥 步骤 4：下载 Xboard

```bash
# 创建目录
sudo mkdir -p /var/www
cd /var/www

# 克隆仓库
sudo git clone https://github.com/cedar2025/Xboard.git xboard
cd xboard

# 设置权限
sudo chown -R www-data:www-data /var/www/xboard
sudo chmod -R 755 /var/www/xboard
sudo chmod -R 775 /var/www/xboard/storage
sudo chmod -R 775 /var/www/xboard/bootstrap/cache
```

---

## ⚙️ 步骤 5：安装依赖

```bash
cd /var/www/xboard

# 安装 PHP 依赖
sudo -u www-data composer install --no-dev --optimize-autoloader

# 复制环境配置
sudo -u www-data cp .env.example .env

# 生成应用密钥
sudo -u www-data php artisan key:generate
```

---

## 🔧 步骤 6：配置环境变量

编辑 `.env` 文件：

```bash
sudo -u www-data nano .env
```

配置以下内容：

```env
APP_NAME=XBoard
APP_ENV=production
APP_KEY=base64:... # 已自动生成
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# 数据库配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xboard
DB_USERNAME=xboard
DB_PASSWORD=your_strong_password

# Redis 配置
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Logto 配置
LOGTO_ENDPOINT=https://your-logto.app
LOGTO_APP_ID=your_app_id
LOGTO_APP_SECRET=your_app_secret
LOGTO_REDIRECT_URI=${APP_URL}/api/v1/passport/auth/logto/callback
LOGTO_POST_LOGOUT_REDIRECT_URI=${APP_URL}
LOGTO_AUTO_CREATE_USER=true
LOGTO_AUTO_UPDATE_USER=true
```

---

## 🗄️ 步骤 7：运行数据库迁移

```bash
cd /var/www/xboard

# 运行迁移
sudo -u www-data php artisan migrate --force

# 优化
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

## 🌐 步骤 8：配置 Nginx

创建 Nginx 配置文件：

```bash
sudo nano /etc/nginx/sites-available/xboard
```

添加以下内容：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/xboard/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

启用站点：

```bash
sudo ln -s /etc/nginx/sites-available/xboard /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔐 步骤 9：配置 SSL（推荐）

```bash
# 安装 Certbot
sudo apt install -y certbot python3-certbot-nginx

# 获取证书
sudo certbot --nginx -d your-domain.com

# 自动续期
sudo certbot renew --dry-run
```

---

## 🚀 步骤 10：配置 Octane

### 安装 Swoole

```bash
sudo pecl install swoole
echo "extension=swoole.so" | sudo tee /etc/php/8.2/mods-available/swoole.ini
sudo phpenmod swoole
```

### 创建 Systemd 服务

创建 `/etc/systemd/system/xboard-octane.service`：

```ini
[Unit]
Description=Xboard Octane Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/xboard
ExecStart=/usr/bin/php /var/www/xboard/artisan octane:start --host=127.0.0.1 --port=8000
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

启动服务：

```bash
sudo systemctl daemon-reload
sudo systemctl start xboard-octane
sudo systemctl enable xboard-octane
```

### 更新 Nginx 配置

修改 `/etc/nginx/sites-available/xboard`：

```nginx
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

重载 Nginx：

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## 📋 步骤 11：配置队列处理

创建 `/etc/systemd/system/xboard-horizon.service`：

```ini
[Unit]
Description=Xboard Horizon
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/xboard
ExecStart=/usr/bin/php /var/www/xboard/artisan horizon
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

启动服务：

```bash
sudo systemctl daemon-reload
sudo systemctl start xboard-horizon
sudo systemctl enable xboard-horizon
```

---

## 🎯 步骤 12：配置 Logto

1. 访问 [Logto Console](https://cloud.logto.io)
2. 创建 Traditional Web Application
3. 配置 Redirect URI：
   ```
   https://your-domain.com/api/v1/passport/auth/logto/callback
   ```
4. 更新 `.env` 中的 Logto 配置
5. 清理缓存：
   ```bash
   sudo -u www-data php artisan config:clear
   ```

---

## 🔐 步骤 13：完成首次登录

1. 访问：`https://your-domain.com`
2. 点击"使用 Logto 登录"
3. 完成认证
4. 获得管理员权限

---

## 🔧 常用管理命令

### 服务管理

```bash
# Octane
sudo systemctl status xboard-octane
sudo systemctl restart xboard-octane
sudo systemctl stop xboard-octane

# Horizon
sudo systemctl status xboard-horizon
sudo systemctl restart xboard-horizon
sudo systemctl stop xboard-horizon

# Nginx
sudo systemctl status nginx
sudo systemctl reload nginx

# PHP-FPM
sudo systemctl status php8.2-fpm
sudo systemctl restart php8.2-fpm

# Redis
sudo systemctl status redis
sudo systemctl restart redis

# MySQL
sudo systemctl status mysql
sudo systemctl restart mysql
```

### 应用管理

```bash
cd /var/www/xboard

# 清理缓存
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# 优化
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# 查看日志
tail -f storage/logs/laravel.log

# 运行队列
sudo -u www-data php artisan queue:work

# 优化缓存
sudo -u www-data php artisan cache:optimize
```

---

## 📊 监控和维护

### 日志位置

- **Laravel**: `/var/www/xboard/storage/logs/laravel.log`
- **Nginx**: `/var/log/nginx/access.log`, `/var/log/nginx/error.log`
- **PHP-FPM**: `/var/log/php8.2-fpm.log`
- **Redis**: `/var/log/redis/redis-server.log`
- **MySQL**: `/var/log/mysql/error.log`

### 定期维护

```bash
# 备份数据库
mysqldump -u xboard -p xboard > backup_$(date +%Y%m%d).sql

# 备份 Redis
/var/www/xboard/scripts/backup-redis.sh

# 清理日志
sudo find /var/www/xboard/storage/logs -name "*.log" -mtime +7 -delete

# 更新系统
sudo apt update && sudo apt upgrade -y
```

---

## 🐛 故障排查

### 查看服务状态

```bash
# 检查所有服务
sudo systemctl status xboard-octane
sudo systemctl status xboard-horizon
sudo systemctl status nginx
sudo systemctl status redis
sudo systemctl status mysql
```

### 查看日志

```bash
# Octane 日志
sudo journalctl -u xboard-octane -f

# Horizon 日志
sudo journalctl -u xboard-horizon -f

# Nginx 日志
sudo tail -f /var/log/nginx/error.log

# Laravel 日志
sudo tail -f /var/www/xboard/storage/logs/laravel.log
```

### 常见问题

1. **权限问题**
   ```bash
   sudo chown -R www-data:www-data /var/www/xboard
   sudo chmod -R 775 /var/www/xboard/storage
   sudo chmod -R 775 /var/www/xboard/bootstrap/cache
   ```

2. **Octane 无法启动**
   ```bash
   # 检查端口占用
   sudo lsof -i :8000
   
   # 重启服务
   sudo systemctl restart xboard-octane
   ```

3. **Redis 连接失败**
   ```bash
   # 测试连接
   redis-cli ping
   
   # 检查配置
   cat /etc/redis/redis.conf | grep bind
   ```

---

## 🔒 安全加固

1. **配置防火墙**
   ```bash
   sudo ufw allow 22
   sudo ufw allow 80
   sudo ufw allow 443
   sudo ufw enable
   ```

2. **限制 PHP 执行**
   ```bash
   sudo nano /etc/php/8.2/fpm/php.ini
   # disable_functions = exec,passthru,shell_exec,system
   ```

3. **配置 fail2ban**
   ```bash
   sudo apt install -y fail2ban
   sudo systemctl enable fail2ban
   ```

4. **定期更新**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

---

## 📚 相关资源

- [Docker Compose 安装](docker-compose.md)
- [完整安装指南](../../INSTALLATION_GUIDE.md)
- [性能优化](../../en/development/performance.md)

---

**安装完成！** 🎉

您现在拥有完全控制的 Xboard 环境。

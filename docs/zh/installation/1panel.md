# 1Panel 安装指南

## 📋 概述

1Panel 是一个现代化的 Linux 服务器管理面板，提供可视化的 Docker 管理界面。

**适用场景：**
- ✅ 喜欢可视化操作
- ✅ 已安装 1Panel
- ✅ 需要管理多个应用
- ✅ 中等技术水平

**预计时间：** 15-20 分钟

---

## 🎯 系统要求

### 硬件要求
- **CPU**: 2核心+
- **内存**: 2GB+
- **存储**: 20GB+

### 软件要求
- **操作系统**: Ubuntu 20.04+, Debian 10+, CentOS 8+
- **1Panel**: 最新版本

---

## 📦 步骤 1：安装 1Panel

如果您还没有安装 1Panel：

```bash
curl -sSL https://resource.fit2cloud.com/1panel/package/quick_start.sh -o quick_start.sh && bash quick_start.sh
```

安装完成后，访问：`http://服务器IP:端口`

---

## 🚀 步骤 2：在 1Panel 中安装 Docker

1. 登录 1Panel 面板
2. 进入 **容器** → **设置**
3. 点击 **安装 Docker**
4. 等待安装完成

---

## 📥 步骤 3：创建 Xboard 应用

### 3.1 创建应用目录

在 1Panel 中：

1. 进入 **文件** → **文件管理**
2. 创建目录：`/opt/xboard`
3. 进入该目录

### 3.2 上传文件

**方式一：使用 Git（推荐）**

在 1Panel 终端中：

```bash
cd /opt
git clone -b compose --depth 1 https://github.com/ElinksTeam/ElinksBoard.git xboard
cd ElinksBoard
```

**方式二：手动上传**

1. 下載 ElinksBoard 到本地
2. 使用 1Panel 文件管理上传
3. 解压到 `/opt/xboard`

---

## ⚙️ 步骤 4：配置环境

### 4.1 复制配置文件

在 1Panel 终端中：

```bash
cd /opt/xboard
cp compose.sample.yaml docker-compose.yml
cp .env.example .env
```

### 4.2 编辑配置

在 1Panel 文件管理中：

1. 打开 `docker-compose.yml`
2. 根据需要修改端口（可选）
3. 保存文件

---

## 🔧 步骤 5：运行安装向导

在 1Panel 终端中：

```bash
cd /opt/xboard

# 使用 SQLite（推荐）
docker compose run -it --rm \
    -e ENABLE_SQLITE=true \
    -e ENABLE_REDIS=true \
    web # Installation wizard removed - configure via .env and admin panel
```

按照向导完成配置：
1. 数据库配置
2. Redis 配置
3. **Logto 配置**（必需）

---

## 🎯 步骤 6：配置 Logto Console

在启动服务前，配置 Logto：

1. 访问 [Logto Console](https://cloud.logto.io)
2. 添加 Redirect URI：
   ```
   http://your-domain.com/api/v1/passport/auth/logto/callback
   ```
3. 添加 Post Logout URI：
   ```
   http://your-domain.com
   ```

---

## 🚀 步骤 7：在 1Panel 中创建编排

### 7.1 创建编排

1. 进入 **容器** → **编排**
2. 点击 **创建编排**
3. 填写信息：
   - **名称**: `xboard`
   - **路径**: `/opt/xboard`
   - **描述**: `Xboard 面板系统`

### 7.2 启动服务

1. 在编排列表中找到 `xboard`
2. 点击 **启动**
3. 等待所有容器启动

### 7.3 查看状态

在编排详情中查看：
- ✅ xboard-web-1 (运行中)
- ✅ xboard-horizon-1 (运行中)
- ✅ xboard-redis-1 (运行中)

---

## 🔐 步骤 8：完成首次登录

1. 访问：`http://服务器IP:7001`
2. 点击"使用 Logto 登录"
3. 完成认证
4. 获得管理员权限

---

## 🔧 1Panel 管理操作

### 查看日志

1. 进入 **容器** → **编排**
2. 点击 `xboard`
3. 选择容器
4. 点击 **日志**

### 重启服务

1. 进入编排详情
2. 点击 **重启**

### 进入容器

1. 选择容器
2. 点击 **终端**
3. 执行命令

### 查看资源使用

1. 进入 **容器** → **容器**
2. 查看 CPU、内存使用情况

---

## 🌐 步骤 9：配置域名（可选）

### 9.1 在 1Panel 中配置反向代理

1. 进入 **网站** → **网站**
2. 点击 **创建网站**
3. 选择 **反向代理**
4. 填写信息：
   - **域名**: `your-domain.com`
   - **代理地址**: `http://127.0.0.1:7001`
5. 启用 **HTTPS**（推荐）
6. 保存

### 9.2 更新配置

编辑 `/opt/xboard/.env`：

```env
APP_URL=https://your-domain.com
```

更新 Logto Console 的 Redirect URI：
```
https://your-domain.com/api/v1/passport/auth/logto/callback
```

重启服务：
```bash
cd /opt/xboard
docker compose restart
```

---

## 📊 监控和维护

### 在 1Panel 中监控

1. **容器监控**
   - 进入 **容器** → **容器**
   - 查看资源使用情况

2. **日志查看**
   - 实时查看容器日志
   - 下载日志文件

3. **性能统计**
   - CPU 使用率
   - 内存使用率
   - 网络流量

### 定期维护

1. **备份数据**
   ```bash
   cd /opt/xboard
   ./scripts/backup-redis.sh
   cp database/database.sqlite database/database.sqlite.backup
   ```

2. **更新系统**
   ```bash
   cd /opt/xboard
   git pull
   docker compose pull
   docker compose up -d
   ```

3. **清理日志**
   - 在 1Panel 中定期清理容器日志

---

## 🐛 故障排查

### 问题 1：容器无法启动

**解决方案：**
1. 在 1Panel 中查看容器日志
2. 检查端口是否被占用
3. 检查配置文件是否正确

### 问题 2：无法访问网站

**解决方案：**
1. 检查防火墙规则
2. 在 1Panel 中检查端口映射
3. 查看容器状态

### 问题 3：Logto 认证失败

**解决方案：**
1. 检查 Logto Console 配置
2. 验证 Redirect URI
3. 查看应用日志

---

## 🔒 安全建议

1. **修改默认端口**
   - 在 docker-compose.yml 中修改端口

2. **启用 HTTPS**
   - 使用 1Panel 的 SSL 证书管理

3. **限制访问**
   - 配置防火墙规则
   - 使用 1Panel 的安全设置

4. **定期备份**
   - 使用 1Panel 的备份功能
   - 定期下载备份到本地

---

## 📚 相关资源

- [1Panel 官方文档](https://1panel.cn/docs/)
- [Docker Compose 安装指南](docker-compose.md)
- [完整安装指南](../../INSTALLATION_GUIDE.md)

---

**安装完成！** 🎉

享受 1Panel 带来的便捷管理体验。

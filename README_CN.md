# ElinksBoard 

<div align="center">

[![Telegram](https://img.shields.io/badge/Telegram-频道-blue)](https://t.me/XboardOfficial)
![PHP](https://img.shields.io/badge/PHP-8.2+-green.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-blue.svg)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

**现代化的面板系统 | 基于 Laravel 11 | 集成 Logto 认证**

[English](README.md) | 简体中文 | [繁體中文](README_TW.md)

</div>

---

## 📖 简介

ElinksBoard 是一个基于 Laravel 11 构建的现代化面板系统，专注于提供简洁高效的用户体验。最新版本已完全集成 Logto 认证系统，提供企业级的身份认证解决方案。

### 🎯 核心特性

- 🚀 **高性能架构** - Laravel 11 + Octane，显著提升性能
- 🔐 **Logto 认证** - 企业级 OAuth2/OIDC 认证，支持 SSO、MFA、社交登录
- 🎨 **现代化界面** - React + Shadcn UI 管理后台，Vue3 + TypeScript 用户前端
- 📱 **响应式设计** - 完美适配各种设备
- 🐳 **容器化部署** - 开箱即用的 Docker 部署方案
- 💾 **Redis 缓存** - 完整的缓存管理系统，包含备份、监控、优化工具
- 🎯 **优化架构** - 更好的可维护性和扩展性

---

## 🆕 最新更新

### v2.0 - Logto 认证集成

- ✅ **完全移除传统登录** - 所有用户通过 Logto 认证
- ✅ **首次用户自动成为管理员** - 安装后首次登录自动获得管理员权限
- ✅ **管理面板配置** - 可在后台直接管理 Logto 设置
- ✅ **自动用户同步** - 支持自动创建和更新用户信息
- ✅ **完整文档** - 提供详细的安装和集成指南

### Redis 缓存管理

- ✅ **自动备份脚本** - 定时备份 Redis 数据
- ✅ **性能监控** - 实时监控缓存性能和告警
- ✅ **优化工具** - 缓存清理和优化命令
- ✅ **Prometheus 集成** - 支持 Prometheus 监控

---

## 🚀 快速开始

### 方式一：Docker Compose（推荐）

这是最简单快速的安装方式，适合大多数用户。

```bash
# 1. 克隆仓库
git clone --depth 1 https://github.com/ElinksTeam/ElinksBoard
cd ElinksBoard

# 2. 复制 Docker Compose 配置
cp compose.sample.yaml docker-compose.yml

# 3. 启动服务
docker compose up -d
```

**访问您的站点：** `http://服务器IP:7001`

⚠️ **重要：安装完成后立即完成首次登录以获取管理员权限！**

---

## 📋 详细安装指南

我们提供多种安装方式，选择最适合您的：

### 🐳 Docker 部署（推荐）

| 方式 | 难度 | 适用场景 | 文档链接 |
|------|------|----------|----------|
| **Docker Compose** | ⭐ 简单 | 新手、快速部署 | [查看教程](docs/zh/installation/docker-compose.md) |
| **1Panel** | ⭐⭐ 中等 | 可视化管理 | [查看教程](docs/zh/installation/1panel.md) |
| **aaPanel + Docker** | ⭐⭐ 中等 | 面板用户 | [查看教程](docs/zh/installation/aapanel-docker.md) |

### 🖥️ 传统部署

| 方式 | 难度 | 适用场景 | 文档链接 |
|------|------|----------|----------|
| **aaPanel** | ⭐⭐⭐ 复杂 | 传统虚拟主机 | [查看教程](docs/zh/installation/aapanel.md) |
| **手动安装** | ⭐⭐⭐⭐ 高级 | 自定义环境 | [查看教程](INSTALLATION_GUIDE.md) |

### 📦 一键安装脚本

```bash
# 即将推出
curl -sSL https://raw.githubusercontent.com/ElinksTeam/ElinksBoard/master/install.sh | bash
```

---

## 🔐 Logto 认证配置

### 快速配置（5分钟）

1. **注册 Logto 账号**
   - 访问 [Logto Cloud](https://cloud.logto.io) 或使用自托管实例
   - 创建一个新的 **Traditional Web Application**

2. **获取凭据**
   - 复制 **Endpoint**、**App ID**、**App Secret**

3. **配置 Redirect URI**
   ```
   http://your-domain.com/api/v1/passport/auth/logto/callback
   ```

4. **完成首次登录** ⚠️ **关键步骤**
   - 安装后立即访问您的站点
   - 点击"使用 Logto 登录"
   - 首次登录的用户自动成为管理员

**详细文档：**
- [Logto 快速设置](LOGTO_SETUP.md)
- [完整集成指南](docs/LOGTO_INTEGRATION.md)
- [前端集成指南](docs/FRONTEND_LOGTO_INTEGRATION.md)

---

## 📚 完整文档

### 🔧 安装与配置

- [安装指南](INSTALLATION_GUIDE.md) - 完整的安装步骤和配置说明
- [Logto 设置](LOGTO_SETUP.md) - Logto 认证快速设置指南
- [Logto 变更说明](LOGTO_CHANGES.md) - Logto 集成的详细变更
- [实施状态](IMPLEMENTATION_STATUS.md) - 当前实施进度和功能状态

### 🔄 迁移指南

从其他系统迁移到 ElinksBoard：

- [从 v2board dev 迁移](docs/en/migration/v2board-dev.md)
- [从 v2board 1.7.4 迁移](docs/en/migration/v2board-1.7.4.md)
- [从 v2board 1.7.3 迁移](docs/en/migration/v2board-1.7.3.md)
- [配置迁移](docs/en/migration/config.md)

### 💾 Redis 缓存管理

- [Redis 缓存指南](docs/REDIS_CACHE_GUIDE.md) - 完整的缓存管理文档
- [Redis 快速参考](docs/REDIS_QUICK_REFERENCE.md) - 常用命令和操作
- [实施总结](REDIS_IMPLEMENTATION_SUMMARY.md) - Redis 功能实施详情

### 🐳 Docker 文档

- [Docker 构建指南](DOCKER_BUILD.md) - 完整的 Docker 镜像构建和推送文档
- [Docker 快速入门](DOCKER_QUICKSTART.md) - Docker 操作快速参考

### 🛠️ 开发文档

- [插件开发指南](docs/en/development/plugin-development-guide.md) - 开发 ElinksBoard 插件
- [性能优化](docs/en/development/performance.md) - 性能优化建议
- [设备限制](docs/en/development/device-limit.md) - 设备限制功能

### 🏗️ 架构文档

- [数据库去中心化分析](docs/DATABASE_DECENTRALIZATION_ANALYSIS.md)
- [混合架构评估](docs/HYBRID_ARCHITECTURE_EVALUATION.md)

---

## 🛠️ 技术栈

### 后端
- **框架**: Laravel 11 + Octane
- **数据库**: MySQL 5.7+ / PostgreSQL / SQLite
- **缓存**: Redis
- **认证**: Logto (OAuth2/OIDC)
- **队列**: Redis Queue

### 前端
- **管理后台**: React + Shadcn UI + TailwindCSS
- **用户前端**: Vue3 + TypeScript + NaiveUI
- **构建工具**: Vite

### 部署
- **容器化**: Docker + Docker Compose
- **Web服务器**: Nginx / Caddy
- **进程管理**: Supervisor / Systemd

---

## 📷 界面预览

### 管理后台
![管理后台预览](./docs/images/admin.png)

### 用户前端
![用户前端预览](./docs/images/user.png)

---

## 🔧 常用命令

### Docker 环境

```bash
# 查看日志
docker compose logs -f web

# 重启服务
docker compose restart

# 进入容器
docker compose exec web bash

# 运行 Artisan 命令
docker compose exec web php artisan [command]

# 清理缓存
docker compose exec web php artisan cache:clear
docker compose exec web php artisan config:clear
```

### Redis 缓存管理

```bash
# 监控 Redis
./scripts/monitor-redis.sh

# 备份 Redis
./scripts/backup-redis.sh

# 恢复备份
./scripts/restore-redis.sh latest

# 优化缓存
docker compose exec web php artisan cache:optimize
```

### Logto 管理

```bash
# 测试 Logto 连接
# 登录管理面板 -> Logto 设置 -> 测试连接

# 查看 Logto 配置
docker compose exec web php artisan config:show logto

# 查看用户统计
# 登录管理面板 -> Logto 设置 -> 用户统计
```

---

## ⚙️ 系统要求

### 最低配置
- **CPU**: 1核
- **内存**: 1GB
- **存储**: 10GB
- **系统**: Linux (推荐 Ubuntu 20.04+)

### 推荐配置
- **CPU**: 2核+
- **内存**: 2GB+
- **存储**: 20GB+
- **系统**: Ubuntu 22.04 LTS

### 软件要求
- **PHP**: 8.2+
- **MySQL**: 5.7+ / PostgreSQL / SQLite
- **Redis**: 6.0+
- **Docker**: 20.10+ (Docker 部署)
- **Docker Compose**: 2.0+ (Docker 部署)

---

## 🔔 重要提示

### 安全建议

1. **立即完成首次登录**
   - 安装后立即登录以获取管理员权限
   - 首次登录的用户自动成为管理员
   - 后续用户为普通用户

2. **保护管理员路径**
   - 管理员路径在安装时随机生成
   - 请妥善保管，不要泄露

3. **使用 HTTPS**
   - 生产环境务必配置 SSL 证书
   - 更新 Logto 回调 URI 为 HTTPS

4. **定期备份**
   - 定期备份数据库
   - 使用 Redis 备份脚本备份缓存
   - 保存 `.env` 配置文件

### 修改管理路径后需要重启

```bash
# Docker 环境
docker compose restart

# aaPanel 环境
# 重启 Octane 守护进程
```

---

## 🔄 升级指南

### 从旧版本升级

⚠️ **重要提示**：本版本涉及重大变更，升级前请：

1. **备份数据库**
   ```bash
   # MySQL
   mysqldump -u root -p elinksboard > backup.sql
   
   # SQLite
   cp database/database.sqlite database/database.sqlite.backup
   ```

2. **备份 Redis**
   ```bash
   ./scripts/backup-redis.sh
   ```

3. **备份配置文件**
   ```bash
   cp .env .env.backup
   ```

4. **查看升级文档**
   - 严格按照升级文档操作
   - 注意：升级和迁移是不同的过程

5. **测试环境验证**
   - 建议先在测试环境验证
   - 确认无误后再升级生产环境

---

## 🤝 贡献指南

欢迎提交 Issue 和 Pull Request 来帮助改进项目！

### 如何贡献

1. Fork 本仓库
2. 创建您的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交您的更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启一个 Pull Request

### 代码规范

- 遵循 PSR-12 编码规范
- 编写清晰的提交信息
- 添加必要的测试
- 更新相关文档

---

## 🌟 维护说明

本项目目前处于轻度维护状态。我们将：

- ✅ 修复关键 bug 和安全问题
- ✅ 审查和合并重要的 Pull Request
- ✅ 提供必要的兼容性更新
- ⚠️ 新功能开发可能受限

---

## ⚠️ 免责声明

本项目仅供学习和交流使用。使用本项目所产生的任何后果由使用者自行承担。

---

## 📞 获取帮助

### 文档资源

- [安装指南](INSTALLATION_GUIDE.md)
- [Logto 设置](LOGTO_SETUP.md)
- [Redis 缓存指南](docs/REDIS_CACHE_GUIDE.md)
- [常见问题](docs/FAQ.md)（即将推出）

### 社区支持

- **Telegram 频道**: [XboardOfficial](https://t.me/XboardOfficial)
- **GitHub Issues**: [提交问题](https://github.com/ElinksTeam/ElinksBoard/issues)
- **GitHub Discussions**: [参与讨论](https://github.com/ElinksTeam/ElinksBoard/discussions)

### 日志调试

```bash
# 查看 Laravel 日志
tail -f storage/logs/laravel.log

# 查看 Docker 日志
docker compose logs -f

# 启用调试模式
# 编辑 .env
APP_DEBUG=true
LOG_LEVEL=debug
```

---

## 📈 Star 历史

[![Stargazers over time](https://starchart.cc/ElinksTeam/ElinksBoard.svg)](https://starchart.cc/ElinksTeam/ElinksBoard)

---

## 📄 许可证

本项目采用 [MIT 许可证](LICENSE)。

---

## 🙏 致谢

感谢所有为本项目做出贡献的开发者！

特别感谢：
- [Laravel](https://laravel.com) - 优雅的 PHP 框架
- [Logto](https://logto.io) - 现代化的身份认证服务
- [Vue.js](https://vuejs.org) - 渐进式 JavaScript 框架
- [React](https://react.dev) - 用于构建用户界面的 JavaScript 库

---

<div align="center">

**如果这个项目对您有帮助，请给我们一个 ⭐️**

Made with ❤️ by ElinksBoard Team

</div>

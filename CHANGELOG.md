# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-11-01

### 🎉 重大更新

这是 Xboard 的重大版本更新，引入了企业级认证系统和完整的缓存管理方案。

### ✨ 新增功能

#### Logto 认证集成
- **完整的 OAuth2/OIDC 认证** - 集成 Logto 企业级认证系统
- **首次用户自动成为管理员** - 安装后首次登录自动获得管理员权限
- **管理面板配置** - 可在后台直接管理 Logto 设置
- **自动用户同步** - 支持自动创建和更新用户信息
- **社交登录支持** - 支持 Google、GitHub 等社交登录
- **多因素认证** - 支持 MFA 增强安全性
- **单点登录** - 支持 SSO 企业级单点登录

相关提交：
- `c29436c` feat(auth): add Logto authentication integration

#### Redis 缓存管理系统
- **自动备份脚本** - 定时备份 Redis 数据，支持自定义保留天数
- **性能监控工具** - 实时监控缓存性能，支持告警
- **优化命令** - 缓存清理和优化 Artisan 命令
- **Prometheus 集成** - 支持 Prometheus 监控和告警
- **完整文档** - 详细的使用指南和快速参考

相关提交：
- `588cf07` feat(cache): add comprehensive Redis cache management system

#### CI/CD 工作流
- **CI 工作流** - 代码质量检查、多版本测试、安全审计
- **Security Scan** - 依赖扫描、代码扫描、镜像扫描
- **Release 工作流** - 自动创建 Release、生成 Changelog
- **Docs 工作流** - 自动部署文档到 GitHub Pages

相关提交：
- `0a62459` ci: add comprehensive GitHub Actions workflows

#### 管理功能增强
- **Custom ID/Original ID 显示** - 支持复制操作
- **插件配置优化** - 自动解码 JSON 配置值

相关提交：
- `213aff3` feat(admin): add Custom ID/Original ID display with copy actions
- `e3c746d` feat(plugin): auto-decode JSON config values by type in PluginManager

#### Telegram 通知优化
- **通知格式改进** - 更清晰的通知格式
- **流量描述修正** - 修正流量使用描述

相关提交：
- `0798b37` feat(telegram plugin): improve Telegram notification formatting
- `7377460` fix(telegram): correct traffic usage description in notification

### 📚 文档更新

#### 中文文档完善
- **中文 README** - 详细的项目介绍和快速开始
- **安装指南** - Docker Compose、1Panel、手动安装
- **Logto 文档** - 快速设置、集成指南、前端集成
- **Redis 文档** - 缓存指南、快速参考、实施总结
- **工作流文档** - 设计方案、测试指南、使用说明

相关提交：
- `17d8b21` docs: add comprehensive Chinese documentation and installation guides
- `ee3d7db` docs: translate all documentation to Chinese

### 🔧 改进

- **性能优化** - 优化缓存策略，提升响应速度
- **安全增强** - 企业级认证，多因素认证支持
- **开发体验** - 完整的 CI/CD 流程，自动化测试和部署
- **文档完善** - 中英文文档，详细的安装和使用指南

### ⚠️ 破坏性变更

#### 认证系统变更
- **移除传统登录** - 不再支持邮箱/密码登录
- **必需 Logto 配置** - 安装时必须配置 Logto
- **首次登录重要性** - 首次登录的用户自动成为管理员

#### 迁移指南

如果您从旧版本升级，请注意：

1. **备份数据**
   ```bash
   # 备份数据库
   mysqldump -u root -p xboard > backup.sql
   
   # 备份 Redis
   ./scripts/backup-redis.sh
   
   # 备份配置
   cp .env .env.backup
   ```

2. **配置 Logto**
   - 注册 Logto 账号
   - 创建 Traditional Web Application
   - 获取 Endpoint、App ID、App Secret

3. **运行迁移**
   ```bash
   php artisan migrate --force
   ```

4. **更新配置**
   - 在 `.env` 中添加 Logto 配置
   - 配置 Logto Console 的 Redirect URI

5. **完成首次登录**
   - 立即使用您的账号登录
   - 获取管理员权限

详细迁移指南请参考：[INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)

### 📦 安装

#### Docker Compose（推荐）

```bash
# 克隆仓库
git clone -b compose --depth 1 https://github.com/cedar2025/Xboard
cd Xboard

# 运行安装向导
docker compose run -it --rm \
    -e ENABLE_SQLITE=true \
    -e ENABLE_REDIS=true \
    web php artisan xboard:install

# 启动服务
docker compose up -d
```

#### Docker 镜像

```bash
docker pull ghcr.io/cedar2025/xboard:2.0.0
```

### 🔗 相关链接

- [完整文档](README_CN.md)
- [安装指南](INSTALLATION_GUIDE.md)
- [Logto 设置](LOGTO_SETUP.md)
- [Redis 缓存指南](docs/REDIS_CACHE_GUIDE.md)
- [工作流文档](docs/WORKFLOW_DESIGN.md)

### 🙏 致谢

感谢所有为本版本做出贡献的开发者！

特别感谢：
- [Logto](https://logto.io) - 提供优秀的认证服务
- [Laravel](https://laravel.com) - 优雅的 PHP 框架
- 所有提交 Issue 和 PR 的贡献者

---

## [1.0.0] - 2024-XX-XX

### 初始版本

- 基于 Laravel 11 的面板系统
- React + Shadcn UI 管理后台
- Vue3 + TypeScript 用户前端
- Docker 部署支持
- 基础功能实现

---

**完整变更历史**: https://github.com/cedar2025/Xboard/commits/master

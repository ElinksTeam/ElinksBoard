# Xboard 工作流设计方案

## 📋 概述

本文档描述 Xboard 项目的完整 CI/CD 工作流设计，包括代码质量检查、自动化测试、Docker 镜像构建和部署流程。

---

## 🎯 设计目标

### 核心目标
1. **代码质量保证** - 自动化代码检查和测试
2. **快速反馈** - PR 提交后快速获得检查结果
3. **自动化部署** - 主分支自动构建和发布 Docker 镜像
4. **安全性** - 依赖安全扫描和镜像签名
5. **可维护性** - 清晰的工作流结构和文档

### 性能目标
- PR 检查：< 5 分钟
- Docker 构建：< 15 分钟
- 完整测试：< 10 分钟

---

## 🏗️ 工作流架构

### 工作流列表

| 工作流 | 触发条件 | 用途 | 执行时间 |
|--------|----------|------|----------|
| **CI** | PR, Push | 代码质量检查、测试 | ~5 分钟 |
| **Docker Build** | Push to master | 构建和发布镜像 | ~15 分钟 |
| **Security Scan** | 每日、PR | 依赖安全扫描 | ~3 分钟 |
| **Release** | Tag push | 创建发布版本 | ~2 分钟 |
| **Docs** | Docs 变更 | 部署文档站点 | ~3 分钟 |

---

## 📝 详细设计

### 1. CI 工作流 (ci.yml)

**触发条件：**
- Pull Request 到 master/dev 分支
- Push 到 master/dev 分支

**执行步骤：**

```yaml
jobs:
  code-quality:
    - PHP 语法检查
    - PHPStan 静态分析
    - PHP CS Fixer 代码风格检查
    - Composer 依赖验证
  
  tests:
    - 单元测试 (PHPUnit)
    - 功能测试
    - 集成测试
    - 代码覆盖率报告
  
  security:
    - Composer 安全审计
    - 依赖漏洞扫描
```

**矩阵测试：**
- PHP 版本：8.2, 8.3
- 数据库：MySQL 5.7, MySQL 8.0, PostgreSQL, SQLite

---

### 2. Docker Build 工作流 (docker-publish.yml)

**触发条件：**
- Push 到 master 分支
- 手动触发

**执行步骤：**

```yaml
jobs:
  build:
    - 多架构构建 (amd64, arm64)
    - 镜像优化和缓存
    - 推送到 GHCR
    - 镜像签名 (Cosign)
    - 生成 SBOM
  
  tags:
    - latest (master 分支)
    - new (master 分支)
    - {branch-name}
    - {git-sha}
    - {version}
```

**优化策略：**
- 使用 BuildKit 缓存
- 多阶段构建
- 层缓存优化

---

### 3. Security Scan 工作流 (security.yml)

**触发条件：**
- 每日定时 (UTC 00:00)
- Pull Request
- 手动触发

**执行步骤：**

```yaml
jobs:
  dependency-scan:
    - Composer 依赖审计
    - npm 依赖审计
    - 已知漏洞检查
  
  code-scan:
    - SAST 扫描
    - 敏感信息检测
    - 配置安全检查
  
  docker-scan:
    - 镜像漏洞扫描 (Trivy)
    - 基础镜像检查
```

---

### 4. Release 工作流 (release.yml)

**触发条件：**
- Tag push (v*.*.*)

**执行步骤：**

```yaml
jobs:
  release:
    - 生成 Changelog
    - 创建 GitHub Release
    - 上传构建产物
    - 发送通知
```

---

### 5. Docs 工作流 (docs.yml)

**触发条件：**
- docs/ 目录变更
- README 变更

**执行步骤：**

```yaml
jobs:
  deploy:
    - 构建文档站点
    - 部署到 GitHub Pages
    - 更新文档索引
```

---

## 🔧 实施细节

### 环境变量和 Secrets

**必需的 Secrets：**
```yaml
GITHUB_TOKEN          # 自动提供
DOCKER_USERNAME       # Docker Hub 用户名（可选）
DOCKER_PASSWORD       # Docker Hub 密码（可选）
CODECOV_TOKEN         # Codecov 令牌（可选）
```

**环境变量：**
```yaml
PHP_VERSION: 8.2
NODE_VERSION: 18
REGISTRY: ghcr.io
```

---

### 缓存策略

**Composer 缓存：**
```yaml
- uses: actions/cache@v3
  with:
    path: vendor
    key: composer-${{ hashFiles('composer.lock') }}
```

**Docker 缓存：**
```yaml
cache-from: type=gha
cache-to: type=gha,mode=max
```

**npm 缓存：**
```yaml
- uses: actions/cache@v3
  with:
    path: node_modules
    key: npm-${{ hashFiles('package-lock.json') }}
```

---

### 并行执行

**Job 依赖关系：**
```
CI 工作流:
  code-quality ─┐
  tests ────────┼─→ report
  security ─────┘

Docker 工作流:
  build ─→ scan ─→ sign ─→ push
```

---

## 📊 质量门禁

### PR 合并要求

**必须通过：**
- ✅ 所有 CI 检查通过
- ✅ 代码覆盖率 ≥ 70%
- ✅ PHPStan Level 5 无错误
- ✅ 无安全漏洞
- ✅ 至少 1 个 Reviewer 批准

**可选：**
- 📝 更新 CHANGELOG
- 📝 更新文档
- 🧪 添加测试

---

## 🚀 部署流程

### 开发流程

```
1. 创建功能分支
   git checkout -b feature/xxx

2. 开发和提交
   git commit -m "feat: xxx"

3. 推送并创建 PR
   git push origin feature/xxx

4. CI 自动运行
   - 代码检查
   - 运行测试
   - 安全扫描

5. Code Review
   - 至少 1 个 Reviewer

6. 合并到 master
   - 自动构建 Docker 镜像
   - 推送到 GHCR
   - 更新 latest 标签
```

### 发布流程

```
1. 更新版本号
   - 更新 CHANGELOG
   - 更新版本文件

2. 创建 Tag
   git tag -a v1.0.0 -m "Release v1.0.0"
   git push origin v1.0.0

3. 自动发布
   - 创建 GitHub Release
   - 生成 Release Notes
   - 上传构建产物

4. 通知
   - Telegram 通知
   - 邮件通知
```

---

## 🔍 监控和通知

### 失败通知

**通知渠道：**
- GitHub 通知
- Telegram Bot（可选）
- Email（可选）

**通知内容：**
- 工作流名称
- 失败原因
- 日志链接
- 提交信息

### 成功通知

**发布成功：**
- 新版本号
- 更新内容
- 下载链接

---

## 📈 性能优化

### 构建优化

1. **并行执行**
   - 多个 Job 并行运行
   - 矩阵测试并行

2. **缓存利用**
   - Composer 依赖缓存
   - Docker 层缓存
   - npm 依赖缓存

3. **增量构建**
   - 只构建变更的部分
   - 跳过不必要的步骤

### 资源优化

1. **Runner 选择**
   - 使用 ubuntu-latest
   - 考虑自托管 Runner

2. **并发限制**
   - 限制同时运行的工作流
   - 取消过时的运行

---

## 🛡️ 安全最佳实践

### 1. Secrets 管理

- ✅ 使用 GitHub Secrets
- ✅ 最小权限原则
- ✅ 定期轮换密钥
- ❌ 不在日志中输出 Secrets

### 2. 依赖安全

- ✅ 定期更新依赖
- ✅ 使用 Dependabot
- ✅ 安全扫描
- ✅ 锁定版本

### 3. 镜像安全

- ✅ 使用官方基础镜像
- ✅ 最小化镜像大小
- ✅ 漏洞扫描
- ✅ 镜像签名

### 4. 代码安全

- ✅ SAST 扫描
- ✅ 敏感信息检测
- ✅ 代码审查
- ✅ 安全编码规范

---

## 📚 工作流文件结构

```
.github/
├── workflows/
│   ├── ci.yml              # CI 检查和测试
│   ├── docker-publish.yml  # Docker 构建和发布
│   ├── security.yml        # 安全扫描
│   ├── release.yml         # 发布管理
│   └── docs.yml            # 文档部署
├── actions/                # 自定义 Actions
│   ├── setup-php/
│   └── setup-node/
└── CODEOWNERS             # 代码所有者
```

---

## 🔄 迁移计划

### 阶段 1：基础 CI（第 1 周）
- ✅ 创建 CI 工作流
- ✅ 添加代码质量检查
- ✅ 配置基础测试

### 阶段 2：完善测试（第 2 周）
- ✅ 添加单元测试
- ✅ 添加集成测试
- ✅ 配置代码覆盖率

### 阶段 3：安全扫描（第 3 周）
- ✅ 添加依赖扫描
- ✅ 添加镜像扫描
- ✅ 配置安全策略

### 阶段 4：优化和监控（第 4 周）
- ✅ 性能优化
- ✅ 添加监控
- ✅ 完善文档

---

## 📖 相关文档

- [GitHub Actions 文档](https://docs.github.com/actions)
- [Docker 最佳实践](https://docs.docker.com/develop/dev-best-practices/)
- [Laravel 测试](https://laravel.com/docs/testing)
- [PHPStan 文档](https://phpstan.org/)

---

## 🤝 贡献指南

### 修改工作流

1. 在功能分支中修改
2. 测试工作流
3. 提交 PR
4. Code Review
5. 合并到 master

### 添加新工作流

1. 创建工作流文件
2. 添加文档说明
3. 测试验证
4. 提交 PR

---

## ❓ 常见问题

### Q: 如何跳过 CI 检查？

A: 在提交信息中添加 `[skip ci]` 或 `[ci skip]`

### Q: 如何手动触发工作流？

A: 在 GitHub Actions 页面点击 "Run workflow"

### Q: 如何查看工作流日志？

A: 进入 Actions 标签页，选择对应的运行记录

### Q: 工作流失败如何处理？

A: 查看日志，修复问题，重新运行或提交新的修复

---

## 📞 获取帮助

- **GitHub Issues**: [提交问题](https://github.com/cedar2025/Xboard/issues)
- **Telegram**: [XboardOfficial](https://t.me/XboardOfficial)
- **文档**: [完整文档](../README_CN.md)

---

**最后更新**: 2025-11-01  
**版本**: 1.0.0

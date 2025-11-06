# 工作流使用指南

## 📋 概述

本文档说明如何使用 Xboard 项目的 GitHub Actions 工作流。

---

## 🚀 快速开始

### 开发者工作流程

```
1. Fork 仓库
   ↓
2. 创建功能分支
   git checkout -b feature/xxx
   ↓
3. 开发和提交
   git commit -m "feat: xxx"
   ↓
4. 推送分支
   git push origin feature/xxx
   ↓
5. 创建 Pull Request
   ↓
6. CI 自动运行
   - 代码检查
   - 运行测试
   - 安全扫描
   ↓
7. Code Review
   ↓
8. 合并到 master
   ↓
9. 自动构建 Docker 镜像
```

---

## 📝 工作流说明

### 1. CI 工作流

**文件**: `.github/workflows/ci.yml`

**触发条件**:
- Pull Request 到 master/dev
- Push 到 master/dev
- 手动触发

**功能**:
- ✅ 代码质量检查（PHPStan, PHP CS Fixer）
- ✅ 单元测试（多 PHP 版本，多数据库）
- ✅ 安全扫描（依赖审计）
- ✅ 代码覆盖率报告

**使用方法**:

```bash
# 自动触发（创建 PR 或推送代码）
git push origin feature/xxx

# 手动触发
gh workflow run ci.yml

# 查看运行状态
gh run list --workflow=ci.yml

# 查看详细日志
gh run view <run-id> --log
```

**Badge**:
```markdown
![CI](https://github.com/ElinksTeam/ElinksBoard/workflows/CI/badge.svg)
```

---

### 2. Docker Build 工作流

**文件**: `.github/workflows/docker-publish.yml`

**触发条件**:
- Push 到 master 分支
- 手动触发

**功能**:
- ✅ 多架构构建（amd64, arm64）
- ✅ 自动推送到 GHCR
- ✅ 镜像签名（Cosign）
- ✅ 多标签支持

**使用方法**:

```bash
# 自动触发（推送到 master）
git push origin master

# 手动触发
gh workflow run docker-publish.yml

# 拉取镜像
docker pull ghcr.io/elinksteam/elinksboard:latest
docker pull ghcr.io/elinksteam/elinksboard:latest

# 验证签名
cosign verify ghcr.io/elinksteam/elinksboard:latest
```

**镜像标签**:
- `latest` - 最新稳定版（master 分支）
- `new` - 最新版本（master 分支）
- `{branch}` - 分支名称
- `{sha}` - Git SHA
- `{version}` - 版本号

---

### 3. Security Scan 工作流

**文件**: `.github/workflows/security.yml`

**触发条件**:
- 每日定时（UTC 00:00）
- Pull Request
- 手动触发

**功能**:
- ✅ 依赖安全扫描
- ✅ 代码安全扫描
- ✅ Docker 镜像扫描
- ✅ 敏感信息检测

**使用方法**:

```bash
# 手动触发
gh workflow run security.yml

# 查看安全报告
# GitHub -> Security -> Code scanning alerts

# 查看依赖警告
# GitHub -> Security -> Dependabot alerts
```

**安全等级**:
- 🔴 Critical - 立即修复
- 🟠 High - 尽快修复
- 🟡 Medium - 计划修复
- 🟢 Low - 可选修复

---

### 4. Release 工作流

**文件**: `.github/workflows/release.yml`

**触发条件**:
- Tag 推送（v*.*.*）
- 手动触发

**功能**:
- ✅ 自动创建 GitHub Release
- ✅ 生成 Release Notes
- ✅ 上传构建产物
- ✅ 发送通知

**使用方法**:

```bash
# 1. 更新版本号和 CHANGELOG
vim CHANGELOG.md

# 2. 提交更改
git add .
git commit -m "chore: prepare release v1.0.0"
git push origin master

# 3. 创建 Tag
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0

# 4. 工作流自动运行，创建 Release

# 5. 查看 Release
gh release view v1.0.0

# 6. 下载构建产物
gh release download v1.0.0
```

**版本号规范**:
- `v1.0.0` - 主版本.次版本.修订号
- `v1.0.0-beta.1` - 预发布版本
- `v1.0.0-rc.1` - 候选版本

---

### 5. Docs 工作流

**文件**: `.github/workflows/docs.yml`

**触发条件**:
- docs/ 目录变更
- README 变更
- 手动触发

**功能**:
- ✅ 构建文档站点
- ✅ 部署到 GitHub Pages
- ✅ 自动更新索引

**使用方法**:

```bash
# 自动触发（修改文档）
git add docs/
git commit -m "docs: update documentation"
git push origin master

# 手动触发
gh workflow run docs.yml

# 访问文档站点
# https://cedar2025.github.io/Xboard/
```

**文档结构**:
```
docs/
├── zh/                    # 中文文档
│   └── installation/      # 安装指南
├── en/                    # 英文文档
│   ├── development/       # 开发文档
│   ├── installation/      # 安装指南
│   └── migration/         # 迁移指南
└── *.md                   # 其他文档
```

---

## 🔧 高级用法

### 跳过 CI

在提交信息中添加 `[skip ci]` 或 `[ci skip]`:

```bash
git commit -m "docs: update README [skip ci]"
```

### 手动触发工作流

```bash
# 使用 GitHub CLI
gh workflow run ci.yml

# 使用 GitHub Web UI
# Actions -> 选择工作流 -> Run workflow
```

### 取消运行中的工作流

```bash
# 取消特定运行
gh run cancel <run-id>

# 取消所有运行中的工作流
gh run list --status in_progress --json databaseId --jq '.[].databaseId' | xargs -I {} gh run cancel {}
```

### 重新运行失败的工作流

```bash
# 重新运行
gh run rerun <run-id>

# 只重新运行失败的 Jobs
gh run rerun <run-id> --failed
```

---

## 📊 监控和通知

### 查看工作流状态

```bash
# 列出最近的运行
gh run list --limit 10

# 查看特定工作流
gh run list --workflow=ci.yml

# 查看运行详情
gh run view <run-id>

# 实时查看日志
gh run watch <run-id>
```

### 设置通知

**GitHub 通知**:
1. Settings -> Notifications
2. 选择通知方式（Email, Web, Mobile）
3. 配置 Actions 通知

**Telegram 通知**（可选）:
```yaml
# 在工作流中添加
- name: Send Telegram notification
  if: always()
  uses: appleboy/telegram-action@master
  with:
    to: ${{ secrets.TELEGRAM_CHAT_ID }}
    token: ${{ secrets.TELEGRAM_BOT_TOKEN }}
    message: |
      Workflow: ${{ github.workflow }}
      Status: ${{ job.status }}
      Commit: ${{ github.sha }}
```

---

## 🛡️ 安全最佳实践

### 1. Secrets 管理

```bash
# 添加 Secret
gh secret set SECRET_NAME

# 列出 Secrets
gh secret list

# 删除 Secret
gh secret remove SECRET_NAME
```

**推荐的 Secrets**:
- `CODECOV_TOKEN` - Codecov 令牌
- `TELEGRAM_BOT_TOKEN` - Telegram Bot 令牌
- `TELEGRAM_CHAT_ID` - Telegram 聊天 ID

### 2. 权限控制

在工作流中明确指定权限:

```yaml
permissions:
  contents: read      # 读取仓库内容
  packages: write     # 写入包
  security-events: write  # 写入安全事件
```

### 3. 依赖固定

使用固定版本的 Actions:

```yaml
# ✅ 好
uses: actions/checkout@v4.1.0

# ❌ 不好
uses: actions/checkout@master
```

---

## 📈 性能优化

### 1. 缓存策略

**Composer 缓存**:
```yaml
- uses: actions/cache@v3
  with:
    path: vendor
    key: composer-${{ hashFiles('composer.lock') }}
```

**Docker 缓存**:
```yaml
cache-from: type=gha
cache-to: type=gha,mode=max
```

### 2. 并行执行

使用矩阵策略:

```yaml
strategy:
  matrix:
    php: ['8.2', '8.3']
    db: ['mysql', 'pgsql']
```

### 3. 条件执行

跳过不必要的步骤:

```yaml
- name: Run tests
  if: github.event_name == 'pull_request'
  run: vendor/bin/phpunit
```

---

## 🐛 故障排查

### 常见问题

**1. 工作流未触发**

检查触发条件:
```yaml
on:
  push:
    branches: [master]
    paths:
      - 'app/**'
      - 'config/**'
```

**2. 权限错误**

添加必要的权限:
```yaml
permissions:
  contents: write
  packages: write
```

**3. 缓存问题**

清除缓存:
```bash
# 删除缓存
gh cache delete <cache-key>

# 列出所有缓存
gh cache list
```

**4. 超时**

增加超时时间:
```yaml
jobs:
  build:
    timeout-minutes: 30  # 默认 360 分钟
```

---

## 📚 相关资源

- [GitHub Actions 文档](https://docs.github.com/actions)
- [工作流设计](WORKFLOW_DESIGN.md)
- [工作流测试](WORKFLOW_TESTING.md)
- [GitHub CLI 文档](https://cli.github.com/manual/)

---

## 🤝 贡献指南

### 修改工作流

1. 创建功能分支
2. 修改工作流文件
3. 本地测试（使用 act）
4. 提交 PR
5. 等待 Review

### 添加新工作流

1. 创建工作流文件
2. 添加文档说明
3. 测试验证
4. 提交 PR

---

## ❓ 常见问题

**Q: 如何查看工作流的执行历史？**

A: 使用 `gh run list --workflow=<workflow-name>` 或访问 GitHub Actions 页面。

**Q: 如何调试工作流？**

A: 启用调试模式，添加 `ACTIONS_RUNNER_DEBUG=true` 和 `ACTIONS_STEP_DEBUG=true` 到 Secrets。

**Q: 工作流可以访问私有仓库吗？**

A: 需要配置 PAT (Personal Access Token) 并添加到 Secrets。

**Q: 如何限制工作流的并发执行？**

A: 使用 `concurrency` 配置:
```yaml
concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true
```

---

## 📞 获取帮助

- **GitHub Issues**: [提交问题](https://github.com/ElinksTeam/ElinksBoard/issues)
- **Telegram**: [XboardOfficial](https://t.me/XboardOfficial)
- **文档**: [完整文档](../README_CN.md)

---

**最后更新**: 2025-11-01  
**版本**: 1.0.0

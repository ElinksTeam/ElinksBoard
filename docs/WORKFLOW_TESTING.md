# 工作流测试指南

## 📋 概述

本文档说明如何测试和验证 GitHub Actions 工作流。

---

## 🧪 测试清单

### 1. CI 工作流测试

#### 测试场景

- [ ] **代码质量检查**
  - PHP 语法检查
  - PHPStan 静态分析
  - PHP CS Fixer 代码风格
  - Composer 验证

- [ ] **单元测试**
  - PHP 8.2 + SQLite
  - PHP 8.2 + MySQL
  - PHP 8.2 + PostgreSQL
  - PHP 8.3 + MySQL

- [ ] **安全检查**
  - Composer 安全审计
  - 依赖漏洞扫描

#### 测试步骤

```bash
# 1. 创建测试分支
git checkout -b test/ci-workflow

# 2. 修改代码触发 CI
echo "// Test CI" >> app/Http/Controllers/Controller.php

# 3. 提交并推送
git add .
git commit -m "test: trigger CI workflow"
git push origin test/ci-workflow

# 4. 创建 PR
gh pr create --title "Test: CI Workflow" --body "Testing CI workflow"

# 5. 查看工作流运行
gh run list --workflow=ci.yml

# 6. 查看详细日志
gh run view <run-id> --log
```

#### 预期结果

✅ 所有检查通过  
✅ 测试覆盖率报告生成  
✅ 无安全漏洞  

---

### 2. Docker Build 工作流测试

#### 测试场景

- [ ] **多架构构建**
  - linux/amd64
  - linux/arm64

- [ ] **镜像标签**
  - latest
  - new
  - branch-name
  - git-sha

- [ ] **镜像签名**
  - Cosign 签名验证

#### 测试步骤

```bash
# 1. 推送到 master 分支
git checkout master
git pull origin master

# 2. 创建测试提交
echo "# Test Docker Build" >> README.md
git add README.md
git commit -m "test: trigger docker build"
git push origin master

# 3. 查看工作流运行
gh run list --workflow=docker-publish.yml

# 4. 验证镜像
docker pull ghcr.io/elinksteam/elinksboard:latest
docker run --rm ghcr.io/elinksteam/elinksboard:latest php --version
```

#### 预期结果

✅ 镜像构建成功  
✅ 多架构支持  
✅ 镜像已签名  
✅ 标签正确  

---

### 3. Security Scan 工作流测试

#### 测试场景

- [ ] **依赖扫描**
  - Composer 依赖
  - npm 依赖

- [ ] **代码扫描**
  - Trivy 扫描
  - 敏感信息检测

- [ ] **镜像扫描**
  - Docker 镜像漏洞

#### 测试步骤

```bash
# 1. 手动触发安全扫描
gh workflow run security.yml

# 2. 查看运行结果
gh run list --workflow=security.yml

# 3. 查看安全报告
# 进入 GitHub Security 标签页查看
```

#### 预期结果

✅ 无高危漏洞  
✅ 依赖安全  
✅ 无敏感信息泄露  

---

### 4. Release 工作流测试

#### 测试场景

- [ ] **创建发布**
  - Tag 推送触发
  - Release Notes 生成
  - 构建产物上传

#### 测试步骤

```bash
# 1. 创建测试 Tag
git tag -a v1.0.0-test -m "Test release"
git push origin v1.0.0-test

# 2. 查看工作流运行
gh run list --workflow=release.yml

# 3. 验证 Release
gh release view v1.0.0-test

# 4. 下载构建产物
gh release download v1.0.0-test

# 5. 清理测试 Release
gh release delete v1.0.0-test --yes
git push origin :refs/tags/v1.0.0-test
```

#### 预期结果

✅ Release 创建成功  
✅ Release Notes 完整  
✅ 构建产物可下载  

---

### 5. Docs 工作流测试

#### 测试场景

- [ ] **文档构建**
  - Markdown 文件处理
  - 索引页面生成

- [ ] **部署**
  - GitHub Pages 部署

#### 测试步骤

```bash
# 1. 修改文档
echo "# Test" >> docs/test.md
git add docs/test.md
git commit -m "docs: add test document"
git push origin master

# 2. 查看工作流运行
gh run list --workflow=docs.yml

# 3. 访问文档站点
# https://cedar2025.github.io/Xboard/

# 4. 清理测试文档
git rm docs/test.md
git commit -m "docs: remove test document"
git push origin master
```

#### 预期结果

✅ 文档构建成功  
✅ 站点可访问  
✅ 索引页面正常  

---

## 🔍 调试技巧

### 1. 查看工作流日志

```bash
# 列出最近的运行
gh run list

# 查看特定运行的日志
gh run view <run-id> --log

# 查看特定 Job 的日志
gh run view <run-id> --log --job=<job-id>

# 下载日志
gh run download <run-id>
```

### 2. 本地测试工作流

使用 [act](https://github.com/nektos/act) 在本地运行工作流：

```bash
# 安装 act
brew install act  # macOS
# 或
curl https://raw.githubusercontent.com/nektos/act/master/install.sh | sudo bash

# 列出工作流
act -l

# 运行特定工作流
act -W .github/workflows/ci.yml

# 运行特定 Job
act -j code-quality

# 使用特定事件触发
act pull_request
```

### 3. 调试模式

在工作流中启用调试：

```yaml
- name: Debug
  run: |
    echo "::debug::Debug message"
    echo "::warning::Warning message"
    echo "::error::Error message"
```

启用 Runner 调试：

```bash
# 在仓库 Secrets 中添加
ACTIONS_RUNNER_DEBUG=true
ACTIONS_STEP_DEBUG=true
```

---

## 🐛 常见问题

### 问题 1：工作流未触发

**原因：**
- 触发条件不匹配
- 工作流文件语法错误
- 权限不足

**解决方案：**
```bash
# 检查工作流语法
gh workflow view ci.yml

# 手动触发
gh workflow run ci.yml

# 检查权限
# Settings -> Actions -> General -> Workflow permissions
```

### 问题 2：测试失败

**原因：**
- 环境配置错误
- 依赖问题
- 数据库连接失败

**解决方案：**
```bash
# 查看详细日志
gh run view <run-id> --log

# 本地复现
act -j tests

# 检查环境变量
# 在工作流中添加调试输出
```

### 问题 3：Docker 构建失败

**原因：**
- 磁盘空间不足
- 网络问题
- 缓存问题

**解决方案：**
```bash
# 清理磁盘空间
# 工作流中已包含清理步骤

# 禁用缓存测试
# 修改 docker-publish.yml，移除 cache-from/cache-to

# 查看构建日志
gh run view <run-id> --log --job=build
```

### 问题 4：权限错误

**原因：**
- Token 权限不足
- GITHUB_TOKEN 过期

**解决方案：**
```bash
# 检查工作流权限
# 在工作流文件中添加：
permissions:
  contents: write
  packages: write

# 使用 PAT (Personal Access Token)
# Settings -> Developer settings -> Personal access tokens
```

---

## 📊 性能监控

### 工作流执行时间

```bash
# 查看最近的运行时间
gh run list --limit 10 --json conclusion,startedAt,updatedAt

# 分析瓶颈
gh run view <run-id> --log | grep "took"
```

### 优化建议

1. **并行执行**
   - 使用矩阵策略
   - 独立的 Jobs 并行运行

2. **缓存优化**
   - Composer 依赖缓存
   - Docker 层缓存
   - npm 依赖缓存

3. **条件执行**
   - 使用 `if` 条件跳过不必要的步骤
   - 使用 `paths` 过滤触发条件

---

## ✅ 验收标准

### CI 工作流

- [ ] 所有代码质量检查通过
- [ ] 所有测试通过
- [ ] 代码覆盖率 ≥ 70%
- [ ] 无安全漏洞
- [ ] 执行时间 < 5 分钟

### Docker Build 工作流

- [ ] 多架构构建成功
- [ ] 镜像可正常运行
- [ ] 镜像已签名
- [ ] 标签正确
- [ ] 执行时间 < 15 分钟

### Security Scan 工作流

- [ ] 依赖扫描完成
- [ ] 代码扫描完成
- [ ] 镜像扫描完成
- [ ] 无高危漏洞
- [ ] 执行时间 < 5 分钟

### Release 工作流

- [ ] Release 创建成功
- [ ] Release Notes 完整
- [ ] 构建产物可下载
- [ ] 通知发送成功
- [ ] 执行时间 < 3 分钟

### Docs 工作流

- [ ] 文档构建成功
- [ ] 站点可访问
- [ ] 索引页面正常
- [ ] 所有链接有效
- [ ] 执行时间 < 3 分钟

---

## 📚 相关资源

- [GitHub Actions 文档](https://docs.github.com/actions)
- [工作流语法](https://docs.github.com/actions/reference/workflow-syntax-for-github-actions)
- [act 文档](https://github.com/nektos/act)
- [工作流设计](WORKFLOW_DESIGN.md)

---

**最后更新**: 2025-11-01  
**版本**: 1.0.0

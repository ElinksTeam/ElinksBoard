# AI 前端组件测试指南

## 测试页面

### 1. 完整功能页面
**URL:** `/ai-assistant.html`

**测试内容:**
- ✅ 搜索功能
- ✅ 聊天功能
- ✅ 响应式布局
- ✅ 移动端适配

### 2. 组件演示页面
**URL:** `/ai-widgets-demo.html`

**测试内容:**
- ✅ 搜索组件浮动按钮
- ✅ 聊天组件浮动按钮
- ✅ 组件交互
- ✅ 集成示例代码

### 3. 移动端测试页面
**URL:** `/ai-mobile-test.html`

**测试内容:**
- ✅ 移动端布局
- ✅ 触摸事件
- ✅ 键盘交互
- ✅ 性能测试

## 测试清单

### 桌面端测试 (>1024px)

#### 搜索组件
- [ ] 浮动按钮显示正常
- [ ] 点击按钮打开搜索面板
- [ ] 搜索面板宽度 400px
- [ ] 输入框可以正常输入
- [ ] 点击搜索按钮触发搜索
- [ ] 按 Enter 键触发搜索
- [ ] 搜索结果正确显示
- [ ] 点击结果项跳转正确
- [ ] 相似度百分比显示正确
- [ ] 点击外部区域关闭面板
- [ ] 关闭按钮工作正常

#### 聊天组件
- [ ] 浮动按钮显示正常
- [ ] 点击按钮打开聊天窗口
- [ ] 聊天窗口尺寸 380x600px
- [ ] 欢迎消息正确显示
- [ ] 输入框可以正常输入
- [ ] 按 Enter 发送消息
- [ ] Shift+Enter 换行
- [ ] 消息气泡正确显示
- [ ] 用户消息靠右显示
- [ ] AI 消息靠左显示
- [ ] 来源链接可以点击
- [ ] 自动滚动到底部
- [ ] 最小化按钮工作正常

### 平板端测试 (768px - 1024px)

#### 搜索组件
- [ ] 浮动按钮大小适中
- [ ] 搜索面板宽度自适应
- [ ] 布局不会溢出
- [ ] 触摸操作流畅

#### 聊天组件
- [ ] 浮动按钮大小适中
- [ ] 聊天窗口宽度自适应
- [ ] 消息气泡宽度合适
- [ ] 触摸操作流畅

### 移动端测试 (<768px)

#### 搜索组件
- [ ] 浮动按钮 48x48px
- [ ] 搜索面板全屏宽度
- [ ] 输入框大小合适
- [ ] 虚拟键盘不遮挡内容
- [ ] 滚动流畅
- [ ] 触摸反馈明显

#### 聊天组件
- [ ] 浮动按钮 48x48px
- [ ] 聊天窗口全屏
- [ ] 消息气泡宽度 85%
- [ ] 输入框自适应高度
- [ ] 虚拟键盘不遮挡输入框
- [ ] 滚动流畅
- [ ] 触摸反馈明显

### 功能测试

#### API 集成
- [ ] 搜索 API 调用成功
- [ ] 聊天 API 调用成功
- [ ] 会话创建成功
- [ ] 认证 token 正确传递
- [ ] 错误处理正确
- [ ] 加载状态显示

#### 用户体验
- [ ] 加载动画流畅
- [ ] 错误提示清晰
- [ ] 空状态提示友好
- [ ] 操作反馈及时
- [ ] 动画效果自然
- [ ] 颜色对比度足够

#### 性能测试
- [ ] 首次加载时间 < 2s
- [ ] 搜索响应时间 < 3s
- [ ] 聊天响应时间 < 5s
- [ ] 内存占用合理
- [ ] 无内存泄漏
- [ ] 滚动性能良好

### 兼容性测试

#### 浏览器
- [ ] Chrome (最新版)
- [ ] Firefox (最新版)
- [ ] Safari (最新版)
- [ ] Edge (最新版)
- [ ] Chrome Mobile
- [ ] Safari Mobile

#### 操作系统
- [ ] Windows 10/11
- [ ] macOS
- [ ] iOS 14+
- [ ] Android 10+

#### 屏幕尺寸
- [ ] 320px (iPhone SE)
- [ ] 375px (iPhone 12/13)
- [ ] 390px (iPhone 14)
- [ ] 414px (iPhone Plus)
- [ ] 768px (iPad)
- [ ] 1024px (iPad Pro)
- [ ] 1280px (小屏笔记本)
- [ ] 1920px (桌面)

## 测试步骤

### 1. 基础功能测试

```bash
# 1. 访问测试页面
打开浏览器访问: http://your-domain.com/ai-widgets-demo.html

# 2. 测试搜索
- 点击绿色搜索按钮
- 输入 "如何配置"
- 点击搜索或按 Enter
- 检查结果是否正确显示

# 3. 测试聊天
- 点击蓝色聊天按钮
- 输入 "你好"
- 点击发送或按 Enter
- 检查回复是否正确显示
```

### 2. 响应式测试

```bash
# 使用浏览器开发者工具
1. 打开开发者工具 (F12)
2. 切换到设备模拟模式
3. 测试不同设备:
   - iPhone SE (375x667)
   - iPhone 12 Pro (390x844)
   - iPad (768x1024)
   - Desktop (1920x1080)
4. 检查布局是否正确
5. 测试横屏和竖屏
```

### 3. 性能测试

```bash
# 使用浏览器性能工具
1. 打开开发者工具
2. 切换到 Performance 标签
3. 开始录制
4. 执行操作 (搜索/聊天)
5. 停止录制
6. 分析性能指标:
   - FPS 应该 > 30
   - 主线程任务 < 50ms
   - 内存增长合理
```

### 4. 网络测试

```bash
# 模拟不同网络条件
1. 打开开发者工具
2. 切换到 Network 标签
3. 选择网络限速:
   - Fast 3G
   - Slow 3G
   - Offline
4. 测试加载和响应时间
5. 检查错误处理
```

## 常见问题

### 组件不显示

**问题:** 浮动按钮不显示

**检查:**
```javascript
// 1. 检查脚本是否加载
console.log(typeof AISearchWidget); // 应该是 'function'
console.log(typeof AIChatWidget);   // 应该是 'function'

// 2. 检查初始化
console.log(window.searchWidget);
console.log(window.chatWidget);

// 3. 检查 CSS
const widget = document.querySelector('.ai-search-widget');
console.log(widget);
console.log(getComputedStyle(widget).display);
```

**解决方案:**
- 确认脚本路径正确
- 检查是否有 JavaScript 错误
- 确认 z-index 没有被覆盖

### 搜索/聊天失败

**问题:** API 调用失败

**检查:**
```javascript
// 1. 检查 token
const token = localStorage.getItem('auth_token');
console.log('Token:', token);

// 2. 检查 API 端点
fetch('/api/v1/user/ai/search', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ query: 'test', limit: 5 })
})
.then(r => r.json())
.then(console.log)
.catch(console.error);
```

**解决方案:**
- 确认用户已登录
- 检查 API 端点是否可访问
- 查看网络请求的响应

### 样式冲突

**问题:** 组件样式不正确

**检查:**
```javascript
// 检查样式表是否加载
const styles = document.getElementById('ai-search-widget-styles');
console.log(styles);

// 检查具体元素的样式
const trigger = document.querySelector('.ai-search-trigger');
console.log(getComputedStyle(trigger));
```

**解决方案:**
- 增加 CSS 选择器优先级
- 使用 `!important` 覆盖
- 检查是否有全局样式冲突

### 移动端问题

**问题:** 移动端布局错误

**检查:**
```html
<!-- 确认 viewport 设置 -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- 检查媒体查询 -->
@media (max-width: 640px) {
    /* 移动端样式 */
}
```

**解决方案:**
- 确认 viewport meta 标签
- 测试不同屏幕尺寸
- 检查触摸事件处理

## 自动化测试

### 使用 Playwright

```javascript
// test/ai-widgets.spec.js
const { test, expect } = require('@playwright/test');

test('搜索组件显示', async ({ page }) => {
    await page.goto('/ai-widgets-demo.html');
    
    const trigger = page.locator('.ai-search-trigger');
    await expect(trigger).toBeVisible();
});

test('搜索功能', async ({ page }) => {
    await page.goto('/ai-widgets-demo.html');
    
    // 打开搜索
    await page.click('.ai-search-trigger');
    
    // 输入搜索词
    await page.fill('#aiSearchInput', '测试');
    
    // 点击搜索
    await page.click('#aiSearchButton');
    
    // 等待结果
    await page.waitForSelector('.ai-search-result-item');
    
    // 检查结果
    const results = await page.locator('.ai-search-result-item').count();
    expect(results).toBeGreaterThan(0);
});

test('聊天功能', async ({ page }) => {
    await page.goto('/ai-widgets-demo.html');
    
    // 打开聊天
    await page.click('.ai-chat-trigger');
    
    // 输入消息
    await page.fill('#aiChatInput', '你好');
    
    // 发送消息
    await page.click('#aiChatSendBtn');
    
    // 等待回复
    await page.waitForSelector('.ai-chat-message.assistant', { timeout: 10000 });
    
    // 检查回复
    const messages = await page.locator('.ai-chat-message').count();
    expect(messages).toBeGreaterThan(1);
});
```

### 运行测试

```bash
# 安装 Playwright
npm install -D @playwright/test

# 运行测试
npx playwright test

# 运行特定测试
npx playwright test ai-widgets.spec.js

# 调试模式
npx playwright test --debug
```

## 性能基准

### 目标指标

| 指标 | 目标值 | 说明 |
|------|--------|------|
| 首次加载 | < 2s | 脚本加载和初始化 |
| 搜索响应 | < 3s | 从输入到显示结果 |
| 聊天响应 | < 5s | 从发送到收到回复 |
| FPS | > 30 | 动画流畅度 |
| 内存占用 | < 50MB | 组件内存使用 |
| 包大小 | < 100KB | 压缩后的文件大小 |

### 测量方法

```javascript
// 测量加载时间
const start = performance.now();
// ... 加载组件
const end = performance.now();
console.log('加载时间:', end - start, 'ms');

// 测量内存使用
if (performance.memory) {
    console.log('内存使用:', 
        (performance.memory.usedJSHeapSize / 1048576).toFixed(2), 'MB'
    );
}

// 测量 FPS
let lastTime = performance.now();
let frames = 0;

function measureFPS() {
    frames++;
    const currentTime = performance.now();
    if (currentTime >= lastTime + 1000) {
        console.log('FPS:', frames);
        frames = 0;
        lastTime = currentTime;
    }
    requestAnimationFrame(measureFPS);
}
measureFPS();
```

## 报告问题

### 问题模板

```markdown
**问题描述:**
简要描述问题

**重现步骤:**
1. 访问页面 xxx
2. 点击按钮 xxx
3. 输入内容 xxx
4. 观察到 xxx

**预期行为:**
应该显示 xxx

**实际行为:**
实际显示 xxx

**环境信息:**
- 浏览器: Chrome 120
- 操作系统: Windows 11
- 屏幕尺寸: 1920x1080
- 设备: Desktop

**截图:**
(如果适用)

**控制台错误:**
(如果有)
```

## 测试报告

### 报告模板

```markdown
# AI 组件测试报告

**测试日期:** 2025-11-06
**测试人员:** xxx
**测试版本:** v1.0.0

## 测试环境
- 浏览器: Chrome 120, Firefox 119, Safari 17
- 设备: Desktop, iPhone 14, iPad Pro
- 网络: WiFi, 4G

## 测试结果

### 桌面端
- ✅ 搜索组件: 通过 (10/10)
- ✅ 聊天组件: 通过 (10/10)
- ✅ 响应式: 通过 (5/5)

### 移动端
- ✅ 搜索组件: 通过 (8/8)
- ✅ 聊天组件: 通过 (8/8)
- ✅ 触摸交互: 通过 (5/5)

### 性能
- ✅ 加载时间: 1.2s (目标 < 2s)
- ✅ 搜索响应: 2.1s (目标 < 3s)
- ✅ 聊天响应: 3.8s (目标 < 5s)
- ✅ FPS: 60 (目标 > 30)

## 发现的问题
无

## 建议
1. 考虑添加离线支持
2. 优化大量结果的渲染性能
3. 添加更多动画效果

## 结论
所有测试通过，组件可以发布。
```

## 持续集成

### GitHub Actions

```yaml
# .github/workflows/test-ai-widgets.yml
name: Test AI Widgets

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '18'
      - run: npm install
      - run: npx playwright install
      - run: npx playwright test
      - uses: actions/upload-artifact@v3
        if: always()
        with:
          name: playwright-report
          path: playwright-report/
```

## 总结

完整的测试流程确保 AI 组件在各种环境下都能正常工作。定期运行测试并记录结果，持续改进组件质量。

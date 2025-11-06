# ElinksBoard 

<div align="center">

[![Telegram](https://img.shields.io/badge/Telegram-頻道-blue)](https://t.me/XboardOfficial)
![PHP](https://img.shields.io/badge/PHP-8.2+-green.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-blue.svg)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

**現代化的面板系統 | 基於 Laravel 11 | 整合 Logto 認證**

[English](README.md) | [简体中文](README_CN.md) | 繁體中文

</div>

---

## 📖 簡介

ElinksBoard 是一個基於 Laravel 11 建構的現代化面板系統，專注於提供簡潔高效的使用者體驗。最新版本已完全整合 Logto 認證系統，提供企業級的身份認證解決方案。

### 🎯 核心特性

- 🚀 **高效能架構** - Laravel 11 + Octane，顯著提升效能
- 🔐 **Logto 認證** - 企業級 OAuth2/OIDC 認證，支援 SSO、MFA、社交登入
- 🎨 **現代化介面** - React + Shadcn UI 管理後台，Vue3 + TypeScript 使用者前端
- 📱 **響應式設計** - 完美適配各種裝置
- 🐳 **容器化部署** - 開箱即用的 Docker 部署方案
- 💾 **Redis 快取** - 完整的快取管理系統，包含備份、監控、最佳化工具
- 🎯 **最佳化架構** - 更好的可維護性和擴充性

---

## 🆕 最新更新

### v2.0 - Logto 認證整合

- ✅ **完全移除傳統登入** - 所有使用者透過 Logto 認證
- ✅ **首次使用者自動成為管理員** - 安裝後首次登入自動獲得管理員權限
- ✅ **管理面板設定** - 可在後台直接管理 Logto 設定
- ✅ **自動使用者同步** - 支援自動建立和更新使用者資訊
- ✅ **完整文件** - 提供詳細的安裝和整合指南

### Redis 快取管理

- ✅ **自動備份腳本** - 定時備份 Redis 資料
- ✅ **效能監控** - 即時監控快取效能和告警
- ✅ **最佳化工具** - 快取清理和最佳化指令
- ✅ **Prometheus 整合** - 支援 Prometheus 監控

---

## 🚀 快速開始

### 方式一：Docker Compose（推薦）

這是最簡單快速的安裝方式，適合大多數使用者。

```bash
# 1. 複製儲存庫
git clone --depth 1 https://github.com/ElinksTeam/ElinksBoard
cd ElinksBoard

# 2. 複製 Docker Compose 設定
cp compose.sample.yaml docker-compose.yml

# 3. 啟動服務
docker compose up -d
```

**存取您的網站：** `http://伺服器IP:7001`

⚠️ **重要：安裝完成後立即完成首次登入以獲取管理員權限！**

---

## 📋 詳細安裝指南

我們提供多種安裝方式，選擇最適合您的：

### 🐳 Docker 部署（推薦）

| 方式 | 難度 | 適用場景 | 文件連結 |
|------|------|----------|----------|
| **Docker Compose** | ⭐ 簡單 | 新手、快速部署 | [查看教學](docs/zh/installation/docker-compose.md) |
| **1Panel** | ⭐⭐ 中等 | 視覺化管理 | [查看教學](docs/zh/installation/1panel.md) |

### 🖥️ 傳統部署

| 方式 | 難度 | 適用場景 | 文件連結 |
|------|------|----------|----------|
| **手動安裝** | ⭐⭐⭐⭐ 進階 | 自訂環境 | [查看教學](INSTALLATION_GUIDE.md) |

---

## 🔐 Logto 認證設定

### 快速設定（5分鐘）

1. **註冊 Logto 帳號**
   - 造訪 [Logto Cloud](https://cloud.logto.io) 或使用自架實例
   - 建立一個新的 **Traditional Web Application**

2. **取得憑證**
   - 複製 **Endpoint**、**App ID**、**App Secret**

3. **設定 Redirect URI**
   ```
   http://your-domain.com/api/v1/passport/auth/logto/callback
   ```

4. **完成首次登入** ⚠️ **關鍵步驟**
   - 安裝後立即造訪您的網站
   - 點選「使用 Logto 登入」
   - 首次登入的使用者自動成為管理員

**詳細文件：**
- [Logto 快速設定](LOGTO_SETUP.md)
- [完整整合指南](docs/LOGTO_INTEGRATION.md)
- [前端整合指南](docs/FRONTEND_LOGTO_INTEGRATION.md)

---

## 📚 完整文件

### 🔧 安裝與設定

- [安裝指南](INSTALLATION_GUIDE.md) - 完整的安裝步驟和設定說明
- [Logto 設定](LOGTO_SETUP.md) - Logto 認證快速設定指南
- [Logto 變更說明](LOGTO_CHANGES.md) - Logto 整合的詳細變更
- [實作狀態](IMPLEMENTATION_STATUS.md) - 目前實作進度和功能狀態
- [Docker 建置指南](DOCKER_BUILD.md) - Docker 映像建置和推送
- [Docker 快速入門](DOCKER_QUICKSTART.md) - Docker 快速參考

### 💾 Redis 快取管理

- [Redis 快取指南](docs/REDIS_CACHE_GUIDE.md) - 完整的快取管理文件
- [Redis 快速參考](docs/REDIS_QUICK_REFERENCE.md) - 常用指令和操作
- [實作總結](REDIS_IMPLEMENTATION_SUMMARY.md) - Redis 功能實作詳情

### 🛠️ 開發文件

- [外掛開發指南](docs/en/development/plugin-development-guide.md) - 開發 ElinksBoard 外掛
- [效能最佳化](docs/en/development/performance.md) - 效能最佳化建議
- [裝置限制](docs/en/development/device-limit.md) - 裝置限制功能

---

## 🛠️ 技術堆疊

### 後端
- **框架**: Laravel 11 + Octane
- **資料庫**: MySQL 5.7+ / PostgreSQL / SQLite
- **快取**: Redis
- **認證**: Logto (OAuth2/OIDC)
- **佇列**: Redis Queue

### 前端
- **管理後台**: React + Shadcn UI + TailwindCSS
- **使用者前端**: Vue3 + TypeScript + NaiveUI
- **建置工具**: Vite

### 部署
- **容器化**: Docker + Docker Compose
- **Web伺服器**: Nginx / Caddy
- **程序管理**: Supervisor / Systemd

---

## 🔧 常用指令

### Docker 環境

```bash
# 查看日誌
docker compose logs -f web

# 重啟服務
docker compose restart

# 進入容器
docker compose exec web bash

# 執行 Artisan 指令
docker compose exec web php artisan [command]

# 清理快取
docker compose exec web php artisan cache:clear
docker compose exec web php artisan config:clear
```

### Redis 快取管理

```bash
# 監控 Redis
./scripts/monitor-redis.sh

# 備份 Redis
./scripts/backup-redis.sh

# 還原備份
./scripts/restore-redis.sh latest

# 最佳化快取
docker compose exec web php artisan cache:optimize
```

### Logto 管理

```bash
# 測試 Logto 連線
# 登入管理面板 -> Logto 設定 -> 測試連線

# 查看 Logto 設定
docker compose exec web php artisan config:show logto

# 查看使用者統計
# 登入管理面板 -> Logto 設定 -> 使用者統計
```

---

## ⚙️ 系統需求

### 最低配置
- **CPU**: 1核心
- **記憶體**: 1GB
- **儲存空間**: 10GB
- **系統**: Linux (推薦 Ubuntu 20.04+)

### 推薦配置
- **CPU**: 2核心+
- **記憶體**: 2GB+
- **儲存空間**: 20GB+
- **系統**: Ubuntu 22.04 LTS

### 軟體需求
- **PHP**: 8.2+
- **MySQL**: 5.7+ / PostgreSQL / SQLite
- **Redis**: 6.0+
- **Docker**: 20.10+ (Docker 部署)
- **Docker Compose**: 2.0+ (Docker 部署)

---

## 🔔 重要提示

### 安全建議

1. **立即完成首次登入**
   - 安裝後立即登入以獲取管理員權限
   - 首次登入的使用者自動成為管理員
   - 後續使用者為一般使用者

2. **保護管理員路徑**
   - 管理員路徑在安裝時隨機產生
   - 請妥善保管，不要洩露

3. **使用 HTTPS**
   - 正式環境務必設定 SSL 憑證
   - 更新 Logto 回呼 URI 為 HTTPS

4. **定期備份**
   - 定期備份資料庫
   - 使用 Redis 備份腳本備份快取
   - 儲存 `.env` 設定檔

### 修改管理路徑後需要重啟

```bash
# Docker 環境
docker compose restart
```

---

## 🔄 升級指南

### 從舊版本升級

⚠️ **重要提示**：本版本涉及重大變更，升級前請：

1. **備份資料庫**
   ```bash
   # MySQL
   mysqldump -u root -p elinksboard > backup.sql
   
   # SQLite
   cp database/database.sqlite database/database.sqlite.backup
   ```

2. **備份 Redis**
   ```bash
   ./scripts/backup-redis.sh
   ```

3. **備份設定檔**
   ```bash
   cp .env .env.backup
   ```

4. **查看升級文件**
   - 嚴格按照升級文件操作
   - 注意：升級和遷移是不同的程序

5. **測試環境驗證**
   - 建議先在測試環境驗證
   - 確認無誤後再升級正式環境

---

## 🤝 貢獻指南

歡迎提交 Issue 和 Pull Request 來協助改進專案！

### 如何貢獻

1. Fork 本儲存庫
2. 建立您的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交您的變更 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 開啟一個 Pull Request

### 程式碼規範

- 遵循 PSR-12 編碼規範
- 撰寫清晰的提交訊息
- 新增必要的測試
- 更新相關文件

---

## 🌟 維護說明

本專案目前處於輕度維護狀態。我們將：

- ✅ 修復關鍵 bug 和安全問題
- ✅ 審查和合併重要的 Pull Request
- ✅ 提供必要的相容性更新
- ⚠️ 新功能開發可能受限

---

## ⚠️ 免責聲明

本專案僅供學習和交流使用。使用本專案所產生的任何後果由使用者自行承擔。

---

## 📞 取得協助

### 文件資源

- [安裝指南](INSTALLATION_GUIDE.md)
- [Logto 設定](LOGTO_SETUP.md)
- [Redis 快取指南](docs/REDIS_CACHE_GUIDE.md)
- [Docker 建置指南](DOCKER_BUILD.md)

### 社群支援

- **Telegram 頻道**: [XboardOfficial](https://t.me/XboardOfficial)
- **GitHub Issues**: [提交問題](https://github.com/ElinksTeam/ElinksBoard/issues)
- **GitHub Discussions**: [參與討論](https://github.com/ElinksTeam/ElinksBoard/discussions)

### 日誌除錯

```bash
# 查看 Laravel 日誌
tail -f storage/logs/laravel.log

# 查看 Docker 日誌
docker compose logs -f

# 啟用除錯模式
# 編輯 .env
APP_DEBUG=true
LOG_LEVEL=debug
```

---

## 📄 授權條款

本專案採用 [MIT 授權條款](LICENSE)。

---

## 🙏 致謝

感謝所有為本專案做出貢獻的開發者！

特別感謝：
- [Laravel](https://laravel.com) - 優雅的 PHP 框架
- [Logto](https://logto.io) - 現代化的身份認證服務
- [Vue.js](https://vuejs.org) - 漸進式 JavaScript 框架
- [React](https://react.dev) - 用於建構使用者介面的 JavaScript 函式庫

---

<div align="center">

**如果這個專案對您有幫助，請給我們一個 ⭐️**

Made with ❤️ by ElinksBoard Team

</div>

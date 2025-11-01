# Xboard

<div align="center">

[![Telegram](https://img.shields.io/badge/Telegram-Channel-blue)](https://t.me/XboardOfficial)
![PHP](https://img.shields.io/badge/PHP-8.2+-green.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-blue.svg)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

**Modern Panel System | Built on Laravel 11 | Integrated with Logto Authentication**

English | [简体中文](README_CN.md)

</div>

---

## 📖 Introduction

Xboard is a modern panel system built on Laravel 11, focusing on providing a clean and efficient user experience. The latest version is fully integrated with Logto authentication system, providing enterprise-grade identity authentication solutions.

## ✨ Features

- 🚀 **High Performance** - Laravel 11 + Octane for significant performance gains
- 🔐 **Logto Authentication** - Enterprise-grade OAuth2/OIDC authentication with SSO, MFA, and social login support
- 🎨 **Modern Interface** - React + Shadcn UI admin panel, Vue3 + TypeScript user frontend
- 📱 **Responsive Design** - Perfect adaptation to various devices
- 🐳 **Containerized Deployment** - Ready-to-use Docker deployment solution
- 💾 **Redis Cache** - Complete cache management system with backup, monitoring, and optimization tools
- 🎯 **Optimized Architecture** - Better maintainability and scalability

## 🚀 Quick Start

```bash
# 1. Clone repository
git clone -b compose --depth 1 https://github.com/cedar2025/Xboard
cd Xboard

# 2. Run installation wizard
docker compose run -it --rm \
    -e ENABLE_SQLITE=true \
    -e ENABLE_REDIS=true \
    web php artisan xboard:install

# 3. Start services
docker compose up -d
```

**The installation wizard will guide you through:**
1. Database configuration (SQLite/MySQL/PostgreSQL)
2. Redis configuration
3. **Logto authentication configuration** (Required)
4. System initialization

**Access your site:** `http://SERVER_IP:7001`

⚠️ **Important: Complete the first login immediately after installation to obtain administrator privileges!**

---

## 📋 Detailed Installation Guides

We provide multiple installation methods, choose the one that suits you best:

### 🐳 Docker Deployment (Recommended)

| Method | Difficulty | Use Case | Documentation |
|--------|------------|----------|---------------|
| **Docker Compose** | ⭐ Easy | Beginners, Quick deployment | [View Tutorial](docs/zh/installation/docker-compose.md) |
| **1Panel** | ⭐⭐ Medium | Visual management | [View Tutorial](docs/zh/installation/1panel.md) |
| **aaPanel + Docker** | ⭐⭐ Medium | Panel users | [View Tutorial](docs/en/installation/aapanel-docker.md) |

### 🖥️ Traditional Deployment

| Method | Difficulty | Use Case | Documentation |
|--------|------------|----------|---------------|
| **aaPanel** | ⭐⭐⭐ Complex | Traditional hosting | [View Tutorial](docs/en/installation/aapanel.md) |
| **Manual Installation** | ⭐⭐⭐⭐ Advanced | Custom environment | [View Tutorial](docs/zh/installation/manual.md) |

## 📖 Documentation

### 🔧 Installation & Configuration

- [Installation Guide](INSTALLATION_GUIDE.md) - Complete installation steps and configuration instructions
- [Logto Setup](LOGTO_SETUP.md) - Quick setup guide for Logto authentication
- [Logto Changes](LOGTO_CHANGES.md) - Detailed changes for Logto integration
- [Implementation Status](IMPLEMENTATION_STATUS.md) - Current implementation progress and feature status

### 🔄 Migration Guides

Migrate from other systems to Xboard:

- [Migrate from v2board dev](docs/en/migration/v2board-dev.md)
- [Migrate from v2board 1.7.4](docs/en/migration/v2board-1.7.4.md)
- [Migrate from v2board 1.7.3](docs/en/migration/v2board-1.7.3.md)
- [Configuration Migration](docs/en/migration/config.md)

### 💾 Redis Cache Management

- [Redis Cache Guide](docs/REDIS_CACHE_GUIDE.md) - Complete cache management documentation
- [Redis Quick Reference](docs/REDIS_QUICK_REFERENCE.md) - Common commands and operations
- [Implementation Summary](REDIS_IMPLEMENTATION_SUMMARY.md) - Redis feature implementation details

### 🛠️ Development Documentation

- [Plugin Development Guide](docs/en/development/plugin-development-guide.md) - Develop Xboard plugins
- [Performance Optimization](docs/en/development/performance.md) - Performance optimization suggestions
- [Device Limit](docs/en/development/device-limit.md) - Device limit feature

### 🏗️ Architecture Documentation

- [Database Decentralization Analysis](docs/DATABASE_DECENTRALIZATION_ANALYSIS.md)
- [Hybrid Architecture Evaluation](docs/HYBRID_ARCHITECTURE_EVALUATION.md)

### 🔄 Upgrade Notice
> 🚨 **Important:** This version involves significant changes. Please strictly follow the upgrade documentation and backup your database before upgrading. Note that upgrading and migration are different processes, do not confuse them.

## 🛠️ Tech Stack

- Backend: Laravel 11 + Octane
- Admin Panel: React + Shadcn UI + TailwindCSS
- User Frontend: Vue3 + TypeScript + NaiveUI
- Deployment: Docker + Docker Compose
- Caching: Redis + Octane Cache

## 📷 Preview
![Admin Preview](./docs/images/admin.png)

![User Preview](./docs/images/user.png)

## ⚠️ Disclaimer

This project is for learning and communication purposes only. Users are responsible for any consequences of using this project.

## 🌟 Maintenance Notice

This project is currently under light maintenance. We will:
- Fix critical bugs and security issues
- Review and merge important pull requests
- Provide necessary updates for compatibility

However, new feature development may be limited.

## 🔔 Important Notes

1. Restart required after modifying admin path:
```bash
docker compose restart
```

2. For aaPanel installations, restart the Octane daemon process

## 🤝 Contributing

Issues and Pull Requests are welcome to help improve the project.

## 📈 Star History

[![Stargazers over time](https://starchart.cc/cedar2025/Xboard.svg)](https://starchart.cc/cedar2025/Xboard)

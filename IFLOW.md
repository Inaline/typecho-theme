# Inaline Typecho 主题开发文档

## 项目概述

Inaline 是一款功能强大、美观、简洁的 Typecho 主题。该主题注重用户体验和性能优化，提供了一个现代化的博客平台界面。

### 基本信息
- **主题名称**: Inaline Typecho Theme
- **开发者**: Inaline Studio
- **版本**: 1.0.0
- **项目地址**: https://gitee.com/inaline/typecho-theme
- **类型**: Typecho 博客主题

## 目录结构

```
Inaline/
├── functions.php          # 主题功能函数入口文件
├── index.php             # 主题主页模板文件
├── READMD.md             # 主题说明文档
├── IFLOW.md              # 本开发文档
├── core/                 # 主题核心功能目录
│   └── core.php          # 核心功能文件（目前为空）
├── components/           # 组件目录（目前为空）
├── library/              # 库文件目录
│   └── sitemap.php       # 站点地图生成功能
└── assets/               # 静态资源目录（目前为空）
```

## 核心文件说明

### 1. functions.php
- **功能**: 主题功能函数入口文件
- **作用**: 引入主题核心文件 (`core/core.php`)
- **安全检查**: 包含 `__TYPECHO_ROOT_DIR__` 安全常量检查
- **代码说明**:
  ```php
  <?php
  if (!defined('__TYPECHO_ROOT_DIR__')) exit;

  // 引入主题核心文件
  require_once("core/core.php");
  ```
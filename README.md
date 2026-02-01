<div align="center">

# Inaline Typecho Theme

### 功能强大、美观、简洁的 Typecho 主题

[![Typecho Version](https://img.shields.io/badge/Typecho-1.2.1-blue.svg)](https://typecho.org/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Gitee](https://img.shields.io/badge/Gitee-inaline%2Ftypecho--theme-red.svg)](https://gitee.com/inaline/typecho-theme)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange.svg)](https://gitee.com/inaline/typecho-theme)

[English](docs/README.en.md) | 简体中文

</div>

---

<div align="left">

![Screenshot](docs/images/homepage.png)

</div>

---

## ✨ 特性

- 🎨 **现代化设计** - 简洁美观的界面，支持深色模式
- 📱 **响应式布局** - 完美适配桌面端和移动端
- 🚀 **高性能** - 服务端渲染，快速加载
- 🔍 **内置搜索** - 支持文章搜索和搜索历史
- 📝 **Markdown 支持** - 完整支持 Markdown 语法
- 🧮 **数学公式** - 支持 KaTeX 数学公式渲染
- 💬 **评论系统** - 支持二级评论和回复功能
- 🏷️ **分类标签** - 多级分类和标签管理
- 📊 **数据统计** - 文章浏览量、点赞数等统计
- 🎯 **SEO 优化** - 内置 SEO 优化功能
- 🌐 **多语言** - 支持中英文界面

## 📸 截图

### 首页
![Home](docs/images/homepage-mobile.png)

### 文章页
![Article](docs/images/artical.png)

### 深色模式
![Dark Mode](docs/images/homepage-dark.png)

### 移动端
![Mobile](docs/images/homepage-mobile.png)

## 🚀 快速开始

### 环境要求

- PHP >= 7.4
- Typecho >= 1.2.0
- MySQL >= 5.7
- Apache/Nginx

### 安装步骤

1. **下载主题**

   ```bash
   git clone https://gitee.com/inaline/typecho-theme.git
   ```

   或访问 [Gitee Releases](https://gitee.com/inaline/typecho-theme/releases) 下载最新版本

2. **安装主题**

   - 将 `inaline` 文件夹上传到 Typecho 的 `/usr/themes/` 目录
   - 登录 Typecho 后台
   - 进入 `控制台` -> `外观` -> `启用主题`

3. **配置主题**

   - 进入 `控制台` -> `外观` -> `设置外观`
   - 根据需要配置主题选项

## 📖 使用文档

### 基础配置

主题提供了丰富的配置选项，包括：

- **站点信息** - 网站标题、描述、关键词
- **外观设置** - Logo、封面图、主题色
- **功能开关** - 搜索、评论、深色模式等
- **SEO 设置** - 自定义 SEO 标签

### 文章发布

支持以下 Markdown 语法：

- 标题、列表、引用、代码块
- 表格、任务列表
- 数学公式（KaTeX）
- 图片、链接、视频

### 评论系统

- 支持二级评论
- 支持回复功能
- 支持评论排序（最新/最早）
- 支持评论分页

## 🎨 自定义

### 修改主题色

在 `assets/css/style.css` 中修改 CSS 变量：

```css
:root {
    --primary-color: #FF7900;
    --primary-color-light: #FF9A40;
    --primary-color-dark: #E66A00;
}
```

### 添加自定义 CSS

在主题设置中添加自定义 CSS，或直接修改 `assets/css/style.css`

### 修改模板文件

主题使用组件化结构，主要模板文件：

- `index.php` - 首页
- `post.php` - 文章页
- `archive.php` - 归档页
- `404.php` - 404 页面

组件文件位于 `core/Components/` 目录

## 📦 项目结构

```
inaline/
├── assets/              # 静态资源
│   ├── css/            # 样式文件
│   ├── js/             # JavaScript 文件
│   ├── fonts/          # 字体文件
│   └── images/         # 图片资源
├── core/               # 核心功能
│   ├── Components/     # 组件
│   ├── Modules/        # 模块
│   └── Widgets/        # 小部件
├── library/            # 库文件
├── functions.php       # 主题函数
├── index.php          # 首页模板
├── post.php           # 文章页模板
├── archive.php        # 归档页模板
├── 404.php            # 404 页面模板
├── config.php         # 配置文件
└── README.md          # 说明文档
```

## 🔧 开发

### 本地开发

1. 克隆项目

```bash
git clone https://gitee.com/inaline/typecho-theme.git
cd typecho-theme
```

2. 在本地 Typecho 安装目录中创建符号链接

```bash
ln -s /path/to/typecho-theme /path/to/typecho/usr/themes/inaline
```

3. 修改代码后刷新页面查看效果

### 贡献指南

欢迎提交 Issue 和 Pull Request！

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 📝 更新日志

### v1.0.0 (2026-02-01)

#### 新增
- 🎉 首个正式版本发布
- ✨ 现代化响应式设计
- 🌙 深色模式支持
- 🔍 内置搜索功能
- 💬 二级评论系统
- 🧮 KaTeX 数学公式支持
- 📊 文章数据统计
- 🏷️ 多级分类和标签
- 📱 移动端适配
- 🎯 SEO 优化

#### 优化
- ⚡ 性能优化，提升加载速度
- 🎨 UI/UX 改进
- 🔧 代码结构优化

#### 修复
- 🐛 修复若干已知问题

## 🤝 技术支持

- 📧 邮箱: Inaline@qq.com
- 💬 QQ: 2291374016
- 🌐 官网: https://inaline.net
- 📦 Gitee: https://gitee.com/inaline/typecho-theme

## 📄 许可证

本项目采用 MIT 许可证。

### 许可条款

1. **版权声明** - 保留版权和许可声明
2. **署名要求** - 在网站页脚等显眼位置保留指向本项目的超链接
3. **免责声明** - 软件按"原样"提供，不提供任何明示或暗示的保证

### 多媒体资源声明

本许可证仅适用于项目的源代码文件和文档。所有多媒体资源（包括但不限于图片、字体、音频、视频、图标和其他媒体文件）**不**受此许可证保护。

这些多媒体资源可能来自互联网上的各种来源，可能受各自许可证和版权限制。用户有责任确保其使用的任何多媒体资源符合适用的许可证。本项目作者和版权持有人不对包含在或随本项目分发的多媒体资源的使用承担任何责任或义务。

有关多媒体资源的具体许可信息，请参阅各个资产文档或联系原始创作者。

完整许可证文本请参阅 [LICENSE](LICENSE) 文件。

## 🙏 致谢

感谢以下开源项目：

- [Typecho](https://typecho.org/) - 轻量级博客系统
- [KaTeX](https://katex.org/) - 快速的数学公式渲染库
- [Material Design Icons](https://materialdesignicons.com/) - Material Design 图标库
- [Bootstrap](https://getbootstrap.com/) - CSS 框架

## 📮 反馈

如果您有任何问题或建议，请：

- 提交 [Issue](https://gitee.com/inaline/typecho-theme/issues)
- 发送邮件至 Inaline@qq.com
- 加入 QQ 群讨论

---

<div align="center">

**Made with ❤️ by Inaline Studio**

[⬆ 回到顶部](#inaline-typecho-theme)

</div>
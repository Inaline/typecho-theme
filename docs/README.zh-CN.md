<div align="center">

# Inaline Typecho 主题

### 功能强大、美观、简洁的 Typecho 主题

[![Typecho 版本](https://img.shields.io/badge/Typecho-1.2.1-blue.svg)](https://typecho.org/)
[![许可证](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Gitee](https://img.shields.io/badge/Gitee-Inaline%2Ftypecho--theme-red.svg)](https://gitee.com/inaline/typecho-theme)
[![GitHub](https://img.shields.io/badge/GitHub-Inaline%2Ftypecho--theme-black.svg)](https://github.com/Inaline/typecho-theme)
[![版本](https://img.shields.io/badge/Version-1.1.2-orange.svg)](https://github.com/Inaline/typecho-theme)

[English](../README.md) | 简体中文

</div>

---

<div align="left">

![首页截图](docs/images/homepage.png)

</div>

---

## ✨ 特性

- 🎨 **现代设计** - 简洁美观的界面，支持深色模式
- 📱 **响应式布局** - 完美适配桌面和移动设备
- 🚀 **高性能** - 服务端渲染，快速加载
- 🔍 **内置搜索** - 文章搜索，支持搜索历史
- 📝 **Markdown 支持** - 完整的 Markdown 语法支持
- 🧮 **数学公式** - KaTeX 数学公式渲染
- 💬 **评论系统** - 嵌套评论和回复功能
- 🏷️ **分类标签** - 多级分类和标签管理
- 📊 **统计功能** - 文章浏览量、点赞等统计
- 🎯 **SEO 优化** - 内置 SEO 优化功能
- 🌐 **多语言** - 中英文界面支持

## 📸 截图

### 首页
![首页](docs/images/homepage.png)

### 文章页
![文章页](docs/images/artical.png)

### 深色模式
![深色模式](docs/images/homepage-dark.png)

### 移动端
![移动端](docs/images/homepage-mobile.png)

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

   - 进入 `控制台` -> `外观` -> `主题设置`
   - 根据需要配置主题选项

## 📖 使用文档

### 基础配置

主题提供丰富的配置选项，包括：

- **站点信息** - 站点标题、描述、关键词
- **外观设置** - Logo、封面图、主题色
- **功能开关** - 搜索、评论、深色模式等
- **SEO 设置** - 自定义 SEO 标签

### 发布文章

支持以下 Markdown 语法：

- 标题、列表、引用、代码块
- 表格、任务列表
- 数学公式（KaTeX）
- 图片、链接、视频

### 自定义语法

#### 1. 卡片语法

```markdown
%%{"type":"card","data":{"title":"卡片标题","content":"卡片内容（支持Markdown）"}}%%
```

#### 2. 折叠语法

```markdown
%%{"type":"collapse","data":{"title":"折叠标题","content":"折叠内容（支持Markdown）"}}%%
```

#### 3. 友链语法

```markdown
%%{"type":"links","data":[{"name":"网站名称","url":"https://example.com","description":"网站描述","avatar":"https://example.com/avatar.png"}]}%%
```

### 评论系统

- 支持嵌套评论
- 回复功能
- 评论排序（最新/最早）
- 评论分页

## 🎨 自定义

### 修改主题颜色

修改 `assets/css/style.css` 中的 CSS 变量：

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

主题采用组件化结构。主要模板文件：

- `index.php` - 首页
- `post.php` - 文章页
- `archive.php` - 归档页
- `404.php` - 404 页

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
│   └── Widgets/        # 小工具
├── library/            # 库文件
├── functions.php       # 主题函数
├── index.php          # 首页模板
├── post.php           # 文章页模板
├── archive.php        # 归档页模板
├── 404.php            # 404 页模板
├── config.php         # 配置文件
└── README.md          # 文档
```

## 🔧 开发

### 本地开发

1. 克隆项目

```bash
git clone https://gitee.com/inaline/typecho-theme.git
cd typecho-theme
```

2. 在本地 Typecho 安装目录创建符号链接

```bash
ln -s /path/to/typecho-theme /path/to/typecho/usr/themes/inaline
```

3. 修改代码后刷新页面查看效果

### 贡献代码

欢迎提交 Issue 和 Pull Request！

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 📝 更新日志

### v1.1.2 (2026-02-12)

#### 新增
- ✨ Sitemap TXT 格式支持（每行一个 URL）
- 📋 Nginx 伪静态规则（sitemap.xml 和 sitemap.txt）
- 🤖 robots.txt 配置建议
- 📁 Markdown 折叠语法支持
- 📝 编辑器工具栏折叠组件按钮
- 🔗 文章页面外部链接跳转保护提示
- 💬 所有 alert 对话框替换为 SweetAlert2
- 📋 复制版权提示（带可爱颜文字）

#### 改进
- 🎨 下拉菜单箭头位置优化（对齐到父项）
- 📱 复制功能 Android 设备兼容性
- 🔧 代码块复制按钮使用现代 Clipboard API

#### 修复
- 🐛 修复 editer.js 文件名拼写错误（现为 editor.js）

### v1.1.0 (2026-02-11)

#### 新增
- ✨ 主题配置导出/导入功能
- 🎨 自动检测系统深色模式偏好（Firefox 支持）
- 🔗 友链模板现在使用页面 slug 进行导航高亮
- ⚙️ 移除 page_name 自定义字段（替换为基于 slug 的方法）

#### 改进
- 🎨 文章头部布局重组（分类移至标题下方）
- 🧹 移除文章头部信息中的作者显示
- 🎯 简化分类显示（移除文件夹图标）

#### 修复
- 🐛 修复设置 page_name 自定义字段时的 404 错误
- 🔧 修复系统偏好检测时的深色模式 Logo 切换

### v1.0.1 (2026-02-09)

#### 新增
- ✨ 随机一言侧边栏卡片
- 🎨 改进图片资源引用

#### 修复
- ⚡ 修复 README 截图引用

### v1.0.0 (2026-01-17)

#### 新增
- 🎉 首个稳定版本发布
- ✨ 现代响应式设计
- 🌙 深色模式支持
- 🔍 内置搜索功能
- 💬 嵌套评论系统
- 🧮 KaTeX 数学公式支持
- 📊 文章统计
- 🏷️ 多级分类和标签
- 📱 移动端适配
- 🎯 SEO 优化
- 📝 说说功能
- 🔗 友链功能
- 📄 站点地图功能
- 🎵 播放器功能
- 📋 增强后台编辑器
- 📑 文章目录
- 💭 消息通知

#### 改进
- ⚡ 性能优化，加载更快
- 🎨 UI/UX 改进
- 🔧 代码结构优化

#### 修复
- 🐛 修复已知问题

## 🤝 支持

- 📧 邮箱: Inaline@qq.com
- 💬 QQ: 2291374016
- 🌐 网站: https://inaline.net
- 📦 Gitee: https://gitee.com/inaline/typecho-theme
- 📦 GitHub: https://github.com/Inaline/typecho-theme

## 📄 许可证

本项目采用 MIT 许可证。

### 许可条款

1. **版权声明** - 保留版权和许可声明
2. **署名要求** - 在网站页脚等显著位置保留指向本项目的超链接
3. **免责声明** - 软件按"原样"提供，不提供任何明示或暗示的保证

### 多媒体资源声明

本许可证仅适用于项目的源代码文件和文档。所有多媒体资源（包括但不限于图片、字体、音频、视频、图标和其他媒体文件）**不**受此许可证覆盖。

这些多媒体资源可能来自互联网的各种来源，可能受其各自的许可证和版权限制。用户有责任确保其使用的任何多媒体资源符合适用的许可证。项目作者和版权持有者不对包含在项目中或与项目一起分发的任何多媒体资源的使用承担任何责任或法律责任。

有关多媒体资源的具体许可信息，请参阅相应的资源文档或联系原始创作者。

完整的许可证文本，请参阅 [LICENSE](LICENSE) 文件。

## 🙏 致谢

感谢以下开源项目：

- [Typecho](https://typecho.org/) - 轻量级博客系统
- [KaTeX](https://katex.org/) - 快速数学公式渲染库
- [Material Design Icons](https://materialdesignicons.com/) - Material Design 图标库
- [Bootstrap](https://getbootstrap.com/) - CSS 框架

## 📮 反馈

如果您有任何问题或建议，请：

- 提交 [Issue](https://gitee.com/inaline/typecho-theme/issues)
- 发送邮件至 Inaline@qq.com
- 加入 QQ 群讨论

---

<div align="center">

**由 Inaline Studio 用 ❤️ 制作**

[⬆ 返回顶部](#inaline-typecho-主题)

</div>
<div align="center">

# Inaline Typecho Theme

### A powerful, beautiful, and simple Typecho theme

[![Typecho Version](https://img.shields.io/badge/Typecho-1.2.1-blue.svg)](https://typecho.org/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![GitHub](https://img.shields.io/badge/GitHub-Inaline%2Ftypecho--theme-black.svg)](https://github.com/Inaline/typecho-theme)
[![Version](https://img.shields.io/badge/Version-1.1.0-orange.svg)](https://github.com/Inaline/typecho-theme)

English | [简体中文](docs/README.en.md)

</div>

---

<div align="left">

![Screenshot](docs/images/homepage.png)

</div>

---

## ✨ Features

- 🎨 **Modern Design** - Clean and beautiful interface with dark mode support
- 📱 **Responsive Layout** - Perfectly adapted for desktop and mobile
- 🚀 **High Performance** - Server-side rendering for fast loading
- 🔍 **Built-in Search** - Article search with search history
- 📝 **Markdown Support** - Full Markdown syntax support
- 🧮 **Math Formula** - KaTeX math formula rendering
- 💬 **Comment System** - Nested comments and reply functionality
- 🏷️ **Categories & Tags** - Multi-level categories and tags management
- 📊 **Statistics** - Article views, likes, and other statistics
- 🎯 **SEO Optimized** - Built-in SEO optimization features
- 🌐 **Multi-language** - Chinese and English interface support

## 📸 Screenshots

### Homepage
![Home](docs/images/homepage.png)

### Article Page
![Article](docs/images/artical.png)

### Dark Mode
![Dark Mode](docs/images/homepage-dark.png)

## 🚀 Quick Start

### Requirements

- PHP >= 7.4
- Typecho >= 1.2.0
- MySQL >= 5.7
- Apache/Nginx

### Installation

1. **Download Theme**

   ```bash
   git clone https://github.com/Inaline/typecho-theme.git
   ```

   Or visit [GitHub Releases](https://github.com/Inaline/typecho-theme/releases) to download the latest version

2. **Install Theme**

   - Upload the `inaline` folder to Typecho's `/usr/themes/` directory
   - Log in to Typecho dashboard
   - Go to `Dashboard` -> `Appearance` -> `Enable Theme`

3. **Configure Theme**

   - Go to `Dashboard` -> `Appearance` -> `Theme Settings`
   - Configure theme options as needed

## 📖 Documentation

### Basic Configuration

The theme provides rich configuration options, including:

- **Site Information** - Site title, description, keywords
- **Appearance Settings** - Logo, cover image, theme color
- **Feature Toggles** - Search, comments, dark mode, etc.
- **SEO Settings** - Custom SEO tags

### Publishing Articles

Supports the following Markdown syntax:

- Headings, lists, quotes, code blocks
- Tables, task lists
- Math formulas (KaTeX)
- Images, links, videos

### Comment System

- Nested comments support
- Reply functionality
- Comment sorting (newest/oldest)
- Comment pagination

## 🎨 Customization

### Change Theme Color

Modify CSS variables in `assets/css/style.css`:

```css
:root {
    --primary-color: #FF7900;
    --primary-color-light: #FF9A40;
    --primary-color-dark: #E66A00;
}
```

### Add Custom CSS

Add custom CSS in theme settings, or directly modify `assets/css/style.css`

### Modify Template Files

The theme uses a component structure. Main template files:

- `index.php` - Homepage
- `post.php` - Article page
- `archive.php` - Archive page
- `404.php` - 404 page

Component files are located in `core/Components/` directory

## 📦 Project Structure

```
inaline/
├── assets/              # Static resources
│   ├── css/            # Style files
│   ├── js/             # JavaScript files
│   ├── fonts/          # Font files
│   └── images/         # Image resources
├── core/               # Core functionality
│   ├── Components/     # Components
│   ├── Modules/        # Modules
│   └── Widgets/        # Widgets
├── library/            # Library files
├── functions.php       # Theme functions
├── index.php          # Homepage template
├── post.php           # Article page template
├── archive.php        # Archive page template
├── 404.php            # 404 page template
├── config.php         # Configuration file
└── README.md          # Documentation
```

## 🔧 Development

### Local Development

1. Clone the project

```bash
git clone https://github.com/Inaline/typecho-theme.git
cd typecho-theme
```

2. Create symbolic link in local Typecho installation directory

```bash
ln -s /path/to/typecho-theme /path/to/typecho/usr/themes/inaline
```

3. Refresh page to see changes after modifying code

### Contributing

Issues and Pull Requests are welcome!

1. Fork this repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Changelog

### v1.1.0 (2026-02-11)

#### Added
- ✨ Theme settings export/import functionality
- 🎨 Auto-detect system dark mode preference (Firefox support)
- 🔗 Links template now uses page slug for navigation highlighting
- ⚙️ Removed page_name custom field (replaced with slug-based approach)

#### Improved
- 🎨 Article header layout reorganization (categories moved below title)
- 🧹 Removed author display from article header info
- 🎯 Simplified category display (removed folder icon)

#### Fixed
- 🐛 Fixed 404 error when page_name custom field was set
- 🔧 Fixed dark mode logo switching for system preference detection

### v1.0.1 (2026-02-09)

#### Added
- ✨ Random quote sidebar card
- 🎨 Improved image resource references

#### Fixed
- ⚡ Fixed README screenshot references

### v1.0.0 (2026-01-17)

#### Added
- 🎉 Initial stable release
- ✨ Modern responsive design
- 🌙 Dark mode support
- 🔍 Built-in search functionality
- 💬 Nested comment system
- 🧮 KaTeX math formula support
- 📊 Article statistics
- 🏷️ Multi-level categories and tags
- 📱 Mobile adaptation
- 🎯 SEO optimization
- 📝 Shuoshuo (Moments) feature
- 🔗 Friend links feature
- 📄 Sitemap functionality
- 🎵 Player feature
- 📋 Enhanced backend editor
- 📑 Article table of contents
- 💭 Message notifications

#### Improved
- ⚡ Performance optimization for faster loading
- 🎨 UI/UX improvements
- 🔧 Code structure optimization

#### Fixed
- 🐛 Fixed various known issues

## 🤝 Support

- 📧 Email: Inaline@qq.com
- 💬 QQ: 2291374016
- 🌐 Website: https://inaline.net
- 📦 GitHub: https://github.com/Inaline/typecho-theme

## 📄 License

This project is licensed under the MIT License.

### License Terms

1. **Copyright Notice** - Retain copyright and license notices
2. **Attribution Requirement** - Keep hyperlinks to this project in prominent locations such as the website footer
3. **Disclaimer** - The software is provided "as is", without any express or implied warranties

### Multimedia Resources Declaration

This license applies only to the project's source code files and documentation. All multimedia resources (including but not limited to images, fonts, audio, video, icons, and other media files) are **not** covered by this license.

These multimedia resources may come from various sources on the internet and may be subject to their respective licenses and copyright restrictions. Users are responsible for ensuring that any multimedia resources they use comply with applicable licenses. The project authors and copyright holders assume no responsibility or liability for the use of any multimedia resources included in or distributed with this project.

For specific licensing information regarding multimedia resources, please refer to the respective asset documentation or contact the original creators.

For the full license text, please see the [LICENSE](LICENSE) file.

## 🙏 Acknowledgments

Thanks to the following open source projects:

- [Typecho](https://typecho.org/) - Lightweight blogging system
- [KaTeX](https://katex.org/) - Fast math formula rendering library
- [Material Design Icons](https://materialdesignicons.com/) - Material Design icon library
- [Bootstrap](https://getbootstrap.com/) - CSS framework

## 📮 Feedback

If you have any questions or suggestions, please:

- Submit an [Issue](https://github.com/Inaline/typecho-theme/issues)
- Send an email to Inaline@qq.com
- Join the QQ group for discussion

---

<div align="center">

**Made with ❤️ by Inaline Studio**

[⬆ Back to Top](#inaline-typecho-theme)

</div>
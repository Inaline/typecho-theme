<?php
/**
 * Inaline 主题 404 错误页面组件
 * @author Inaline Studio
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - <?= $this->options->title ?></title>
    <link rel="icon" href="<?= e($this->options->siteUrl) ?>usr/themes/inaline/assets/images/logo/favicon.ico">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" type="text/css" href="<?= Get::Assets('assets/css/style.css') ?>">
    <style>
        /* 404 页面专用样式 */
        @font-face {
            font-family: 'HYTangMeiRen';
            src: url('<?= Get::Assets('assets/fonts/HYTangMeiRen55W.woff2') ?>') format('woff2');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        
        :root {
            --bg-color: #ffffff;
            --text-color: #333333;
            --text-color-light: #666666;
            --primary-color: #4ecdc4;
            --primary-color-dark: #3dbdb5;
            --border-radius: 12px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
            --font-primary: 'HYTangMeiRen', 'Noto Sans SC', sans-serif;
        }
        
        body.dark-mode {
            --bg-color: #1a1a1a;
            --text-color: #ffffff;
            --text-color-light: #b3b3b3;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4);
        }
        
        body {
            background-color: var(--bg-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: background-color var(--transition-speed);
        }
        
        .error-page-container {
            max-width: 600px;
            width: 100%;
            padding: 40px;
            background: var(--bg-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }
        
        .error-page-content {
            display: flex;
            align-items: center;
            gap: 24px;
            width: 100%;
        }
        
        .error-page-image {
            flex: 0 0 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-page-image img {
            width: 100%;
            height: auto;
            max-height: 200px;
            object-fit: contain;
        }
        
        .error-page-text {
            flex: 1;
            text-align: left;
        }
        
        .error-page-title {
            font-size: 56px;
            font-weight: 700;
            color: var(--text-color);
            margin: 0 0 8px 0;
            line-height: 1;
            font-family: var(--font-primary);
        }
        
        .error-page-subtitle {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-color-light);
            margin: 0 0 16px 0;
            font-family: var(--font-primary);
        }
        
        .error-page-description {
            font-size: 15px;
            color: var(--text-color-light);
            line-height: 1.6;
            margin: 0 0 24px 0;
            font-family: var(--font-primary);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 28px;
            border-radius: var(--border-radius);
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--transition-speed);
            border: none;
            outline: none;
            font-family: var(--font-primary);
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-color-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        @media (max-width: 768px) {
            .error-page-container {
                padding: 24px 20px;
            }
            
            .error-page-content {
                flex-direction: column;
                gap: 24px;
                text-align: center;
            }
            
            .error-page-image {
                flex: 0 0 auto;
                width: 200px;
            }
            
            .error-page-image img {
                max-height: 200px;
            }
            
            .error-page-text {
                text-align: center;
            }
            
            .error-page-title {
                font-size: 48px;
            }
            
            .error-page-subtitle {
                font-size: 20px;
            }
            
            .error-page-description {
                font-size: 14px;
            }
            
            .btn {
                width: 100%;
                max-width: 240px;
            }
        }
    </style>
    <script>
        // 检测系统暗色模式偏好
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark-mode');
        }
        // 监听系统主题变化
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (e.matches) {
                    document.documentElement.classList.add('dark-mode');
                } else {
                    document.documentElement.classList.remove('dark-mode');
                }
            });
        }
    </script>
</head>
<body>
    <div class="error-page-container">
        <div class="error-page-content">
            <div class="error-page-image">
                <img src="<?= Get::Assets('assets/images/xiaowenzi/404.png') ?>" alt="404" />
            </div>
            <div class="error-page-text">
                <h1 class="error-page-title">404</h1>
                <h2 class="error-page-subtitle"><?php _e('页面未找到'); ?></h2>
                <p class="error-page-description">
                    <?php _e('您访问的页面不存在或已被删除。'); ?>
                </p>
                <a href="<?= $this->options->siteUrl() ?>" class="btn btn-primary">
                    <span class="mdi mdi-home"></span>
                    <?php _e('返回首页'); ?>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
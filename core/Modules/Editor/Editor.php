<?php

// 获取后台目录名
$adminDir = defined('__TYPECHO_ADMIN_DIR__') ? basename(__TYPECHO_ADMIN_DIR__) : 'admin';

Typecho_Plugin::factory($adminDir . '/write-post.php')->bottom = array('Editor', 'edit');
Typecho_Plugin::factory($adminDir . '/write-page.php')->bottom = array('Editor', 'edit');

class Editor
{
    // 在页面插入编辑器的样式、图标库和 JavaScript 文件
    public static function edit()
    {
        // 引入 MDI 图标库
        echo '<link href="https://cdn.bootcdn.net/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css" rel="stylesheet">';
        // 引入编辑器样式
        echo '<link rel="stylesheet" href="' . GetSite::themeUrl("assets/css/editor.css") . '">';
        // 引入编辑器脚本
        echo '<script src="' . GetSite::themeUrl("assets/js/editer.js") . '"></script>';
    }
}
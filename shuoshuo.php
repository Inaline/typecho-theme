<?php
/**
 * 说说页面 - 类似朋友圈的个人动态展示
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 获取当前页码
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// 获取页面数据
$params_article = ComponentData::GetArticleData($this);

// 从自定义字段获取SEO信息
$customTitle = $params_article['fields']['seo_title'] ?? '';
$customDescription = $params_article['fields']['seo_description'] ?? '';
$customKeywords = $params_article['fields']['seo_keywords'] ?? '';

// 获取页面参数（使用首页样式）
$params_head = ComponentData::GetHeader(
    'shuoshuo',
    $this,
    $customTitle,
    $customDescription,
    $customKeywords
);
$params_top_bar = ComponentData::GetTopBar('shuoshuo');
$params_foot = ComponentData::GetFooter('shuoshuo', $this);
$params_sidebar = ComponentData::GetSidebarData('home'); // 使用首页侧边栏样式

// 获取说说列表数据
$params_shuoshuo_list = ComponentData::GetShuoshuoListData($currentPage, 10);

// 渲染头部组件
Get::Component($this, 'Header', $params_head);
Get::Component($this, 'TopBar', $params_top_bar);

// 渲染说说头部组件（无大图标题）
Get::Component($this, 'ShuoshuoHeader', [
    'title' => '说说',
    'cover' => ''
]);

Get::Component($this, 'Common', ['type' => 'main-start']);
Get::Component($this, 'Common', ['type' => 'wrapper-start']);
Get::Component($this, 'Common', ['type' => 'content-column-start']);

// 渲染说说列表组件
Get::Component($this, 'ShuoshuoList', $params_shuoshuo_list);

Get::Component($this, 'Common', ['type' => 'content-column-end']);
Get::Component($this, 'Common', ['type' => 'sidebar-column-start']);

// 循环渲染侧边栏卡片
foreach ($params_sidebar as $widget) {
    Get::Component($this, 'Sidebar', $widget);
}

Get::Component($this, 'Common', ['type' => 'sidebar-column-end']);
Get::Component($this, 'Common', ['type' => 'wrapper-end']);
Get::Component($this, 'Common', ['type' => 'main-end']);
Get::Component($this, 'Footer', $params_foot);
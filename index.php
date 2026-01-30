<?php
/**
 * 功能强大、美观、简洁的 Typecho 主题
 *
 * @package Inaline Typecho Theme
 * @author Inaline Studio
 * @version 1.0.0
 * @link https://gitee.com/inaline/typecho-theme
 *
 */

// [DEBUG] 现在页面直接 echo 输出的都是调试信息，不得用于生产环境

$params_head = ComponentData::GetHeader('home', $this);
$params_top_bar = ComponentData::GetTopBar('home');
$params_foot = ComponentData::GetFooter('home', $this);
$params_carousel = ComponentData::GetCarouselData();
$params_sidebar = ComponentData::GetSidebarData();

Get::Component($this, 'Header', $params_head);
Get::Component($this, 'TopBar', $params_top_bar);
Get::Component($this, 'Common', ['type' => 'main-start']);
Get::Component($this, 'Common', ['type' => 'wrapper-start']);
Get::Component($this, 'Common', ['type' => 'content-column-start']);

// 未开启轮播图或者没写值就不显示
if ($params_carousel['enabled'] && !empty($params_carousel['items'])) {
    Get::Component($this, 'Widgets/CarouselWidget', $params_carousel);
}

// 获取文章列表组件参数
$params_article_list = ComponentData::GetArticleListData();

Get::Component($this, 'ArticleList', $params_article_list);
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
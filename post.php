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

$params_head = ComponentData::GetHeader('post', $this);
$params_top_bar = ComponentData::GetTopBar('post');
$params_foot = ComponentData::GetFooter('post', $this);
$params_sidebar = ComponentData::GetSidebarData('post');

Get::Component($this, 'Header', $params_head);
Get::Component($this, 'TopBar', $params_top_bar);

// 获取文章数据
$params_article = ComponentData::GetArticleData($this);

// 渲染文章头部
Get::Component($this, 'ArticleHeader', $params_article);

Get::Component($this, 'Common', ['type' => 'main-start']);
Get::Component($this, 'Common', ['type' => 'wrapper-start']);
Get::Component($this, 'Common', ['type' => 'content-column-start']);

// 获取文章数据
$params_article = ComponentData::GetArticleData($this);

// 渲染文章阅读器组件
Get::Component($this, 'ArticleReader', $params_article);

// 获取评论参数
$commentPage = isset($_GET['comments-page']) ? intval($_GET['comments-page']) : 1;
$commentOrder = isset($_GET['comments-order']) ? $_GET['comments-order'] : 'desc';

// 获取评论数据
$params_comment = ComponentData::GetCommentData(
    $params_article['cid'],
    $commentPage,
    10, // 每页10条评论
    $commentOrder
);

// 渲染评论列表组件
Get::Component($this, 'CommentList', $params_comment);

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
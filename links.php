<?php

/**
 * 友链 (无大图，有评论区)
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

$params_head = ComponentData::GetHeader('links', $this);
$params_top_bar = ComponentData::GetTopBar('links');
$params_foot = ComponentData::GetFooter('links', $this);
$params_sidebar = ComponentData::GetSidebarData('links');

Get::Component($this, 'Header', $params_head);
Get::Component($this, 'TopBar', $params_top_bar);

Get::Component($this, 'Common', ['type' => 'main-start']);
Get::Component($this, 'Common', ['type' => 'wrapper-start']);
Get::Component($this, 'Common', ['type' => 'content-column-start']);

// 获取页面数据（使用 GetArticleData 方法以支持自定义语法）
$params_article = ComponentData::GetArticleData($this);

// 输出页面内容
echo '<div class="card">';
echo '<div class="card-content">';
echo '<div class="markdown-content link-content">';
echo $params_article['content'];
echo '</div>';
echo '</div>';
echo '</div>';

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
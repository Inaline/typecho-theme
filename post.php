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

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 获取文章数据（只获取一次，避免重复增加阅读量）
$params_article = ComponentData::GetArticleData($this);

// 获取文章类型
$articleType = $params_article['fields']['article_type'] ?? 'article';

// 根据文章类型选择不同的头部和侧边栏
if ($articleType === 'shuoshuo') {
    // 说说类型：使用说说头部和首页侧边栏
    $params_head = ComponentData::GetHeader('shuoshuo', $this);
    $params_top_bar = ComponentData::GetTopBar('shuoshuo');
    $params_foot = ComponentData::GetFooter('shuoshuo', $this);
    $params_sidebar = ComponentData::GetSidebarData('home');
} else {
    // 文章类型：使用文章头部和文章侧边栏
    $params_head = ComponentData::GetHeader('post', $this);
    $params_top_bar = ComponentData::GetTopBar('post');
    $params_foot = ComponentData::GetFooter('post', $this);
    $params_sidebar = ComponentData::GetSidebarData('post');
}

Get::Component($this, 'Header', $params_head);
Get::Component($this, 'TopBar', $params_top_bar);

// 根据文章类型渲染不同的内容
if ($articleType === 'shuoshuo') {
    // 说说类型：显示说说UI
    $shuoshuoCover = Get::themeOption('shuoshuo_cover', Get::Assets('assets/images/cover/cover1.jpg'));
    $shuoshuoCover = Get::resolveUri($shuoshuoCover);

    Get::Component($this, 'ShuoshuoHeader', [
        'title' => '说说',
        'cover' => $shuoshuoCover
    ]);
} else {
    // 文章类型：显示文章UI
    // 渲染文章头部组件（在 main-container 外面）
    Get::Component($this, 'ArticleHeader', [
        'title' => $this->title,
        'categories' => $this->categories,
        'date' => date('Y-m-d', $this->created),
        'views' => $params_article['views'],
        'comments' => $params_article['comments'],
        'thumbnail' => $params_article['thumbnail'] ?? ''
    ]);
}

Get::Component($this, 'Common', ['type' => 'main-start']);
Get::Component($this, 'Common', ['type' => 'wrapper-start']);
Get::Component($this, 'Common', ['type' => 'content-column-start']);

// 根据文章类型渲染不同的内容
if ($articleType === 'shuoshuo') {
    // 使用 ShuoshuoList 组件显示单个说说
    $shuoshuoData = ComponentData::GetShuoshuoListData(1, 1, $this->cid);
    Get::Component($this, 'ShuoshuoList', $shuoshuoData);
} else {
    // 渲染文章阅读器组件
    Get::Component($this, 'ArticleReader', $params_article);
}

// 渲染评论列表组件
Get::Component($this, 'CommentList', ComponentData::GetCommentData($this->cid, 1, 10, 'desc'));

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
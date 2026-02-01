<?php

/**
 * 友链 (无标题栏，有评论区)
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 获取页面数据
$params_article = ComponentData::GetArticleData($this);

// 获取自定义 SEO 信息
$custom_title = '';
$custom_description = '';
$custom_keywords = '';

if (isset($this->cid) && $this->cid) {
    try {
        $db = \Typecho\Db::get();
        $fields = $db->fetchAll($db->select('name', 'str_value')
            ->from('table.fields')
            ->where('cid = ?', $this->cid)
            ->where('name IN ?', ['seo_title', 'seo_keywords', 'seo_description']));

        foreach ($fields as $field) {
            if ($field['name'] === 'seo_title' && !empty($field['str_value'])) {
                $custom_title = $field['str_value'];
            }
            if ($field['name'] === 'seo_keywords' && !empty($field['str_value'])) {
                $custom_keywords = $field['str_value'];
            }
            if ($field['name'] === 'seo_description' && !empty($field['str_value'])) {
                $custom_description = $field['str_value'];
            }
        }
    } catch (Exception $e) {
        // 忽略错误
    }
}

$params_head = ComponentData::GetHeader('links', $this, $custom_title, $custom_description, $custom_keywords);
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

// 输出页面内容 - 使用与文章页面一致的结构（除了底部的文章信息）
echo '<div class="article-reader" data-cid="' . $params_article['cid'] . '">';
echo '<div class="card article-reader-content">';
echo '<div class="article-content markdown-content">';
echo $params_article['content'];
echo '</div>';

// 文章标签（如果有）
if (!empty($params_article['tags'])) {
    echo '<div class="article-tags">';
    echo '<span class="article-tags-label">';
    echo '<span class="mdi mdi-tag-multiple"></span> 标签：';
    echo '</span>';
    foreach ($params_article['tags'] as $tag) {
        echo '<a href="' . htmlspecialchars($tag['url']) . '" class="article-tag" title="' . htmlspecialchars($tag['name']) . '">';
        echo htmlspecialchars($tag['name']);
        echo '</a>';
    }
    echo '</div>';
}

// 分享按钮
echo '<div class="article-actions">';
echo '<button class="article-action-btn share-btn">';
echo '<span class="mdi mdi-share-variant"></span>';
echo '<span class="article-action-text">分享</span>';
echo '</button>';
echo '</div>';

// 注意：不输出文章信息部分
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
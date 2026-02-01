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

// 获取归档页面标题和类型
$archive_title = '';
$archive_description = '';
$archive_type = '';
$current_page = 'archive'; // 默认当前页面
$category_mid = 0; // 初始化分类 mid
$category_path_slugs = []; // 初始化分类路径 slugs

if ($this->is('category')) {
    $archive_type = 'category';
    $category_name = '';
    
    // 从 URL 中提取当前访问的分类 slug
    $request_path = $this->request->getPathInfo();
    $request_path = trim($request_path, '/');
    $path_parts = explode('/', $request_path);
    $current_slug = end($path_parts);
    
    // 尝试从 categories 数组获取 - 匹配当前 slug
    if (isset($this->categories) && is_array($this->categories) && !empty($this->categories)) {
        foreach ($this->categories as $cat) {
            if ($cat['slug'] === $current_slug) {
                $category_name = $cat['name'];
                $current_page = $cat['slug'];
                $category_mid = $cat['mid'];
                break;
            }
        }
    }
    
    // 如果没有找到，尝试通过 URL slug 从数据库查询
    if (empty($category_mid) && !empty($current_slug)) {
        $db = \Typecho_Db::get();
        $row = $db->fetchRow($db->select('mid', 'name', 'slug')->from('table.metas')
            ->where('slug = ?', $current_slug)
            ->where('type = ?', 'category')
            ->limit(1));
        if ($row) {
            $category_name = $row['name'];
            $current_page = $row['slug'];
            $category_mid = $row['mid'];
        }
    }
    
    // 如果没有找到，尝试通过 mid 获取
    if (empty($category_mid) && isset($this->mid) && $this->mid) {
        $db = \Typecho_Db::get();
        $row = $db->fetchRow($db->select('mid', 'name', 'slug')->from('table.metas')
            ->where('mid = ?', $this->mid)
            ->where('type = ?', 'category')
            ->limit(1));
        if ($row) {
            $category_name = $row['name'];
            $current_page = $row['slug'];
            $category_mid = $row['mid'];
        }
    }
    
    // 如果通过 mid 查询失败，尝试通过 slug 查询
    if (empty($category_name) && isset($this->slug) && $this->slug) {
        $db = \Typecho_Db::get();
        $row = $db->fetchRow($db->select('mid', 'name', 'slug')->from('table.metas')
            ->where('slug = ?', $this->slug)
            ->where('type = ?', 'category')
            ->limit(1));
        if ($row) {
            $category_name = $row['name'];
            $current_page = $row['slug'];
            $category_mid = $row['mid'];
        }
    }
    
    // 如果通过 slug 查询失败，尝试通过 name 查询
    if (empty($category_name) && isset($this->category) && $this->category) {
        $db = \Typecho_Db::get();
        $row = $db->fetchRow($db->select('mid', 'name', 'slug')->from('table.metas')
            ->where('name = ?', $this->category)
            ->where('type = ?', 'category')
            ->limit(1));
        if ($row) {
            $category_name = $row['name'];
            $current_page = $row['slug'];
            $category_mid = $row['mid'];
        }
    }
    
    // 如果仍然没有获取到分类名称，使用 $this->category 作为后备
    if (empty($category_name) && isset($this->category) && $this->category) {
        $category_name = $this->category;
    }
    
    $archive_title = $category_name;
    $archive_description = '分类 "' . $category_name . '" 下的文章';
    
    // 获取分类路径（用于高亮父分类）
    $category_path_slugs = [];
    if ($category_mid > 0) {
        $category_path = GetCategory::path($category_mid, ['slug']);
        foreach ($category_path as $cat) {
            $category_path_slugs[] = $cat['slug'];
        }
    }
} elseif ($this->is('tag')) {
    $archive_type = 'tag';
    $archive_title = $this->tag;
    $archive_description = '标签 "' . $this->tag . '" 下的文章';
    // 获取标签 slug - 使用 Typecho 的方式
    $current_page = '';
    if (isset($this->tags) && is_array($this->tags) && !empty($this->tags)) {
        $current_page = $this->tags[0]['slug'];
    } else {
        // 如果 tags 为空，尝试通过标签名称查找
        $db = \Typecho_Db::get();
        $row = $db->fetchRow($db->select('mid', 'slug')->from('table.metas')
            ->where('name = ?', $this->tag)
            ->where('type = ?', 'tag')
            ->limit(1));
        if ($row) {
            $current_page = $row['slug'];
        }
    }
    $category_path_slugs = [];
} elseif ($this->is('search')) {
    $archive_type = 'search';
    $archive_title = $this->keywords;
    $archive_description = '包含关键词 "' . $this->keywords . '" 的文章';
} elseif ($this->is('author')) {
    $archive_type = 'author';
    $archive_title = $this->author->name;
    $archive_description = $this->author->name . ' 发布的文章';
    $category_path_slugs = [];
} elseif ($this->is('date')) {
    $archive_type = 'date';
    $archive_title = $this->archiveTitle([
        'year'  => _t('%s 年'),
        'month' => _t('%s 月'),
        'day'   => _t('%s 日')
    ], '', '');
    $archive_description = $archive_title . ' 的文章';
    $category_path_slugs = [];
} else {
    $archive_type = 'archive';
    $archive_title = '文章归档';
    $archive_description = '所有文章归档';
    $category_path_slugs = [];
}

// 使用正确的 current_page 参数
// 对于分类页面，不传递 current_page，避免高亮导航栏中的页面
if ($archive_type === 'category') {
    $params_top_bar = ComponentData::GetTopBar('', $category_path_slugs);
    $params_sidebar = ComponentData::GetSidebarData('');
} else {
    $params_top_bar = ComponentData::GetTopBar($current_page, $category_path_slugs);
    $params_sidebar = ComponentData::GetSidebarData($current_page);
}

// 构建页面标题、描述和关键词
$page_title = '';
$page_description = '';
$page_keywords = '';
$site_name = $this->options->title;

if ($archive_type === 'category') {
    $page_title = $archive_title . ' - 文章归档 - ' . $site_name;
    $page_description = $archive_description;
    $page_keywords = $archive_title;
} elseif ($archive_type === 'tag') {
    $page_title = $archive_title . ' - 文章归档 - ' . $site_name;
    $page_description = $archive_description;
    $page_keywords = $archive_title;
} elseif ($archive_type === 'search') {
    $page_title = '搜索: ' . $archive_title . ' - 文章归档 - ' . $site_name;
    $page_description = $archive_description;
    $page_keywords = $archive_title;
} elseif ($archive_type === 'author') {
    $page_title = $archive_title . ' - 作者归档 - ' . $site_name;
    $page_description = $archive_description;
    $page_keywords = $archive_title;
} elseif ($archive_type === 'date') {
    $page_title = $archive_title . ' - 时间归档 - ' . $site_name;
    $page_description = $archive_description;
    $page_keywords = $archive_title;
} else {
    $page_title = $archive_title . ' - ' . $site_name;
    $page_description = $archive_description;
    $page_keywords = '';
}

// 重新获取 Header，使用正确的标题、描述和关键词
$params_head = ComponentData::GetHeader('archive', $this, $page_title, $page_description, $page_keywords);
$params_foot = ComponentData::GetFooter('archive', $this);

Get::Component($this, 'Header', $params_head);
Get::Component($this, 'TopBar', $params_top_bar);

Get::Component($this, 'Common', ['type' => 'main-start']);
Get::Component($this, 'Common', ['type' => 'wrapper-start']);
Get::Component($this, 'Common', ['type' => 'content-column-start']);

// 获取归档文章列表组件参数
$keywords = $archive_type === 'search' ? ($this->keywords ?? '') : '';
$params_article_list = ComponentData::GetArchiveListData($this, $archive_type, $category_mid, $keywords);

// 归档页面标题展示
echo '<div class="archive-header">';
echo '<h1 class="archive-title">' . htmlspecialchars($archive_title) . '</h1>';
echo '<p class="archive-description">' . htmlspecialchars($archive_description) . '</p>';
echo '</div>';

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
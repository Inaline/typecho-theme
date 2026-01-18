<?php
/**
 * Inaline 主题 TopBar 的 component
 * @author Inaline Studio
 */
// 解析页面配置 JSON
$pages = isset($this->data->pages) ? json_decode($this->data->pages, true) : [];
if (!is_array($pages)) {
    $pages = [];
}

// 解析分类配置 JSON
$categories = isset($this->data->categories) ? json_decode($this->data->categories, true) : [];
if (!is_array($categories)) {
    $categories = [];
}

// 获取当前页面
$currentPage = isset($this->data->current_page) ? $this->data->current_page : '';

// 递归渲染导航项的辅助函数
function renderNavItem($item, $depth = 0, $currentPage = '') {
    $hasChildren = isset($item['children']) && is_array($item['children']) && !empty($item['children']);
    $icon = isset($item['icon']) ? $item['icon'] : '';
    $label = isset($item['label']) ? $item['label'] : '';
    $url = isset($item['url']) ? $item['url'] : '#';
    $itemName = isset($item['name']) ? $item['name'] : '';
    
    // 检查是否为当前页面或其子页面
    $isActive = false;
    if ($itemName === $currentPage) {
        $isActive = true;
    } elseif ($hasChildren) {
        // 检查子页面是否包含当前页面
        foreach ($item['children'] as $child) {
            if (isset($child['name']) && $child['name'] === $currentPage) {
                $isActive = true;
                break;
            }
        }
    }
    
    if ($hasChildren) {
        // 下拉菜单
        echo '<div class="topbar-nav-item topbar-nav-dropdown';
        if ($isActive) echo ' active';
        echo '">';
        echo '<span class="topbar-nav-link">';
        if ($icon && $depth === 0) {
            echo '<span class="mdi ' . htmlspecialchars($icon) . ' topbar-nav-icon"></span>';
        }
        echo htmlspecialchars($label) . '</span>';
        echo '<span class="mdi mdi-chevron-down topbar-nav-arrow"></span>';
        echo '<div class="topbar-dropdown-menu">';
        foreach ($item['children'] as $child) {
            $childLabel = isset($child['label']) ? $child['label'] : '';
            $childUrl = isset($child['url']) ? $child['url'] : '#';
            $childName = isset($child['name']) ? $child['name'] : '';
            $childActive = ($childName === $currentPage) ? ' active' : '';
            echo '<a href="' . htmlspecialchars($childUrl) . '" class="topbar-dropdown-item' . $childActive . '"';
            echo '>' . htmlspecialchars($childLabel) . '</a>';
        }
        echo '</div></div>';
    } else {
        // 普通链接
        echo '<a href="' . htmlspecialchars($url) . '" class="topbar-nav-item';
        if ($isActive) echo ' active';
        if ($icon && $depth === 0) {
            echo ' data-icon="' . htmlspecialchars($icon) . '"';
        }
        echo '>';
        if ($icon && $depth === 0) {
            echo '<span class="mdi ' . htmlspecialchars($icon) . ' topbar-nav-icon"></span>';
        }
        echo htmlspecialchars($label) . '</a>';
    }
}

// 递归渲染侧边栏链接的辅助函数
function renderSidebarItem($item, $depth = 0, $currentPage = '') {
    $hasChildren = isset($item['children']) && is_array($item['children']) && !empty($item['children']);
    $icon = isset($item['icon']) ? $item['icon'] : 'mdi-file';
    $label = isset($item['label']) ? $item['label'] : '';
    $url = isset($item['url']) ? $item['url'] : '#';
    $itemName = isset($item['name']) ? $item['name'] : '';
    
    // 检查是否为当前页面或其子页面
    $isActive = false;
    if ($itemName === $currentPage) {
        $isActive = true;
    } elseif ($hasChildren) {
        // 检查子页面是否包含当前页面
        foreach ($item['children'] as $child) {
            if (isset($child['name']) && $child['name'] === $currentPage) {
                $isActive = true;
                break;
            }
        }
    }
    
    if ($hasChildren) {
        // 树形结构
        echo '<div class="sidebar-tree';
        if ($isActive) echo ' active';
        echo '">';
        echo '<div class="sidebar-tree-item">';
        echo '<div class="sidebar-tree-header';
        if ($isActive) echo ' active';
        echo '">';
        if ($depth === 0) {
            echo '<span class="sidebar-tree-item-icon mdi ' . htmlspecialchars($icon) . '"></span>';
        }
        echo '<span class="sidebar-tree-label">' . htmlspecialchars($label) . '</span>';
        echo '<span class="sidebar-tree-icon mdi mdi-chevron-right"></span>';
        echo '</div>';
        echo '<div class="sidebar-tree-children';
        if ($isActive) echo ' active';
        echo '">';
        foreach ($item['children'] as $child) {
            renderSidebarItem($child, $depth + 1, $currentPage);
        }
        echo '</div></div></div>';
    } else {
        // 普通链接
        echo '<a href="' . htmlspecialchars($url) . '" class="sidebar-link';
        if ($isActive) echo ' active';
        echo '">';
        if ($depth === 0) {
            echo '<span class="mdi ' . htmlspecialchars($icon) . '"></span>';
        }
        echo htmlspecialchars($label);
        echo '</a>';
    }
}
?>

<!-- TopBar -->
<header class="topbar">
    <div class="topbar-container">
        <!-- 移动端菜单按钮 -->
        <button class="topbar-btn" id="menuBtn">
            <span class="mdi mdi-menu"></span>
        </button>

        <!-- Logo -->
        <div class="topbar-logo">
            <img src="<?=e($this->data->logo, Get::Assets('assets/images/logo/Inaline.png')) ?>" 
                 alt="Logo"
                 data-light-logo="<?=e($this->data->logo, Get::Assets('assets/images/logo/Inaline.png')) ?>"
                 data-dark-logo="<?=e($this->data->logo_dark, Get::Assets('assets/images/logo/Inaline-dark.png')) ?>">
        </div>

        <!-- 桌面端导航标签 -->
        <nav class="topbar-nav">
            <?php
            foreach ($pages as $page) {
                renderNavItem($page, 0, $currentPage);
            }
            ?>
        </nav>

        <!-- 右侧按钮组 -->
        <div class="topbar-actions">
            <!-- 搜索按钮 -->
            <button class="topbar-btn" id="searchBtn">
                <span class="mdi mdi-magnify"></span>
            </button>

            <!-- 更多选项按钮 -->
            <button class="topbar-btn" id="moreIcon">
                <span class="mdi mdi-dots-vertical"></span>
            </button>

            <!-- 更多选项菜单 -->
            <div class="more-menu" id="moreMenu">
                <div class="more-menu-item">
                    <span class="mdi mdi-cog more-menu-icon"></span>
                    <span>设置</span>
                </div>
                <div class="more-menu-item dark-mode-toggle">
                    <span class="mdi mdi-brightness-6 more-menu-icon"></span>
                    <span>暗色模式</span>
                </div>
                <div class="more-menu-divider"></div>
                <div class="more-menu-item">
                    <span class="mdi mdi-login more-menu-icon"></span>
                    <span>登录</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 搜索框 -->
    <div class="topbar-search" id="searchBox">
        <div class="topbar-search-content">
            <!-- 搜索卡片 -->
            <div class="search-card">
                <!-- 搜索输入框 -->
                <div class="search-input-wrapper">
                    <span class="mdi mdi-magnify search-icon"></span>
                    <input type="text" id="searchInput" class="search-input" placeholder="搜索文章、标签..." autocomplete="off">
                    <button id="clearSearch" class="search-clear" style="display: none;">
                        <span class="mdi mdi-close"></span>
                    </button>
                    <button id="searchSubmit" class="search-submit">搜索</button>
                </div>

                <!-- 搜索历史 -->
                <div class="search-history" id="searchHistory">
                    <div class="search-section-header">
                        <span class="search-section-title">搜索历史</span>
                        <button class="search-clear-all" id="clearAllHistory">
                            <span class="mdi mdi-trash-can-outline"></span>
                            清空
                        </button>
                    </div>
                    <div class="search-history-list" id="historyList">
                        <!-- 搜索历史项将通过 JS 动态生成 -->
                    </div>
                </div>

                <!-- 热搜 -->
                <div class="search-hot">
                    <div class="search-section-header">
                        <span class="search-section-title">热搜</span>
                    </div>
                    <div class="search-hot-list">
                        <div class="search-hot-item">
                            <span class="search-hot-rank hot-rank-1">1</span>
                            <span class="search-hot-text">Typecho主题开发</span>
                        </div>
                        <div class="search-hot-item">
                            <span class="search-hot-rank hot-rank-2">2</span>
                            <span class="search-hot-text">PHP编程技巧</span>
                        </div>
                        <div class="search-hot-item">
                            <span class="search-hot-rank hot-rank-3">3</span>
                            <span class="search-hot-text">前端框架</span>
                        </div>
                        <div class="search-hot-item">
                            <span class="search-hot-rank">4</span>
                            <span class="search-hot-text">响应式设计</span>
                        </div>
                        <div class="search-hot-item">
                            <span class="search-hot-rank">5</span>
                            <span class="search-hot-text">性能优化</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 搜索/菜单遮罩 -->
    <div class="search-overlay" id="searchOverlay"></div>
</header>

<!-- 左侧边栏 -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <!-- 用户信息 -->
        <div class="sidebar-user">
            <div class="sidebar-user-status"><?= e($this->data->sidebar_user_status, 'EMOing') ?></div>
            <div class="sidebar-avatar">
                <img src="<?= e($this->data->sidebar_user_avatar, 'http://q1.qlogo.cn/g?b=qq&nk=2291374026&s=640') ?>" alt="用户头像">
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= e($this->data->sidebar_user_name, 'Inaline') ?></div>
                <div class="sidebar-user-bio"><?= e($this->data->sidebar_user_bio, '昔人已乘黄鹤去，此地空余黄鹤楼') ?></div>
            </div>
            <div class="sidebar-stats">
                <div class="sidebar-stat-item">
                    <div class="sidebar-stat-value"><?= $this->data->article_count ?></div>
                    <div class="sidebar-stat-label">文章</div>
                </div>
                <div class="sidebar-stat-item">
                    <div class="sidebar-stat-value"><?= $this->data->comment_count ?></div>
                    <div class="sidebar-stat-label">评论</div>
                </div>
            </div>
        </div>

        <!-- 选项卡 -->
        <div class="sidebar-tabs">
            <div class="sidebar-tab active" data-tab="pages">页面</div>
            <div class="sidebar-tab" data-tab="categories">分类</div>
        </div>

        <!-- 选项卡内容（可滚动区域） -->
        <div class="sidebar-tab-content">
            <!-- 页面列表 -->
            <div class="sidebar-tab-pane active" id="pages">
                <?php
                foreach ($pages as $page) {
                    renderSidebarItem($page, 0, $currentPage);
                }
                ?>
            </div>

            <!-- 分类列表 -->
            <div class="sidebar-tab-pane" id="categories">
                <?php
                foreach ($categories as $category) {
                    renderSidebarItem($category, 0, $currentPage);
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- 侧边栏遮罩 -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php
/**
 * Inaline 主题 TopBar 的 component
 * @author Inaline Studio
 */
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
            <img src="<?=e($this->data->logo, Get::Assets('assets/images/logo/Inaline.png')) ?>" alt="Logo">
        </div>

        <!-- 桌面端导航标签 -->
        <nav class="topbar-nav">
            <a href="#home" class="topbar-nav-item active">首页</a>
            <div class="topbar-nav-item topbar-nav-dropdown">
                <span class="topbar-nav-link">分类</span>
                <span class="mdi mdi-chevron-down topbar-nav-arrow"></span>
                <div class="topbar-dropdown-menu">
                    <a href="#category-tech" class="topbar-dropdown-item" data-parent="categories">技术</a>
                    <a href="#category-life" class="topbar-dropdown-item" data-parent="categories">生活</a>
                    <a href="#category-travel" class="topbar-dropdown-item" data-parent="categories">旅行</a>
                </div>
            </div>
            <a href="#about" class="topbar-nav-item">关于</a>
            <a href="#contact" class="topbar-nav-item">联系</a>
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
        </div>
    </div>

    <!-- 搜索框 -->
    <div class="topbar-search" id="searchBox">
        <div class="topbar-search-content">
            <!-- 搜索卡片 -->
            <div class="search-card"></div>
        </div>
    </div>

    <!-- 搜索/菜单遮罩 -->
    <div class="search-overlay" id="searchOverlay"></div>
</header>

<!-- 左侧边栏 -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-content"></div>
</div>

<!-- 侧边栏遮罩 -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- 更多选项菜单 -->
<div class="more-menu" id="moreMenu">
    <div class="more-menu-item">设置</div>
    <div class="more-menu-item">暗色模式</div>
    <div class="more-menu-divider"></div>
    <div class="more-menu-item">登录</div>
</div>
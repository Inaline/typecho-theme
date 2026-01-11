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

            <!-- 更多选项菜单 -->
            <div class="more-menu" id="moreMenu">
                <div class="more-menu-item">
                    <span class="mdi mdi-cog more-menu-icon"></span>
                    <span>设置</span>
                </div>
                <div class="more-menu-item">
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
            <div class="search-card"></div>
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
            <div class="sidebar-avatar">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" alt="用户头像">
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">Inaline</div>
                <div class="sidebar-user-bio">热爱技术，分享生活</div>
            </div>
            <div class="sidebar-stats">
                <div class="sidebar-stat-item">
                    <div class="sidebar-stat-value">128</div>
                    <div class="sidebar-stat-label">文章</div>
                </div>
                <div class="sidebar-stat-item">
                    <div class="sidebar-stat-value">256</div>
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
                <a href="#about" class="sidebar-link"><span class="mdi mdi-information"></span>关于</a>
                <a href="#contact" class="sidebar-link"><span class="mdi mdi-email"></span>联系</a>
                <div class="sidebar-tree">
                    <div class="sidebar-tree-item">
                        <div class="sidebar-tree-header">
                            <span class="sidebar-tree-item-icon mdi mdi-folder"></span>
                            <span class="sidebar-tree-label">项目文档</span>
                            <span class="sidebar-tree-icon mdi mdi-chevron-right"></span>
                        </div>
                        <div class="sidebar-tree-children">
                            <a href="#docs-intro" class="sidebar-link sidebar-tree-link">项目介绍</a>
                            <a href="#docs-api" class="sidebar-link sidebar-tree-link">API 文档</a>
                            <div class="sidebar-tree">
                                <div class="sidebar-tree-item">
                                    <div class="sidebar-tree-header">
                                        <span class="sidebar-tree-item-icon mdi mdi-folder"></span>
                                        <span class="sidebar-tree-label">开发指南</span>
                                        <span class="sidebar-tree-icon mdi mdi-chevron-right"></span>
                                    </div>
                                    <div class="sidebar-tree-children">
                                        <a href="#docs-dev-install" class="sidebar-link sidebar-tree-link">安装部署</a>
                                        <a href="#docs-dev-config" class="sidebar-link sidebar-tree-link">配置说明</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="#links" class="sidebar-link"><span class="mdi mdi-link-variant"></span>友链</a>
            </div>

            <!-- 分类列表 -->
            <div class="sidebar-tab-pane" id="categories">
                <div class="sidebar-tree">
                    <div class="sidebar-tree-item">
                        <div class="sidebar-tree-header">
                            <span class="sidebar-tree-item-icon mdi mdi-folder"></span>
                            <span class="sidebar-tree-label">技术</span>
                            <span class="sidebar-tree-icon mdi mdi-chevron-right"></span>
                        </div>
                        <div class="sidebar-tree-children">
                            <a href="#category-tech-frontend" class="sidebar-link sidebar-tree-link">前端开发</a>
                            <a href="#category-tech-backend" class="sidebar-link sidebar-tree-link">后端开发</a>
                            <a href="#category-tech-devops" class="sidebar-link sidebar-tree-link">运维部署</a>
                        </div>
                    </div>
                </div>
                <a href="#category-life" class="sidebar-link"><span class="mdi mdi-heart"></span>生活</a>
                <a href="#category-travel" class="sidebar-link"><span class="mdi mdi-airplane"></span>旅行</a>
            </div>
        </div>
    </div>
</div>

<!-- 侧边栏遮罩 -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
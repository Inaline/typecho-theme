/**
 * Inaline 主题前台全局 JavaScript
 * @author Inaline Studio
 */

$(document).ready(function() {
    // 获取元素
    const $menuBtn = $('#menuBtn');
    const $searchBtn = $('#searchBtn');
    const $moreIcon = $('#moreIcon');
    const $sidebar = $('#sidebar');
    const $sidebarOverlay = $('#sidebarOverlay');
    const $searchOverlay = $('#searchOverlay');
    const $searchBox = $('#searchBox');
    const $moreMenu = $('#moreMenu');

    // 根据当前页面 URL 自动切换选中标签页
    function updateActiveNav() {
        const currentPath = window.location.pathname;
        const currentHash = window.location.hash;

        // 移除所有选中状态
        $('.topbar-nav-item').removeClass('active');
        $('.topbar-dropdown-item').removeClass('active');
        $('.sidebar-link').removeClass('active');
        $('.sidebar-tree-header').removeClass('active');

        if (currentHash) {
            // 首先检查是否匹配二级菜单项
            const $dropdownItem = $(`.topbar-dropdown-item[href="${currentHash}"]`);
            if ($dropdownItem.length) {
                // 高亮二级菜单项
                $dropdownItem.addClass('active');
                // 高亮对应的一级父菜单
                const parentHash = $dropdownItem.data('parent');
                if (parentHash) {
                    $(`.topbar-nav-dropdown`).filter(function() {
                        return $(this).find('.topbar-nav-link').text() === '分类';
                    }).addClass('active');
                }
                return;
            }

            // 如果有 hash，匹配 hash
            const $navItem = $(`.topbar-nav-item[href="${currentHash}"]`);

            if ($navItem.length) {
                // 如果是一级导航项，直接高亮
                $navItem.addClass('active');
            }

            // 检查侧边栏链接
            const $sidebarLink = $(`.sidebar-link[href="${currentHash}"]`);
            if ($sidebarLink.length) {
                $sidebarLink.addClass('active');
                // 自动展开父级树形节点
                $sidebarLink.parents('.sidebar-tree-item').addClass('expanded');
            }
        } else if (currentPath === '/' || currentPath === '/index.php') {
            // 如果是首页，选中首页标签
            $('.topbar-nav-item[href="#home"]').addClass('active');
        }
    }

    // 初始化时更新选中状态
    updateActiveNav();

    // 监听 hash 变化
    $(window).on('hashchange', updateActiveNav);

    // 打开左侧边栏
    $menuBtn.on('click', function() {
        $sidebar.addClass('show');
        $sidebarOverlay.addClass('show');
        $('body').addClass('overflow-hidden');
    });

    // 关闭侧边栏和遮罩
    function closeAll() {
        $sidebar.removeClass('show');
        $sidebarOverlay.removeClass('show');
        $searchOverlay.removeClass('show');
        $searchBox.removeClass('show');
        $moreMenu.removeClass('show');
        $('body').removeClass('overflow-hidden');
    }

    // 点击遮罩关闭所有
    $sidebarOverlay.on('click', closeAll);
    $searchOverlay.on('click', closeAll);

    // 搜索按钮点击
    $searchBtn.on('click', function(e) {
        e.stopPropagation();
        $searchBox.toggleClass('show');
        $moreMenu.removeClass('show');

        // 显示遮罩
        if ($searchBox.hasClass('show')) {
            $searchOverlay.addClass('show');
            $('body').addClass('overflow-hidden');
        } else {
            $searchOverlay.removeClass('show');
            $('body').removeClass('overflow-hidden');
        }
    });

    // 更多选项按钮点击
    $moreIcon.on('click', function(e) {
        e.stopPropagation();
        $moreMenu.toggleClass('show');
        $searchBox.removeClass('show');

        // 显示遮罩
        if ($moreMenu.hasClass('show')) {
            $searchOverlay.addClass('show');
            $('body').addClass('overflow-hidden');
        } else {
            $searchOverlay.removeClass('show');
            $('body').removeClass('overflow-hidden');
        }
    });

    // 点击页面其他区域关闭所有弹出层
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.topbar').length &&
            !$(e.target).closest('.sidebar').length &&
            !$(e.target).closest('.more-menu').length) {
            closeAll();
        }
    });

    // 阻止搜索框和菜单的点击冒泡
    $searchBox.on('click', function(e) {
        e.stopPropagation();
    });

    $moreMenu.on('click', function(e) {
        e.stopPropagation();
    });

    // 窗口大小改变时关闭所有弹出层
    $(window).on('resize', function() {
        closeAll();
    });

    // 二级菜单点击处理
    $('.topbar-nav-dropdown').on('click', function(e) {
        // 点击下拉菜单项时不阻止默认行为
        if ($(e.target).hasClass('topbar-dropdown-item')) {
            return;
        }
        // 点击一级菜单时不阻止默认行为（如果有链接）
        e.stopPropagation();
    });

    // 二级菜单项点击处理
    $('.topbar-dropdown-item').on('click', function(e) {
        // 允许默认跳转行为
        const $this = $(this);
        const href = $this.attr('href');

        // 更新 active 状态
        $('.topbar-dropdown-item').removeClass('active');
        $this.addClass('active');

        // 保持父菜单的 active 状态
        $this.closest('.topbar-nav-dropdown').addClass('active');
    });

    // 侧边栏选项卡切换
    $('.sidebar-tab').on('click', function() {
        const $this = $(this);
        const tabId = $this.data('tab');

        // 切换选项卡样式
        $('.sidebar-tab').removeClass('active');
        $this.addClass('active');

        // 切换内容显示
        $('.sidebar-tab-pane').removeClass('active');
        $(`#${tabId}`).addClass('active');
    });

    // 侧边栏链接点击处理（不包含树形节点头部）
    $('.sidebar-link').on('click', function(e) {
        // 如果是树形节点内部的子链接，正常跳转
        const $this = $(this);
        const href = $this.attr('href');

        // 移除所有选中状态（包括树形节点头部）
        $('.sidebar-link').removeClass('active');
        $('.sidebar-tree-header').removeClass('active');

        // 给当前链接添加选中状态
        $this.addClass('active');

        // 允许默认跳转行为
        // updateActiveNav 会在 hashchange 时被调用
    });

    // 树形节点头部点击处理
    $('.sidebar-tree-header').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $this = $(this);
        const $treeItem = $this.closest('.sidebar-tree-item');
        const $children = $treeItem.find('.sidebar-tree-children').first();
        const isExpanded = $treeItem.hasClass('expanded');

        // 切换展开状态
        $treeItem.toggleClass('expanded');

        // 切换选中状态：展开时选中，收起时取消选中
        if (!isExpanded) {
            // 展开时，移除所有链接和树形头部的选中状态，只给当前树形头部添加选中状态
            $('.sidebar-link').removeClass('active');
            $('.sidebar-tree-header').removeClass('active');
            $this.addClass('active');
        } else {
            // 收起时，移除当前树形头部的选中状态
            $this.removeClass('active');
        }

        // 平滑展开/收起动画
        $children.slideToggle(300);
    });
});

// 动态注入 @font-face, 放到最后别阻塞功能
if (typeof fontPath !== 'undefined' && typeof fontFormat !== 'undefined') {
    const style = document.createElement('style');
    style.textContent = `
      @font-face {
        font-family: 'CustomFont';
        src: url('${fontPath}') format('${fontFormat}');
        font-display: swap;
      }

      body {
        font-family: 'CustomFont', 'Microsoft YaHei', sans-serif;
      }
    `;
    document.head.appendChild(style);
    console.log(style);
}
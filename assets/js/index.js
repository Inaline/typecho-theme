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

    // 树形节点头部点击处理
    $('.sidebar-tree-header').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $this = $(this);
        const $treeItem = $this.closest('.sidebar-tree-item');
        const $children = $treeItem.find('.sidebar-tree-children').first();

        // 切换展开状态
        $treeItem.toggleClass('expanded');

        // 平滑展开/收起动画
        $children.slideToggle(300);
    });

    // 深色模式初始化
    const savedTheme = localStorage.getItem('theme');
    console.log('[深色模式] 初始化，保存的主题:', savedTheme);

    if (savedTheme === 'dark') {
        $('body').addClass('dark-mode');
        console.log('[深色模式] 已应用深色主题');
    }

    // 切换深色模式
    window.toggleDarkMode = function() {
        $('body').toggleClass('dark-mode');

        const isDarkMode = $('body').hasClass('dark-mode');
        console.log('[深色模式] 切换到:', isDarkMode ? '深色' : '浅色');

        // 保存到 localStorage
        if (isDarkMode) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
        console.log('[深色模式] 已保存到 localStorage');
    };

    // 绑定暗色模式菜单项点击事件
    $(document).on('click', '.dark-mode-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('[深色模式] 点击了暗色模式切换按钮');
        window.toggleDarkMode();
        closeAll();
    });

    // 调试：检查元素是否存在
    console.log('[深色模式] 检查元素:', $('.dark-mode-toggle').length, '个');
    console.log('[深色模式] 元素 HTML:', $('.dark-mode-toggle').html());

    // 尝试直接绑定（如果元素已存在）
    $('.dark-mode-toggle').on('click', function(e) {
        console.log('[深色模式] 直接绑定触发');
        e.preventDefault();
        e.stopPropagation();
        window.toggleDarkMode();
        closeAll();
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
}
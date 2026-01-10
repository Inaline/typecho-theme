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
    });

    // 关闭侧边栏和遮罩
    function closeAll() {
        $sidebar.removeClass('show');
        $sidebarOverlay.removeClass('show');
        $searchOverlay.removeClass('show');
        $searchBox.removeClass('show');
        $moreMenu.removeClass('show');
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
        } else {
            $searchOverlay.removeClass('show');
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
        } else {
            $searchOverlay.removeClass('show');
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
});
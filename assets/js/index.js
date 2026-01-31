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

    // 二级菜单点击处理
    $('.topbar-nav-dropdown').on('click', function(e) {
        // 点击下拉菜单项时不阻止默认行为
        if ($(e.target).hasClass('topbar-dropdown-item')) {
            return;
        }
        // 点击一级菜单时不阻止默认行为（如果有链接）
        e.stopPropagation();
    });

    // 分类下拉菜单点击处理（桌面端点击展开/收起，移动端点击展开/收起）
    $('.category-dropdown').on('click', function(e) {
        const $this = $(this);
        const $dropdownMenu = $this.find('.category-dropdown-menu');
        
        // 如果点击的是链接本身，允许跳转，不阻止默认行为
        if ($(e.target).hasClass('category-link') || $(e.target).closest('.category-link').length > 0) {
            return;
        }
        
        // 如果点击的是子菜单中的链接，不阻止默认行为
        if ($(e.target).hasClass('category-item') || $(e.target).closest('.category-item').length > 0) {
            return;
        }
        
        // 点击箭头或整个下拉项的空白区域时，切换展开/收起状态
        e.preventDefault();
        e.stopPropagation();
        $this.toggleClass('expanded');
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

    if (savedTheme === 'dark') {
        $('body').addClass('dark-mode');

        // 切换 Logo 到深色模式
        const $logo = $('.topbar-logo img');
        if ($logo.length > 0) {
            const darkLogo = $logo.data('dark-logo');
            if (darkLogo) {
                $logo.attr('src', darkLogo);
            }
        }
    }

    // 切换深色模式
    window.toggleDarkMode = function() {
        $('body').toggleClass('dark-mode');

        const isDarkMode = $('body').hasClass('dark-mode');

        // 切换 Logo
        const $logo = $('.topbar-logo img');
        if ($logo.length > 0) {
            const newSrc = isDarkMode
                ? $logo.data('dark-logo')
                : $logo.data('light-logo');
            if (newSrc) {
                $logo.attr('src', newSrc);
            }
        }

        // 保存到 localStorage
        if (isDarkMode) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    };

    // 绑定暗色模式菜单项点击事件
    $(document).on('click', '.dark-mode-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        window.toggleDarkMode();
        closeAll();
    });

    // 尝试直接绑定（如果元素已存在）
    $('.dark-mode-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        window.toggleDarkMode();
        closeAll();
    });

    // 搜索框相关变量
    const $searchInput = $('#searchInput');
    const $clearSearch = $('#clearSearch');
    const $searchSubmit = $('#searchSubmit');
    const $clearAllHistory = $('#clearAllHistory');

    // 搜索框输入事件
    $searchInput.on('input', function() {
        const value = $(this).val();
        $clearSearch.toggle(value.length > 0);
    });

    // 清空搜索框
    $clearSearch.on('click', function() {
        $searchInput.val('').focus();
        $clearSearch.hide();
    });

    // 搜索按钮点击事件
    $searchSubmit.on('click', function() {
        const keyword = $searchInput.val().trim();
        if (keyword) {
            SearchHistory.saveHistory(keyword);
            SearchHistory.renderHistory();
            // TODO: 执行实际搜索
        }
    });

    // 搜索框回车事件
    $searchInput.on('keypress', function(e) {
        if (e.which === 13) {
            const keyword = $(this).val().trim();
            if (keyword) {
                SearchHistory.saveHistory(keyword);
                SearchHistory.renderHistory();
                // TODO: 执行实际搜索
            }
        }
    });

    // 点击搜索历史项
    $(document).on('click', '.search-history-item', function(e) {
        const keyword = $(this).find('.search-history-text').text();
        $searchInput.val(keyword);
        SearchHistory.saveHistory(keyword);
        SearchHistory.renderHistory();
        // TODO: 执行实际搜索
    });

    // 清空所有搜索历史
    $clearAllHistory.on('click', function() {
        if (confirm('确定要清空所有搜索历史吗？')) {
            SearchHistory.clearAllHistory();
        }
    });

    // 点击热搜项
    $(document).on('click', '.search-hot-item', function() {
        const keyword = $(this).find('.search-hot-text').text();
        $searchInput.val(keyword);
        SearchHistory.saveHistory(keyword);
        SearchHistory.renderHistory();
        // TODO: 执行实际搜索
    });

    // 打开搜索框时渲染搜索历史
    $searchBtn.on('click', function() {
        setTimeout(() => {
            SearchHistory.renderHistory();
            $searchInput.focus();
        }, 100);
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

/* ========================
 * 搜索功能
 * ======================== */

// 搜索历史管理
const SearchHistory = {
    STORAGE_KEY: 'search_history',
    MAX_HISTORY: 10,

    // 获取搜索历史
    getHistory() {
        try {
            const history = localStorage.getItem(this.STORAGE_KEY);
            return history ? JSON.parse(history) : [];
        } catch (e) {
            return [];
        }
    },

    // 保存搜索历史
    saveHistory(keyword) {
        if (!keyword || keyword.trim() === '') return;

        let history = this.getHistory();

        // 移除已存在的相同关键词（如果有的话）
        history = history.filter(item => item !== keyword);

        // 将新关键词添加到开头
        history.unshift(keyword);

        // 限制历史记录数量
        if (history.length > this.MAX_HISTORY) {
            history = history.slice(0, this.MAX_HISTORY);
        }

        try {
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(history));
        } catch (e) {
            // 静默失败
        }
    },

    // 清空所有历史记录
    clearAllHistory() {
        try {
            localStorage.removeItem(this.STORAGE_KEY);
            this.renderHistory();
        } catch (e) {
            // 静默失败
        }
    },

    // 渲染搜索历史
    renderHistory() {
        const history = this.getHistory();
        const $historyList = $('#historyList');
        const $searchHistory = $('#searchHistory');

        if (history.length === 0) {
            $searchHistory.hide();
            return;
        }

        $searchHistory.show();
        $historyList.empty();

        history.forEach(keyword => {
            const $item = $('<div class="search-history-item"></div>');
            $item.html(`
                <span class="search-history-text">${this.escapeHtml(keyword)}</span>
            `);
            $historyList.append($item);
        });
    },

    // HTML 转义
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// 轮播图功能
const Carousel = {
    currentIndex: 0,
    interval: null,
    intervalTime: 5000,

    init() {
        const $carousel = $('.carousel');
        if ($carousel.length === 0) return;

        // 获取自动切换间隔
        const intervalStr = $carousel.data('interval');
        if (intervalStr && parseInt(intervalStr) > 0) {
            this.intervalTime = parseInt(intervalStr) * 1000;
            this.startAutoPlay();
        }

        // 绑定切换按钮事件
        $carousel.find('.carousel-control').on('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const direction = $(e.currentTarget).data('direction');
            if (direction === 'prev') {
                this.prev();
            } else {
                this.next();
            }
        });

        // 绑定指示器事件
        $carousel.find('.carousel-indicator').on('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const index = $(e.currentTarget).data('index');
            this.goTo(index);
        });

        // 鼠标悬停暂停自动播放
        $carousel.on('mouseenter', () => {
            this.stopAutoPlay();
        });

        $carousel.on('mouseleave', () => {
            if (this.intervalTime > 0) {
                this.startAutoPlay();
            }
        });

        // 触摸滑动支持
        this.initTouchSwipe($carousel);
    },

    goTo(index) {
        const $carousel = $('.carousel');
        const $items = $carousel.find('.carousel-item');
        const $indicators = $carousel.find('.carousel-indicator');

        if (index < 0) {
            index = $items.length - 1;
        } else if (index >= $items.length) {
            index = 0;
        }

        $items.removeClass('active');
        $indicators.removeClass('active');

        $items.eq(index).addClass('active');
        $indicators.eq(index).addClass('active');

        this.currentIndex = index;
    },

    next() {
        this.goTo(this.currentIndex + 1);
    },

    prev() {
        this.goTo(this.currentIndex - 1);
    },

    startAutoPlay() {
        this.stopAutoPlay();
        this.interval = setInterval(() => {
            this.next();
        }, this.intervalTime);
    },

    stopAutoPlay() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    },

    initTouchSwipe($carousel) {
        let startX = 0;
        let startY = 0;
        let endX = 0;
        let hasMoved = false;
        const threshold = 50; // 滑动阈值

        $carousel.on('touchstart', (e) => {
            // 如果触摸的是控制按钮或指示器，不处理滑动
            if ($(e.target).closest('.carousel-control, .carousel-indicator').length > 0) {
                return;
            }
            startX = e.originalEvent.touches[0].clientX;
            startY = e.originalEvent.touches[0].clientY;
            endX = startX;
            hasMoved = false;
        });

        $carousel.on('touchmove', (e) => {
            endX = e.originalEvent.touches[0].clientX;
            const endY = e.originalEvent.touches[0].clientY;
            const moveX = Math.abs(endX - startX);
            const moveY = Math.abs(endY - startY);

            // 只有水平移动距离大于垂直移动距离时才认为是滑动
            if (moveX > 10 && moveX > moveY) {
                hasMoved = true;
            }
        });

        $carousel.on('touchend', (e) => {
            // 如果触摸的是控制按钮或指示器，不处理滑动
            if ($(e.target).closest('.carousel-control, .carousel-indicator').length > 0) {
                return;
            }

            const diff = startX - endX;
            if (hasMoved && Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.next(); // 左滑，下一张
                } else {
                    this.prev(); // 右滑，上一张
                }
            }
        });
    }
};

// 初始化轮播图
Carousel.init();

/* ========================
 * 页脚运行时间动态更新
 * ======================== */

const RunTime = {
    init() {
        const $footer = $('.footer');
        if ($footer.length === 0) return;

        const startDateStr = $footer.data('start-date');
        if (!startDateStr) return;

        const startDate = new Date(startDateStr);
        const $runTimeElement = $('#runTime');

        if ($runTimeElement.length === 0) return;

        // 更新运行时间
        this.updateRunTime(startDate, $runTimeElement);

        // 每秒更新一次
        setInterval(() => {
            this.updateRunTime(startDate, $runTimeElement);
        }, 1000);
    },

    updateRunTime(startDate, $element) {
        const now = new Date();
        const diff = now - startDate;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        let timeText = '';
        if (days > 0) timeText += days + '天';
        if (hours > 0) timeText += hours + '小时';
        if (minutes > 0) timeText += minutes + '分';
        timeText += seconds + '秒';

        $element.text(timeText);
    }
};

// 初始化运行时间
RunTime.init();
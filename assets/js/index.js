/**
 * Inaline 主题前台全局 JavaScript
 * @author Inaline Studio
 */

/* ========================
 * 搜索历史管理
 * ======================== */
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

    // 阻止搜索框内的点击冒泡到 document（避免关闭搜索框）
    $searchBox.on('click', function(e) {
        e.stopPropagation();
    });

    // 阻止菜单的点击冒泡
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

    // 更新暗色模式菜单项
    updateDarkModeMenuItem();

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

        // 更新暗色模式菜单项
        updateDarkModeMenuItem();

        // 保存到 localStorage
        if (isDarkMode) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    };

    // 更新暗色模式菜单项的图标和文本
    function updateDarkModeMenuItem() {
        const $menuItem = $('.dark-mode-toggle');
        if ($menuItem.length === 0) return;

        const isDarkMode = $('body').hasClass('dark-mode');
        const $icon = $menuItem.find('.more-menu-icon');
        const $text = $menuItem.find('span:last-child');

        if (isDarkMode) {
            // 当前是暗色模式，显示切换到浅色模式的图标和文本
            const darkIcon = $icon.data('icon-dark');
            const darkText = $text.data('text-dark');
            if (darkIcon) {
                $icon.removeClass('mdi-brightness-4').addClass(darkIcon);
            }
            if (darkText) {
                $text.text(darkText);
            }
        } else {
            // 当前是浅色模式，显示切换到暗色模式的图标和文本
            const lightIcon = $icon.data('icon-light');
            const lightText = $text.data('text-light');
            if (lightIcon) {
                $icon.removeClass('mdi-brightness-6').addClass(lightIcon);
            }
            if (lightText) {
                $text.text(lightText);
            }
        }
    }

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
            // 跳转到搜索页面
            window.location.href = '/search/' + encodeURIComponent(keyword) + '/';
        }
    });

    // 搜索框回车事件
    $searchInput.on('keypress', function(e) {
        if (e.which === 13) {
            const keyword = $(this).val().trim();
            if (keyword) {
                SearchHistory.saveHistory(keyword);
                SearchHistory.renderHistory();
                // 跳转到搜索页面
                window.location.href = '/search/' + encodeURIComponent(keyword) + '/';
            }
        }
    });

    // 点击搜索历史项
    $searchBox.on('click', '.search-history-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const keyword = $(this).find('.search-history-text').text();
        $searchInput.val(keyword);
        SearchHistory.saveHistory(keyword);
        SearchHistory.renderHistory();
        // 触发搜索按钮点击
        $searchSubmit.trigger('click');
    });

    // 清空所有搜索历史
    $clearAllHistory.on('click', function() {
        if (confirm('确定要清空所有搜索历史吗？')) {
            SearchHistory.clearAllHistory();
        }
    });

    // 点击热搜项
    $searchBox.on('click', '.search-hot-item', function() {
        const keyword = $(this).find('.search-hot-text').text();
        $searchInput.val(keyword);
        SearchHistory.saveHistory(keyword);
        SearchHistory.renderHistory();
        // 触发搜索按钮点击
        $searchSubmit.trigger('click');
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
 * 轮播图功能
 * ======================== */
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

        // 使用PHP输出的运行时间，不再由JS计算
        const $runTimeElement = $('#runTime');

        if ($runTimeElement.length === 0) return;

        // 每秒更新一次运行时间
        this.updateRunTime($runTimeElement);
        setInterval(() => {
            this.updateRunTime($runTimeElement);
        }, 1000);
    },

    updateRunTime($element) {
        // 从PHP输出的运行时间字符串中提取天、时、分、秒
        let timeText = $element.text();
        let totalSeconds = 0;

        // 解析运行时间字符串
        const daysMatch = timeText.match(/(\d+)天/);
        const hoursMatch = timeText.match(/(\d+)小时/);
        const minutesMatch = timeText.match(/(\d+)分/);
        const secondsMatch = timeText.match(/(\d+)秒/);

        if (daysMatch) totalSeconds += parseInt(daysMatch[1]) * 86400;
        if (hoursMatch) totalSeconds += parseInt(hoursMatch[1]) * 3600;
        if (minutesMatch) totalSeconds += parseInt(minutesMatch[1]) * 60;
        if (secondsMatch) totalSeconds += parseInt(secondsMatch[1]);

        // 增加一秒
        totalSeconds++;

        // 转换回天、时、分、秒
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        // 生成新的时间字符串
        let newTimeText = '';
        if (days > 0) newTimeText += days + '天';
        if (hours > 0) newTimeText += hours + '小时';
        if (minutes > 0) newTimeText += minutes + '分';
        newTimeText += seconds + '秒';

        $element.text(newTimeText);
    }
};

// 初始化运行时间
RunTime.init();

/* ========================
 * 图片查看功能 (viewer.js)
 * ======================== */

const ImageViewer = {
    viewerLoaded: false,
    viewerLoading: false,

    // 加载 viewer.js
    loadViewer() {
        if (this.viewerLoaded || this.viewerLoading) return;

        this.viewerLoading = true;

        // 动态加载 viewer.js 样式
        const style = document.createElement('link');
        style.rel = 'stylesheet';
        style.href = 'https://cdn.bootcdn.net/ajax/libs/viewerjs/1.11.6/viewer.min.css';
        document.head.appendChild(style);

        // 动态加载 viewer.js 脚本
        const script = document.createElement('script');
        script.src = 'https://cdn.bootcdn.net/ajax/libs/viewerjs/1.11.6/viewer.min.js';
        script.async = true;
        script.onload = () => {
            this.viewerLoaded = true;
            this.viewerLoading = false;
            this.initAllViewers();
        };
        script.onerror = () => {
            this.viewerLoading = false;
        };
        document.body.appendChild(script);
    },

    // 初始化所有图片查看器
    initAllViewers() {
        if (typeof Viewer === 'undefined') return;

        // 初始化说说图片 - 为每个说说的图片容器分别初始化
        const shuoshuoItems = document.querySelectorAll('.shuoshuo-item');
        shuoshuoItems.forEach(function(item) {
            const images = item.querySelectorAll('.shuoshuo-image-item img');
            if (images.length > 0) {
                try {
                    const imagesContainer = item.querySelector('.shuoshuo-images');
                    if (imagesContainer) {
                        new Viewer(imagesContainer, {
                            url: 'src',
                            toolbar: {
                                zoomIn: 1,
                                zoomOut: 1,
                                oneToOne: 1,
                                reset: 1,
                                prev: 1,
                                play: 0,
                                next: 1,
                                rotateLeft: 1,
                                rotateRight: 1,
                                flipHorizontal: 1,
                                flipVertical: 1,
                            },
                            title: false,
                            transition: true,
                            keyboard: true,
                            zoomRatio: 0.2,
                            minZoomRatio: 0.5,
                            maxZoomRatio: 3,
                        });
                    }
                } catch (e) {
                    // 静默失败
                }
            }
        });

        // 初始化文章内容图片
        const articleImages = document.querySelectorAll('.markdown-content img');
        if (articleImages.length > 0) {
            try {
                const articleViewer = new Viewer(articleImages[0].closest('.markdown-content'), {
                    url: 'src',
                    toolbar: {
                        zoomIn: 1,
                        zoomOut: 1,
                        oneToOne: 1,
                        reset: 1,
                        prev: 1,
                        play: 0,
                        next: 1,
                        rotateLeft: 1,
                        rotateRight: 1,
                        flipHorizontal: 1,
                        flipVertical: 1,
                    },
                    title: false,
                    transition: true,
                    keyboard: true,
                    zoomRatio: 0.2,
                    minZoomRatio: 0.5,
                    maxZoomRatio: 3,
                });
            } catch (e) {
                // 静默失败
            }
        }
    },

    // 检查并初始化
    checkAndInit() {
        // 检查是否有图片需要查看器
        const hasShuoshuoImages = document.querySelectorAll('.shuoshuo-image-item img').length > 0;
        const hasArticleImages = document.querySelectorAll('.markdown-content img').length > 0;

        if (hasShuoshuoImages || hasArticleImages) {
            if (typeof Viewer === 'undefined') {
                this.loadViewer();
            } else {
                this.initAllViewers();
            }
        }
    }
};

// 页面加载完成后检查并初始化图片查看器
$(document).ready(function() {
    ImageViewer.checkAndInit();
});

/* ==========================
 * 开发者工具检测
 * ========================== */
(function() {
    // 显示提示消息的函数
    function showDevToolsWarning() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '哎呀！',
                text: '别偷偷看我的代码欸，人家也是会害羞的嘛. ￣へ￣',
                icon: 'warning',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#fff',
                customClass: {
                    popup: 'swal2-popup-custom'
                }
            });
        }
    }

    // 检测 F12 键
    document.addEventListener('keydown', function(e) {
        // F12 键 (keyCode: 123)
        if (e.keyCode === 123) {
            showDevToolsWarning();
        }

        // Ctrl+Shift+I (Chrome/Edge 开发者工具)
        if (e.ctrlKey && e.shiftKey && e.keyCode === 73) {
            showDevToolsWarning();
        }

        // Ctrl+Shift+J (Chrome 控制台)
        if (e.ctrlKey && e.shiftKey && e.keyCode === 74) {
            showDevToolsWarning();
        }

        // Ctrl+U (查看源代码)
        if (e.ctrlKey && e.keyCode === 85) {
            showDevToolsWarning();
        }
    });
})();
/**
 * Inaline 主题文章页面脚本
 * @author Inaline Studio
 */

// 全局变量，用于跟踪 KaTeX 加载状态
window.katexLoaded = false;
window.katexLoading = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log('[初始化] DOMContentLoaded 事件触发，开始初始化');

    // 为代码块添加行号（函数内部会处理高亮逻辑）
    if (typeof hljs !== 'undefined') {
        console.log('[初始化] hljs 已加载，调用 addLineNumbers');
        addLineNumbers();
    } else {
        console.warn('[初始化] hljs 未加载，跳过代码块处理');
    }

    // 初始化文章目录
    initTOC();

    // 初始化阅读进度
    initReadingProgress();

    // 初始化音乐播放器
    initMusicPlayer();

    // 初始化评论功能
    initComments();

    // 点赞按钮功能
    initLikeButton();

    // 分享按钮功能
    initShareButton();

    // 初始化文章页面滚动效果
    initArticlePageScrollEffect();

    // 初始化图片懒加载和加载失败处理
    initImageLazyLoad();

    // 初始化数学公式解析
    initMathJax();

    // 处理评论锚点滚动
    handleCommentAnchorScroll();

    // 初始化网盘卡片复制功能
    initNetdiskCopy();

    // 初始化外部链接跳转提示
    initExternalLinkProtection();
});

/**
 * 异步加载脚本
 * @param {string} src 脚本 URL
 * @returns {Promise}
 */
function loadScript(src) {
    return new Promise(function(resolve, reject) {
        if (document.querySelector('script[src="' + src + '"]')) {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = resolve;
        script.onerror = function() {
            reject(new Error('Failed to load script: ' + src));
        };
        document.head.appendChild(script);
    });
}

/**
 * 异步加载 CSS
 * @param {string} href CSS URL
 * @returns {Promise}
 */
function loadCSS(href) {
    return new Promise(function(resolve, reject) {
        if (document.querySelector('link[href="' + href + '"]')) {
            resolve();
            return;
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.onload = resolve;
        link.onerror = function() {
            reject(new Error('Failed to load CSS: ' + href));
        };
        document.head.appendChild(link);
    });
}

/**
 * 异步加载 KaTeX 库
 * @returns {Promise}
 */
function loadKaTeX() {
    if (window.katexLoaded) {
        return Promise.resolve();
    }

    if (window.katexLoading) {
        return new Promise(function(resolve) {
            const checkInterval = setInterval(function() {
                if (window.katexLoaded) {
                    clearInterval(checkInterval);
                    resolve();
                }
            }, 100);
        });
    }

    window.katexLoading = true;

    // 使用 BootCDN 加载 KaTeX
    const katexVersion = '0.16.9';
    const cssUrl = 'https://cdn.bootcdn.net/ajax/libs/KaTeX/' + katexVersion + '/katex.min.css';
    const jsUrl = 'https://cdn.bootcdn.net/ajax/libs/KaTeX/' + katexVersion + '/katex.min.js';
    const autoRenderUrl = 'https://cdn.bootcdn.net/ajax/libs/KaTeX/' + katexVersion + '/contrib/auto-render.min.js';

    return loadCSS(cssUrl)
        .then(function() {
            return loadScript(jsUrl);
        })
        .then(function() {
            return loadScript(autoRenderUrl);
        })
        .then(function() {
            window.katexLoaded = true;
            window.katexLoading = false;
        })
        .catch(function(error) {
            console.error('加载 KaTeX 失败:', error);
            window.katexLoading = false;
            throw error;
        });
}

/**
 * 为代码块添加行号
 */
function addLineNumbers() {
    const codeBlocks = document.querySelectorAll('.markdown-content pre code');

    codeBlocks.forEach(function(block) {
        const pre = block.parentElement;

        // 获取纯文本内容
        const code = block.textContent;

        // 将代码按行分割
        const lines = code.split('\n');

        // 移除最后一行（如果是空的）
        if (lines.length > 0 && lines[lines.length - 1].trim() === '') {
            lines.pop();
        }

        // 创建行号列
        const lineNumbersDiv = document.createElement('div');
        lineNumbersDiv.className = 'line-numbers';

        // 为每一行添加行号
        for (let i = 1; i <= lines.length; i++) {
            const lineNumber = document.createElement('span');
            lineNumber.className = 'line-number';
            lineNumber.textContent = i;
            lineNumbersDiv.appendChild(lineNumber);
        }

        // 将行号列插入到 pre 的最前面
        pre.insertBefore(lineNumbersDiv, block);

        // 重新高亮代码
        hljs.highlightElement(block);

        // 添加复制按钮
        addCopyButton(block);
    });
}

/**
 * 为代码块添加复制按钮
 */
function addCopyButton(codeBlock) {
    const pre = codeBlock.parentElement;
    const copyBtn = document.createElement('button');
    copyBtn.className = 'copy-btn';
    copyBtn.innerHTML = '<span class="mdi mdi-content-copy"></span> 复制';

    copyBtn.addEventListener('click', function() {
        copyCode(codeBlock, copyBtn);
    });

    pre.appendChild(copyBtn);
}

/**
 * 复制代码到剪贴板
 */
function copyCode(codeBlock, copyBtn) {
    // 直接从代码块获取纯文本内容
    const codeText = codeBlock.textContent;

    // 优先使用现代 API，降级使用传统方法
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(codeText).then(function() {
            showCopySuccess(copyBtn, false);
            showCopyrightNotice();
        }).catch(function(err) {
            console.error('现代 API 复制失败，尝试降级方案：', err);
            fallbackCopy(codeText, copyBtn, false);
        });
    } else {
        fallbackCopy(codeText, copyBtn, false);
    }
}

/**
 * 降级复制方案（兼容非 HTTPS 环境）
 * @param {string} text - 要复制的文本
 * @param {HTMLElement} copyBtn - 复制按钮元素
 * @param {boolean} isNetdisk - 是否为网盘复制按钮
 */
function fallbackCopy(text, copyBtn, isNetdisk) {
    // 创建临时文本域
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    textarea.style.top = '0';
    document.body.appendChild(textarea);

    try {
        textarea.select();
        const successful = document.execCommand('copy');

        if (successful) {
            showCopySuccess(copyBtn, isNetdisk);
            showCopyrightNotice();
        } else {
            copyBtn.innerHTML = '<span class="mdi mdi-alert"></span> 复制失败';
            if (isNetdisk) {
                setTimeout(function() {
                    copyBtn.innerHTML = '<span class="mdi mdi-content-copy"></span> 复制提取码';
                }, 2000);
            }
        }
    } catch (err) {
        console.error('降级复制失败：', err);
        copyBtn.innerHTML = '<span class="mdi mdi-alert"></span> 复制失败';
        if (isNetdisk) {
            setTimeout(function() {
                copyBtn.innerHTML = '<span class="mdi mdi-content-copy"></span> 复制提取码';
            }, 2000);
        }
    }

    document.body.removeChild(textarea);
}

/**
 * 显示复制成功状态
 * @param {HTMLElement} copyBtn - 复制按钮元素
 * @param {boolean} isNetdisk - 是否为网盘复制按钮
 */
function showCopySuccess(copyBtn, isNetdisk) {
    const originalText = copyBtn.innerHTML;
    copyBtn.innerHTML = '<span class="mdi mdi-check"></span> 已复制';
    copyBtn.classList.add('copied');

    setTimeout(function() {
        if (isNetdisk) {
            copyBtn.innerHTML = '<span class="mdi mdi-content-copy"></span> 复制提取码';
        } else {
            copyBtn.innerHTML = originalText;
        }
        copyBtn.classList.remove('copied');
    }, 2000);
}

/**
 * 显示版权提示
 */
function showCopyrightNotice() {
    const messages = [
        '记得注明出处哦~ (๑•̀ㅂ•́)و✧',
        '转载请保留版权信息呀~ ٩(๑>◡<๑)۶',
        '好东西要记得分享~ (ﾉ◕ヮ◕)ﾉ*:･ﾟ✧',
        '感谢喜欢，记得标明来源哦~ o(*￣▽￣*)o',
        '知识共享，记得署名~ (｡♥‿♥｡)',
    ];
    const randomMessage = messages[Math.floor(Math.random() * messages.length)];

    Swal.fire({
        text: '复制成功！' + randomMessage,
        icon: 'info',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'copyright-notice-toast'
        }
    });
}

/**
 * 监听全局复制事件
 */
document.addEventListener('copy', function(event) {
    // 使用防抖，避免短时间内多次触发
    if (window._copyTimeout) {
        clearTimeout(window._copyTimeout);
    }
    window._copyTimeout = setTimeout(function() {
        showCopyrightNotice();
    }, 300);
});

/**
 * 初始化点赞按钮
 */
function initLikeButton() {
    const likeButtons = document.querySelectorAll('.like-btn');

    likeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const articleId = this.dataset.articleId;
            const currentLikes = parseInt(this.dataset.likes);
            const icon = this.querySelector('.mdi');
            const countSpan = this.querySelector('.article-action-count');

            // 切换点赞状态
            if (this.classList.contains('liked')) {
                // 取消点赞
                this.classList.remove('liked');
                icon.classList.remove('mdi-thumb-up');
                icon.classList.add('mdi-thumb-up-outline');
                countSpan.textContent = currentLikes - 1;
                this.dataset.likes = currentLikes - 1;
            } else {
                // 点赞
                this.classList.add('liked');
                icon.classList.remove('mdi-thumb-up-outline');
                icon.classList.add('mdi-thumb-up');
                countSpan.textContent = currentLikes + 1;
                this.dataset.likes = currentLikes + 1;

                // 添加动画效果
                icon.style.transform = 'scale(1.2)';
                setTimeout(function() {
                    icon.style.transform = 'scale(1)';
                }, 200);
            }

            // TODO: 发送 AJAX 请求到后端更新点赞数
            // updateLikes(articleId, this.classList.contains('liked'));
        });
    });
}

/**
 * 初始化分享按钮
 */
function initShareButton() {
    const shareButtons = document.querySelectorAll('.share-btn');

    shareButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const title = document.title;
            const url = window.location.href;
            const description = document.querySelector('meta[name="description"]')?.content || '';

            // 检查是否支持原生分享 API
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: description,
                    url: url
                }).catch(function(err) {
                    // 如果原生分享失败，显示备用分享方式
                    showShareDialog(title, url);
                });
            } else {
                // 不支持原生分享，显示备用分享方式
                showShareDialog(title, url);
            }
        });
    });
}

/**
 * 显示分享对话框
 */
function showShareDialog(title, url) {
    // 简单实现：复制链接到剪贴板
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function() {
            Swal.fire({
                title: '成功',
                text: '链接已复制到剪贴板！',
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        }).catch(function(err) {
            prompt('请复制以下链接：', url);
        });
    } else {
        // 降级方案
        prompt('请复制以下链接：', url);
    }
}

/**
 * 更新点赞数（待实现）
 * @param {number} articleId 文章ID
 * @param {boolean} isLiked 是否点赞
 */
function updateLikes(articleId, isLiked) {
    // TODO: 实现后端 API 调用
}

/**
 * 初始化评论功能
 */
function initComments() {
    const commentSection = document.querySelector('.comment-section');
    if (!commentSection) return;

    // 评论排序按钮
    const sortButtons = commentSection.querySelectorAll('.comment-sort-btn');
    sortButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const order = this.getAttribute('data-order');
            loadComments(1, order);
        });
    });

    // 分页按钮
    const pageButtons = commentSection.querySelectorAll('.comment-page-btn');
    pageButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const page = parseInt(this.getAttribute('data-page'));
            const activeSortBtn = commentSection.querySelector('.comment-sort-btn.active');
            const order = activeSortBtn ? activeSortBtn.getAttribute('data-order') : 'desc';
            loadComments(page, order);
        });
    });
}

// 使用事件委托处理回复按钮点击（因为评论内容可能被动态替换）
document.addEventListener('click', function(e) {
    // 检查点击的是否是回复按钮或其子元素
    const replyBtn = e.target.closest('.reply-btn');
    if (replyBtn) {
        e.preventDefault();
        const commentId = replyBtn.getAttribute('data-comment-id');
        let parentId = replyBtn.getAttribute('data-parent-id');

        // 如果是子评论（有 data-parent-id 属性），则使用一级评论 ID 作为父评论
        if (parentId) {
            parentId = parseInt(parentId);
        } else {
            // 一级评论，使用当前评论 ID 作为父评论
            parentId = parseInt(commentId);
        }

        if (typeof TypechoComment !== 'undefined') {
            TypechoComment.reply('comment-' + commentId, parentId);
        } else {
            Swal.fire({
                title: '提示',
                text: '评论回复功能暂不可用，请刷新页面重试',
                icon: 'warning'
            });
        }
    }
});

/**
 * 加载评论列表
 * @param {number} page 页码
 * @param {string} order 排序方式 ('asc' | 'desc')
 */
function loadComments(page, order) {
    const commentSection = document.querySelector('.comment-section');
    if (!commentSection) return;

    // 获取文章ID
    const articleId = commentSection.getAttribute('data-cid');
    if (!articleId) return;

    // 显示加载状态
    const commentList = document.getElementById('comment-list');
    if (commentList) {
        commentList.style.opacity = '0.5';
    }

    // 构建请求URL
    const url = new URL(window.location.href);
    url.searchParams.set('comment_page', page);
    url.searchParams.set('comment_order', order);

    // 发送请求
    fetch(url.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('网络请求失败');
        }
        return response.text();
    })
    .then(function(html) {
        // 解析返回的HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newCommentSection = doc.querySelector('.comment-section');

        if (newCommentSection) {
            // 替换评论区域
            commentSection.innerHTML = newCommentSection.innerHTML;

            // 重新初始化评论功能
            initComments();
        }
    })
    .catch(function(error) {
        console.error('加载评论失败:', error);
        Swal.fire({
            title: '错误',
            text: '加载评论失败，请重试',
            icon: 'error'
        });
    })
    .finally(function() {
        // 恢复评论列表透明度
        if (commentList) {
            commentList.style.opacity = '1';
        }
    });
}

/**
 * 初始化文章目录
 */
function initTOC() {
    const tocContainer = document.getElementById('article-toc');
    if (!tocContainer) return;

    // 查找文章内容区域
    const articleContent = document.querySelector('.markdown-content');
    if (!articleContent) {
        tocContainer.innerHTML = '<div class="toc-empty">未找到目录</div>';
        return;
    }

    // 查找所有标题
    const headings = articleContent.querySelectorAll('h1, h2, h3, h4, h5, h6');
    if (headings.length === 0) {
        tocContainer.innerHTML = '<div class="toc-empty">暂无目录</div>';
        return;
    }

    // 生成目录 HTML
    let tocHtml = '<ul class="toc-list">';
    let currentLevel = 0;
    let levelStack = [];
    const headingLevels = []; // 存储每个标题的层级

    headings.forEach(function(heading, index) {
        const level = parseInt(heading.tagName.charAt(1));
        const text = heading.textContent.trim();
        const id = 'heading-' + index;
        headingLevels.push(level);

        // 为标题添加 ID
        heading.id = id;

        // 处理层级
        if (level > currentLevel) {
            // 进入更深层级
            for (let i = currentLevel; i < level; i++) {
                tocHtml += '<ul class="toc-sub-list" data-level="' + (i + 1) + '">';
                levelStack.push('</ul>');
            }
        } else if (level < currentLevel) {
            // 返回更浅层级
            for (let i = currentLevel; i > level; i--) {
                tocHtml += '</ul>';
                levelStack.pop();
            }
        }

        currentLevel = level;

        // 检查是否有子标题
        const hasChildren = index < headings.length - 1 && parseInt(headings[index + 1].tagName.charAt(1)) > level;

        // 添加目录项
        tocHtml += '<li class="toc-item toc-level-' + level + (hasChildren ? ' has-children' : '') + '" data-index="' + index + '" data-level="' + level + '">';

        // 如果有子标题，添加折叠按钮
        if (hasChildren) {
            tocHtml += '<span class="mdi mdi-chevron-down toc-item-toggle" data-target-index="' + index + '"></span>';
        }

        tocHtml += '<a href="#' + id + '" class="toc-link" data-target="' + id + '" data-index="' + index + '">';
        tocHtml += text;
        tocHtml += '</a>';
        tocHtml += '</li>';
    });

    // 关闭所有未闭合的列表
    while (levelStack.length > 0) {
        tocHtml += levelStack.pop();
    }

    tocHtml += '</ul>';

    // 渲染目录
    tocContainer.innerHTML = tocHtml;

    // 初始化折叠状态
    initTOCFoldState();

    // 添加点击事件
    const tocLinks = tocContainer.querySelectorAll('.toc-link');
    tocLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                // 使用 scrollIntoView 滚动
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // 更新 URL hash
                history.pushState(null, null, '#' + targetId);

                // 展开当前项及其父级
                expandTOCItem(parseInt(this.getAttribute('data-index')));
            }
        });
    });

    // 添加折叠按钮点击事件
    const toggleButtons = tocContainer.querySelectorAll('.toc-item-toggle');
    toggleButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const targetIndex = parseInt(this.getAttribute('data-target-index'));
            toggleTOCItem(targetIndex);
        });
    });

    // 监听滚动，高亮当前目录项、自动展开/折叠并自动滚动
    window.addEventListener('scroll', function() {
        updateActiveTOCItem(headings, tocLinks);
        autoExpandCollapseTOC(headings);
        autoScrollTOC(headings);
    });
}

/**
 * 初始化目录折叠状态（默认展开所有）
 */
function initTOCFoldState() {
    const subLists = document.querySelectorAll('.toc-sub-list');
    subLists.forEach(function(list) {
        list.classList.add('expanded');
    });

    const toggleButtons = document.querySelectorAll('.toc-item-toggle');
    toggleButtons.forEach(function(btn) {
        btn.classList.remove('collapsed');
    });
}

/**
 * 切换目录项的折叠/展开状态
 * @param {number} index 目录项索引
 */
function toggleTOCItem(index) {
    const tocItem = document.querySelector('.toc-item[data-index="' + index + '"]');
    if (!tocItem) return;

    const toggleBtn = tocItem.querySelector('.toc-item-toggle');
    if (!toggleBtn) return;

    // 查找子列表
    let nextElement = tocItem.nextElementSibling;
    while (nextElement && nextElement.tagName !== 'UL') {
        nextElement = nextElement.nextElementSibling;
    }

    if (nextElement && nextElement.classList.contains('toc-sub-list')) {
        // 切换展开/折叠状态
        nextElement.classList.toggle('expanded');
        toggleBtn.classList.toggle('collapsed');
    }
}

/**
 * 展开指定目录项及其所有父级
 * @param {number} index 目录项索引
 */
function expandTOCItem(index) {
    const tocItem = document.querySelector('.toc-item[data-index="' + index + '"]');
    if (!tocItem) return;

    // 展开当前项
    const toggleBtn = tocItem.querySelector('.toc-item-toggle');
    if (toggleBtn) {
        toggleBtn.classList.remove('collapsed');
    }

    // 展开子列表
    let nextElement = tocItem.nextElementSibling;
    while (nextElement && nextElement.tagName !== 'UL') {
        nextElement = nextElement.nextElementSibling;
    }
    if (nextElement && nextElement.classList.contains('toc-sub-list')) {
        nextElement.classList.add('expanded');
    }

    // 递归展开父级
    let parentItem = tocItem.parentElement.closest('.toc-item');
    while (parentItem) {
        const parentIndex = parseInt(parentItem.getAttribute('data-index'));
        expandTOCItem(parentIndex);
        parentItem = parentItem.parentElement.closest('.toc-item');
    }
}

/**
 * 自动展开/折叠目录（根据滚动位置）
 * @param {NodeList} headings 标题列表
 */
function autoExpandCollapseTOC(headings) {
    const topBar = document.querySelector('.topbar');
    const topBarHeight = topBar ? topBar.offsetHeight : 88;
    const offset = topBarHeight + 20;

    // 获取标题层级
    const headingLevels = Array.from(headings).map(h => parseInt(h.tagName.charAt(1)));

    // 辅助函数：获取一个标题的范围（结束索引）
    function getHeadingRange(headingIndex) {
        const currentLevel = headingLevels[headingIndex];
        
        for (let i = headingIndex + 1; i < headingLevels.length; i++) {
            if (headingLevels[i] <= currentLevel) {
                return i;
            }
        }
        return headingLevels.length;
    }

    // 辅助函数：检查视口参考点是否在某个标题的范围内
    function isViewportInHeadingRange(headingIndex) {
        const heading = headings[headingIndex];
        const rangeEndIndex = getHeadingRange(headingIndex);
        
        if (rangeEndIndex === headingLevels.length) {
            // 最后一个标题，检查其顶部是否在视口上方
            const rect = heading.getBoundingClientRect();
            return rect.top <= offset;
        }
        
        const nextHeading = headings[rangeEndIndex];
        const rect = heading.getBoundingClientRect();
        const nextRect = nextHeading.getBoundingClientRect();
        
        // 视口参考点在标题范围之间
        const viewportRef = window.scrollY + offset;
        const headingTop = window.scrollY + rect.top;
        const nextHeadingTop = window.scrollY + nextRect.top;
        
        return viewportRef >= headingTop && viewportRef < nextHeadingTop;
    }

    // 找到当前激活的标题（视口参考点落在其范围内的标题）
    let currentHeadingIndex = -1;
    let maxLevel = 0;

    for (let i = 0; i < headings.length; i++) {
        if (isViewportInHeadingRange(i)) {
            const level = headingLevels[i];
            // 选择层级最深的标题
            if (level > maxLevel) {
                maxLevel = level;
                currentHeadingIndex = i;
            }
        }
    }

    // 如果没有找到，使用最接近视口顶部的标题
    if (currentHeadingIndex === -1) {
        let minDistance = Infinity;
        for (let i = 0; i < headings.length; i++) {
            const rect = headings[i].getBoundingClientRect();
            const distance = Math.abs(rect.top - offset);
            if (distance < minDistance) {
                minDistance = distance;
                currentHeadingIndex = i;
            }
        }
    }

    if (currentHeadingIndex === -1) return;

    // 构建"应该展开的标题索引集合"
    const shouldExpand = new Set();
    
    // 添加当前标题本身
    shouldExpand.add(currentHeadingIndex);
    
    // 添加当前标题的所有直接祖先（父级、祖父级等）
    // 从当前标题往前找，找到最近的层级更小的标题作为父级，然后再找父级的父级，以此类推
    let searchIndex = currentHeadingIndex - 1;
    let targetLevel = headingLevels[currentHeadingIndex];
    
    while (searchIndex >= 0 && targetLevel > 1) {
        // 找到第一个层级小于目标层级的标题
        while (searchIndex >= 0 && headingLevels[searchIndex] >= targetLevel) {
            searchIndex--;
        }
        
        if (searchIndex >= 0) {
            shouldExpand.add(searchIndex);
            targetLevel = headingLevels[searchIndex];
            searchIndex--;
        }
    }

    // 遍历所有有子元素的目录项，根据集合展开或折叠
    const tocItems = document.querySelectorAll('.toc-item.has-children');
    tocItems.forEach(function(item) {
        const index = parseInt(item.getAttribute('data-index'));
        const toggleBtn = item.querySelector('.toc-item-toggle');
        
        // 查找子列表
        let nextElement = item.nextElementSibling;
        while (nextElement && nextElement.tagName !== 'UL') {
            nextElement = nextElement.nextElementSibling;
        }
        
        if (nextElement && nextElement.classList.contains('toc-sub-list')) {
            if (shouldExpand.has(index)) {
                // 展开
                nextElement.classList.add('expanded');
                if (toggleBtn) toggleBtn.classList.remove('collapsed');
            } else {
                // 折叠
                nextElement.classList.remove('expanded');
                if (toggleBtn) toggleBtn.classList.add('collapsed');
            }
        }
    });
}

/**
 * 自动滚动目录到当前观看位置
 * @param {NodeList} headings 标题列表
 */
function autoScrollTOC(headings) {
    const topBar = document.querySelector('.topbar');
    const topBarHeight = topBar ? topBar.offsetHeight : 88;
    const offset = topBarHeight + 20;

    // 获取标题层级
    const headingLevels = Array.from(headings).map(h => parseInt(h.tagName.charAt(1)));

    // 辅助函数：获取一个标题的范围（结束索引）
    function getHeadingRange(headingIndex) {
        const currentLevel = headingLevels[headingIndex];
        
        for (let i = headingIndex + 1; i < headingLevels.length; i++) {
            if (headingLevels[i] <= currentLevel) {
                return i;
            }
        }
        return headingLevels.length;
    }

    // 辅助函数：检查视口参考点是否在某个标题的范围内
    function isViewportInHeadingRange(headingIndex) {
        const heading = headings[headingIndex];
        const rangeEndIndex = getHeadingRange(headingIndex);
        
        const headingRect = heading.getBoundingClientRect();
        const headingTop = window.scrollY + headingRect.top;
        
        let rangeBottom;
        if (rangeEndIndex < headingLevels.length) {
            const nextHeading = headings[rangeEndIndex];
            const nextRect = nextHeading.getBoundingClientRect();
            rangeBottom = window.scrollY + nextRect.top;
        } else {
            rangeBottom = document.documentElement.scrollHeight;
        }
        
        const viewportRef = window.scrollY + offset;
        return viewportRef >= headingTop && viewportRef < rangeBottom;
    }

    // 找到当前激活的标题
    let currentHeadingIndex = -1;
    let maxLevel = 0;

    for (let i = 0; i < headingLevels.length; i++) {
        if (isViewportInHeadingRange(i)) {
            const level = headingLevels[i];
            if (level > maxLevel) {
                maxLevel = level;
                currentHeadingIndex = i;
            }
        }
    }

    if (currentHeadingIndex === -1) {
        let minDistance = Infinity;
        headings.forEach(function(heading, index) {
            const rect = heading.getBoundingClientRect();
            const distance = Math.abs(rect.top - offset);
            if (distance < minDistance) {
                minDistance = distance;
                currentHeadingIndex = index;
            }
        });
    }

    if (currentHeadingIndex === -1) return;

    // 滚动目录到当前激活项
    const tocContainer = document.getElementById('article-toc');
    if (!tocContainer) return;

    const activeLink = document.querySelector('.toc-link[data-index="' + currentHeadingIndex + '"]');
    if (!activeLink) return;

    // 计算目录容器的滚动位置
    const containerRect = tocContainer.getBoundingClientRect();
    const linkRect = activeLink.getBoundingClientRect();
    
    // 目标位置：让激活项显示在目录容器的中间
    const targetScrollTop = tocContainer.scrollTop + (linkRect.top - containerRect.top) - (containerRect.height / 2) + (linkRect.height / 2);
    
    // 平滑滚动到目标位置
    tocContainer.scrollTo({
        top: targetScrollTop,
        behavior: 'smooth'
    });
}

/**
 * 更新当前激活的目录项
 * @param {NodeList} headings 标题列表
 * @param {NodeList} tocLinks 目录链接列表
 */
function updateActiveTOCItem(headings, tocLinks) {
    // 获取顶部导航栏高度
    const topBar = document.querySelector('.topbar');
    const topBarHeight = topBar ? topBar.offsetHeight : 88;
    const offset = topBarHeight + 20; // 额外添加 20px 偏移

    let currentHeading = null;
    let maxScrollPosition = -Infinity;

    // 找到当前可见的标题
    headings.forEach(function(heading) {
        const rect = heading.getBoundingClientRect();
        const scrollPosition = window.scrollY + rect.top;

        // 如果标题在视口上方（考虑导航栏高度）或者在视口内
        if (rect.top <= offset) {
            // 选择滚动位置最大的标题（即最接近视口顶部的标题）
            if (scrollPosition > maxScrollPosition) {
                maxScrollPosition = scrollPosition;
                currentHeading = heading;
            }
        }
    });

    // 移除所有激活状态
    tocLinks.forEach(function(link) {
        link.classList.remove('active');
    });

    // 添加激活状态
    if (currentHeading) {
        const activeLink = document.querySelector('.toc-link[data-target="' + currentHeading.id + '"]');
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }
}

/**
 * 初始化文章页面滚动效果
 */
function initArticlePageScrollEffect() {
    // 检查是否为文章页面
    if (document.body.id !== 'post') return;

    const topbar = document.querySelector('.topbar');
    if (!topbar) return;

    // 监听滚动事件
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const articleHeader = document.getElementById('articleHeader');

        if (articleHeader) {
            // 获取文章头部的高度
            const headerHeight = articleHeader.offsetHeight;

            // 当滚动超过文章头部的一定比例时，添加 scrolled 类
            if (scrollTop > headerHeight * 0.5) {
                topbar.classList.add('scrolled');
            } else {
                topbar.classList.remove('scrolled');
            }
        }
    });

    // 鼠标移入 topbar 时恢复
    topbar.addEventListener('mouseenter', function() {
        topbar.classList.add('scrolled');
    });

    // 鼠标移出 topbar 时，根据滚动位置决定是否恢复
    topbar.addEventListener('mouseleave', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const articleHeader = document.getElementById('articleHeader');

        if (articleHeader) {
            const headerHeight = articleHeader.offsetHeight;

            // 只有在头部范围内才移除 scrolled 类
            if (scrollTop <= headerHeight * 0.5) {
                topbar.classList.remove('scrolled');
            }
        }
    });
}

/**
 * 初始化图片懒加载和加载失败处理
 */
function initImageLazyLoad() {
    const markdownContent = document.querySelector('.markdown-content');
    if (!markdownContent) return;

    // 查找所有图片
    const images = markdownContent.querySelectorAll('img');

    images.forEach(function(img) {
        // 创建图片容器
        const container = document.createElement('div');
        container.className = 'img-container';

        // 将图片包裹在容器中
        img.parentNode.insertBefore(container, img);
        container.appendChild(img);

        // 添加懒加载属性
        img.setAttribute('loading', 'lazy');

        // 处理加载失败的函数
        function handleImageError() {
            img.style.display = 'none';
            var errorDiv = document.createElement('div');
            errorDiv.className = 'img-error';
            errorDiv.innerHTML = '<span class="mdi mdi-image-off"></span><span class="img-error-text">图片加载失败</span>';
            container.appendChild(errorDiv);
        }

        // 检查图片是否已经加载完成
        if (img.complete) {
            // 如果图片已经加载完成，检查是否加载成功
            if (img.naturalWidth === 0) {
                // 图片加载失败
                handleImageError();
            }
        } else {
            // 图片还在加载中，添加错误处理
            img.onerror = handleImageError;
        }
    });
}

/**
 * 初始化数学公式解析
 */
function initMathJax() {
    const markdownContent = document.querySelector('.markdown-content');
    if (!markdownContent) {
        return;
    }

    // 检查是否包含数学公式标记
    const hasMath = checkForMath(markdownContent);

    if (!hasMath) {
        return; // 没有数学公式，不加载 KaTeX
    }

    // 异步加载 KaTeX 并渲染公式
    loadKaTeX()
        .then(function() {
            renderMathInContent(markdownContent);
        })
        .catch(function(error) {
            console.error('数学公式解析失败:', error);
        });
}

/**
 * 检查内容中是否包含数学公式标记
 * @param {HTMLElement} content 内容元素
 * @returns {boolean}
 */
function checkForMath(content) {
    const html = content.innerHTML;

    // 检查行内公式 $...$ （排除代码块中的 $）
    // 先移除代码块内容
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    const codeBlocks = tempDiv.querySelectorAll('pre, code, kbd, samp');
    codeBlocks.forEach(block => block.remove());
    const textWithoutCode = tempDiv.innerHTML;

    // 检查行内公式 $...$ - 需要成对出现
    const inlineMathRegex = /\$[^$\n]+\$/g;
    if (inlineMathRegex.test(textWithoutCode)) {
        return true;
    }

    // 检查块级公式 $...$ 或 \[...\]
    const displayMathRegex = /\$\$[\s\S]*?\$\$|\\\[[\s\S]*?\\\]/g;
    if (displayMathRegex.test(textWithoutCode)) {
        return true;
    }

    // 检查 LaTeX 语法 \(...\)
    const latexInlineRegex = /\\\([^$]*?\\\)/g;
    if (latexInlineRegex.test(textWithoutCode)) {
        return true;
    }

    return false;
}

/**
 * 在内容中渲染数学公式
 * @param {HTMLElement} content 元素
 */
function renderMathInContent(content) {
    if (!window.katex || !window.renderMathInElement) {
        console.error('KaTeX 未正确加载');
        return;
    }

    // 配置 auto-render
    renderMathInElement(content, {
        delimiters: [
            { left: '$$', right: '$$', display: true },
            { left: '$', right: '$', display: false },
            { left: '\\(', right: '\\)', display: false },
            { left: '\\[', right: '\\]', display: true }
        ],
        throwOnError: false,
        errorColor: '#cc0000',
        strict: false,
        trust: false,
        ignoredTags: [
            'script',
            'noscript',
            'style',
            'textarea',
            'pre',
            'code'
        ],
        ignoredClasses: [
            'katex',
            'katex-display'
        ],
        macros: {
            "\\f": "#1f(#2)",
            "\\R": "\\mathbb{R}",
            "\\N": "\\mathbb{N}",
            "\\Z": "\\mathbb{Z}",
            "\\Q": "\\mathbb{Q}",
            "\\C": "\\mathbb{C}"
        }
    });
}

/**
 * 处理评论锚点滚动
 * 当页面加载时，如果 URL 中有评论锚点，滚动到评论上方 200px 的位置
 */
function handleCommentAnchorScroll() {
    // 检查 URL 中是否有评论锚点
    if (window.location.hash && window.location.hash.startsWith('#comment-')) {
        // 延迟执行，确保页面完全加载
        setTimeout(function() {
            const targetId = window.location.hash.substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                // 获取顶部导航栏高度
                const topBar = document.querySelector('.topbar');
                const topBarHeight = topBar ? topBar.offsetHeight : 88;
                
                // 计算滚动位置：元素顶部 - 导航栏高度 - 200px
                const offset = 200;
                const scrollPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - topBarHeight - offset;

                // 平滑滚动到目标位置
                window.scrollTo({
                    top: scrollPosition,
                    behavior: 'smooth'
                });

                // 高亮显示该评论
                targetElement.classList.add('comment-highlight');
                
                // 3秒后移除高亮
                setTimeout(function() {
                    targetElement.classList.remove('comment-highlight');
                }, 3000);
            }
        }, 100);
    }
}

/**
 * 初始化阅读进度
 */
function initReadingProgress() {
    const tocProgress = document.querySelector('.toc-progress');
    if (!tocProgress) return;

    // 监听滚动事件
    window.addEventListener('scroll', function() {
        updateReadingProgress(tocProgress);
    });

    // 初始化时更新一次
    updateReadingProgress(tocProgress);
}

/**
 * 更新阅读进度百分比
 * @param {HTMLElement} progressElement 进度显示元素
 */
function updateReadingProgress(progressElement) {
    const articleContent = document.querySelector('.markdown-content');
    if (!articleContent) return;

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    // 获取文章内容的位置
    const articleTop = articleContent.getBoundingClientRect().top + scrollTop;
    const articleBottom = articleContent.getBoundingClientRect().bottom + scrollTop;

    // 计算文章内容的高度
    const articleHeight = articleBottom - articleTop;

    // 计算已阅读的部分
    let readHeight = scrollTop + windowHeight / 2 - articleTop;

    // 限制在 0 到 articleHeight 之间
    readHeight = Math.max(0, Math.min(readHeight, articleHeight));

    // 计算百分比
    const percentage = Math.round((readHeight / articleHeight) * 100);

    // 更新显示
    progressElement.textContent = percentage + '%';
}

/**
 * 初始化音乐播放器
 */
function initMusicPlayer() {
    // 检查页面中是否有音乐播放器容器
    const musicContainers = document.querySelectorAll('.music-player-container');
    
    if (musicContainers.length === 0) {
        return; // 没有音乐播放器，不加载资源
    }

    // 动态加载 Aplayer CSS
    loadCSS('https://cdn.bootcdn.net/ajax/libs/aplayer/1.10.1/APlayer.min.css')
        .then(function() {
            // 加载 Aplayer JS
            return loadScript('https://cdn.bootcdn.net/ajax/libs/aplayer/1.10.1/APlayer.min.js');
        })
        .then(function() {
            
            // 初始化所有 Aplayer 实例
            const aplayerContainers = document.querySelectorAll('.aplayer[data-audio]');
            aplayerContainers.forEach(function(container) {
                try {
                    const audioData = JSON.parse(container.getAttribute('data-audio'));
                    const theme = container.getAttribute('data-theme') || '#C20C0C';
                    const loop = container.getAttribute('data-loop') || 'all';
                    const preload = container.getAttribute('data-preload') || 'auto';
                    const volume = parseFloat(container.getAttribute('data-volume')) || 0.7;
                    const mutex = container.getAttribute('data-mutex') === 'true';
                    const listFolded = container.getAttribute('data-list-folded') === 'true';
                    const listMaxHeight = container.getAttribute('data-list-max-height') || '250px';

                    new APlayer({
                        container: container,
                        autoplay: false,
                        theme: theme,
                        loop: loop,
                        preload: preload,
                        volume: volume,
                        mutex: mutex,
                        listFolded: listFolded,
                        listMaxHeight: listMaxHeight,
                        audio: audioData
                    });
                } catch (error) {
                    console.error('[Inaline Music] Aplayer 实例初始化失败:', error);
                }
            });
            
            // 检查是否有网易云歌单
            const hasMeting = document.querySelector('meting-js');
            if (hasMeting) {
                // 加载 MetingJS
                return loadScript('https://cdn.bootcdn.net/ajax/libs/meting/2.0.1/Meting.min.js');
            }
            return Promise.resolve();
        })
        .then(function() {
            if (document.querySelector('meting-js')) {
                // MetingJS 加载完成
            }
        })
        .catch(function(error) {
            console.error('[Inaline Music] 音乐播放器资源加载失败:', error);
        });
}

/**
 * 初始化网盘卡片复制功能
 */
function initNetdiskCopy() {
    const copyButtons = document.querySelectorAll('.netdisk-btn-copy');

    copyButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const code = this.getAttribute('data-code');
            if (!code) return;

            // 复制到剪贴板
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(function() {
                    showCopySuccess(btn, true);
                    showCopyrightNotice();
                }).catch(function(err) {
                    console.error('现代 API 复制失败，尝试降级方案：', err);
                    fallbackCopy(code, btn, true);
                });
            } else {
                fallbackCopy(code, btn, true);
            }
        });
    });
}

/**
 * 显示复制成功状态
 * @param {HTMLElement} btn 复制按钮元素
 */
function showCopySuccess(btn) {
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="mdi mdi-check"></span> 已复制';
    btn.classList.add('copied');

    setTimeout(function() {
        btn.innerHTML = originalHtml;
        btn.classList.remove('copied');
    }, 2000);
}

/**
 * 初始化外部链接跳转提示
 */
function initExternalLinkProtection() {
    // 获取当前域名
    const currentDomain = window.location.hostname;
    const currentHost = window.location.host;

    // 检查是否为文章页面
    const isArticlePage = document.body.id === 'post';

    // 如果不是文章页面，直接返回
    if (!isArticlePage) {
        return;
    }

    // 监听所有链接点击事件
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href) return;

        // 忽略 javascript:void(0) 和 javascript:;
        if (href.startsWith('javascript:') || href === 'void(0)' || href === '#') {
            return;
        }

        // 只处理以 // http:// 或 https:// 开头的链接
        if (!href.match(/^\/\/|^https?:\/\//i)) {
            return;
        }

        // 解析 URL
        let targetUrl;
        try {
            targetUrl = new URL(href, window.location.origin);
        } catch (err) {
            return;
        }

        // 检查是否为本站或本站子域名
        if (isSameDomain(targetUrl.hostname, currentDomain)) {
            return;
        }

        // 阻止默认跳转
        e.preventDefault();

        // 显示确认对话框
        showExternalLinkConfirm(targetUrl.href, link);
    });
}

/**
 * 检查是否为相同域名（包括子域名）
 */
function isSameDomain(hostname1, hostname2) {
    // 如果完全相同
    if (hostname1 === hostname2) {
        return true;
    }

    // 提取主域名（去掉 www. 和子域名）
    const getMainDomain = function(hostname) {
        const parts = hostname.split('.');
        if (parts.length < 2) return hostname;
        return parts.slice(-2).join('.');
    };

    return getMainDomain(hostname1) === getMainDomain(hostname2);
}

/**
 * 显示外部链接确认对话框
 */
function showExternalLinkConfirm(url, linkElement) {
    Swal.fire({
        title: '即将离开本站',
        html: '您即将跳转到外部链接：<br><code style="word-break: break-all;">' + escapeHtml(url) + '</code><br><br>我们无法保证该链接的安全性，请谨慎访问！',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '继续访问',
        cancelButtonText: '取消',
        confirmButtonColor: '#ff7946',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then(function(result) {
        if (result.isConfirmed) {
            // 继续访问
            window.open(url, '_blank');
        }
        // 如果点击取消，什么都不做
    });
}

/**
 * HTML 转义
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * 初始化折叠组件
 */
function initCollapse() {
    const collapseHeaders = document.querySelectorAll('.md-collapse-header');

    collapseHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            const collapse = this.closest('.md-collapse');
            if (collapse) {
                collapse.classList.toggle('expanded');
            }
        });
    });
}

// 在 DOMContentLoaded 中调用
document.addEventListener('DOMContentLoaded', function() {
    initCollapse();
});

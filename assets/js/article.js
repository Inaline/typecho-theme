/**
 * Inaline 主题文章页面脚本
 * @author Inaline Studio
 */

// 全局变量，用于跟踪 KaTeX 加载状态
window.katexLoaded = false;
window.katexLoading = false;

document.addEventListener('DOMContentLoaded', function() {
    // 为代码块添加行号（函数内部会处理高亮逻辑）
    if (typeof hljs !== 'undefined') {
        addLineNumbers();
    }

    // 初始化文章目录
    initTOC();

    // 初始化阅读进度
    initReadingProgress();

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

        // 重新构建代码内容（不带行号）
        block.textContent = lines.join('\n');

        // 移除可能存在的高亮标记
        block.removeAttribute('data-highlighted');
        block.classList.remove('hljs');

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
    // 克隆代码块，避免修改原始 DOM
    const clonedBlock = codeBlock.cloneNode(true);
    const codeLines = clonedBlock.querySelectorAll('.code-line');

    // 获取纯文本代码（行号由 CSS 伪元素生成，不会出现在文本中）
    const codeText = Array.from(codeLines).map(function(line) {
        return line.textContent;
    }).join('\n');

    // 复制到剪贴板
    if (navigator.clipboard) {
        navigator.clipboard.writeText(codeText).then(function() {
            showCopySuccess(copyBtn);
        }).catch(function(err) {
            console.error('复制失败:', err);
            fallbackCopy(codeText, copyBtn);
        });
    } else {
        fallbackCopy(codeText, copyBtn);
    }
}

/**
 * 显示复制成功状态
 */
function showCopySuccess(copyBtn) {
    const originalText = copyBtn.innerHTML;
    copyBtn.innerHTML = '<span class="mdi mdi-check"></span> 已复制';
    copyBtn.classList.add('copied');

    setTimeout(function() {
        copyBtn.innerHTML = originalText;
        copyBtn.classList.remove('copied');
    }, 2000);
}

/**
 * 降级复制方案
 */
function fallbackCopy(text, copyBtn) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    textarea.style.top = '0';
    textarea.style.width = '2em';
    textarea.style.height = '2em';
    textarea.style.padding = '0';
    textarea.style.border = 'none';
    textarea.style.outline = 'none';
    textarea.style.boxShadow = 'none';
    textarea.style.background = 'transparent';
    document.body.appendChild(textarea);

    // 选中文本
    textarea.focus();
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length); // 对于移动设备

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess(copyBtn);
        } else {
            throw new Error('execCommand copy failed');
        }
    } catch (err) {
        console.error('复制失败:', err);
        copyBtn.innerHTML = '<span class="mdi mdi-alert"></span> 复制失败';
    }

    document.body.removeChild(textarea);
}

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
                    console.log('分享失败:', err);
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
            alert('链接已复制到剪贴板！');
        }).catch(function(err) {
            console.log('复制失败:', err);
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
    console.log('更新点赞数:', articleId, isLiked);
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
            alert('评论回复功能暂不可用，请刷新页面重试');
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
        alert('加载评论失败，请重试');
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

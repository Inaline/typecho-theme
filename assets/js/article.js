/**
 * Inaline 主题文章页面脚本
 * @author Inaline Studio
 */

document.addEventListener('DOMContentLoaded', function() {
    // 为代码块添加行号（函数内部会处理高亮逻辑）
    if (typeof hljs !== 'undefined') {
        addLineNumbers();
    }

    // 初始化文章目录
    initTOC();

    // 初始化评论功能
    initComments();

    // 点赞按钮功能
    initLikeButton();

    // 分享按钮功能
    initShareButton();
});

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
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();

    try {
        document.execCommand('copy');
        showCopySuccess(copyBtn);
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

    headings.forEach(function(heading, index) {
        const level = parseInt(heading.tagName.charAt(1));
        const text = heading.textContent.trim();
        const id = 'heading-' + index;

        // 为标题添加 ID
        heading.id = id;

        // 处理层级
        if (level > currentLevel) {
            // 进入更深层级
            for (let i = currentLevel; i < level; i++) {
                tocHtml += '<ul class="toc-sub-list">';
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

        // 添加目录项
        tocHtml += '<li class="toc-item toc-level-' + level + '">';
        tocHtml += '<a href="#' + id + '" class="toc-link" data-target="' + id + '">';
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
            }
        });
    });

    // 监听滚动，高亮当前目录项
    window.addEventListener('scroll', function() {
        updateActiveTOCItem(headings, tocLinks);
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
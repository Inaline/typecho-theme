/**
 * Inaline 主题文章页面脚本
 * @author Inaline Studio
 */

document.addEventListener('DOMContentLoaded', function() {
    // 初始化代码高亮
    if (typeof hljs !== 'undefined') {
        hljs.highlightAll();
        // 为代码块添加行号
        addLineNumbers();
    }

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
        const code = block.innerHTML;
        // 将代码按行分割
        const lines = code.split('\n');

        // 移除最后一行（如果是空的）
        if (lines.length > 0 && lines[lines.length - 1].trim() === '') {
            lines.pop();
        }

        // 计算总行数
        const totalLines = lines.length;

        // 根据行数计算行号宽度
        const maxLineNumber = totalLines;
        const digits = maxLineNumber.toString().length;
        const charWidth = 7; // 每个字符大约 7px
        const lineNumberWidth = digits * charWidth + 10; // 加上一些边距

        // 为每一行添加行号
        const numberedCode = lines.map(function(line, index) {
            const lineNumber = index + 1;
            return '<span class="code-line"><span class="line-number" style="width: ' + lineNumberWidth + 'px;">' + lineNumber + '</span>' + line + '</span>';
        }).join('\n');

        block.innerHTML = numberedCode;

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

    // 获取纯文本代码（去除行号）
    const codeText = Array.from(codeLines).map(function(line) {
        // 移除行号元素，只保留代码内容
        const lineNumber = line.querySelector('.line-number');
        if (lineNumber) {
            lineNumber.remove();
        }
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
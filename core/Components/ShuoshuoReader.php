<?php
/**
 * Inaline 主题说说阅读组件
 * @author Inaline Studio
 *
 * @param array $data 传入的数据，包含：
 *   - title: 说说标题
 *   - content: 说说内容(已处理的自定义 Markdown 语法)
 *   - date: 发布日期
 *   - author: 作者
 *   - views: 阅读量
 *   - comments: 评论数
 *   - cid: 说说ID
 *   - url: 说说链接
 *   - fields: 自定义字段
 */
$data = $this->data;
$title = $data['title'] ?? '';
$content = $data['content'] ?? '';
$date = $data['date'] ?? '';
$author = $data['author'] ?? '';
$views = $data['views'] ?? 0;
$comments = $data['comments'] ?? 0;
$articleUrl = $data['url'] ?? '';
$articleId = $data['cid'] ?? '';

// 获取用户头像和名称
$userAvatar = Get::themeOption('sidebar_user_avatar', 'http://q1.qlogo.cn/g?b=qq&nk=2291374026&s=640');
$userName = Get::themeOption('sidebar_user_name', 'Inaline');

// 解析说说内容，分离 markdown 和图片
$parsedContent = [];
$markdown = $content;
$images = [];

// 分割内容，查找分隔线 %----------%
$parts = explode("%----------%", $content);

if (count($parts) >= 2) {
    // 第一部分是 markdown 内容
    $markdown = trim($parts[0]);

    // 第二部分开始是图片列表
    $imagePart = trim($parts[1]);

    // 解析图片列表
    $lines = explode("\n", $imagePart);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // 匹配 markdown 图片格式: ![描述][数字] 或 ![描述](url)
        if (preg_match('/!\[([^\]]*)\]\[(\d+)\]/', $line, $matches)) {
            // 引用格式，需要查找对应的链接定义
            $refId = $matches[2];
            if (preg_match('/\[' . $refId . '\]:\s*(.+)/', $imagePart, $urlMatch)) {
                $images[] = [
                    'url' => trim($urlMatch[1]),
                    'alt' => $matches[1]
                ];
            }
        } elseif (preg_match('/!\[([^\]]*)\]\(([^)]+)\)/', $line, $matches)) {
            // 直接链接格式
            $images[] = [
                'url' => $matches[2],
                'alt' => $matches[1]
            ];
        }
    }
} else {
    // 没有分隔线，全部作为 markdown 内容
    $markdown = $content;
}

// 从 markdown 内容中移除图片（如果有图片在 markdown 部分）
$markdown = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '', $markdown);
$markdown = preg_replace('/!\[([^\]]*)\]\[(\d+)\]/', '', $markdown);
?>

<div class="shuoshuo-reader" data-cid="<?= $articleId ?>">
    <div class="card shuoshuo-reader-content">
        <!-- 说说主体 -->
        <div class="shuoshuo-reader-main">
            <div class="shuoshuo-reader-avatar">
                <img src="<?= $userAvatar ?>" alt="<?= $userName ?>">
            </div>
            <div class="shuoshuo-reader-body">
                <div class="shuoshuo-reader-header">
                    <div class="shuoshuo-reader-author">
                        <span class="author-name"><?= $userName ?></span>
                    </div>
                    <div class="shuoshuo-reader-date">
                        <span class="mdi mdi-clock-outline"></span>
                        <?= $date ?>
                    </div>
                </div>
                <div class="shuoshuo-reader-content markdown-content">
                    <?= $markdown ?>
                </div>
                <?php if (!empty($images)): ?>
                    <div class="shuoshuo-reader-images">
                        <?php foreach ($images as $image): ?>
                            <div class="shuoshuo-reader-image-item">
                                <img src="<?= $image['url'] ?>" alt="<?= $image['alt'] ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 说说信息 -->
        <div class="shuoshuo-reader-meta">
            <div class="shuoshuo-reader-stats">
                <div class="shuoshuo-reader-stat">
                    <span class="mdi mdi-eye-outline"></span>
                    <span><?= $views ?></span>
                </div>
                <div class="shuoshuo-reader-stat">
                    <span class="mdi mdi-comment-outline"></span>
                    <span><?= $comments ?></span>
                </div>
            </div>
            <div class="shuoshuo-reader-actions">
                <button class="shuoshuo-reader-action-btn share-btn">
                    <span class="mdi mdi-share-variant"></span>
                    <span>分享</span>
                </button>
            </div>
        </div>
    </div>
</div>
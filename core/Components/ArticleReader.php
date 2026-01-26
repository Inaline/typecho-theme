<?php
/**
 * Inaline 主题文章阅读组件
 * @author Inaline Studio
 *
 * @param array $data 传入的数据，包含：
 *   - title: 文章标题
 *   - content: 文章内容(已处理的自定义 Markdown 语法)
 *   - date: 发布日期
 *   - author: 作者
 *   - views: 阅读量
 *   - comments: 评论数
 *   - likes: 点赞数
 *   - tags: 标签数组
 *   - categories: 分类数组
 *   - cid: 文章ID
 */
$data = $this->data;
$title = $data['title'] ?? '';
$content = $data['content'] ?? '';
$date = $data['date'] ?? '';
$author = $data['author'] ?? '';
$views = $data['views'] ?? 0;
$comments = $data['comments'] ?? 0;
$likes = $data['likes'] ?? 0;
$tags = $data['tags'] ?? [];
$categories = $data['categories'] ?? [];
$articleUrl = $data['url'] ?? '';
$articleId = $data['cid'] ?? '';
?>

<div class="article-reader">
    <div class="card article-reader-content">
        <div class="article-content markdown-content">
            <?= $content ?>
        </div>

        <!-- 文章标签 -->
        <?php if (!empty($tags)): ?>
        <div class="article-tags">
            <span class="article-tags-label">
                <span class="mdi mdi-tag-multiple"></span> 标签：
            </span>
            <?php foreach ($tags as $tag): ?>
            <a href="<?= $tag['url'] ?>" class="article-tag" title="<?= $tag['name'] ?>">
                <?= $tag['name'] ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- 点赞和分享按钮 -->
        <div class="article-actions">
            <button class="article-action-btn like-btn" data-likes="<?= $likes ?>" data-article-id="<?= $articleId ?>">
                <span class="mdi mdi-thumb-up"></span>
                <span class="article-action-text">点赞</span>
                <span class="article-action-count"><?= $likes ?></span>
            </button>
            <button class="article-action-btn share-btn">
                <span class="mdi mdi-share-variant"></span>
                <span class="article-action-text">分享</span>
            </button>
        </div>

        <!-- 文章信息 -->
        <div class="article-meta-info">
            <div class="article-meta-item">
                <span class="mdi mdi-copyright article-meta-icon"></span>
                <span class="article-meta-label">版权属于：</span>
                <span class="article-meta-value"><?= $author ?></span>
            </div>
            <div class="article-meta-item">
                <span class="mdi mdi-link article-meta-icon"></span>
                <span class="article-meta-label">本文链接：</span>
                <a href="<?= $articleUrl ?>" class="article-meta-link" target="_blank"><?= $articleUrl ?></a>
            </div>
            <div class="article-meta-item">
                <span class="mdi mdi-license article-meta-icon"></span>
                <span class="article-meta-label">作品采用：</span>
                <span class="article-meta-value">
                    <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh" target="_blank" rel="noopener noreferrer">
                        《署名-非商业性使用-相同方式共享 4.0 国际 (CC BY-NC-SA 4.0)》
                    </a>
                    许可协议授权
                </span>
            </div>
        </div>
    </div>
</div>
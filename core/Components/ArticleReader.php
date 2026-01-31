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

<div class="article-reader" data-cid="<?= $articleId ?>">
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

        <!-- 分享按钮 -->
        <div class="article-actions">
            <button class="article-action-btn share-btn">
                <span class="mdi mdi-share-variant"></span>
                <span class="article-action-text">分享</span>
            </button>
        </div>

        <!-- 文章信息 -->
        <div class="article-meta-info">
            <div class="article-meta-title"><?= $title ?></div>
            <div class="article-meta-url">
                <a href="<?= $articleUrl ?>" target="_blank" rel="noopener noreferrer" class="inaline-link">
                    <span class="mdi mdi-link-variant"></span>
                    <?= $articleUrl ?>
                </a>
            </div>
            <div class="article-meta-stats">
                <div class="article-meta-stat-item">
                    <div class="article-meta-stat-label">作者</div>
                    <div class="article-meta-stat-value"><?= $author ?></div>
                </div>
                <div class="article-meta-stat-item">
                    <div class="article-meta-stat-label">发布于</div>
                    <div class="article-meta-stat-value"><?= $date ?></div>
                </div>
                <div class="article-meta-stat-item">
                    <div class="article-meta-stat-label">更新于</div>
                    <div class="article-meta-stat-value"><?= $date ?></div>
                </div>
                <div class="article-meta-stat-item">
                    <div class="article-meta-stat-label">许可协议</div>
                    <div class="article-meta-stat-value">
                        <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/deed.zh" target="_blank" rel="noopener noreferrer" class="inaline-link">
                            CC BY-NC-SA 4
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
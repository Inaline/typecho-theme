<?php
/**
 * Inaline 主题文章列表组件
 * @author Inaline Studio
 *
 * @param array $data 传入的数据，包含：
 *   - sort: 当前排序方式 (date/views/comments/likes)
 *   - layout: 当前布局方式 (list/card)
 *   - p: 当前页码
 *   - total: 总文章数
 *   - per_page: 每页文章数
 */
$data = $this->data;
$currentSort = $data['sort'] ?? 'date';
$currentLayout = $data['layout'] ?? 'list';
$currentPage = max(1, intval($data['p'] ?? 1));
$totalArticles = intval($data['total'] ?? 0);
$perPage = intval($data['per_page'] ?? 10);
$totalPages = intval($data['total_pages'] ?? ceil($totalArticles / $perPage));

// 使用真实文章数据
$articles = $data['articles'] ?? [];
?>
<div class="card article-list">
    <div class="article-list-tabs">
        <div class="article-list-tabs-left">
            <a href="?sort=date&layout=<?= $currentLayout ?>&p=1" class="article-list-tab <?= $currentSort === 'date' ? 'active' : '' ?>" data-sort="date">
                最新文章
            </a>
            <a href="?sort=views&layout=<?= $currentLayout ?>&p=1" class="article-list-tab <?= $currentSort === 'views' ? 'active' : '' ?>" data-sort="views">
                阅读最多
            </a>
            <a href="?sort=comments&layout=<?= $currentLayout ?>&p=1" class="article-list-tab <?= $currentSort === 'comments' ? 'active' : '' ?>" data-sort="comments">
                评论最多
            </a>
            <a href="?sort=likes&layout=<?= $currentLayout ?>&p=1" class="article-list-tab <?= $currentSort === 'likes' ? 'active' : '' ?>" data-sort="likes">
                热门文章
            </a>
        </div>
        <div class="article-list-tabs-right">
            <a href="?sort=<?= $currentSort ?>&layout=list&p=<?= $currentPage ?>" class="article-list-layout-btn <?= $currentLayout === 'list' ? 'active' : '' ?>" data-layout="list" title="列表视图">
                <span class="mdi mdi-format-list-bulleted"></span>
            </a>
            <a href="?sort=<?= $currentSort ?>&layout=card&p=<?= $currentPage ?>" class="article-list-layout-btn <?= $currentLayout === 'card' ? 'active' : '' ?>" data-layout="card" title="卡片视图">
                <span class="mdi mdi-view-grid"></span>
            </a>
        </div>
    </div>
    <div class="article-list-content article-list-<?= $currentLayout ?>">
        <?php if (empty($articles)): ?>
            <div class="article-list-empty">
                <div class="article-list-empty-icon">
                    <span class="mdi mdi-file-document-outline"></span>
                </div>
                <div class="article-list-empty-text">
                    暂无文章
                </div>
            </div>
        <?php elseif ($currentLayout === 'list'): ?>
            <div class="article-list-items">
                <?php foreach ($articles as $article): ?>
                <article class="article-item">
                    <a href="<?= $article['url'] ?>" class="article-item-link">
                        <div class="article-item-thumbnail">
                            <img src="<?= $article['thumbnail'] ?: Get::Assets('assets/images/placeholder.png') ?>" alt="<?= $article['title'] ?>" />
                        </div>
                        <div class="article-item-content">
                            <h3 class="article-item-title"><?= $article['title'] ?></h3>
                            <p class="article-item-excerpt"><?= $article['excerpt'] ?></p>
                            <div class="article-item-meta">
                                <span class="article-item-date">
                                    <span class="mdi mdi-calendar"></span>
                                    <?= $article['date'] ?>
                                </span>
                                <span class="article-item-views">
                                    <span class="mdi mdi-eye"></span>
                                    <?= $article['views'] ?>
                                </span>
                                <span class="article-item-comments">
                                    <span class="mdi mdi-comment"></span>
                                    <?= $article['comments'] ?>
                                </span>
                                <span class="article-item-likes">
                                    <span class="mdi mdi-heart"></span>
                                    <?= $article['likes'] ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="article-list-cards">
                <?php foreach ($articles as $article): ?>
                <article class="article-card">
                    <a href="<?= $article['url'] ?>" class="article-card-link">
                        <div class="article-card-thumbnail">
                            <img src="<?= $article['thumbnail'] ?>" alt="<?= $article['title'] ?>" />
                        </div>
                        <div class="article-card-content">
                            <h3 class="article-card-title"><?= $article['title'] ?></h3>
                            <p class="article-card-excerpt"><?= $article['excerpt'] ?></p>
                            <div class="article-card-meta">
                                <span class="article-card-date">
                                    <span class="mdi mdi-calendar"></span>
                                    <?= $article['date'] ?>
                                </span>
                                <span class="article-card-views">
                                    <span class="mdi mdi-eye"></span>
                                    <?= $article['views'] ?>
                                </span>
                                <span class="article-card-comments">
                                    <span class="mdi mdi-comment"></span>
                                    <?= $article['comments'] ?>
                                </span>
                                <span class="article-card-likes">
                                    <span class="mdi mdi-heart"></span>
                                    <?= $article['likes'] ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="article-list-pagination">
        <?php if ($currentPage > 1): ?>
        <a href="?sort=<?= $currentSort ?>&layout=<?= $currentLayout ?>&p=<?= $currentPage - 1 ?>" class="pagination-btn pagination-prev">
            <span class="mdi mdi-chevron-left"></span>
            上一页
        </a>
        <?php endif; ?>
        
        <div class="pagination-pages">
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            if ($startPage > 1) {
                echo '<a href="?sort=' . $currentSort . '&layout=' . $currentLayout . '&p=1" class="pagination-page">1</a>';
                if ($startPage > 2) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
            }
            
            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = $i === $currentPage ? 'active' : '';
                echo '<a href="?sort=' . $currentSort . '&layout=' . $currentLayout . '&p=' . $i . '" class="pagination-page ' . $activeClass . '">' . $i . '</a>';
            }
            
            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                echo '<a href="?sort=' . $currentSort . '&layout=' . $currentLayout . '&p=' . $totalPages . '" class="pagination-page">' . $totalPages . '</a>';
            }
            ?>
        </div>
        
        <?php if ($currentPage < $totalPages): ?>
        <a href="?sort=<?= $currentSort ?>&layout=<?= $currentLayout ?>&p=<?= $currentPage + 1 ?>" class="pagination-btn pagination-next">
            下一页
            <span class="mdi mdi-chevron-right"></span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
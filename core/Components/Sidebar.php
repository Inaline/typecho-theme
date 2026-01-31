<?php
/**
 * Inaline 主题侧边栏组件
 * @author Inaline Studio
 *
 * @param string $type 卡片类型
 *   - 'user': 用户信息卡片
 *   - 'test': 测试卡片
 * @param array $data 卡片数据
 */
?>
<?php
$type = $this->data->type ?? '';
$data = $this->data->data ?? [];

switch ($type) {
    case 'user':
        // 用户信息卡片
        $status = $data['status'] ?? 'EMOing';
        $avatar = $data['avatar'] ?? '';
        $name = $data['name'] ?? 'Inaline';
        $bio = $data['bio'] ?? '';
        $qq = $data['qq'] ?? '';
        $email = $data['email'] ?? '';
        $bilibili = $data['bilibili'] ?? '';
        $article_count = $data['article_count'] ?? 0;
        $comment_count = $data['comment_count'] ?? 0;
        ?>
        <div class="card user-card">
            <div class="card-content">
                <div class="user-info-row">
                    <div class="user-avatar">
                        <?php if ($avatar): ?>
                            <img src="<?= e($avatar) ?>" alt="<?= e($name) ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder"><?= mb_substr($name, 0, 1, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="user-info-text">
                        <div class="user-name-row">
                            <div class="user-name"><?= e($name) ?></div>
                            <div class="user-status-badge"><?= e($status) ?></div>
                        </div>
                        <div class="user-bio"><?= e($bio) ?></div>
                    </div>
                </div>
                <div class="user-bottom-row">
                    <div class="user-stats">
                        <div class="user-stat-item">
                            <div class="user-stat-value"><?= e($article_count) ?></div>
                            <div class="user-stat-label">文章</div>
                        </div>
                        <div class="user-stat-item">
                            <div class="user-stat-value"><?= e($comment_count) ?></div>
                            <div class="user-stat-label">评论</div>
                        </div>
                    </div>
                    <?php if (!empty($qq) || !empty($email) || !empty($bilibili)): ?>
                    <div class="user-contacts">
                        <?php if (!empty($qq)): ?>
                        <a href="tencent://message/?uin=<?= e($qq) ?>&Site=&Menu=yes" class="user-contact-item" title="QQ">
                            <span class="mdi mdi-qqchat"></span>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($email)): ?>
                        <a href="mailto:<?= e($email) ?>" class="user-contact-item" title="邮箱">
                            <span class="mdi mdi-email"></span>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($bilibili)): ?>
                        <a href="<?= e($bilibili) ?>" class="user-contact-item" title="Bilibili" target="_blank">
                            <span class="mdi mdi-television"></span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        break;

    case 'hot_articles':
        // 热门文章卡片
        $articles = $data['articles'] ?? [];
        $sort = $data['sort'] ?? 'views';
        ?>
        <div class="card hot-articles-card">
            <div class="card-title">热门文章</div>
            <div class="card-content">
                <?php if (!empty($articles)): ?>
                <ul class="article-list">
                    <?php foreach ($articles as $article): ?>
                    <li class="article-item">
                        <a href="<?= e($article['url']) ?>" class="article-link" title="<?= e($article['title']) ?>">
                            <div class="article-thumbnail">
                                <img src="<?= e($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>">
                            </div>
                            <div class="article-info">
                                <div class="article-title"><?= e($article['title']) ?></div>
                                <div class="article-meta">
                                    <span class="article-date"><span class="mdi mdi-calendar"></span> <?= e($article['created']) ?></span>
                                    <?php if ($sort === 'views'): ?>
                                    <span class="article-views"><span class="mdi mdi-eye"></span> <?= e($article['views']) ?></span>
                                    <?php elseif ($sort === 'comments'): ?>
                                    <span class="article-comments"><span class="mdi mdi-comment"></span> <?= e($article['comments']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-text">暂无文章</div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        break;

    case 'recent_comments':
        // 最新评论卡片
        $comments = $data['comments'] ?? [];
        ?>
        <div class="card recent-comments-card">
            <div class="card-title">最新评论</div>
            <div class="card-content">
                <?php if (!empty($comments)): ?>
                <ul class="recent-comments-list">
                    <?php foreach ($comments as $comment): ?>
                    <li class="recent-comments-item">
                        <div class="recent-comments-avatar">
                            <?= GetAvatar::generate($comment['author'], '', 32, false) ?>
                        </div>
                        <div class="recent-comments-content">
                            <div class="recent-comments-header">
                                <span class="recent-comments-author"><?= e($comment['author']) ?></span>
                                <span class="recent-comments-date"><?= e($comment['created']) ?></span>
                            </div>
                            <div class="recent-comments-text">
                                <?= e($comment['text']) ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-text">暂无评论</div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        break;

    case 'toc':
        // 文章目录卡片
        ?>
        <div class="card toc-card">
            <div class="card-title">文章目录</div>
            <div class="card-content">
                <div id="article-toc" class="article-toc">
                    <div class="toc-loading">正在生成目录...</div>
                </div>
            </div>
        </div>
        <?php
        break;

    default:
        // 默认卡片
        ?>
        <div class="card">
            <div class="card-content">
                <?= e($data['content'] ?? '') ?>
            </div>
        </div>
        <?php
        break;
}
?>
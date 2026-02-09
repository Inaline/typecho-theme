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
                            <?= GetAvatar::generate($comment['author'], $comment['mail'] ?? '', 32, false) ?>
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

    case 'random_word':
        // 随机一言卡片
        $word = $data['word'] ?? '暂无一言';
        ?>
        <div class="card random-word-card">
            <div class="card-title">随机一言</div>
            <div class="card-content">
                <div class="random-word-container">
                    <div class="quote-icon quote-open">
                        <svg width="24" height="24" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M27.194,12l0,8.025c-2.537,0.14 -4.458,0.603 -5.761,1.39c-1.304,0.787 -2.22,2.063 -2.749,3.829c-0.528,1.766 -0.793,4.292 -0.793,7.579l9.303,0l0,19.145l-19.081,0l0,-18.201c0,-7.518 1.612,-13.025 4.836,-16.522c3.225,-3.497 7.973,-5.245 14.245,-5.245Zm28.806,0l0,8.025c-2.537,0.14 -4.457,0.586 -5.761,1.338c-1.304,0.751 -2.247,2.028 -2.828,3.829c-0.581,1.8 -0.872,4.344 -0.872,7.631l9.461,0l0,19.145l-19.186,0l0,-18.201c0,-7.518 1.603,-13.025 4.809,-16.522c3.207,-3.497 7.999,-5.245 14.377,-5.245Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="random-word-text">
                        <?= e($word) ?>
                    </div>
                    <div class="quote-icon quote-close">
                        <svg width="24" height="24" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M36.806,52l0,-8.025c2.537,-0.14 4.458,-0.603 5.761,-1.39c1.304,-0.787 2.22,-2.063 2.749,-3.829c0.528,-1.766 0.793,-4.292 0.793,-7.579l-9.303,0l0,-19.145l19.081,0l0,18.201c0,7.518 -1.612,13.025 -4.836,16.522c-3.225,3.497 -7.973,5.245 -14.245,5.245Zm-28.806,0l0,-8.025c2.537,-0.14 4.457,-0.586 5.761,-1.338c1.304,-0.751 2.247,-2.028 2.828,-3.829c0.581,-1.8 0.872,-4.344 0.872,-7.631l-9.461,0l0,-19.145l19.186,0l0,18.201c0,7.518 -1.603,13.025 -4.809,16.522c-3.207,3.497 -7.999,5.245 -14.377,5.245Z" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <?php
        break;

    case 'toc':
        // 文章目录卡片
        ?>
        <div class="card toc-card">
            <div class="card-title">
                文章目录
                <span class="toc-progress">0%</span>
            </div>
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
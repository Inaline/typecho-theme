<?php
/**
 * Inaline 主题评论列表组件
 * @author Inaline Studio
 */
// 初始化 Typecho 评论系统（这是评论提交功能正常工作所必需的）
$this->comments()->to($typechoComments);

$data = $this->data;
$total = $data['total'] ?? 0;
$page = $data['page'] ?? 1;
$totalPages = $data['totalPages'] ?? 1;
$order = $data['order'] ?? 'desc';
$comments = $data['comments'] ?? [];

// 检查用户是否登录
$user = \Widget\User::alloc();
$isLoggedIn = $user->hasLogin();
?>
<!-- 评论列表组件 -->
<div class="comments-section" id="comments">
    <div class="card comments-card">
        <!-- 发布评论表单 -->
        <?php if ($this->allow('comment')): ?>
        <div id="<?php $this->respondId(); ?>" class="comment-form-wrapper">
            <div class="cancel-comment-reply">
                <?php $typechoComments->cancelReply(); ?>
            </div>
            <div class="comment-form-header">
                <h3 class="comment-form-title">
                    <i class="mdi mdi-comment-edit-outline"></i>
                    发表评论
                </h3>
            </div>
            <form id="comment-form" method="post" action="<?php $this->commentUrl() ?>#comments" class="comment-form">
                <input type="hidden" name="parent" id="comment-parent" value="0">
                <?php if (!$isLoggedIn): ?>
                <!-- 未登录用户显示昵称、邮箱、网站字段 -->
                <div class="comment-form-fields">
                    <div class="form-group">
                        <input type="text" name="author" placeholder="昵称 *" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="mail" placeholder="邮箱 *" required>
                    </div>
                    <div class="form-group">
                        <input type="url" name="url" placeholder="网站">
                    </div>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <textarea name="text" placeholder="写下你的评论..." required></textarea>
                </div>
                <div class="comment-form-actions">
                    <button type="submit" class="submit-btn">
                        <i class="mdi mdi-send"></i>
                        发表评论
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        <div class="comments-header">
            <h3 class="comments-title">
                <i class="mdi mdi-comment-multiple-outline"></i>
                评论 (<span class="comments-count"><?= $total ?></span>)
            </h3>
            <div class="comments-sort">
                <a href="?comments-order=asc#comments" class="sort-btn <?= $order === 'asc' ? 'active' : '' ?>">
                    <i class="mdi mdi-clock-in"></i>
                    最早
                </a>
                <a href="?comments-order=desc#comments" class="sort-btn <?= $order === 'desc' ? 'active' : '' ?>">
                    <i class="mdi mdi-clock-outline"></i>
                    最新
                </a>
            </div>
        </div>

        <div class="comments-list">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <!-- 一级评论 -->
                    <div class="comment-item" data-comment-id="<?= $comment['coid'] ?>" id="comment-<?= $comment['coid'] ?>">
                        <div class="comment-main">
                            <div class="comment-header">
                                <div class="comment-avatar">
                                    <?= GetAvatar::generate($comment['author'], $comment['mail'] ?? '', 32, false) ?>
                                </div>
                                <div class="comment-author">
                                    <span class="author-name"><?= htmlspecialchars($comment['author']) ?></span>
                                    <?php if ($comment['authorId'] > 0): ?>
                                        <span class="author-badge">博主</span>
                                    <?php endif; ?>
                                </div>
                                <div class="comment-meta">
                                    <span class="comment-date"><?= $comment['created'] ?></span>
                                    <span class="comment-floor"><?= $comment['floor'] ?></span>
                                </div>
                            </div>
                            <div class="comment-content">
                                <?= $comment['text'] ?>
                            </div>
                            <div class="comment-actions">
                                <button class="action-btn reply-btn" data-comment-id="<?= $comment['coid'] ?>">
                                    <i class="mdi mdi-reply"></i>
                                    回复
                                </button>
                            </div>
                        </div>

                        <!-- 二级评论 -->
                        <?php if (!empty($comment['children'])): ?>
                            <div class="comment-replies">
                                <?php foreach ($comment['children'] as $child): ?>
                                    <div class="comment-item reply-item" data-comment-id="<?= $child['coid'] ?>" data-parent-id="<?= $comment['coid'] ?>" id="comment-<?= $child['coid'] ?>">
                                        <div class="comment-main">
                                            <div class="comment-header">
                                                <div class="comment-avatar">
                                                    <?= GetAvatar::generate($child['author'], $child['mail'] ?? '', 28, false) ?>
                                                </div>
                                                <div class="comment-author">
                                                    <span class="author-name"><?= htmlspecialchars($child['author']) ?></span>
                                                </div>
                                                <div class="comment-meta">
                                                    <span class="comment-date"><?= $child['created'] ?></span>
                                                    <span class="comment-floor"><?= $child['floor'] ?></span>
                                                </div>
                                            </div>
                                            <div class="comment-content">
                                                <?php if (!empty($child['parent'])): ?>
                                                    <a href="#comment-<?= $child['coid'] ?>" class="reply-to">@<?= htmlspecialchars($child['parent']) ?></a>
                                                <?php endif; ?>
                                                <?= $child['text'] ?>
                                            </div>
                                            <div class="comment-actions">
                                                <button class="action-btn reply-btn" data-comment-id="<?= $child['coid'] ?>" data-parent-id="<?= $comment['coid'] ?>">
                                                    <i class="mdi mdi-reply"></i>
                                                    回复
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="comment-empty">
                    <i class="mdi mdi-comment-text-outline"></i>
                    <p>暂无评论，快来抢沙发吧！</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 分页器 -->
        <?php if ($totalPages > 1): ?>
            <div class="comments-pagination">
                <?php if ($page > 1): ?>
                    <a href="?comments-page=<?= $page - 1 ?>&comments-order=<?= $order ?>#comments" class="pagination-btn prev-btn">
                        <i class="mdi mdi-chevron-left"></i>
                        上一页
                    </a>
                <?php else: ?>
                    <button class="pagination-btn prev-btn" disabled>
                        <i class="mdi mdi-chevron-left"></i>
                        上一页
                    </button>
                <?php endif; ?>

                <div class="pagination-pages">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <button class="pagination-page active" data-page="<?= $i ?>"><?= $i ?></button>
                        <?php else: ?>
                            <a href="?comments-page=<?= $i ?>&comments-order=<?= $order ?>#comments" class="pagination-page"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="?comments-page=<?= $page + 1 ?>&comments-order=<?= $order ?>#comments" class="pagination-btn next-btn">
                        下一页
                        <i class="mdi mdi-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <button class="pagination-btn next-btn" disabled>
                        下一页
                        <i class="mdi mdi-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
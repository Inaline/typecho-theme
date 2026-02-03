<?php
/**
 * Inaline 主题说说列表组件
 * @author Inaline Studio
 *
 * @param array $data 传入的数据，包含：
 *   - shuoshuos: 说说数组
 *   - total: 总数
 *   - page: 当前页码
 *   - page_size: 每页数量
 *   - total_pages: 总页数
 *   - single_mode: 是否为单说详情模式
 */
$data = $this->data;
$shuoshuos = $data['shuoshuos'] ?? [];
$total = $data['total'] ?? 0;
$page = $data['page'] ?? 1;
$pageSize = $data['page_size'] ?? 10;
$totalPages = $data['total_pages'] ?? 1;
$singleMode = $data['single_mode'] ?? false;

// 获取用户头像和名称
$userAvatar = Get::themeOption('sidebar_user_avatar', 'http://q1.qlogo.cn/g?b=qq&nk=2291374026&s=640');
$userName = Get::themeOption('sidebar_user_name', 'Inaline');

// 获取当前页面 URL
$currentUrl = $this->request->getRequestUrl();
$currentPath = parse_url($currentUrl, PHP_URL_PATH);

// 获取说说封面图
$shuoshuoCover = Get::themeOption('shuoshuo_cover', Get::Assets('assets/images/cover/cover1.jpg'));
$shuoshuoCover = Get::resolveUri($shuoshuoCover);

// 获取用户简介
$userBio = Get::themeOption('sidebar_user_bio', '');
?>

<!-- 说说列表组件 -->
<div class="shuoshuo-section" id="shuoshuo">
    <div class="card shuoshuo-card">
        <!-- 封面背景图 -->
        <div class="shuoshuo-cover" style="background-image: url('<?= $shuoshuoCover ?>');">
            <div class="shuoshuo-cover-overlay"></div>
            <div class="shuoshuo-cover-content">
                <div class="shuoshuo-cover-userinfo">
                    <span class="shuoshuo-cover-username"><?= $userName ?></span>
                    <?php if (!empty($userBio)): ?>
                        <span class="shuoshuo-cover-bio"><?= $userBio ?></span>
                    <?php endif; ?>
                </div>
                <div class="shuoshuo-cover-avatar">
                    <img src="<?= $userAvatar ?>" alt="<?= $userName ?>">
                </div>
            </div>
        </div>

        <div class="shuoshuo-list">
            <?php if (!empty($shuoshuos)): ?>
                <?php foreach ($shuoshuos as $index => $shuoshuo): ?>
                    <div class="shuoshuo-item" data-shuoshuo-id="<?= $shuoshuo['cid'] ?>">
                        <div class="shuoshuo-main">
                            <div class="shuoshuo-avatar">
                                <img src="<?= $userAvatar ?>" alt="<?= $userName ?>">
                            </div>
                            <div class="shuoshuo-body">
                                <div class="shuoshuo-header-info">
                                    <div class="shuoshuo-author">
                                        <span class="author-name"><?= $userName ?></span>
                                    </div>
                                </div>
                                <div class="shuoshuo-content">
                                    <div class="markdown-content">
                                        <?= $shuoshuo['content'] ?>
                                    </div>
                                </div>
                                <?php if (!empty($shuoshuo['images'])): ?>
                                    <div class="shuoshuo-images">
                                        <?php foreach ($shuoshuo['images'] as $image): ?>
                                            <div class="shuoshuo-image-item">
                                                <img src="<?= $image['url'] ?>" alt="<?= $image['alt'] ?>" loading="lazy">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="shuoshuo-footer">
                                    <span class="shuoshuo-date-full"><?= $shuoshuo['created_date'] . ' ' . $shuoshuo['created_time'] ?></span>
                                    <div class="shuoshuo-actions">
                                        <?php if ($shuoshuo['allow_comment']): ?>
                                            <a href="<?= $shuoshuo['url'] ?>#comments" class="action-btn">
                                                <i class="mdi mdi-comment-outline"></i>
                                                <?= $shuoshuo['comments'] > 0 ? $shuoshuo['comments'] : '评论' ?>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= $shuoshuo['url'] ?>" class="action-btn">
                                            <i class="mdi mdi-link-variant"></i>
                                            详情
                                        </a>
                                    </div>
                                </div>
                                <?php if (!$singleMode && $shuoshuo['allow_comment'] && !empty($shuoshuo['comment_list'])): ?>
                                    <div class="shuoshuo-comments">
                                        <?php foreach ($shuoshuo['comment_list'] as $comment): ?>
                                            <div class="shuoshuo-comment-item">
                                                <span class="comment-user"><?= htmlspecialchars($comment['author']) ?></span>
                                                <span class="comment-text">：<?= htmlspecialchars($comment['text']) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if ($shuoshuo['comments'] > 2): ?>
                                            <a href="<?= $shuoshuo['url'] ?>#comments" class="comment-more">查看全部 <?= $shuoshuo['comments'] ?> 条评论</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="shuoshuo-empty">
                    <i class="mdi mdi-comment-text-outline"></i>
                    <p>还没有发布说说哦~</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 分页器（列表模式显示，详情模式隐藏） -->
        <?php if (!$singleMode): ?>
        <div class="shuoshuo-pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="pagination-btn prev-btn">
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
                        <a href="?page=<?= $i ?>" class="pagination-page"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="pagination-btn next-btn">
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
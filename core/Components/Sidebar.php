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

    case 'test':
        // 测试卡片
        $content = $data['content'] ?? '测试文字';
        ?>
        <div class="card test-card">
            <div class="card-content">
                <?= e($content) ?>
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
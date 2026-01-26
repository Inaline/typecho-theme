<?php
/**
 * Inaline 主题评论列表组件
 * @author Inaline Studio
 *
 * @param array $data 传入的数据，包含：
 *   - cid: 文章ID
 *   - page: 当前页码
 *   - pageSize: 每页评论数
 *   - order: 排序方式 ('asc' | 'desc')
 */
$data = $this->data;
$cid = $data['cid'] ?? 0;
$page = $data['page'] ?? 1;
$pageSize = $data['pageSize'] ?? 10;
$order = $data['order'] ?? 'desc';

// 获取所有评论
$allComments = GetComment::byArticle($cid, ['coid', 'author', 'mail', 'url', 'text', 'created', 'parent', 'status'], 'created', $order, 0);

// 过滤出已批准的评论
$approvedComments = array_filter($allComments, function($comment) {
    return isset($comment['status']) && $comment['status'] === 'approved';
});

// 测试数据：如果评论少于3条，添加一些测试评论
if (count($approvedComments) < 3) {
    $testComments = [
        [
            'coid' => 'test1',
            'author' => '测试用户1',
            'mail' => 'test1@example.com',
            'url' => '',
            'text' => '这是一条测试评论，用于测试分页功能。这条评论应该显示在评论列表中。',
            'created' => time() - 3600,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test2',
            'author' => '测试用户2',
            'mail' => 'test2@example.com',
            'url' => 'https://example.com',
            'text' => '这是第二条测试评论。测试评论功能是否正常工作，包括评论的显示和样式。',
            'created' => time() - 7200,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test3',
            'author' => '测试用户3',
            'mail' => 'test3@example.com',
            'url' => '',
            'text' => '这是第三条测试评论，用于测试分页功能。当评论数量超过每页显示数量时，应该显示分页按钮。',
            'created' => time() - 10800,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test4',
            'author' => '测试用户4',
            'mail' => 'test4@example.com',
            'url' => '',
            'text' => '这是第四条测试评论。测试评论的样式是否正确，包括头像、昵称、时间和内容。',
            'created' => time() - 14400,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test5',
            'author' => '测试用户5',
            'mail' => 'test5@example.com',
            'url' => 'https://example.com',
            'text' => '这是第五条测试评论，用于测试评论的排序功能。点击"最新"和"最早"按钮应该能正确排序。',
            'created' => time() - 18000,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test6',
            'author' => '测试用户6',
            'mail' => 'test6@example.com',
            'url' => '',
            'text' => '这是第六条测试评论。测试评论的回复功能是否正常，点击回复按钮应该能显示回复表单（功能待实现）。',
            'created' => time() - 21600,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test7',
            'author' => '测试用户7',
            'mail' => 'test7@example.com',
            'url' => '',
            'text' => '这是第七条测试评论，用于测试评论的树形结构。这条评论应该有子评论。',
            'created' => time() - 25200,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test8',
            'author' => '测试用户8',
            'mail' => 'test8@example.com',
            'url' => 'https://example.com',
            'text' => '这是第八条测试评论。测试评论的样式是否在不同屏幕尺寸下都能正常显示。',
            'created' => time() - 28800,
            'parent' => 'test7',
            'status' => 'approved'
        ],
        [
            'coid' => 'test9',
            'author' => '测试用户9',
            'mail' => 'test9@example.com',
            'url' => '',
            'text' => '这是第九条测试评论，用于测试评论的分页功能。点击下一页按钮应该能加载更多评论。',
            'created' => time() - 32400,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test10',
            'author' => '测试用户10',
            'mail' => 'test10@example.com',
            'url' => '',
            'text' => '这是第十条测试评论。测试评论的样式是否与主题整体风格保持一致。',
            'created' => time() - 36000,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test11',
            'author' => '测试用户11',
            'mail' => 'test11@example.com',
            'url' => 'https://example.com',
            'text' => '这是第十一条测试评论，用于测试评论的第二页显示。',
            'created' => time() - 39600,
            'parent' => '0',
            'status' => 'approved'
        ],
        [
            'coid' => 'test12',
            'author' => '测试用户12',
            'mail' => 'test12@example.com',
            'url' => '',
            'text' => '这是第十二条测试评论。测试评论的样式是否正确渲染。',
            'created' => time() - 43200,
            'parent' => '0',
            'status' => 'approved'
        ]
    ];

    // 合并真实评论和测试评论
    $approvedComments = array_merge($approvedComments, $testComments);
}

// 获取评论总数（只统计已批准的评论）
$totalComments = count($approvedComments);
$totalPages = ceil($totalComments / $pageSize);

// 分页
$comments = array_slice($approvedComments, ($page - 1) * $pageSize, $pageSize);

// 构建评论树形结构（只获取顶级评论的子评论）
$commentTree = GetComment::buildTree($comments);
?>

<div class="comment-section" data-cid="<?= $cid ?>">
    <div class="card comment-header-card">
        <div class="comment-header">
            <h3 class="comment-title">
                <span class="mdi mdi-comment-multiple-outline"></span>
                评论 (<span id="comment-count"><?= $totalComments ?></span>)
            </h3>
            <div class="comment-sort">
                <button class="comment-sort-btn <?= $order === 'desc' ? 'active' : '' ?>" data-order="desc">
                    <span class="mdi mdi-sort-descending"></span>
                    最新
                </button>
                <button class="comment-sort-btn <?= $order === 'asc' ? 'active' : '' ?>" data-order="asc">
                    <span class="mdi mdi-sort-ascending"></span>
                    最早
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($commentTree)): ?>
    <div class="card comment-empty-card">
        <div class="comment-empty">
            <span class="mdi mdi-comment-off-outline"></span>
            <p>暂无评论，快来抢沙发吧！</p>
        </div>
    </div>
    <?php else: ?>
    <div class="comment-list" id="comment-list">
        <?php foreach ($commentTree as $comment): ?>
        <?php renderCommentItem($comment); ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 分页 -->
    <?php if ($totalPages > 1): ?>
    <div class="comment-pagination">
        <?php if ($page > 1): ?>
        <button class="comment-page-btn" data-page="<?= $page - 1 ?>">
            <span class="mdi mdi-chevron-left"></span>
            上一页
        </button>
        <?php endif; ?>

        <span class="comment-page-info">
            第 <?= $page ?> / <?= $totalPages ?> 页
        </span>

        <?php if ($page < $totalPages): ?>
        <button class="comment-page-btn" data-page="<?= $page + 1 ?>">
            下一页
            <span class="mdi mdi-chevron-right"></span>
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
/**
 * 渲染单个评论项（包括子评论）
 * @param array $comment 评论数据
 * @param int $depth 深度
 */
function renderCommentItem($comment, $depth = 0) {
    $avatar = !empty($comment['mail']) ? 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment['mail']))) . '?s=48&d=mp' : 'https://www.gravatar.com/avatar/?s=48&d=mp';
    $authorUrl = !empty($comment['url']) ? $comment['url'] : 'javascript:void(0)';
    $authorTarget = !empty($comment['url']) ? 'target="_blank" rel="noopener noreferrer"' : '';
    $date = date('Y-m-d H:i:s', $comment['created']);
    $hasChildren = !empty($comment['children']);

    echo '<div class="comment-item" data-coid="' . $comment['coid'] . '" data-parent="' . $comment['parent'] . '" data-depth="' . $depth . '">';

    echo '<div class="card comment-card">';
    echo '<div class="comment-main">';
    echo '<div class="comment-avatar">';
    echo '<img src="' . $avatar . '" alt="' . htmlspecialchars($comment['author']) . '">';
    echo '</div>';
    echo '<div class="comment-content">';
    echo '<div class="comment-header">';
    echo '<span class="comment-author">';
    echo '<a href="' . $authorUrl . '" ' . $authorTarget . '>' . htmlspecialchars($comment['author']) . '</a>';
    if ($comment['parent'] > 0) {
        echo '<span class="comment-reply-badge">回复</span>';
    }
    echo '</span>';
    echo '<span class="comment-date">' . $date . '</span>';
    echo '</div>';
    echo '<div class="comment-text markdown-content">' . $comment['text'] . '</div>';
    echo '<div class="comment-actions">';
    echo '<button class="comment-action-btn comment-reply-btn" data-coid="' . $comment['coid'] . '" data-author="' . htmlspecialchars($comment['author']) . '">';
    echo '<span class="mdi mdi-reply"></span>';
    echo '回复';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    // 渲染子评论
    if ($hasChildren) {
        echo '<div class="comment-children">';
        foreach ($comment['children'] as $child) {
            renderCommentItem($child, $depth + 1);
        }
        echo '</div>';
    }

    echo '</div>';
}
?>
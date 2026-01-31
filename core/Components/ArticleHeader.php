<?php
/**
 * Inaline 主题文章头部组件
 * @author Inaline Studio
 *
 * @param array $data 传入的数据，包含：
 *   - title: 文章标题
 *   - date: 发布日期
 *   - author: 作者
 *   - views: 阅读量
 *   - comments: 评论数
 *   - categories: 分类数组
 *   - thumbnail: 缩略图URL
 */
$data = $this->data;
$title = $data['title'] ?? '';
$date = $data['date'] ?? '';
$author = $data['author'] ?? '';
$views = $data['views'] ?? 0;
$comments = $data['comments'] ?? 0;
$categories = $data['categories'] ?? [];
$thumbnail = $data['thumbnail'] ?? '';

// 如果没有缩略图，使用默认图片
if (empty($thumbnail)) {
    $thumbnail = Get::Assets('assets/images/cover/cover1.jpg');
}
?>

<div class="article-header" id="articleHeader">
    <div class="article-header-bg" style="background-image: url('<?= $thumbnail ?>');"></div>
    <div class="article-header-overlay"></div>
    <div class="article-header-content">
        <div class="article-header-inner">
            <!-- 文章分类 -->
            <?php if (!empty($categories)): ?>
            <div class="article-header-categories">
                <?php foreach ($categories as $category): ?>
                <a href="<?= $category['url'] ?>" class="article-header-category">
                    <span class="mdi mdi-folder"></span>
                    <?= $category['name'] ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- 文章标题 -->
            <h1 class="article-header-title"><?= $title ?></h1>

            <!-- 文章信息 -->
            <div class="article-header-info">
                <div class="article-header-info-item">
                    <span class="mdi mdi-account"></span>
                    <span><?= $author ?></span>
                </div>
                <div class="article-header-info-item">
                    <span class="mdi mdi-calendar"></span>
                    <span><?= $date ?></span>
                </div>
                <div class="article-header-info-item">
                    <span class="mdi mdi-eye"></span>
                    <span><?= $views ?></span>
                </div>
                <div class="article-header-info-item">
                    <span class="mdi mdi-comment"></span>
                    <span><?= $comments ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
/**
 * Inaline 主题轮播图小组件
 * @author Inaline Studio
 *
 * @param array $data 传入的数据，包含：
 *   - enabled: 是否启用
 *   - items: 轮播图项目数组
 *   - interval: 自动切换间隔
 */
?>
<?php
$carousel_items = $this->data->items ?? [];
$carousel_interval = $this->data->interval ?? 5;
?>
<div class="carousel" data-interval="<?= e($carousel_interval) ?>">
    <div class="carousel-inner">
        <?php foreach ($carousel_items as $index => $item): ?>
        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
            <a href="<?= e($item['url'] ?? '#') ?>" class="carousel-link">
                <div class="carousel-image" style="background-image: url('<?= e($item['image'] ?? '') ?>')"></div>
                <div class="carousel-caption">
                    <h3 class="carousel-title"><?= e($item['title'] ?? '') ?></h3>
                    <p class="carousel-description"><?= e($item['description'] ?? '') ?></p>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 切换按钮 -->
    <?php if (count($carousel_items) > 1): ?>
    <button class="carousel-control carousel-control-prev" data-direction="prev">
        <span class="mdi mdi-chevron-left"></span>
    </button>
    <button class="carousel-control carousel-control-next" data-direction="next">
        <span class="mdi mdi-chevron-right"></span>
    </button>
    <?php endif; ?>

    <!-- 指示器 -->
    <?php if (count($carousel_items) > 1): ?>
    <div class="carousel-indicators">
        <?php foreach ($carousel_items as $index => $item): ?>
        <button class="carousel-indicator <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>"></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
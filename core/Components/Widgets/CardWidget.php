<?php
/**
 * Inaline 主题卡片小组件
 * @author Inaline Studio
 * 
 * @param string $title 卡片标题
 * @param string $content 卡片内容（可选）
 */
?>
<?php if (isset($this->data->title)): ?>
<div class="card">
    <div class="card-title"><?= e($this->data->title) ?></div>
    <div class="card-content">
        <?= $this->data->content ?? '' ?>
    </div>
</div>
<?php endif; ?>
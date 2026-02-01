<?php
/**
 * Inaline 主题说说页面头部组件
 * @author Inaline Studio
 *
 * @param array $data 传入的数据，包含：
 *   - title: 页面标题（仅用于 SEO）
 *   - cover: 封面图片URL（仅用于 SEO）
 */
$data = $this->data;
$title = $data['title'] ?? '说说';
$cover = $data['cover'] ?? '';

// 如果没有封面，使用默认图片
if (empty($cover)) {
    $cover = Get::Assets('assets/images/cover/cover1.jpg');
}
?>

<!-- 说说页面使用简洁顶部装饰，不显示大图标题 -->
<div class="shuoshuo-header" id="shuoshuoHeader">
    <div class="shuoshuo-header-deco"></div>
</div>
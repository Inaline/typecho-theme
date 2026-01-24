<?php
/**
 * 功能强大、美观、简洁的 Typecho 主题
 *
 * @package Inaline Typecho Theme
 * @author Inaline Studio
 * @version 1.0.0
 * @link https://gitee.com/inaline/typecho-theme
 *
 */

// [DEBUG] 现在页面直接 echo 输出的都是调试信息，不得用于生产环境

$params_head = ComponentData::GetHeader('home');
$params_top_bar = ComponentData::GetTopBar('home');
$params_foot = ComponentData::GetFooter();
$params_carousel = ComponentData::GetCarouselData();

Get::Component($this, 'Header', $params_head);
Get::Component($this, 'TopBar', $params_top_bar);
Get::Component($this, 'Common', ['type' => 'main-start']);
Get::Component($this, 'Common', ['type' => 'wrapper-start']);
Get::Component($this, 'Common', ['type' => 'content-column-start']);

// 未开启轮播图或者没写值就不显示
if ($params_carousel['enabled'] && !empty($params_carousel['items'])) {
    Get::Component($this, 'Widgets/CarouselWidget', $params_carousel);
}

echo '<div class="card">
    <div class="card-title">文章列表</div>
    <div class="card-content">
        这里是文章列表区域
    </div>
</div>';
Get::Component($this, 'Common', ['type' => 'content-column-end']);
Get::Component($this, 'Common', ['type' => 'sidebar-column-start']);
echo '<div class="card">
    <div class="card-title">最新文章</div>
    <div class="card-content">
        这里是最新文章列表
    </div>
</div>
<div class="card">
    <div class="card-title">标签云</div>
    <div class="card-content">
        这里是标签云
    </div>
</div>';
Get::Component($this, 'Common', ['type' => 'sidebar-column-end']);
Get::Component($this, 'Common', ['type' => 'wrapper-end']);
Get::Component($this, 'Common', ['type' => 'main-end']);
Get::Component($this, 'Footer', $params_foot);
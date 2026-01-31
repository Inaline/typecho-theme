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

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 获取 Header 组件数据
$site_name = $this->options->title;
$page_title = '404 - ' . $site_name;
$page_description = '页面未找到';
$params_head = ComponentData::GetHeader('404', $this, $page_title, $page_description, '');
$params_foot = ComponentData::GetFooter('404', $this);

Get::Component($this, 'Header', $params_head);
Get::Component($this, 'TopBar', ComponentData::GetTopBar());

Get::Component($this, 'Common', ['type' => 'main-start']);
Get::Component($this, 'Common', ['type' => 'wrapper-start']);
Get::Component($this, 'Common', ['type' => 'content-column-start']);
?>

<div class="card error-page">
    <div class="error-page-content">
        <div class="error-page-image">
            <img src="<?= Get::Assets('assets/images/xiaowenzi/404.png') ?>" alt="404" />
        </div>
        <div class="error-page-text">
            <h1 class="error-page-title">404</h1>
            <h2 class="error-page-subtitle"><?php _e('页面未找到'); ?></h2>
            <p class="error-page-description">
                <?php _e('您访问的页面不存在或已被删除。'); ?>
            </p>
            <a href="<?php $this->options->siteUrl(); ?>" class="btn btn-primary">
                <span class="mdi mdi-home"></span>
                <?php _e('返回首页'); ?>
            </a>
        </div>
    </div>
</div>

<?php
Get::Component($this, 'Common', ['type' => 'content-column-end']);
Get::Component($this, 'Common', ['type' => 'wrapper-end']);
Get::Component($this, 'Common', ['type' => 'main-end']);
Get::Component($this, 'Footer', $params_foot);
?>
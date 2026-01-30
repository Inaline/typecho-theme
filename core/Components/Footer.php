<?php
/**
 * Inaline 主题页面底部 component
 * @author Inaline Studio
 */
?>
<footer class="footer" data-start-date="<?= e($this->data->start_date ?? '2024-01-01') ?>">
    <div class="footer-content">
        <div class="footer-main">
            <div class="footer-info">
                <?php if (!empty($this->data->run_time)): ?>
                <div class="footer-item">
                    <span class="mdi mdi-clock-outline footer-icon"></span>
                    <span class="footer-text">已运行 <span id="runTime"><?= e($this->data->run_time) ?></span></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($this->data->copyright)): ?>
                <div class="footer-item">
                    <span class="mdi mdi-copyright footer-icon"></span>
                    <span class="footer-text"><?= $this->data->copyright ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($this->data->icp)): ?>
                <div class="footer-item">
                    <span class="mdi mdi-shield-check footer-icon"></span>
                    <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer" class="footer-link"><?= e($this->data->icp) ?></a>
                </div>
                <?php endif; ?>

                <?php if (!empty($this->data->custom_content)): ?>
                <div class="footer-item footer-custom">
                    <?= $this->data->custom_content ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="footer-links">
                <?php if (!empty($this->data->rss_url)): ?>
                <a href="<?= e($this->data->rss_url) ?>" class="footer-link-item" title="RSS订阅">
                    <span class="mdi mdi-rss"></span>
                </a>
                <?php endif; ?>

                <?php if (!empty($this->data->sitemap_url)): ?>
                <a href="<?= e($this->data->sitemap_url) ?>" class="footer-link-item" title="站点地图">
                    <span class="mdi mdi-sitemap"></span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>
<?php
/* 统一输出脚本 */
$scripts = $this->data->scripts ?? [];
foreach ($scripts as $item):
    if (isset($item['src'])):
        echo '<script';
        foreach ($item as $k => $v):
            if ($k !== 'content') echo ' ' . $k . '="' . e($v) . '"';
        endforeach;
        echo '></script>';
    elseif (isset($item['content'])):
        echo '<script>' . $item['content'] . '</script>';
    endif;
endforeach;
?>
<?php if (isset($this->data->typecho_footer)): ?>
    <?= $this->data->typecho_footer ?>
<?php endif; ?>
<?php if (isset($this->data->custom)): ?>
    <?= $this->data->custom; ?>
<?php endif; ?>
</body>
</html>
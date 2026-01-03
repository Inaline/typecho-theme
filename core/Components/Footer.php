<?php
/**
 * Inaline 主题页面底部 component
 * @author Inaline Studio
 */
?>
<footer class="footer">
</footer>
<?php
/* 统一输出脚本 */
$scripts = $this->data->scripts ?? [];
foreach ($scripts as $item):
    if (isset($item['src'])):
        echo '<script';
        foreach ($item as $k => $v) echo ' ' . $k . '="' . e($v) . '"';
        echo '></script>';
    endif;
endforeach;
?>
<?php if (isset($this->data->custom)): ?>
    <?= $this->data->custom; ?>
<?php endif; ?>
</body>
</html>
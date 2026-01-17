<?php
/**
 * Inaline 主题页面头部的 component
 * @author Inaline Studio
 */
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=e($this->data->title, GetSite::title()) ?></title>
    <?php if (isset($this->data->keywords)): ?>
        <meta name="keywords" content="<?=e($this->data->keywords) ?>">
    <?php endif; ?>
    <?php if (isset($this->data->description)): ?>
        <meta name="description" content="<?=e($this->data->description) ?>">
    <?php endif; ?>
    <?php if (isset($this->data->favicon)): ?>
        <link rel="icon" href="<?=e($this->data->favicon) ?>">
    <?php endif; ?>
    <script>
    <?php if (!empty($this->data->font)): ?>
        const fontPath   = "<?= e($this->data->font) ?>";
        const fontFormat = "<?= e($this->data->font_type ?? 'woff2') ?>";
    <?php endif; ?>
    <?php if (isset($this->data->performance) && is_array($this->data->performance)): ?>
    const performance = <?= json_encode($this->data->performance, JSON_UNESCAPED_UNICODE) ?>;
    <?php endif; ?>
</script>
    <?php if (!empty($this->data->links) && is_array($this->data->links)): ?>
        <?php foreach ($this->data->links as $link): ?>
            <?php if (isset($link['href'])): ?>
                <link<?php foreach ($link as $k => $v) echo ' ' . $k . '="' . e($v) . '"' ?>>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php
    $scripts = $this->data->scripts ?? [];
    ?>
    <?php foreach ($scripts as $item): ?>
        <?php if (isset($item['src'])): ?>
            <script<?php foreach ($item as $k => $v) echo ' ' . $k . '="' . e($v) . '"' ?>></script>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (isset($this->data->copyright)): ?>
        <meta name="copyright" content="<?=e($this->data->copyright) ?>">
    <?php endif; ?>
    <?php if (isset($this->data->author)): ?>
        <meta name="author" content="<?=e($this->data->author) ?>">
    <?php endif; ?>
    <?php if (isset($this->data->custom)): ?>
        <?= $this->data->custom; ?>
    <?php endif; ?>
</head>
<body id="<?=e($this->data->body_id, 'achieve') ?>">
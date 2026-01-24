<?php
/**
 * Inaline 主题通用小组件
 * @author Inaline Studio
 *
 * @param string $type 返回内容类型
 *   - 'main-start': 返回 main 标签开始
 *   - 'main-end': 返回 main 标签结束
 *   - 'wrapper-start': 返回 content-wrapper 标签开始
 *   - 'wrapper-end': 返回 content-wrapper 标签结束
 *   - 'content-column-start': 返回左侧内容栏开始
 *   - 'content-column-end': 返回左侧内容栏结束
 *   - 'sidebar-column-start': 返回右侧侧边栏开始
 *   - 'sidebar-column-end': 返回右侧侧边栏结束
 */
?>
<?php
$type = $this->data->type ?? '';
switch ($type) {
    case 'main-start':
        echo '<main class="main-container">';
        break;
    case 'main-end':
        echo '</main>';
        break;
    case 'wrapper-start':
        echo '<div class="content-wrapper">';
        break;
    case 'wrapper-end':
        echo '</div>';
        break;
    case 'content-column-start':
        echo '<div class="content-column">';
        break;
    case 'content-column-end':
        echo '</div>';
        break;
    case 'sidebar-column-start':
        echo '<div class="sidebar-column">';
        break;
    case 'sidebar-column-end':
        echo '</div>';
        break;
}
?>
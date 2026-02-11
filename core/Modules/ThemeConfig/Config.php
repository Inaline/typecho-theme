<?php
/**
 * Inaline 主题的主题设置主要文件
 * @author Inaline Studio
 */

require_once __DIR__ . '/ConfigBuilder.php';

function themeConfig($form)
{
    $builder = new ConfigBuilder;
    $fields  = require __DIR__ . '/ConfigFields.php';
    $builder->setFields($fields);
    $builder->generateHtml();

    /* 把渲染好的隐藏域塞进 Typecho 表单 */
    $hid = new Typecho_Widget_Helper_Form_Element_Hidden('data');
    $hid->value($builder->getJson());   // 最终 JSON
    $form->addInput($hid);

    /*
     * 把真正给用户看的界面 echo 出来
     * 这里有个很玄学的问题，这个外部链接无论放到 ConfigBuidler那个时候，都会被作为字符串输出到最前面。直接使用点语法拼接的echo语句也一样，还就只能这样写
     */
    echo '<link rel="stylesheet" href="';
    echo Helper::options()->themeUrl('assets/css/admin.css');
    echo'"/>';
    
    echo $builder->getHtml();
    
    // 添加导出和导入按钮
    echo '<div class="config-actions" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">';
    echo '<button type="button" id="btn-export-config" class="btn primary" style="margin-right: 10px;">📤 导出配置</button> ';
    echo '<button type="button" id="btn-import-config" class="btn success">📥 导入配置</button>';
    echo '</div>';
    
    echo '<script src="';
    echo Helper::options()->themeUrl('assets/js/admin.js');
    echo '"></script>';
}
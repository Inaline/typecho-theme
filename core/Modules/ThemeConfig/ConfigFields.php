<?php
/**
 * Inaline 主题的主题设置项定义
 * @author Inaline Studio
 */
return [
    [
        'title'   => '欢迎使用',
        'content' => '<h1>Inaline - 功能强大、美观、简洁的 Typecho 主题</h1>'
    ],
    [
        'title'  => '常用组件示例',
        'fields' => [
            [
                'name'        => 'demo_text',
                'label'       => '文本输入框',
                'type'        => 'text',
                'default'     => 'Hello Typecho',
                'description' => '普通单行文本'
            ],
            [
                'name'        => 'demo_textarea',
                'label'       => '多行文本',
                'type'        => 'textarea',
                'default'     => "第一行\n第二行",
                'description' => '支持换行'
            ],
            [
                'name'    => 'demo_radio',
                'label'   => '单选',
                'type'    => 'radio',
                'default' => 'option2',
                'options' => ['option1' => '选项一', 'option2' => '选项二']
            ],
            [
                'name'    => 'demo_checkbox',
                'label'   => '多选',
                'type'    => 'checkbox',
                'default' => true
            ],
            [
                'name'    => 'demo_select',
                'label'   => '下拉选择',
                'type'    => 'select',
                'default' => 'apple',
                'options' => ['apple' => '苹果', 'banana' => '香蕉', 'orange' => '橙子']
            ]
        ]
    ],
    [
        'title' => '网站基本设置',
        'fields' => [
            [
                'name' => 'logo',
                'label' => 'Logo',
                'type' => 'text',
                'default' => '@assets/images/logo/Inaline.png',
                'description' => '网站的 logo, 显示在网站的各个地方'
            ],
            [
                'name' => 'favicon',
                'label' => 'Favicon',
                'type' => 'text',
                'default' => '@assets/images/logo/favicon.ico',
                'description' => '网站的 Favicon, 显示在浏览器标签页左边'
            ],
            [
                'name' => 'custom_head',
                'label' => '自定义头部',
                'type' => 'textarea',
                'default' => '',
                'description' => '网站的自定义头部信息, 可以直接写HTML, 会输出到<head>部分'
            ]
        ]
    ]
];

/*
约定:
  - 关于 URI 我们约定
    1. 以 http/https 开头为外部 URI 如 https://example.com/a.png
    2. 以 / 开头为本地绝对路径 如 /index.php
    3. 以 @ 开头为相对于主题的路径 如 @assets/images/Inaline.png
    4. 以 data: 开头为 dataUrl
*/
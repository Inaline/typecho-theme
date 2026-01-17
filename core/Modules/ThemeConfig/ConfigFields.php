<?php
/**
 * Inaline 主题的主题设置项定义
 * @author Inaline Studio
 */
return [
    [
        'title'   => '欢迎使用',
        'content' => '<h1>Inaline - 功能强大、美观、简洁的 Typecho 主题</h1>
        <a href="https://gitee.com/inaline/typecho-theme">Gitee</a> <a href="https://github.com/inaline">Github</a> <a href="https://m.bilibili.com/space/3493111149890117">Bilibili</a>
        <p>关于URI的约定: <br> 1. 以 http/https 开头为外部 URI 如 https://example.com/a.png <br> 2. 以 / 开头为本地绝对路径 如 /index.php <br> 3. 以 @ 开头为相对于主题的路径 如 @assets/images/Inaline.png <br> 4. 以 data: 开头为 dataUrl</p>'
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
            ],
            [
                'name' => 'custom_foot',
                'label' => '自定义尾部',
                'type' => 'textarea',
                'default' => '',
                'description' => '网站的自定义头部信息, 可以直接写HTML, 会输出到<body>部分尾部'
            ],
            [
                'name' => 'font',
                'label' => '全局字体',
                'type' => 'text',
                'default' => '@assets/fonts/HYTangMeiRen55W.woff2',
                'description' => '网站的全局字体, 留空默认使用 汉仪唐美人, 加载失败回退 微软雅黑'
            ],
            [
                'name'    => 'font_type',
                'label'   => '全局字体类型',
                'type'    => 'radio',
                'default' => 'woff2',
                'options' => ['woff2' => 'woff2', 'ttf' => 'ttf', 'otf' => 'otf']
            ],
        ]
    ],
    [
        'title' => '标题栏设置',
        'fields' => [
            [
                'name'    => 'top_bar_mode',
                'label'   => '标题栏模式',
                'type'    => 'radio',
                'default' => 'transparent',
                'options' => ['transparent' => '跟随背景透明', 'display' => '保持不透明']
            ],
            [
                'name'    => 'top_bar_pages',
                'label'   => '标题栏显示的页面(JSON)',
                'type'    => 'textarea',
                'default' => '[{"name":"home","label":"首页","icon":"mdi-home","url":"/"},{"name":"more","label":"更多","icon":"mdi-more","children":[{"name":"test","label":"测试","url":"/test.html"}]}]',
                'description' => '显示在页面顶部标题栏的标签, JSON格式<br>事例:[{"name":"home","label":"首页","icon":"mdi-home","url":"/"},{"name":"more","label":"更多","icon":"mdi-more","children":[{"name":"test","label":"测试","url":"/test.html"}]}]'
            ]
        ]
    ],
    [
        'title' => '侧边栏用户信息',
        'fields' => [
            [
                'name'        => 'sidebar_user_status',
                'label'       => '用户状态',
                'type'        => 'text',
                'default'     => 'EMOing',
                'description' => '显示在头像上方的状态文字'
            ],
            [
                'name'        => 'sidebar_user_avatar',
                'label'       => '用户头像',
                'type'        => 'text',
                'default'     => 'http://q1.qlogo.cn/g?b=qq&nk=2291374026&s=640',
                'description' => '用户头像的 URI，支持外部链接、本地路径、相对路径'
            ],
            [
                'name'        => 'sidebar_user_name',
                'label'       => '用户名',
                'type'        => 'text',
                'default'     => 'Inaline',
                'description' => '显示在头像下方的用户名'
            ],
            [
                'name'        => 'sidebar_user_bio',
                'label'       => '用户简介',
                'type'        => 'textarea',
                'default'     => '昔人已乘黄鹤去，此地空余黄鹤楼',
                'description' => '显示在用户名下方的简介文字'
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
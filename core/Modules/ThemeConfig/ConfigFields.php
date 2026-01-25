<?php
/**
 * Inaline 主题的主题设置项定义
 * @author Inaline Studio
 */
return [
    [
        'title'   => '欢迎使用',
        'content' => '<h1>Inaline - 功能强大、美观、简洁的 Typecho 主题</h1>
        <a href="https://gitee.com/inaline/typecho-theme">Gitee</a> <a href="https://github.com/inaline">Github</a> <a href="https://bilibili.com/space/3493111149890117">Bilibili</a>
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
                'name' => 'logo_dark',
                'label' => '深色模式 Logo',
                'type' => 'text',
                'default' => '@assets/images/logo/Inaline-dark.png',
                'description' => '深色模式下显示的 logo, 留空则使用普通 Logo'
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
            ],
            [
                'name'        => 'sidebar_user_qq',
                'label'       => 'QQ',
                'type'        => 'text',
                'default'     => '',
                'description' => 'QQ号或QQ主页链接，留空则不显示'
            ],
            [
                'name'        => 'sidebar_user_email',
                'label'       => '邮箱',
                'type'        => 'text',
                'default'     => '',
                'description' => '邮箱地址，留空则不显示'
            ],
            [
                'name'        => 'sidebar_user_bilibili',
                'label'       => 'Bilibili',
                'type'        => 'text',
                'default'     => '',
                'description' => 'Bilibili UID，留空则不显示'
            ]
        ]
    ],
    [
        'title' => '侧边栏卡片设置',
        'fields' => [
            [
                'name'    => 'sidebar_widget_user',
                'label'   => '用户信息卡片',
                'type'    => 'checkbox',
                'default' => true,
                'description' => '显示用户信息卡片（状态、头像、用户名、简介）'
            ],
            [
                'name'    => 'sidebar_widget_hot_articles',
                'label'   => '热门文章',
                'type'    => 'checkbox',
                'default' => true,
                'description' => '显示热门文章卡片'
            ],
                        [
                            'name'    => 'sidebar_widget_hot_articles_count',
                            'label'   => '热门文章数量',
                            'type'    => 'text',
                            'default' => '5',
                            'description' => '热门文章显示数量'
                        ],
                        [
                            'name'    => 'sidebar_widget_hot_articles_sort',
                            'label'   => '热门文章排序',
                            'type'    => 'radio',
                            'default' => 'views',
                            'options' => ['views' => '阅读量', 'comments' => '评论数', 'likes' => '点赞数']
                        ],
                        [
                            'name'    => 'sidebar_widget_recent_comments',
                            'label'   => '最新评论',
                            'type'    => 'checkbox',
                            'default' => true,
                            'description' => '显示最新评论卡片'
                        ],
                        [
                            'name'    => 'sidebar_widget_recent_comments_count',
                            'label'   => '最新评论数量',
                            'type'    => 'text',
                            'default' => '5',
                            'description' => '最新评论显示数量'
                        ]
                    ]
                ],
                [
                    'title' => '页脚设置',
        'fields' => [
            [
                'name'        => 'footer_start_date',
                'label'       => '建站日期',
                'type'        => 'text',
                'default'     => '2024-01-01',
                'description' => '网站建站日期，格式：YYYY-MM-DD'
            ],
            [
                'name'        => 'footer_copyright',
                'label'       => '版权信息',
                'type'        => 'text',
                'default'     => '© {year} Inaline. All rights reserved.',
                'description' => '版权信息，支持HTML和{year}占位符，留空则不显示'
            ],
            [
                'name'        => 'footer_icp',
                'label'       => '备案信息',
                'type'        => 'text',
                'default'     => '',
                'description' => 'ICP备案号，支持{year}占位符，留空则不显示'
            ],
            [
                'name'        => 'footer_custom',
                'label'       => '自定义内容',
                'type'        => 'textarea',
                'default'     => '',
                'description' => '页脚自定义内容，支持HTML'
            ]
        ]
    ],
    [
        'title' => '轮播图设置',
        'fields' => [
            [
                'name'        => 'carousel_enabled',
                'label'       => '启用轮播图',
                'type'        => 'checkbox',
                'default'     => true,
                'description' => '是否在首页显示轮播图'
            ],
            [
                'name'        => 'carousel_items',
                'label'       => '轮播图内容(JSON)',
                'type'        => 'textarea',
                'default'     => '[{"image":"@assets/images/logo/Inaline.png","title":"欢迎使用 Inaline","description":"功能强大、美观、简洁的 Typecho 主题","url":"/"},{"image":"@assets/images/logo/wenzi.png","title":"Inaline Studio","description":"专注于 Typecho 主题开发","url":"/"}]',
                'description' => '轮播图内容，JSON格式<br>示例:[{"image":"背景图URI","title":"标题","description":"简介","url":"跳转URI"}]'
            ],
            [
                'name'        => 'carousel_interval',
                'label'       => '自动切换间隔(秒)',
                'type'        => 'text',
                'default'     => '5',
                'description' => '轮播图自动切换的时间间隔，单位为秒，设为0则不自动切换'
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
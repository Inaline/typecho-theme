<?php

/**
 * Inaline Typecho 主题配置文件
 * 此文件可以在 Typecho "切换主题" 下直接修改，与主题设置不同，这里主要存储功能性设置，而非内容性。
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

return [
    // 主题设置
    'app' => [
        'debug' => true,            // 调试模式
        'compress_html' => true,   // HTML 压缩
    ],
    
    // 模块设置
    'plugins' => [
        'restfulapi' => [          // RESTful API 设置
            'enabled' => true,     // 是否开启 RESTful API
            'route' => 'api',      // RESTful API 路由配置
            'token' => [
                'enabled' => true,            // 是否启用Token
                'value' => 'your_token_here', // Token值
            ],
            'headers' => [
                'access_control_allow_origin' => '*', // 跨域配置
            ]
        ],
        'stiemap' => [          // 站点地图设置
            'enabled' => true,  // 是否开启站点地图功能
            'modes' => [        // 展示方法
                'txt' => true,  // TXT 纯文本
                'xml' => true,  // XML Sitemap
                'rss' => true   // RSS
            ]
        ],
        'backup' => [                     // 备份功能开关
            'enabled' => true,            // 是否开启
            'entrance' => 'backup',       // 安全入口路由
            'token' => 'your_token_here', // 备份功能的 token
            'items' => [
                'database' => true,       // 备份数据库
                'config_file' => true,    // 备份配置文件
                'uploads' => true         // 备份上传的文件
            ]
        ]
    ]
];
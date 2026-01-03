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

$params_head = [
    'title' => GetSite::title(),
    'keywords' => GetSite::keywords(),
    'description' => GetSite::description(),
    'favicon' => Get::resolveUri(Get::themeOption('favicon')),
    'copyright' => GetSite::authorName(),
    'author' => GetSite::authorName(),
    'links' => [
        [
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => Get::Assets('assets/css/style.css')
        ]
    ],
    'scripts' => [
        [
            'type' => 'text/javascript',
            'src' => GetSite::adminPath() . 'js/jquery.js',
        ]
    ],
    'custom' => Get::themeOption('custom_head'),
    'body_id' => 'home'
];

$params_top_bar = [];
$params_foot = [
    'scripts' => [
        [
            'type' => 'text/javascript',
            'src' => GetSite::adminPath() . 'js/jquery.js',
        ]
    ],
    'custom' => '<script> /* 自定义的 Footer 内容 */ </script>'
];

Get::Component($this, 'Header', $params_head);
Get::Component($this, 'TopBar', $params_top_bar);
Get::Component($this, 'Footer', $params_foot);
<?php
/**
 * Inaline Typecho 主题组件数据获取类
 * 统一管理各组件的数据获取逻辑
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class ComponentData
{
    /* ==========================
     * Header 组件数据
     * ========================== */

    /**
     * 获取 Header 组件数据
     * @param string $body_id 页面 body_id
     * @return array
     */
    public static function GetHeader($body_id = 'home')
    {
        // 获取性能统计数据
        $performanceStats = Inaline::getPerformanceStats();
        $isDebug = defined('__TYPECHO_DEBUG__') && __TYPECHO_DEBUG__;
        
        // 非调试模式下只显示非敏感信息
        $performanceData = [
            'execution_time' => $performanceStats['execution_time'],
            'memory_used' => $performanceStats['memory_used']
        ];
        
        // 调试模式下显示完整信息
        if ($isDebug) {
            $performanceData['memory_peak'] = $performanceStats['memory_peak'];
            $performanceData['memory_current'] = $performanceStats['memory_current'];
            $performanceData['debug_mode'] = true;
        } else {
            $performanceData['debug_mode'] = false;
        }
        
        return [
            'title'       => GetSite::title(),
            'keywords'    => GetSite::keywords(),
            'description' => GetSite::description(),
            'favicon'     => Get::resolveUri(Get::themeOption('favicon')),
            'copyright'   => GetSite::authorName(),
            'author'      => GetSite::authorName(),
            'links'       => [
                [
                    'rel'  => 'stylesheet',
                    'href' => 'https://cdn.bootcdn.net/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css'
                ],
                [
                    'rel'  => 'stylesheet',
                    'type' => 'text/css',
                    'href' => Get::Assets('assets/css/style.css')
                ]
            ],
            'scripts'     => [],
            'custom'      => Get::themeOption('custom_head'),
            'body_id'     => $body_id,
            'font'        => Get::resolveUri(Get::themeOption('font')),
            'font_type'   => Get::themeOption('font_type'),
            'performance' => $performanceData
        ];
    }

    /* ==========================
     * TopBar 组件数据
     * ========================== */

    /**
     * 获取 TopBar 组件数据
     * @param string $current_page 当前页面名称
     * @return array
     */
    public static function GetTopBar($current_page = 'home')
    {
        return [
            'logo' => Get::resolveUri(Get::themeOption('logo')),
            'logo_dark' => Get::resolveUri(Get::themeOption('logo_dark')),
            'pages' => Get::themeOption('top_bar_pages', '[{"name":"home","label":"首页","icon":"mdi-home","url":"/"}]'),
            'categories' => GetCategory::buildNavJson(),
            'current_page' => $current_page,
            'sidebar_user_status' => Get::themeOption('sidebar_user_status', 'EMOing'),
            'sidebar_user_avatar' => Get::themeOption('sidebar_user_avatar', 'http://q1.qlogo.cn/g?b=qq&nk=2291374026&s=640'),
            'sidebar_user_name' => Get::themeOption('sidebar_user_name', 'Inaline'),
            'sidebar_user_bio' => Get::themeOption('sidebar_user_bio', '昔人已乘黄鹤去，此地空余黄鹤楼'),
            'article_count' => GetArticle::total(),
            'comment_count' => GetComment::total()
        ];
    }

    /* ==========================
     * Footer 组件数据
     * ========================== */

    /**
     * 获取 Footer 组件数据
     * @return array
     */
    public static function GetFooter()
    {
        return [
            'scripts' => [
                [
                    'type' => 'text/javascript',
                    'src' => GetSite::adminPath() . 'js/jquery.js',
                ],
                [
                    'type' => 'text/javascript',
                    'src' => Get::Assets('assets/js/index.js')
                ]
            ],
            'custom' => Get::themeOption('custom_foot')
        ];
    }
}
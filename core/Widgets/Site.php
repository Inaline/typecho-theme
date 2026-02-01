<?php
/**
 * Inaline Typecho 主题 GetSite 方法类
 * 提供站点相关获取
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class GetSite
{
    /* ==========================
     * 基础信息
     * ========================== */

    /** @return string 站点标题 */
    public static function title()
    {
        return Helper::options()->title;
    }

    /** @return string 站点副标题 */
    public static function subtitle()
    {
        return Helper::options()->subtitle;
    }

    /** @return string 站点描述 */
    public static function description()
    {
        return Helper::options()->description;
    }

    /** @return string 站点关键字 */
    public static function keywords()
    {
        return Helper::options()->keywords;
    }

    /** @return string 站点首页地址（带协议） */
    public static function siteUrl()
    {
        return Helper::options()->siteUrl;
    }

    /* ==========================
     * 后台管理文件夹
     * ========================== */
    
    /**
     * 后台入口目录名（URL 里的那段，不含前后斜杠）
     * 如果用户把 admin 改名为 xxx，也能自动识别
     * @return string
     */
    public static function adminDirName()
    {
        /* Typecho 后台常量 __TYPECHO_ADMIN__ 在后台入口文件 index.php 里被定义，
           其值就是入口目录名，例如 admin 或 xxx */
        return defined('__TYPECHO_ADMIN__') ? __TYPECHO_ADMIN__ : 'admin';
    }
    
    /**
     * 后台目录在磁盘上的绝对路径（末尾带 /）
     * 注意：前台调用时 __TYPECHO_ADMIN__ 可能未定义，此时只能退而求其次拼主题路径
     * @return string
     */
    public static function adminPath()
    {
        if (defined('__TYPECHO_ADMIN_DIR__')) {
            // Typecho 1.2+ 官方常量，直接就是磁盘路径
            return rtrim(__TYPECHO_ADMIN_DIR__, '/') . '/';
        }
    
        /* 兼容旧版本：先拿根路径，再拼目录名 */
        $root = defined('__TYPECHO_ROOT_DIR__') ? __TYPECHO_ROOT_DIR__ : dirname(__DIR__, 2);
        return $root . '/' . self::adminDirName() . '/';
    }

    /* ==========================
     * 作者 / 管理员
     * ========================== */

    /** @return Widget_User 第一条管理员记录 */
    public static function admin()
    {
        static $admin = null;
        if ($admin === null) {
            $admin = \Widget\Users\Author::alloc(['uid' => 1]);
        }
        return $admin;
    }

    /** @return string 管理员昵称 */
    public static function authorName()
    {
        return self::admin()->screenName;
    }

    /** @return string 管理员邮箱 */
    public static function authorMail()
    {
        return self::admin()->mail;
    }

    /** @return string 管理员头像（Gravatar 64px） */
    public static function authorAvatar($size = 64)
    {
        return self::gravatar(self::authorMail(), $size);
    }

    /* ==========================
     * 实用工具
     * ========================== */

    /**

     * 快速输出 WeAvatar

     * @param string $mail 邮箱

     * @param int $size 尺寸

     * @param string $d 默认头像

     * @return string

     */

    public static function gravatar($mail, $size = 64, $d = 'mp')

    {

        $hash = hash('sha256', trim($mail));

        return "https://weavatar.com/avatar/{$hash}?s={$size}&d={$d}";

    }    /**
     * 获取主题目录下的静态资源完整 URL
     * @param string $path 例如 css/style.css
     * @return string
     */
    public static function themeUrl($path = '')
    {
        $theme = Helper::options()->theme;
        return Helper::options()->themeUrl . '/' . ltrim($path, '/');
    }

    /* ==========================
     * 其它快捷封装
     * ========================== */

    /**
     * 后台「阅读设置」中设定的「每页文章数目」
     * @return int
     */
    public static function postsPerPage()
    {
        return Helper::options()->postsListSize;
    }

    /**
     * 是否开启了「评论」
     * @return bool
     */
    public static function isCommentEnabled()
    {
        return Helper::options()->commentsPageDisplay != 'hide';
    }

    /**
     * 是否开启了「RSS」
     * @return bool
     */
    public static function isRssEnabled()
    {
        return Helper::options()->feedUrl !== false;
    }

    /**
     * 获取 RSS 地址
     * @return string
     */
    public static function rssUrl()
    {
        return Helper::options()->feedUrl ?: Helper::options()->siteUrl . 'feed/';
    }

    /* ==========================
     * Header 组件数据
     * ========================== */

    /**
     * 获取 Header 组件数据
     * @param string $body_id 页面 body_id
     * @param object $archive 当前 Archive 对象（可选）
     * @param string $custom_title 自定义标题（可选）
     * @param string $custom_description 自定义描述（可选）
     * @param string $custom_keywords 自定义关键词（可选）
     * @return array
     */
    public static function getHeaderData($body_id = 'home', $archive = null, $custom_title = '', $custom_description = '', $custom_keywords = '')
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
        
        // 构建样式表链接
        $links = [
            [
                'rel'  => 'stylesheet',
                'href' => 'https://cdn.bootcdn.net/ajax/libs/MaterialDesign-Webfont/7.4.47/css/materialdesignicons.min.css'
            ],
            [
                'rel'  => 'stylesheet',
                'type' => 'text/css',
                'href' => Get::Assets('assets/css/style.css')
            ]
        ];

        // 仅在文章页面和links页面引入 markdown.css 和 highlight.js
        if ($body_id === 'post' || $body_id === 'links') {
            $links[] = [
                'rel'  => 'stylesheet',
                'type' => 'text/css',
                'href' => Get::Assets('assets/css/markdown.css')
            ];
            $links[] = [
                'rel'  => 'stylesheet',
                'href' => 'https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/styles/monokai-sublime.min.css'
            ];
        }

        // 获取标题、描述和关键词
        if (!empty($custom_title)) {
            $title = $custom_title;
        } else {
            $title = self::title();
        }
        
        if (!empty($custom_description)) {
            $description = $custom_description;
        } else {
            $description = self::description();
        }
        
        if (!empty($custom_keywords)) {
            $keywords = $custom_keywords;
        } else {
            $keywords = self::keywords();
        }

        if ($body_id === 'post' && $archive && isset($archive->title)) {
            // 文章页面使用"文章名 - 站名"格式
            $siteName = self::title();
            $title = $archive->title . ' - ' . $siteName;

            // 尝试获取文章的 SEO 信息
            // 直接从数据库查询文章的自定义字段
            if (isset($archive->cid) && $archive->cid) {
                try {
                    $db = \Typecho\Db::get();
                    $fields = $db->fetchAll($db->select('name', 'str_value')
                        ->from('table.fields')
                        ->where('cid = ?', $archive->cid)
                        ->where('name IN ?', ['seo_keywords', 'seo_description']));

                    foreach ($fields as $field) {
                        if ($field['name'] === 'seo_keywords' && !empty($field['str_value'])) {
                            $keywords = $field['str_value'];
                        }
                        if ($field['name'] === 'seo_description' && !empty($field['str_value'])) {
                            $description = $field['str_value'];
                        }
                    }
                } catch (Exception $e) {
                    // 忽略错误
                }
            }
        } elseif ($body_id === 'links' && $archive && isset($archive->title)) {
            // links 页面使用"页面名 - 站名"格式，如果没有自定义标题
            if (empty($custom_title)) {
                $siteName = self::title();
                $title = $archive->title . ' - ' . $siteName;
            }

            // 尝试获取页面的 SEO 信息
            if (isset($archive->cid) && $archive->cid) {
                try {
                    $db = \Typecho\Db::get();
                    $fields = $db->fetchAll($db->select('name', 'str_value')
                        ->from('table.fields')
                        ->where('cid = ?', $archive->cid)
                        ->where('name IN ?', ['seo_keywords', 'seo_description']));

                    foreach ($fields as $field) {
                        if ($field['name'] === 'seo_keywords' && !empty($field['str_value'])) {
                            $keywords = $field['str_value'];
                        }
                        if ($field['name'] === 'seo_description' && !empty($field['str_value'])) {
                            $description = $field['str_value'];
                        }
                    }
                } catch (Exception $e) {
                    // 忽略错误
                }
            }
        }

        // 获取 Typecho 头部输出（包含评论系统所需的 JavaScript）
        // 注意：在文章页面，如果存在自定义的 description 或 keywords，需要移除 Typecho 输出的默认值并添加自定义值
        $typechoHeader = '';
        if ($archive) {
            ob_start();
            $archive->header();
            $typechoHeader = ob_get_clean();
            
            // 检查是否是文章页面或 links 页面且有自定义 SEO 信息
            $hasCustomDescription = (($body_id === 'post' || $body_id === 'links') && isset($archive->cid) && $archive->cid && !empty($description) && $description !== self::description());
            $hasCustomKeywords = (($body_id === 'post' || $body_id === 'links') && isset($archive->cid) && $archive->cid && !empty($keywords) && $keywords !== self::keywords());
            
            if ($hasCustomDescription || $hasCustomKeywords) {
                // 移除 Typecho 输出的默认 description 和 keywords 元标签
                $typechoHeader = preg_replace('/<meta\s+name=["\']description["\'][^>]*\/?>/i', '', $typechoHeader);
                $typechoHeader = preg_replace('/<meta\s+name=["\']keywords["\'][^>]*\/?>/i', '', $typechoHeader);
                
                // 在最前面添加自定义的 SEO meta 标签
                $customSeoTags = '';
                if ($hasCustomDescription) {
                    $customSeoTags .= '<meta name="description" content="' . htmlspecialchars($description) . '" />' . "\n";
                }
                if ($hasCustomKeywords) {
                    $customSeoTags .= '<meta name="keywords" content="' . htmlspecialchars($keywords) . '" />' . "\n";
                }
                
                // 将自定义 SEO 标签添加到最前面
                $typechoHeader = $customSeoTags . $typechoHeader;
            }
        }

        return [
            'title'       => $title,
            'keywords'    => $keywords,
            'description' => $description,
            'favicon'     => Get::resolveUri(Get::themeOption('favicon')),
            'copyright'   => self::authorName(),
            'author'      => self::authorName(),
            'links'       => $links,
            'scripts'     => [],
            'custom'      => Get::themeOption('custom_head'),
            'body_id'     => $body_id,
            'font'        => Get::resolveUri(Get::themeOption('font')),
            'font_type'   => Get::themeOption('font_type'),
            'performance' => $performanceData,
            'typecho_header' => $typechoHeader
        ];
    }

    /* ==========================
     * Footer 组件数据
     * ========================== */

    /**
     * 获取 Footer 组件数据
     * @param string $body_id 页面 body_id
     * @param object $archive 当前 Archive 对象（可选）
     * @return array
     */
    public static function getFooterData($body_id = 'home', $archive = null)
    {
        // 计算网站运行时间（精确到秒）
        $start_date = Get::themeOption('footer_start_date', '2024-01-01');
        $start_timestamp = strtotime($start_date);
        $current_timestamp = time();
        $diff_seconds = $current_timestamp - $start_timestamp;

        $days = floor($diff_seconds / 86400);
        $hours = floor(($diff_seconds % 86400) / 3600);
        $minutes = floor(($diff_seconds % 3600) / 60);
        $seconds = $diff_seconds % 60;

        $run_time_parts = [];
        if ($days > 0) $run_time_parts[] = $days . '天';
        if ($hours > 0) $run_time_parts[] = $hours . '小时';
        if ($minutes > 0) $run_time_parts[] = $minutes . '分';
        $run_time_parts[] = $seconds . '秒';
        $run_time = implode('', $run_time_parts);

        // 处理{year}占位符
        $start_year = date('Y', $start_timestamp);
        $current_year = date('Y', $current_timestamp);
        if ($start_year == $current_year) {
            $year_text = $current_year;
        } else {
            $year_text = $start_year . '-' . $current_year;
        }

        // 替换版权信息和备案信息中的{year}占位符
        $copyright = Get::themeOption('footer_copyright', '');
        $icp = Get::themeOption('footer_icp', '');

        if (!empty($copyright)) {
            $copyright = str_replace('{year}', $year_text, $copyright);
        }

        if (!empty($icp)) {
            $icp = str_replace('{year}', $year_text, $icp);
        }

        // 获取 RSS 和 Sitemap 链接
        $rss_url = self::rssUrl();
        $sitemap_url = Get::Assets('library/sitemap.php');

        // 构建脚本列表
        $scripts = [
            [
                'type' => 'text/javascript',
                'src' => Helper::options()->adminUrl . 'js/jquery.js',
            ],
            [
                'type' => 'text/javascript',
                'src' => Get::Assets('assets/js/index.js')
            ]
        ];

        // 仅在文章页面和links页面引入 highlight.js 和 article.js
        if ($body_id === 'post' || $body_id === 'links') {
            $scripts[] = [
                'type' => 'text/javascript',
                'src' => 'https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/highlight.min.js'
            ];
            $scripts[] = [
                'type' => 'text/javascript',
                'src' => Get::Assets('assets/js/article.js')
            ];
        }

        // 获取 Typecho 底部输出
        $typechoFooter = '';
        if ($archive) {
            ob_start();
            $archive->footer();
            $typechoFooter = ob_get_clean();
        }

        return [
            'scripts' => $scripts,
            'custom' => Get::themeOption('custom_foot'),
            'run_time' => $run_time,
            'start_date' => $start_date,
            'copyright' => $copyright,
            'icp' => $icp,
            'custom_content' => Get::themeOption('footer_custom', ''),
            'rss_url' => $rss_url,
            'sitemap_url' => $sitemap_url,
            'typecho_footer' => $typechoFooter
        ];
    }
}
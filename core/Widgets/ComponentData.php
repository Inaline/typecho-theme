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

    /* ==========================
     * Carousel 组件数据
     * ========================== */

    /**
     * 获取轮播图组件数据
     * @return array
     */
    public static function GetCarouselData()
    {
        $carousel_enabled = Get::themeOption('carousel_enabled', true);
        $carousel_items = Get::themeOption('carousel_items');
        $carousel_interval = Get::themeOption('carousel_interval', '5');

        // 解析 JSON 数据
        $items = [];
        if (!empty($carousel_items)) {
            $items = json_decode($carousel_items, true);
            if (!is_array($items)) {
                $items = [];
            }

            // 解析图片 URI
            foreach ($items as &$item) {
                if (isset($item['image'])) {
                    $item['image'] = Get::resolveUri($item['image']);
                }
            }
        }

        return [
            'enabled' => $carousel_enabled,
            'items' => $items,
            'interval' => $carousel_interval
        ];
    }

    /* ==========================
     * ArticleList 组件数据
     * ========================== */

    /**
     * 获取文章列表组件数据
     * @return array
     */
    public static function GetArticleListData()
    {
        $sort = Get::queryParam('sort', 'date');
        $layout = Get::queryParam('layout', 'list');
        $currentPage = max(1, intval(Get::queryParam('p', '1')));
        $perPage = 10;

        // 排序映射
        $orderMap = [
            'date' => 'created',
            'views' => 'views',
            'comments' => 'commentsNum',
            'likes' => 'likes'
        ];

        $order = isset($orderMap[$sort]) ? $orderMap[$sort] : 'created';

        // 获取文章总数
        $total = GetArticle::total();
        $totalPages = ceil($total / $perPage);

        // 计算偏移量
        $offset = ($currentPage - 1) * $perPage;

        // 获取文章列表
        $articles = GetArticle::all(
            ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order', 'url'],
            $order,
            'desc',
            $perPage,
            $offset
        );

        // 格式化文章数据
        $formattedArticles = [];
        foreach ($articles as $article) {
            // 获取自定义字段
            $thumbnail = '';
            $excerpt = '';
            $articleViews = 0;
            $articleLikes = 0;

            // 尝试从自定义字段获取数据
            try {
                $db = Typecho_Db::get();
                $row = $db->fetchRow($db->select('fields')->from('table.contents')->where('cid = ?', $article['cid']));
                if ($row) {
                    $fields = unserialize($row['fields']);
                    if (is_array($fields)) {
                        $thumbnail = $fields['article_thumbnail'] ?? '';
                        $excerpt = $fields['article_excerpt'] ?? '';
                        $articleViews = intval($fields['article_views'] ?? 0);
                        $articleLikes = intval($fields['article_likes'] ?? 0);
                    }
                }
            } catch (Exception $e) {
                // 忽略错误
            }

            // 如果没有自定义缩略图，尝试从文章内容中提取
            if (empty($thumbnail)) {
                $thumbnail = GetArticle::firstImage($article['cid']);
            }

            // 如果没有缩略图，使用默认缩略图
            if (empty($thumbnail)) {
                $thumbnail = Get::Assets('assets/images/cover/cover1.jpg');
            } else {
                $thumbnail = Get::resolveUri($thumbnail);
            }

            // 如果没有自定义摘要，使用文章前200字作为摘要
            if (empty($excerpt)) {
                $text = strip_tags($article['text']);
                $excerpt = mb_substr($text, 0, 200, 'UTF-8');
                if (mb_strlen($text, 'UTF-8') > 200) {
                    $excerpt .= '...';
                }
            }

            // 格式化日期
            $date = date('Y-m-d', $article['created']);

            $formattedArticles[] = [
                'title' => $article['title'],
                'excerpt' => $excerpt,
                'thumbnail' => $thumbnail ? Get::resolveUri($thumbnail) : '',
                'views' => $articleViews > 0 ? $articleViews : ($article['views'] ?? 0),
                'comments' => $article['commentsNum'] ?? 0,
                'likes' => $articleLikes,
                'date' => $date,
                'url' => $article['url']
            ];
        }

        return [
            'sort' => $sort,
            'layout' => $layout,
            'p' => $currentPage,
            'total' => $total,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'articles' => $formattedArticles
        ];
    }
}
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
     * @param object $archive 当前 Archive 对象（可选）
     * @return array
     */
    public static function GetHeader($body_id = 'home', $archive = null)
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

        // 仅在文章页面引入 markdown.css 和 highlight.js
        if ($body_id === 'post') {
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

        // 获取标题
        $title = GetSite::title();
        if ($body_id === 'post' && $archive && isset($archive->title)) {
            // 文章页面使用"文章名 - 站名"格式
            $siteName = GetSite::title();
            $title = $archive->title . ' - ' . $siteName;
        }

        return [
            'title'       => $title,
            'keywords'    => GetSite::keywords(),
            'description' => GetSite::description(),
            'favicon'     => Get::resolveUri(Get::themeOption('favicon')),
            'copyright'   => GetSite::authorName(),
            'author'      => GetSite::authorName(),
            'links'       => $links,
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
    public static function GetFooter($body_id = 'home')
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
        $rss_url = GetSite::rssUrl();
        $sitemap_url = Get::Assets('library/sitemap.php');

        // 构建脚本列表
        $scripts = [
            [
                'type' => 'text/javascript',
                'src' => GetSite::adminPath() . 'js/jquery.js',
            ],
            [
                'type' => 'text/javascript',
                'src' => Get::Assets('assets/js/index.js')
            ]
        ];

        // 仅在文章页面引入 highlight.js 和 article.js
        if ($body_id === 'post') {
            $scripts[] = [
                'type' => 'text/javascript',
                'src' => 'https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/highlight.min.js'
            ];
            $scripts[] = [
                'type' => 'text/javascript',
                'src' => Get::Assets('assets/js/article.js')
            ];
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
            'sitemap_url' => $sitemap_url
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
            ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'likes', 'order', 'url', 'fields'],
            $order,
            'desc',
            $perPage,
            $offset
        );

        // 格式化文章数据
        $formattedArticles = [];
        foreach ($articles as $article) {
            // 获取自定义字段
            $thumbnail = getArticleThumbnail($article);
            $excerpt = getArticleExcerpt($article, 200);
            $articleViews = getArticleViews($article);

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
                'thumbnail' => $thumbnail,
                'views' => $articleViews > 0 ? $articleViews : ($article['views'] ?? 0),
                'comments' => $article['commentsNum'] ?? 0,
                'likes' => $article['likes'] ?? 0,
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

    /* ==========================
     * ArticleReader 组件数据
     * ========================== */

    /**
     * 获取文章阅读组件数据
     * @param object $archive 当前 Archive 对象
     * @return array
     */
    public static function GetArticleData($archive = null)
    {
        // 引入 Markdown 解析器
        require_once dirname(__FILE__) . '/../Modules/MarkdownParser/MarkdownParser.php';

        // 如果没有传入 archive 对象，尝试从全局获取
        if ($archive === null) {
            global $archive;
            if ($archive === null) {
                return [];
            }
        }

        // 直接从 archive 对象获取文章数据
        $title = $archive->title ?? '';
        $text = $archive->text ?? '';
        $created = $archive->created ?? time();

        // 获取作者信息 - 使用 Typecho 自带的 Widget\User 组件
        $author = '';
        if (isset($archive->authorId) && $archive->authorId) {
            try {
                $userWidget = \Widget\User::alloc('uid=' . $archive->authorId);
                if ($userWidget->have()) {
                    $userWidget->next();
                    $author = $userWidget->name;
                }
            } catch (Exception $e) {
                // 忽略错误
            }
        }

        $views = isset($archive->views) ? $archive->views : 0;
        $commentsNum = $archive->commentsNum ?? 0;

        // 获取点赞数（从自定义字段获取）
        $likes = getArticleLikes($archive);

        // 先解析文章内容中的自定义 Markdown 语法（转换为占位符）
        $content = $text;
        $content = MarkdownParser::parse($content);

        // 再使用 Typecho 的 Markdown 解析器处理标准 Markdown
        $content = Utils\Markdown::convert($content);

        // 最后渲染自定义组件（将占位符替换为最终 HTML）
        $content = MarkdownParser::renderComponents($content);

        // 获取标签
        $tags = [];
        if (isset($archive->tags) && is_array($archive->tags)) {
            foreach ($archive->tags as $tag) {
                $tags[] = [
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'url' => $tag['permalink']
                ];
            }
        }

        // 获取分类
        $categories = [];
        if (isset($archive->categories) && is_array($archive->categories)) {
            foreach ($archive->categories as $category) {
                $categories[] = [
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'url' => $category['permalink']
                ];
            }
        }

        return [
            'title' => $title,
            'content' => $content,
            'date' => date('Y-m-d', $created),
            'author' => $author,
            'views' => $views,
            'comments' => $commentsNum,
            'likes' => $likes,
            'tags' => $tags,
            'categories' => $categories,
            'url' => $archive->permalink,
            'cid' => $archive->cid
        ];
    }

    /* ==========================
     * Sidebar 组件数据
     * ========================== */

    /**
     * 获取侧边栏组件数据
     * @param string $pageType 页面类型 ('home', 'post', 'page' 等)
     * @return array
     */
    public static function GetSidebarData($pageType = 'home')
    {
        $widgetList = [];

        // 按固定顺序读取卡片配置
        // 1. 用户信息卡片
        if (Get::themeOption('sidebar_widget_user', true)) {
            // 处理 QQ：支持QQ号或主页链接
            $qq = Get::themeOption('sidebar_user_qq', '');
            if (!empty($qq)) {
                // 如果是完整链接，提取QQ号
                if (preg_match('/qq\.com\/(\d+)/', $qq, $matches)) {
                    $qq = $matches[1];
                }
            }

            // 处理 Bilibili：UID自动生成链接
            $bilibili_uid = Get::themeOption('sidebar_user_bilibili', '');
            $bilibili = '';
            if (!empty($bilibili_uid)) {
                $bilibili = 'https://space.bilibili.com/' . trim($bilibili_uid);
            }

            $widgetList[] = [
                'type' => 'user',
                'data' => [
                    'status' => Get::themeOption('sidebar_user_status', 'EMOing'),
                    'avatar' => Get::resolveUri(Get::themeOption('sidebar_user_avatar', 'http://q1.qlogo.cn/g?b=qq&nk=2291374026&s=640')),
                    'name' => Get::themeOption('sidebar_user_name', 'Inaline'),
                    'bio' => Get::themeOption('sidebar_user_bio', '昔人已乘黄鹤去，此地空余黄鹤楼'),
                    'qq' => $qq,
                    'email' => Get::themeOption('sidebar_user_email', ''),
                    'bilibili' => $bilibili,
                    'article_count' => GetArticle::total(),
                    'comment_count' => GetComment::total()
                ]
            ];
        }

        // 2. 热门文章（文章页面不显示）
        if (Get::themeOption('sidebar_widget_hot_articles', true) && $pageType !== 'post') {
            $count = intval(Get::themeOption('sidebar_widget_hot_articles_count', 5));
            $sort = Get::themeOption('sidebar_widget_hot_articles_sort', 'views');

            // 排序映射
            $orderMap = [
                'views' => 'views',
                'comments' => 'commentsNum',
                'likes' => 'likes'
            ];

            $order = isset($orderMap[$sort]) ? $orderMap[$sort] : 'views';

            // 获取热门文章
            $articles = GetArticle::all(
                ['cid', 'title', 'slug', 'created', 'views', 'commentsNum'],
                $order,
                'desc',
                $count,
                0
            );

            // 格式化文章数据
            $formattedArticles = [];
            foreach ($articles as $article) {
                // 获取完整的文章信息（包含URL）
                $fullArticle = GetArticle::get($article['cid'], ['cid', 'title', 'url', 'created', 'views', 'commentsNum', 'fields']);
                if ($fullArticle) {
                    // 获取缩略图
                    $thumbnail = getArticleThumbnail($fullArticle);

                    // 如果没有自定义缩略图，尝试从文章内容中提取
                    if (empty($thumbnail)) {
                        $thumbnail = GetArticle::firstImage($fullArticle['cid']);
                    }

                    // 如果没有缩略图，使用默认缩略图
                    if (empty($thumbnail)) {
                        $thumbnail = Get::Assets('assets/images/cover/cover1.jpg');
                    }

                    $formattedArticles[] = [
                        'title' => $fullArticle['title'],
                        'url' => $fullArticle['url'],
                        'thumbnail' => $thumbnail,
                        'created' => date('Y-m-d', $fullArticle['created']),
                        'views' => $fullArticle['views'] ?? 0,
                        'comments' => $fullArticle['commentsNum'] ?? 0
                    ];
                }
            }

            $widgetList[] = [
                'type' => 'hot_articles',
                'data' => [
                    'articles' => $formattedArticles,
                    'sort' => $sort
                ]
            ];
        }

        // 5. 最新评论（文章页面不显示）
        if (Get::themeOption('sidebar_widget_recent_comments', true) && $pageType !== 'post') {
            $count = intval(Get::themeOption('sidebar_widget_recent_comments_count', 5));

            // 获取最新评论
            $comments = GetComment::all(
                ['coid', 'author', 'mail', 'text', 'created', 'cid'],
                'created',
                'desc',
                $count,
                0
            );

            // 格式化评论数据
            $formattedComments = [];
            foreach ($comments as $comment) {
                // 获取评论所属文章
                $article = GetArticle::get($comment['cid'], ['cid', 'title', 'url']);

                // 生成头像URL
                $avatar = '';
                if (!empty($comment['mail'])) {
                    $avatar = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment['mail']))) . '?s=32&d=mp';
                } else {
                    $avatar = 'https://www.gravatar.com/avatar/?s=32&d=mp';
                }

                $formattedComments[] = [
                    'author' => $comment['author'],
                    'avatar' => $avatar,
                    'text' => mb_substr(strip_tags($comment['text']), 0, 50, 'UTF-8'),
                    'created' => date('Y-m-d', $comment['created']),
                    'article_title' => $article ? $article['title'] : '',
                    'article_url' => $article ? $article['url'] : '',
                    'comment_url' => $article ? $article['url'] . '#comment-' . $comment['coid'] : ''
                ];
            }

            $widgetList[] = [
                'type' => 'recent_comments',
                'data' => [
                    'comments' => $formattedComments
                ]
            ];
        }

        // 6. 文章目录（仅文章页面显示）
        if ($pageType === 'post') {
            $widgetList[] = [
                'type' => 'toc',
                'data' => []
            ];
        }

        return $widgetList;
    }

    /* ==========================
     * Comment 组件数据
     * ========================== */

    /**
     * 获取评论列表数据
     * @param int $cid 文章ID
     * @param int $page 当前页码
     * @param int $pageSize 每页评论数
     * @param string $order 排序方式 ('asc' | 'desc')
     * @return array
     */
    public static function GetCommentData($cid, $page = 1, $pageSize = 10, $order = 'desc')
    {
        return [
            'cid' => $cid,
            'page' => $page,
            'pageSize' => $pageSize,
            'order' => $order
        ];
    }
}
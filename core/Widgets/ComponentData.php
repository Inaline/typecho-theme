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
     * @param string $custom_title 自定义标题（可选）
     * @param string $custom_description 自定义描述（可选）
     * @param string $custom_keywords 自定义关键词（可选）
     * @return array
     */
    public static function GetHeader($body_id = 'home', $archive = null, $custom_title = '', $custom_description = '', $custom_keywords = '')
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

        // 获取标题、描述和关键词
        if (!empty($custom_title)) {
            $title = $custom_title;
        } else {
            $title = GetSite::title();
        }
        
        if (!empty($custom_description)) {
            $description = $custom_description;
        } else {
            $description = GetSite::description();
        }
        
        if (!empty($custom_keywords)) {
            $keywords = $custom_keywords;
        } else {
            $keywords = GetSite::keywords();
        }

        if ($body_id === 'post' && $archive && isset($archive->title)) {
            // 文章页面使用"文章名 - 站名"格式
            $siteName = GetSite::title();
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
        }

        

                // 获取 Typecho 头部输出（包含评论系统所需的 JavaScript）
                // 注意：在文章页面，如果存在自定义的 description 或 keywords，需要移除 Typecho 输出的默认值并添加自定义值
                $typechoHeader = '';
                if ($archive) {
                    ob_start();
                    $archive->header();
                    $typechoHeader = ob_get_clean();
                    
                    // 检查是否是文章页面且有自定义 SEO 信息
                    $hasCustomDescription = ($body_id === 'post' && isset($archive->cid) && $archive->cid && !empty($description) && $description !== GetSite::description());
                    $hasCustomKeywords = ($body_id === 'post' && isset($archive->cid) && $archive->cid && !empty($keywords) && $keywords !== GetSite::keywords());
                    
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
            'copyright'   => GetSite::authorName(),
            'author'      => GetSite::authorName(),
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
     * TopBar 组件数据
     * ========================== */

    /**
     * 获取 TopBar 组件数据
     * @param string $current_page 当前页面名称
     * @return array
     */
    public static function GetTopBar($current_page = 'home', $category_path_slugs = [])
    {
        return [
            'logo' => Get::resolveUri(Get::themeOption('logo')),
            'logo_dark' => Get::resolveUri(Get::themeOption('logo_dark')),
            'pages' => Get::themeOption('top_bar_pages', '[{"name":"home","label":"首页","icon":"mdi-home","url":"/"}]'),
            'categories' => GetCategory::buildNavJson(),
            'current_page' => $current_page,
            'category_path_slugs' => $category_path_slugs,
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
     * @param string $body_id 页面 body_id
     * @param object $archive 当前 Archive 对象（可选）
     * @return array
     */
    public static function GetFooter($body_id = 'home', $archive = null)
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

        $commentsNum = $archive->commentsNum ?? 0;
        $likes = 0;

        // 从数据库获取并增加文章阅读数
        $views = 0;
        if (isset($archive->cid) && $archive->cid) {
            try {
                $db = \Typecho\Db::get();
                $cid = $archive->cid;
                
                // 检查是否已有 article_views 字段
                $existing = $db->fetchRow($db->select('str_value')
                    ->from('table.fields')
                    ->where('cid = ?', $cid)
                    ->where('name = ?', 'article_views')
                    ->limit(1));
                
                if ($existing) {
                    // 已存在，更新并获取新值
                    $db->query($db->update('table.fields')
                        ->expression('str_value', 'str_value + 1')
                        ->where('cid = ?', $cid)
                        ->where('name = ?', 'article_views'));
                    
                    // 重新获取更新后的值
                    $updated = $db->fetchRow($db->select('str_value')
                        ->from('table.fields')
                        ->where('cid = ?', $cid)
                        ->where('name = ?', 'article_views')
                        ->limit(1));
                    $views = intval($updated['str_value'] ?? 0);
                } else {
                    // 不存在，插入
                    $db->query($db->insert('table.fields')
                        ->rows([
                            'cid' => $cid,
                            'name' => 'article_views',
                            'type' => 'str',
                            'str_value' => 1,
                            'int_value' => 0,
                            'float_value' => 0
                        ]));
                    $views = 1;
                }
            } catch (Exception $e) {
                // 忽略错误，使用默认值 0
                $views = 0;
            }
        }

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

        // 获取文章缩略图（从自定义字段获取）
        $thumbnail = '';
        if (isset($archive->cid) && $archive->cid) {
            try {
                $db = \Typecho_Db::get();
                $thumbnailField = $db->fetchRow($db->select('str_value')
                    ->from('table.fields')
                    ->where('cid = ?', $archive->cid)
                    ->where('name = ?', 'thumbnail')
                    ->limit(1));
                
                if ($thumbnailField && !empty($thumbnailField['str_value'])) {
                    $thumbnail = $thumbnailField['str_value'];
                }
            } catch (Exception $e) {
                // 忽略错误
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
            'cid' => $archive->cid,
            'thumbnail' => $thumbnail
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

                $formattedComments[] = [
                    'author' => $comment['author'],
                    'mail' => $comment['mail'] ?? '',
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
        // 获取该文章的所有评论（只获取顶级评论）
        $allComments = GetComment::byArticle($cid, ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'text', 'status', 'parent'], 'created', $order);

        // 只保留顶级评论（parent = 0）
        $topLevelComments = array_filter($allComments, function($comment) {
            return $comment['parent'] == 0;
        });

        // 重新索引数组
        $topLevelComments = array_values($topLevelComments);

        // 计算总数和总页数
        $total = count($topLevelComments);
        $totalPages = ceil($total / $pageSize);

        // 计算偏移量
        $offset = ($page - 1) * $pageSize;

        // 获取当前页的评论
        $pageComments = array_slice($topLevelComments, $offset, $pageSize);

        // 格式化评论数据
        $formattedComments = [];
        $floor = $offset + 1; // 楼层号

        foreach ($pageComments as $comment) {
            // 获取子评论
            $children = GetComment::children($comment['coid'], ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'text', 'status', 'parent']);

            // 格式化子评论
            $formattedChildren = [];
            $childFloor = 1;
            foreach ($children as $child) {
                // 获取被回复的评论信息
                $parentComment = GetComment::get($child['parent'], ['author']);
                $parentName = $parentComment ? $parentComment['author'] : '';

                $formattedChildren[] = [
                    'coid' => $child['coid'],
                    'author' => $child['author'],
                    'authorId' => $child['authorId'],
                    'mail' => $child['mail'],
                    'text' => $child['text'],
                    'created' => date('Y-m-d H:i', $child['created']),
                    'parent' => $parentName,
                    'floor' => $floor . '.' . $childFloor
                ];
                $childFloor++;
            }

            // 生成头像URL
            $avatar = '';
            if (!empty($comment['mail'])) {
                $avatar = Get::Assets('assets/images/cover/cover1.jpg');
            } else {
                $avatar = Get::Assets('assets/images/cover/cover1.jpg');
            }

            $formattedComments[] = [
                'coid' => $comment['coid'],
                'author' => $comment['author'],
                'authorId' => $comment['authorId'],
                'mail' => $comment['mail'],
                'avatar' => $avatar,
                'text' => $comment['text'],
                'created' => date('Y-m-d H:i', $comment['created']),
                'floor' => '#' . $floor,
                'children' => $formattedChildren
            ];
            $floor++;
        }

        return [
            'cid' => $cid,
            'page' => $page,
            'pageSize' => $pageSize,
            'total' => $total,
            'totalPages' => $totalPages,
            'order' => $order,
            'comments' => $formattedComments
        ];
    }

    /* ==========================
     * Archive List 组件数据
     * ========================== */

    /**
     * 获取归档页面文章列表数据
     * @param object $archive 当前 Archive 对象
     * @param string $archive_type 归档类型 ('category', 'tag', 'search', 'author', 'date', 'archive')
     * @return array
     */
    public static function GetArchiveListData($archive = null, $archive_type = 'archive')
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

        // 使用 Widget 获取文章列表
        $articles = [];
        $total = 0;

        try {
            if ($archive_type === 'category') {
                // 分类文章 - 使用 SQL 查询，避免触发自动输出
                $db = \Typecho_Db::get();
                
                // 获取分类 mid
                $mid = 0;
                
                if (isset($archive->mid) && $archive->mid) {
                    $mid = $archive->mid;
                } elseif (isset($archive->categories) && is_array($archive->categories) && !empty($archive->categories)) {
                    $mid = $archive->categories[0]['mid'];
                } else {
                    // 从请求路径中提取分类 slug
                    $request_path = $archive->request->getPathInfo();
                    $request_path = trim($request_path, '/');
                    $path_parts = explode('/', $request_path);
                    $category_slug_from_url = end($path_parts);
                    
                    if (!empty($category_slug_from_url)) {
                        $row = $db->fetchRow($db->select('mid')->from('table.metas')
                            ->where('slug = ?', $category_slug_from_url)
                            ->where('type = ?', 'category')
                            ->order('mid', Typecho_Db::SORT_DESC)
                            ->limit(1));
                        if ($row) {
                            $mid = $row['mid'];
                        }
                    }
                    
                    // 如果通过 URL slug 查询失败，尝试其他方式
                    if ($mid === 0 && isset($archive->slug) && $archive->slug) {
                        $row = $db->fetchRow($db->select('mid')->from('table.metas')
                            ->where('slug = ?', $archive->slug)
                            ->where('type = ?', 'category')
                            ->order('mid', Typecho_Db::SORT_DESC)
                            ->limit(1));
                        if ($row) {
                            $mid = $row['mid'];
                        }
                    }
                    
                    if ($mid === 0 && isset($archive->category)) {
                        $row = $db->fetchRow($db->select('mid')->from('table.metas')
                            ->where('name = ?', $archive->category)
                            ->where('type = ?', 'category')
                            ->order('mid', Typecho_Db::SORT_DESC)
                            ->limit(1));
                        if ($row) {
                            $mid = $row['mid'];
                        }
                    }
                }
                
                // 使用 SQL 查询获取该分类下的文章
                if ($mid > 0) {
                    $rows = $db->fetchAll($db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'u.screenName as author')
                        ->from('table.contents AS c')
                        ->join('table.relationships AS r', 'c.cid = r.cid', Typecho_Db::LEFT_JOIN)
                        ->join('table.users AS u', 'c.authorId = u.uid', Typecho_Db::LEFT_JOIN)
                        ->where('r.mid = ?', $mid)
                        ->where('c.type = ?', 'post')
                        ->where('c.status = ?', 'publish')
                        ->order('c.created', Typecho_Db::SORT_DESC));
                    
                    foreach ($rows as $row) {
                        // 构建摘要
                        $text = strip_tags($row['text']);
                        $text = preg_replace('/\s+/', ' ', $text);
                        $text = trim($text);
                        $excerpt = mb_substr($text, 0, 200, 'UTF-8');
                        if (mb_strlen($text, 'UTF-8') > 200) {
                            $excerpt .= '...';
                        }
                        
                        // 构建 URL - 使用 Typecho 的 Widget 获取正确的 permalink，但不触发自动输出
                        $articleWidget = \Widget\Contents\Post\Recent::alloc();
                        // 手动设置 Widget 的属性，避免使用 push() 方法
                        foreach ($row as $key => $value) {
                            $articleWidget->row[$key] = $value;
                        }
                        $url = $articleWidget->permalink;
                        
                        $articles[] = [
                            'cid' => $row['cid'],
                            'title' => $row['title'],
                            'slug' => $row['slug'],
                            'created' => $row['created'],
                            'modified' => $row['modified'],
                            'authorId' => $row['authorId'],
                            'author' => $row['author'],
                            'text' => $row['text'],
                            'views' => 0,
                            'commentsNum' => $row['commentsNum'],
                            'order' => $row['order'],
                            'url' => $url,
                            'excerpt' => $excerpt,
                            'fields' => []
                        ];
                    }
                }
                $total = count($articles);
                
            } elseif ($archive_type === 'tag') {
                // 标签文章 - 使用 SQL 查询
                $db = \Typecho_Db::get();
                
                // 获取标签 mid
                $mid = 0;
                if (isset($archive->mid) && $archive->mid) {
                    $mid = $archive->mid;
                } elseif (isset($archive->tags) && is_array($archive->tags) && !empty($archive->tags)) {
                    $mid = $archive->tags[0]['mid'];
                } elseif (isset($archive->tag)) {
                    $row = $db->fetchRow($db->select('mid')->from('table.metas')
                        ->where('name = ?', $archive->tag)
                        ->where('type = ?', 'tag')
                        ->limit(1));
                    if ($row) {
                        $mid = $row['mid'];
                    }
                }
                
                // 使用 SQL 查询获取该标签下的文章
                if ($mid > 0) {
                    $rows = $db->fetchAll($db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'u.screenName as author')
                        ->from('table.contents AS c')
                        ->join('table.relationships AS r', 'c.cid = r.cid', Typecho_Db::LEFT_JOIN)
                        ->join('table.users AS u', 'c.authorId = u.uid', Typecho_Db::LEFT_JOIN)
                        ->where('r.mid = ?', $mid)
                        ->where('c.type = ?', 'post')
                        ->where('c.status = ?', 'publish')
                        ->order('c.created', Typecho_Db::SORT_DESC));
                    
                    foreach ($rows as $row) {
                        $text = strip_tags($row['text']);
                        $text = preg_replace('/\s+/', ' ', $text);
                        $text = trim($text);
                        $excerpt = mb_substr($text, 0, 200, 'UTF-8');
                        if (mb_strlen($text, 'UTF-8') > 200) {
                            $excerpt .= '...';
                        }
                        
                        // 构建 URL - 使用 Typecho 的 Widget 获取正确的 permalink，但不触发自动输出
                        $articleWidget = \Widget\Contents\Post\Recent::alloc();
                        // 手动设置 Widget 的属性，避免使用 push() 方法
                        foreach ($row as $key => $value) {
                            $articleWidget->row[$key] = $value;
                        }
                        $url = $articleWidget->permalink;
                        
                        $articles[] = [
                            'cid' => $row['cid'],
                            'title' => $row['title'],
                            'slug' => $row['slug'],
                            'created' => $row['created'],
                            'modified' => $row['modified'],
                            'authorId' => $row['authorId'],
                            'author' => $row['author'],
                            'text' => $row['text'],
                            'views' => 0,
                            'commentsNum' => $row['commentsNum'],
                            'order' => $row['order'],
                            'url' => $url,
                            'excerpt' => $excerpt,
                            'fields' => []
                        ];
                    }
                }
                $total = count($articles);
                
            } elseif ($archive_type === 'search') {
                // 搜索文章 - 使用 SQL 查询
                $keyword = isset($archive->keywords) ? $archive->keywords : '';
                $db = \Typecho_Db::get();
                
                if (!empty($keyword)) {
                    $rows = $db->fetchAll($db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'u.screenName as author')
                        ->from('table.contents AS c')
                        ->join('table.users AS u', 'c.authorId = u.uid', Typecho_Db::LEFT_JOIN)
                        ->where('c.type = ?', 'post')
                        ->where('c.status = ?', 'publish')
                        ->where('c.title LIKE ?', '%' . $keyword . '%')
                        ->orWhere('c.text LIKE ?', '%' . $keyword . '%')
                        ->order('c.created', Typecho_Db::SORT_DESC));
                    
                    foreach ($rows as $row) {
                        $text = strip_tags($row['text']);
                        $text = preg_replace('/\s+/', ' ', $text);
                        $text = trim($text);
                        $excerpt = mb_substr($text, 0, 200, 'UTF-8');
                        if (mb_strlen($text, 'UTF-8') > 200) {
                            $excerpt .= '...';
                        }
                        
                        // 构建 URL - 使用 Typecho 的 Widget 获取正确的 permalink，但不触发自动输出
                        $articleWidget = \Widget\Contents\Post\Recent::alloc();
                        // 手动设置 Widget 的属性，避免使用 push() 方法
                        foreach ($row as $key => $value) {
                            $articleWidget->row[$key] = $value;
                        }
                        $url = $articleWidget->permalink;
                        
                        $articles[] = [
                            'cid' => $row['cid'],
                            'title' => $row['title'],
                            'slug' => $row['slug'],
                            'created' => $row['created'],
                            'modified' => $row['modified'],
                            'authorId' => $row['authorId'],
                            'author' => $row['author'],
                            'text' => $row['text'],
                            'views' => 0,
                            'commentsNum' => $row['commentsNum'],
                            'order' => $row['order'],
                            'url' => $url,
                            'excerpt' => $excerpt,
                            'fields' => []
                        ];
                    }
                }
                $total = count($articles);
                
            } elseif ($archive_type === 'author') {
                // 作者文章 - 使用 SQL 查询
                $authorId = isset($archive->authorId) ? $archive->authorId : 0;
                $db = \Typecho_Db::get();
                
                if ($authorId > 0) {
                    $rows = $db->fetchAll($db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'u.screenName as author')
                        ->from('table.contents AS c')
                        ->join('table.users AS u', 'c.authorId = u.uid', Typecho_Db::LEFT_JOIN)
                        ->where('c.authorId = ?', $authorId)
                        ->where('c.type = ?', 'post')
                        ->where('c.status = ?', 'publish')
                        ->order('c.created', Typecho_Db::SORT_DESC));
                    
                    foreach ($rows as $row) {
                        $text = strip_tags($row['text']);
                        $text = preg_replace('/\s+/', ' ', $text);
                        $text = trim($text);
                        $excerpt = mb_substr($text, 0, 200, 'UTF-8');
                        if (mb_strlen($text, 'UTF-8') > 200) {
                            $excerpt .= '...';
                        }
                        
                        // 构建 URL - 使用 Typecho 的 Widget 获取正确的 permalink，但不触发自动输出
                        $articleWidget = \Widget\Contents\Post\Recent::alloc();
                        // 手动设置 Widget 的属性，避免使用 push() 方法
                        foreach ($row as $key => $value) {
                            $articleWidget->row[$key] = $value;
                        }
                        $url = $articleWidget->permalink;
                        
                        $articles[] = [
                            'cid' => $row['cid'],
                            'title' => $row['title'],
                            'slug' => $row['slug'],
                            'created' => $row['created'],
                            'modified' => $row['modified'],
                            'authorId' => $row['authorId'],
                            'author' => $row['author'],
                            'text' => $row['text'],
                            'views' => 0,
                            'commentsNum' => $row['commentsNum'],
                            'order' => $row['order'],
                            'url' => $url,
                            'excerpt' => $excerpt,
                            'fields' => []
                        ];
                    }
                }
                $total = count($articles);
                
            } elseif ($archive_type === 'date') {
                // 日期归档文章 - 使用 SQL 查询
                $year = isset($archive->year) ? $archive->year : 0;
                $month = isset($archive->month) ? $archive->month : 0;
                $day = isset($archive->day) ? $archive->day : 0;
                $db = \Typecho_Db::get();
                
                $select = $db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'u.screenName as author')
                    ->from('table.contents AS c')
                    ->join('table.users AS u', 'c.authorId = u.uid', Typecho_Db::LEFT_JOIN)
                    ->where('c.type = ?', 'post')
                    ->where('c.status = ?', 'publish');
                
                if ($day > 0) {
                    $startDate = mktime(0, 0, 0, $month, $day, $year);
                    $endDate = mktime(23, 59, 59, $month, $day, $year);
                    $select->where('c.created >= ?', $startDate)
                           ->where('c.created <= ?', $endDate);
                } elseif ($month > 0) {
                    $startDate = mktime(0, 0, 0, $month, 1, $year);
                    $endDate = mktime(23, 59, 59, $month + 1, 0, $year);
                    $select->where('c.created >= ?', $startDate)
                           ->where('c.created <= ?', $endDate);
                } else {
                    $startDate = mktime(0, 0, 0, 1, 1, $year);
                    $endDate = mktime(23, 59, 59, 12, 31, $year);
                    $select->where('c.created >= ?', $startDate)
                           ->where('c.created <= ?', $endDate);
                }
                
                $select->order('c.created', Typecho_Db::SORT_DESC);
                $rows = $db->fetchAll($select);
                
                foreach ($rows as $row) {
                    $text = strip_tags($row['text']);
                    $text = preg_replace('/\s+/', ' ', $text);
                    $text = trim($text);
                    $excerpt = mb_substr($text, 0, 200, 'UTF-8');
                    if (mb_strlen($text, 'UTF-8') > 200) {
                        $excerpt .= '...';
                    }
                    
                    // 构建 URL - 使用 Typecho 的 Widget 获取正确的 permalink，但不触发自动输出
                    $articleWidget = \Widget\Contents\Post\Recent::alloc();
                    // 手动设置 Widget 的属性，避免使用 push() 方法
                    foreach ($row as $key => $value) {
                        $articleWidget->row[$key] = $value;
                    }
                    $url = $articleWidget->permalink;
                    
                    $articles[] = [
                        'cid' => $row['cid'],
                        'title' => $row['title'],
                        'slug' => $row['slug'],
                        'created' => $row['created'],
                        'modified' => $row['modified'],
                        'authorId' => $row['authorId'],
                        'author' => $row['author'],
                        'text' => $row['text'],
                        'views' => 0,
                        'commentsNum' => $row['commentsNum'],
                        'order' => $row['order'],
                        'url' => $url,
                        'excerpt' => $excerpt,
                        'fields' => []
                    ];
                }
                $total = count($articles);
                
            } else {
                // 默认归档（所有文章）
                $articles = GetArticle::all(['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'likes', 'order', 'url', 'excerpt', 'fields'], $order, 'desc', 0, 0);
                $total = count($articles);
            }

            // 排序
            $sortDir = 'DESC';
            if (!empty($articles)) {
                usort($articles, function($a, $b) use ($order, $sortDir) {
                    $valA = isset($a[$order]) ? $a[$order] : 0;
                    $valB = isset($b[$order]) ? $b[$order] : 0;
                    
                    if ($sortDir === 'DESC') {
                        return $valB <=> $valA;
                    } else {
                        return $valA <=> $valB;
                    }
                });
            }

            // 计算总页数和偏移量
            $totalPages = ceil($total / $perPage);
            $offset = ($currentPage - 1) * $perPage;

            // 应用分页
            $pagedArticles = array_slice($articles, $offset, $perPage);

            // 批量获取自定义字段
            $cids = array_column($pagedArticles, 'cid');
            $allCustomFields = [];
            
            if (!empty($cids)) {
                $db = \Typecho_Db::get();
                $fieldRows = $db->fetchAll($db->select('cid', 'name', 'str_value', 'int_value', 'float_value')
                    ->from('table.fields')
                    ->where('cid IN ?', $cids));
                
                foreach ($fieldRows as $fieldRow) {
                    $cid = $fieldRow['cid'];
                    $fieldName = $fieldRow['name'];
                    $fieldValue = null;
                    
                    if (!empty($fieldRow['str_value'])) {
                        $fieldValue = $fieldRow['str_value'];
                    } elseif (!empty($fieldRow['int_value'])) {
                        $fieldValue = $fieldRow['int_value'];
                    } elseif (!empty($fieldRow['float_value'])) {
                        $fieldValue = $fieldRow['float_value'];
                    }
                    
                    if ($fieldValue !== null) {
                        if (!isset($allCustomFields[$cid])) {
                            $allCustomFields[$cid] = [];
                        }
                        $allCustomFields[$cid][$fieldName] = $fieldValue;
                    }
                }
            }

            // 格式化文章数据
            $formattedArticles = [];
            foreach ($pagedArticles as &$article) {
                $cid = $article['cid'];
                $customFields = $allCustomFields[$cid] ?? [];
                
                // 添加自定义字段
                $article['fields'] = $customFields;
                
                // 获取缩略图
                $thumbnail = getArticleThumbnail($article);
                if (empty($thumbnail)) {
                    $thumbnail = GetArticle::firstImage($cid);
                }
                if (empty($thumbnail)) {
                    $thumbnail = Get::Assets('assets/images/cover/cover1.jpg');
                } else {
                    $thumbnail = Get::resolveUri($thumbnail);
                }
                
                // 获取摘要
                $excerpt = getArticleExcerpt($article, 200);
                if (empty($excerpt)) {
                    // 从文章内容中提取摘要
                    $text = strip_tags($article['text']);
                    // 移除多余的空白字符
                    $text = preg_replace('/\s+/', ' ', $text);
                    $text = trim($text);
                    // 截取前200个字符
                    $excerpt = mb_substr($text, 0, 200, 'UTF-8');
                    if (mb_strlen($text, 'UTF-8') > 200) {
                        $excerpt .= '...';
                    }
                } else {
                    // 如果有自定义摘要，也要截断
                    $excerpt = strip_tags($excerpt);
                    $excerpt = preg_replace('/\s+/', ' ', $excerpt);
                    $excerpt = trim($excerpt);
                    $excerpt = mb_substr($excerpt, 0, 200, 'UTF-8');
                    if (mb_strlen($excerpt, 'UTF-8') >= 200) {
                        $excerpt .= '...';
                    }
                }
                
                // 获取浏览量
                $views = getArticleViews($article);
                if (empty($views)) {
                    $views = isset($customFields['article_views']) ? intval($customFields['article_views']) : 0;
                }
                if (empty($views)) {
                    $views = $article['views'] ?? 0;
                }
                
                // 获取点赞数
                $likes = isset($customFields['article_likes']) ? intval($customFields['article_likes']) : 0;
                
                // 格式化日期
                $date = date('Y-m-d', $article['created']);
                
                $formattedArticles[] = [
                    'title' => $article['title'],
                    'excerpt' => $excerpt,
                    'thumbnail' => $thumbnail,
                    'views' => $views,
                    'comments' => $article['commentsNum'] ?? 0,
                    'likes' => $likes,
                    'date' => $date,
                    'url' => $article['url']
                ];
            }
            
        } catch (Exception $e) {
            // 出错时返回空数据
            $total = 0;
            $totalPages = 0;
            $formattedArticles = [];
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
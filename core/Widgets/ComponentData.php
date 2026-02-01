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
        return GetSite::getHeaderData($body_id, $archive, $custom_title, $custom_description, $custom_keywords);
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
        return GetSite::getFooterData($body_id, $archive);
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
        return GetArticle::getListData();
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
        return GetArticle::getReaderData($archive);
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

            echo "<!-- [热门文章] 开始获取，count={$count}, pageType={$pageType} -->\n";

            // 直接使用原生 SQL 查询热门文章
            $db = \Typecho_Db::get();
            $prefix = $db->getPrefix();
            $adapter = $db->getAdapter();

            $sql = "SELECT c.cid, c.title, c.slug, c.created
                    FROM {$prefix}contents AS c
                    INNER JOIN {$prefix}fields AS f ON c.cid = f.cid AND f.name = " . $adapter->quoteValue('article_views') . "
                    WHERE c.type = " . $adapter->quoteValue('post') . "
                    AND c.status = " . $adapter->quoteValue('publish') . "
                    AND c.cid IN (
                        SELECT DISTINCT f2.cid
                        FROM {$prefix}fields AS f2
                        WHERE f2.name = " . $adapter->quoteValue('article_type') . "
                        AND f2.str_value = " . $adapter->quoteValue('article') . "
                    )
                    ORDER BY CAST(f.str_value AS UNSIGNED) DESC
                    LIMIT {$count}";

            echo "<!-- [热门文章] 执行查询: " . htmlspecialchars($sql) . " -->\n";

            $rows = $db->fetchAll($sql);

            echo "<!-- [热门文章] 查询返回 " . count($rows) . " 条记录 -->\n";

            // 格式化文章数据
            $formattedArticles = [];
            foreach ($rows as $row) {
                echo "<!-- [热门文章] 处理文章 CID: {$row['cid']}, 标题: {$row['title']} -->\n";

                // 获取完整的文章信息（包含URL、浏览量等）
                $fullArticle = GetArticle::get($row['cid'], ['cid', 'title', 'url', 'created', 'views', 'commentsNum', 'fields']);
                if ($fullArticle) {
                    echo "<!-- [热门文章] 获取到完整文章信息，URL: {$fullArticle['url']}, 浏览量: " . ($fullArticle['views'] ?? 0) . " -->\n";

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
                } else {
                    echo "<!-- [热门文章] 获取完整文章信息失败，CID: {$row['cid']} -->\n";
                }
            }

            echo "<!-- [热门文章] 格式化完成，最终文章数量: " . count($formattedArticles) . " -->\n";

            $widgetList[] = [
                'type' => 'hot_articles',
                'data' => [
                    'articles' => $formattedArticles
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
        return GetComment::getListData($cid, $page, $pageSize, $order);
    }

    /* ==========================
     * Archive List 组件数据
     * ========================== */

    /**
     * 获取归档页面文章列表数据
     * @param object $archive 当前 Archive 对象
     * @param string $archive_type 归档类型 ('category', 'tag', 'search', 'author', 'date', 'archive')
     * @param int $category_mid 分类 ID（可选）
     * @param string $keywords 搜索关键词（可选）
     * @return array
     */
    public static function GetArchiveListData($archive = null, $archive_type = 'archive', $category_mid = 0, $keywords = '')
    {
        return GetArticle::getArchiveListData($archive, $archive_type, $category_mid, $keywords);
    }
}
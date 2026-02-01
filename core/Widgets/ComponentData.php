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


            $rows = $db->fetchAll($sql);


            // 格式化文章数据
            $formattedArticles = [];
            foreach ($rows as $row) {

                // 获取完整的文章信息（包含URL、浏览量等）
                $fullArticle = GetArticle::get($row['cid'], ['cid', 'title', 'url', 'created', 'views', 'commentsNum', 'fields']);
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
                } else {
                }
            }


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

    /* ==========================
     * Shuoshuo List 组件数据
     * ========================== */

    /**
     * 获取说说列表数据
     * @param int $page 当前页码
     * @param int $pageSize 每页说说数量
     * @param int|null $shuoshuoId 说说ID（用于查询单个说说）
     * @return array
     */
    public static function GetShuoshuoListData($page = 1, $pageSize = 10, $shuoshuoId = null)
    {
        $db = \Typecho_Db::get();
        $prefix = $db->getPrefix();
        $adapter = $db->getAdapter();

        // 如果指定了说说ID，只查询该说说
        if ($shuoshuoId) {
            $sql = "SELECT c.cid, c.title, c.slug, c.created, c.text, c.authorId, c.allowComment
                    FROM {$prefix}contents AS c
                    INNER JOIN {$prefix}fields AS f ON c.cid = f.cid AND f.name = " . $adapter->quoteValue('article_type') . "
                    WHERE c.type = " . $adapter->quoteValue('post') . "
                    AND c.status = " . $adapter->quoteValue('publish') . "
                    AND f.str_value = " . $adapter->quoteValue('shuoshuo') . "
                    AND c.cid = " . $adapter->quoteValue($shuoshuoId) . "
                    LIMIT 1";

            $rows = $db->fetchAll($sql);
            $total = 1;
        } else {
            // 计算偏移量
            $offset = ($page - 1) * $pageSize;

            // 查询说说类型的文章
            $sql = "SELECT c.cid, c.title, c.slug, c.created, c.text, c.authorId, c.allowComment
                    FROM {$prefix}contents AS c
                    INNER JOIN {$prefix}fields AS f ON c.cid = f.cid AND f.name = " . $adapter->quoteValue('article_type') . "
                    WHERE c.type = " . $adapter->quoteValue('post') . "
                    AND c.status = " . $adapter->quoteValue('publish') . "
                    AND f.str_value = " . $adapter->quoteValue('shuoshuo') . "
                    ORDER BY c.created DESC
                    LIMIT {$pageSize} OFFSET {$offset}";

            $rows = $db->fetchAll($sql);

            // 获取总数
            $countSql = "SELECT COUNT(*) as total
                         FROM {$prefix}contents AS c
                         INNER JOIN {$prefix}fields AS f ON c.cid = f.cid AND f.name = " . $adapter->quoteValue('article_type') . "
                         WHERE c.type = " . $adapter->quoteValue('post') . "
                         AND c.status = " . $adapter->quoteValue('publish') . "
                         AND f.str_value = " . $adapter->quoteValue('shuoshuo');

            $countResult = $db->fetchRow($countSql);
            $total = $countResult['total'] ?? 0;
        }

        // 格式化说说数据
        $formattedShuoshuos = [];
        foreach ($rows as $row) {
            // 获取完整的说说信息
            $fullShuoshuo = GetArticle::get($row['cid'], ['cid', 'title', 'url', 'created', 'views', 'commentsNum', 'text', 'fields']);
            if ($fullShuoshuo) {
                // 获取用户信息
                $user = $db->fetchRow($db->select('name', 'screenName')->from('table.users')->where('uid = ?', $row['authorId']));

                // 解析说说内容，分离 markdown 和图片
                $parsedContent = self::parseShuoshuoContent($fullShuoshuo['text']);

                // 获取评论数据
                $comments = [];
                if ($row['allowComment'] == '1' && $fullShuoshuo['commentsNum'] > 0) {
                    $comments = GetComment::getListData($row['cid'], 1, 2, 'desc');
                    $comments = $comments['comments'] ?? [];
                }

                $formattedShuoshuos[] = [
                    'cid' => $fullShuoshuo['cid'],
                    'title' => $fullShuoshuo['title'] ?? '',
                    'content' => $parsedContent['markdown'],
                    'images' => $parsedContent['images'],
                    'url' => $fullShuoshuo['url'],
                    'created' => date('Y-m-d H:i:s', $fullShuoshuo['created']),
                    'created_date' => date('Y-m-d', $fullShuoshuo['created']),
                    'created_time' => date('H:i', $fullShuoshuo['created']),
                    'author' => $user ? $user['screenName'] : '匿名',
                    'comments' => $fullShuoshuo['commentsNum'] ?? 0,
                    'allow_comment' => $row['allowComment'] == '1',
                    'comment_list' => $comments
                ];
            }
        }

        return [
            'shuoshuos' => $formattedShuoshuos,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'total_pages' => ceil($total / $pageSize),
            'single_mode' => $shuoshuoId !== null
        ];
    }

    /**
     * 解析说说内容，分离 markdown 和图片
     * @param string $content 原始内容
     * @return array ['markdown' => string, 'images' => array]
     */
    private static function parseShuoshuoContent($content)
    {
        $markdown = '';
        $images = [];

        // 分割内容，查找分隔线 %----------%
        $parts = explode("%----------%", $content);

        if (count($parts) >= 2) {
            // 第一部分是 markdown 内容
            $markdown = trim($parts[0]);

            // 第二部分开始是图片列表
            $imagePart = trim($parts[1]);

            // 解析图片列表
            $lines = explode("\n", $imagePart);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // 匹配 markdown 图片格式: ![描述][数字] 或 ![描述](url)
                if (preg_match('/!\[([^\]]*)\]\[(\d+)\]/', $line, $matches)) {
                    // 引用格式，需要查找对应的链接定义
                    $refId = $matches[2];
                    if (preg_match('/\[' . $refId . '\]:\s*(.+)/', $imagePart, $urlMatch)) {
                        $images[] = [
                            'url' => trim($urlMatch[1]),
                            'alt' => $matches[1]
                        ];
                    }
                } elseif (preg_match('/!\[([^\]]*)\]\(([^)]+)\)/', $line, $matches)) {
                    // 直接链接格式
                    $images[] = [
                        'url' => $matches[2],
                        'alt' => $matches[1]
                    ];
                }
            }
        } else {
            // 没有分隔线，全部作为 markdown 内容
            $markdown = $content;
        }

        // 从 markdown 内容中移除图片（如果有图片在 markdown 部分）
        $markdown = preg_replace('/!\[([^\]]*)\]\[?\d*\]?\([^\)]*\)/', '', $markdown);
        $markdown = preg_replace('/!\[([^\]]*)\]\[\d+\]/', '', $markdown);

        return [
            'markdown' => $markdown,
            'images' => $images
        ];
    }
}
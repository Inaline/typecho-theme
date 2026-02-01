<?php
/**
 * Inaline Typecho 主题 GetArticle 方法类
 * 提供文章相关获取
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class GetArticle
{
    /* ==========================
     * 基础信息
     * ========================== */

    /**
     * 获取所有文章
     * @param array $fields 指定返回字段
     * @param string $order 排序字段 'created', 'modified', 'views', 'commentsNum', 'likes', 'mid'
     * @param string $sort 排序方向 'asc', 'desc'
     * @param int $limit 返回数量限制，0 表示不限制
     * @param int $offset 偏移量
     * @return array
     */
    public static function all($fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'likes', 'order'], $order = 'created', $sort = 'desc', $limit = 0, $offset = 0)
    {
        $result = [];

        echo "<!-- [GetArticle::all] 开始执行，order={$order}, sort={$sort}, limit={$limit}, offset={$offset} -->\n";

        try {
            $db = \Typecho_Db::get();

            // 排序映射
            $orderMap = [
                'created' => 'c.created',
                'modified' => 'c.modified',
                'views' => 'article_views',
                'commentsNum' => 'c.commentsNum',
                'likes' => 'article_likes',
                'mid' => 'c.cid',
                'order' => 'c.order'
            ];

            $sortDir = strtolower($sort) === 'desc' ? \Typecho_Db::SORT_DESC : \Typecho_Db::SORT_ASC;

            echo "<!-- [GetArticle::all] 排序字段: {$order}, 排序方向: {$sortDir} -->\n";

            // 构建基础查询
            $select = $db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'c.status')
                ->from('table.contents AS c')
                ->where('c.type = ?', 'post')
                ->where('c.status = ?', 'publish');

            // 如果按自定义字段排序，需要 JOIN fields 表
            if ($order === 'views' || $order === 'likes') {
                $fieldName = $order === 'views' ? 'article_views' : 'article_likes';
                echo "<!-- [GetArticle::all] 按自定义字段排序: {$fieldName}，使用 INNER JOIN -->\n";
                // 使用 INNER JOIN 只查询有该字段的文章
                $select->join('table.fields AS f', 'c.cid = f.cid AND f.name = ?', $fieldName, \Typecho_Db::INNER_JOIN);
            }

            // 应用排序
            if ($order === 'views' || $order === 'likes') {
                // 先添加临时排序，稍后替换
                $select->order('f.str_value', $sortDir);
            } else {
                $sortField = isset($orderMap[$order]) ? $orderMap[$order] : 'c.created';
                $select->order($sortField, $sortDir);
            }

            // 应用限制和偏移
            if ($limit > 0) {
                $select->limit($limit);
            }
            if ($offset > 0) {
                $select->offset($offset);
            }

            // 执行查询
            $sql = $select->__toString();
            // 修复 CAST 被加反引号的问题
            if ($order === 'views' || $order === 'likes') {
                $sql = str_replace('ORDER BY `f`.`str_value`', 'ORDER BY CAST(f.str_value AS UNSIGNED)', $sql);
            }

            echo "<!-- [GetArticle::all] 执行查询: " . htmlspecialchars($sql) . " -->\n";
            $rows = $db->fetchAll($sql);

            echo "<!-- [GetArticle::all] 查询返回 " . count($rows) . " 条记录 -->\n";

            if (empty($rows)) {
                return [];
            }

            // 收集所有 CID
            $cids = array_column($rows, 'cid');

            echo "<!-- [GetArticle::all] 收集到 CID 数量: " . count($cids) . " -->\n";

            // 使用批量查询方法获取自定义字段和作者信息
            $allCustomFields = self::batchGetCustomFields($cids);
            $authors = self::batchGetAuthors(array_column($rows, 'authorId'));

            echo "<!-- [GetArticle::all] 自定义字段和作者信息获取完成 -->\n";

            // 格式化文章数据
            $filteredCount = 0;
            foreach ($rows as $row) {
                $cid = $row['cid'];
                $customFields = $allCustomFields[$cid] ?? [];

                // 只获取 article_type 为 article 的文章
                $articleType = isset($customFields['article_type']) ? $customFields['article_type'] : 'article';
                if ($articleType !== 'article') {
                    echo "<!-- [GetArticle::all] 过滤文章 CID: {$cid}，类型: {$articleType} -->\n";
                    continue;
                }

                $filteredCount++;

                $item = [];

                if (in_array('cid', $fields)) $item['cid'] = $cid;
                if (in_array('title', $fields)) $item['title'] = $row['title'];
                if (in_array('slug', $fields)) $item['slug'] = $row['slug'];
                if (in_array('created', $fields)) $item['created'] = $row['created'];
                if (in_array('modified', $fields)) $item['modified'] = $row['modified'];
                if (in_array('authorId', $fields)) $item['authorId'] = $row['authorId'];
                if (in_array('author', $fields)) $item['author'] = $authors[$row['authorId']] ?? '';
                if (in_array('text', $fields)) $item['text'] = $row['text'];
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $row['commentsNum'];
                if (in_array('order', $fields)) $item['order'] = $row['order'];

                // 浏览量和点赞数从自定义字段获取
                if (in_array('views', $fields)) {
                    $item['views'] = isset($customFields['article_views']) ? intval($customFields['article_views']) : 0;
                }
                if (in_array('likes', $fields)) {
                    $item['likes'] = isset($customFields['article_likes']) ? intval($customFields['article_likes']) : 0;
                }

                // 添加自定义字段
                if (in_array('fields', $fields)) {
                    $item['fields'] = $customFields;
                }

                // 生成 URL - 使用优化后的方法
                if (in_array('url', $fields)) {
                    $item['url'] = self::getPermalinkByCid($row['cid'], $row['slug'], $row['created']);
                }

                // 生成摘要
                if (in_array('excerpt', $fields)) {
                    $text = strip_tags($row['text']);
                    $item['excerpt'] = mb_substr($text, 0, 200, 'UTF-8') . '...';
                }

                $result[] = $item;
            }

            echo "<!-- [GetArticle::all] 过滤后文章数量: {$filteredCount}，最终返回数量: " . count($result) . " -->\n";

        } catch (Exception $e) {
            echo "<!-- [GetArticle::all] 异常: " . $e->getMessage() . " -->\n";
            return [];
        }

        return $result;
    }

    /**
     * 获取单篇文章
     * @param int|string $cid 文章 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function get($cid, $fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'])
    {
        $result = null;
    
        try {
            $db = \Typecho_Db::get();
            
            // 判断是 ID 还是 slug
            if (is_numeric($cid)) {
                $row = $db->fetchRow($db->select()->from('table.contents')->where('cid = ?', $cid)->where('type = ?', 'post')->limit(1));
            } else {
                $row = $db->fetchRow($db->select()->from('table.contents')->where('slug = ?', $cid)->where('type = ?', 'post')->limit(1));
            }
    
            if ($row) {
                $item = [];
                
                if (in_array('cid', $fields)) $item['cid'] = $row['cid'];
                if (in_array('title', $fields)) $item['title'] = $row['title'];
                if (in_array('slug', $fields)) $item['slug'] = $row['slug'];
                if (in_array('created', $fields)) $item['created'] = $row['created'];
                if (in_array('modified', $fields)) $item['modified'] = $row['modified'];
                if (in_array('authorId', $fields)) $item['authorId'] = $row['authorId'];
                if (in_array('author', $fields)) {
                    // 获取作者信息
                    $authorRow = $db->fetchRow($db->select('screenName')->from('table.users')->where('uid = ?', $row['authorId'])->limit(1));
                    $item['author'] = $authorRow ? $authorRow['screenName'] : '';
                }
                if (in_array('text', $fields)) $item['text'] = $row['text'];
                if (in_array('views', $fields)) {
                    // 从 fields 表中获取浏览量
                    $viewField = $db->fetchRow($db->select('str_value')->from('table.fields')->where('cid = ?', $row['cid'])->where('name = ?', 'article_views')->limit(1));
                    $item['views'] = $viewField ? intval($viewField['str_value']) : 0;
                }
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $row['commentsNum'];
                if (in_array('order', $fields)) $item['order'] = $row['order'];
                if (in_array('url', $fields)) {
                    echo "<!-- [get] 请求 CID: {$row['cid']} 的 URL, slug: '{$row['slug']}' -->\n";

                    // 直接使用 getPermalinkByCid 方法
                    $item['url'] = self::getPermalinkByCid($row['cid'], $row['slug'], $row['created']);

                    echo "<!-- [get] 最终 URL: {$item['url']} -->\n";
                }
                if (in_array('excerpt', $fields)) {
                    $text = strip_tags($row['text']);
                    $item['excerpt'] = mb_substr($text, 0, 200, 'UTF-8') . '...';
                }
                
                // 添加自定义字段
                if (in_array('fields', $fields)) {
                    $item['fields'] = self::getCustomFields($row['cid']);
                }
    
                $result = $item;
            }
        } catch (Exception $e) {
            return null;
        }
    
        return $result;
    }

    /**
     * 获取文章自定义字段
     * @param int $cid 文章 ID
     * @return array
     */
    private static function getCustomFields($cid)
    {
        $fields = [];
        
        try {
            $db = \Typecho\Db::get();
            $fieldRows = $db->fetchAll($db->select('name', 'str_value', 'int_value', 'float_value')
                ->from('table.fields')
                ->where('cid = ?', $cid));

            foreach ($fieldRows as $fieldRow) {
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
                    $fields[$fieldName] = $fieldValue;
                }
            }
        } catch (Exception $e) {
            $fields = [];
        }
        
        return $fields;
    }

    /**
     * 获取文章标题
     * @param int|string $cid 文章 ID 或缩略名
     * @return string
     */
    public static function title($cid)
    {
        $article = self::get($cid, ['title']);
        return $article ? $article['title'] : '';
    }

    /**
     * 获取文章缩略名
     * @param int|string $cid 文章 ID 或缩略名
     * @return string
     */
    public static function slug($cid)
    {
        $article = self::get($cid, ['slug']);
        return $article ? $article['slug'] : '';
    }

    /**
     * 获取文章内容
     * @param int|string $cid 文章 ID 或缩略名
     * @return string
     */
    public static function content($cid)
    {
        $article = self::get($cid, ['text']);
        return $article ? $article['text'] : '';
    }

    /**
     * 获取文章摘要
     * @param int|string $cid 文章 ID 或缩略名
     * @param int $length 摘要长度
     * @return string
     */
    public static function excerpt($cid, $length = 200)
    {
        $article = self::get($cid, ['text']);
        if (!$article) {
            return '';
        }
        
        $text = strip_tags($article['text']);
        $text = mb_substr($text, 0, $length, 'UTF-8');
        return $text . (mb_strlen($text, 'UTF-8') >= $length ? '...' : '');
    }

    /**
     * 获取文章 URL
     * @param int|string $cid 文章 ID 或缩略名
     * @return string
     */
    public static function url($cid)
    {
        $article = self::get($cid, ['url']);
        return $article ? $article['url'] : '';
    }

    /**
     * 获取文章作者
     * @param int|string $cid 文章 ID 或缩略名
     * @return string
     */
    public static function author($cid)
    {
        $article = self::get($cid, ['author']);
        return $article ? $article['author'] : '';
    }

    /**
     * 获取文章作者 ID
     * @param int|string $cid 文章 ID 或缩略名
     * @return int
     */
    public static function authorId($cid)
    {
        $article = self::get($cid, ['authorId']);
        return $article ? $article['authorId'] : 0;
    }

    /**
     * 获取文章浏览量
     * @param int|string $cid 文章 ID 或缩略名
     * @return int
     */
    public static function views($cid)
    {
        $article = self::get($cid, ['views']);
        return $article ? $article['views'] : 0;
    }

    /**
     * 获取文章评论数
     * @param int|string $cid 文章 ID 或缩略名
     * @return int
     */
    public static function commentsNum($cid)
    {
        $article = self::get($cid, ['commentsNum']);
        return $article ? $article['commentsNum'] : 0;
    }

    /**
     * 获取文章创建时间
     * @param int|string $cid 文章 ID 或缩略名
     * @param string $format 时间格式，默认为 'Y-m-d H:i:s'
     * @return string
     */
    public static function created($cid, $format = 'Y-m-d H:i:s')
    {
        $article = self::get($cid, ['created']);
        return $article ? date($format, $article['created']) : '';
    }

    /**
     * 获取文章修改时间
     * @param int|string $cid 文章 ID 或缩略名
     * @param string $format 时间格式，默认为 'Y-m-d H:i:s'
     * @return string
     */
    public static function modified($cid, $format = 'Y-m-d H:i:s')
    {
        $article = self::get($cid, ['modified']);
        return $article ? date($format, $article['modified']) : '';
    }

    /* ==========================
     * 分类相关
     * ========================== */

    /**
     * 获取文章所属分类
     * @param int|string $cid 文章 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function categories($cid, $fields = ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order'])
    {
        $result = [];
        
        try {
            $widget = \Widget\Contents\Post\Recent::alloc('pageSize=999999');
            
            while ($widget->next()) {
                if ($widget->cid == $cid || $widget->slug == $cid) {
                    $categories = $widget->categories;
                    foreach ($categories as $category) {
                        $item = [];
                        
                        if (in_array('mid', $fields)) $item['mid'] = $category['mid'];
                        if (in_array('name', $fields)) $item['name'] = $category['name'];
                        if (in_array('slug', $fields)) $item['slug'] = $category['slug'];
                        if (in_array('parent', $fields)) $item['parent'] = $category['parent'];
                        if (in_array('description', $fields)) $item['description'] = $category['description'];
                        if (in_array('count', $fields)) $item['count'] = $category['count'];
                        if (in_array('order', $fields)) $item['order'] = $category['order'];
                        if (in_array('url', $fields)) $item['url'] = $category['permalink'];
                        
                        $result[] = $item;
                    }
                    break;
                }
            }
        } catch (Exception $e) {
            return [];
        }

        return $result;
    }

    /**
     * 获取文章主分类（第一个分类）
     * @param int|string $cid 文章 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function category($cid, $fields = ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order'])
    {
        $categories = self::categories($cid, $fields);
        return !empty($categories) ? $categories[0] : null;
    }

    /* ==========================
     * 标签相关
     * ========================== */

    /**
     * 获取文章标签
     * @param int|string $cid 文章 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function tags($cid, $fields = ['mid', 'name', 'slug', 'count', 'order'])
    {
        $result = [];
        
        try {
            $widget = \Widget\Contents\Post\Recent::alloc('pageSize=999999');
            
            while ($widget->next()) {
                if ($widget->cid == $cid || $widget->slug == $cid) {
                    $tags = $widget->tags;
                    foreach ($tags as $tag) {
                        $item = [];
                        
                        if (in_array('mid', $fields)) $item['mid'] = $tag['mid'];
                        if (in_array('name', $fields)) $item['name'] = $tag['name'];
                        if (in_array('slug', $fields)) $item['slug'] = $tag['slug'];
                        if (in_array('count', $fields)) $item['count'] = $tag['count'];
                        if (in_array('order', $fields)) $item['order'] = $tag['order'];
                        if (in_array('url', $fields)) $item['url'] = $tag['permalink'];
                        
                        $result[] = $item;
                    }
                    break;
                }
            }
        } catch (Exception $e) {
            return [];
        }

        return $result;
    }

    /* ==========================
     * 按分类获取文章
     * ========================== */

    /**
     * 获取指定分类下的文章
     * @param int|string $mid 分类 ID 或缩略名
     * @param array $fields 指定返回字段
     * @param string $order 排序字段
     * @param string $sort 排序方向
     * @param int $limit 返回数量限制
     * @return array
     */
    public static function byCategory($mid, $fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'], $order = 'created', $sort = 'desc', $limit = 0)
    {
        $result = [];

        try {
            $db = \Typecho_Db::get();

            // 排序映射
            $orderMap = [
                'created' => 'c.created',
                'modified' => 'c.modified',
                'views' => 'article_views',
                'commentsNum' => 'c.commentsNum',
                'likes' => 'article_likes',
                'mid' => 'c.cid'
            ];

            $sortField = isset($orderMap[$order]) ? $orderMap[$order] : 'c.created';
            $sortDir = strtolower($sort) === 'desc' ? \Typecho_Db::SORT_DESC : \Typecho_Db::SORT_ASC;

            // 构建查询 - 使用 JOIN 获取分类下的文章
            $select = $db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'c.status')
                ->from('table.contents AS c')
                ->join('table.relationships AS r', 'c.cid = r.cid', \Typecho_Db::LEFT_JOIN)
                ->where('c.type = ?', 'post')
                ->where('c.status = ?', 'publish')
                ->where('r.mid = ?', $mid);

            // 如果按自定义字段排序，需要再次 JOIN fields 表
            if ($order === 'views' || $order === 'likes') {
                $fieldName = $order === 'views' ? 'article_views' : 'article_likes';
                $select->join('table.fields AS f', 'c.cid = f.cid AND f.name = ?', $fieldName, \Typecho_Db::LEFT_JOIN)
                    ->order(new \Typecho_Db_Expr('CAST(f.str_value AS UNSIGNED)'), $sortDir);
            } else {
                $select->order($sortField, $sortDir);
            }

            if ($limit > 0) {
                $select->limit($limit);
            }

            $rows = $db->fetchAll($select);

            if (empty($rows)) {
                return [];
            }

            $cids = array_column($rows, 'cid');

            // 使用批量查询方法
            $allCustomFields = self::batchGetCustomFields($cids);
            $authors = self::batchGetAuthors(array_column($rows, 'authorId'));

            foreach ($rows as $row) {
                $cid = $row['cid'];
                $customFields = $allCustomFields[$cid] ?? [];

                // 只获取 article_type 为 article 的文章
                $articleType = isset($customFields['article_type']) ? $customFields['article_type'] : 'article';
                if ($articleType !== 'article') {
                    continue;
                }

                $item = [];

                if (in_array('cid', $fields)) $item['cid'] = $cid;
                if (in_array('title', $fields)) $item['title'] = $row['title'];
                if (in_array('slug', $fields)) $item['slug'] = $row['slug'];
                if (in_array('created', $fields)) $item['created'] = $row['created'];
                if (in_array('modified', $fields)) $item['modified'] = $row['modified'];
                if (in_array('authorId', $fields)) $item['authorId'] = $row['authorId'];
                if (in_array('author', $fields)) $item['author'] = $authors[$row['authorId']] ?? '';
                if (in_array('text', $fields)) $item['text'] = $row['text'];
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $row['commentsNum'];
                if (in_array('order', $fields)) $item['order'] = $row['order'];

                if (in_array('views', $fields)) {
                    $item['views'] = isset($customFields['article_views']) ? intval($customFields['article_views']) : 0;
                }

                if (in_array('url', $fields)) {
                    $item['url'] = self::getPermalinkByCid($row['cid'], $row['slug'], $row['created']);
                }

                if (in_array('excerpt', $fields)) {
                    $text = strip_tags($row['text']);
                    $item['excerpt'] = mb_substr($text, 0, 200, 'UTF-8') . '...';
                }

                $result[] = $item;
            }

        } catch (Exception $e) {
            return [];
        }

        return $result;
    }

    /* ==========================
     * 按标签获取文章
     * ========================== */

    /**
     * 获取指定标签下的文章
     * @param int|string $mid 标签 ID 或缩略名
     * @param array $fields 指定返回字段
     * @param string $order 排序字段
     * @param string $sort 排序方向
     * @param int $limit 返回数量限制
     * @return array
     */
    public static function byTag($mid, $fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'], $order = 'created', $sort = 'desc', $limit = 0)
    {
        $result = [];

        try {
            $db = \Typecho_Db::get();

            // 排序映射
            $orderMap = [
                'created' => 'c.created',
                'modified' => 'c.modified',
                'views' => 'article_views',
                'commentsNum' => 'c.commentsNum',
                'likes' => 'article_likes',
                'mid' => 'c.cid'
            ];

            $sortField = isset($orderMap[$order]) ? $orderMap[$order] : 'c.created';
            $sortDir = strtolower($sort) === 'desc' ? \Typecho_Db::SORT_DESC : \Typecho_Db::SORT_ASC;

            // 构建查询 - 使用 JOIN 获取标签下的文章
            $select = $db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'c.status')
                ->from('table.contents AS c')
                ->join('table.relationships AS r', 'c.cid = r.cid', \Typecho_Db::LEFT_JOIN)
                ->where('c.type = ?', 'post')
                ->where('c.status = ?', 'publish')
                ->where('r.mid = ?', $mid);

            // 如果按自定义字段排序，需要再次 JOIN fields 表
            if ($order === 'views' || $order === 'likes') {
                $fieldName = $order === 'views' ? 'article_views' : 'article_likes';
                $select->join('table.fields AS f', 'c.cid = f.cid AND f.name = ?', $fieldName, \Typecho_Db::LEFT_JOIN)
                    ->order(new \Typecho_Db_Expr('CAST(f.str_value AS UNSIGNED)'), $sortDir);
            } else {
                $select->order($sortField, $sortDir);
            }

            if ($limit > 0) {
                $select->limit($limit);
            }

            $rows = $db->fetchAll($select);

            if (empty($rows)) {
                return [];
            }

            $cids = array_column($rows, 'cid');

            // 使用批量查询方法
            $allCustomFields = self::batchGetCustomFields($cids);
            $authors = self::batchGetAuthors(array_column($rows, 'authorId'));

            foreach ($rows as $row) {
                $cid = $row['cid'];
                $customFields = $allCustomFields[$cid] ?? [];

                // 只获取 article_type 为 article 的文章
                $articleType = isset($customFields['article_type']) ? $customFields['article_type'] : 'article';
                if ($articleType !== 'article') {
                    continue;
                }

                $item = [];

                if (in_array('cid', $fields)) $item['cid'] = $cid;
                if (in_array('title', $fields)) $item['title'] = $row['title'];
                if (in_array('slug', $fields)) $item['slug'] = $row['slug'];
                if (in_array('created', $fields)) $item['created'] = $row['created'];
                if (in_array('modified', $fields)) $item['modified'] = $row['modified'];
                if (in_array('authorId', $fields)) $item['authorId'] = $row['authorId'];
                if (in_array('author', $fields)) $item['author'] = $authors[$row['authorId']] ?? '';
                if (in_array('text', $fields)) $item['text'] = $row['text'];
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $row['commentsNum'];
                if (in_array('order', $fields)) $item['order'] = $row['order'];

                if (in_array('views', $fields)) {
                    $item['views'] = isset($customFields['article_views']) ? intval($customFields['article_views']) : 0;
                }

                if (in_array('url', $fields)) {
                    $item['url'] = self::getPermalinkByCid($row['cid'], $row['slug'], $row['created']);
                }

                if (in_array('excerpt', $fields)) {
                    $text = strip_tags($row['text']);
                    $item['excerpt'] = mb_substr($text, 0, 200, 'UTF-8') . '...';
                }

                $result[] = $item;
            }

        } catch (Exception $e) {
            return [];
        }

        return $result;
    }

    /* ==========================
     * 当前文章
     * ========================== */

    /**
     * 获取当前正在显示的文章
     * @return array|null
     */
    public static function current()
    {
        try {
            $widget = \Widget\Contents\Post::alloc();
            
            if ($widget->have()) {
                $widget->next();
                
                return [
                    'cid' => $widget->cid,
                    'title' => $widget->title,
                    'slug' => $widget->slug,
                    'created' => $widget->created,
                    'modified' => $widget->modified,
                    'authorId' => $widget->authorId,
                    'author' => $widget->author,
                    'text' => $widget->text,
                    'views' => isset($widget->views) ? $widget->views : 0,
                    'commentsNum' => $widget->commentsNum,
                    'order' => $widget->order,
                    'url' => $widget->permalink,
                    'excerpt' => $widget->excerpt(200, '...'),
                    'tags' => self::getTagsFromWidget($widget),
                    'categories' => self::getCategoriesFromWidget($widget)
                ];
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * 从 Widget 对象中获取标签
     * @param object $widget Widget 对象
     * @return array
     */
    private static function getTagsFromWidget($widget)
    {
        $tags = [];
        
        if (isset($widget->tags) && is_array($widget->tags)) {
            foreach ($widget->tags as $tag) {
                $tags[] = [
                    'mid' => $tag['mid'],
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'count' => $tag['count'],
                    'order' => $tag['order'],
                    'url' => $tag['permalink']
                ];
            }
        }
        
        return $tags;
    }

    /**
     * 从 Widget 对象中获取分类
     * @param object $widget Widget 对象
     * @return array
     */
    private static function getCategoriesFromWidget($widget)
    {
        $categories = [];
        
        if (isset($widget->categories) && is_array($widget->categories)) {
            foreach ($widget->categories as $category) {
                $categories[] = [
                    'mid' => $category['mid'],
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'parent' => $category['parent'],
                    'description' => $category['description'],
                    'count' => $category['count'],
                    'order' => $category['order'],
                    'url' => $category['permalink']
                ];
            }
        }
        
        return $categories;
    }

    /* ==========================
     * 实用工具
     * ========================== */

    /**
     * 检查文章是否存在
     * @param int|string $cid 文章 ID 或缩略名
     * @return bool
     */
    public static function exists($cid)
    {
        return self::get($cid) !== null;
    }

    /**
     * 获取文章总数
     * @return int
     */
    public static function total()
    {
        echo "<!-- [Article] total() 开始 -->\n";

        try {
            $db = \Typecho_Db::get();

            // 获取所有文章的 CID
            $rows = $db->fetchAll($db->select('c.cid')
                ->from('table.contents AS c')
                ->where('c.type = ?', 'post')
                ->where('c.status = ?', 'publish'));

            echo "<!-- [Article] total() 查询到 " . count($rows) . " 篇已发布文章 -->\n";

            if (empty($rows)) {
                return 0;
            }

            $cids = array_column($rows, 'cid');

            // 批量获取自定义字段
            $allCustomFields = [];
            $fieldRows = $db->fetchAll($db->select('cid', 'name', 'str_value', 'int_value', 'float_value')
                ->from('table.fields')
                ->where('cid IN ?', $cids));

            echo "<!-- [Article] total() 查询到 " . count($fieldRows) . " 条自定义字段记录 -->\n";

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

            // 统计 article_type 为 article 的文章数量
            $count = 0;
            foreach ($cids as $cid) {
                $customFields = $allCustomFields[$cid] ?? [];
                $articleType = isset($customFields['article_type']) ? $customFields['article_type'] : 'article';
                if ($articleType === 'article') {
                    $count++;
                }
            }

            echo "<!-- [Article] total() 最终统计: {$count} 篇有效文章 -->\n";

            return $count;
        } catch (Exception $e) {
            echo "<!-- [Article] total() 异常: " . $e->getMessage() . " -->\n";
            return 0;
        }
    }

    /**
     * 获取热门文章（按浏览量排序）
     * @param int $limit 返回数量限制
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function popular($limit = 10, $fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'])
    {
        return self::all($fields, 'views', 'desc', $limit);
    }

    /**
     * 获取最新文章
     * @param int $limit 返回数量限制
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function latest($limit = 10, $fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'])
    {
        return self::all($fields, 'created', 'desc', $limit);
    }

    /**
     * 获取随机文章
     * @param int $limit 返回数量限制
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function random($limit = 10, $fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'])
    {
        $articles = self::all($fields);
        
        if (empty($articles)) {
            return [];
        }
        
        // 随机打乱数组
        shuffle($articles);

        // 返回指定数量的文章
        return array_slice($articles, 0, $limit);
    }

    /**
     * 搜索文章
     * @param string $keyword 搜索关键词
     * @param array $fields 指定返回字段
     * @param int $limit 返回数量限制
     * @return array
     */
    public static function search($keyword, $fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'], $limit = 0)
    {
        $result = [];

        try {
            $db = \Typecho_Db::get();

            // 构建搜索查询
            $select = $db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'c.status')
                ->from('table.contents AS c')
                ->where('c.type = ?', 'post')
                ->where('c.status = ?', 'publish')
                ->where('c.title LIKE ?', '%' . $keyword . '%')
                ->orWhere('c.text LIKE ?', '%' . $keyword . '%')
                ->order('c.created', \Typecho_Db::SORT_DESC);

            if ($limit > 0) {
                $select->limit($limit);
            }

            $rows = $db->fetchAll($select);

            if (empty($rows)) {
                return [];
            }

            $cids = array_column($rows, 'cid');

            // 使用批量查询方法
            $allCustomFields = self::batchGetCustomFields($cids);
            $authors = self::batchGetAuthors(array_column($rows, 'authorId'));

            foreach ($rows as $row) {
                $cid = $row['cid'];
                $customFields = $allCustomFields[$cid] ?? [];

                // 只获取 article_type 为 article 的文章
                $articleType = isset($customFields['article_type']) ? $customFields['article_type'] : 'article';
                if ($articleType !== 'article') {
                    continue;
                }

                $item = [];

                if (in_array('cid', $fields)) $item['cid'] = $cid;
                if (in_array('title', $fields)) $item['title'] = $row['title'];
                if (in_array('slug', $fields)) $item['slug'] = $row['slug'];
                if (in_array('created', $fields)) $item['created'] = $row['created'];
                if (in_array('modified', $fields)) $item['modified'] = $row['modified'];
                if (in_array('authorId', $fields)) $item['authorId'] = $row['authorId'];
                if (in_array('author', $fields)) $item['author'] = $authors[$row['authorId']] ?? '';
                if (in_array('text', $fields)) $item['text'] = $row['text'];
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $row['commentsNum'];
                if (in_array('order', $fields)) $item['order'] = $row['order'];

                if (in_array('views', $fields)) {
                    $item['views'] = isset($customFields['article_views']) ? intval($customFields['article_views']) : 0;
                }

                if (in_array('url', $fields)) {
                    $item['url'] = self::getPermalinkByCid($row['cid'], $row['slug'], $row['created']);
                }

                if (in_array('excerpt', $fields)) {
                    $text = strip_tags($row['text']);
                    $item['excerpt'] = mb_substr($text, 0, 200, 'UTF-8') . '...';
                }

                $result[] = $item;
            }

        } catch (Exception $e) {
            return [];
        }

        return $result;
    }

    /* ==========================
     * 相邻文章
     * ========================== */

    /**
     * 获取上一篇文章
     * @param int|string $cid 文章 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function previous($cid, $fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'])
    {
        $current = self::get($cid, ['created']);
        if (!$current) {
            return null;
        }

        $articles = self::all($fields, 'created', 'desc');
        
        foreach ($articles as $index => $article) {
            if ($article['cid'] == $cid && isset($articles[$index + 1])) {
                return $articles[$index + 1];
            }
        }
        
        return null;
    }

    /**
     * 获取下一篇文章
     * @param int|string $cid 文章 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function next($cid, $fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'])
    {
        $current = self::get($cid, ['created']);
        if (!$current) {
            return null;
        }

        $articles = self::all($fields, 'created', 'desc');
        
        foreach ($articles as $index => $article) {
            if ($article['cid'] == $cid && isset($articles[$index - 1])) {
                return $articles[$index - 1];
            }
        }
        
        return null;
    }

    /* ==========================
     * 图片提取
     * ========================== */

    /**
     * 从文章内容中提取第一张图片
     * @param int|string $cid 文章 ID 或缩略名
     * @return string 图片 URL，如果没有则返回空字符串
     */
    public static function firstImage($cid)
    {
        $article = self::get($cid, ['text']);
        if (!$article) {
            return '';
        }

        $text = $article['text'];
        
        // 匹配 markdown 格式的图片
        if (preg_match('/!\[.*?\]\((.*?)\)/', $text, $matches)) {
            return $matches[1];
        }
        
        // 匹配 HTML 格式的图片
        if (preg_match('/<img.*?src=["\'](.*?)["\'].*?>/i', $text, $matches)) {
            return $matches[1];
        }
        
        return '';
    }

    /**
     * 从文章内容中提取所有图片
     * @param int|string $cid 文章 ID 或缩略名
     * @return array 图片 URL 数组
     */
    public static function allImages($cid)
    {
        $article = self::get($cid, ['text']);
        if (!$article) {
            return [];
        }

        $text = $article['text'];
        $images = [];
        
        // 匹配 markdown 格式的图片
        if (preg_match_all('/!\[.*?\]\((.*?)\)/', $text, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        
        // 匹配 HTML 格式的图片
        if (preg_match_all('/<img.*?src=["\'](.*?)["\'].*?>/i', $text, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        
        return array_unique($images);
    }

    /* ==========================
     * 统一文章查询系统
     * ========================== */

    /**
     * 统一的文章查询方法
     * @param array $params 查询参数
     *   - filter_type: 筛选类型 ('all', 'category', 'tag', 'search', 'author', 'date')
     *   - filter_id: 筛选 ID (分类/标签 ID)
     *   - keywords: 搜索关键词
     *   - order: 排序字段 ('created', 'modified', 'views', 'commentsNum', 'likes')
     *   - sort: 排序方向 ('asc', 'desc')
     *   - page: 当前页码
     *   - per_page: 每页数量
     * @return array 查询结果
     */
    public static function queryArticles($params = [])
    {
        echo "<!-- [Article] queryArticles 开始，参数: " . json_encode($params, JSON_UNESCAPED_UNICODE) . " -->\n";

        $defaults = [
            'filter_type' => 'all',
            'filter_id' => 0,
            'keywords' => '',
            'order' => 'created',
            'sort' => 'desc',
            'page' => 1,
            'per_page' => 10
        ];

        $params = array_merge($defaults, $params);
        $params['page'] = max(1, intval($params['page']));
        $offset = ($params['page'] - 1) * $params['per_page'];

        echo "<!-- [Article] 合并后参数: " . json_encode($params, JSON_UNESCAPED_UNICODE) . " -->\n";

        try {
            $db = \Typecho_Db::get();
            echo "<!-- [Article] 数据库连接成功 -->\n";

            // 排序映射 - 支持多种排序字段
            $orderMap = [
                'created' => 'c.created',
                'modified' => 'c.modified',
                'views' => 'article_views',
                'commentsNum' => 'c.commentsNum',
                'comments' => 'c.commentsNum',
                'likes' => 'article_likes'
            ];

            $sortField = isset($orderMap[$params['order']]) ? $orderMap[$params['order']] : 'c.created';
            $sortDir = strtolower($params['sort']) === 'desc' ? \Typecho_Db::SORT_DESC : \Typecho_Db::SORT_ASC;

            // 构建基础查询
            $select = $db->select('c.cid', 'c.title', 'c.slug', 'c.created', 'c.modified', 'c.authorId', 'c.text', 'c.commentsNum', 'c.order', 'c.type', 'c.status')
                ->from('table.contents AS c')
                ->where('c.type = ?', 'post')
                ->where('c.status = ?', 'publish');

            echo "<!-- [Article] 基础查询构建完成 -->\n";

            // 添加筛选条件
            switch ($params['filter_type']) {
                case 'category':
                    if ($params['filter_id'] > 0) {
                        $select->join('table.relationships AS r', 'c.cid = r.cid', \Typecho_Db::LEFT_JOIN)
                            ->where('r.mid = ?', $params['filter_id']);
                        echo "<!-- [Article] 添加分类筛选: {$params['filter_id']} -->\n";
                    }
                    break;
                case 'tag':
                    if ($params['filter_id'] > 0) {
                        $select->join('table.relationships AS r', 'c.cid = r.cid', \Typecho_Db::LEFT_JOIN)
                            ->where('r.mid = ?', $params['filter_id']);
                        echo "<!-- [Article] 添加标签筛选: {$params['filter_id']} -->\n";
                    }
                    break;
                case 'search':
                    if (!empty($params['keywords'])) {
                        $keyword = '%' . $params['keywords'] . '%';
                        $select->where('c.title LIKE ?', $keyword)
                            ->orWhere('c.text LIKE ?', $keyword);
                        echo "<!-- [Article] 添加搜索筛选: {$params['keywords']} -->\n";
                    }
                    break;
                case 'author':
                    if ($params['filter_id'] > 0) {
                        $select->where('c.authorId = ?', $params['filter_id']);
                        echo "<!-- [Article] 添加作者筛选: {$params['filter_id']} -->\n";
                    }
                    break;
                case 'date':
                    // 日期筛选逻辑可以根据需要扩展
                    break;
            }

            // 获取总数 - 重新构建查询而不是克隆
            $totalSelect = $db->select('COUNT(DISTINCT c.cid) as count')
                ->from('table.contents AS c')
                ->where('c.type = ?', 'post')
                ->where('c.status = ?', 'publish');

            // 添加相同的筛选条件
            switch ($params['filter_type']) {
                case 'category':
                    if ($params['filter_id'] > 0) {
                        $totalSelect->join('table.relationships AS r', 'c.cid = r.cid', \Typecho_Db::LEFT_JOIN)
                            ->where('r.mid = ?', $params['filter_id']);
                    }
                    break;
                case 'tag':
                    if ($params['filter_id'] > 0) {
                        $totalSelect->join('table.relationships AS r', 'c.cid = r.cid', \Typecho_Db::LEFT_JOIN)
                            ->where('r.mid = ?', $params['filter_id']);
                    }
                    break;
                case 'search':
                    if (!empty($params['keywords'])) {
                        $keyword = '%' . $params['keywords'] . '%';
                        $totalSelect->where('c.title LIKE ?', $keyword)
                            ->orWhere('c.text LIKE ?', $keyword);
                    }
                    break;
                case 'author':
                    if ($params['filter_id'] > 0) {
                        $totalSelect->where('c.authorId = ?', $params['filter_id']);
                    }
                    break;
            }

            $countResult = $db->fetchRow($totalSelect);
            $total = intval($countResult['count'] ?? 0);
            $totalPages = ceil($total / $params['per_page']);

            echo "<!-- [Article] 总数查询结果: total={$total}, totalPages={$totalPages} -->\n";

            // 如果按自定义字段排序，需要 JOIN fields 表
            if ($params['order'] === 'views' || $params['order'] === 'likes') {
                $fieldName = $params['order'] === 'views' ? 'article_views' : 'article_likes';
                echo "<!-- [Article] 按自定义字段排序: {$fieldName} -->\n";

                // 获取表前缀和适配器
                $prefix = $db->getPrefix();
                $adapter = $db->getAdapter();

                // 构建原生 SQL 查询，手动转义参数
                $sql = "SELECT c.cid, c.title, c.slug, c.created, c.modified, c.authorId, c.text, c.commentsNum, c.order, c.type, c.status
                        FROM {$prefix}contents AS c
                        LEFT JOIN {$prefix}fields AS f ON c.cid = f.cid AND f.name = " . $adapter->quoteValue($fieldName) . "
                        WHERE c.type = " . $adapter->quoteValue('post') . " AND c.status = " . $adapter->quoteValue('publish');

                // 添加筛选条件
                switch ($params['filter_type']) {
                    case 'category':
                        if ($params['filter_id'] > 0) {
                            $sql .= " AND EXISTS (SELECT 1 FROM {$prefix}relationships AS r WHERE r.cid = c.cid AND r.mid = " . $adapter->quoteValue($params['filter_id']) . ")";
                        }
                        break;
                    case 'tag':
                        if ($params['filter_id'] > 0) {
                            $sql .= " AND EXISTS (SELECT 1 FROM {$prefix}relationships AS r WHERE r.cid = c.cid AND r.mid = " . $adapter->quoteValue($params['filter_id']) . ")";
                        }
                        break;
                    case 'search':
                        if (!empty($params['keywords'])) {
                            $keyword = '%' . $params['keywords'] . '%';
                            $sql .= " AND (c.title LIKE " . $adapter->quoteValue($keyword) . " OR c.text LIKE " . $adapter->quoteValue($keyword) . ")";
                        }
                        break;
                    case 'author':
                        if ($params['filter_id'] > 0) {
                            $sql .= " AND c.authorId = " . $adapter->quoteValue($params['filter_id']);
                        }
                        break;
                }

                // 添加排序
                $sql .= " ORDER BY CAST(f.str_value AS UNSIGNED) {$sortDir}";

                // 添加 limit 和 offset
                $sql .= " LIMIT " . intval($params['per_page']) . " OFFSET " . intval($offset);

                echo "<!-- [Article] 最终查询: " . $sql . " -->\n";

                // 执行查询
                $resource = $db->query($sql, \Typecho_Db::READ);
                $rows = $db->fetchAll($resource);
            } else {
                $select->order($sortField, $sortDir);

                // 统一添加 limit 和 offset
                $select->limit($params['per_page'])->offset($offset);

                echo "<!-- [Article] 最终查询: " . $select->__toString() . " -->\n";

                // 执行查询
                $rows = $db->fetchAll($select);
            }

            echo "<!-- [Article] 查询执行完成，返回 " . count($rows) . " 条记录 -->\n";

            if (empty($rows)) {
                echo "<!-- [Article] 查询结果为空，返回空结果 -->\n";
                return self::buildQueryResult($params, 0, 0, []);
            }

            // 批量获取数据
            $cids = array_column($rows, 'cid');
            echo "<!-- [Article] 批量获取自定义字段，CID 数量: " . count($cids) . " -->\n";
            $customFields = self::batchGetCustomFields($cids);
            echo "<!-- [Article] 自定义字段获取完成 -->\n";

            $authors = self::batchGetAuthors(array_column($rows, 'authorId'));
            echo "<!-- [Article] 作者信息获取完成 -->\n";

            // 格式化文章数据
            $formattedArticles = [];
            foreach ($rows as $row) {
                $cid = $row['cid'];
                $fields = $customFields[$cid] ?? [];

                // 只获取 article_type 为 article 的文章
                $articleType = isset($fields['article_type']) ? $fields['article_type'] : 'article';
                if ($articleType !== 'article') {
                    echo "<!-- [Article] 跳过文章 {$cid}，类型: {$articleType} -->\n";
                    continue;
                }

                $formattedArticles[] = self::formatArticleForList($row, $fields, $authors);
            }

            echo "<!-- [Article] 格式化完成，最终文章数量: " . count($formattedArticles) . " -->\n";

            return self::buildQueryResult($params, $total, $totalPages, $formattedArticles);

        } catch (Exception $e) {
            echo "<!-- [Article] 查询异常: " . $e->getMessage() . " -->\n";
            return self::buildQueryResult($params, 0, 0, []);
        }
    }

    /**
     * 批量获取自定义字段
     * @param array $cids 文章 ID 数组
     * @return array 自定义字段数组
     */
    private static function batchGetCustomFields($cids)
    {
        $result = [];

        if (empty($cids)) {
            echo "<!-- [Article] batchGetCustomFields: CID 数组为空 -->\n";
            return $result;
        }

        echo "<!-- [Article] batchGetCustomFields: 开始查询 " . count($cids) . " 个文章的自定义字段 -->\n";

        try {
            $db = \Typecho_Db::get();
            $fieldRows = $db->fetchAll($db->select('cid', 'name', 'str_value', 'int_value', 'float_value')
                ->from('table.fields')
                ->where('cid IN ?', $cids));

            echo "<!-- [Article] batchGetCustomFields: 查询到 " . count($fieldRows) . " 条字段记录 -->\n";

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
                    if (!isset($result[$cid])) {
                        $result[$cid] = [];
                    }
                    $result[$cid][$fieldName] = $fieldValue;
                }
            }

            echo "<!-- [Article] batchGetCustomFields: 完成，" . count($result) . " 个文章有自定义字段 -->\n";
        } catch (Exception $e) {
            echo "<!-- [Article] batchGetCustomFields 异常: " . $e->getMessage() . " -->\n";
        }

        return $result;
    }

    /**
     * 批量获取作者信息
     * @param array $authorIds 作者 ID 数组
     * @return array 作者信息数组
     */
    private static function batchGetAuthors($authorIds)
    {
        $result = [];

        if (empty($authorIds)) {
            echo "<!-- [Article] batchGetAuthors: 作者ID数组为空 -->\n";
            return $result;
        }

        echo "<!-- [Article] batchGetAuthors: 开始查询 " . count($authorIds) . " 个作者的信息 -->\n";

        try {
            $db = \Typecho_Db::get();
            $authorRows = $db->fetchAll($db->select('uid', 'screenName')
                ->from('table.users')
                ->where('uid IN ?', array_unique($authorIds)));

            echo "<!-- [Article] batchGetAuthors: 查询到 " . count($authorRows) . " 个作者 -->\n";

            foreach ($authorRows as $authorRow) {
                $result[$authorRow['uid']] = $authorRow['screenName'];
            }
        } catch (Exception $e) {
            echo "<!-- [Article] batchGetAuthors 异常: " . $e->getMessage() . " -->\n";
        }

        return $result;
    }

    /**
     * 格式化文章数据（用于列表展示）
     * @param array $row 数据库行
     * @param array $customFields 自定义字段
     * @param array $authors 作者信息
     * @return array 格式化后的文章数据
     */
    private static function formatArticleForList($row, $customFields, $authors)
    {
        echo "<!-- [Article] formatArticleForList: 开始格式化文章 {$row['cid']} ({$row['title']}) -->\n";

        $articleArray = [
            'cid' => $row['cid'],
            'title' => $row['title'],
            'slug' => $row['slug'],
            'created' => $row['created'],
            'modified' => $row['modified'],
            'authorId' => $row['authorId'],
            'author' => $authors[$row['authorId']] ?? '',
            'text' => $row['text'],
            'commentsNum' => $row['commentsNum'],
            'order' => $row['order'],
            'fields' => $customFields
        ];

        echo "<!-- [Article] formatArticleForList: 自定义字段数量: " . count($customFields) . " -->\n";

        // 获取缩略图
        $thumbnail = getArticleThumbnail($articleArray);
        if (empty($thumbnail)) {
            $thumbnail = self::firstImage($row['cid']);
        }
        if (empty($thumbnail)) {
            $thumbnail = Get::Assets('assets/images/cover/cover1.jpg');
        } else {
            $thumbnail = Get::resolveUri($thumbnail);
        }

        // 获取浏览量
        $views = getArticleViews($articleArray);
        if (empty($views)) {
            $views = isset($customFields['article_views']) ? intval($customFields['article_views']) : 0;
        }

        // 获取点赞数
        $likes = isset($customFields['article_likes']) ? intval($customFields['article_likes']) : 0;

        // 获取摘要
        $excerpt = getArticleExcerpt($articleArray, 200);
        if (empty($excerpt)) {
            $text = strip_tags($row['text']);
            $excerpt = mb_substr($text, 0, 200, 'UTF-8');
            if (mb_strlen($text, 'UTF-8') > 200) {
                $excerpt .= '...';
            }
        }

        // 生成 URL - 使用 Typecho Widget 获取正确的 permalink
        try {
            $widget = \Widget\Contents\Post\Recent::alloc('permalink_' . $row['cid']);
            // 使用 push 方法初始化 Widget
            $widget->push([
                'cid' => $row['cid'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'created' => $row['created'],
                'modified' => $row['modified'],
                'authorId' => $row['authorId'],
                'type' => $row['type'],
                'status' => $row['status'],
                'commentsNum' => $row['commentsNum'],
                'order' => $row['order'],
                'text' => $row['text'],
                'password' => '',
                'allowComment' => '1',
                'allowPing' => '1',
                'allowFeed' => '1',
                'parent' => '0'
            ]);
            $url = $widget->permalink;
        } catch (Exception $e) {
            // 如果 Widget 失败，使用备用方法
            $url = self::getPermalinkByCid($row['cid'], $row['slug'], $row['created']);
        }

        echo "<!-- [Article] formatArticleForList: 格式化完成，浏览量={$views}, 点赞数={$likes} -->\n";

        return [
            'title' => $row['title'],
            'excerpt' => $excerpt,
            'thumbnail' => $thumbnail,
            'views' => $views,
            'comments' => $row['commentsNum'] ?? 0,
            'likes' => $likes,
            'date' => date('Y-m-d', $row['created']),
            'url' => $url
        ];
    }

    /**
     * 构建查询结果
     * @param array $params 查询参数
     * @param int $total 总数
     * @param int $totalPages 总页数
     * @param array $articles 文章列表
     * @return array 查询结果
     */
    private static function buildQueryResult($params, $total, $totalPages, $articles)
    {
        return [
            'sort' => $params['order'],
            'layout' => 'list',
            'p' => $params['page'],
            'total' => $total,
            'per_page' => $params['per_page'],
            'total_pages' => $totalPages,
            'articles' => $articles
        ];
    }

    /**
     * 通过 CID 获取文章永久链接（优化版本）
     * @param int $cid 文章 ID
     * @param string $slug 文章缩略名
     * @param int $created 创建时间
     * @return string 文章 URL
     */
    private static function getPermalinkByCid($cid, $slug = '', $created = 0)
    {
        echo "<!-- [getPermalinkByCid] cid={$cid}, slug='{$slug}', created={$created} -->\n";

        try {
            // 使用 Typecho 的 Router 生成 URL
            $path = \Typecho\Router::url('post', [
                'cid' => $cid
            ]);

            $options = \Typecho_Widget::widget('Widget_Options');
            $url = \Typecho\Common::url($path, $options->index);

            echo "<!-- [getPermalinkByCid] 使用 Router::url 生成 URL: {$url} -->\n";
            return $url;
        } catch (Exception $e) {
            echo "<!-- [getPermalinkByCid] 异常: " . $e->getMessage() . "，使用备用 URL -->\n";
            // 备用方案
            try {
                $options = \Typecho_Widget::widget('Widget_Options');
                return $options->siteUrl . '/archives/' . $cid . '/';
            } catch (Exception $e2) {
                return '/archives/' . $cid . '/';
            }
        }
    }

    /* ==========================
     * 文章列表组件数据
     * ========================== */

    /**
     * 获取文章列表组件数据（首页）
     * @return array
     */
    public static function getListData()
    {
        echo "<!-- [Article] getListData 开始 -->\n";

        $sort = Get::queryParam('sort', 'date');
        $layout = Get::queryParam('layout', 'list');
        $currentPage = max(1, intval(Get::queryParam('p', '1')));

        echo "<!-- [Article] URL参数: sort={$sort}, layout={$layout}, p={$currentPage} -->\n";

        // 排序映射
        $orderMap = [
            'date' => 'created',
            'views' => 'views',
            'comments' => 'commentsNum',
            'likes' => 'likes'
        ];

        $order = isset($orderMap[$sort]) ? $orderMap[$sort] : 'created';

        echo "<!-- [Article] 排序字段映射: {$sort} -> {$order} -->\n";

        $result = self::queryArticles([
            'filter_type' => 'all',
            'order' => $order,
            'sort' => 'desc',
            'page' => $currentPage,
            'per_page' => 10
        ]);

        // 返回原始的 sort 参数，用于标签页高亮
        $result['sort'] = $sort;
        $result['layout'] = $layout;

        echo "<!-- [Article] getListData 结束，返回文章数量: " . count($result['articles']) . " -->\n";

        return $result;
    }

    /* ==========================
     * 归档文章列表
     * ========================== */

    /**
     * 获取归档页面文章列表数据
     * @param object $archive 当前 Archive 对象
     * @param string $archive_type 归档类型 ('category', 'tag', 'search', 'author', 'date', 'archive')
     * @param int $category_mid 分类 ID
     * @param string $keywords 搜索关键词
     * @return array
     */
    public static function getArchiveListData($archive = null, $archive_type = 'archive', $category_mid = 0, $keywords = '')
    {
        echo "<!-- [Article] getArchiveListData 开始，类型: {$archive_type} -->\n";

        $sort = Get::queryParam('sort', 'date');
        $layout = Get::queryParam('layout', 'list');
        $currentPage = max(1, intval(Get::queryParam('p', '1')));

        echo "<!-- [Article] getArchiveListData 参数: sort={$sort}, layout={$layout}, p={$currentPage}, keywords={$keywords}, category_mid={$category_mid} -->\n";

        // 排序映射
        $orderMap = [
            'date' => 'created',
            'views' => 'views',
            'comments' => 'commentsNum',
            'likes' => 'likes'
        ];

        $order = isset($orderMap[$sort]) ? $orderMap[$sort] : 'created';

        // 确定筛选类型和 ID
        $filterType = $archive_type;
        $filterId = 0;

        switch ($archive_type) {
            case 'category':
                $filterId = $category_mid > 0 ? $category_mid : self::getArchiveMid($archive, 'category');
                $filterType = 'category';
                echo "<!-- [Article] getArchiveListData 分类筛选 ID: {$filterId} -->\n";
                break;
            case 'tag':
                $filterId = self::getArchiveMid($archive, 'tag');
                $filterType = 'tag';
                echo "<!-- [Article] getArchiveListData 标签筛选 ID: {$filterId} -->\n";
                break;
            case 'search':
                // 搜索使用 keywords 参数
                echo "<!-- [Article] getArchiveListData 搜索关键词: {$keywords} -->\n";
                break;
            case 'author':
                $filterId = self::getArchiveMid($archive, 'author');
                $filterType = 'author';
                echo "<!-- [Article] getArchiveListData 作者筛选 ID: {$filterId} -->\n";
                break;
            default:
                $filterType = 'all';
                echo "<!-- [Article] getArchiveListData 全部文章 -->\n";
                break;
        }

        $result = self::queryArticles([
            'filter_type' => $filterType,
            'filter_id' => $filterId,
            'keywords' => $keywords,
            'order' => $order,
            'sort' => 'desc',
            'page' => $currentPage,
            'per_page' => 10
        ]);

        // 返回原始的 sort 参数，用于标签页高亮
        $result['sort'] = $sort;
        $result['layout'] = $layout;

        echo "<!-- [Article] getArchiveListData 结束，返回文章数量: " . count($result['articles']) . " -->\n";

        return $result;
    }

    /**
     * 从 Archive 对象获取 mid
     * @param object $archive Archive 对象
     * @param string $type 类型 ('category', 'tag', 'author')
     * @return int
     */
    private static function getArchiveMid($archive, $type)
    {
        if (!$archive) {
            return 0;
        }

        // 直接从属性获取
        if (property_exists($archive, 'mid') && $archive->mid) {
            return intval($archive->mid);
        }

        // 从数组获取
        if ($type === 'category' && isset($archive->categories) && is_array($archive->categories) && !empty($archive->categories)) {
            return intval($archive->categories[0]['mid']);
        }

        if ($type === 'author' && property_exists($archive, 'authorId') && $archive->authorId) {
            return intval($archive->authorId);
        }

        return 0;
    }

    /* ==========================
     * 文章阅读数据
     * ========================== */

    /**
     * 获取文章阅读器数据
     * @param object $archive Archive 对象
     * @return array
     */
    public static function getReaderData($archive = null)
    {
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
                    ->where('name = ?', 'article_thumbnail')
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
}
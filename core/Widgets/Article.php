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
        $cids = [];
        
        try {
            $widget = \Widget\Contents\Post\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999) . '&page=' . ceil(($offset + 1) / ($limit > 0 ? $limit : 1)));
            
            // 遍历文章，收集所有 CID
            while ($widget->next()) {
                $item = [];
                
                // 只返回指定字段
                if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                if (in_array('title', $fields)) $item['title'] = $widget->title;
                if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                if (in_array('created', $fields)) $item['created'] = $widget->created;
                if (in_array('modified', $fields)) $item['modified'] = $widget->modified;
                if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                if (in_array('author', $fields)) {
                    // author 可能是对象或字符串
                    if (is_object($widget->author)) {
                        $item['author'] = $widget->author->name ?? '';
                    } else {
                        $item['author'] = $widget->author ?? '';
                    }
                }
                if (in_array('text', $fields)) $item['text'] = $widget->text;
                if (in_array('views', $fields)) $item['views'] = isset($widget->views) ? $widget->views : 0;
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $widget->commentsNum;
                if (in_array('order', $fields)) $item['order'] = $widget->order;
                
                // 添加 URL
                if (in_array('url', $fields)) $item['url'] = $widget->permalink;
                
                // 添加摘要
                if (in_array('excerpt', $fields)) $item['excerpt'] = $widget->excerpt(200, '...');
                
                // 收集 CID 用于后续批量查询自定义字段
                $cids[] = $widget->cid;
                
                $result[] = $item;
            }
            
            // 批量获取所有文章的自定义字段
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
                    
                    // 根据不同的值字段判断类型并获取值
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
            
            // 将自定义字段添加到结果中
            foreach ($result as &$item) {
                $cid = $item['cid'];
                
                // 添加自定义字段
                if (in_array('fields', $fields)) {
                    $item['fields'] = $allCustomFields[$cid] ?? [];
                }
                
                // 添加点赞数（从自定义字段获取）
                if (in_array('likes', $fields)) {
                    $item['likes'] = isset($allCustomFields[$cid]['article_likes']) ? intval($allCustomFields[$cid]['article_likes']) : 0;
                }
                
                // 更新浏览量（从自定义字段获取，如果有的话）
                if (in_array('views', $fields) && isset($allCustomFields[$cid]['article_views'])) {
                    $item['views'] = intval($allCustomFields[$cid]['article_views']);
                }
            }
            unset($item);
            
        } catch (Exception $e) {
            return [];
        }

        // 排序映射
        $orderMap = [
            'created' => 'created',
            'modified' => 'modified',
            'views' => 'views',
            'commentsNum' => 'commentsNum',
            'likes' => 'likes',
            'mid' => 'cid',
            'order' => 'order'
        ];
        
        $sortField = isset($orderMap[$order]) ? $orderMap[$order] : 'created';
        $sortDir = strtolower($sort) === 'desc' ? SORT_DESC : SORT_ASC;

        // 排序
        if (!empty($result)) {
            usort($result, function($a, $b) use ($sortField, $sortDir) {
                $valA = isset($a[$sortField]) ? $a[$sortField] : 0;
                $valB = isset($b[$sortField]) ? $b[$sortField] : 0;
                
                if ($sortDir === SORT_DESC) {
                    return $valB <=> $valA;
                } else {
                    return $valA <=> $valB;
                }
            });
        }

        // 应用偏移量和限制
        if ($offset > 0 || $limit > 0) {
            $result = array_slice($result, $offset, $limit > 0 ? $limit : null);
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
                if (in_array('views', $fields)) $item['views'] = isset($row['views']) ? $row['views'] : 0;
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $row['commentsNum'];
                if (in_array('order', $fields)) $item['order'] = $row['order'];
                if (in_array('url', $fields)) {
                    // 使用 Typecho Widget 获取正确的 permalink
                    $widget = \Widget\Contents\Post\Recent::alloc();
                    $widget->row = $row;
                    $widget->cid = $row['cid'];
                    $widget->title = $row['title'];
                    $widget->slug = $row['slug'];
                    $widget->created = $row['created'];
                    $widget->modified = $row['modified'];
                    $widget->authorId = $row['authorId'];
                    $widget->type = $row['type'];
                    $widget->status = $row['status'];
                    $widget->commentsNum = $row['commentsNum'];
                    $widget->order = $row['order'];
                    $widget->template = $row['template'];
                    $widget->password = $row['password'];
                    $widget->allowComment = $row['allowComment'];
                    $widget->allowPing = $row['allowPing'];
                    $widget->allowFeed = $row['allowFeed'];
                    $widget->parent = $row['parent'];
                    $widget->text = $row['text'];
                    $item['url'] = $widget->permalink;
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
            $widget = \Widget\Contents\Post\Category::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                $item = [];
                
                if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                if (in_array('title', $fields)) $item['title'] = $widget->title;
                if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                if (in_array('created', $fields)) $item['created'] = $widget->created;
                if (in_array('modified', $fields)) $item['modified'] = $widget->modified;
                if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                if (in_array('author', $fields)) {
                    // author 可能是对象或字符串
                    if (is_object($widget->author)) {
                        $item['author'] = $widget->author->name ?? '';
                    } else {
                        $item['author'] = $widget->author ?? '';
                    }
                }
                if (in_array('text', $fields)) $item['text'] = $widget->text;
                if (in_array('views', $fields)) $item['views'] = isset($widget->views) ? $widget->views : 0;
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $widget->commentsNum;
                if (in_array('order', $fields)) $item['order'] = $widget->order;
                if (in_array('url', $fields)) $item['url'] = $widget->permalink;
                if (in_array('excerpt', $fields)) $item['excerpt'] = $widget->excerpt(200, '...');
                
                $result[] = $item;
            }
        } catch (Exception $e) {
            return [];
        }

        // 排序
        $orderMap = [
            'created' => 'created',
            'modified' => 'modified',
            'views' => 'views',
            'commentsNum' => 'commentsNum',
            'mid' => 'cid'
        ];
        
        $sortField = isset($orderMap[$order]) ? $orderMap[$order] : 'created';
        $sortDir = strtolower($sort) === 'desc' ? SORT_DESC : SORT_ASC;

        if (!empty($result)) {
            usort($result, function($a, $b) use ($sortField, $sortDir) {
                $valA = isset($a[$sortField]) ? $a[$sortField] : 0;
                $valB = isset($b[$sortField]) ? $b[$sortField] : 0;
                
                if ($sortDir === SORT_DESC) {
                    return $valB <=> $valA;
                } else {
                    return $valA <=> $valB;
                }
            });
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
            $widget = \Widget\Contents\Post\Tag::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                $item = [];
                
                if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                if (in_array('title', $fields)) $item['title'] = $widget->title;
                if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                if (in_array('created', $fields)) $item['created'] = $widget->created;
                if (in_array('modified', $fields)) $item['modified'] = $widget->modified;
                if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                if (in_array('author', $fields)) {
                    // author 可能是对象或字符串
                    if (is_object($widget->author)) {
                        $item['author'] = $widget->author->name ?? '';
                    } else {
                        $item['author'] = $widget->author ?? '';
                    }
                }
                if (in_array('text', $fields)) $item['text'] = $widget->text;
                if (in_array('views', $fields)) $item['views'] = isset($widget->views) ? $widget->views : 0;
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $widget->commentsNum;
                if (in_array('order', $fields)) $item['order'] = $widget->order;
                if (in_array('url', $fields)) $item['url'] = $widget->permalink;
                if (in_array('excerpt', $fields)) $item['excerpt'] = $widget->excerpt(200, '...');
                
                $result[] = $item;
            }
        } catch (Exception $e) {
            return [];
        }

        // 排序
        $orderMap = [
            'created' => 'created',
            'modified' => 'modified',
            'views' => 'views',
            'commentsNum' => 'commentsNum',
            'mid' => 'cid'
        ];
        
        $sortField = isset($orderMap[$order]) ? $orderMap[$order] : 'created';
        $sortDir = strtolower($sort) === 'desc' ? SORT_DESC : SORT_ASC;

        if (!empty($result)) {
            usort($result, function($a, $b) use ($sortField, $sortDir) {
                $valA = isset($a[$sortField]) ? $a[$sortField] : 0;
                $valB = isset($b[$sortField]) ? $b[$sortField] : 0;
                
                if ($sortDir === SORT_DESC) {
                    return $valB <=> $valA;
                } else {
                    return $valA <=> $valB;
                }
            });
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
     * 通过 CID 获取文章永久链接
     * @param int $cid 文章 ID
     * @return string 文章 URL
     */
    private static function getPermalinkByCid($cid)
    {
        try {
            $db = \Typecho_Db::get();
            $row = $db->fetchRow($db->select('cid', 'slug', 'created')
                ->from('table.contents')
                ->where('cid = ?', $cid)
                ->where('type = ?', 'post')
                ->limit(1));
            
            if ($row) {
                $options = \Typecho_Widget::widget('Widget_Options');
                $slug = $row['slug'];
                
                if (!empty($slug)) {
                    // 使用 slug 生成 URL
                    $permalink = $options->siteUrl . $slug . '/';
                } else {
                    // 使用 cid 生成 URL
                    $permalink = $options->siteUrl . 'archives/' . $cid . '/';
                }
                
                return $permalink;
            }
        } catch (Exception $e) {
            // 出错时返回默认 URL
            return '/archives/' . $cid . '/';
        }
        
        return '/archives/' . $cid . '/';
    }

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
        try {
            $widget = \Widget\Contents\Post\Recent::alloc('pageSize=999999');
            $count = 0;
            
            while ($widget->next()) {
                $count++;
            }
            
            return $count;
        } catch (Exception $e) {
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
            $widget = \Widget\Contents\Post\Search::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                $title = $widget->title;
                $text = strip_tags($widget->text);
                
                // 在标题和内容中搜索关键词
                if (stripos($title, $keyword) !== false || stripos($text, $keyword) !== false) {
                    $item = [];
                    
                    if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                    if (in_array('title', $fields)) $item['title'] = $widget->title;
                    if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                    if (in_array('created', $fields)) $item['created'] = $widget->created;
                    if (in_array('modified', $fields)) $item['modified'] = $widget->modified;
                    if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                    if (in_array('author', $fields)) {
                        // author 可能是对象或字符串
                        if (is_object($widget->author)) {
                            $item['author'] = $widget->author->name ?? '';
                        } else {
                            $item['author'] = $widget->author ?? '';
                        }
                    }
                    if (in_array('text', $fields)) $item['text'] = $widget->text;
                    if (in_array('views', $fields)) $item['views'] = isset($widget->views) ? $widget->views : 0;
                    if (in_array('commentsNum', $fields)) $item['commentsNum'] = $widget->commentsNum;
                    if (in_array('order', $fields)) $item['order'] = $widget->order;
                    if (in_array('url', $fields)) $item['url'] = $widget->permalink;
                    if (in_array('excerpt', $fields)) $item['excerpt'] = $widget->excerpt(200, '...');
                    
                    $result[] = $item;
                }
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
     * 文章列表组件数据
     * ========================== */

    /**
     * 获取文章列表组件数据（首页）
     * @return array
     */
    public static function getListData()
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
        $total = self::total();
        $totalPages = ceil($total / $perPage);

        // 计算偏移量
        $offset = ($currentPage - 1) * $perPage;

        // 获取文章列表
        $articles = self::all(
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
                $thumbnail = self::firstImage($article['cid']);
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
     * 归档文章列表
     * ========================== */

    /**
     * 获取归档页面文章列表数据
     * @param object $archive 当前 Archive 对象
     * @param string $archive_type 归档类型 ('category', 'tag', 'search', 'author', 'date', 'archive')
     * @return array
     */
    public static function getArchiveListData($archive = null, $archive_type = 'archive')
    {
        // 获取分页和排序参数
        $sort = Get::queryParam('sort', 'date');
        $layout = Get::queryParam('layout', 'list');
        $currentPage = max(1, intval(Get::queryParam('p', '1')));
        $perPage = 10;

        try {
            // 使用 Typecho 原生 Widget 获取文章列表
            $widget = self::getArchiveWidget($archive_type);
            if (!$widget) {
                return self::emptyResult($sort, $layout, $currentPage, $perPage);
            }

            // 设置分页参数
            $widget->parameter->pageSize = $perPage;
            $widget->setCurrentPage($currentPage);

            // 获取文章总数
            $total = $widget->total ?? 0;
            $totalPages = ceil($total / $perPage);

            // 收集文章数据
            $articles = [];
            while ($widget->next()) {
                $articles[] = self::extractArticleData($widget);
            }

            // 格式化文章数据
            $formattedArticles = self::formatArticles($articles);

            return [
                'sort' => $sort,
                'layout' => $layout,
                'p' => $currentPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'per_page' => $perPage,
                'articles' => $formattedArticles
            ];

        } catch (Exception $e) {
            return self::emptyResult($sort, $layout, $currentPage, $perPage);
        }
    }

    /**
     * 获取归档 Widget
     * @param string $archive_type 归档类型
     * @return object|null Widget 对象或 null
     */
    private static function getArchiveWidget($archive_type)
    {
        // 所有归档类型都使用同一个 Widget，通过 archive 对象自动处理
        return \Widget\Contents\Post\Recent::alloc();
    }

    /**
     * 从 Widget 提取文章数据
     * @param object $widget Widget 对象
     * @return array 文章数据
     */
    private static function extractArticleData($widget)
    {
        return [
            'cid' => $widget->cid,
            'title' => $widget->title,
            'slug' => $widget->slug,
            'created' => $widget->created,
            'modified' => $widget->modified,
            'authorId' => $widget->authorId,
            'author' => is_object($widget->author) ? $widget->author->name : $widget->author,
            'text' => $widget->text,
            'views' => isset($widget->views) ? $widget->views : 0,
            'commentsNum' => $widget->commentsNum,
            'likes' => 0,
            'order' => $widget->order,
            'url' => $widget->permalink,
            'excerpt' => $widget->excerpt(200, '...'),
            'fields' => self::getCustomFields($widget->cid)
        ];
    }

    /**
     * 格式化文章列表数据
     * @param array $articles 原始文章数据
     * @return array 格式化后的文章数据
     */
    private static function formatArticles($articles)
    {
        $formatted = [];

        foreach ($articles as $article) {
            $cid = $article['cid'];
            $customFields = $article['fields'] ?? [];

            // 获取缩略图
            $thumbnail = getArticleThumbnail($article);
            if (empty($thumbnail)) {
                $thumbnail = self::firstImage($cid);
            }
            if (empty($thumbnail)) {
                $thumbnail = Get::Assets('assets/images/cover/cover1.jpg');
            } else {
                $thumbnail = Get::resolveUri($thumbnail);
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

            // 从摘要中移除图片语法
            $excerpt = $article['excerpt'] ?? '';
            $excerpt = preg_replace('/!\[.*?\]\(.*?\)/', '', $excerpt);
            $excerpt = preg_replace('/!\[.*?\]\[.*?\]/', '', $excerpt);
            $excerpt = strip_tags($excerpt);
            $excerpt = preg_replace('/\s+/', ' ', $excerpt);
            $excerpt = trim($excerpt);
            $excerpt = mb_substr($excerpt, 0, 200, 'UTF-8');
            if (mb_strlen($excerpt, 'UTF-8') >= 200) {
                $excerpt .= '...';
            }

            $formatted[] = [
                'cid' => $cid,
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

        return $formatted;
    }

    /**
     * 返回空结果
     * @param string $sort 排序方式
     * @param string $layout 布局方式
     * @param int $currentPage 当前页码
     * @param int $perPage 每页数量
     * @return array 空结果数组
     */
    private static function emptyResult($sort, $layout, $currentPage, $perPage)
    {
        return [
            'sort' => $sort,
            'layout' => $layout,
            'p' => $currentPage,
            'total' => 0,
            'total_pages' => 0,
            'per_page' => $perPage,
            'articles' => []
        ];
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
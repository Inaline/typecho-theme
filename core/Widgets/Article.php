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
     * @param string $order 排序字段 'created', 'modified', 'views', 'commentsNum', 'mid'
     * @param string $sort 排序方向 'asc', 'desc'
     * @param int $limit 返回数量限制，0 表示不限制
     * @param int $offset 偏移量
     * @return array
     */
    public static function all($fields = ['cid', 'title', 'slug', 'created', 'modified', 'authorId', 'author', 'text', 'views', 'commentsNum', 'order'], $order = 'created', $sort = 'desc', $limit = 0, $offset = 0)
    {
        $result = [];
        
        try {
            $widget = \Widget\Contents\Post\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999) . '&page=' . ceil(($offset + 1) / ($limit > 0 ? $limit : 1)));
            
            // 遍历文章
            while ($widget->next()) {
                $item = [];
                
                // 只返回指定字段
                if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                if (in_array('title', $fields)) $item['title'] = $widget->title;
                if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                if (in_array('created', $fields)) $item['created'] = $widget->created;
                if (in_array('modified', $fields)) $item['modified'] = $widget->modified;
                if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                if (in_array('author', $fields)) $item['author'] = $widget->author;
                if (in_array('text', $fields)) $item['text'] = $widget->text;
                if (in_array('views', $fields)) $item['views'] = isset($widget->views) ? $widget->views : 0;
                if (in_array('commentsNum', $fields)) $item['commentsNum'] = $widget->commentsNum;
                if (in_array('order', $fields)) $item['order'] = $widget->order;
                
                // 添加 URL
                if (in_array('url', $fields)) $item['url'] = $widget->permalink;
                
                // 添加摘要
                if (in_array('excerpt', $fields)) $item['excerpt'] = $widget->excerpt(200, '...');
                
                $result[] = $item;
            }
        } catch (Exception $e) {
            return [];
        }

        // 排序映射
        $orderMap = [
            'created' => 'created',
            'modified' => 'modified',
            'views' => 'views',
            'commentsNum' => 'commentsNum',
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
        $widget = \Widget\Contents\Post\Recent::alloc('pageSize=1');
        $result = null;

        try {
            while ($widget->next()) {
                if ($widget->cid == $cid || $widget->slug == $cid) {
                    $item = [];
                    
                    if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                    if (in_array('title', $fields)) $item['title'] = $widget->title;
                    if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                    if (in_array('created', $fields)) $item['created'] = $widget->created;
                    if (in_array('modified', $fields)) $item['modified'] = $widget->modified;
                    if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                    if (in_array('author', $fields)) $item['author'] = $widget->author;
                    if (in_array('text', $fields)) $item['text'] = $widget->text;
                    if (in_array('views', $fields)) $item['views'] = isset($widget->views) ? $widget->views : 0;
                    if (in_array('commentsNum', $fields)) $item['commentsNum'] = $widget->commentsNum;
                    if (in_array('order', $fields)) $item['order'] = $widget->order;
                    if (in_array('url', $fields)) $item['url'] = $widget->permalink;
                    if (in_array('excerpt', $fields)) $item['excerpt'] = $widget->excerpt(200, '...');

                    $result = $item;
                    break;
                }
            }
        } catch (Exception $e) {
            return null;
        }

        return $result;
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
            $widget = \Widget\Contents\Post\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                $categories = $widget->categories;
                $inCategory = false;
                
                foreach ($categories as $category) {
                    if ($category['mid'] == $mid || $category['slug'] == $mid) {
                        $inCategory = true;
                        break;
                    }
                }
                
                if ($inCategory) {
                    $item = [];
                    
                    if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                    if (in_array('title', $fields)) $item['title'] = $widget->title;
                    if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                    if (in_array('created', $fields)) $item['created'] = $widget->created;
                    if (in_array('modified', $fields)) $item['modified'] = $widget->modified;
                    if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                    if (in_array('author', $fields)) $item['author'] = $widget->author;
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
            $widget = \Widget\Contents\Post\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                $tags = $widget->tags;
                $hasTag = false;
                
                foreach ($tags as $tag) {
                    if ($tag['mid'] == $mid || $tag['slug'] == $mid) {
                        $hasTag = true;
                        break;
                    }
                }
                
                if ($hasTag) {
                    $item = [];
                    
                    if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                    if (in_array('title', $fields)) $item['title'] = $widget->title;
                    if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                    if (in_array('created', $fields)) $item['created'] = $widget->created;
                    if (in_array('modified', $fields)) $item['modified'] = $widget->modified;
                    if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                    if (in_array('author', $fields)) $item['author'] = $widget->author;
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
            $widget = \Widget\Contents\Post\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
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
                    if (in_array('author', $fields)) $item['author'] = $widget->author;
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
}

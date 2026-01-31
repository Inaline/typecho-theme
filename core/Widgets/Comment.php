<?php
/**
 * Inaline Typecho 主题 GetComment 方法类
 * 提供评论相关获取
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class GetComment
{
    /* ==========================
     * 基础信息
     * ========================== */

    /**
     * 获取所有评论
     * @param array $fields 指定返回字段 ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes']
     * @param string $order 排序字段 'created', 'likes'
     * @param string $sort 排序方向 'asc', 'desc'
     * @param int $limit 返回数量限制，0 表示不限制
     * @param int $offset 偏移量
     * @return array
     */
    public static function all($fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'], $order = 'created', $sort = 'desc', $limit = 0, $offset = 0)
    {
        $result = [];
        
        try {
            // 获取评论，如果有限制数量，则获取更多以便排序后再限制
            $fetchLimit = ($limit > 0) ? max($limit + $offset, $limit) : 999999;
            $widget = \Widget\Comments\Recent::alloc('pageSize=' . $fetchLimit . '&page=1');
            
            // 遍历评论
            while ($widget->next()) {
                $item = [];
                
                // 只返回指定字段
                if (in_array('coid', $fields)) $item['coid'] = $widget->coid;
                if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                if (in_array('created', $fields)) $item['created'] = $widget->created;
                if (in_array('author', $fields)) $item['author'] = $widget->author;
                if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                if (in_array('mail', $fields)) $item['mail'] = $widget->mail;
                if (in_array('url', $fields)) $item['url'] = $widget->url;
                if (in_array('ip', $fields)) $item['ip'] = $widget->ip;
                if (in_array('agent', $fields)) $item['agent'] = $widget->agent;
                if (in_array('text', $fields)) $item['text'] = $widget->text;
                if (in_array('type', $fields)) $item['type'] = $widget->type;
                if (in_array('status', $fields)) $item['status'] = $widget->status;
                if (in_array('parent', $fields)) $item['parent'] = $widget->parent;
                if (in_array('likes', $fields)) $item['likes'] = isset($widget->likes) ? $widget->likes : 0;
                
                $result[] = $item;
            }
        } catch (Exception $e) {
            return [];
        }

        // 排序映射
        $orderMap = [
            'created' => 'created',
            'likes' => 'likes',
            'coid' => 'coid'
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
     * 获取单条评论
     * @param int $coid 评论 ID
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function get($coid, $fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'])
    {
        $result = null;
    
        try {
            $db = \Typecho_Db::get();
            $row = $db->fetchRow($db->select()->from('table.comments')->where('coid = ?', $coid)->limit(1));
    
            if ($row) {
                $item = [];
                
                if (in_array('coid', $fields)) $item['coid'] = $row['coid'];
                if (in_array('cid', $fields)) $item['cid'] = $row['cid'];
                if (in_array('created', $fields)) $item['created'] = $row['created'];
                if (in_array('author', $fields)) $item['author'] = $row['author'];
                if (in_array('authorId', $fields)) $item['authorId'] = $row['authorId'];
                if (in_array('mail', $fields)) $item['mail'] = $row['mail'];
                if (in_array('url', $fields)) $item['url'] = $row['url'];
                if (in_array('ip', $fields)) $item['ip'] = $row['ip'];
                if (in_array('agent', $fields)) $item['agent'] = $row['agent'];
                if (in_array('text', $fields)) $item['text'] = $row['text'];
                if (in_array('type', $fields)) $item['type'] = $row['type'];
                if (in_array('status', $fields)) $item['status'] = $row['status'];
                if (in_array('parent', $fields)) $item['parent'] = $row['parent'];
                if (in_array('likes', $fields)) $item['likes'] = isset($row['likes']) ? $row['likes'] : 0;
    
                $result = $item;
            }
        } catch (Exception $e) {
            return null;
        }
    
        return $result;
    }
    /**
     * 获取评论作者
     * @param int $coid 评论 ID
     * @return string
     */
    public static function author($coid)
    {
        $comment = self::get($coid, ['author']);
        return $comment ? $comment['author'] : '';
    }

    /**
     * 获取评论作者 ID
     * @param int $coid 评论 ID
     * @return int
     */
    public static function authorId($coid)
    {
        $comment = self::get($coid, ['authorId']);
        return $comment ? $comment['authorId'] : 0;
    }

    /**
     * 获取评论内容
     * @param int $coid 评论 ID
     * @return string
     */
    public static function content($coid)
    {
        $comment = self::get($coid, ['text']);
        return $comment ? $comment['text'] : '';
    }

    /**
     * 获取评论邮箱
     * @param int $coid 评论 ID
     * @return string
     */
    public static function mail($coid)
    {
        $comment = self::get($coid, ['mail']);
        return $comment ? $comment['mail'] : '';
    }

    /**
     * 获取评论作者网站
     * @param int $coid 评论 ID
     * @return string
     */
    public static function url($coid)
    {
        $comment = self::get($coid, ['url']);
        return $comment ? $comment['url'] : '';
    }

    /**
     * 获取评论 IP
     * @param int $coid 评论 ID
     * @return string
     */
    public static function ip($coid)
    {
        $comment = self::get($coid, ['ip']);
        return $comment ? $comment['ip'] : '';
    }

    /**
     * 获取评论时间
     * @param int $coid 评论 ID
     * @param string $format 时间格式，默认为 'Y-m-d H:i:s'
     * @return string
     */
    public static function created($coid, $format = 'Y-m-d H:i:s')
    {
        $comment = self::get($coid, ['created']);
        return $comment ? date($format, $comment['created']) : '';
    }

    /**
     * 获取评论点赞数
     * @param int $coid 评论 ID
     * @return int
     */
    public static function likes($coid)
    {
        $comment = self::get($coid, ['likes']);
        return $comment ? $comment['likes'] : 0;
    }

    /**
     * 获取评论状态
     * @param int $coid 评论 ID
     * @return string
     */
    public static function status($coid)
    {
        $comment = self::get($coid, ['status']);
        return $comment ? $comment['status'] : '';
    }

    /**
     * 获取父评论 ID
     * @param int $coid 评论 ID
     * @return int
     */
    public static function parent($coid)
    {
        $comment = self::get($coid, ['parent']);
        return $comment ? $comment['parent'] : 0;
    }

    /* ==========================
     * 按文章获取评论
     * ========================== */

    /**
     * 获取指定文章的评论
     * @param int|string $cid 文章 ID 或缩略名
     * @param array $fields 指定返回字段
     * @param string $order 排序字段
     * @param string $sort 排序方向
     * @param int $limit 返回数量限制
     * @return array
     */
    public static function byArticle($cid, $fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'], $order = 'created', $sort = 'desc', $limit = 0)
    {
        $result = [];
        
        try {
            $widget = \Widget\Comments\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                if ($widget->cid == $cid) {
                    $item = [];
                    
                    if (in_array('coid', $fields)) $item['coid'] = $widget->coid;
                    if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                    if (in_array('created', $fields)) $item['created'] = $widget->created;
                    if (in_array('author', $fields)) $item['author'] = $widget->author;
                    if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                    if (in_array('mail', $fields)) $item['mail'] = $widget->mail;
                    if (in_array('url', $fields)) $item['url'] = $widget->url;
                    if (in_array('ip', $fields)) $item['ip'] = $widget->ip;
                    if (in_array('agent', $fields)) $item['agent'] = $widget->agent;
                    if (in_array('text', $fields)) $item['text'] = $widget->text;
                    if (in_array('type', $fields)) $item['type'] = $widget->type;
                    if (in_array('status', $fields)) $item['status'] = $widget->status;
                    if (in_array('parent', $fields)) $item['parent'] = $widget->parent;
                    if (in_array('likes', $fields)) $item['likes'] = isset($widget->likes) ? $widget->likes : 0;
                    
                    $result[] = $item;
                }
            }
        } catch (Exception $e) {
            return [];
        }

        // 排序
        $orderMap = [
            'created' => 'created',
            'likes' => 'likes',
            'coid' => 'coid'
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
     * 树形结构
     * ========================== */

    /**
     * 获取评论树形结构（按文章）
     * @param int|string $cid 文章 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function tree($cid, $fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'])
    {
        $all = self::byArticle($cid, $fields, 'created', 'asc');
        return self::buildTree($all);
    }

    /**
     * 构建树形结构（辅助方法）
     * @param array $elements 所有评论
     * @param int $parentId 父评论 ID
     * @return array
     */
    public static function buildTree(array $elements, $parentId = 0)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent'] == $parentId) {
                $children = self::buildTree($elements, $element['coid']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * 获取子评论
     * @param int $coid 评论 ID
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function children($coid, $fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'])
    {
        $comment = self::get($coid, ['cid']);
        if (!$comment) {
            return [];
        }

        $all = self::byArticle($comment['cid'], $fields);
        return self::buildTree($all, $coid);
    }

    /**
     * 获取父评论信息
     * @param int $coid 评论 ID
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function parentInfo($coid, $fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'])
    {
        $parentId = self::parent($coid);
        if ($parentId > 0) {
            return self::get($parentId, $fields);
        }
        return null;
    }

    /**
     * 获取顶级评论（根评论）
     * @param int $coid 评论 ID
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function root($coid, $fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'])
    {
        $current = self::get($coid, ['parent']);
        if (!$current) {
            return null;
        }

        // 如果当前就是根评论，直接返回
        if ($current['parent'] == 0) {
            return self::get($coid, $fields);
        }

        // 递归查找根评论
        return self::root($current['parent'], $fields);
    }

    /* ==========================
     * 实用工具
     * ========================== */

    /**
     * 检查评论是否存在
     * @param int $coid 评论 ID
     * @return bool
     */
    public static function exists($coid)
    {
        return self::get($coid) !== null;
    }

    /**
     * 检查是否有子评论
     * @param int $coid 评论 ID
     * @return bool
     */
    public static function hasChildren($coid)
    {
        $children = self::children($coid, ['coid']);
        return count($children) > 0;
    }

    /**
     * 获取评论总数
     * @param int|string $cid 文章 ID 或缩略名，如果为空则返回所有评论数
     * @return int
     */
    public static function total($cid = null)
    {
        try {
            if ($cid !== null) {
                $comments = self::byArticle($cid, ['coid']);
                return count($comments);
            } else {
                $widget = \Widget\Comments\Recent::alloc('pageSize=999999');
                $count = 0;
                
                while ($widget->next()) {
                    $count++;
                }
                
                return $count;
            }
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * 获取最新评论
     * @param int $limit 返回数量限制
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function latest($limit = 10, $fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'])
    {
        return self::all($fields, 'created', 'desc', $limit);
    }

    /**
     * 获取热门评论（按点赞数排序）
     * @param int $limit 返回数量限制
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function popular($limit = 10, $fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'])
    {
        return self::all($fields, 'likes', 'desc', $limit);
    }

    /**
     * 按作者获取评论
     * @param int|string $authorId 作者 ID 或作者名称
     * @param array $fields 指定返回字段
     * @param int $limit 返回数量限制
     * @return array
     */
    public static function byAuthor($authorId, $fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'], $limit = 0)
    {
        $result = [];
        
        try {
            $widget = \Widget\Comments\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                if ($widget->authorId == $authorId || $widget->author == $authorId) {
                    $item = [];
                    
                    if (in_array('coid', $fields)) $item['coid'] = $widget->coid;
                    if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                    if (in_array('created', $fields)) $item['created'] = $widget->created;
                    if (in_array('author', $fields)) $item['author'] = $widget->author;
                    if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                    if (in_array('mail', $fields)) $item['mail'] = $widget->mail;
                    if (in_array('url', $fields)) $item['url'] = $widget->url;
                    if (in_array('ip', $fields)) $item['ip'] = $widget->ip;
                    if (in_array('agent', $fields)) $item['agent'] = $widget->agent;
                    if (in_array('text', $fields)) $item['text'] = $widget->text;
                    if (in_array('type', $fields)) $item['type'] = $widget->type;
                    if (in_array('status', $fields)) $item['status'] = $widget->status;
                    if (in_array('parent', $fields)) $item['parent'] = $widget->parent;
                    if (in_array('likes', $fields)) $item['likes'] = isset($widget->likes) ? $widget->likes : 0;
                    
                    $result[] = $item;
                }
            }
        } catch (Exception $e) {
            return [];
        }

        return $result;
    }

    /**
     * 获取待审核评论
     * @param array $fields 指定返回字段
     * @param int $limit 返回数量限制
     * @return array
     */
    public static function pending($fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'], $limit = 0)
    {
        $result = [];
        
        try {
            $widget = \Widget\Comments\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                if ($widget->status == 'waiting') {
                    $item = [];
                    
                    if (in_array('coid', $fields)) $item['coid'] = $widget->coid;
                    if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                    if (in_array('created', $fields)) $item['created'] = $widget->created;
                    if (in_array('author', $fields)) $item['author'] = $widget->author;
                    if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                    if (in_array('mail', $fields)) $item['mail'] = $widget->mail;
                    if (in_array('url', $fields)) $item['url'] = $widget->url;
                    if (in_array('ip', $fields)) $item['ip'] = $widget->ip;
                    if (in_array('agent', $fields)) $item['agent'] = $widget->agent;
                    if (in_array('text', $fields)) $item['text'] = $widget->text;
                    if (in_array('type', $fields)) $item['type'] = $widget->type;
                    if (in_array('status', $fields)) $item['status'] = $widget->status;
                    if (in_array('parent', $fields)) $item['parent'] = $widget->parent;
                    if (in_array('likes', $fields)) $item['likes'] = isset($widget->likes) ? $widget->likes : 0;
                    
                    $result[] = $item;
                }
            }
        } catch (Exception $e) {
            return [];
        }

        return $result;
    }

    /**
     * 获取已批准评论
     * @param array $fields 指定返回字段
     * @param int $limit 返回数量限制
     * @return array
     */
    public static function approved($fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'], $limit = 0)
    {
        $result = [];
        
        try {
            $widget = \Widget\Comments\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                if ($widget->status == 'approved') {
                    $item = [];
                    
                    if (in_array('coid', $fields)) $item['coid'] = $widget->coid;
                    if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                    if (in_array('created', $fields)) $item['created'] = $widget->created;
                    if (in_array('author', $fields)) $item['author'] = $widget->author;
                    if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                    if (in_array('mail', $fields)) $item['mail'] = $widget->mail;
                    if (in_array('url', $fields)) $item['url'] = $widget->url;
                    if (in_array('ip', $fields)) $item['ip'] = $widget->ip;
                    if (in_array('agent', $fields)) $item['agent'] = $widget->agent;
                    if (in_array('text', $fields)) $item['text'] = $widget->text;
                    if (in_array('type', $fields)) $item['type'] = $widget->type;
                    if (in_array('status', $fields)) $item['status'] = $widget->status;
                    if (in_array('parent', $fields)) $item['parent'] = $widget->parent;
                    if (in_array('likes', $fields)) $item['likes'] = isset($widget->likes) ? $widget->likes : 0;
                    
                    $result[] = $item;
                }
            }
        } catch (Exception $e) {
            return [];
        }

        return $result;
    }

    /**
     * 获取垃圾评论
     * @param array $fields 指定返回字段
     * @param int $limit 返回数量限制
     * @return array
     */
    public static function spam($fields = ['coid', 'cid', 'created', 'author', 'authorId', 'mail', 'url', 'ip', 'agent', 'text', 'type', 'status', 'parent', 'likes'], $limit = 0)
    {
        $result = [];
        
        try {
            $widget = \Widget\Comments\Recent::alloc('pageSize=' . ($limit > 0 ? $limit : 999999));
            
            while ($widget->next()) {
                if ($widget->status == 'spam') {
                    $item = [];
                    
                    if (in_array('coid', $fields)) $item['coid'] = $widget->coid;
                    if (in_array('cid', $fields)) $item['cid'] = $widget->cid;
                    if (in_array('created', $fields)) $item['created'] = $widget->created;
                    if (in_array('author', $fields)) $item['author'] = $widget->author;
                    if (in_array('authorId', $fields)) $item['authorId'] = $widget->authorId;
                    if (in_array('mail', $fields)) $item['mail'] = $widget->mail;
                    if (in_array('url', $fields)) $item['url'] = $widget->url;
                    if (in_array('ip', $fields)) $item['ip'] = $widget->ip;
                    if (in_array('agent', $fields)) $item['agent'] = $widget->agent;
                    if (in_array('text', $fields)) $item['text'] = $widget->text;
                    if (in_array('type', $fields)) $item['type'] = $widget->type;
                    if (in_array('status', $fields)) $item['status'] = $widget->status;
                    if (in_array('parent', $fields)) $item['parent'] = $widget->parent;
                    if (in_array('likes', $fields)) $item['likes'] = isset($widget->likes) ? $widget->likes : 0;
                    
                    $result[] = $item;
                }
            }
        } catch (Exception $e) {
            return [];
        }

        return $result;
    }

    /* ==========================
     * 评论统计
     * ========================== */

    /**
     * 获取评论统计信息
     * @param int|string $cid 文章 ID 或缩略名，如果为空则返回全局统计
     * @return array
     */
    public static function statistics($cid = null)
    {
        $stats = [
            'total' => 0,
            'approved' => 0,
            'pending' => 0,
            'spam' => 0,
            'by_author' => []
        ];
        
        try {
            if ($cid !== null) {
                $comments = self::byArticle($cid, ['status', 'author', 'authorId']);
            } else {
                $comments = self::all(['status', 'author', 'authorId']);
            }
            
            foreach ($comments as $comment) {
                $stats['total']++;
                
                if ($comment['status'] == 'approved') {
                    $stats['approved']++;
                } elseif ($comment['status'] == 'waiting') {
                    $stats['pending']++;
                } elseif ($comment['status'] == 'spam') {
                    $stats['spam']++;
                }
                
                // 按作者统计
                $author = $comment['author'];
                if (!isset($stats['by_author'][$author])) {
                    $stats['by_author'][$author] = 0;
                }
                $stats['by_author'][$author]++;
            }
        } catch (Exception $e) {
            return $stats;
        }

        return $stats;
    }

    /**
     * 获取评论最多的文章
     * @param int $limit 返回数量限制
     * @return array
     */
    public static function mostCommented($limit = 10)
    {
        $result = [];
        
        try {
            $widget = \Widget\Contents\Post\Recent::alloc('pageSize=999999');
            $articles = [];
            
            while ($widget->next()) {
                $articles[] = [
                    'cid' => $widget->cid,
                    'title' => $widget->title,
                    'slug' => $widget->slug,
                    'url' => $widget->permalink,
                    'commentsNum' => $widget->commentsNum
                ];
            }
            
            // 按评论数排序
            usort($articles, function($a, $b) {
                return $b['commentsNum'] <=> $a['commentsNum'];
            });
            
            // 返回指定数量的文章
            $result = array_slice($articles, 0, $limit);
        } catch (Exception $e) {
            return [];
        }

        return $result;
    }
}
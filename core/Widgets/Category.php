<?php
/**
 * Inaline Typecho 主题 GetCategory 方法类
 * 提供分类相关获取
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class GetCategory
{
    /* ==========================
     * 基础信息
     * ========================== */

    /**
     * 获取所有分类
     * @param array $fields 指定返回字段 ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order']
     * @param string $order 排序字段 'order', 'count', 'mid', 'name'
     * @param string $sort 排序方向 'asc', 'desc'
     * @return array
     */
    public static function all($fields = ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order'], $order = 'order', $sort = 'asc')
    {
        $result = [];
        
        try {
            $widget = \Widget\Metas\Category\Rows::alloc();
            
            // 遍历分类
            while ($widget->next()) {
                $item = [];
                
                // 只返回指定字段
                if (in_array('mid', $fields)) $item['mid'] = $widget->mid;
                if (in_array('name', $fields)) $item['name'] = $widget->name;
                if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                if (in_array('parent', $fields)) $item['parent'] = $widget->parent;
                if (in_array('description', $fields)) $item['description'] = $widget->description;
                if (in_array('count', $fields)) $item['count'] = $widget->count;
                if (in_array('order', $fields)) $item['order'] = $widget->order;
                
                // 添加 URL
                if (in_array('url', $fields)) $item['url'] = $widget->permalink;
                
                $result[] = $item;
            }
        } catch (Exception $e) {
            // 如果出错，返回空数组
            return [];
        }

        // 排序映射
        $orderMap = [
            'order' => 'order',
            'count' => 'count',
            'mid' => 'mid',
            'name' => 'name'
        ];
        
        $sortField = isset($orderMap[$order]) ? $orderMap[$order] : 'order';
        $sortDir = strtolower($sort) === 'desc' ? SORT_DESC : SORT_ASC;

        // 排序 - 使用 usort 避免数组大小不一致问题
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
     * 获取单个分类
     * @param int|string $mid 分类 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function get($mid, $fields = ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order'])
    {
        $widget = \Widget\Metas\Category\Rows::alloc();
        $result = null;

        try {
            while ($widget->next()) {
                if ($widget->mid == $mid || $widget->slug == $mid) {
                    $item = [];
                    
                    if (in_array('mid', $fields)) $item['mid'] = $widget->mid;
                    if (in_array('name', $fields)) $item['name'] = $widget->name;
                    if (in_array('slug', $fields)) $item['slug'] = $widget->slug;
                    if (in_array('parent', $fields)) $item['parent'] = $widget->parent;
                    if (in_array('description', $fields)) $item['description'] = $widget->description;
                    if (in_array('count', $fields)) $item['count'] = $widget->count;
                    if (in_array('order', $fields)) $item['order'] = $widget->order;
                    if (in_array('url', $fields)) $item['url'] = $widget->permalink;

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
     * 获取分类名称
     * @param int|string $mid 分类 ID 或缩略名
     * @return string
     */
    public static function name($mid)
    {
        $category = self::get($mid, ['name']);
        return $category ? $category['name'] : '';
    }

    /**
     * 获取分类缩略名
     * @param int|string $mid 分类 ID 或缩略名
     * @return string
     */
    public static function slug($mid)
    {
        $category = self::get($mid, ['slug']);
        return $category ? $category['slug'] : '';
    }

    /**
     * 获取分类描述
     * @param int|string $mid 分类 ID 或缩略名
     * @return string
     */
    public static function description($mid)
    {
        $category = self::get($mid, ['description']);
        return $category ? $category['description'] : '';
    }

    /**
     * 获取父级分类 ID
     * @param int|string $mid 分类 ID 或缩略名
     * @return int
     */
    public static function parent($mid)
    {
        $category = self::get($mid, ['parent']);
        return $category ? $category['parent'] : 0;
    }

    /**
     * 获取分类文章数量
     * @param int|string $mid 分类 ID 或缩略名
     * @return int
     */
    public static function count($mid)
    {
        $category = self::get($mid, ['count']);
        return $category ? $category['count'] : 0;
    }

    /**
     * 获取分类 URL
     * @param int|string $mid 分类 ID 或缩略名
     * @return string
     */
    public static function url($mid)
    {
        $category = self::get($mid, ['url']);
        return $category ? $category['url'] : '';
    }

    /* ==========================
     * 树形结构
     * ========================== */

    /**
     * 获取分类树形结构
     * @param array $fields 指定返回字段
     * @param int $parentId 父级 ID，0 表示根分类
     * @return array
     */
    public static function tree($fields = ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order'], $parentId = 0)
    {
        $all = self::all($fields);
        return self::buildTree($all, $parentId);
    }

    /**
     * 构建树形结构（辅助方法）
     * @param array $elements 所有元素
     * @param int $parentId 父级 ID
     * @return array
     */
    private static function buildTree(array $elements, $parentId = 0)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent'] == $parentId) {
                $children = self::buildTree($elements, $element['mid']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * 获取子分类
     * @param int|string $mid 分类 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function children($mid, $fields = ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order'])
    {
        $category = self::get($mid, ['mid']);
        if (!$category) {
            return [];
        }

        return self::tree($fields, $category['mid']);
    }

    /**
     * 获取父级分类信息
     * @param int|string $mid 分类 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function parentInfo($mid, $fields = ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order'])
    {
        $parentId = self::parent($mid);
        if ($parentId > 0) {
            return self::get($parentId, $fields);
        }
        return null;
    }

    /**
     * 获取顶级分类（根分类）
     * @param int|string $mid 分类 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array|null
     */
    public static function root($mid, $fields = ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order'])
    {
        $current = self::get($mid, ['parent']);
        if (!$current) {
            return null;
        }

        // 如果当前就是根分类，直接返回
        if ($current['parent'] == 0) {
            return self::get($mid, $fields);
        }

        // 递归查找根分类
        return self::root($current['parent'], $fields);
    }

    /* ==========================
     * 实用工具
     * ========================== */

    /**
     * 检查分类是否存在
     * @param int|string $mid 分类 ID 或缩略名
     * @return bool
     */
    public static function exists($mid)
    {
        return self::get($mid) !== null;
    }

    /**
     * 检查是否为子分类
     * @param int|string $mid 分类 ID 或缩略名
     * @return bool
     */
    public static function hasChildren($mid)
    {
        $children = self::children($mid, ['mid']);
        return count($children) > 0;
    }

    /**
     * 获取分类路径（从根到当前分类的所有分类）
     * @param int|string $mid 分类 ID 或缩略名
     * @param array $fields 指定返回字段
     * @return array
     */
    public static function path($mid, $fields = ['mid', 'name', 'slug', 'parent', 'description', 'count', 'order'])
    {
        $path = [];
        $current = self::get($mid, ['mid', 'parent']);
        
        if (!$current) {
            return $path;
        }

        // 从当前分类向上查找
        while ($current && $current['parent'] > 0) {
            array_unshift($path, self::get($current['mid'], $fields));
            $current = self::get($current['parent'], ['mid', 'parent']);
        }
        
        // 添加根分类
        if ($current) {
            array_unshift($path, self::get($current['mid'], $fields));
        }

        return $path;
    }

    /* ==========================
     * 描述解析
     * ========================== */

    /**
     * 解析分类描述 JSON
     * @param string $description 分类描述
     * @return array
     */
    private static function parseDescription($description)
    {
        $parsed = json_decode($description, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
            return $parsed;
        }
        
        return ['icon' => '', 'details' => $description];
    }

    /**
     * 获取分类图标（从描述中解析）
     * @param int|string $mid 分类 ID 或缩略名
     * @return string
     */
    public static function icon($mid)
    {
        $category = self::get($mid, ['description', 'parent']);
        if (!$category) {
            return '';
        }

        // 只有顶级分类才有图标
        if ($category['parent'] > 0) {
            return '';
        }

        $parsed = self::parseDescription($category['description']);
        return isset($parsed['icon']) ? $parsed['icon'] : '';
    }

    /**
     * 获取分类详情（从描述中解析）
     * @param int|string $mid 分类 ID 或缩略名
     * @return string
     */
    public static function details($mid)
    {
        $category = self::get($mid, ['description']);
        if (!$category) {
            return '';
        }

        $parsed = self::parseDescription($category['description']);
        return isset($parsed['details']) ? $parsed['details'] : '';
    }

    /* ==========================
     * TopBar 导航构建
     * ========================== */

    /**
     * 构建分类导航 JSON（用于 TopBar）
     * @return string JSON 字符串
     */
    public static function buildNavJson()
    {
        $tree = self::tree(['mid', 'name', 'slug', 'parent', 'description', 'count', 'url']);
        $navItems = [];

        foreach ($tree as $category) {
            $navItems[] = self::buildNavItem($category, 0);
        }

        return json_encode($navItems, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 递归构建导航项（辅助方法）
     * @param array $category 分类数据
     * @param int $depth 深度
     * @return array 导航项
     */
    private static function buildNavItem($category, $depth = 0)
    {
        $item = [
            'name' => $category['slug'],
            'label' => $category['name'],
            'url' => $category['url']
        ];

        // 只有顶级分类才有图标
        if ($depth === 0) {
            $item['icon'] = self::icon($category['mid']);
        }

        // 如果有子分类，递归添加
        if (isset($category['children']) && !empty($category['children'])) {
            $item['children'] = [];
            foreach ($category['children'] as $child) {
                $item['children'][] = self::buildNavItem($child, $depth + 1);
            }
        }

        return $item;
    }
}
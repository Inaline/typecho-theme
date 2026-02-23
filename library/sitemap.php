<?php
/**
 * Inaline 主题 Sitemap 生成器
 * 生成符合 Google Sitemap 协议的 XML 站点地图
 * @author Inaline Studio
 * @version 1.2.0
 */

// 如果直接访问此文件，需要初始化 Typecho 环境
if (!defined('__TYPECHO_ROOT_DIR__')) {
    // 尝试从上级目录查找 Typecho 根目录
    $theme_dir = dirname(dirname(__FILE__));
    $possible_paths = [
        dirname(dirname(dirname($theme_dir))),  // /opt/1panel/apps/typecho/typecho/data
        dirname(dirname(dirname(dirname($theme_dir)))),  // /opt/1panel/apps/typecho/typecho
        dirname(dirname(dirname(dirname(dirname($theme_dir))))),  // 再多一级
    ];

    $found = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path . '/config.inc.php')) {
            define('__TYPECHO_ROOT_DIR__', $path);
            require_once($path . '/config.inc.php');
            $found = true;
            break;
        }
    }

    if (!$found) {
        // 尝试通过 Composer 自动加载
        $autoload_paths = [
            $theme_dir . '/../../vendor/autoload.php',
            dirname($theme_dir) . '/vendor/autoload.php',
            '/opt/1panel/apps/typecho/typecho/vendor/autoload.php',
        ];
        
        foreach ($autoload_paths as $autoload) {
            if (file_exists($autoload)) {
                require_once($autoload);
                break;
            }
        }
        
        throw new RuntimeException('Cannot find Typecho configuration file. Searched: ' . implode(', ', $possible_paths));
    }
}

// 确保 Widget 和 Router 类已加载
if (!class_exists('Typecho_Widget')) {
    require_once __TYPECHO_ROOT_DIR__ . '/var/Typecho/Widget.php';
}
if (!class_exists('Typecho_Router')) {
    require_once __TYPECHO_ROOT_DIR__ . '/var/Typecho/Router.php';
}
if (!class_exists('Utils\Helper')) {
    require_once __TYPECHO_ROOT_DIR__ . '/var/Utils/Helper.php';
}

use Utils\Helper;

class Sitemap
{
    /** @var Typecho_Widget_Options */
    private static $options;

    /** @var string 站点基础 URL */
    private static $siteUrl;

    /**
     * 初始化配置
     */
    private static function init()
    {
        if (self::$options === null) {
            self::$options = Helper::options();
            
            // 修复：确保获取正确的站点 URL
            // 方法1: 使用 siteUrl
            self::$siteUrl = rtrim(self::$options->siteUrl, '/');
            
            // 方法2: 如果 siteUrl 不正确，尝试从 index 构建
            if (empty(self::$siteUrl) || strpos(self::$siteUrl, '127.0.0.1') === false) {
                $index = self::$options->index;
                if (!empty($index)) {
                    self::$siteUrl = rtrim($index, '/');
                }
            }
            
            // 方法3: 如果还是空，尝试从数据库读取
            if (empty(self::$siteUrl)) {
                $db = Typecho_Db::get();
                $row = $db->fetchRow($db->select('value')->from('table.options')->where('name = ?', 'siteUrl'));
                if ($row) {
                    self::$siteUrl = rtrim(unserialize($row['value']), '/');
                }
            }
            
            // 初始化 Router
            Typecho_Router::setRoutes(self::$options->routingTable);
        }
    }

    /**
     * 生成 Sitemap XML
     * @return string Sitemap XML 内容
     */
    public static function generate()
    {
        try {
            self::init();
            
            $db = Typecho_Db::get();

            // 设置 XML 头部
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            // 添加首页
            $xml .= self::createUrl(
                self::$siteUrl . '/',
                date('c'),
                'daily',
                1.0
            );

            // 添加文章页面
            try {
                $xml .= self::addPosts($db);
            } catch (Exception $e) {
                error_log('Sitemap posts error: ' . $e->getMessage());
            }

            // 添加独立页面
            try {
                $xml .= self::addPages($db);
            } catch (Exception $e) {
                error_log('Sitemap pages error: ' . $e->getMessage());
            }

            // 添加分类页面
            try {
                $xml .= self::addCategories($db);
            } catch (Exception $e) {
                error_log('Sitemap categories error: ' . $e->getMessage());
            }

            // 添加标签页面
            try {
                $xml .= self::addTags($db);
            } catch (Exception $e) {
                error_log('Sitemap tags error: ' . $e->getMessage());
            }

            $xml .= '</urlset>';

            return $xml;
        } catch (Exception $e) {
            error_log('Sitemap generate error: ' . $e->getMessage());
            return self::generateFallback();
        }
    }

    /**
     * 生成备用 Sitemap（仅首页）
     */
    private static function generateFallback()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $siteUrl = 'http://127.0.0.1'; // 默认 fallback
        try {
            if (self::$options) {
                $siteUrl = self::$siteUrl ?: self::$options->siteUrl;
            }
        } catch (Exception $e) {}
        
        $xml .= self::createUrl(rtrim($siteUrl, '/') . '/', date('c'), 'daily', 1.0);
        $xml .= '</urlset>';
        return $xml;
    }

    /**
     * 创建 URL 元素
     */
    private static function createUrl($loc, $lastmod, $changefreq, $priority)
    {
        // 确保 URL 是绝对路径
        $loc = self::ensureAbsoluteUrl($loc);
        
        return sprintf(
            "  <url>\n    <loc>%s</loc>\n    <lastmod>%s</lastmod>\n    <changefreq>%s</changefreq>\n    <priority>%.1f</priority>\n  </url>\n",
            htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'),
            $lastmod,
            $changefreq,
            $priority
        );
    }

    /**
     * 确保 URL 是绝对路径
     */
    private static function ensureAbsoluteUrl($url)
    {
        // 如果已经是绝对 URL，直接返回
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return $url;
        }
        
        // 如果是相对路径，拼接站点 URL
        if (strpos($url, '/') === 0) {
            return self::$siteUrl . $url;
        }
        
        // 其他情况，确保有斜杠分隔
        return self::$siteUrl . '/' . ltrim($url, '/');
    }

    /**
     * 构建文章/页面永久链接
     * 修复：直接使用数据库数据和 Router，但确保输出绝对 URL
     */
    private static function buildContentPermalink($row)
    {
        // 获取路由前缀
        $type = $row['type'];
        
        // 构建路由参数
        $params = array(
            'cid' => $row['cid'],
            'slug' => $row['slug']
        );
        
        // 使用 Router 生成路径
        $route = ($type == 'page') ? 'page' : 'post';
        $path = Typecho_Router::url($route, $params, self::$siteUrl);
        
        // 如果 Router 返回的是相对路径，确保拼接站点 URL
        return self::ensureAbsoluteUrl($path);
    }

    /**
     * 构建分类/标签永久链接
     */
    private static function buildMetaPermalink($row)
    {
        $params = array('slug' => $row['slug']);
        $path = Typecho_Router::url($row['type'], $params, self::$siteUrl);
        
        return self::ensureAbsoluteUrl($path);
    }

    /**
     * 添加文章页面
     */
    private static function addPosts($db)
    {
        $xml = '';

        $posts = $db->fetchAll(
            $db->select('cid', 'slug', 'created', 'type')
                ->from('table.contents')
                ->where('type = ?', 'post')
                ->where('status = ?', 'publish')
                ->where('created < ?', time())
                ->order('created', Typecho_Db::SORT_DESC)
        );

        foreach ($posts as $post) {
            $permalink = self::buildContentPermalink($post);
            $lastmod = date('c', $post['created']);
            $xml .= self::createUrl($permalink, $lastmod, 'weekly', 0.8);
        }
        
        return $xml;
    }

    /**
     * 添加独立页面
     */
    private static function addPages($db)
    {
        $xml = '';

        $pages = $db->fetchAll(
            $db->select('cid', 'slug', 'created', 'type')
                ->from('table.contents')
                ->where('type = ?', 'page')
                ->where('status = ?', 'publish')
                ->where('created < ?', time())
                ->order('created', Typecho_Db::SORT_DESC)
        );

        foreach ($pages as $page) {
            $permalink = self::buildContentPermalink($page);
            $lastmod = date('c', $page['created']);
            $xml .= self::createUrl($permalink, $lastmod, 'monthly', 0.6);
        }

        return $xml;
    }

    /**
     * 添加分类页面
     */
    private static function addCategories($db)
    {
        $xml = '';

        $categories = $db->fetchAll(
            $db->select('mid', 'slug', 'name')
                ->from('table.metas')
                ->where('type = ?', 'category')
                ->order('order', Typecho_Db::SORT_ASC)
        );

        foreach ($categories as $category) {
            $category['type'] = 'category';
            $permalink = self::buildMetaPermalink($category);
            $xml .= self::createUrl($permalink, date('c'), 'weekly', 0.5);
        }

        return $xml;
    }

    /**
     * 添加标签页面
     */
    private static function addTags($db)
    {
        $xml = '';

        $tags = $db->fetchAll(
            $db->select('mid', 'slug', 'name')
                ->from('table.metas')
                ->where('type = ?', 'tag')
                ->order('mid', Typecho_Db::SORT_ASC)
        );

        foreach ($tags as $tag) {
            $tag['type'] = 'tag';
            $permalink = self::buildMetaPermalink($tag);
            $xml .= self::createUrl($permalink, date('c'), 'monthly', 0.4);
        }

        return $xml;
    }

    /**
     * 获取所有 URL 列表
     */
    private static function getAllUrls()
    {
        try {
            self::init();
            
            $urls = [self::$siteUrl . '/'];
            $db = Typecho_Db::get();

            // 文章
            try {
                $posts = $db->fetchAll(
                    $db->select('cid', 'slug', 'type')
                        ->from('table.contents')
                        ->where('type = ?', 'post')
                        ->where('status = ?', 'publish')
                        ->where('created < ?', time())
                );
                foreach ($posts as $post) {
                    $urls[] = self::buildContentPermalink($post);
                }
            } catch (Exception $e) {}

            // 页面
            try {
                $pages = $db->fetchAll(
                    $db->select('cid', 'slug', 'type')
                        ->from('table.contents')
                        ->where('type = ?', 'page')
                        ->where('status = ?', 'publish')
                );
                foreach ($pages as $page) {
                    $urls[] = self::buildContentPermalink($page);
                }
            } catch (Exception $e) {}

            // 分类
            try {
                $categories = $db->fetchAll(
                    $db->select('mid', 'slug')
                        ->from('table.metas')
                        ->where('type = ?', 'category')
                );
                foreach ($categories as $category) {
                    $category['type'] = 'category';
                    $urls[] = self::buildMetaPermalink($category);
                }
            } catch (Exception $e) {}

            // 标签
            try {
                $tags = $db->fetchAll(
                    $db->select('mid', 'slug')
                        ->from('table.metas')
                        ->where('type = ?', 'tag')
                );
                foreach ($tags as $tag) {
                    $tag['type'] = 'tag';
                    $urls[] = self::buildMetaPermalink($tag);
                }
            } catch (Exception $e) {}

            return $urls;
        } catch (Exception $e) {
            return [self::$siteUrl ?: 'http://127.0.0.1'];
        }
    }

    /**
     * 生成 Sitemap TXT
     */
    public static function generateTxt()
    {
        $urls = self::getAllUrls();
        return implode("\n", $urls);
    }

    /**
     * 输出 Sitemap
     */
    public static function output($type = 'xml')
    {
        $type = strtolower($type);

        if ($type === 'txt') {
            header('Content-Type: text/plain; charset=UTF-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            echo self::generateTxt();
        } else {
            header('Content-Type: application/xml; charset=UTF-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            echo self::generate();
        }
    }
}

// 如果直接访问此文件，则输出 Sitemap
if (basename($_SERVER['PHP_SELF']) === 'sitemap.php' ||
    (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'sitemap.php') !== false)) {
    try {
        $type = isset($_GET['type']) ? $_GET['type'] : 'xml';
        Sitemap::output($type);
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
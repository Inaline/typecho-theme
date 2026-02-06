<?php
/**
 * Inaline 主题 Sitemap 生成器
 * 生成符合 Google Sitemap 协议的 XML 站点地图
 * @author Inaline Studio
 * @version 1.0.0
 */

use Utils\Helper;

// 如果直接访问此文件，需要初始化 Typecho 环境
if (!defined('__TYPECHO_ROOT_DIR__')) {
    // 尝试从上级目录查找 Typecho 根目录
    $theme_dir = dirname(dirname(__FILE__));
    $possible_paths = [
        dirname(dirname(dirname($theme_dir))),  // /opt/1panel/apps/typecho/typecho/data
        dirname(dirname(dirname(dirname($theme_dir)))),  // /opt/1panel/apps/typecho/typecho
    ];

    foreach ($possible_paths as $path) {
        if (file_exists($path . '/config.inc.php')) {
            define('__TYPECHO_ROOT_DIR__', $path);
            require_once($path . '/config.inc.php');

            // 手动加载 Helper 类
            if (file_exists(__TYPECHO_ROOT_DIR__ . '/var/Utils/Helper.php')) {
                require_once(__TYPECHO_ROOT_DIR__ . '/var/Utils/Helper.php');
            }

            break;
        }
    }

    // 如果还是找不到，抛出错误
    if (!defined('__TYPECHO_ROOT_DIR__')) {
        throw new RuntimeException('Cannot find Typecho configuration file');
    }
}

class Sitemap
{
    /**
     * 生成 Sitemap XML
     * @return string Sitemap XML 内容
     */
    public static function generate()
    {
        try {
            // 获取数据库对象
            $db = Typecho_Db::get();
            $options = Helper::options();

            // 设置 XML 头部
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            // 添加首页
            $xml .= self::createUrl(
                $options->siteUrl,
                date('c'),
                'daily',
                1.0
            );

            // 添加文章页面
            try {
                $xml .= self::addPosts($db, $options);
            } catch (Exception $e) {
                // 文章查询失败，继续处理其他内容
            }

            // 添加独立页面
            try {
                $xml .= self::addPages($db, $options);
            } catch (Exception $e) {
                // 页面查询失败，继续处理其他内容
            }

            // 添加分类页面
            try {
                $xml .= self::addCategories($db, $options);
            } catch (Exception $e) {
                // 分类查询失败，继续处理其他内容
            }

            // 添加标签页面
            try {
                $xml .= self::addTags($db, $options);
            } catch (Exception $e) {
                // 标签查询失败，继续处理其他内容
            }

            $xml .= '</urlset>';

            return $xml;
        } catch (Exception $e) {
            // 如果整个生成过程失败，返回基本的 sitemap
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            try {
                $options = Helper::options();
                $xml .= self::createUrl(
                    $options->siteUrl,
                    date('c'),
                    'daily',
                    1.0
                );
            } catch (Exception $e2) {
                // 如果连选项都获取不到，返回空 sitemap
            }
            $xml .= '</urlset>';
            return $xml;
        }
    }

    /**
     * 创建 URL 元素
     * @param string $loc URL 地址
     * @param string $lastmod 最后修改时间
     * @param string $changefreq 更新频率
     * @param float $priority 优先级
     * @return string URL 元素 XML
     */
    private static function createUrl($loc, $lastmod, $changefreq, $priority)
    {
        return sprintf(
            "  <url>\n    <loc>%s</loc>\n    <lastmod>%s</lastmod>\n    <changefreq>%s</changefreq>\n    <priority>%.1f</priority>\n  </url>\n",
            htmlspecialchars($loc, ENT_QUOTES, 'UTF-8'),
            $lastmod,
            $changefreq,
            $priority
        );
    }

    /**
     * 添加文章页面
     * @param Typecho_Db $db 数据库对象
     * @param Typecho_Widget_Options $options 选项对象
     * @return string 文章 URL XML
     */
    private static function addPosts($db, $options)
    {
        $xml = '';

        // 查询所有已发布的文章
        $posts = $db->fetchAll(
            $db->select('cid', 'slug', 'created')
                ->from('table.contents')
                ->where('type = ?', 'post')
                ->where('status = ?', 'publish')
                ->where('created < ?', time())
                ->order('created', Typecho_Db::SORT_DESC)
        );

        foreach ($posts as $post) {
            // 构建文章永久链接
            $permalink = $options->siteUrl . 'archives/' . $post['cid'] . '/';
            $lastmod = date('c', $post['created']);
            $xml .= self::createUrl(
                $permalink,
                $lastmod,
                'weekly',
                0.8
            );
        }

        return $xml;
    }

    /**
     * 添加独立页面
     * @param Typecho_Db $db 数据库对象
     * @param Typecho_Widget_Options $options 选项对象
     * @return string 页面 URL XML
     */
    private static function addPages($db, $options)
    {
        $xml = '';

        // 查询所有已发布的独立页面
        $pages = $db->fetchAll(
            $db->select('cid', 'slug', 'created')
                ->from('table.contents')
                ->where('type = ?', 'page')
                ->where('status = ?', 'publish')
                ->where('created < ?', time())
                ->order('created', Typecho_Db::SORT_DESC)
        );

        foreach ($pages as $page) {
            // 构建页面永久链接
            $permalink = $options->siteUrl . $page['slug'] . '.html';
            $lastmod = date('c', $page['created']);
            $xml .= self::createUrl(
                $permalink,
                $lastmod,
                'monthly',
                0.6
            );
        }

        return $xml;
    }

    /**
     * 添加分类页面
     * @param Typecho_Db $db 数据库对象
     * @param Typecho_Widget_Options $options 选项对象
     * @return string 分类 URL XML
     */
    private static function addCategories($db, $options)
    {
        $xml = '';

        // 查询所有分类
        $categories = $db->fetchAll(
            $db->select('mid', 'slug')
                ->from('table.metas')
                ->where('type = ?', 'category')
                ->order('order', Typecho_Db::SORT_ASC)
        );

        foreach ($categories as $category) {
            $permalink = $options->siteUrl . 'category/' . $category['slug'] . '/';
            $xml .= self::createUrl(
                $permalink,
                date('c'),
                'weekly',
                0.5
            );
        }

        return $xml;
    }

    /**
     * 添加标签页面
     * @param Typecho_Db $db 数据库对象
     * @param Typecho_Widget_Options $options 选项对象
     * @return string 标签 URL XML
     */
    private static function addTags($db, $options)
    {
        $xml = '';

        // 查询所有标签
        $tags = $db->fetchAll(
            $db->select('mid', 'slug')
                ->from('table.metas')
                ->where('type = ?', 'tag')
                ->order('mid', Typecho_Db::SORT_ASC)
        );

        foreach ($tags as $tag) {
            $permalink = $options->siteUrl . 'tag/' . $tag['slug'] . '/';
            $xml .= self::createUrl(
                $permalink,
                date('c'),
                'monthly',
                0.4
            );
        }

        return $xml;
    }

    /**
     * 输出 Sitemap XML
     */
    public static function output()
    {
        // 设置响应头
        header('Content-Type: application/xml; charset=UTF-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        // 输出 Sitemap
        echo self::generate();
    }
}

// 如果直接访问此文件，则输出 Sitemap
if (basename($_SERVER['PHP_SELF']) === 'sitemap.php' ||
    (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'sitemap.php') !== false)) {
    try {
        Sitemap::output();
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<error>Failed to generate sitemap</error>';
    }
}
<?php
/**
 * Inaline Typecho 主题 Get 方法类
 * 提供站点相关获取
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class GetSite
{
    /* ==========================
     * 基础信息
     * ========================== */

    /** @return string 站点标题 */
    public static function title()
    {
        return Helper::options()->title;
    }

    /** @return string 站点副标题 */
    public static function subtitle()
    {
        return Helper::options()->subtitle;
    }

    /** @return string 站点描述 */
    public static function description()
    {
        return Helper::options()->description;
    }

    /** @return string 站点关键字 */
    public static function keywords()
    {
        return Helper::options()->keywords;
    }

    /** @return string 站点首页地址（带协议） */
    public static function siteUrl()
    {
        return Helper::options()->siteUrl;
    }

    /* ==========================
     * 后台管理文件夹
     * ========================== */
    
    /**
     * 后台入口目录名（URL 里的那段，不含前后斜杠）
     * 如果用户把 admin 改名为 xxx，也能自动识别
     * @return string
     */
    public static function adminDirName()
    {
        /* Typecho 后台常量 __TYPECHO_ADMIN__ 在后台入口文件 index.php 里被定义，
           其值就是入口目录名，例如 admin 或 xxx */
        return defined('__TYPECHO_ADMIN__') ? __TYPECHO_ADMIN__ : 'admin';
    }
    
    /**
     * 后台目录在磁盘上的绝对路径（末尾带 /）
     * 注意：前台调用时 __TYPECHO_ADMIN__ 可能未定义，此时只能退而求其次拼主题路径
     * @return string
     */
    public static function adminPath()
    {
        if (defined('__TYPECHO_ADMIN_DIR__')) {
            // Typecho 1.2+ 官方常量，直接就是磁盘路径
            return rtrim(__TYPECHO_ADMIN_DIR__, '/') . '/';
        }
    
        /* 兼容旧版本：先拿根路径，再拼目录名 */
        $root = defined('__TYPECHO_ROOT_DIR__') ? __TYPECHO_ROOT_DIR__ : dirname(__DIR__, 2);
        return $root . '/' . self::adminDirName() . '/';
    }

    /* ==========================
     * 作者 / 管理员
     * ========================== */

    /** @return Widget_User 第一条管理员记录 */
    public static function admin()
    {
        static $admin = null;
        if ($admin === null) {
            $admin = \Widget\Users\Author::alloc(['uid' => 1]);
        }
        return $admin;
    }

    /** @return string 管理员昵称 */
    public static function authorName()
    {
        return self::admin()->screenName;
    }

    /** @return string 管理员邮箱 */
    public static function authorMail()
    {
        return self::admin()->mail;
    }

    /** @return string 管理员头像（Gravatar 64px） */
    public static function authorAvatar($size = 64)
    {
        return self::gravatar(self::authorMail(), $size);
    }

    /* ==========================
     * 实用工具
     * ========================== */

    /**
     * 快速输出 Gravatar
     * @param string $mail 邮箱
     * @param int $size 尺寸
     * @param string $d 默认头像
     * @return string
     */
    public static function gravatar($mail, $size = 64, $d = 'mp')
    {
        $hash = md5(strtolower(trim($mail)));
        return "https://secure.gravatar.com/avatar/{$hash}?s={$size}&d={$d}";
    }

    /**
     * 获取主题目录下的静态资源完整 URL
     * @param string $path 例如 css/style.css
     * @return string
     */
    public static function themeUrl($path = '')
    {
        $theme = Helper::options()->theme;
        return Helper::options()->themeUrl . '/' . ltrim($path, '/');
    }

    /* ==========================
     * 其它快捷封装
     * ========================== */

    /**
     * 后台「阅读设置」中设定的「每页文章数目」
     * @return int
     */
    public static function postsPerPage()
    {
        return Helper::options()->postsListSize;
    }

    /**
     * 是否开启了「评论」
     * @return bool
     */
    public static function isCommentEnabled()
    {
        return Helper::options()->commentsPageDisplay != 'hide';
    }

    /**
     * 是否开启了「RSS」
     * @return bool
     */
    public static function isRssEnabled()
    {
        return Helper::options()->feedUrl !== false;
    }

    /**
     * 获取 RSS 地址
     * @return string
     */
    public static function rssUrl()
    {
        return Helper::options()->feedUrl ?: Helper::options()->siteUrl . 'feed/';
    }
}
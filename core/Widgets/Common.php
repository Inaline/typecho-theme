<?php

/**
 * Inaline Typecho 主题 Get 方法类
 * 提供页面头部、尾部等通用功能
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 全局 XSS 防护，带默认值（第 2 参数）
if (!function_exists('e')) {
    function e(
        $str,
        string $default = '',        // 新增，最常用
        int    $flags    = ENT_QUOTES,
        string $enc      = 'UTF-8'
    ): string {
        $str = $str ?? '';
        if ($str === '') {           // 空串用默认值
            $str = $default;
        }
        return htmlspecialchars($str, $flags, $enc);
    }
}

class Get
{
    /** @var AppConfig|null */
    private static $config;

    /**
     * 升级后的组件加载器
     *
     * @param Widget_Archive $archive  当前模板实例
     * @param string         $name     组件名（不含 .php）
     * @param array          $params   额外参数，会 extract 到组件作用域
     * @throws Exception 当组件不存在或路径非法时
     */
    public static function Component(
        Widget_Archive $archive,
        string $name,
        array $params = []
    ): void {
        $cmp = dirname(__DIR__, 2) . '/core/Components/' . $name . '.php';
        if (!file_exists($cmp)) {
            throw new Exception("Component {$name} 不存在");
        }
    
        /* 1. 把参数封成 ArrayObject，挂到 $archive 上 */
        $archive->data = new ArrayObject($params, ArrayObject::ARRAY_AS_PROPS);
    
        /* 2. 正常走 need，组件里就能 $this->data->title 了 */
        $archive->need('core/Components/' . $name . '.php');
    
        /* 3. 清理，避免污染后续逻辑 */
        unset($archive->data);
    }
    
    /**
     * 静态快捷读取主题配置
     * @param string $key     点语法键名，如 "color.primary"
     * @param mixed  $default
     * @return mixed
     */
    public static function Config(string $key = '', $default = null)
    {
        if (self::$config === null) {
            self::$config = new AppConfig();   // 只 new 一次
        }
        return $key === '' 
            ? self::$config->getConfig()      // 返回整个数组
            : self::$config->get($key, $default);
    }
    
    /**
     * 取页面的标题
     * - 待实现
     */
    public static function Title()
    {
    }
    
    /**
     * 取 Typecho 主题设置项（只读取 data 字段中的 JSON，支持点语法）
     *
     * @param string $key     支持点语法，如 demo_textarea
     * @param mixed  $default 找不到时返回的默认值
     * @return mixed
     */
    public static function themeOption(string $key = '', $default = null)
    {
        static $cache = null;
    
        if ($cache === null) {
            $options = Helper::options();
            $raw = $options->data ?? '';
            $cache = @json_decode($raw, true); // 转成数组
            if (!is_array($cache)) {
                $cache = []; // 解析失败就当成空数组
            }
        }
    
        if ($key === '') {
            return $cache;
        }
    
        // 点语法解析（这里只有一层，但保留兼容）
        $keys = explode('.', $key);
        $val = $cache;
        foreach ($keys as $k) {
            if (is_array($val) && array_key_exists($k, $val)) {
                $val = $val[$k];
            } else {
                return $default;
            }
        }
    
        return $val;
    }
    
    /**
     * 获取本地静态资源uri
     *
     * @param string $assets
     * @return string
     */
    public static function Assets($assets)
    {
        return Helper::options()->themeUrl . '/' . $assets;
    }
    
    /**
     * 根据主题约定解析任意资源 URI
     *
     * 约定:
     * 1. http/https 开头 → 原样返回（外部资源）
     * 2. 以 / 开头 → 原样返回（站点绝对路径）
     * 3. 以 @ 开头 → 转成相对于当前主题目录的 URI
     *    其余不满足 1、2、4 的情况也走这条规则
     * 4. data: 开头 → 原样返回（dataUrl）
     *
     * @param string $path 原始路径
     * @return string 可直接放入 src/href 的 URI
     */
    public static function resolveUri(string $path): string
    {
        $path = trim($path);
    
        // 1. 外部 URI
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
    
        // 4. dataUrl
        if (strpos($path, 'data:') === 0) {
            return $path;
        }
    
        // 2. 站点绝对路径
        if (strpos($path, '/') === 0) {
            return $path;
        }
    
        // 3. 相对于主题目录（去掉可选的 @ 前缀）
        $path = ltrim($path, '@');
        return Helper::options()->themeUrl . '/' . $path;
    }
}

// 启用 HTML 压缩 ( config.php 设置在函数内判断 )
Inaline::startMinify();
register_shutdown_function(fn() => Inaline::endMinify());
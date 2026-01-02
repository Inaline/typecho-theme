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
     * 取 Typecho 主题设置项
     * - 待实现
     */
    public static function ThemeOption()
    {
    }
}

// 启用 HTML 压缩 ( config.php 设置在函数内判断 )
Inaline::startMinify();
register_shutdown_function(fn() => Inaline::endMinify());
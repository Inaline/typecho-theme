<?php

/**
 * 主题配置文件核心处理类
 * 提供对应用配置文件的加载与访问功能
 *
 * @author Inaline Studio
 */

class AppConfig
{
    /**
     * 配置数据数组
     * @var array|null
     */
    private $app_config;

    /**
     * 构造函数
     * 加载并解析配置文件
     *
     * @throws RuntimeException 如果配置文件加载失败
     */
    public function __construct()
    {
        // 使用绝对路径，避免相对路径解析错误
        $configPath = dirname(__DIR__, 3) . '/config.php';

        if (!file_exists($configPath)) {
            throw new RuntimeException("配置文件不存在: {$configPath}");
        }

        $config = include $configPath;

        if (!is_array($config)) {
            throw new RuntimeException("配置文件必须返回一个数组: {$configPath}");
        }

        $this->app_config = $config;
    }

    /**
     * 获取全部配置项
     *
     * @return array 配置数组
     */
    public function getConfig(): array
    {
        return $this->app_config;
    }

    /**
     * 根据键获取配置项
     *
     * @param string $key 配置键名，支持点语法（如 app.debug）
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->app_config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
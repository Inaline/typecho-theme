<?php
/**
 * 主题 Inaline 基本类
 * 提供 HTML 压缩等基本功能
 * @author Inaline Studio
 */

require_once(__DIR__ . '/AppConfig.php');
require_once(__DIR__ . '/ErrorHandler.php');

try {
    $config = new AppConfig();
    $debug = $config->get('app.debug', true);
} catch (RuntimeException $e) {
    echo "配置加载失败: " . $e->getMessage();
    $debug = true; // 默认启用调试模式
}

if ($debug) {
    define('__TYPECHO_DEBUG__', true);
    ErrorHandler::register();
}

class Inaline
{
    // 使用全局变量来确保每个请求都有独立的数据
    private static function getPerfData()
    {
        if (!isset($GLOBALS['inaline_perf_data'])) {
            $GLOBALS['inaline_perf_data'] = [
                'start_memory' => 0,
                'start_time' => 0,
                'enabled' => false
            ];
        }
        return $GLOBALS['inaline_perf_data'];
    }

    /**
     * 移除行间多余空白、换行、HTML 注释
     * 保留 <pre><textarea><script><style> 内部原样
     */
    public static function minify(string $html): string
    {
        if (empty($html)) {
            return $html;
        }

        // 保护块
        $protect = [];
        $i = 0;
        
        foreach (['pre','textarea','script','style'] as $tag) {
            $html = preg_replace_callback(
                "#<{$tag}[^>]*>.*?</{$tag}>#isU",  // 添加 U 修饰符，使用非贪婪模式
                function($m) use (&$protect, &$i) {
                    $token = "🐱‍👤".($i++)."🐱‍👤";
                    $protect[$token] = $m[0];
                    return $token;
                },
                $html
            );
            
            if ($html === null) {
                // preg_replace_callback 出错，返回原内容
                return $html ?? '';
            }
        }

        // 压缩 - 修正正则表达式
        $patterns = [
            '/<!--(?!\s*\[if).*?-->/s',  // 删除普通注释，保留条件注释
            '/\s+/s',                    // 压缩多个空白字符
        ];
        
        $replacements = [
            '',  // 删除注释
            ' ', // 多个空白替换为单个空格
        ];
        
        $html = preg_replace($patterns, $replacements, $html);
        
        // 进一步压缩标签间的空白
        $html = preg_replace([
            '/>\s+</',    // 移除标签间的空白
            '/\s+</',     // 移除<前的空白
            '/>\s+/',     // 移除>后的空白
        ], [
            '><',
            '<',
            '>',
        ], $html);

        // 还原保护块
        if (!empty($protect)) {
            $html = strtr($html, $protect);
        }
        
        return trim($html);
    }

    /* 供 ob_start 的回调 */
    public static function handle(string $buffer, int $flags): string
    {
        // 检查是否需要压缩
        if (defined('__TYPECHO_DEBUG__') && __TYPECHO_DEBUG__) {
            return $buffer; // 调试模式下不压缩
        }
        
        // 检查配置是否启用HTML压缩
        try {
            $config = new AppConfig();
            $compressHtml = $config->get('app.compress_html', false);
            if (!$compressHtml) {
                return $buffer; // 配置未启用压缩时直接返回
            }
        } catch (RuntimeException $e) {
            return $buffer; // 配置加载失败时直接返回
        }
        
        // 检查内容类型
        $headers = headers_list();
        foreach ($headers as $header) {
            if (stripos($header, 'Content-Type:') !== false && 
                stripos($header, 'text/html') === false) {
                return $buffer; // 非HTML内容不压缩
            }
        }
        
        return self::minify($buffer);
    }
    
    /**
     * 启动HTML压缩
     */
    public static function startMinify(): void
    {
        ob_start([self::class, 'handle']);
    }
    
    /**
     * 结束并输出压缩后的内容
     */
    public static function endMinify(): string
    {
        if (ob_get_level() > 0) {
            return ob_end_flush();
        }
        return '';
    }
    
    /**
     * 通过 IPv4/IPv6 地址取归属地
     * - 传入 IP 返回 array 包含 国家(非中国) 或 省级行政区 和 地级市
     * - 待实现
     */
    public static function getLocationByIP()
    {
    }
    
    /**
     * 通过 UA 获取用户的操作系统和版本
     * - 待实现
     */
    public static function getSystemByUA()
    {
    }
    
    /**
     * 通过 UA 获取用户的浏览器和版本
     * - 待实现
     */
    public static function getBrowserByUA()
    {
    }
    
    /* ==========================
     * 性能监控
     * ========================== */
    
    /**
     * 初始化性能监控
     */
    public static function initPerformanceMonitor(): void
    {
        $data = self::getPerfData();
        $data['start_memory'] = memory_get_usage(false);
        $data['start_time'] = microtime(true);
        $data['enabled'] = true;
        $GLOBALS['inaline_perf_data'] = $data;
    }
    
    /**
     * 获取性能统计信息
     * @return array 包含内存使用和执行时间
     */
    public static function getPerformanceStats(): array
    {
        $data = self::getPerfData();
        
        if (!$data['enabled']) {
            return [
                'memory_used' => 'N/A',
                'memory_peak' => 'N/A',
                'memory_current' => 'N/A',
                'execution_time' => 'N/A'
            ];
        }
        
        $currentMemory = memory_get_usage(false);
        $peakMemory = memory_get_peak_usage(false);
        $memoryUsed = $currentMemory - $data['start_memory'];
        
        // 计算执行时间
        $executionTime = microtime(true) - $data['start_time'];
        
        return [
            'memory_used' => self::formatBytes($memoryUsed),
            'memory_peak' => self::formatBytes($peakMemory),
            'memory_current' => self::formatBytes($currentMemory),
            'execution_time' => self::formatTime($executionTime)
        ];
    }
    
    /**
     * 格式化字节数
     */
    private static function formatBytes($bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * 格式化时间
     */
    private static function formatTime($seconds): string
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000, 2) . ' μs';
        } elseif ($seconds < 1) {
            return round($seconds * 1000, 2) . ' ms';
        } else {
            return round($seconds, 2) . ' s';
        }
    }
    
    /**
     * 检查性能监控是否已启用
     */
    public static function isPerformanceEnabled(): bool
    {
        $data = self::getPerfData();
        return $data['enabled'];
    }
}
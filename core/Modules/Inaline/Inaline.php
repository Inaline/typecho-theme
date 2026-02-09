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
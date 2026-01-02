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
}
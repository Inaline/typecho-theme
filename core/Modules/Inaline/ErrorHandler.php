<?php
/**
 * Inaline 主题专用单文件 ErrorHandler
 * 新页面模板 + 代码片段 + JSON(中文不转义)
 *
 * @package Inaline
 * @author Inaline Studio
 */

class ErrorHandler
{
    private static $inited = false;

    public static function register(): void
    {
        if (self::$inited || self::isAdmin()) {
            return;
        }
        self::$inited = true;
        set_error_handler([__CLASS__, 'onError']);
        set_exception_handler([__CLASS__, 'onException']);
    }

    /* ---------- 内部实现 ---------- */

    public static function onError(int $severity, string $message, string $file, int $line): void
    {
        if (!(error_reporting() & $severity)) {
            return;
        }
        self::render('Error', $message, $file, $line, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
    }

    public static function onException(\Throwable $e): void
    {
        self::render(
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTrace()
        );
    }

    private static function render(string $type, string $msg, string $file, int $line, array $trace): void
    {
        /* ---------- 清掉之前所有输出 ---------- */
        while (ob_get_level()) {
            ob_end_clean();   // 一层层清
        }
    
        /* ---------- 再正常发 500 ---------- */
        http_response_code(500);
    
        /* ?json 强制返回 JSON */
        if (isset($_GET['json'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => [
                    'type'      => $type,
                    'msg'       => $msg,
                    'file'      => $file,
                    'line'      => $line,
                    'backtrace' => $trace,
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    
        /* 非 GET 请求 -> JSON */
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => [
                    'type'      => $type,
                    'msg'       => $msg,
                    'file'      => $file,
                    'line'      => $line,
                    'backtrace' => $trace,
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    
        /* GET 请求 -> HTML 新页面 */
        $code = self::getCodeFragment($file, $line);
        $backtraceHtml = self::traceToHtml($trace);
        $typeHtml = htmlspecialchars($type, ENT_QUOTES);
        $msgHtml  = htmlspecialchars($msg, ENT_QUOTES);
        $fileHtml = htmlspecialchars($file, ENT_QUOTES);
        $lineHtml = htmlspecialchars((string)$line, ENT_QUOTES);
    
        self::newPageTemplate($typeHtml, $msgHtml, $fileHtml, $lineHtml, $code, $backtraceHtml);
        exit;
    }

    /* ---------- 工具 ---------- */

    /* 读取文件并生成带行号的高亮片段 */
    private static function getCodeFragment(string $file, int $line, int $padding = 6): string
    {
        if (!is_readable($file)) {
            return '<div class="code-block"><pre>文件不可读</pre></div>';
        }
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $total = count($lines);
        $start = max(1, $line - $padding);
        $end   = min($total, $line + $padding);
        $html  = '<div class="code-block"><pre>';
        for ($i = $start; $i <= $end; $i++) {
            $code = htmlspecialchars($lines[$i - 1] ?? '', ENT_QUOTES);
            $num  = sprintf('%' . strlen((string)$end) . 'd', $i);
            if ($i === $line) {
                $html .= "<span class=\"error-line\"><span class=\"line-number\">{$num}</span>{$code}</span>\n";
            } else {
                $html .= "<span class=\"line-number\">{$num}</span>{$code}\n";
            }
        }
        $html .= '</pre></div>';
        return $html;
    }

    /* 调用栈转 HTML */
    private static function traceToHtml(array $trace): string
    {
        $html = '';
        foreach ($trace as $k => $v) {
            $file = htmlspecialchars($v['file'] ?? '[internal]', ENT_QUOTES);
            $line = htmlspecialchars((string)($v['line'] ?? 0), ENT_QUOTES);
            $func = htmlspecialchars($v['function'] ?? '', ENT_QUOTES);
            $html .= "<div class=\"backtrace-item\">#{$k} {$file}:{$line} → {$func}()</div>";
        }
        return $html ?: '<div class="backtrace-item">[无调用栈]</div>';
    }

    /* 新页面完整 HTML（含横向滚动修复） */
    private static function newPageTemplate(string $type, string $msg, string $file, string $line, string $code, string $backtrace): void
    {
        echo <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP 错误处理页面 - Inaline</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5; color: #333; line-height: 1.6; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .header { background-color: #e74c3c; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); word-break: break-word; white-space: normal; }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .error-info { background-color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .error-item { margin-bottom: 15px; }
        .error-label { font-weight: bold; color: #555; margin-bottom: 5px; }
        .error-value { color: #e74c3c; font-family: 'Courier New', monospace; background-color: #f8f8f8; padding: 8px; border-radius: 4px; overflow-x: auto; white-space: pre; }
        .code-container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .code-header { font-weight: bold; margin-bottom: 15px; color: #555; }
        .code-block { background-color: #f8f8f8; border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; font-family: 'Courier New', monospace; font-size: 14px; line-height: 1.5; overflow-x: auto; }
        .line-number { display: inline-block; width: 40px; color: #999; text-align: right; margin-right: 15px; }
        .error-line { background-color: #ffe6e6; color: #e74c3c; font-weight: bold; }
        .backtrace { background-color: white; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .backtrace h3 { margin-bottom: 15px; color: #555; }
        .backtrace-item { margin-bottom: 10px; padding: 10px; background-color: #f8f8f8; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 14px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$type}</h1>
            <p>处理您的请求时发生错误: {$msg}</p>
        </div>

        <div class="error-info">
            <div class="error-item">
                <div class="error-label">错误类型：</div>
                <div class="error-value">{$type}</div>
            </div>
            <div class="error-item">
                <div class="error-label">错误信息：</div>
                <div class="error-value">{$msg}</div>
            </div>
            <div class="error-item">
                <div class="error-label">文件：</div>
                <div class="error-value">{$file}</div>
            </div>
            <div class="error-item">
                <div class="error-label">行号：</div>
                <div class="error-value">{$line}</div>
            </div>
        </div>

        <div class="code-container">
            <div class="code-header">相关代码：</div>
            {$code}
        </div>

        <div class="backtrace">
            <h3>调用栈：</h3>
            {$backtrace}
        </div>
    </div>
</body>
</html>
HTML;
    }

    /** 判断是否后台 */
    private static function isAdmin(): bool
    {
        return defined('__TYPECHO_ADMIN__') ||
               strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') !== false;
    }
}
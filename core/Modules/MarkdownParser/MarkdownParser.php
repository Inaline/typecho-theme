<?php
/**
 * Inaline 主题 Markdown 解析器
 * 支持自定义语法: $$json$$
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class MarkdownParser
{
    /**
     * 解析文章内容中的自定义 Markdown 语法（在 Typecho 处理之前）
     * 匹配 $$json$$ 语法，只能有一行
     * @param string $content 原始内容
     * @return string 解析后的内容
     */
    public static function parse($content)
    {
        // 匹配 %%json%% 语法，只匹配同一行内的内容
        // 使用贪婪匹配，但不使用 /s 标志，这样 . 不会匹配换行符
        $pattern = '/%%(.*)%%/';

        return preg_replace_callback($pattern, [self::class, 'replaceJsonBlock'], $content);
    }

    /**
     * 替换 JSON 块为 HTML 占位符
     * @param array $matches 正则匹配结果
     * @return string HTML 占位符
     */
    private static function replaceJsonBlock($matches)
    {
        $jsonString = $matches[1];

        // 验证 JSON 格式
        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // JSON 解析失败，返回原始内容
            // 添加调试信息
            $errorMsg = json_last_error_msg();
            // 在开发模式下显示错误信息
            if (defined('__TYPECHO_DEBUG__') && __TYPECHO_DEBUG__) {
                return '<div style="background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px 0; border-radius: 4px;">' .
                       '<strong>JSON 解析失败:</strong> ' . $errorMsg . '<br>' .
                       '<strong>内容:</strong> <code>' . htmlspecialchars($jsonString) . '</code>' .
                       '</div>';
            }
            return '%%' . $jsonString . '%%';
        }

        // 检查是否有 type 字段
        if (!isset($data['type'])) {
            if (defined('__TYPECHO_DEBUG__') && __TYPECHO_DEBUG__) {
                return '<div style="background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px 0; border-radius: 4px;">' .
                       '<strong>错误:</strong> JSON 缺少 type 字段<br>' .
                       '<strong>内容:</strong> <code>' . htmlspecialchars($jsonString) . '</code>' .
                       '</div>';
            }
            return '%%' . $jsonString . '%%';
        }

        // 生成占位符，使用 data 属性存储原始数据
        $placeholderId = uniqid('inaline-');
        $encodedData = htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');

        // 使用自定义标签避免被 Markdown 解析器包裹在 p 标签中
        return '<inaline-component id="' . $placeholderId . '" data-raw="' . $encodedData . '"></inaline-component>';
    }

    /**
     * 渲染组件（在 Typecho 解析之后调用）
     * 将占位符替换为最终的 HTML
     * @param string $content 已经过 Typecho 解析的内容
     * @return string 最终内容
     */
    public static function renderComponents($content)
    {
        // 查找所有占位符组件
        $pattern = '/<inaline-component id="([^"]+)" data-raw="([^"]+)"><\/inaline-component>/s';

        return preg_replace_callback($pattern, [self::class, 'renderComponent'], $content);
    }

    /**
     * 渲染单个组件
     * @param array $matches 正则匹配结果
     * @return string 最终 HTML
     */
    private static function renderComponent($matches)
    {
        $id = $matches[1];
        $encodedData = $matches[2];

        // 解码数据
        $data = json_decode(htmlspecialchars_decode($encodedData, ENT_QUOTES), true);

        if (!$data || !isset($data['type'])) {
            return '';
        }

        // 根据 type 渲染不同的组件
        switch ($data['type']) {
            case 'card':
                return self::renderCard($data['data'] ?? []);
            default:
                return '';
        }
    }

    /**
     * 渲染卡片组件
     * @param array $data 卡片数据
     * @return string HTML 字符串
     */
    private static function renderCard($data)
    {
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';

        $html = '<div class="md-card">';
        if (!empty($title)) {
            $html .= '<div class="md-card-title">' . $title . '</div>';
        }
        if (!empty($content)) {
            // 处理内容：先解析 !!!html!!! 标记，再解析 Markdown
            $content = self::processContent($content);
            $html .= '<div class="md-card-content markdown-content">' . $content . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * 处理内容：支持 HTML 和 Markdown 混合
     * @param string $content 原始内容
     * @return string 处理后的内容
     */
    private static function processContent($content)
    {
        // 先处理 !!!html!!! 标记，提取纯 HTML
        $htmlBlocks = [];
        $placeholderIndex = 0;

        $content = preg_replace_callback('/!!!html!!!(.*?)!!!html!!!/s', function($matches) use (&$htmlBlocks, &$placeholderIndex) {
            $placeholder = '<inaline-html-block id="' . $placeholderIndex . '"></inaline-html-block>';
            $htmlBlocks[$placeholderIndex] = $matches[1];
            $placeholderIndex++;
            return $placeholder;
        }, $content);

        // 再解析剩余内容的 Markdown
        $content = Utils\Markdown::convert($content);

        // 清理代码块中可能存在的行号文本（如 "3", "4", "11" 等单独的数字）
        // 匹配 <code> 标签内单独的数字行号
        $content = preg_replace_callback('/<code>(.*?)<\/code>/s', function($matches) {
            $codeContent = $matches[1];
            // 移除行开头的纯数字行号（如 "3", "4", "11" 等）
            // 匹配行首或换行后的纯数字，后面跟着空格或特殊字符
            $codeContent = preg_replace('/(^|\n)\s*\d+\s*/', '$1', $codeContent);
            return '<code>' . $codeContent . '</code>';
        }, $content);

        // 最后将 HTML 块替换回去
        foreach ($htmlBlocks as $index => $html) {
            $placeholder = '<inaline-html-block id="' . $index . '"></inaline-html-block>';
            $content = str_replace($placeholder, $html, $content);
        }

        return $content;
    }
}
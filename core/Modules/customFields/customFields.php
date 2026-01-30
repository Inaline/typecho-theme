<?php
/**
 * Inaline 主题文章自定义字段
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 添加自定义字段到文章编辑页面
 */
function themeFields($layout)
{
    // 文章类型
    $articleType = new \Typecho\Widget\Helper\Form\Element\Select(
        'article_type',
        ['article' => '文章', 'shuoshuo' => '说说'],
        NULL,
        _t('文章类型'),
        _t('选择文章类型：文章、说说等')
    );
    $layout->addItem($articleType);

    // 文章缩略图
    $articleThumbnail = new \Typecho\Widget\Helper\Form\Element\Text(
        'article_thumbnail',
        NULL,
        NULL,
        _t('文章缩略图'),
        _t('输入文章封面图片的URL地址')
    );
    $layout->addItem($articleThumbnail);

    // 文章摘要
    $articleExcerpt = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'article_excerpt',
        NULL,
        NULL,
        _t('文章摘要'),
        _t('输入文章的简短摘要，用于列表展示')
    );
    $layout->addItem($articleExcerpt);

    // SEO 关键词
    $seoKeywords = new \Typecho\Widget\Helper\Form\Element\Text(
        'seo_keywords',
        NULL,
        NULL,
        _t('SEO 关键词'),
        _t('输入文章的SEO关键词，多个关键词用逗号分隔')
    );
    $layout->addItem($seoKeywords);

    // SEO 描述
    $seoDescription = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'seo_description',
        NULL,
        NULL,
        _t('SEO 描述'),
        _t('输入文章的SEO描述，用于搜索引擎优化')
    );
    $layout->addItem($seoDescription);

    // 文章阅读量
    $articleViews = new \Typecho\Widget\Helper\Form\Element\Text(
        'article_views',
        NULL,
        '0',
        _t('文章阅读量'),
        _t('设置文章的初始阅读量')
    );
    $layout->addItem($articleViews);

    // 文章点赞量
    $articleLikes = new \Typecho\Widget\Helper\Form\Element\Text(
        'article_likes',
        NULL,
        '0',
        _t('文章点赞量'),
        _t('设置文章的初始点赞量')
    );
    $layout->addItem($articleLikes);
}

/**
 * 获取文章类型
 * @param object $content 文章对象
 * @return string 文章类型
 */
function getArticleType($content)
{
    return $content->fields->article_type ?? 'article';
}

/**
 * 获取文章缩略图
 * @param object|array $content 文章对象或数组
 * @return string 缩略图URL
 */
function getArticleThumbnail($content)
{
    $thumbnail = '';
    
    // 处理对象类型
    if (is_object($content)) {
        // 检查字段是否存在
        if (isset($content->fields) && is_object($content->fields)) {
            // 尝试直接访问属性
            if (property_exists($content->fields, 'article_thumbnail')) {
                $thumbnail = $content->fields->article_thumbnail ?? '';
            }
        }
        
        // 如果没有获取到，尝试通过数组方式访问
        if (empty($thumbnail) && isset($content->fields) && is_array($content->fields)) {
            $thumbnail = $content->fields['article_thumbnail'] ?? '';
        }
        
        return $thumbnail ? Get::resolveUri($thumbnail) : '';
    }
    
    // 处理数组类型
    if (is_array($content)) {
        $thumbnail = $content['fields']['article_thumbnail'] ?? '';
        return $thumbnail ? Get::resolveUri($thumbnail) : '';
    }
    
    return '';
}

/**
 * 获取文章摘要
 * @param object|array $content 文章对象或数组
 * @param int $length 摘要长度
 * @return string 摘要内容
 */
function getArticleExcerpt($content, $length = 200)
{
    $excerpt = '';
    
    // 处理对象类型
    if (is_object($content)) {
        // 检查字段是否存在
        if (isset($content->fields) && is_object($content->fields)) {
            // 尝试直接访问属性
            if (property_exists($content->fields, 'article_excerpt')) {
                $excerpt = $content->fields->article_excerpt ?? '';
            }
        }
        
        // 如果没有获取到，尝试通过数组方式访问
        if (empty($excerpt) && isset($content->fields) && is_array($content->fields)) {
            $excerpt = $content->fields['article_excerpt'] ?? '';
        }
        
        // 如果没有自定义摘要，从内容中提取纯文本段落
        if (empty($excerpt)) {
            $excerpt = extractPlainTextParagraphs($content->content, $length);
        }
        
        return $excerpt;
    }
    
    // 处理数组类型
    if (is_array($content)) {
        $excerpt = $content['fields']['article_excerpt'] ?? '';
        if (empty($excerpt)) {
            $text = $content['text'] ?? $content['content'] ?? '';
            $excerpt = extractPlainTextParagraphs($text, $length);
        }
        return $excerpt;
    }
    
    return '';
}

/**
 * 从 Markdown/HTML 内容中提取纯文本段落
 * @param string $content 文章内容
 * @param int $length 摘要长度
 * @return string 纯文本段落
 */
function extractPlainTextParagraphs($content, $length = 200)
{
    if (empty($content)) {
        return '';
    }
    
    // 1. 移除代码块（Markdown 格式）
    $content = preg_replace('/```[\s\S]*?```/', '', $content);
    
    // 2. 移除行内代码
    $content = preg_replace('/`[^`]+`/', '', $content);
    
    // 3. 移除 HTML 标签
    $content = strip_tags($content);
    
    // 4. 移除标题（Markdown 格式）
    $content = preg_replace('/^#{1,6}\s+.+$/m', '', $content);
    
    // 5. 移除表格（Markdown 格式）
    // 移除表格分隔线行（如 |---|---|）
    $content = preg_replace('/^\|[\s\-:|]+\|$/m', '', $content);
    // 移除表格内容行（如 | 列1 | 列2 |）
    $content = preg_replace('/^\|.*\|$/m', '', $content);
    
    // 6. 移除无序列表
    $content = preg_replace('/^[\-\*]\s+.+$/m', '', $content);
    
    // 7. 移除有序列表
    $content = preg_replace('/^\d+\.\s+.+$/m', '', $content);
    
    // 8. 移除引用
    $content = preg_replace('/^>\s+.+$/m', '', $content);
    
    // 9. 移除图片
    $content = preg_replace('/!\[.*?\]\(.*?\)/', '', $content);
    
    // 10. 移除链接但保留链接文本
    $content = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $content);
    
    // 11. 移除水平线
    $content = preg_replace('/^[-*]{3,}$/m', '', $content);
    
    // 12. 移除 Markdown 格式标记（加粗、斜体等），只保留内部文本
    // 移除加粗：**text** 或 __text__
    $content = preg_replace('/\*\*([^*]+)\*\*/', '$1', $content);
    $content = preg_replace('/__([^_]+)__/', '$1', $content);
    
    // 移除斜体：*text* 或 _text_
    $content = preg_replace('/\*([^*]+)\*/', '$1', $content);
    $content = preg_replace('/_([^_]+)_/', '$1', $content);
    
    // 移除删除线：~~text~~
    $content = preg_replace('/~~([^~]+)~~/', '$1', $content);
    
    // 移除下划线：<u>text</u>（HTML 格式）
    $content = preg_replace('/<u>([^<]+)<\/u>/', '$1', $content);
    
    // 13. 按行分割，移除空行
    $lines = explode("\n", $content);
    $paragraphs = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        // 只保留非空行，且不以特殊符号开头（如 -、*、>、#、数字.、| 等）
        if (!empty($line) && !preg_match('/^[\-\*>#\d|]/', $line)) {
            $paragraphs[] = $line;
        }
    }
    
    // 14. 合并段落，只取开头的文本
    $text = implode(' ', $paragraphs);
    $text = trim($text);
    
    // 15. 移除多余空白
    $text = preg_replace('/\s+/', ' ', $text);
    
    // 16. 截取指定长度
    if (mb_strlen($text, 'UTF-8') > $length) {
        $excerpt = mb_substr($text, 0, $length, 'UTF-8');
        // 确保在单词边界处截断
        $lastSpace = mb_strrpos($excerpt, ' ', 0, 'UTF-8');
        if ($lastSpace !== false) {
            $excerpt = mb_substr($excerpt, 0, $lastSpace, 'UTF-8');
        }
        $excerpt .= '...';
    } else {
        $excerpt = $text;
    }
    
    return $excerpt;
}

/**
 * 获取 SEO 关键词
 * @param object $content 文章对象
 * @return string SEO关键词
 */
function getSeoKeywords($content)
{
    return $content->fields->seo_keywords ?? '';
}

/**
 * 获取 SEO 描述
 * @param object $content 文章对象
 * @return string SEO描述
 */
function getSeoDescription($content)
{
    return $content->fields->seo_description ?? '';
}

/**
 * 获取文章阅读量
 * @param object|array $content 文章对象或数组
 * @return int 阅读量
 */
function getArticleViews($content)
{
    $views = 0;
    
    // 处理对象类型
    if (is_object($content)) {
        // 检查字段是否存在
        if (isset($content->fields) && is_object($content->fields)) {
            // 尝试直接访问属性
            if (property_exists($content->fields, 'article_views')) {
                $views = intval($content->fields->article_views ?? 0);
            }
        }
        
        // 如果没有获取到，尝试通过数组方式访问
        if ($views === 0 && isset($content->fields) && is_array($content->fields)) {
            $views = intval($content->fields['article_views'] ?? 0);
        }
        
        return $views;
    }
    
    // 处理数组类型
    if (is_array($content)) {
        return intval($content['fields']['article_views'] ?? 0);
    }
    
    return 0;
}

/**
 * 增加文章阅读量
 * @param int $cid 文章ID
 * @return bool 是否成功
 */
function incrementArticleViews($cid)
{
    $db = Typecho_Db::get();
    $row = $db->fetchRow($db->select('fields')->from('table.contents')->where('cid = ?', $cid));
    
    if ($row) {
        $fields = unserialize($row['fields']);
        if (!is_array($fields)) {
            $fields = [];
        }
        $fields['article_views'] = isset($fields['article_views']) ? intval($fields['article_views']) + 1 : 1;
        $db->query($db->update('table.contents')->rows(['fields' => serialize($fields)])->where('cid = ?', $cid));
        return true;
    }
    return false;
}
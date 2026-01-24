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
 * @param object $content 文章对象
 * @return string 缩略图URL
 */
function getArticleThumbnail($content)
{
    $thumbnail = $content->fields->article_thumbnail ?? '';
    return $thumbnail ? Get::resolveUri($thumbnail) : '';
}

/**
 * 获取文章摘要
 * @param object $content 文章对象
 * @param int $length 摘要长度
 * @return string 摘要内容
 */
function getArticleExcerpt($content, $length = 200)
{
    $excerpt = $content->fields->article_excerpt ?? '';
    if (empty($excerpt)) {
        $excerpt = mb_substr(strip_tags($content->content), 0, $length, 'UTF-8');
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
 * @param object $content 文章对象
 * @return int 阅读量
 */
function getArticleViews($content)
{
    return intval($content->fields->article_views ?? 0);
}

/**
 * 获取文章点赞量
 * @param object $content 文章对象
 * @return int 点赞量
 */
function getArticleLikes($content)
{
    return intval($content->fields->article_likes ?? 0);
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

/**
 * 增加文章点赞量
 * @param int $cid 文章ID
 * @return bool 是否成功
 */
function incrementArticleLikes($cid)
{
    $db = Typecho_Db::get();
    $row = $db->fetchRow($db->select('fields')->from('table.contents')->where('cid = ?', $cid));
    
    if ($row) {
        $fields = unserialize($row['fields']);
        if (!is_array($fields)) {
            $fields = [];
        }
        $fields['article_likes'] = isset($fields['article_likes']) ? intval($fields['article_likes']) + 1 : 1;
        $db->query($db->update('table.contents')->rows(['fields' => serialize($fields)])->where('cid = ?', $cid));
        return true;
    }
    return false;
}
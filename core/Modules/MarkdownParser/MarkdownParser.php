<?php
/**
 * Inaline 主题 Markdown 解析器
 * 支持自定义语法: $$json$$, %%json%%
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class MarkdownParser
{
    /**
     * 解析文章内容中的自定义 Markdown 语法（在 Typecho 处理之前）
     * 匹配 $$json$$ 和 %%json%% 语法（只支持单行）
     * @param string $content 原始内容
     * @return string 解析后的内容
     */
    public static function parse($content)
    {
        // 匹配 %%json%% 语法，只匹配同一行内的内容
        // 不使用 /s 标志，这样 . 不会匹配换行符
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

        // 如果是友链列表，直接渲染
        if (isset($data['type']) && $data['type'] === 'links' && isset($data['data']) && is_array($data['data'])) {
            return self::renderLinksList($data['data']);
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
            case 'collapse':
                return self::renderCollapse($data['data'] ?? []);
            case 'bilibili_video':
                return self::renderBilibiliVideo($data['data'] ?? []);
            case 'music':
                return self::renderMusic($data['data'] ?? []);
            case 'netease_playlist':
                return self::renderNeteasePlaylist($data['data'] ?? []);
            case 'netdisk':
                return self::renderNetdisk($data['data'] ?? []);
            default:
                return '';
        }
    }

    /**
     * 渲染卡片组件
     * @param array $data 卡片数据（已经是 data 字段的内容）
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
     * 渲染折叠组件
     * @param array $data 折叠数据（已经是 data 字段的内容）
     * @return string HTML 字符串
     */
    private static function renderCollapse($data)
    {
        $title = $data['title'] ?? '点击展开';
        $content = $data['content'] ?? '';

        $html = '<div class="md-collapse">';
        $html .= '<div class="md-collapse-header">';
        $html .= '<div class="md-collapse-title">' . $title . '</div>';
        $html .= '<div class="md-collapse-icon"><i class="mdi mdi-chevron-down"></i></div>';
        $html .= '</div>';
        if (!empty($content)) {
            // 处理内容：先解析 !!!html!!! 标记，再解析 Markdown
            $content = self::processContent($content);
            $html .= '<div class="md-collapse-content markdown-content">' . $content . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * 渲染友链列表
     * @param array $links 友链数组
     * @return string HTML 字符串
     */
    private static function renderLinksList($links)
    {
        if (empty($links) || !is_array($links)) {
            return '';
        }

        $html = '<div class="links-grid">';

        foreach ($links as $link) {
            $name = $link['name'] ?? '';
            $url = $link['url'] ?? '#';
            $description = $link['description'] ?? '';
            $avatar = $link['avatar'] ?? '';

            // 处理头像 URI
            if (!empty($avatar)) {
                $avatar = Get::resolveUri($avatar);
            } else {
                // 使用默认头像
                $avatar = Get::Assets('assets/images/logo/Inaline.png');
            }

            $html .= '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer" class="link-card-a">';
            $html .= '<div class="link-card">';
            $html .= '<div class="link-card-avatar">';
            $html .= '<img src="' . htmlspecialchars($avatar) . '" alt="' . htmlspecialchars($name) . '" />';
            $html .= '</div>';
            $html .= '<div class="link-card-info">';
            $html .= '<div class="link-card-name">' . htmlspecialchars($name) . '</div>';
            if (!empty($description)) {
                $html .= '<div class="link-card-description">' . htmlspecialchars($description) . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</a>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * 渲染 Bilibili 视频
     * @param array $data 视频数据
     * @return string HTML 字符串
     */
    private static function renderBilibiliVideo($data)
    {
        $bvid = $data['bvid'] ?? '';
        $danmaku = isset($data['danmaku']) && $data['danmaku'] ? 1 : 0;

        if (empty($bvid)) {
            return '';
        }

        // 处理 BV 号：如果没有 BV 前缀则自动添加
        if (!preg_match('/^BV/i', $bvid)) {
            $bvid = 'BV' . $bvid;
        }

        // 构建 iframe URL
        // 参数：不开启自动播放, 不静音，展示封面，弹幕根据设置，高清播放，宽屏模式
        $iframeUrl = 'https://player.bilibili.com/player.html?bvid=' . $bvid . '&autoplay=0&muted=0&poster=1&danmaku=' . $danmaku . '&high_quality=1&as_wide=1';

        $html = '<div class="bilibili-video-container">';
        $html .= '<iframe class="bilibili-video-iframe" src="' . htmlspecialchars($iframeUrl) . '" ';
        $html .= 'scrolling="no" border="0" frameborder="no" framespacing="0" allowfullscreen="true"></iframe>';
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

    /**
     * 渲染单个音乐
     * @param array $data 音乐数据
     * @return string HTML 字符串
     */
    private static function renderMusic($data)
    {
        $name = $data['name'] ?? '';
        $artist = $data['artist'] ?? '';
        $url = $data['url'] ?? '';
        $cover = $data['cover'] ?? '';
        $lrc = $data['lrc'] ?? '';

        if (empty($name) || empty($url)) {
            return '';
        }

        $uniqueId = uniqid('aplayer-');

        // 构建 Aplayer 配置
        $audioData = [
            'name' => $name,
            'artist' => $artist,
            'url' => $url
        ];

        if (!empty($cover)) {
            $audioData['cover'] = $cover;
        }

        if (!empty($lrc)) {
            $audioData['lrc'] = $lrc;
        }

        $audioJson = json_encode([$audioData], JSON_UNESCAPED_UNICODE);

        $html = '<div class="music-player-container">';
        $html .= '<div id="' . $uniqueId . '" class="aplayer" ';
        $html .= 'data-audio="' . htmlspecialchars($audioJson, ENT_QUOTES, 'UTF-8') . '" ';
        $html .= 'data-theme="#C20C0C" ';
        $html .= 'data-loop="all" ';
        $html .= 'data-preload="auto" ';
        $html .= 'data-volume="0.7" ';
        $html .= 'data-mutex="true" ';
        $html .= 'data-list-folded="false" ';
        $html .= 'data-list-max-height="250px"';
        $html .= '></div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * 渲染网易云歌单
     * @param array $data 歌单数据
     * @return string HTML 字符串
     */
    private static function renderNeteasePlaylist($data)
    {
        $playlistId = $data['id'] ?? '';

        if (empty($playlistId)) {
            return '';
        }

        $uniqueId = uniqid('meting-');

        $html = '<div class="music-player-container">';
        $html .= '<meting-js ';
        $html .= 'id="' . htmlspecialchars($playlistId) . '" ';
        $html .= 'server="netease" ';
        $html .= 'type="playlist" ';
        $html .= 'theme="#C20C0C" ';
        $html .= 'preload="auto" ';
        $html .= 'mutex="true" ';
        $html .= 'listFolded="false" ';
        $html .= 'listMaxHeight="250px"';
        $html .= '></meting-js>';
        $html .= '</div>';

        return $html;
    }

    /**
     * 渲染网盘卡片
     * @param array $data 网盘数据
     * @return string HTML 字符串
     */
    private static function renderNetdisk($data)
    {
        $type = $data['type'] ?? 'baidu';
        $filename = $data['filename'] ?? '';
        $url = $data['url'] ?? '';
        $code = $data['code'] ?? '';

        if (empty($filename) || empty($url)) {
            return '';
        }

        // 网盘类型图标映射
        $iconMap = [
            'baidu' => 'baiducould.svg',
            'quark' => 'quarkpan.svg',
            '123pan' => '123pan.png',
            'lanzou' => 'lanzoucloud.webp',
            'openlist' => 'openlist.svg',
            'local' => 'server.svg'
        ];

        $iconFile = $iconMap[$type] ?? 'baiducould.svg';
        $iconUrl = Get::Assets('assets/images/icons/' . $iconFile);

        $html = '<div class="netdisk-card">';
        $html .= '<div class="netdisk-icon">';
        $html .= '<img src="' . htmlspecialchars($iconUrl) . '" alt="' . htmlspecialchars($type) . '">';
        $html .= '</div>';
        $html .= '<div class="netdisk-info">';
        $html .= '<div class="netdisk-header">';
        $html .= '<div class="netdisk-filename">' . htmlspecialchars($filename) . '</div>';
        if (!empty($code)) {
            $html .= '<div class="netdisk-code">';
            $html .= '<span class="code-label">提取码：</span>';
            $html .= '<span class="code-value">' . htmlspecialchars($code) . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '<div class="netdisk-actions">';
        $html .= '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer" class="netdisk-btn netdisk-btn-download">';
        $html .= '<span class="mdi mdi-download"></span>';
        $html .= '下载';
        $html .= '</a>';
        if (!empty($code)) {
            $html .= '<button type="button" class="netdisk-btn netdisk-btn-copy" data-code="' . htmlspecialchars($code) . '">';
            $html .= '<span class="mdi mdi-content-copy"></span>';
            $html .= '复制提取码';
            $html .= '</button>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
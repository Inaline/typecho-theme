<?php
/**
 * Inaline 主题头像生成类
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class GetAvatar
{
    /**
     * 预定义的颜色列表
     */
    private static $colors = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
        '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
        '#F8B500', '#FF6F61', '#6B5B95', '#88B04B', '#F7CAC9',
        '#92A8D1', '#955251', '#B565A7', '#009B77', '#DD4124',
        '#D65076', '#45B8AC', '#EFC050', '#5B5EA6', '#9B2335'
    ];

    /**
     * 根据用户名生成头像 HTML
     * @param string $username 用户名
     * @param string $email 邮箱（保留参数以兼容，但不再使用）
     * @param int $size 头像大小
     * @param bool $useGravatar 是否使用 Gravatar（已禁用，保留参数以兼容）
     * @return string 头像 HTML
     */
    public static function generate($username, $email = '', $size = 32, $useGravatar = false)
    {
        // 直接生成默认头像（不使用 Gravatar）
        return self::generateDefault($username, $size);
    }

    /**
     * 生成默认头像（基于用户名的首字母）
     * @param string $username 用户名
     * @param int $size 头像大小
     * @return string 头像 HTML
     */
    public static function generateDefault($username, $size = 32)
    {
        // 提取显示文字
        $text = self::extractText($username);
        
        // 根据用户名计算颜色
        $color = self::getColorByUsername($username);
        
        // 计算字体大小（约为头像大小的 40-50%）
        $fontSize = $size * 0.45;
        
        // 生成头像 HTML
        return '<div class="avatar-default" style="width:' . $size . 'px;height:' . $size . 'px;background-color:' . $color . ';border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:' . $fontSize . 'px;font-weight:600;color:#fff;text-transform:uppercase;">' . htmlspecialchars($text) . '</div>';
    }

    /**
     * 从用户名提取显示文字
     * @param string $username 用户名
     * @return string 显示文字
     */
    private static function extractText($username)
    {
        if (empty($username)) {
            return '?';
        }

        // 移除特殊符号，只保留字母、数字和中文
        $clean = preg_replace('/[^\p{L}\p{N}]/u', '', $username);
        
        if (empty($clean)) {
            // 如果清理后为空，返回第一个字符
            return mb_substr($username, 0, 1, 'UTF-8');
        }

        // 检查是否有中文字符
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $clean)) {
            // 有中文，取第一个汉字
            return mb_substr($clean, 0, 1, 'UTF-8');
        } else {
            // 没有中文，取前两个字母
            $text = strtoupper(substr($clean, 0, 2));
            // 如果只有一个字符，就返回一个
            return strlen($text) >= 2 ? $text : $text . '?';
        }
    }

    /**
     * 根据用户名计算颜色
     * @param string $username 用户名
     * @return string 颜色值
     */
    private static function getColorByUsername($username)
    {
        if (empty($username)) {
            return self::$colors[0];
        }

        // 计算用户名的 hash 值
        $hash = crc32($username);
        
        // 取绝对值并对颜色数量取余
        $index = abs($hash) % count(self::$colors);
        
        return self::$colors[$index];
    }

    /**
     * 获取默认头像 URL（已废弃，保留以兼容）
     * @param string $email 邮箱
     * @param int $size 头像大小
     * @return string 空字符串
     */
    public static function getGravatarUrl($email, $size = 32)
    {
        // Gravatar 在中国无法访问，返回空字符串
        return '';
    }
}
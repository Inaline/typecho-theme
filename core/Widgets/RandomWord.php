<?php

/**
 * 随机一言类
 * 从 words.txt 中随机输出一行"一言"
 * @author Inaline Studio
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class RandomWord
{
    /**
     * 从 words.txt 中随机获取一行
     * 
     * @return string 随机的一言
     */
    public static function get(): string
    {
        $wordsFile = dirname(__DIR__, 2) . '/core/Widgets/words.txt';
        
        if (!file_exists($wordsFile)) {
            return '暂无一言';
        }
        
        // 获取总行数
        $totalLines = 0;
        $handle = fopen($wordsFile, 'r');
        if ($handle === false) {
            return '暂无一言';
        }
        
        while (fgets($handle) !== false) {
            $totalLines++;
        }
        
        if ($totalLines === 0) {
            fclose($handle);
            return '暂无一言';
        }
        
        // 随机选择一行
        $randomLine = rand(0, $totalLines - 1);
        
        // 跳到指定行
        rewind($handle);
        for ($i = 0; $i < $randomLine; $i++) {
            fgets($handle);
        }
        
        // 读取目标行
        $line = trim(fgets($handle));
        fclose($handle);
        
        return $line ?: '暂无一言';
    }
}
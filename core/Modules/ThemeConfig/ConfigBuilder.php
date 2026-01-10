<?php
/**
 * Inaline 主题的主题设置构建器
 * @author Inaline Studio
 */

class ConfigBuilder
{
    protected $fields = [];
    protected $html   = '';
    protected $json   = '';

    public function setFields($f)
    {
        $this->fields = $f;
    }

    /**
     * 主入口：生成完整 HTML（不拼任何外部 css / js）
     */
    public function generateHtml()
    {
        /* 1. 取上次保存的数据（没有就用默认值） */
        $lastRaw = Helper::options()->data;
        $lastVal = $lastRaw ? @json_decode($lastRaw, true) : [];
        $defaults= $this->collectDefaults();
        $current = array_merge($defaults, (array)$lastVal);

        /* 2. 直接拼标签页 + 表单内容，不带资源 */
        $this->html = '<div class="typecho-option-tab clearfix">';

        /* 标签页按钮 */
        $idx = 0;
        foreach ($this->fields as $sec) {
            $cls = $idx === 0 ? ' active' : '';
            $tit = htmlspecialchars($sec['title']);
            $this->html .= "<button type='button' class='typecho-option-tab-btn{$cls}' data-tab='tab-{$idx}'>{$tit}</button>";
            $idx++;
        }
        $this->html .= '</div><div class="typecho-option-panel">';

        /* 各页内容 */
        $idx = 0;
        foreach ($this->fields as $sec) {
            $cls = $idx === 0 ? '' : ' style="display:none"';
            $this->html .= "<div class='typecho-option-tab-content' id='tab-{$idx}'{$cls}>";
            if (!empty($sec['content'])) {
                $this->html .= '<div class="typecho-option-item">' . $sec['content'] . '</div>';
            }
            if (!empty($sec['fields'])) {
                foreach ($sec['fields'] as $f) {
                    $this->html .= $this->buildField($f, $current);
                }
            }
            $this->html .= '</div>';
            $idx++;
        }
        $this->html .= '</div>';

        /* 3. 把当前实际值注入隐藏域，防止 {} */
        $this->json = json_encode($current, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getHtml()
    {
        return $this->html;
    }

    public function getJson()
    {
        return $this->json;
    }

    /* ---------- 内部工具 ---------- */

    private function buildField($f, $current)
    {
        $name  = $f['name'];
        $label = $f['label'];
        $type  = $f['type'];
        $val   = $current[$name] ?? ($f['default'] ?? '');
        $desc  = $f['description'] ?? '';

        $out  = '<div class="typecho-option-item">';
        $out .= '<label class="typecho-label">' . $label . '</label>';
        $out .= '<div class="typecho-option-item-content">';

        switch ($type) {
            case 'text':
                $out .= '<input type="text" class="w-100 text" name="' . $name . '" value="' . htmlspecialchars($val) . '"/>';
                break;

            case 'textarea':
                $out .= '<textarea class="w-100 text" name="' . $name . '" rows="5">' . htmlspecialchars($val) . '</textarea>';
                break;

            case 'radio':
                foreach ($f['options'] as $k => $v) {
                    $checked = ($k === $val) ? 'checked' : '';
                    $out .= '<label class="radio-inline"><input type="radio" name="' . $name . '" value="' . $k . '" ' . $checked . '> ' . $v . '</label> ';
                }
                break;

            case 'checkbox':
                $checked = !empty($val) ? 'checked' : '';
                $out .= '<label class="checkbox-inline"><input type="checkbox" name="' . $name . '" ' . $checked . '> 启用</label>';
                break;

            case 'select':
                $out .= '<select class="w-100" name="' . $name . '">';
                foreach ($f['options'] as $k => $v) {
                    $selected = ($k === $val) ? 'selected' : '';
                    $out .= '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
                }
                $out .= '</select>';
                break;
        
        }

        if ($desc) {
            $out .= '<p class="description">' . $desc . '</p>';
        }
        $out .= '</div></div>';
        return $out;
    }

    private function collectDefaults()
    {
        $ret = [];
        foreach ($this->fields as $s) {
            if (empty($s['fields'])) {
                continue;
            }
            foreach ($s['fields'] as $f) {
                $ret[$f['name']] = $f['default'] ?? '';
            }
        }
        return $ret;
    }
}
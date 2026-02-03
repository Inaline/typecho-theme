/**
 * Inaline Typecho 主题 - 编辑器增强功能
 */

(function ($) {
    // jQuery 插件扩展
    $.fn.extend({
        /* 插入内容 */
        insertContent: function (myValue, t) {
            var $t = $(this)[0];
            if (document.selection) {
                this.focus();
                var sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
                sel.moveStart('character', -l);
                var wee = sel.text.length;
                if (arguments.length == 2) {
                    var l = $t.value.length;
                    sel.moveEnd('character', wee + t);
                    t <= 0 ? sel.moveStart('character', wee - 2 * t - myValue.length) : sel.moveStart('character', wee - t - myValue.length);
                    sel.select();
                }
            } else if ($t.selectionStart || $t.selectionStart == '0') {
                var startPos = $t.selectionStart;
                var endPos = $t.selectionEnd;
                var scrollTop = $t.scrollTop;
                $t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
                this.focus();
                $t.selectionStart = startPos + myValue.length;
                $t.selectionEnd = startPos + myValue.length;
                $t.scrollTop = scrollTop;
                if (arguments.length == 2) {
                    $t.setSelectionRange(startPos - t, $t.selectionEnd + t);
                    this.focus();
                }
            } else {
                this.value += myValue;
                this.focus();
            }
            // 触发 input 事件
            $(this).trigger('input');
        },
        /* 选择 */
        selectionRange: function (start, end) {
            var str = '';
            var thisSrc = this[0];
            if (start === undefined) {
                if (/input|textarea/i.test(thisSrc.tagName) && /firefox/i.test(navigator.userAgent)) str = thisSrc.value.substring(thisSrc.selectionStart, thisSrc.selectionEnd);
                else if (document.selection) str = document.selection.createRange().text;
                else str = document.getSelection().toString();
            } else {
                if (!/input|textarea/.test(thisSrc.tagName.toLowerCase())) return false;
                end === undefined && (end = start);
                if (thisSrc.setSelectionRange) {
                    thisSrc.setSelectionRange(start, end);
                    this.focus();
                } else {
                    var range = thisSrc.createTextRange();
                    range.move('character', start);
                    range.moveEnd('character', end - start);
                    range.select();
                }
            }
            if (start === undefined) return str;
            else return this;
        }
    });

    // 对话框生成器
    function Dialog(options) {
        console.log('[Inaline Editor] 创建对话框:', options.title);
        
        this.options = $.extend({
            title: '对话框',
            fields: [],
            onConfirm: null,
            onCancel: null
        }, options);
        
        this.init();
    }
    
    Dialog.prototype.init = function() {
        console.log('[Inaline Editor] 初始化对话框');
        
        // 创建遮罩层
        this.overlay = $('<div class="inaline-dialog-overlay"></div>');
        
        // 创建对话框容器
        this.dialog = $('<div class="inaline-dialog"></div>');
        
        // 创建标题栏
        this.header = $('<div class="inaline-dialog-header"></div>');
        this.title = $('<h3>' + this.options.title + '</h3>');
        this.closeBtn = $('<span class="inaline-dialog-close">&times;</span>');
        this.header.append(this.title).append(this.closeBtn);
        
        // 创建内容区
        this.body = $('<div class="inaline-dialog-body"></div>');
        
        // 创建表单字段
        this.form = $('<form class="inaline-dialog-form"></form>');
        this.options.fields.forEach(function(field, index) {
            var fieldGroup = $('<div class="inaline-field-group"></div>');
            var label = $('<label class="inaline-field-label">' + field.label + '</label>');
            
            if (field.type === 'textarea') {
                var input = $('<textarea class="inaline-field-input inaline-textarea" rows="5"></textarea>');
            } else {
                var input = $('<input type="text" class="inaline-field-input" />');
            }
            
            if (field.placeholder) {
                input.attr('placeholder', field.placeholder);
            }
            
            if (field.value !== undefined) {
                input.val(field.value);
            }
            
            fieldGroup.append(label).append(input);
            this.form.append(fieldGroup);
        }, this);
        
        this.body.append(this.form);
        
        // 创建按钮区
        this.footer = $('<div class="inaline-dialog-footer"></div>');
        this.cancelBtn = $('<button type="button" class="inaline-btn inaline-btn-cancel">取消</button>');
        this.confirmBtn = $('<button type="button" class="inaline-btn inaline-btn-confirm">确定</button>');
        this.footer.append(this.cancelBtn).append(this.confirmBtn);
        
        // 组装对话框
        this.dialog.append(this.header).append(this.body).append(this.footer);
        this.overlay.append(this.dialog);
        
        // 绑定事件
        this.bindEvents();
        
        // 添加到页面
        $('body').append(this.overlay);
        
        console.log('[Inaline Editor] 对话框已添加到页面');
    };
    
    Dialog.prototype.bindEvents = function() {
        var self = this;
        
        // 关闭按钮
        this.closeBtn.on('click', function() {
            self.close();
        });
        
        // 取消按钮
        this.cancelBtn.on('click', function() {
            self.close();
            if (self.options.onCancel) {
                self.options.onCancel();
            }
        });
        
        // 确定按钮
        this.confirmBtn.on('click', function() {
            var values = [];
            self.form.find('.inaline-field-input').each(function() {
                values.push($(this).val());
            });
            
            if (self.options.onConfirm) {
                self.options.onConfirm(values);
            }
            self.close();
        });
        
        // 点击遮罩关闭
        this.overlay.on('click', function(e) {
            if (e.target === self.overlay[0]) {
                self.close();
            }
        });
        
        // ESC 键关闭
        $(document).on('keydown.dialog', function(e) {
            if (e.keyCode === 27) {
                self.close();
            }
        });
    };
    
    Dialog.prototype.close = function() {
        console.log('[Inaline Editor] 关闭对话框');
        this.overlay.remove();
        $(document).off('keydown.dialog');
    };
    
    Dialog.prototype.show = function() {
        console.log('[Inaline Editor] 显示对话框');
        this.overlay.fadeIn(200);
        // 聚焦第一个输入框
        this.form.find('.inaline-field-input').first().focus();
    };

    // 增加自定义功能
    const items = [
        {
            title: '卡片',
            id: 'wmd-card-button',
            mdi: 'mdi-card-outline',
            dialog: true,
            dialogTitle: '插入卡片',
            dialogFields: [
                { type: 'input', label: '卡片标题', placeholder: '请输入卡片标题' },
                { type: 'textarea', label: '卡片内容', placeholder: '请输入卡片内容（支持 HTML 和 Markdown）' }
            ],
            onConfirm: function(values) {
                var title = values[0] || '';
                var content = values[1] || '';
                // 替换换行符为 \n
                content = content.replace(/\n/g, '\\n');
                var cardSyntax = '\n%%{"type": "card", "data": {"title": "' + title + '", "content": "' + content + '"}}%%\n';
                $('#text').insertContent(cardSyntax);
            }
        },
        {
            title: '友链',
            id: 'wmd-links-button',
            mdi: 'mdi-link-variant',
            dialog: true,
            dialogTitle: '插入友链',
            dialogFields: [
                { type: 'textarea', label: '友链列表', placeholder: '每行一个友链，格式：网站名称|URL|描述|头像URL' }
            ],
            onConfirm: function(values) {
                var linksText = values[0] || '';
                var links = [];
                
                // 按行分割
                var lines = linksText.split('\n');
                lines.forEach(function(line) {
                    line = line.trim();
                    if (!line) return;
                    
                    // 按 | 分割
                    var parts = line.split('|');
                    if (parts.length >= 2) {
                        var link = {
                            name: parts[0].trim(),
                            url: parts[1].trim(),
                            description: parts[2] ? parts[2].trim() : '',
                            avatar: parts[3] ? parts[3].trim() : ''
                        };
                        links.push(link);
                    }
                });
                
                var linksSyntax = '\n%%{"type":"links","data":' + JSON.stringify(links) + '}%%\n';
                $('#text').insertContent(linksSyntax);
            }
        },
        {
            title: '删除线',
            id: 'wmd-strikethrough-button',
            mdi: 'mdi-format-strikethrough',
            text: '~~',
            suffix: '~~'
        },
        {
            title: '下划线',
            id: 'wmd-underline-button',
            mdi: 'mdi-format-underline',
            text: '<u>',
            suffix: '</u>'
        },
        {
            title: '任务列表',
            id: 'wmd-checkbox-button',
            mdi: 'mdi-checkbox-marked-outline',
            text: '- [ ] '
        },
        {
            title: '代码块',
            id: 'wmd-codeblock-button',
            mdi: 'mdi-code-braces',
            text: '```\n',
            suffix: '\n```'
        },
        {
            title: '表格',
            id: 'wmd-table-button',
            mdi: 'mdi-table',
            text: '\n| 标题1 | 标题2 | 标题3 |\n|-------|-------|-------|\n| 内容1 | 内容2 | 内容3 |\n| 内容4 | 内容5 | 内容6 |\n'
        },
        {
            title: '居中',
            id: 'wmd-center-button',
            mdi: 'mdi-format-align-center',
            text: '<div style="text-align:center;">',
            suffix: '</div>'
        },
        {
            title: '高亮',
            id: 'wmd-highlight-button',
            mdi: 'mdi-marker',
            text: '<mark>',
            suffix: '</mark>'
        },
        {
            title: '文字颜色',
            id: 'wmd-color-button',
            mdi: 'mdi-format-color-text',
            prompt: '请输入颜色（如 red, #ff0000）：',
            prefix: '<span style="color:',
            suffix: '">',
            close: '</span>'
        },
        {
            title: '时间戳',
            id: 'wmd-time-button',
            mdi: 'mdi-clock-outline',
            text: function() {
                const now = new Date();
                return `> 发布于：${now.toLocaleString('zh-CN')}\n`;
            }
        },
        {
            title: '音乐',
            id: 'wmd-music-button',
            mdi: 'mdi-music-note',
            prompt: '请输入音乐地址：',
            prefix: '<audio src="',
            suffix: '" controls></audio>\n'
        },
        {
            title: '视频',
            id: 'wmd-video-button',
            mdi: 'mdi-video',
            prompt: '请输入视频地址：',
            prefix: '<video src="',
            suffix: '" controls></video>\n'
        }
    ];

    // 初始化编辑器工具栏
    function initEditorToolbar() {
        console.log('[Inaline Editor] 初始化工具栏');
        var wmdButtonRow = $('#wmd-button-row');
        if (wmdButtonRow.length === 0) return;

        // 添加分隔线
        wmdButtonRow.append('<li class="wmd-spacer inaline-spacer"></li>');

        // 创建工具按钮
        items.forEach(function (_) {
            // 使用 MDI 图标或 SVG
            var iconHtml = _.mdi ? '<span class="mdi ' + _.mdi + '"></span>' : _.svg;
            var item = $('<li class="wmd-button inaline-button" id="' + _.id + '" title="' + _.title + '">' + iconHtml + '</li>');
            item.on('click', function () {
                console.log('[Inaline Editor] 点击按钮:', _.title);
                
                // 如果是对话框类型
                if (_.dialog) {
                    console.log('[Inaline Editor] 打开对话框:', _.dialogTitle);
                    var dialog = new Dialog({
                        title: _.dialogTitle,
                        fields: _.dialogFields,
                        onConfirm: function(values) {
                            console.log('[Inaline Editor] 对话框确认，值:', values);
                            if (_.onConfirm) {
                                _.onConfirm(values);
                            }
                        }
                    });
                    dialog.show();
                    return;
                }
                
                // 原有逻辑
                var text = $('#text');
                var content = '';

                if (typeof _.text === 'function') {
                    content = _.text();
                    text.insertContent(content);
                } else if (_.prompt) {
                    var value = prompt(_.prompt, '');
                    if (value) {
                        content = _.prefix + value + _.suffix;
                        if (_.close) {
                            content += _.close;
                        }
                        text.insertContent(content);
                    }
                } else {
                    content = _.text || '';
                    if (_.suffix) {
                        content += text.selectionRange() + _.suffix;
                    }
                    text.insertContent(content);
                }
            });
            wmdButtonRow.append(item);
        });
        
        console.log('[Inaline Editor] 工具栏初始化完成');
    }

    // 页面加载完成后初始化
    $(document).ready(function() {
        setTimeout(initEditorToolbar, 100);
    });

})(jQuery);
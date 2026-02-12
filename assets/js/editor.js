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
            } else if (field.type === 'checkbox') {
                var input = $('<input type="checkbox" class="inaline-field-input inaline-checkbox" />');
                fieldGroup.addClass('inaline-field-group-checkbox');
            } else if (field.type === 'select') {
                var input = $('<select class="inaline-field-input inaline-select"></select>');
                if (field.options && Array.isArray(field.options)) {
                    field.options.forEach(function(option) {
                        var optionEl = $('<option value="' + option + '">' + option + '</option>');
                        input.append(optionEl);
                    });
                }
            } else {
                var input = $('<input type="text" class="inaline-field-input" />');
            }
            
            if (field.placeholder) {
                input.attr('placeholder', field.placeholder);
            }
            
            if (field.value !== undefined) {
                if (field.type === 'checkbox') {
                    input.prop('checked', field.value);
                } else {
                    input.val(field.value);
                }
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
                var input = $(this);
                if (input.attr('type') === 'checkbox') {
                    values.push(input.prop('checked'));
                } else {
                    values.push(input.val());
                }
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
            title: '折叠',
            id: 'wmd-collapse-button',
            mdi: 'mdi-chevron-down',
            dialog: true,
            dialogTitle: '插入折叠内容',
            dialogFields: [
                { type: 'input', label: '标题', placeholder: '请输入折叠标题' },
                { type: 'textarea', label: '内容', placeholder: '请输入折叠内容（支持 Markdown）', rows: 5 }
            ],
            onConfirm: function(values) {
                var title = values[0] || '点击展开';
                var content = values[1] || '';

                var collapseSyntax = '\n%%{"type":"collapse","data":{"title":"' + title + '","content":"' + content + '"}}%%\n';
                $('#text').insertContent(collapseSyntax);
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
            dialog: true,
            dialogTitle: '插入单个音乐',
            dialogFields: [
                { type: 'input', label: '歌曲名称', placeholder: '请输入歌曲名称' },
                { type: 'input', label: '艺术家', placeholder: '请输入艺术家名称（可选）' },
                { type: 'input', label: '音乐链接', placeholder: '请输入音乐链接（MP3 等）' },
                { type: 'input', label: '封面链接', placeholder: '请输入封面图片链接（可选）' },
                { type: 'textarea', label: '歌词内容', placeholder: '请输入歌词内容（LRC 格式，可选）', rows: 5 },
                { type: 'input', label: '歌词链接', placeholder: '请输入歌词文件链接（LRC 格式，可选）' }
            ],
            onConfirm: function(values) {
                var name = values[0] || '';
                var artist = values[1] || '';
                var url = values[2] || '';
                var cover = values[3] || '';
                var lrcText = values[4] || '';
                var lrcUrl = values[5] || '';

                if (!name || !url) {
                    Swal.fire({
                        title: '提示',
                        text: '请输入歌曲名称和音乐链接',
                        icon: 'warning'
                    });
                    return;
                }

                var musicData = {
                    name: name,
                    artist: artist,
                    url: url
                };

                if (cover) {
                    musicData.cover = cover;
                }

                if (lrcText) {
                    // 处理歌词文本，替换换行符
                    var lrcProcessed = lrcText.replace(/\n/g, '\\n');
                    musicData.lrc = lrcProcessed;
                } else if (lrcUrl) {
                    musicData.lrc = lrcUrl;
                }

                var musicSyntax = '\n%%{"type": "music", "data": ' + JSON.stringify(musicData) + '}%%\n';
                $('#text').insertContent(musicSyntax);
            }
        },
        {
            title: '网易云',
            id: 'wmd-netease-button',
            svg: '<svg height="1em" style="flex:none;line-height:1" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg"><title>netease</title><g><path fill="none" d="M0 0h24v24H0z"/><path fill="#C20C0C" d="M10.421 11.375c-.294 1.028.012 2.064.784 2.653 1.061.81 2.565.3 2.874-.995.08-.337.103-.722.027-1.056-.23-1.001-.52-1.988-.792-2.996-1.33.154-2.543 1.172-2.893 2.394zm5.548-.287c.273 1.012.285 2.017-.127 3-1.128 2.69-4.721 3.14-6.573.826-1.302-1.627-1.28-3.961.06-5.734.78-1.032 1.804-1.707 3.048-2.054l.379-.104c-.084-.415-.188-.816-.243-1.224-.176-1.317.512-2.503 1.744-3.04 1.226-.535 2.708-.216 3.53.76.406.479.395 1.08-.025 1.464-.412.377-.996.346-1.435-.09-.247-.246-.51-.44-.877-.436-.525.006-.987.418-.945.937.037.468.173.93.3 1.386.022.078.216.135.338.153 1.334.197 2.504.731 3.472 1.676 2.558 2.493 2.861 6.531.672 9.44-1.529 2.032-3.61 3.168-6.127 3.409-4.621.44-8.664-2.53-9.7-7.058C2.515 10.255 4.84 5.831 8.795 4.25c.586-.234 1.143-.031 1.371.498.232.537-.019 1.086-.61 1.35-2.368 1.06-3.817 2.855-4.215 5.424-.533 3.433 1.656 6.776 5 7.72 2.723.77 5.658-.166 7.308-2.33 1.586-2.08 1.4-5.099-.427-6.873a3.979 3.979 0 0 0-1.823-1.013c.198.716.389 1.388.57 2.062z"/></g></svg>',
            dialog: true,
            dialogTitle: '插入网易云歌单',
            dialogFields: [
                { type: 'input', label: '歌单 ID', placeholder: '请输入网易云歌单 ID' }
            ],
            onConfirm: function(values) {
                var playlistId = values[0] || '';

                if (!playlistId) {
                    Swal.fire({
                        title: '提示',
                        text: '请输入网易云歌单 ID',
                        icon: 'warning'
                    });
                    return;
                }

                var playlistSyntax = '\n%%{"type": "netease_playlist", "data": {"id": "' + playlistId + '"}}%%\n';
                $('#text').insertContent(playlistSyntax);
            }
        },
        {
            title: 'Bilibili',
            id: 'wmd-bilibili-button',
            svg: '<svg height="1em" style="flex:none;line-height:1" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg"><title>bilibili</title><path clip-rule="evenodd" d="M4.977 3.561a1.31 1.31 0 111.818-1.884l2.828 2.728c.08.078.149.163.205.254h4.277a1.32 1.32 0 01.205-.254l2.828-2.728a1.31 1.31 0 011.818 1.884L17.82 4.66h.848A5.333 5.333 0 0124 9.992v7.34a5.333 5.333 0 01-5.333 5.334H5.333A5.333 5.333 0 010 17.333V9.992a5.333 5.333 0 015.333-5.333h.781L4.977 3.56zm.356 3.67a2.667 2.667 0 00-2.666 2.667v7.529a2.667 2.667 0 002.666 2.666h13.334a2.667 2.667 0 002.666-2.666v-7.53a2.667 2.667 0 00-2.666-2.666H5.333zm1.334 5.192a1.333 1.333 0 112.666 0v1.192a1.333 1.333 0 11-2.666 0v-1.192zM16 11.09c-.736 0-1.333.597-1.333 1.333v1.192a1.333 1.333 0 102.666 0v-1.192c0-.736-.597-1.333-1.333-1.333z" fill="#FB7299" fill-rule="evenodd"></path></svg>',
            dialog: true,
            dialogTitle: '插入 Bilibili 视频',
            dialogFields: [
                { type: 'input', label: 'BV 号', placeholder: '请输入 BV 号（如 BV1B7411m7LV）' },
                { type: 'checkbox', label: '显示弹幕', value: true }
            ],
            onConfirm: function(values) {
                var bvid = values[0] || '';
                var danmaku = values[1] !== false;
                
                // 处理 BV 号：如果没有 BV 前缀则自动添加
                if (bvid && !bvid.toUpperCase().startsWith('BV')) {
                    bvid = 'BV' + bvid;
                }
                
                var videoSyntax = '\n%%{"type": "bilibili_video", "data": {"bvid": "' + bvid + '", "danmaku": ' + danmaku + '}}%%\n';
                $('#text').insertContent(videoSyntax);
            }
        },
        {
            title: '视频',
            id: 'wmd-video-button',
            mdi: 'mdi-video',
            prompt: '请输入视频地址：',
            prefix: '<video src="',
            suffix: '" controls></video>\n'
        },
        {
            title: '网盘',
            id: 'wmd-netdisk-button',
            mdi: 'mdi-cloud-download-outline',
            dialog: true,
            dialogTitle: '插入网盘外链',
            dialogFields: [
                { type: 'select', label: '网盘类型', options: ['百度网盘', '夸克网盘', '123云盘', '蓝奏云', 'OpenList', '本地服务器'] },
                { type: 'input', label: '文件名', placeholder: '请输入文件名' },
                { type: 'input', label: '下载链接', placeholder: '请输入下载链接' },
                { type: 'input', label: '提取码', placeholder: '请输入提取码（可选）' }
            ],
            onConfirm: function(values) {
                var type = values[0] || '';
                var filename = values[1] || '';
                var url = values[2] || '';
                var code = values[3] || '';

                if (!filename || !url) {
                    Swal.fire({
                        title: '提示',
                        text: '请输入文件名和下载链接',
                        icon: 'warning'
                    });
                    return;
                }

                // 网盘类型映射
                var typeMap = {
                    '百度网盘': 'baidu',
                    '夸克网盘': 'quark',
                    '123云盘': '123pan',
                    '蓝奏云': 'lanzou',
                    'OpenList': 'openlist',
                    '本地服务器': 'local'
                };

                var netdiskData = {
                    type: typeMap[type] || 'baidu',
                    filename: filename,
                    url: url
                };

                if (code) {
                    netdiskData.code = code;
                }

                var netdiskSyntax = '\n%%{"type": "netdisk", "data": ' + JSON.stringify(netdiskData) + '}%%\n';
                $('#text').insertContent(netdiskSyntax);
            }
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

    // 初始化行号功能
    function initLineNumbers() {
        console.log('[Inaline Editor] 初始化行号');
        var textarea = $('#text');
        if (textarea.length === 0) return;

        // 创建行号容器
        var numbers = $('<div class="inaline-numbers"></div>');
        
        // 创建包装容器
        var wrapper = $('<div class="inaline-container"></div>');
        textarea.wrap(wrapper);
        textarea.before(numbers);

        // 获取 textarea 的样式
        var textareaStyles = window.getComputedStyle(textarea[0]);
        
        // 同步样式到行号容器
        ['fontFamily', 'fontSize', 'fontWeight', 'letterSpacing', 'lineHeight', 'padding'].forEach(function(property) {
            numbers.css(property, textareaStyles[property]);
        });

        // 创建 canvas 用于计算文本宽度
        var canvas = document.createElement('canvas');
        var context = canvas.getContext('2d');
        var font = textareaStyles.fontSize + ' ' + textareaStyles.fontFamily;
        context.font = font;

        // 计算一个句子占据多少行
        function calcStringLines(sentence, width) {
            if (!width) return 0;
            var words = sentence.split('');
            var lineCount = 0;
            var currentLine = '';
            
            for (var i = 0; i < words.length; i++) {
                var wordWidth = context.measureText(words[i]).width;
                var lineWidth = context.measureText(currentLine).width;
                if (lineWidth + wordWidth > width) {
                    lineCount++;
                    currentLine = words[i];
                } else {
                    currentLine += words[i];
                }
            }
            if (currentLine.trim() !== '') lineCount++;
            return lineCount;
        }

        // 计算所有行号
        function calcLines() {
            var lines = textarea.val().split('\n');
            var textareaWidth = textarea[0].getBoundingClientRect().width;
            var textareaScrollWidth = textareaWidth - textarea[0].clientWidth;
            
            // 解析 px 值
            var parseNumber = function(v) {
                return v.endsWith('px') ? parseInt(v.slice(0, -2), 10) : 0;
            };
            
            var textareaPaddingLeft = parseNumber(textareaStyles.paddingLeft);
            var textareaPaddingRight = parseNumber(textareaStyles.paddingRight);
            var textareaContentWidth = textareaWidth - textareaPaddingLeft - textareaPaddingRight - textareaScrollWidth;
            
            var numLines = lines.map(function(lineString) {
                return calcStringLines(lineString, textareaContentWidth);
            });
            
            var lineNumbers = [];
            var i = 1;
            while (numLines.length > 0) {
                var numLinesOfSentence = numLines.shift();
                lineNumbers.push(i);
                if (numLinesOfSentence > 1) {
                    Array(numLinesOfSentence - 1).fill('').forEach(function() {
                        lineNumbers.push('');
                    });
                }
                i++;
            }
            return lineNumbers;
        }

        // 更新行号显示
        function updateLineNumbers() {
            var lines = calcLines();
            var lineDoms = lines.map(function(line, i) {
                return '<div>' + (line || '&nbsp;') + '</div>';
            }).join('');
            numbers.html(lineDoms);
        }

        // 监听滚动
        textarea.on('scroll', function() {
            numbers.scrollTop(textarea.scrollTop());
        });

        // 监听输入
        textarea.on('input', function() {
            updateLineNumbers();
        });

        // 监听尺寸变化
        var ro = new ResizeObserver(function() {
            var rect = textarea[0].getBoundingClientRect();
            // 行号容器高度等于 textarea 高度
            numbers.height(rect.height);
            updateLineNumbers();
        });
        ro.observe(textarea[0]);

        // 初始化
        updateLineNumbers();
        
        console.log('[Inaline Editor] 行号初始化完成');
    }

    // 页面加载完成后初始化
    $(document).ready(function() {
        setTimeout(initEditorToolbar, 100);
        setTimeout(initLineNumbers, 200);
    });

})(jQuery);
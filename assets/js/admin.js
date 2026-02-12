/* 把最新界面值同步到隐藏域 */
function sync() {
    var obj = {};
    /* 1. 文本框、多行、下拉 */
    document.querySelectorAll('.typecho-option-item input[type=text], ' +
                              '.typecho-option-item textarea, ' +
                              '.typecho-option-item select').forEach(function(el){
        if (!el.name) return;
        obj[el.name] = el.value;
    });
    /* 2. 单选 */
    document.querySelectorAll('.typecho-option-item input[type=radio]:checked').forEach(function(el){
        obj[el.name] = el.value;
    });
    /* 3. 多选 */
    document.querySelectorAll('.typecho-option-item input[type=checkbox]').forEach(function(el){
        if (!el.name) return;
        obj[el.name] = el.checked;
    });

    /* 写回隐藏域 */
    document.querySelector('input[name="data"]').value = JSON.stringify(obj);
    return obj;
}

/* 提交前 alert 调试用 */
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[action*="themes-edit"]');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        sync();                       // 先同步
        // alert(document.querySelector('input[name="data"]').value);
        /* 如果想中断提交，取消下面注释即可 */
        // e.preventDefault();
    });
});

/* 切换标签页 */
document.addEventListener('DOMContentLoaded', function () {
    var tabs = document.querySelectorAll('.typecho-option-tab-btn');
    var panes = document.querySelectorAll('.typecho-option-tab-content');
    tabs.forEach(function(btn){
        btn.addEventListener('click', function(){
            var target = document.querySelector('#' + this.dataset.tab);
            tabs.forEach(function(b){ b.classList.remove('active'); });
            panes.forEach(function(p){ p.style.display = 'none'; });
            this.classList.add('active');
            target.style.display = 'block';
        });
    });
});

/* 导出配置 */
document.addEventListener('DOMContentLoaded', function () {
    var exportBtn = document.getElementById('btn-export-config');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            var config = sync();
            var jsonStr = JSON.stringify(config, null, 2);
            
            // 创建一个弹窗显示配置
            var overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;';
            
            var modal = document.createElement('div');
            modal.style.cssText = 'background:#fff;padding:20px;border-radius:8px;max-width:600px;width:90%;max-height:80vh;display:flex;flex-direction:column;';
            
            modal.innerHTML = '<h3 style="margin:0 0 10px 0;">导出配置</h3>' +
                '<p style="margin:0 0 10px 0;color:#666;">请复制以下配置 JSON：</p>' +
                '<textarea id="export-config-textarea" rows="10" style="width:100%;font-family:monospace;font-size:12px;resize:vertical;flex:1;margin-bottom:10px;">' + jsonStr + '</textarea>' +
                '<div style="display:flex;gap:10px;justify-content:flex-end;">' +
                '<button id="btn-copy" style="padding:8px 16px;background:#4CAF50;color:#fff;border:none;border-radius:4px;cursor:pointer;">复制</button>' +
                '<button id="btn-close-export" style="padding:8px 16px;background:#ccc;color:#333;border:none;border-radius:4px;cursor:pointer;">关闭</button>' +
                '</div>';
            
            overlay.appendChild(modal);
            document.body.appendChild(overlay);
            
            // 复制按钮
            document.getElementById('btn-copy').addEventListener('click', function () {
                var textarea = document.getElementById('export-config-textarea');
                textarea.select();
                document.execCommand('copy');
                Swal.fire({
                    title: '成功',
                    text: '配置已复制到剪贴板！',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
            
            // 关闭按钮
            document.getElementById('btn-close-export').addEventListener('click', function () {
                document.body.removeChild(overlay);
            });
            
            // 点击遮罩关闭
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    document.body.removeChild(overlay);
                }
            });
        });
    }
});

/* 导入配置 */
document.addEventListener('DOMContentLoaded', function () {
    var importBtn = document.getElementById('btn-import-config');
    if (importBtn) {
        importBtn.addEventListener('click', function () {
            // 创建遮罩层
            var overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;';
            
            // 创建模态框
            var modal = document.createElement('div');
            modal.style.cssText = 'background:#fff;padding:20px;border-radius:8px;max-width:600px;width:90%;max-height:80vh;display:flex;flex-direction:column;';
            
            // 标题
            var title = document.createElement('h3');
            title.style.cssText = 'margin:0 0 10px 0;';
            title.textContent = '导入配置';
            
            // 描述
            var desc = document.createElement('p');
            desc.style.cssText = 'margin:0 0 10px 0;color:#666;';
            desc.textContent = '请粘贴配置 JSON：';
            
            // 文本域
            var textarea = document.createElement('textarea');
            textarea.id = 'import-config-textarea';
            textarea.rows = 10;
            textarea.style.cssText = 'width:100%;font-family:monospace;font-size:12px;resize:vertical;flex:1;margin-bottom:10px;';
            textarea.placeholder = '请粘贴 JSON 配置...';
            
            // 错误提示
            var errorMsg = document.createElement('p');
            errorMsg.id = 'import-error';
            errorMsg.style.cssText = 'margin:0 0 10px 0;color:red;display:none;';
            
            // 按钮容器
            var btnContainer = document.createElement('div');
            btnContainer.style.cssText = 'display:flex;gap:10px;justify-content:flex-end;';
            
            // 导入按钮
            var confirmBtn = document.createElement('button');
            confirmBtn.id = 'btn-import-confirm';
            confirmBtn.style.cssText = 'padding:8px 16px;background:#4CAF50;color:#fff;border:none;border-radius:4px;cursor:pointer;';
            confirmBtn.textContent = '导入';
            
            // 取消按钮
            var cancelBtn = document.createElement('button');
            cancelBtn.id = 'btn-close-import';
            cancelBtn.style.cssText = 'padding:8px 16px;background:#ccc;color:#333;border:none;border-radius:4px;cursor:pointer;';
            cancelBtn.textContent = '取消';
            
            // 组装
            btnContainer.appendChild(confirmBtn);
            btnContainer.appendChild(cancelBtn);
            modal.appendChild(title);
            modal.appendChild(desc);
            modal.appendChild(textarea);
            modal.appendChild(errorMsg);
            modal.appendChild(btnContainer);
            overlay.appendChild(modal);
            document.body.appendChild(overlay);
            
            // 导入按钮点击事件
            confirmBtn.addEventListener('click', function () {
                var jsonStr = textarea.value.trim();
                
                if (!jsonStr) {
                    errorMsg.textContent = '请输入配置 JSON';
                    errorMsg.style.display = 'block';
                    return;
                }
                
                try {
                    var config = JSON.parse(jsonStr);
                    if (typeof config !== 'object' || config === null) {
                        throw new Error('JSON 必须是对象');
                    }
                    
                    // 填充表单
                    for (var name in config) {
                        var val = config[name];
                        
                        // 文本框和下拉框
                        var textInput = document.querySelector('input[type=text][name="' + name + '"], textarea[name="' + name + '"], select[name="' + name + '"]');
                        if (textInput) {
                            textInput.value = val;
                            continue;
                        }
                        
                        // 单选框
                        var radio = document.querySelector('input[type=radio][name="' + name + '"][value="' + val + '"]');
                        if (radio) {
                            radio.checked = true;
                            continue;
                        }
                        
                        // 复选框
                        var checkbox = document.querySelector('input[type=checkbox][name="' + name + '"]');
                        if (checkbox) {
                            checkbox.checked = !!val;
                        }
                    }
                    
                    document.body.removeChild(overlay);
                    Swal.fire({
                        title: '成功',
                        text: '配置已导入，请检查后点击保存！',
                        icon: 'success'
                    });
                } catch (e) {
                    errorMsg.textContent = 'JSON 格式错误: ' + e.message;
                    errorMsg.style.display = 'block';
                }
            });
            
            // 关闭按钮点击事件
            cancelBtn.addEventListener('click', function () {
                document.body.removeChild(overlay);
            });
            
            // 点击遮罩关闭
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    document.body.removeChild(overlay);
                }
            });
        });
    }
});
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
}

/* 提交前 alert 调试用 */
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[action*="themes-edit"]');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        sync();                       // 先同步
        alert(document.querySelector('input[name="data"]').value);
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
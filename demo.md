# Markdown 语法演示

这是一个演示文档，展示了 Inaline 主题支持的各种 Markdown 语法和自定义组件。

## 基础文本样式

**粗体文本** 和 *斜体文本*，以及 `行内代码`。

> 这是一段引用文本，用于强调重要内容或引用他人的观点。

## 代码块

```javascript
function greet(name) {
    return `Hello, ${name}!`;
}

console.log(greet('World'));
```

```python
def hello_world():
    print("Hello, World!")
    return True
```

```bash
echo "Hello, World"
ls -la
```

## 列表

### 无序列表

- 第一项
- 第二项
  - 子项 1
  - 子项 2
- 第三项

### 有序列表

1. 第一步
2. 第二步
3. 第三步

### 任务列表

- [x] 已完成的任务
- [ ] 未完成的任务
- [ ] 另一个待办事项

## 表格

| 名称 | 类型 | 描述 |
|------|------|------|
| string | 字符串 | 文本类型 |
| number | 数字 | 数值类型 |
| boolean | 布尔值 | 真或假 |
| array | 数组 | 有序集合 |
| object | 对象 | 键值对集合 |

## 链接和图片

[访问 Typecho 官网](https://typecho.org)

## 分割线

---

## 数学公式

行内公式：$E = mc^2$

块级公式：

$$
f(x) = \int_{-\infty}^{\infty} \hat{f}(\xi)\,e^{2\pi i \xi x} \,d\xi
$$

---

## 自定义卡片组件

### 基础卡片

%{"type":"card","data":{"title":"提示","content":"这是一个基础的卡片组件，使用自定义语法 %%json%% 创建。"}}%

### 包含 Markdown 内容的卡片

%{"type":"card","data":{"title":"Markdown 内容","content":"卡片内部也支持 **Markdown** 语法：\n\n- 列表项 1\n- 列表项 2\n- 列表项 3\n\n以及 `行内代码`。"}}%

### 包含 HTML 内容的卡片

%{"type":"card","data":{"title":"HTML 内容","content":"!!!html!!!<div style=\\\"padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; text-align: center;\\\">\n  <h3 style=\\\"margin: 0 0 8px 0;\\\">🎨 渐变背景</h3>\n  <p style=\\\"margin: 0;\\\">这是一个使用 HTML 自定义样式的卡片内容</p>\n</div>!!!html!!!"}}%

### 混合内容的卡片

%{"type":"card","data":{"title":"混合内容演示","content":"这个卡片同时包含 Markdown 和 HTML：\n\n!!!html!!!<div style=\\\"background: #f0f9ff; padding: 12px; border-left: 4px solid #3b82f6; border-radius: 4px; margin: 12px 0;\\\">\n  <strong style=\\\"color: #1e40af;\\\">💡 提示：</strong>\n</div>!!!html!!!\n\n这是正常的 Markdown 文本，可以继续使用各种格式。"}}%

### 嵌套卡片

%{"type":"card","data":{"title":"外层卡片","content":"这是一个包含多层内容的卡片：\n\n!!!html!!!<div style=\\\"background: #ecfdf5; padding: 12px; border: 1px solid #10b981; border-radius: 8px; margin: 12px 0;\\\">\n  <span style=\\\"color: #059669; font-weight: bold;\\\">✅ 成功状态</span>\n</div>!!!html!!!\n\n卡片内容可以包含任意复杂的 HTML 和 Markdown 组合。"}}%

### 信息提示卡片

%{"type":"card","data":{"title":"信息提示","content":"!!!html!!!<div style=\\\"background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px; margin: 12px 0; border-radius: 4px;\\\">\n  <span style=\\\"color: #d97706; font-weight: bold;\\\">⚠️ 注意：</span>\n  <span style=\\\"color: #92400e;\\\">这是一个警告提示框</span>\n</div>!!!html!!!"}}%

### 代码示例卡片

%{"type":"card","data":{"title":"代码示例","content":"```javascript\n// JavaScript 示例代码\nconst greeting = 'Hello, World!';\nconsole.log(greeting);\n```"}}%

---

## 总结

Inaline 主题支持：

1. ✅ 标准 Markdown 语法
2. ✅ 自定义卡片组件（`%%json%%`）
3. ✅ HTML 内容嵌入（`!!!html!!!`）
4. ✅ Markdown 和 HTML 混合使用
5. ✅ 数学公式（`$` 和 `$$`）
6. ✅ 代码高亮
7. ✅ 任务列表

这些特性让内容创作更加灵活和强大！

---

**使用说明：**

- **自定义卡片**：使用 `%%{"type":"card","data":{...}}%%` 语法
- **HTML 内容**：在卡片内容中使用 `!!!html!!!...!!!html!!!` 包裹 HTML
- **HTML 属性转义**：HTML 属性中的双引号需要转义为 `\"`
- **数学公式**：使用 `$` 表示行内公式，`$$` 表示块级公式
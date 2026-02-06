/**
 * Inaline 主题全局消息提示组件
 * @author Inaline Studio
 */

class Message {
    constructor() {
        this.container = null;
        this.init();
    }

    // 初始化
    init() {
        // 创建消息容器
        this.container = document.createElement('div');
        this.container.className = 'message-container';
        document.body.appendChild(this.container);
    }

    // 显示消息
    show(message, type = 'info', duration = 3000) {
        const messageEl = document.createElement('div');
        messageEl.className = `message message-${type}`;
        messageEl.innerHTML = `
            <span class="message-content">${message}</span>
        `;

        // 添加到容器
        this.container.appendChild(messageEl);

        // 触发动画
        setTimeout(() => {
            messageEl.classList.add('message-show');
        }, 10);

        // 自动移除
        setTimeout(() => {
            messageEl.classList.remove('message-show');
            setTimeout(() => {
                if (messageEl.parentNode) {
                    messageEl.parentNode.removeChild(messageEl);
                }
            }, 300);
        }, duration);
    }

    // 成功消息
    success(message, duration) {
        this.show(message, 'success', duration);
    }

    // 警告消息
    warning(message, duration) {
        this.show(message, 'warning', duration);
    }

    // 错误消息
    error(message, duration) {
        this.show(message, 'error', duration);
    }

    // 信息消息
    info(message, duration) {
        this.show(message, 'info', duration);
    }

    // 俏皮消息
    playful(message, duration) {
        this.show(message, 'playful', duration);
    }
}

// 创建全局实例
const message = new Message();
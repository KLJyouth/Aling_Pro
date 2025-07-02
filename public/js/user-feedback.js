/**
 * 用户反馈系统脚本
 * 
 * 提供用户反馈收集、评分和建议功能，与无障碍功能紧密集成
 */

(function() {
    'use strict';
    
    // 配置参数
    const config = {
        apiEndpoint: '/api/user-feedback',
        feedbackTypes: ['建议', '问题报告', '功能请求', '无障碍反馈', '其他']
    };
    
    // DOM元素缓存
    let feedbackPanel, feedbackToggle, feedbackForm, submitButton, ratingInputs;
    
    // 初始化函数
    function initFeedbackSystem() {
        // 创建反馈面板
        createFeedbackPanel();
        
        // 绑定事件
        bindEvents();
        
        // 初始化评分系统
        initRatingSystem();
        
        console.log('用户反馈系统已初始化');
    }
    
    // 创建反馈面板
    function createFeedbackPanel() {
        // 创建主容器
        feedbackPanel = document.createElement('div');
        feedbackPanel.className = 'feedback-panel';
        feedbackPanel.setAttribute('aria-label', '用户反馈');
        
        // 创建切换按钮
        feedbackToggle = document.createElement('button');
        feedbackToggle.className = 'feedback-toggle';
        feedbackToggle.innerHTML = '<i class="fas fa-comment-alt"></i>';
        feedbackToggle.setAttribute('aria-label', '打开用户反馈');
        feedbackToggle.setAttribute('title', '用户反馈');
        
        // 创建面板内容
        const panelContent = document.createElement('div');
        panelContent.className = 'feedback-panel-content';
        
        // 创建表单
        feedbackForm = document.createElement('form');
        feedbackForm.className = 'feedback-form';
        feedbackForm.setAttribute('aria-labelledby', 'feedback-title');
        
        // 表单标题
        const formTitle = document.createElement('h3');
        formTitle.id = 'feedback-title';
        formTitle.textContent = '我们重视您的反馈';
        
        // 反馈类型选择
        const typeGroup = document.createElement('div');
        typeGroup.className = 'form-group';
        
        const typeLabel = document.createElement('label');
        typeLabel.setAttribute('for', 'feedback-type');
        typeLabel.textContent = '反馈类型:';
        
        const typeSelect = document.createElement('select');
        typeSelect.id = 'feedback-type';
        typeSelect.name = 'feedback-type';
        typeSelect.required = true;
        
        // 添加空选项
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = '请选择...';
        defaultOption.disabled = true;
        defaultOption.selected = true;
        typeSelect.appendChild(defaultOption);
        
        // 添加反馈类型选项
        config.feedbackTypes.forEach(type => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            typeSelect.appendChild(option);
        });
        
        typeGroup.appendChild(typeLabel);
        typeGroup.appendChild(typeSelect);
        
        // 评分系统
        const ratingGroup = document.createElement('div');
        ratingGroup.className = 'form-group';
        
        const ratingLabel = document.createElement('label');
        ratingLabel.textContent = '您的评分:';
        
        const ratingContainer = document.createElement('div');
        ratingContainer.className = 'rating-container';
        ratingContainer.setAttribute('role', 'radiogroup');
        ratingContainer.setAttribute('aria-label', '评分，1星到5星');
        
        for (let i = 1; i <= 5; i++) {
            const ratingItem = document.createElement('div');
            ratingItem.className = 'rating-item';
            
            const ratingInput = document.createElement('input');
            ratingInput.type = 'radio';
            ratingInput.name = 'rating';
            ratingInput.id = `rating-${i}`;
            ratingInput.value = i;
            ratingInput.setAttribute('aria-label', `${i}星`);
            
            const ratingLabel = document.createElement('label');
            ratingLabel.setAttribute('for', `rating-${i}`);
            ratingLabel.innerHTML = '<i class="fas fa-star"></i>';
            ratingLabel.title = `${i}星`;
            
            ratingItem.appendChild(ratingInput);
            ratingItem.appendChild(ratingLabel);
            ratingContainer.appendChild(ratingItem);
        }
        
        ratingGroup.appendChild(ratingLabel);
        ratingGroup.appendChild(ratingContainer);
        
        // 反馈内容
        const contentGroup = document.createElement('div');
        contentGroup.className = 'form-group';
        
        const contentLabel = document.createElement('label');
        contentLabel.setAttribute('for', 'feedback-content');
        contentLabel.textContent = '您的反馈:';
        
        const contentTextarea = document.createElement('textarea');
        contentTextarea.id = 'feedback-content';
        contentTextarea.name = 'feedback-content';
        contentTextarea.placeholder = '请在此输入您的反馈、建议或问题...';
        contentTextarea.required = true;
        contentTextarea.rows = 5;
        
        contentGroup.appendChild(contentLabel);
        contentGroup.appendChild(contentTextarea);
        
        // 联系方式（可选）
        const contactGroup = document.createElement('div');
        contactGroup.className = 'form-group';
        
        const contactLabel = document.createElement('label');
        contactLabel.setAttribute('for', 'feedback-contact');
        contactLabel.textContent = '联系方式（可选）:';
        
        const contactInput = document.createElement('input');
        contactInput.type = 'email';
        contactInput.id = 'feedback-contact';
        contactInput.name = 'feedback-contact';
        contactInput.placeholder = '您的邮箱（如需回复）';
        
        contactGroup.appendChild(contactLabel);
        contactGroup.appendChild(contactInput);
        
        // 提交按钮
        submitButton = document.createElement('button');
        submitButton.type = 'submit';
        submitButton.className = 'feedback-submit';
        submitButton.textContent = '提交反馈';
        
        // 组装表单
        feedbackForm.appendChild(formTitle);
        feedbackForm.appendChild(typeGroup);
        feedbackForm.appendChild(ratingGroup);
        feedbackForm.appendChild(contentGroup);
        feedbackForm.appendChild(contactGroup);
        feedbackForm.appendChild(submitButton);
        
        // 组装面板
        panelContent.appendChild(feedbackForm);
        feedbackPanel.appendChild(feedbackToggle);
        feedbackPanel.appendChild(panelContent);
        
        // 添加到文档
        document.body.appendChild(feedbackPanel);
    }
    
    // 绑定事件
    function bindEvents() {
        // 切换面板显示
        feedbackToggle.addEventListener('click', function() {
            feedbackPanel.classList.toggle('active');
            
            if (feedbackPanel.classList.contains('active')) {
                feedbackToggle.setAttribute('aria-label', '关闭用户反馈');
            } else {
                feedbackToggle.setAttribute('aria-label', '打开用户反馈');
            }
        });
        
        // 表单提交
        feedbackForm.addEventListener('submit', function(event) {
            event.preventDefault();
            submitFeedback();
        });
    }
    
    // 初始化评分系统
    function initRatingSystem() {
        ratingInputs = document.querySelectorAll('input[name="rating"]');
        
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                // 清除所有星级高亮
                ratingInputs.forEach(inp => {
                    inp.parentElement.classList.remove('selected');
                });
                
                // 高亮当前选中的星级及之前的星级
                const rating = parseInt(this.value);
                for (let i = 0; i < rating; i++) {
                    ratingInputs[i].parentElement.classList.add('selected');
                }
            });
        });
    }
    
    // 提交反馈
    function submitFeedback() {
        // 获取表单数据
        const type = document.getElementById('feedback-type').value;
        const content = document.getElementById('feedback-content').value;
        const contact = document.getElementById('feedback-contact').value;
        let rating = 0;
        
        // 获取选中的评分
        ratingInputs.forEach(input => {
            if (input.checked) {
                rating = parseInt(input.value);
            }
        });
        
        // 验证必填字段
        if (!type || !content) {
            showMessage('请填写所有必填字段', 'error');
            return;
        }
        
        // 禁用提交按钮，防止重复提交
        submitButton.disabled = true;
        submitButton.textContent = '提交中...';
        
        // 准备提交数据
        const feedbackData = {
            type: type,
            content: content,
            rating: rating,
            contact: contact,
            url: window.location.href,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent
        };
        
        // 发送数据到服务器
        fetch(config.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(feedbackData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('服务器响应错误');
            }
            return response.json();
        })
        .then(data => {
            // 成功提交
            showMessage('感谢您的反馈！', 'success');
            resetForm();
        })
        .catch(error => {
            console.error('提交反馈失败:', error);
            
            // 尝试本地存储
            try {
                const storedFeedback = JSON.parse(localStorage.getItem('pendingFeedback')) || [];
                storedFeedback.push(feedbackData);
                localStorage.setItem('pendingFeedback', JSON.stringify(storedFeedback));
                showMessage('当前无法连接服务器，您的反馈已保存，将在下次连接时提交', 'warning');
                resetForm();
            } catch (storageError) {
                showMessage('提交失败，请稍后再试', 'error');
            }
        })
        .finally(() => {
            // 恢复提交按钮
            submitButton.disabled = false;
            submitButton.textContent = '提交反馈';
        });
    }
    
    // 重置表单
    function resetForm() {
        feedbackForm.reset();
        
        // 重置评分星级
        ratingInputs.forEach(input => {
            input.parentElement.classList.remove('selected');
        });
        
        // 延迟关闭面板
        setTimeout(() => {
            feedbackPanel.classList.remove('active');
        }, 2000);
    }
    
    // 显示消息
    function showMessage(message, type) {
        // 创建消息元素
        const messageElement = document.createElement('div');
        messageElement.className = `feedback-message ${type}`;
        messageElement.textContent = message;
        messageElement.setAttribute('role', 'alert');
        
        // 添加到面板
        feedbackPanel.appendChild(messageElement);
        
        // 自动消失
        setTimeout(() => {
            messageElement.classList.add('fadeout');
            
            setTimeout(() => {
                messageElement.remove();
            }, 500);
        }, 3000);
    }
    
    // 检查是否有未提交的反馈
    function checkPendingFeedback() {
        try {
            const pendingFeedback = JSON.parse(localStorage.getItem('pendingFeedback'));
            
            if (pendingFeedback && pendingFeedback.length > 0) {
                // 尝试提交
                Promise.all(pendingFeedback.map(feedback => 
                    fetch(config.apiEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(feedback)
                    })
                ))
                .then(() => {
                    // 清空本地存储
                    localStorage.removeItem('pendingFeedback');
                    console.log('成功提交本地存储的反馈');
                })
                .catch(error => {
                    console.error('无法提交本地存储的反馈:', error);
                });
            }
        } catch (error) {
            console.error('检查本地存储的反馈时出错:', error);
        }
    }
    
    // DOM 加载完成后初始化
    window.addEventListener('DOMContentLoaded', function() {
        initFeedbackSystem();
        checkPendingFeedback();
    });
    
})();
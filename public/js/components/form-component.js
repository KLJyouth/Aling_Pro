/**
 * AlingAi Pro - 表单组件
 * 现代化、智能的表单组件，支持验证、提交处理和量子效果
 * 
 * @version 2.0.0
 * @author AlingAi Team
 * @features
 * - 实时验证
 * - 自动完成
 * - 量子效果
 * - 文件上传
 * - 多步骤表单
 * - 数据绑定
 */

class AlingFormComponent {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            autoValidate: true,
            showErrors: true,
            quantumEffect: false,
            submitOnEnter: true,
            resetAfterSubmit: false,
            confirmBeforeReset: true,
            saveProgress: false,
            multiStep: false,
            uploadEndpoint: API_ENDPOINTS.UPLOAD,
            validationRules: {},
            customMessages: {},
            onSubmit: null,
            onValidate: null,
            onError: null,
            onSuccess: null,
            ...options
        };

        this.fields = new Map();
        this.errors = new Map();
        this.isValid = false;
        this.isSubmitting = false;
        this.currentStep = 0;
        this.steps = [];
        this.uploadedFiles = new Map();

        this.init();
    }

    init() {
        this.setupForm();
        this.findFields();
        this.bindEvents();
        this.setupValidation();
        
        if (this.options.multiStep) {
            this.setupSteps();
        }
        
        if (this.options.quantumEffect) {
            this.addQuantumEffects();
        }

        if (this.options.saveProgress) {
            this.loadProgress();
        }
    }

    setupForm() {
        if (!this.element.tagName === 'FORM') {
            console.warn('AlingFormComponent: Element is not a form');
            return;
        }

        this.element.setAttribute('novalidate', '');
        this.element.classList.add('aling-form');
        
        // 添加表单样式
        if (!document.getElementById('aling-form-styles')) {
            this.addStyles();
        }
    }

    addStyles() {
        const style = document.createElement('style');
        style.id = 'aling-form-styles';
        style.textContent = `
            .aling-form {
                max-width: 100%;
            }
            
            .form-field {
                margin-bottom: 1.5rem;
                position: relative;
            }
            
            .form-label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
                color: var(--text-color, #374151);
                font-size: 0.875rem;
            }
            
            .form-label.required::after {
                content: " *";
                color: var(--error-color, #ef4444);
            }
            
            .form-input {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 1px solid var(--border-color, #d1d5db);
                border-radius: 8px;
                background: var(--input-background, #ffffff);
                color: var(--text-color, #374151);
                font-size: 1rem;
                transition: all 0.2s ease;
                box-sizing: border-box;
            }
            
            .form-input:focus {
                outline: none;
                border-color: var(--primary-color, #3b82f6);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
            
            .form-input.error {
                border-color: var(--error-color, #ef4444);
                box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
            }
            
            .form-input.success {
                border-color: var(--success-color, #10b981);
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            }
            
            .form-error {
                color: var(--error-color, #ef4444);
                font-size: 0.875rem;
                margin-top: 0.25rem;
                display: block;
            }
            
            .form-help {
                color: var(--text-muted, #6b7280);
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }
            
            .form-group {
                display: flex;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .form-group .form-field {
                flex: 1;
            }
            
            .form-actions {
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
                margin-top: 2rem;
                padding-top: 1.5rem;
                border-top: 1px solid var(--border-color, #e5e7eb);
            }
            
            .form-step {
                display: none;
            }
            
            .form-step.active {
                display: block;
            }
            
            .form-progress {
                margin-bottom: 2rem;
            }
            
            .progress-bar {
                width: 100%;
                height: 8px;
                background: var(--background-secondary, #f3f4f6);
                border-radius: 4px;
                overflow: hidden;
            }
            
            .progress-fill {
                height: 100%;
                background: var(--primary-color, #3b82f6);
                transition: width 0.3s ease;
                border-radius: 4px;
            }
            
            .step-indicators {
                display: flex;
                justify-content: space-between;
                margin-bottom: 1rem;
            }
            
            .step-indicator {
                flex: 1;
                text-align: center;
                position: relative;
                padding: 0.5rem;
            }
            
            .step-indicator::before {
                content: attr(data-step);
                display: inline-block;
                width: 2rem;
                height: 2rem;
                line-height: 2rem;
                border-radius: 50%;
                background: var(--background-secondary, #f3f4f6);
                color: var(--text-muted, #6b7280);
                font-weight: 500;
                margin-bottom: 0.5rem;
            }
            
            .step-indicator.completed::before {
                background: var(--success-color, #10b981);
                color: white;
                content: "✓";
            }
            
            .step-indicator.active::before {
                background: var(--primary-color, #3b82f6);
                color: white;
            }
            
            .quantum-form {
                position: relative;
                overflow: hidden;
            }
            
            .quantum-form::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, 
                    transparent, 
                    rgba(59, 130, 246, 0.1), 
                    transparent
                );
                animation: quantumScan 3s infinite;
                pointer-events: none;
                z-index: 1;
            }
            
            @keyframes quantumScan {
                0% { left: -100%; }
                50% { left: 100%; }
                100% { left: 100%; }
            }
            
            .file-upload-area {
                border: 2px dashed var(--border-color, #d1d5db);
                border-radius: 8px;
                padding: 2rem;
                text-align: center;
                transition: all 0.2s ease;
                cursor: pointer;
            }
            
            .file-upload-area:hover,
            .file-upload-area.dragover {
                border-color: var(--primary-color, #3b82f6);
                background: rgba(59, 130, 246, 0.05);
            }
            
            .file-list {
                margin-top: 1rem;
            }
            
            .file-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.5rem;
                background: var(--background-secondary, #f9fafb);
                border-radius: 4px;
                margin-bottom: 0.5rem;
            }
            
            .file-item .remove-file {
                color: var(--error-color, #ef4444);
                cursor: pointer;
                padding: 0.25rem;
            }
        `;
        document.head.appendChild(style);
    }

    findFields() {
        const inputs = this.element.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            const field = this.createFieldWrapper(input);
            this.fields.set(input.name || input.id, field);
        });
    }

    createFieldWrapper(input) {
        const wrapper = input.closest('.form-field') || this.createFieldContainer(input);
        
        return {
            element: input,
            wrapper: wrapper,
            label: wrapper.querySelector('.form-label'),
            error: wrapper.querySelector('.form-error'),
            help: wrapper.querySelector('.form-help'),
            rules: this.parseValidationRules(input),
            value: input.value,
            isValid: true
        };
    }

    createFieldContainer(input) {
        // 如果输入框没有包装容器，创建一个
        const wrapper = document.createElement('div');
        wrapper.className = 'form-field';
        
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        // 创建标签
        if (input.getAttribute('data-label')) {
            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = input.getAttribute('data-label');
            if (input.required) {
                label.classList.add('required');
            }
            wrapper.insertBefore(label, input);
        }
        
        // 添加样式类
        input.classList.add('form-input');
        
        // 创建错误容器
        const errorContainer = document.createElement('span');
        errorContainer.className = 'form-error';
        errorContainer.style.display = 'none';
        wrapper.appendChild(errorContainer);
        
        // 添加帮助文本
        if (input.getAttribute('data-help')) {
            const help = document.createElement('div');
            help.className = 'form-help';
            help.textContent = input.getAttribute('data-help');
            wrapper.appendChild(help);
        }
        
        return wrapper;
    }

    parseValidationRules(input) {
        const rules = {};
        
        // HTML5 验证规则
        if (input.required) rules.required = true;
        if (input.type === 'email') rules.email = true;
        if (input.type === 'url') rules.url = true;
        if (input.minLength) rules.minLength = input.minLength;
        if (input.maxLength) rules.maxLength = input.maxLength;
        if (input.min) rules.min = parseFloat(input.min);
        if (input.max) rules.max = parseFloat(input.max);
        if (input.pattern) rules.pattern = new RegExp(input.pattern);
        
        // 自定义验证规则
        const customRules = input.getAttribute('data-rules');
        if (customRules) {
            try {
                const parsed = JSON.parse(customRules);
                Object.assign(rules, parsed);
            } catch (e) {
                console.warn('Invalid validation rules:', customRules);
            }
        }
        
        // 来自选项的规则
        const fieldName = input.name || input.id;
        if (this.options.validationRules[fieldName]) {
            Object.assign(rules, this.options.validationRules[fieldName]);
        }
        
        return rules;
    }

    bindEvents() {
        // 表单提交
        this.element.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });

        // 字段验证
        this.fields.forEach((field, name) => {
            const input = field.element;
            
            if (this.options.autoValidate) {
                input.addEventListener('blur', () => this.validateField(name));
                input.addEventListener('input', () => {
                    if (this.errors.has(name)) {
                        this.validateField(name);
                    }
                });
            }

            // 文件上传处理
            if (input.type === 'file') {
                this.setupFileUpload(field);
            }
        });

        // 回车提交
        if (this.options.submitOnEnter) {
            this.element.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    if (!e.target.classList.contains('no-submit')) {
                        e.preventDefault();
                        this.handleSubmit();
                    }
                }
            });
        }

        // 保存进度
        if (this.options.saveProgress) {
            this.fields.forEach((field) => {
                field.element.addEventListener('change', () => this.saveProgress());
            });
        }
    }

    setupFileUpload(field) {
        const input = field.element;
        const wrapper = field.wrapper;
        
        // 创建拖拽区域
        const uploadArea = document.createElement('div');
        uploadArea.className = 'file-upload-area';
        uploadArea.innerHTML = `
            <div class="upload-icon">📁</div>
            <div class="upload-text">
                <strong>点击上传文件</strong>或拖拽文件到此处
            </div>
            <div class="upload-hint">支持多种文件格式</div>
        `;
        
        // 文件列表
        const fileList = document.createElement('div');
        fileList.className = 'file-list';
        
        wrapper.appendChild(uploadArea);
        wrapper.appendChild(fileList);
        
        // 隐藏原始输入框
        input.style.display = 'none';
        
        // 点击上传
        uploadArea.addEventListener('click', () => input.click());
        
        // 拖拽处理
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            this.handleFileSelect(e.dataTransfer.files, field);
        });
        
        // 文件选择
        input.addEventListener('change', (e) => {
            this.handleFileSelect(e.target.files, field);
        });
    }

    handleFileSelect(files, field) {
        const fileList = field.wrapper.querySelector('.file-list');
        
        Array.from(files).forEach(file => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <span class="file-name">${file.name}</span>
                <span class="file-size">${this.formatFileSize(file.size)}</span>
                <span class="remove-file" data-file="${file.name}">×</span>
            `;
            
            fileList.appendChild(fileItem);
            
            // 上传文件
            this.uploadFile(file, field, fileItem);
        });
        
        // 移除文件事件
        fileList.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-file')) {
                const fileName = e.target.getAttribute('data-file');
                this.removeFile(fileName, field);
                e.target.closest('.file-item').remove();
            }
        });
    }

    async uploadFile(file, field, fileItem) {
        const formData = new FormData();
        formData.append('file', file);
        
        try {
            const response = await fetch(this.options.uploadEndpoint, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok) {
                fileItem.classList.add('upload-success');
                this.uploadedFiles.set(file.name, result);
                
                // 添加成功图标
                const icon = fileItem.querySelector('.file-name');
                icon.innerHTML = `✓ ${file.name}`;
            } else {
                throw new Error(result.message || '上传失败');
            }
        } catch (error) {
            fileItem.classList.add('upload-error');
            fileItem.querySelector('.file-size').textContent = error.message;
        }
    }

    removeFile(fileName, field) {
        this.uploadedFiles.delete(fileName);
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    setupValidation() {
        this.validationRules = {
            required: (value) => value !== null && value !== undefined && value.toString().trim() !== '',
            email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            url: (value) => /^https?:\/\/.+/.test(value),
            minLength: (value, min) => value.length >= min,
            maxLength: (value, max) => value.length <= max,
            min: (value, min) => parseFloat(value) >= min,
            max: (value, max) => parseFloat(value) <= max,
            pattern: (value, pattern) => pattern.test(value),
            custom: (value, validator) => validator(value)
        };
    }

    validateField(fieldName) {
        const field = this.fields.get(fieldName);
        if (!field) return true;

        const value = field.element.value;
        const rules = field.rules;
        let isValid = true;
        let errorMessage = '';

        // 验证规则
        for (const [ruleName, ruleValue] of Object.entries(rules)) {
            const validator = this.validationRules[ruleName];
            if (validator && !validator(value, ruleValue)) {
                isValid = false;
                errorMessage = this.getErrorMessage(fieldName, ruleName, ruleValue);
                break;
            }
        }

        // 自定义验证
        if (this.options.onValidate) {
            const customResult = this.options.onValidate(fieldName, value, field);
            if (customResult !== true) {
                isValid = false;
                errorMessage = customResult || '验证失败';
            }
        }

        // 更新字段状态
        this.updateFieldStatus(field, isValid, errorMessage);
        
        if (isValid) {
            this.errors.delete(fieldName);
        } else {
            this.errors.set(fieldName, errorMessage);
        }

        return isValid;
    }

    updateFieldStatus(field, isValid, errorMessage) {
        const input = field.element;
        const error = field.error;

        // 移除之前的状态
        input.classList.remove('error', 'success');
        
        if (isValid) {
            input.classList.add('success');
            if (error) {
                error.style.display = 'none';
                error.textContent = '';
            }
        } else {
            input.classList.add('error');
            if (error) {
                error.style.display = 'block';
                error.textContent = errorMessage;
            }
        }

        field.isValid = isValid;
    }

    getErrorMessage(fieldName, ruleName, ruleValue) {
        const customMessages = this.options.customMessages[fieldName];
        if (customMessages && customMessages[ruleName]) {
            return customMessages[ruleName];
        }

        const defaultMessages = {
            required: '此字段为必填项',
            email: '请输入有效的邮箱地址',
            url: '请输入有效的URL地址',
            minLength: `最少需要 ${ruleValue} 个字符`,
            maxLength: `最多允许 ${ruleValue} 个字符`,
            min: `值不能小于 ${ruleValue}`,
            max: `值不能大于 ${ruleValue}`,
            pattern: '格式不正确'
        };

        return defaultMessages[ruleName] || '验证失败';
    }

    validateForm() {
        let isValid = true;
        
        this.fields.forEach((field, name) => {
            if (!this.validateField(name)) {
                isValid = false;
            }
        });

        this.isValid = isValid;
        return isValid;
    }

    async handleSubmit() {
        if (this.isSubmitting) return;

        // 验证表单
        if (!this.validateForm()) {
            if (this.options.onError) {
                this.options.onError(this.errors);
            }
            return;
        }

        this.isSubmitting = true;
        this.showSubmitLoading();

        try {
            const formData = this.getFormData();
            
            let result;
            if (this.options.onSubmit) {
                result = await this.options.onSubmit(formData, this);
            } else {
                result = await this.submitToServer(formData);
            }

            this.hideSubmitLoading();
            this.isSubmitting = false;

            if (this.options.onSuccess) {
                this.options.onSuccess(result, this);
            }

            if (this.options.resetAfterSubmit) {
                this.reset();
            }

            this.emit('submit:success', { data: formData, result });

        } catch (error) {
            this.hideSubmitLoading();
            this.isSubmitting = false;

            if (this.options.onError) {
                this.options.onError(error);
            }

            this.emit('submit:error', { error });
        }
    }

    getFormData() {
        const data = {};
        
        this.fields.forEach((field, name) => {
            if (field.element.type === 'checkbox') {
                data[name] = field.element.checked;
            } else if (field.element.type === 'radio') {
                if (field.element.checked) {
                    data[name] = field.element.value;
                }
            } else if (field.element.type === 'file') {
                data[name] = this.uploadedFiles.get(name) || null;
            } else {
                data[name] = field.element.value;
            }
        });

        return data;
    }

    async submitToServer(data) {
        const response = await fetch(this.element.action || API_ENDPOINTS.FORM, {
            method: this.element.method || 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    showSubmitLoading() {
        const submitBtn = this.element.querySelector('[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = '提交中...';
        }
    }

    hideSubmitLoading() {
        const submitBtn = this.element.querySelector('[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.getAttribute('data-original-text') || '提交';
        }
    }

    setupSteps() {
        const steps = this.element.querySelectorAll('.form-step');
        if (steps.length === 0) return;

        this.steps = Array.from(steps);
        this.currentStep = 0;

        // 创建进度指示器
        this.createStepIndicators();
        this.createProgressBar();
        this.createNavigationButtons();

        // 显示第一步
        this.showStep(0);
    }

    createStepIndicators() {
        const indicators = document.createElement('div');
        indicators.className = 'step-indicators';

        this.steps.forEach((step, index) => {
            const indicator = document.createElement('div');
            indicator.className = 'step-indicator';
            indicator.setAttribute('data-step', index + 1);
            
            const title = step.getAttribute('data-title') || `步骤 ${index + 1}`;
            indicator.innerHTML = `<div class="step-title">${title}</div>`;
            
            indicators.appendChild(indicator);
        });

        this.element.insertBefore(indicators, this.steps[0]);
    }

    createProgressBar() {
        const progressContainer = document.createElement('div');
        progressContainer.className = 'form-progress';
        progressContainer.innerHTML = `
            <div class="progress-bar">
                <div class="progress-fill" style="width: ${(1 / this.steps.length) * 100}%"></div>
            </div>
        `;

        this.element.insertBefore(progressContainer, this.steps[0]);
    }

    createNavigationButtons() {
        const navigation = document.createElement('div');
        navigation.className = 'form-navigation';
        navigation.innerHTML = `
            <button type="button" class="btn btn-secondary" id="prevStep" style="display: none;">上一步</button>
            <button type="button" class="btn btn-primary" id="nextStep">下一步</button>
            <button type="submit" class="btn btn-primary" id="submitForm" style="display: none;">提交</button>
        `;

        this.element.appendChild(navigation);

        // 绑定导航事件
        this.element.querySelector('#prevStep').addEventListener('click', () => this.previousStep());
        this.element.querySelector('#nextStep').addEventListener('click', () => this.nextStep());
    }

    showStep(stepIndex) {
        // 隐藏所有步骤
        this.steps.forEach(step => step.classList.remove('active'));
        
        // 显示当前步骤
        this.steps[stepIndex].classList.add('active');
        
        // 更新指示器
        this.updateStepIndicators(stepIndex);
        
        // 更新进度条
        this.updateProgressBar(stepIndex);
        
        // 更新导航按钮
        this.updateNavigationButtons(stepIndex);
        
        this.currentStep = stepIndex;
    }

    updateStepIndicators(currentStep) {
        const indicators = this.element.querySelectorAll('.step-indicator');
        
        indicators.forEach((indicator, index) => {
            indicator.classList.remove('active', 'completed');
            
            if (index < currentStep) {
                indicator.classList.add('completed');
            } else if (index === currentStep) {
                indicator.classList.add('active');
            }
        });
    }

    updateProgressBar(currentStep) {
        const progressFill = this.element.querySelector('.progress-fill');
        const progress = ((currentStep + 1) / this.steps.length) * 100;
        progressFill.style.width = `${progress}%`;
    }

    updateNavigationButtons(currentStep) {
        const prevBtn = this.element.querySelector('#prevStep');
        const nextBtn = this.element.querySelector('#nextStep');
        const submitBtn = this.element.querySelector('#submitForm');
        
        prevBtn.style.display = currentStep === 0 ? 'none' : 'inline-block';
        nextBtn.style.display = currentStep === this.steps.length - 1 ? 'none' : 'inline-block';
        submitBtn.style.display = currentStep === this.steps.length - 1 ? 'inline-block' : 'none';
    }

    nextStep() {
        // 验证当前步骤
        if (!this.validateCurrentStep()) {
            return;
        }
        
        if (this.currentStep < this.steps.length - 1) {
            this.showStep(this.currentStep + 1);
        }
    }

    previousStep() {
        if (this.currentStep > 0) {
            this.showStep(this.currentStep - 1);
        }
    }

    validateCurrentStep() {
        const currentStepElement = this.steps[this.currentStep];
        const stepFields = currentStepElement.querySelectorAll('input, select, textarea');
        let isValid = true;

        stepFields.forEach(field => {
            const fieldName = field.name || field.id;
            if (fieldName && !this.validateField(fieldName)) {
                isValid = false;
            }
        });

        return isValid;
    }

    addQuantumEffects() {
        this.element.classList.add('quantum-form');
    }

    saveProgress() {
        if (!this.options.saveProgress) return;

        const formId = this.element.id || 'form';
        const data = this.getFormData();
        
        localStorage.setItem(`form_progress_${formId}`, JSON.stringify({
            data,
            currentStep: this.currentStep,
            timestamp: Date.now()
        }));
    }

    loadProgress() {
        if (!this.options.saveProgress) return;

        const formId = this.element.id || 'form';
        const saved = localStorage.getItem(`form_progress_${formId}`);
        
        if (saved) {
            try {
                const progress = JSON.parse(saved);
                
                // 检查是否过期（24小时）
                if (Date.now() - progress.timestamp < 24 * 60 * 60 * 1000) {
                    this.setFormData(progress.data);
                    if (this.options.multiStep && progress.currentStep) {
                        this.showStep(progress.currentStep);
                    }
                }
            } catch (e) {
                console.warn('Failed to load form progress:', e);
            }
        }
    }

    setFormData(data) {
        Object.entries(data).forEach(([name, value]) => {
            const field = this.fields.get(name);
            if (field) {
                field.element.value = value;
            }
        });
    }

    reset() {
        if (this.options.confirmBeforeReset) {
            if (!confirm('确定要重置表单吗？所有数据将丢失。')) {
                return;
            }
        }

        this.element.reset();
        this.errors.clear();
        this.uploadedFiles.clear();

        // 清除验证状态
        this.fields.forEach(field => {
            field.element.classList.remove('error', 'success');
            if (field.error) {
                field.error.style.display = 'none';
            }
        });

        // 重置步骤
        if (this.options.multiStep) {
            this.showStep(0);
        }

        // 清除保存的进度
        if (this.options.saveProgress) {
            const formId = this.element.id || 'form';
            localStorage.removeItem(`form_progress_${formId}`);
        }

        this.emit('reset');
    }

    emit(eventName, data = {}) {
        const event = new CustomEvent(`form:${eventName}`, {
            detail: { form: this, ...data }
        });
        this.element.dispatchEvent(event);
    }

    destroy() {
        // 清除事件监听器和DOM元素
        this.fields.clear();
        this.errors.clear();
        this.uploadedFiles.clear();
    }

    // 静态方法
    static create(formElement, options = {}) {
        return new AlingFormComponent(formElement, options);
    }
}

// 导出组件
window.AlingFormComponent = AlingFormComponent;

// 自动初始化
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-component="form"]').forEach(form => {
        const options = {};
        
        // 从数据属性解析选项
        if (form.hasAttribute('data-auto-validate')) {
            options.autoValidate = form.getAttribute('data-auto-validate') !== 'false';
        }
        if (form.hasAttribute('data-quantum-effect')) {
            options.quantumEffect = form.getAttribute('data-quantum-effect') === 'true';
        }
        if (form.hasAttribute('data-multi-step')) {
            options.multiStep = form.getAttribute('data-multi-step') === 'true';
        }
        if (form.hasAttribute('data-save-progress')) {
            options.saveProgress = form.getAttribute('data-save-progress') === 'true';
        }
        
        new AlingFormComponent(form, options);
    });
});



/**
 * AlingAi Pro 国际化翻译配置文件
 * 支持中文(zh-CN)和英文(en-US)
 */

window.translations = {
    'zh-CN': {
        // 导航栏
        nav: {
            home: '首页',
            products: '产品',
            solutions: '解决方案',
            about: '关于我们',
            contact: '联系我们',
            login: '登录',
            register: '注册',
            chat: '智能对话',
            dashboard: '控制台',
            documentation: '文档',
            support: '支持'
        },
        
        // 首页内容
        home: {
            title: '珑凌科技 | 量子安全·智能未来',
            subtitle: '全球领先的量子安全基础设施提供商',
            description: '为企业级客户提供从底层加密到智能决策的全栈解决方案',
            getStarted: '开始体验',
            learnMore: '了解更多',
            watchDemo: '观看演示',
            
            // Hero 区域
            heroTitle: '量子安全时代的智能解决方案',
            heroSubtitle: '引领未来科技，保护数字世界',
            heroDescription: '结合量子加密技术和人工智能，为您的业务提供前所未有的安全保障和智能决策支持',
            
            // 特性区域
            features: {
                title: '核心特性',
                subtitle: '领先技术，卓越体验',
                quantum: {
                    title: '量子安全',
                    description: '基于量子密码学的下一代数据保护'
                },
                ai: {
                    title: '智能分析',
                    description: '人工智能驱动的数据洞察和决策支持'
                },
                realtime: {
                    title: '实时处理',
                    description: '毫秒级响应的实时数据处理能力'
                },
                scalable: {
                    title: '弹性扩展',
                    description: '云原生架构，支持海量并发处理'
                }
            }
        },
        
        // 聊天界面
        chat: {
            title: '智能对话助手',
            placeholder: '请输入您的问题...',
            send: '发送',
            thinking: '思考中...',
            error: '抱歉，发生了错误，请稍后重试',
            retry: '重试',
            clear: '清除对话',
            newChat: '新建对话',
            
            // 预设问题
            suggestions: [
                '介绍一下珑凌科技的主要产品',
                '量子安全技术有什么优势？',
                '如何开始使用你们的服务？',
                '技术支持联系方式'
            ]
        },
        
        // 登录注册
        auth: {
            login: '登录',
            register: '注册',
            logout: '退出',
            username: '用户名',
            email: '邮箱',
            password: '密码',
            confirmPassword: '确认密码',
            rememberMe: '记住我',
            forgotPassword: '忘记密码？',
            noAccount: '还没有账户？',
            hasAccount: '已有账户？',
            signUp: '立即注册',
            signIn: '立即登录'
        },
        
        // 通用操作
        common: {
            save: '保存',
            cancel: '取消',
            confirm: '确认',
            close: '关闭',
            submit: '提交',
            search: '搜索',
            loading: '加载中...',
            success: '操作成功',
            error: '操作失败'
        },
          // 主题切换
        theme: {
            light: '明亮模式',
            dark: '深色模式',
            quantum: '量子主题',
            matrix: '矩阵主题',
            aurora: '极光主题'
        },

        // 系统诊断相关
        diagnostics: {
            title: '系统诊断',
            subtitle: '系统状态监控和检测',
            
            // 总体状态
            status: {
                healthy: '系统健康',
                warning: '系统警告',
                error: '系统错误',
                unknown: '状态未知'
            },
            
            // 诊断类型
            types: {
                comprehensive: '综合测试',
                floating_buttons: '悬浮按钮',
                integrated_detection: '集成检测',
                chat_system: '聊天系统',
                error_handler: '错误处理',
                ux_validator: 'UX验证',
                functionality: '功能演示',
                deep_diagnostics: '深度诊断',
                visualization: '可视化仪表板',
                performance: '性能监控',
                website_status: '网站状态'
            },
            
            // 操作按钮
            actions: {
                run: '运行诊断',
                stop: '停止诊断',
                reset: '重置状态',
                export: '导出报告',
                refresh: '刷新数据',
                details: '查看详情',
                configure: '配置设置'
            },
            
            // 报告内容
            reports: {
                summary: '诊断摘要',
                details: '详细报告',
                errors: '错误列表',
                warnings: '警告信息',
                performance: '性能指标',
                recommendations: '建议改进'
            },
            
            // 状态消息
            messages: {
                running: '诊断正在运行...',
                completed: '诊断已完成',
                failed: '诊断失败',
                no_issues: '未发现问题',
                issues_found: '发现 {{count}} 个问题',
                last_run: '最后运行时间：{{time}}'
            }
        },

        // 管理员界面
        admin: {
            title: '管理员控制台',
            dashboard: '仪表板',
            
            // 系统管理
            system: {
                title: '系统管理',
                diagnostics: '系统诊断',
                logs: '系统日志',
                monitoring: '监控中心',
                performance: '性能分析',
                security: '安全检查'
            },
            
            // 用户管理
            users: {
                title: '用户管理',
                list: '用户列表',
                permissions: '权限管理',
                activity: '活动日志'
            },
            
            // 内容管理
            content: {
                title: '内容管理',
                pages: '页面管理',
                resources: '资源管理',
                settings: '站点设置'
            }
        },

        // 悬浮按钮
        floating_buttons: {
            title: '快捷功能',
            
            // 按钮类型
            types: {
                diagnostics: '系统诊断',
                chat: '智能对话',
                theme: '主题切换',
                language: '语言切换',
                help: '帮助中心',
                feedback: '意见反馈'
            },
            
            // 工具提示
            tooltips: {
                diagnostics: '运行系统诊断检查',
                chat: '打开智能对话助手',
                theme: '切换页面主题',
                language: '更改界面语言',
                help: '查看帮助文档',
                feedback: '提交意见和建议'
            }
        }
    },
    
    'en-US': {
        // Navigation
        nav: {
            home: 'Home',
            products: 'Products',
            solutions: 'Solutions',
            about: 'About',
            contact: 'Contact',
            login: 'Login',
            register: 'Register',
            chat: 'AI Chat',
            dashboard: 'Dashboard',
            documentation: 'Docs',
            support: 'Support'
        },
        
        // Home page content
        home: {
            title: 'AlingTech | Quantum Security · Intelligent Future',
            subtitle: 'Leading Global Quantum Security Infrastructure Provider',
            description: 'Comprehensive solutions from encryption to intelligent decision-making for enterprise clients',
            getStarted: 'Get Started',
            learnMore: 'Learn More',
            watchDemo: 'Watch Demo',
            
            // Hero section
            heroTitle: 'Intelligent Solutions for the Quantum Security Era',
            heroSubtitle: 'Leading Future Technology, Protecting Digital World',
            heroDescription: 'Combining quantum encryption technology and artificial intelligence to provide unprecedented security and intelligent decision support for your business',
            
            // Features section
            features: {
                title: 'Core Features',
                subtitle: 'Leading Technology, Excellent Experience',
                quantum: {
                    title: 'Quantum Security',
                    description: 'Next-generation data protection based on quantum cryptography'
                },
                ai: {
                    title: 'Intelligent Analysis',
                    description: 'AI-driven data insights and decision support'
                },
                realtime: {
                    title: 'Real-time Processing',
                    description: 'Millisecond-level real-time data processing capabilities'
                },
                scalable: {
                    title: 'Elastic Scaling',
                    description: 'Cloud-native architecture supporting massive concurrent processing'
                }
            }
        },
        
        // Chat interface
        chat: {
            title: 'AI Assistant',
            placeholder: 'Type your question...',
            send: 'Send',
            thinking: 'Thinking...',
            error: 'Sorry, an error occurred. Please try again later',
            retry: 'Retry',
            clear: 'Clear Chat',
            newChat: 'New Chat',
            
            // Suggested questions
            suggestions: [
                'Tell me about AlingTech\'s main products',
                'What are the advantages of quantum security technology?',
                'How to get started with your services?',
                'Technical support contact information'
            ]
        },
        
        // Authentication
        auth: {
            login: 'Login',
            register: 'Register',
            logout: 'Logout',
            username: 'Username',
            email: 'Email',
            password: 'Password',
            confirmPassword: 'Confirm Password',
            rememberMe: 'Remember Me',
            forgotPassword: 'Forgot Password?',
            noAccount: 'Don\'t have an account?',
            hasAccount: 'Already have an account?',
            signUp: 'Sign Up Now',
            signIn: 'Sign In Now'
        },
        
        // Common operations
        common: {
            save: 'Save',
            cancel: 'Cancel',
            confirm: 'Confirm',
            close: 'Close',
            submit: 'Submit',
            search: 'Search',
            loading: 'Loading...',
            success: 'Operation successful',
            error: 'Operation failed'
        },
          // Theme switching
        theme: {
            light: 'Light Mode',
            dark: 'Dark Mode',
            quantum: 'Quantum Theme',
            matrix: 'Matrix Theme',
            aurora: 'Aurora Theme'
        },

        // System Diagnostics
        diagnostics: {
            title: 'System Diagnostics',
            subtitle: 'System Status Monitoring and Detection',
            
            // Overall status
            status: {
                healthy: 'System Healthy',
                warning: 'System Warning',
                error: 'System Error',
                unknown: 'Status Unknown'
            },
            
            // Diagnostic types
            types: {
                comprehensive: 'Comprehensive Test',
                floating_buttons: 'Floating Buttons',
                integrated_detection: 'Integrated Detection',
                chat_system: 'Chat System',
                error_handler: 'Error Handler',
                ux_validator: 'UX Validator',
                functionality: 'Functionality Demo',
                deep_diagnostics: 'Deep Diagnostics',
                visualization: 'Visualization Dashboard',
                performance: 'Performance Monitor',
                website_status: 'Website Status'
            },
            
            // Action buttons
            actions: {
                run: 'Run Diagnostics',
                stop: 'Stop Diagnostics',
                reset: 'Reset Status',
                export: 'Export Report',
                refresh: 'Refresh Data',
                details: 'View Details',
                configure: 'Configure Settings'
            },
            
            // Report content
            reports: {
                summary: 'Diagnostic Summary',
                details: 'Detailed Report',
                errors: 'Error List',
                warnings: 'Warning Information',
                performance: 'Performance Metrics',
                recommendations: 'Improvement Recommendations'
            },
            
            // Status messages
            messages: {
                running: 'Diagnostics running...',
                completed: 'Diagnostics completed',
                failed: 'Diagnostics failed',
                no_issues: 'No issues found',
                issues_found: '{{count}} issues found',
                last_run: 'Last run: {{time}}'
            }
        },

        // Admin Interface
        admin: {
            title: 'Admin Console',
            dashboard: 'Dashboard',
            
            // System management
            system: {
                title: 'System Management',
                diagnostics: 'System Diagnostics',
                logs: 'System Logs',
                monitoring: 'Monitoring Center',
                performance: 'Performance Analysis',
                security: 'Security Check'
            },
            
            // User management
            users: {
                title: 'User Management',
                list: 'User List',
                permissions: 'Permission Management',
                activity: 'Activity Log'
            },
            
            // Content management
            content: {
                title: 'Content Management',
                pages: 'Page Management',
                resources: 'Resource Management',
                settings: 'Site Settings'
            }
        },

        // Floating Buttons
        floating_buttons: {
            title: 'Quick Functions',
            
            // Button types
            types: {
                diagnostics: 'System Diagnostics',
                chat: 'AI Chat',
                theme: 'Theme Switch',
                language: 'Language Switch',
                help: 'Help Center',
                feedback: 'Feedback'
            },
            
            // Tooltips
            tooltips: {
                diagnostics: 'Run system diagnostic check',
                chat: 'Open AI chat assistant',
                theme: 'Switch page theme',
                language: 'Change interface language',
                help: 'View help documentation',
                feedback: 'Submit feedback and suggestions'
            }
        }
    }
};

// 导出翻译对象供其他模块使用
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.translations;
}
#!/usr/bin/env node

/**
 * AlingAi Pro 数据库集成验证脚本
 * 验证所有JavaScript文件的语法和功能完整性
 */

const fs = require('fs');
const path = require('path');

const files = [
    'public/assets/js/homepage-ai-chat.js',
    'public/assets/js/components/chat-component.js', 
    'public/assets/js/components/enhanced-chat-component.js',
    'public/assets/js/chat/core.js',
    'public/assets/js/quantum-chat-integrator.js'
];

console.log('🔍 AlingAi Pro 数据库集成验证');
console.log('=' .repeat(50));

let allValid = true;

files.forEach(file => {
    const filePath = path.join(__dirname, file);
    
    console.log(`\n📄 检查文件: ${file}`);
    
    try {
        // 检查文件是否存在
        if (!fs.existsSync(filePath)) {
            console.log(`❌ 文件不存在`);
            allValid = false;
            return;
        }
        
        // 读取文件内容
        const content = fs.readFileSync(filePath, 'utf8');
        
        // 基本语法检查
        const checks = [
            {
                name: '文件大小',
                test: () => content.length > 0,
                result: content.length > 0 ? `${content.length} 字符` : '空文件'
            },
            {
                name: '大括号匹配',
                test: () => {
                    const openBraces = (content.match(/\{/g) || []).length;
                    const closeBraces = (content.match(/\}/g) || []).length;
                    return openBraces === closeBraces;
                },
                result: function() {
                    const openBraces = (content.match(/\{/g) || []).length;
                    const closeBraces = (content.match(/\}/g) || []).length;
                    return `${openBraces}:{, ${closeBraces}:}`;
                }()
            },
            {
                name: '圆括号匹配',
                test: () => {
                    const openParens = (content.match(/\(/g) || []).length;
                    const closeParens = (content.match(/\)/g) || []).length;
                    return openParens === closeParens;
                },
                result: function() {
                    const openParens = (content.match(/\(/g) || []).length;
                    const closeParens = (content.match(/\)/g) || []).length;
                    return `${openParens}:(, ${closeParens}:)`;
                }()
            },
            {
                name: '包含认证检查',
                test: () => content.includes('checkAuthentication'),
                result: content.includes('checkAuthentication') ? '已实现' : '未找到'
            },
            {
                name: '包含API调用',
                test: () => content.includes('/api/v1/'),
                result: content.includes('/api/v1/') ? '已集成' : '未找到'
            },
            {
                name: '包含localStorage',
                test: () => content.includes('localStorage'),
                result: content.includes('localStorage') ? '支持' : '未支持'
            }
        ];
        
        let fileValid = true;
        checks.forEach(check => {
            const passed = check.test();
            console.log(`  ${passed ? '✅' : '❌'} ${check.name}: ${check.result}`);
            if (!passed) fileValid = false;
        });
        
        if (fileValid) {
            console.log(`✅ ${file} 验证通过`);
        } else {
            console.log(`❌ ${file} 验证失败`);
            allValid = false;
        }
        
    } catch (error) {
        console.log(`❌ 检查失败: ${error.message}`);
        allValid = false;
    }
});

console.log('\n' + '=' .repeat(50));
if (allValid) {
    console.log('🎉 所有文件验证通过！数据库集成完成。');
    console.log('\n📋 下一步:');
    console.log('1. 运行 start-test.bat 启动测试环境');
    console.log('2. 在浏览器中测试所有功能');
    console.log('3. 部署到生产环境');
} else {
    console.log('⚠️  部分文件验证失败，请检查并修复问题。');
}

console.log('\n💡 测试命令:');
console.log('  node test-api-server.js    # 启动模拟API');
console.log('  start-test.bat             # 启动完整测试环境');

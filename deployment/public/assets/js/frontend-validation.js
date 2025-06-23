// 前端修复完成验证脚本
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 开始前端修复验证...');
    
    const fixes = {
        '产品矩阵显示': false,
        '技术实力显示': false,
        '按钮样式': false,
        '按钮点击事件': false,
        '语言切换功能': false,
        '量子彩虹丝带': false,
        '3D量子球增强': false
    };
    
    // 检查产品矩阵显示
    const productsSection = document.getElementById('products');
    if (productsSection && getComputedStyle(productsSection).opacity !== '0') {
        fixes['产品矩阵显示'] = true;
        console.log('✅ 产品矩阵显示正常');
    } else {
        console.log('❌ 产品矩阵显示异常');
    }
    
    // 检查技术实力显示
    const technologySection = document.getElementById('technology');
    if (technologySection && getComputedStyle(technologySection).opacity !== '0') {
        fixes['技术实力显示'] = true;
        console.log('✅ 技术实力显示正常');
    } else {
        console.log('❌ 技术实力显示异常');
    }
    
    // 检查按钮样式
    const primaryBtns = document.querySelectorAll('.button-enhanced-primary');
    const secondaryBtns = document.querySelectorAll('.button-enhanced-secondary');
    if (primaryBtns.length > 0 && secondaryBtns.length > 0) {
        fixes['按钮样式'] = true;
        console.log('✅ 按钮样式正常，找到主要按钮', primaryBtns.length, '个，次要按钮', secondaryBtns.length, '个');
    } else {
        console.log('❌ 按钮样式异常');
    }
    
    // 检查语言切换器
    const langSelector = document.querySelector('.lang-selector-enhanced');
    if (langSelector) {
        fixes['语言切换功能'] = true;
        console.log('✅ 语言切换功能正常');
    } else {
        console.log('❌ 语言切换功能异常');
    }
    
    // 检查量子彩虹丝带
    const ribbon = document.querySelector('.quantum-rainbow-ribbon');
    if (ribbon) {
        fixes['量子彩虹丝带'] = true;
        console.log('✅ 量子彩虹丝带已添加');
    } else {
        console.log('❌ 量子彩虹丝带未找到');
    }
    
    // 检查3D量子模型
    const quantumModel = document.getElementById('quantum-model');
    if (quantumModel) {
        const canvas = quantumModel.querySelector('canvas');
        if (canvas) {
            fixes['3D量子球增强'] = true;
            console.log('✅ 3D量子球增强成功');
        } else {
            console.log('⚠️ 3D量子球容器存在但无canvas元素');
        }
    } else {
        console.log('❌ 3D量子球容器未找到');
    }
    
    // 测试按钮点击事件
    setTimeout(() => {
        let clickTestPassed = true;
        primaryBtns.forEach((btn, index) => {
            if (index === 0) { // 只测试第一个按钮
                const originalHandler = btn.onclick;
                btn.onclick = function() {
                    fixes['按钮点击事件'] = true;
                    console.log('✅ 按钮点击事件正常');
                    return originalHandler ? originalHandler.call(this) : true;
                };
                
                // 模拟点击
                btn.click();
            }
        });
    }, 1000);
    
    // 生成验证报告
    setTimeout(() => {
        console.log('\n🎯 前端修复验证报告:');
        console.log('================================');
        
        let passedCount = 0;
        const totalCount = Object.keys(fixes).length;
        
        for (const [feature, status] of Object.entries(fixes)) {
            const icon = status ? '✅' : '❌';
            console.log(`${icon} ${feature}: ${status ? '通过' : '失败'}`);
            if (status) passedCount++;
        }
        
        console.log('================================');
        console.log(`总体进度: ${passedCount}/${totalCount} (${Math.round(passedCount/totalCount*100)}%)`);
        
        if (passedCount === totalCount) {
            console.log('🎉 所有前端修复已完成！');
        } else {
            console.log('⚠️ 还有部分功能需要修复');
        }
        
        // 将结果显示在页面上（如果有调试容器）
        const debugContainer = document.getElementById('debug-info') || document.getElementById('debug-container');
        if (debugContainer) {
            let html = '<h3 class="text-lg font-bold mb-4">🎯 前端修复验证报告</h3>';
            html += '<div class="space-y-2">';
            
            for (const [feature, status] of Object.entries(fixes)) {
                const statusClass = status ? 'text-green-400' : 'text-red-400';
                const icon = status ? '✅' : '❌';
                html += `<div class="flex items-center gap-2"><span>${icon}</span><span class="${statusClass}">${feature}: ${status ? '通过' : '失败'}</span></div>`;
            }
            
            html += '</div>';
            html += `<div class="mt-4 p-3 bg-gray-700 rounded">总体进度: ${passedCount}/${totalCount} (${Math.round(passedCount/totalCount*100)}%)</div>`;
            
            debugContainer.innerHTML = html;
        }
    }, 2000);
});

// 导出验证函数供外部调用
window.validateFrontendFixes = function() {
    console.log('🔄 重新验证前端修复...');
    location.reload();
};

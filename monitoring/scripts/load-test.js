/**
 * API监控系统负载测试脚本
 * 使用autocannon进行负载测试
 */

const autocannon = require('autocannon');
const fs = require('fs');
const path = require('path');

// 默认配置
const DEFAULT_CONFIG = {
  url: 'http://localhost:8080',
  connections: 10,
  duration: 30,
  requests: [
    {
      method: 'GET',
      path: '/'
    },
    {
      method: 'GET',
      path: '/metrics'
    },
    {
      method: 'GET',
      path: '/api/health'
    }
  ]
};

// 解析命令行参数
const args = process.argv.slice(2);
let config = { ...DEFAULT_CONFIG };

// 处理命令行参数
for (let i = 0; i < args.length; i++) {
  const arg = args[i];
  
  if (arg === '--url' && args[i + 1]) {
    config.url = args[i + 1];
    i++;
  } else if (arg === '--connections' && args[i + 1]) {
    config.connections = parseInt(args[i + 1], 10);
    i++;
  } else if (arg === '--duration' && args[i + 1]) {
    config.duration = parseInt(args[i + 1], 10);
    i++;
  } else if (arg === '--config' && args[i + 1]) {
    // 从配置文件加载
    try {
      const configPath = args[i + 1];
      const configData = fs.readFileSync(configPath, 'utf8');
      const fileConfig = JSON.parse(configData);
      config = { ...config, ...fileConfig };
    } catch (error) {
      console.error('加载配置文件失败:', error.message);
      process.exit(1);
    }
    i++;
  }
}

console.log('启动负载测试，配置:', config);

// 创建结果目录
const resultsDir = path.join(__dirname, '../results');
if (!fs.existsSync(resultsDir)) {
  fs.mkdirSync(resultsDir, { recursive: true });
}

// 执行负载测试
const instance = autocannon(config, (err, result) => {
  if (err) {
    console.error('测试执行失败:', err);
    process.exit(1);
  }
  
  // 输出结果
  console.log('负载测试完成!');
  console.log('结果概览:');
  console.log(`请求总数: ${result.requests.total}`);
  console.log(`成功请求数: ${result.requests.successful}`);
  console.log(`失败请求数: ${result.errors}`);
  console.log(`平均延迟: ${result.latency.average} ms`);
  console.log(`最大延迟: ${result.latency.max} ms`);
  console.log(`P99延迟: ${result.latency.p99} ms`);
  console.log(`请求/秒: ${result.requests.average}`);
  
  // 保存结果
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const resultPath = path.join(resultsDir, `load-test-${timestamp}.json`);
  fs.writeFileSync(resultPath, JSON.stringify(result, null, 2));
  console.log(`详细结果已保存至: ${resultPath}`);
});

// 显示进度
instance.on('tick', (counter) => {
  console.log(`测试进行中... 已完成: ${Math.round((counter / config.duration) * 100)}%`);
});

// 处理用户中断
process.on('SIGINT', () => {
  console.log('用户中断测试...');
  instance.stop();
}); 
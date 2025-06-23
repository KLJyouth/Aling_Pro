/**
 * AlingAi API监控系统 - 配置管理器
 */

const fs = require('fs');
const path = require('path');

class ConfigManager {
  /**
   * 创建配置管理器
   * @param {string} configPath - 配置文件路径
   */
  constructor(configPath) {
    this.configPath = configPath;
    this.config = null;
    this.defaultConfig = {
      port: 3000,
      logLevel: 'info',
      storage: {
        type: 'sqlite',
        connection: {
          filename: path.join(__dirname, '../data/monitoring.db')
        },
        options: {
          dataRetention: 90 // 默认保留90天数据
        }
      },
      apis: [],
      alerts: {
        enabled: true,
        providers: {
          email: {
            enabled: false,
            smtpServer: '',
            smtpPort: 587,
            secure: true,
            auth: {
              user: '',
              pass: ''
            },
            from: '',
            to: []
          },
          webhook: {
            enabled: false,
            url: '',
            headers: {}
          }
        }
      }
    };
  }

  /**
   * 加载配置
   * @returns {Promise<Object>} 配置对象
   */
  async loadConfig() {
    try {
      // 检查配置文件是否存在
      if (fs.existsSync(this.configPath)) {
        // 读取并解析配置文件
        const configData = fs.readFileSync(this.configPath, 'utf8');
        this.config = JSON.parse(configData);
        console.log(`配置已从 ${this.configPath} 加载`);
      } else {
        console.log(`配置文件 ${this.configPath} 不存在，使用默认配置`);
        this.config = this.defaultConfig;
        
        // 确保目录存在
        const configDir = path.dirname(this.configPath);
        if (!fs.existsSync(configDir)) {
          fs.mkdirSync(configDir, { recursive: true });
        }
        
        // 保存默认配置
        await this.saveConfig();
      }
      
      // 确保必要的目录存在
      this.ensureDirectories();
      
      return this.config;
    } catch (error) {
      console.error('加载配置时出错:', error);
      throw error;
    }
  }

  /**
   * 保存配置到文件
   * @returns {Promise<void>}
   */
  async saveConfig() {
    try {
      await fs.promises.writeFile(
        this.configPath, 
        JSON.stringify(this.config, null, 2), 
        'utf8'
      );
      console.log(`配置已保存到 ${this.configPath}`);
    } catch (error) {
      console.error('保存配置时出错:', error);
      throw error;
    }
  }

  /**
   * 确保必要的目录存在
   * @private
   */
  ensureDirectories() {
    const dirs = [
      path.join(__dirname, '../logs'),
      path.join(__dirname, '../data')
    ];
    
    for (const dir of dirs) {
      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
        console.log(`创建目录: ${dir}`);
      }
    }
  }

  /**
   * 更新配置
   * @param {Object} newConfig - 新的配置对象
   * @returns {Promise<Object>} 更新后的配置对象
   */
  async updateConfig(newConfig) {
    this.config = { ...this.config, ...newConfig };
    await this.saveConfig();
    return this.config;
  }

  /**
   * 获取配置的特定部分
   * @param {string} section - 配置部分名称
   * @returns {Object} 配置部分
   */
  getConfigSection(section) {
    return this.config[section] || {};
  }

  /**
   * 更新配置的特定部分
   * @param {string} section - 配置部分名称
   * @param {Object} sectionConfig - 部分配置对象
   * @returns {Promise<Object>} 更新后的配置对象
   */
  async updateConfigSection(section, sectionConfig) {
    if (!this.config[section]) {
      this.config[section] = {};
    }
    
    this.config[section] = { ...this.config[section], ...sectionConfig };
    await this.saveConfig();
    return this.config;
  }

  /**
   * 添加API配置
   * @param {Object} apiConfig - API配置对象
   * @returns {Promise<Object>} 更新后的配置对象
   */
  async addApiConfig(apiConfig) {
    if (!this.config.apis) {
      this.config.apis = [];
    }
    
    // 检查API是否已存在
    const existingIndex = this.config.apis.findIndex(api => api.name === apiConfig.name);
    
    if (existingIndex >= 0) {
      // 更新已存在的API
      this.config.apis[existingIndex] = { ...this.config.apis[existingIndex], ...apiConfig };
    } else {
      // 添加新API
      this.config.apis.push(apiConfig);
    }
    
    await this.saveConfig();
    return this.config;
  }

  /**
   * 删除API配置
   * @param {string} apiName - API名称
   * @returns {Promise<Object>} 更新后的配置对象
   */
  async removeApiConfig(apiName) {
    if (!this.config.apis) {
      return this.config;
    }
    
    this.config.apis = this.config.apis.filter(api => api.name !== apiName);
    await this.saveConfig();
    return this.config;
  }
}

module.exports = ConfigManager; 
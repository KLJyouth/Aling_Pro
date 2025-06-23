/**
 * 指标缓冲区 - 缓存指标数据
 */

class MetricsBuffer {
  /**
   * 创建指标缓冲区
   * @param {number} capacity - 缓冲区容量
   * @param {Object} logger - 日志记录器
   */
  constructor(capacity, logger) {
    this.capacity = capacity || 1000;
    this.logger = logger;
    this.buffer = [];
  }

  /**
   * 添加指标数据到缓冲区
   * @param {Object} metric - 指标数据
   * @returns {boolean} 是否成功添加
   */
  add(metric) {
    if (this.isFull()) {
      this.logger.warn(`指标缓冲区已满（${this.capacity}条记录），无法添加更多指标`);
      return false;
    }

    this.buffer.push(metric);
    return true;
  }

  /**
   * 获取所有指标数据
   * @returns {Array} 指标数据数组
   */
  getAll() {
    return [...this.buffer];
  }

  /**
   * 清空缓冲区
   */
  clear() {
    this.buffer = [];
  }

  /**
   * 检查缓冲区是否已满
   * @returns {boolean} 是否已满
   */
  isFull() {
    return this.buffer.length >= this.capacity;
  }

  /**
   * 获取缓冲区中的指标数量
   * @returns {number} 指标数量
   */
  size() {
    return this.buffer.length;
  }

  /**
   * 获取缓冲区容量
   * @returns {number} 缓冲区容量
   */
  getCapacity() {
    return this.capacity;
  }

  /**
   * 设置缓冲区容量
   * @param {number} newCapacity - 新容量
   */
  setCapacity(newCapacity) {
    if (newCapacity < 1) {
      this.logger.warn('缓冲区容量必须大于0，设置被忽略');
      return;
    }

    // 如果新容量小于当前容量，且缓冲区中有数据，发出警告
    if (newCapacity < this.capacity && this.buffer.length > newCapacity) {
      this.logger.warn(`降低缓冲区容量（${this.capacity} -> ${newCapacity}），有 ${this.buffer.length - newCapacity} 条记录可能被丢弃`);
    }

    this.capacity = newCapacity;
    
    // 如果缓冲区中的数据超过新容量，截断数据
    if (this.buffer.length > this.capacity) {
      this.buffer = this.buffer.slice(0, this.capacity);
    }
  }
}

module.exports = MetricsBuffer; 
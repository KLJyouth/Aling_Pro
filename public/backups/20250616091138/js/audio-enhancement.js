/**
 * C++动画音效增强系统
 * 为视觉效果配合相应的音效，创造电影级体验
 */
class AudioEnhancementSystem {
    constructor() {
        this.audioContext = null;
        this.sounds = new Map();
        this.enabled = false;
        this.volume = 0.3;
        this.initialized = false;
        
        this.soundLibrary = {
            typing: {
                frequency: 800,
                duration: 0.1,
                type: 'square'
            },
            explosion: {
                frequency: 200,
                duration: 0.5,
                type: 'sawtooth'
            },
            quantum: {
                frequency: 1200,
                duration: 2.0,
                type: 'sine'
            },
            absorption: {
                frequency: 600,
                duration: 1.5,
                type: 'triangle'
            },
            transformation: {
                frequency: 400,
                duration: 3.0,
                type: 'sine'
            }
        };
        
        this.init();
    }
    
    async init() {
        try {
            // 需要用户交互才能初始化音频上下文
            document.addEventListener('click', this.initAudioContext.bind(this), { once: true });
            document.addEventListener('keydown', this.initAudioContext.bind(this), { once: true });
            console.log('🔊 音效系统准备就绪，等待用户交互');
        } catch (error) {
            console.warn('音效系统初始化失败:', error);
        }
    }
    
    async initAudioContext() {
        if (this.initialized) return;
        
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.enabled = true;
            this.initialized = true;
            console.log('🎵 音效系统已激活');
            
            // 预生成音效
            this.preloadSounds();
        } catch (error) {
            console.warn('音频上下文创建失败:', error);
        }
    }
    
    preloadSounds() {
        for (const [name, config] of Object.entries(this.soundLibrary)) {
            this.createSound(name, config);
        }
    }
    
    createSound(name, config) {
        if (!this.audioContext) return;
        
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.type = config.type;
        oscillator.frequency.setValueAtTime(config.frequency, this.audioContext.currentTime);
        
        gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
        gainNode.gain.linearRampToValueAtTime(this.volume, this.audioContext.currentTime + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + config.duration);
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        this.sounds.set(name, { oscillator, gainNode, config });
    }
    
    playSound(name, customConfig = {}) {
        if (!this.enabled || !this.audioContext) return;
        
        try {
            const config = { ...this.soundLibrary[name], ...customConfig };
            
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();
            
            oscillator.type = config.type;
            oscillator.frequency.setValueAtTime(config.frequency, this.audioContext.currentTime);
            
            // 添加频率变化效果
            if (name === 'quantum') {
                oscillator.frequency.linearRampToValueAtTime(
                    config.frequency * 1.5, 
                    this.audioContext.currentTime + config.duration * 0.5
                );
                oscillator.frequency.linearRampToValueAtTime(
                    config.frequency * 0.8, 
                    this.audioContext.currentTime + config.duration
                );
            }
            
            gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
            gainNode.gain.linearRampToValueAtTime(this.volume, this.audioContext.currentTime + 0.01);
            gainNode.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + config.duration);
            
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + config.duration);
            
        } catch (error) {
            console.warn('音效播放失败:', error);
        }
    }
    
    // 打字音效
    playTypingSound() {
        this.playSound('typing', {
            frequency: 800 + Math.random() * 200 // 随机频率变化
        });
    }
    
    // 爆炸音效
    playExplosionSound() {
        this.playSound('explosion');
        
        // 添加额外的噪音效果
        setTimeout(() => {
            this.playSound('explosion', { frequency: 150, duration: 0.3 });
        }, 100);
    }
    
    // 量子效果音效
    playQuantumSound() {
        this.playSound('quantum');
    }
    
    // 吸收效果音效
    playAbsorptionSound() {
        this.playSound('absorption');
    }
    
    // 变形音效
    playTransformationSound() {
        this.playSound('transformation');
        
        // 创建和声效果
        setTimeout(() => {
            this.playSound('transformation', { 
                frequency: 300,
                duration: 2.5 
            });
        }, 200);
    }
    
    // 创建环境音效
    createAmbientSound() {
        if (!this.enabled || !this.audioContext) return;
        
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(60, this.audioContext.currentTime);
        
        gainNode.gain.setValueAtTime(0.05, this.audioContext.currentTime);
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        oscillator.start();
        
        // 5秒后停止
        oscillator.stop(this.audioContext.currentTime + 5);
    }
    
    setVolume(volume) {
        this.volume = Math.max(0, Math.min(1, volume));
    }
    
    toggle() {
        this.enabled = !this.enabled;
        return this.enabled;
    }
    
    destroy() {
        if (this.audioContext) {
            this.audioContext.close();
        }
        this.sounds.clear();
    }
}

// 全局实例
window.audioEnhancement = new AudioEnhancementSystem();

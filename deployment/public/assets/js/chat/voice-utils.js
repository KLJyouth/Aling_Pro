/**
 * 语音交互工具类
 */
export class VoiceUtils {
    constructor() {
        // 初始化音频上下文和语音合成
        this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        this.speechSynthesis = window.speechSynthesis; 
        this.isSpeaking = false;
        this.utterance = null;
        this.conversationState = 'idle';
        this.recognition = null;

        // 加载音效文件
        this.loadSoundEffects();
    }

    /**
     * 加载音效
     */
    async loadSoundEffects() {
        try {
            this.soundEffects = {
                start: await this.loadAudioBuffer('/sounds/start.mp3'),
                stop: await this.loadAudioBuffer('/sounds/stop.mp3'),
                error: await this.loadAudioBuffer('/sounds/error.mp3')
            };
        } catch (error) {
            console.error('Failed to load sound effects:', error);
        }
    }

    /**
     * 加载音频文件
     */
    async loadAudioBuffer(url) {
        try {
            const response = await fetch(url);
            const arrayBuffer = await response.arrayBuffer();
            return await this.audioContext.decodeAudioData(arrayBuffer);
        } catch (error) {
            console.error(`Failed to load audio file ${url}:`, error);
            return null;
        }
    }

    /**
     * 播放音效
     */
    playFeedbackTone(type) {
        if (!this.soundEffects?.[type]) return;

        const source = this.audioContext.createBufferSource();
        source.buffer = this.soundEffects[type];
        source.connect(this.audioContext.destination);
        source.start();
    }

    /**
     * 初始化语音识别
     */
    initRecognition(onResult) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            throw new Error('浏览器不支持语音识别');
        }

        this.recognition = new SpeechRecognition();
        this.recognition.continuous = true;
        this.recognition.interimResults = true;
        this.recognition.lang = 'zh-CN';

        this.recognition.onstart = () => {
            this.conversationState = 'listening';
            this.playFeedbackTone('start');
        };

        this.recognition.onend = () => {
            if (this.conversationState === 'listening') {
                this.recognition.start();
            }
        };

        this.recognition.onerror = (event) => {
            console.error('语音识别错误:', event.error);
            this.playFeedbackTone('error');
            this.resetState();
        };

        this.recognition.onresult = (event) => {
            let final = '';
            let interim = '';
            
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    final += transcript;
                } else {
                    interim += transcript;
                }
            }

            if (final) {
                this.conversationState = 'processing';
                this.playFeedbackTone('stop');
                onResult(final);
            }
        };
    }

    /**
     * 开始语音识别
     */
    startListening() {
        if (this.conversationState === 'idle') {
            try {
                this.recognition?.start();
            } catch (error) {
                console.error('启动语音识别失败:', error);
                this.playFeedbackTone('error');
            }
        }
    }

    /**
     * 停止语音识别
     */
    stopListening() {
        if (this.recognition && this.conversationState === 'listening') {
            this.recognition.stop();
            this.resetState();
        }
    }

    /**
     * 语音合成朗读文本
     */
    speak(text) {
        return new Promise((resolve) => {
            if (this.isSpeaking) {
                this.stopSpeaking();
            }

            this.utterance = new SpeechSynthesisUtterance(text);
            this.utterance.lang = 'zh-CN';
            this.utterance.rate = 0.9;  // 语速
            this.utterance.pitch = 1.1; // 语调
            
            this.utterance.onstart = () => {
                this.isSpeaking = true;
                this.conversationState = 'speaking';
            };
            
            this.utterance.onend = () => {
                this.isSpeaking = false;
                this.conversationState = 'idle';
                resolve();
            };
            
            this.speechSynthesis.speak(this.utterance);
        });
    }

    /**
     * 停止语音合成
     */
    stopSpeaking() {
        if (this.isSpeaking) {
            this.speechSynthesis.cancel();
            this.isSpeaking = false;
            this.conversationState = 'idle';
        }
    }

    /**
     * 重置状态
     */
    resetState() {
        this.conversationState = 'idle';
        this.stopListening();
        this.stopSpeaking();
    }
}

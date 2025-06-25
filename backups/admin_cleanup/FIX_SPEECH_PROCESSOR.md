# SpeechProcessor.php 修复指南

## 需要修复的问题

1. 在 `BaseSpeechModel` 类中有重复的 `process` 方法，第二个实现还包含语法错误：

```php
// 错误的代码
abstract public function process(string $audioPath, array $options = []): array;

public function process(()) {
    // TODO: 实现 process 方法
    throw new \Exception('Method process not implemented');';
}
```

应该删除第二个 `process` 方法，只保留抽象方法声明。

2. VoiceAnalysisModel 和 SpeakerRecognitionModel 类被注释掉了：

```php
// class VoiceAnalysisModel extends BaseSpeechModel
 // 不可达代码
```

应该恢复这些类的定义。

## 修复步骤

1. 打开文件 `apps/ai-platform/Services/Speech/SpeechProcessor.php`

2. 找到 `BaseSpeechModel` 类（大约在第350行附近）
   - 删除第二个 `process` 方法（包含 `process(())` 的那个）
   - 保留抽象方法 `abstract public function process(string $audioPath, array $options = []): array;`

3. 查找注释掉的模型类
   - 将 `// class VoiceAnalysisModel extends BaseSpeechModel` 改为 `class VoiceAnalysisModel extends BaseSpeechModel`
   - 将 `// class SpeakerRecognitionModel extends BaseSpeechModel` 改为 `class SpeakerRecognitionModel extends BaseSpeechModel`
   - 删除 `// 不可达代码` 注释

## 正确的代码

`BaseSpeechModel` 类应该如下所示：

```php
abstract class BaseSpeechModel
{
    protected array $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    abstract public function process(string $audioPath, array $options = []): array;
}
```

`VoiceAnalysisModel` 类应该如下所示：

```php
class VoiceAnalysisModel extends BaseSpeechModel
{
    public function analyze(string $audioPath): array
    {
        return [
            'voice_type' => ['male', 'female'][rand(0, 1)],
            'age_estimate' => rand(20, 60) . '-' . rand(65, 80),
            'accent' => 'neutral',
            'speaking_rate' => rand(120, 180) . ' words/minute',
            'pitch_range' => rand(80, 120) . '-' . rand(200, 350) . 'Hz',
            'voice_quality' => 'clear',
            'emotional_state' => ['neutral', 'happy', 'calm', 'excited'][rand(0, 3)],
            'confidence' => round(rand(75, 95) / 100, 2)
        ];
    }

    public function process(string $audioPath, array $options = []): array
    {
        return $this->analyze($audioPath);
    }
}
```

`SpeakerRecognitionModel` 类应该如下所示：

```php
class SpeakerRecognitionModel extends BaseSpeechModel
{
    public function identify(string $audioPath, array $options = []): array
    {
        return [
            'speaker_id' => 'speaker_' . rand(1000, 9999),
            'is_known_speaker' => rand(0, 1) === 1,
            'confidence' => round(rand(70, 95) / 100, 2),
            'voice_print_match' => rand(0, 1) === 1,
            'similarity_scores' => [
                'speaker_1' => round(rand(20, 80) / 100, 2),
                'speaker_2' => round(rand(30, 90) / 100, 2),
                'speaker_3' => round(rand(10, 70) / 100, 2)
            ]
        ];
    }

    public function process(string $audioPath, array $options = []): array
    {
        return $this->identify($audioPath, $options);
    }
}
```
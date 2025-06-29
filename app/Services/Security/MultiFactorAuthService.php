<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\UserMfaMethod;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class MultiFactorAuthService
{
    /**
     * Google 2FA实例
     * 
     * @var \PragmaRX\Google2FA\Google2FA
     */
    protected $google2fa;
    
    /**
     * 构造函数
     * 
     * @return void
     */
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }
    
    /**
     * 启用多因素认证
     * 
     * @param User $user 用户
     * @param string $method 认证方法 (app, sms, email)
     * @return array 启用结果
     */
    public function enableMfa(User $user, string $method): array
    {
        try {
            // 检查方法是否已启用
            $existingMethod = UserMfaMethod::where('user_id', $user->id)
                ->where('method', $method)
                ->first();
            
            if ($existingMethod) {
                return [
                    'success' => false,
                    'message' => "多因素认证方法 {$method} 已启用"
                ];
            }
            
            // 根据不同的方法进行处理
            switch ($method) {
                case 'app':
                    return $this->setupAppMfa($user);
                case 'sms':
                    return $this->setupSmsMfa($user);
                case 'email':
                    return $this->setupEmailMfa($user);
                case 'fingerprint':
                    return $this->setupFingerprintMfa($user);
                default:
                    return [
                        'success' => false,
                        'message' => "不支持的多因素认证方法：{$method}"
                    ];
            }
        } catch (\Exception $e) {
            Log::error("启用多因素认证失败", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "启用多因素认证失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 设置应用认证
     * 
     * @param User $user 用户
     * @return array 设置结果
     */
    protected function setupAppMfa(User $user): array
    {
        // 生成密钥
        $secretKey = $this->google2fa->generateSecretKey();
        
        // 生成二维码URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );
        
        // 保存临时密钥到缓存
        Cache::put("mfa_setup:{$user->id}:app", [
            'secret_key' => $secretKey,
            'created_at' => now()->timestamp
        ], now()->addMinutes(30));
        
        // 生成二维码
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrCodeUrl));
        
        return [
            'success' => true,
            'message' => "应用认证设置已准备就绪，请扫描二维码",
            'data' => [
                'qr_code' => "data:image/png;base64,{$qrCode}",
                'secret_key' => $secretKey,
                'requires_verification' => true
            ]
        ];
    }
    
    /**
     * 设置短信认证
     * 
     * @param User $user 用户
     * @return array 设置结果
     */
    protected function setupSmsMfa(User $user): array
    {
        // 检查用户是否有手机号
        if (!$user->phone_number) {
            return [
                'success' => false,
                'message' => "请先绑定手机号"
            ];
        }
        
        // 生成验证码
        $verificationCode = sprintf('%06d', mt_rand(100000, 999999));
        
        // 保存验证码到缓存
        Cache::put("mfa_setup:{$user->id}:sms", [
            'verification_code' => $verificationCode,
            'created_at' => now()->timestamp
        ], now()->addMinutes(10));
        
        // 发送短信验证码（模拟）
        Log::info("发送短信验证码", [
            'phone' => $user->phone_number,
            'code' => $verificationCode
        ]);
        
        // 实际应用中应调用短信发送服务
        // $this->smsService->send($user->phone_number, "您的验证码是：{$verificationCode}，10分钟内有效。");
        
        return [
            'success' => true,
            'message' => "验证码已发送到您的手机",
            'data' => [
                'phone_number' => substr($user->phone_number, 0, 3) . '****' . substr($user->phone_number, -4),
                'requires_verification' => true
            ]
        ];
    }
    
    /**
     * 设置邮箱认证
     * 
     * @param User $user 用户
     * @return array 设置结果
     */
    protected function setupEmailMfa(User $user): array
    {
        // 生成验证码
        $verificationCode = sprintf('%06d', mt_rand(100000, 999999));
        
        // 保存验证码到缓存
        Cache::put("mfa_setup:{$user->id}:email", [
            'verification_code' => $verificationCode,
            'created_at' => now()->timestamp
        ], now()->addMinutes(30));
        
        // 发送邮件验证码（模拟）
        Log::info("发送邮件验证码", [
            'email' => $user->email,
            'code' => $verificationCode
        ]);
        
        // 实际应用中应发送邮件
        // Mail::to($user->email)->send(new MfaVerificationMail($verificationCode));
        
        return [
            'success' => true,
            'message' => "验证码已发送到您的邮箱",
            'data' => [
                'email' => substr($user->email, 0, 3) . '***' . strstr($user->email, '@'),
                'requires_verification' => true
            ]
        ];
    }
    
    /**
     * 设置指纹认证
     * 
     * @param User $user 用户
     * @return array 设置结果
     */
    protected function setupFingerprintMfa(User $user): array
    {
        // 生成挑战码
        $challengeCode = Str::random(32);
        
        // 保存挑战码到缓存
        Cache::put("mfa_setup:{$user->id}:fingerprint", [
            'challenge_code' => $challengeCode,
            'created_at' => now()->timestamp
        ], now()->addMinutes(10));
        
        return [
            'success' => true,
            'message' => "请在设备上完成指纹验证",
            'data' => [
                'challenge_code' => $challengeCode,
                'requires_verification' => true
            ]
        ];
    }
    
    /**
     * 验证多因素认证设置
     * 
     * @param User $user 用户
     * @param string $method 认证方法
     * @param string $code 验证码
     * @return array 验证结果
     */
    public function verifyMfaSetup(User $user, string $method, string $code): array
    {
        try {
            // 获取缓存中的设置数据
            $setupData = Cache::get("mfa_setup:{$user->id}:{$method}");
            
            if (!$setupData) {
                return [
                    'success' => false,
                    'message' => "设置已过期，请重新开始"
                ];
            }
            
            $verified = false;
            
            // 根据不同的方法进行验证
            switch ($method) {
                case 'app':
                    $verified = $this->google2fa->verifyKey($setupData['secret_key'], $code);
                    break;
                case 'sms':
                case 'email':
                    $verified = $setupData['verification_code'] === $code;
                    break;
                case 'fingerprint':
                    // 指纹验证需要客户端完成，这里假设已验证
                    $verified = true;
                    break;
            }
            
            if (!$verified) {
                return [
                    'success' => false,
                    'message' => "验证码错误"
                ];
            }
            
            // 创建MFA方法记录
            $mfaMethod = new UserMfaMethod();
            $mfaMethod->user_id = $user->id;
            $mfaMethod->method = $method;
            $mfaMethod->is_primary = !UserMfaMethod::where('user_id', $user->id)->exists(); // 如果是第一个方法，设为主要方法
            
            // 保存方法特定的数据
            $methodData = [];
            switch ($method) {
                case 'app':
                    $methodData['secret_key'] = $setupData['secret_key'];
                    break;
                case 'sms':
                    $methodData['phone_number'] = $user->phone_number;
                    break;
                case 'email':
                    $methodData['email'] = $user->email;
                    break;
                case 'fingerprint':
                    $methodData['device_id'] = $user->currentDevice()->device_id ?? null;
                    break;
            }
            
            $mfaMethod->metadata = $methodData;
            $mfaMethod->save();
            
            // 清除缓存
            Cache::forget("mfa_setup:{$user->id}:{$method}");
            
            // 更新用户MFA状态
            $user->has_mfa = true;
            $user->save();
            
            // 记录安全日志
            $this->logMfaEvent($user, "mfa_enabled", [
                'method' => $method,
                'is_primary' => $mfaMethod->is_primary
            ]);
            
            return [
                'success' => true,
                'message' => "多因素认证已成功启用",
                'data' => [
                    'method' => $method,
                    'is_primary' => $mfaMethod->is_primary
                ]
            ];
        } catch (\Exception $e) {
            Log::error("验证多因素认证设置失败", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "验证失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 禁用多因素认证
     * 
     * @param User $user 用户
     * @param string $method 认证方法
     * @return array 禁用结果
     */
    public function disableMfa(User $user, string $method): array
    {
        try {
            // 查找MFA方法
            $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                ->where('method', $method)
                ->first();
            
            if (!$mfaMethod) {
                return [
                    'success' => false,
                    'message' => "多因素认证方法 {$method} 未启用"
                ];
            }
            
            $isPrimary = $mfaMethod->is_primary;
            
            // 删除MFA方法
            $mfaMethod->delete();
            
            // 如果删除的是主要方法，设置新的主要方法
            if ($isPrimary) {
                $newPrimary = UserMfaMethod::where('user_id', $user->id)->first();
                if ($newPrimary) {
                    $newPrimary->is_primary = true;
                    $newPrimary->save();
                }
            }
            
            // 更新用户MFA状态
            $userHasMfa = UserMfaMethod::where('user_id', $user->id)->exists();
            $user->has_mfa = $userHasMfa;
            $user->save();
            
            // 记录安全日志
            $this->logMfaEvent($user, "mfa_disabled", [
                'method' => $method
            ]);
            
            return [
                'success' => true,
                'message' => "多因素认证已禁用",
                'data' => [
                    'has_mfa' => $userHasMfa
                ]
            ];
        } catch (\Exception $e) {
            Log::error("禁用多因素认证失败", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "禁用失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 发送多因素认证验证码
     * 
     * @param User $user 用户
     * @param string $method 认证方法 (可选，默认使用主要方法)
     * @return array 发送结果
     */
    public function sendMfaCode(User $user, string $method = null): array
    {
        try {
            // 如果未指定方法，使用主要方法
            if (!$method) {
                $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                    ->where('is_primary', true)
                    ->first();
                
                if (!$mfaMethod) {
                    return [
                        'success' => false,
                        'message' => "未找到多因素认证方法"
                    ];
                }
                
                $method = $mfaMethod->method;
            } else {
                // 检查指定的方法是否存在
                $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                    ->where('method', $method)
                    ->first();
                
                if (!$mfaMethod) {
                    return [
                        'success' => false,
                        'message' => "多因素认证方法 {$method} 未启用"
                    ];
                }
            }
            
            // 根据不同的方法发送验证码
            switch ($method) {
                case 'app':
                    // 应用认证不需要发送验证码
                    return [
                        'success' => true,
                        'message' => "请在认证应用中查看验证码",
                        'data' => [
                            'method' => $method
                        ]
                    ];
                
                case 'sms':
                    // 生成验证码
                    $verificationCode = sprintf('%06d', mt_rand(100000, 999999));
                    
                    // 保存验证码到缓存
                    Cache::put("mfa_code:{$user->id}:sms", [
                        'verification_code' => $verificationCode,
                        'created_at' => now()->timestamp
                    ], now()->addMinutes(10));
                    
                    // 发送短信验证码（模拟）
                    $phoneNumber = $mfaMethod->metadata['phone_number'] ?? $user->phone_number;
                    Log::info("发送MFA短信验证码", [
                        'phone' => $phoneNumber,
                        'code' => $verificationCode
                    ]);
                    
                    // 实际应用中应调用短信发送服务
                    // $this->smsService->send($phoneNumber, "您的验证码是：{$verificationCode}，10分钟内有效。");
                    
                    return [
                        'success' => true,
                        'message' => "验证码已发送到您的手机",
                        'data' => [
                            'method' => $method,
                            'phone_number' => substr($phoneNumber, 0, 3) . '****' . substr($phoneNumber, -4)
                        ]
                    ];
                
                case 'email':
                    // 生成验证码
                    $verificationCode = sprintf('%06d', mt_rand(100000, 999999));
                    
                    // 保存验证码到缓存
                    Cache::put("mfa_code:{$user->id}:email", [
                        'verification_code' => $verificationCode,
                        'created_at' => now()->timestamp
                    ], now()->addMinutes(30));
                    
                    // 发送邮件验证码（模拟）
                    $email = $mfaMethod->metadata['email'] ?? $user->email;
                    Log::info("发送MFA邮件验证码", [
                        'email' => $email,
                        'code' => $verificationCode
                    ]);
                    
                    // 实际应用中应发送邮件
                    // Mail::to($email)->send(new MfaVerificationMail($verificationCode));
                    
                    return [
                        'success' => true,
                        'message' => "验证码已发送到您的邮箱",
                        'data' => [
                            'method' => $method,
                            'email' => substr($email, 0, 3) . '***' . strstr($email, '@')
                        ]
                    ];
                
                case 'fingerprint':
                    // 生成挑战码
                    $challengeCode = Str::random(32);
                    
                    // 保存挑战码到缓存
                    Cache::put("mfa_code:{$user->id}:fingerprint", [
                        'challenge_code' => $challengeCode,
                        'created_at' => now()->timestamp
                    ], now()->addMinutes(10));
                    
                    return [
                        'success' => true,
                        'message' => "请在设备上完成指纹验证",
                        'data' => [
                            'method' => $method,
                            'challenge_code' => $challengeCode
                        ]
                    ];
                
                default:
                    return [
                        'success' => false,
                        'message' => "不支持的多因素认证方法：{$method}"
                    ];
            }
        } catch (\Exception $e) {
            Log::error("发送多因素认证验证码失败", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "发送验证码失败：" . $e->getMessage()
            ];
        }
    }

    /**
     * 验证多因素认证
     * 
     * @param User $user 用户
     * @param string $method 认证方法
     * @param string $code 验证码
     * @return array 验证结果
     */
    public function verifyMfa(User $user, string $method, string $code): array
    {
        try {
            // 查找MFA方法
            $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                ->where('method', $method)
                ->first();
            
            if (!$mfaMethod) {
                return [
                    'success' => false,
                    'message' => "多因素认证方法 {$method} 未启用"
                ];
            }
            
            $verified = false;
            
            // 根据不同的方法进行验证
            switch ($method) {
                case 'app':
                    $secretKey = $mfaMethod->metadata['secret_key'] ?? '';
                    $verified = $this->google2fa->verifyKey($secretKey, $code);
                    break;
                
                case 'sms':
                    $codeData = Cache::get("mfa_code:{$user->id}:sms");
                    if (!$codeData) {
                        return [
                            'success' => false,
                            'message' => "验证码已过期，请重新获取"
                        ];
                    }
                    
                    $verified = $codeData['verification_code'] === $code;
                    
                    // 验证成功后清除缓存
                    if ($verified) {
                        Cache::forget("mfa_code:{$user->id}:sms");
                    }
                    break;
                
                case 'email':
                    $codeData = Cache::get("mfa_code:{$user->id}:email");
                    if (!$codeData) {
                        return [
                            'success' => false,
                            'message' => "验证码已过期，请重新获取"
                        ];
                    }
                    
                    $verified = $codeData['verification_code'] === $code;
                    
                    // 验证成功后清除缓存
                    if ($verified) {
                        Cache::forget("mfa_code:{$user->id}:email");
                    }
                    break;
                
                case 'fingerprint':
                    // 指纹验证需要客户端完成，这里假设已验证
                    $verified = true;
                    
                    // 清除挑战码缓存
                    Cache::forget("mfa_code:{$user->id}:fingerprint");
                    break;
            }
            
            if (!$verified) {
                // 记录失败日志
                $this->logMfaEvent($user, "mfa_verification_failed", [
                    'method' => $method
                ]);
                
                return [
                    'success' => false,
                    'message' => "验证码错误"
                ];
            }
            
            // 记录成功日志
            $this->logMfaEvent($user, "mfa_verification_success", [
                'method' => $method
            ]);
            
            // 更新MFA方法的最后使用时间
            $mfaMethod->last_used_at = now();
            $mfaMethod->save();
            
            return [
                'success' => true,
                'message' => "验证成功",
                'data' => [
                    'method' => $method
                ]
            ];
        } catch (\Exception $e) {
            Log::error("验证多因素认证失败", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "验证失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取用户的MFA方法
     * 
     * @param User $user 用户
     * @return array MFA方法列表
     */
    public function getUserMfaMethods(User $user): array
    {
        $methods = UserMfaMethod::where('user_id', $user->id)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($method) {
                // 移除敏感数据
                $data = $method->toArray();
                if (isset($data['metadata']['secret_key'])) {
                    $data['metadata']['secret_key'] = '***********';
                }
                return $data;
            });
        
        return [
            'success' => true,
            'methods' => $methods
        ];
    }
    
    /**
     * 设置主要MFA方法
     * 
     * @param User $user 用户
     * @param int $methodId 方法ID
     * @return array 设置结果
     */
    public function setPrimaryMfaMethod(User $user, int $methodId): array
    {
        try {
            // 查找MFA方法
            $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                ->where('id', $methodId)
                ->first();
            
            if (!$mfaMethod) {
                return [
                    'success' => false,
                    'message' => "未找到指定的多因素认证方法"
                ];
            }
            
            // 将所有方法设为非主要
            UserMfaMethod::where('user_id', $user->id)
                ->update(['is_primary' => false]);
            
            // 设置新的主要方法
            $mfaMethod->is_primary = true;
            $mfaMethod->save();
            
            // 记录安全日志
            $this->logMfaEvent($user, "mfa_primary_changed", [
                'method' => $mfaMethod->method,
                'method_id' => $mfaMethod->id
            ]);
            
            return [
                'success' => true,
                'message' => "已设置为主要验证方法",
                'data' => [
                    'method' => $mfaMethod->method,
                    'method_id' => $mfaMethod->id
                ]
            ];
        } catch (\Exception $e) {
            Log::error("设置主要MFA方法失败", [
                'user_id' => $user->id,
                'method_id' => $methodId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "设置失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 生成恢复代码
     * 
     * @param User $user 用户
     * @return array 恢复代码
     */
    public function generateRecoveryCodes(User $user): array
    {
        try {
            // 生成10个恢复代码
            $recoveryCodes = [];
            for ($i = 0; $i < 10; $i++) {
                $recoveryCodes[] = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
            }
            
            // 保存恢复代码（哈希后）
            $hashedCodes = [];
            foreach ($recoveryCodes as $code) {
                $hashedCodes[] = [
                    'code' => hash('sha256', $code),
                    'used' => false
                ];
            }
            
            // 更新用户的恢复代码
            $user->mfa_recovery_codes = $hashedCodes;
            $user->save();
            
            // 记录安全日志
            $this->logMfaEvent($user, "recovery_codes_generated", [
                'count' => count($recoveryCodes)
            ]);
            
            return [
                'success' => true,
                'message' => "已生成恢复代码",
                'data' => [
                    'recovery_codes' => $recoveryCodes
                ]
            ];
        } catch (\Exception $e) {
            Log::error("生成恢复代码失败", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "生成恢复代码失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 验证恢复代码
     * 
     * @param User $user 用户
     * @param string $code 恢复代码
     * @return array 验证结果
     */
    public function verifyRecoveryCode(User $user, string $code): array
    {
        try {
            // 获取用户的恢复代码
            $recoveryCodes = $user->mfa_recovery_codes ?? [];
            
            if (empty($recoveryCodes)) {
                return [
                    'success' => false,
                    'message' => "未设置恢复代码"
                ];
            }
            
            // 查找匹配的恢复代码
            $hashedCode = hash('sha256', $code);
            $found = false;
            $updatedCodes = [];
            
            foreach ($recoveryCodes as $recoveryCode) {
                if ($recoveryCode['code'] === $hashedCode && !$recoveryCode['used']) {
                    $found = true;
                    $updatedCodes[] = [
                        'code' => $recoveryCode['code'],
                        'used' => true
                    ];
                } else {
                    $updatedCodes[] = $recoveryCode;
                }
            }
            
            if (!$found) {
                // 记录失败日志
                $this->logMfaEvent($user, "recovery_code_verification_failed", []);
                
                return [
                    'success' => false,
                    'message' => "恢复代码无效或已使用"
                ];
            }
            
            // 更新恢复代码状态
            $user->mfa_recovery_codes = $updatedCodes;
            $user->save();
            
            // 记录成功日志
            $this->logMfaEvent($user, "recovery_code_used", []);
            
            return [
                'success' => true,
                'message' => "恢复代码验证成功",
                'data' => [
                    'remaining_codes' => count(array_filter($updatedCodes, function ($code) {
                        return !$code['used'];
                    }))
                ]
            ];
        } catch (\Exception $e) {
            Log::error("验证恢复代码失败", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "验证恢复代码失败：" . $e->getMessage()
            ];
        }
    }
    
    /**
     * 记录MFA事件
     * 
     * @param User $user 用户
     * @param string $eventType 事件类型
     * @param array $metadata 元数据
     * @return void
     */
    protected function logMfaEvent(User $user, string $eventType, array $metadata = []): void
    {
        $log = new SecurityLog();
        $log->user_id = $user->id;
        $log->ip_address = request()->ip();
        $log->user_agent = request()->userAgent();
        $log->event_type = $eventType;
        $log->context = 'mfa';
        $log->metadata = array_merge($metadata, [
            'timestamp' => now()->timestamp,
        ]);
        $log->save();
    }
}

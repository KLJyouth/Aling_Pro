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
     * Google 2FAʵ��
     * 
     * @var \PragmaRX\Google2FA\Google2FA
     */
    protected $google2fa;
    
    /**
     * ���캯��
     * 
     * @return void
     */
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }
    
    /**
     * ���ö�������֤
     * 
     * @param User $user �û�
     * @param string $method ��֤���� (app, sms, email)
     * @return array ���ý��
     */
    public function enableMfa(User $user, string $method): array
    {
        try {
            // ��鷽���Ƿ�������
            $existingMethod = UserMfaMethod::where('user_id', $user->id)
                ->where('method', $method)
                ->first();
            
            if ($existingMethod) {
                return [
                    'success' => false,
                    'message' => "��������֤���� {$method} ������"
                ];
            }
            
            // ���ݲ�ͬ�ķ������д���
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
                        'message' => "��֧�ֵĶ�������֤������{$method}"
                    ];
            }
        } catch (\Exception $e) {
            Log::error("���ö�������֤ʧ��", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "���ö�������֤ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ����Ӧ����֤
     * 
     * @param User $user �û�
     * @return array ���ý��
     */
    protected function setupAppMfa(User $user): array
    {
        // ������Կ
        $secretKey = $this->google2fa->generateSecretKey();
        
        // ���ɶ�ά��URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );
        
        // ������ʱ��Կ������
        Cache::put("mfa_setup:{$user->id}:app", [
            'secret_key' => $secretKey,
            'created_at' => now()->timestamp
        ], now()->addMinutes(30));
        
        // ���ɶ�ά��
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrCodeUrl));
        
        return [
            'success' => true,
            'message' => "Ӧ����֤������׼����������ɨ���ά��",
            'data' => [
                'qr_code' => "data:image/png;base64,{$qrCode}",
                'secret_key' => $secretKey,
                'requires_verification' => true
            ]
        ];
    }
    
    /**
     * ���ö�����֤
     * 
     * @param User $user �û�
     * @return array ���ý��
     */
    protected function setupSmsMfa(User $user): array
    {
        // ����û��Ƿ����ֻ���
        if (!$user->phone_number) {
            return [
                'success' => false,
                'message' => "���Ȱ��ֻ���"
            ];
        }
        
        // ������֤��
        $verificationCode = sprintf('%06d', mt_rand(100000, 999999));
        
        // ������֤�뵽����
        Cache::put("mfa_setup:{$user->id}:sms", [
            'verification_code' => $verificationCode,
            'created_at' => now()->timestamp
        ], now()->addMinutes(10));
        
        // ���Ͷ�����֤�루ģ�⣩
        Log::info("���Ͷ�����֤��", [
            'phone' => $user->phone_number,
            'code' => $verificationCode
        ]);
        
        // ʵ��Ӧ����Ӧ���ö��ŷ��ͷ���
        // $this->smsService->send($user->phone_number, "������֤���ǣ�{$verificationCode}��10��������Ч��");
        
        return [
            'success' => true,
            'message' => "��֤���ѷ��͵������ֻ�",
            'data' => [
                'phone_number' => substr($user->phone_number, 0, 3) . '****' . substr($user->phone_number, -4),
                'requires_verification' => true
            ]
        ];
    }
    
    /**
     * ����������֤
     * 
     * @param User $user �û�
     * @return array ���ý��
     */
    protected function setupEmailMfa(User $user): array
    {
        // ������֤��
        $verificationCode = sprintf('%06d', mt_rand(100000, 999999));
        
        // ������֤�뵽����
        Cache::put("mfa_setup:{$user->id}:email", [
            'verification_code' => $verificationCode,
            'created_at' => now()->timestamp
        ], now()->addMinutes(30));
        
        // �����ʼ���֤�루ģ�⣩
        Log::info("�����ʼ���֤��", [
            'email' => $user->email,
            'code' => $verificationCode
        ]);
        
        // ʵ��Ӧ����Ӧ�����ʼ�
        // Mail::to($user->email)->send(new MfaVerificationMail($verificationCode));
        
        return [
            'success' => true,
            'message' => "��֤���ѷ��͵���������",
            'data' => [
                'email' => substr($user->email, 0, 3) . '***' . strstr($user->email, '@'),
                'requires_verification' => true
            ]
        ];
    }
    
    /**
     * ����ָ����֤
     * 
     * @param User $user �û�
     * @return array ���ý��
     */
    protected function setupFingerprintMfa(User $user): array
    {
        // ������ս��
        $challengeCode = Str::random(32);
        
        // ������ս�뵽����
        Cache::put("mfa_setup:{$user->id}:fingerprint", [
            'challenge_code' => $challengeCode,
            'created_at' => now()->timestamp
        ], now()->addMinutes(10));
        
        return [
            'success' => true,
            'message' => "�����豸�����ָ����֤",
            'data' => [
                'challenge_code' => $challengeCode,
                'requires_verification' => true
            ]
        ];
    }
    
    /**
     * ��֤��������֤����
     * 
     * @param User $user �û�
     * @param string $method ��֤����
     * @param string $code ��֤��
     * @return array ��֤���
     */
    public function verifyMfaSetup(User $user, string $method, string $code): array
    {
        try {
            // ��ȡ�����е���������
            $setupData = Cache::get("mfa_setup:{$user->id}:{$method}");
            
            if (!$setupData) {
                return [
                    'success' => false,
                    'message' => "�����ѹ��ڣ������¿�ʼ"
                ];
            }
            
            $verified = false;
            
            // ���ݲ�ͬ�ķ���������֤
            switch ($method) {
                case 'app':
                    $verified = $this->google2fa->verifyKey($setupData['secret_key'], $code);
                    break;
                case 'sms':
                case 'email':
                    $verified = $setupData['verification_code'] === $code;
                    break;
                case 'fingerprint':
                    // ָ����֤��Ҫ�ͻ�����ɣ������������֤
                    $verified = true;
                    break;
            }
            
            if (!$verified) {
                return [
                    'success' => false,
                    'message' => "��֤�����"
                ];
            }
            
            // ����MFA������¼
            $mfaMethod = new UserMfaMethod();
            $mfaMethod->user_id = $user->id;
            $mfaMethod->method = $method;
            $mfaMethod->is_primary = !UserMfaMethod::where('user_id', $user->id)->exists(); // ����ǵ�һ����������Ϊ��Ҫ����
            
            // ���淽���ض�������
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
            
            // �������
            Cache::forget("mfa_setup:{$user->id}:{$method}");
            
            // �����û�MFA״̬
            $user->has_mfa = true;
            $user->save();
            
            // ��¼��ȫ��־
            $this->logMfaEvent($user, "mfa_enabled", [
                'method' => $method,
                'is_primary' => $mfaMethod->is_primary
            ]);
            
            return [
                'success' => true,
                'message' => "��������֤�ѳɹ�����",
                'data' => [
                    'method' => $method,
                    'is_primary' => $mfaMethod->is_primary
                ]
            ];
        } catch (\Exception $e) {
            Log::error("��֤��������֤����ʧ��", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "��֤ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ���ö�������֤
     * 
     * @param User $user �û�
     * @param string $method ��֤����
     * @return array ���ý��
     */
    public function disableMfa(User $user, string $method): array
    {
        try {
            // ����MFA����
            $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                ->where('method', $method)
                ->first();
            
            if (!$mfaMethod) {
                return [
                    'success' => false,
                    'message' => "��������֤���� {$method} δ����"
                ];
            }
            
            $isPrimary = $mfaMethod->is_primary;
            
            // ɾ��MFA����
            $mfaMethod->delete();
            
            // ���ɾ��������Ҫ�����������µ���Ҫ����
            if ($isPrimary) {
                $newPrimary = UserMfaMethod::where('user_id', $user->id)->first();
                if ($newPrimary) {
                    $newPrimary->is_primary = true;
                    $newPrimary->save();
                }
            }
            
            // �����û�MFA״̬
            $userHasMfa = UserMfaMethod::where('user_id', $user->id)->exists();
            $user->has_mfa = $userHasMfa;
            $user->save();
            
            // ��¼��ȫ��־
            $this->logMfaEvent($user, "mfa_disabled", [
                'method' => $method
            ]);
            
            return [
                'success' => true,
                'message' => "��������֤�ѽ���",
                'data' => [
                    'has_mfa' => $userHasMfa
                ]
            ];
        } catch (\Exception $e) {
            Log::error("���ö�������֤ʧ��", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "����ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ���Ͷ�������֤��֤��
     * 
     * @param User $user �û�
     * @param string $method ��֤���� (��ѡ��Ĭ��ʹ����Ҫ����)
     * @return array ���ͽ��
     */
    public function sendMfaCode(User $user, string $method = null): array
    {
        try {
            // ���δָ��������ʹ����Ҫ����
            if (!$method) {
                $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                    ->where('is_primary', true)
                    ->first();
                
                if (!$mfaMethod) {
                    return [
                        'success' => false,
                        'message' => "δ�ҵ���������֤����"
                    ];
                }
                
                $method = $mfaMethod->method;
            } else {
                // ���ָ���ķ����Ƿ����
                $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                    ->where('method', $method)
                    ->first();
                
                if (!$mfaMethod) {
                    return [
                        'success' => false,
                        'message' => "��������֤���� {$method} δ����"
                    ];
                }
            }
            
            // ���ݲ�ͬ�ķ���������֤��
            switch ($method) {
                case 'app':
                    // Ӧ����֤����Ҫ������֤��
                    return [
                        'success' => true,
                        'message' => "������֤Ӧ���в鿴��֤��",
                        'data' => [
                            'method' => $method
                        ]
                    ];
                
                case 'sms':
                    // ������֤��
                    $verificationCode = sprintf('%06d', mt_rand(100000, 999999));
                    
                    // ������֤�뵽����
                    Cache::put("mfa_code:{$user->id}:sms", [
                        'verification_code' => $verificationCode,
                        'created_at' => now()->timestamp
                    ], now()->addMinutes(10));
                    
                    // ���Ͷ�����֤�루ģ�⣩
                    $phoneNumber = $mfaMethod->metadata['phone_number'] ?? $user->phone_number;
                    Log::info("����MFA������֤��", [
                        'phone' => $phoneNumber,
                        'code' => $verificationCode
                    ]);
                    
                    // ʵ��Ӧ����Ӧ���ö��ŷ��ͷ���
                    // $this->smsService->send($phoneNumber, "������֤���ǣ�{$verificationCode}��10��������Ч��");
                    
                    return [
                        'success' => true,
                        'message' => "��֤���ѷ��͵������ֻ�",
                        'data' => [
                            'method' => $method,
                            'phone_number' => substr($phoneNumber, 0, 3) . '****' . substr($phoneNumber, -4)
                        ]
                    ];
                
                case 'email':
                    // ������֤��
                    $verificationCode = sprintf('%06d', mt_rand(100000, 999999));
                    
                    // ������֤�뵽����
                    Cache::put("mfa_code:{$user->id}:email", [
                        'verification_code' => $verificationCode,
                        'created_at' => now()->timestamp
                    ], now()->addMinutes(30));
                    
                    // �����ʼ���֤�루ģ�⣩
                    $email = $mfaMethod->metadata['email'] ?? $user->email;
                    Log::info("����MFA�ʼ���֤��", [
                        'email' => $email,
                        'code' => $verificationCode
                    ]);
                    
                    // ʵ��Ӧ����Ӧ�����ʼ�
                    // Mail::to($email)->send(new MfaVerificationMail($verificationCode));
                    
                    return [
                        'success' => true,
                        'message' => "��֤���ѷ��͵���������",
                        'data' => [
                            'method' => $method,
                            'email' => substr($email, 0, 3) . '***' . strstr($email, '@')
                        ]
                    ];
                
                case 'fingerprint':
                    // ������ս��
                    $challengeCode = Str::random(32);
                    
                    // ������ս�뵽����
                    Cache::put("mfa_code:{$user->id}:fingerprint", [
                        'challenge_code' => $challengeCode,
                        'created_at' => now()->timestamp
                    ], now()->addMinutes(10));
                    
                    return [
                        'success' => true,
                        'message' => "�����豸�����ָ����֤",
                        'data' => [
                            'method' => $method,
                            'challenge_code' => $challengeCode
                        ]
                    ];
                
                default:
                    return [
                        'success' => false,
                        'message' => "��֧�ֵĶ�������֤������{$method}"
                    ];
            }
        } catch (\Exception $e) {
            Log::error("���Ͷ�������֤��֤��ʧ��", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "������֤��ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }

    /**
     * ��֤��������֤
     * 
     * @param User $user �û�
     * @param string $method ��֤����
     * @param string $code ��֤��
     * @return array ��֤���
     */
    public function verifyMfa(User $user, string $method, string $code): array
    {
        try {
            // ����MFA����
            $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                ->where('method', $method)
                ->first();
            
            if (!$mfaMethod) {
                return [
                    'success' => false,
                    'message' => "��������֤���� {$method} δ����"
                ];
            }
            
            $verified = false;
            
            // ���ݲ�ͬ�ķ���������֤
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
                            'message' => "��֤���ѹ��ڣ������»�ȡ"
                        ];
                    }
                    
                    $verified = $codeData['verification_code'] === $code;
                    
                    // ��֤�ɹ����������
                    if ($verified) {
                        Cache::forget("mfa_code:{$user->id}:sms");
                    }
                    break;
                
                case 'email':
                    $codeData = Cache::get("mfa_code:{$user->id}:email");
                    if (!$codeData) {
                        return [
                            'success' => false,
                            'message' => "��֤���ѹ��ڣ������»�ȡ"
                        ];
                    }
                    
                    $verified = $codeData['verification_code'] === $code;
                    
                    // ��֤�ɹ����������
                    if ($verified) {
                        Cache::forget("mfa_code:{$user->id}:email");
                    }
                    break;
                
                case 'fingerprint':
                    // ָ����֤��Ҫ�ͻ�����ɣ������������֤
                    $verified = true;
                    
                    // �����ս�뻺��
                    Cache::forget("mfa_code:{$user->id}:fingerprint");
                    break;
            }
            
            if (!$verified) {
                // ��¼ʧ����־
                $this->logMfaEvent($user, "mfa_verification_failed", [
                    'method' => $method
                ]);
                
                return [
                    'success' => false,
                    'message' => "��֤�����"
                ];
            }
            
            // ��¼�ɹ���־
            $this->logMfaEvent($user, "mfa_verification_success", [
                'method' => $method
            ]);
            
            // ����MFA���������ʹ��ʱ��
            $mfaMethod->last_used_at = now();
            $mfaMethod->save();
            
            return [
                'success' => true,
                'message' => "��֤�ɹ�",
                'data' => [
                    'method' => $method
                ]
            ];
        } catch (\Exception $e) {
            Log::error("��֤��������֤ʧ��", [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "��֤ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ��ȡ�û���MFA����
     * 
     * @param User $user �û�
     * @return array MFA�����б�
     */
    public function getUserMfaMethods(User $user): array
    {
        $methods = UserMfaMethod::where('user_id', $user->id)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($method) {
                // �Ƴ���������
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
     * ������ҪMFA����
     * 
     * @param User $user �û�
     * @param int $methodId ����ID
     * @return array ���ý��
     */
    public function setPrimaryMfaMethod(User $user, int $methodId): array
    {
        try {
            // ����MFA����
            $mfaMethod = UserMfaMethod::where('user_id', $user->id)
                ->where('id', $methodId)
                ->first();
            
            if (!$mfaMethod) {
                return [
                    'success' => false,
                    'message' => "δ�ҵ�ָ���Ķ�������֤����"
                ];
            }
            
            // �����з�����Ϊ����Ҫ
            UserMfaMethod::where('user_id', $user->id)
                ->update(['is_primary' => false]);
            
            // �����µ���Ҫ����
            $mfaMethod->is_primary = true;
            $mfaMethod->save();
            
            // ��¼��ȫ��־
            $this->logMfaEvent($user, "mfa_primary_changed", [
                'method' => $mfaMethod->method,
                'method_id' => $mfaMethod->id
            ]);
            
            return [
                'success' => true,
                'message' => "������Ϊ��Ҫ��֤����",
                'data' => [
                    'method' => $mfaMethod->method,
                    'method_id' => $mfaMethod->id
                ]
            ];
        } catch (\Exception $e) {
            Log::error("������ҪMFA����ʧ��", [
                'user_id' => $user->id,
                'method_id' => $methodId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "����ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ���ɻָ�����
     * 
     * @param User $user �û�
     * @return array �ָ�����
     */
    public function generateRecoveryCodes(User $user): array
    {
        try {
            // ����10���ָ�����
            $recoveryCodes = [];
            for ($i = 0; $i < 10; $i++) {
                $recoveryCodes[] = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
            }
            
            // ����ָ����루��ϣ��
            $hashedCodes = [];
            foreach ($recoveryCodes as $code) {
                $hashedCodes[] = [
                    'code' => hash('sha256', $code),
                    'used' => false
                ];
            }
            
            // �����û��Ļָ�����
            $user->mfa_recovery_codes = $hashedCodes;
            $user->save();
            
            // ��¼��ȫ��־
            $this->logMfaEvent($user, "recovery_codes_generated", [
                'count' => count($recoveryCodes)
            ]);
            
            return [
                'success' => true,
                'message' => "�����ɻָ�����",
                'data' => [
                    'recovery_codes' => $recoveryCodes
                ]
            ];
        } catch (\Exception $e) {
            Log::error("���ɻָ�����ʧ��", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "���ɻָ�����ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ��֤�ָ�����
     * 
     * @param User $user �û�
     * @param string $code �ָ�����
     * @return array ��֤���
     */
    public function verifyRecoveryCode(User $user, string $code): array
    {
        try {
            // ��ȡ�û��Ļָ�����
            $recoveryCodes = $user->mfa_recovery_codes ?? [];
            
            if (empty($recoveryCodes)) {
                return [
                    'success' => false,
                    'message' => "δ���ûָ�����"
                ];
            }
            
            // ����ƥ��Ļָ�����
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
                // ��¼ʧ����־
                $this->logMfaEvent($user, "recovery_code_verification_failed", []);
                
                return [
                    'success' => false,
                    'message' => "�ָ�������Ч����ʹ��"
                ];
            }
            
            // ���»ָ�����״̬
            $user->mfa_recovery_codes = $updatedCodes;
            $user->save();
            
            // ��¼�ɹ���־
            $this->logMfaEvent($user, "recovery_code_used", []);
            
            return [
                'success' => true,
                'message' => "�ָ�������֤�ɹ�",
                'data' => [
                    'remaining_codes' => count(array_filter($updatedCodes, function ($code) {
                        return !$code['used'];
                    }))
                ]
            ];
        } catch (\Exception $e) {
            Log::error("��֤�ָ�����ʧ��", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "��֤�ָ�����ʧ�ܣ�" . $e->getMessage()
            ];
        }
    }
    
    /**
     * ��¼MFA�¼�
     * 
     * @param User $user �û�
     * @param string $eventType �¼�����
     * @param array $metadata Ԫ����
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

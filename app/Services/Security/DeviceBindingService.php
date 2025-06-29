<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DeviceBindingService
{
    /**
     * �����豸�󶨶�ά��
     * 
     * @param User $user �û�
     * @return array ��ά������
     */
    public function generateBindingQrCode(User $user): array
    {
        // ����Ψһ�İ���
        $bindingCode = Str::random(32);
        
        // ���ɰ�����
        $bindingData = [
            'user_id' => $user->id,
            'binding_code' => $bindingCode,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'created_at' => now()->timestamp,
        ];
        
        // �������ݴ��뻺��
        Cache::put("device_binding:{$bindingCode}", $bindingData, now()->addMinutes(10));
        
        // ������URL
        $bindingUrl = url("/auth/device/bind/{$bindingCode}");
        
        // ���ɶ�ά��
        $qrCode = base64_encode(QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($bindingUrl));
        
        return [
            'binding_code' => $bindingCode,
            'qr_code' => "data:image/png;base64,{$qrCode}",
            'expires_at' => $bindingData['expires_at'],
            'url' => $bindingUrl,
        ];
    }
    
    /**
     * ���豸
     * 
     * @param string $bindingCode ����
     * @param array $deviceInfo �豸��Ϣ
     * @return array �󶨽��
     */
    public function bindDevice(string $bindingCode, array $deviceInfo): array
    {
        // �ӻ����л�ȡ������
        $bindingData = Cache::get("device_binding:{$bindingCode}");
        
        if (!$bindingData) {
            return [
                'success' => false,
                'message' => '������Ч���ѹ���'
            ];
        }
        
        // �������Ƿ����
        if ($bindingData['expires_at'] < now()->timestamp) {
            return [
                'success' => false,
                'message' => '�����ѹ���'
            ];
        }
        
        try {
            // ��ȡ�û�
            $user = User::find($bindingData['user_id']);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => '�û�������'
                ];
            }
            
            // ����豸�Ƿ��Ѱ�
            $existingDevice = UserDevice::where('user_id', $user->id)
                ->where('device_id', $deviceInfo['device_id'])
                ->first();
            
            if ($existingDevice) {
                // �����豸��Ϣ
                $existingDevice->update([
                    'device_name' => $deviceInfo['device_name'] ?? $existingDevice->device_name,
                    'device_model' => $deviceInfo['device_model'] ?? $existingDevice->device_model,
                    'os_version' => $deviceInfo['os_version'] ?? $existingDevice->os_version,
                    'last_active_at' => now(),
                    'is_verified' => true,
                ]);
                
                return [
                    'success' => true,
                    'message' => '�豸�Ѹ���',
                    'device' => $existingDevice
                ];
            }
            
            // �������豸��¼
            $device = new UserDevice();
            $device->user_id = $user->id;
            $device->device_id = $deviceInfo['device_id'];
            $device->device_name = $deviceInfo['device_name'] ?? 'δ�����豸';
            $device->device_type = $deviceInfo['device_type'] ?? 'unknown';
            $device->device_model = $deviceInfo['device_model'] ?? '';
            $device->os_type = $deviceInfo['os_type'] ?? 'unknown';
            $device->os_version = $deviceInfo['os_version'] ?? '';
            $device->app_version = $deviceInfo['app_version'] ?? '';
            $device->device_fingerprint = $deviceInfo['device_fingerprint'] ?? '';
            $device->phone_number = $deviceInfo['phone_number'] ?? null;
            $device->imei = $deviceInfo['imei'] ?? null;
            $device->mac_address = $deviceInfo['mac_address'] ?? null;
            $device->is_verified = true;
            $device->last_active_at = now();
            $device->save();
            
            // ����󶨻���
            Cache::forget("device_binding:{$bindingCode}");
            
            // ��¼�豸����־
            Log::channel('security')->info("�豸�󶨳ɹ�", [
                'user_id' => $user->id,
                'device_id' => $device->device_id,
                'device_type' => $device->device_type,
            ]);
            
            return [
                'success' => true,
                'message' => '�豸�󶨳ɹ�',
                'device' => $device
            ];
        } catch (\Exception $e) {
            Log::error("�豸��ʧ��", [
                'binding_code' => $bindingCode,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => '�豸��ʧ�ܣ�' . $e->getMessage()
            ];
        }
    }
    
    /**
     * ��֤�豸
     * 
     * @param User $user �û�
     * @param Request $request �������
     * @return array ��֤���
     */
    public function verifyDevice(User $user, Request $request): array
    {
        // ��ȡ�豸ID
        $deviceId = $request->header('X-Device-ID');
        $deviceFingerprint = $request->header('X-Device-Fingerprint');
        
        if (!$deviceId && !$deviceFingerprint) {
            return [
                'success' => false,
                'verified' => false,
                'message' => 'δ�ṩ�豸��ʶ',
                'requires_binding' => true
            ];
        }
        
        // ��ѯ�豸
        $query = UserDevice::where('user_id', $user->id);
        
        if ($deviceId) {
            $query->where('device_id', $deviceId);
        } elseif ($deviceFingerprint) {
            $query->where('device_fingerprint', $deviceFingerprint);
        }
        
        $device = $query->first();
        
        // ����豸������
        if (!$device) {
            return [
                'success' => false,
                'verified' => false,
                'message' => '�豸δ��',
                'requires_binding' => true
            ];
        }
        
        // �����豸�ʱ��
        $device->last_active_at = now();
        $device->save();
        
        // ����豸δ��֤
        if (!$device->is_verified) {
            return [
                'success' => false,
                'verified' => false,
                'message' => '�豸δ��֤',
                'requires_verification' => true,
                'device' => $device
            ];
        }
        
        return [
            'success' => true,
            'verified' => true,
            'message' => '�豸����֤',
            'device' => $device
        ];
    }
    
    /**
     * ����豸
     * 
     * @param User $user �û�
     * @param int $deviceId �豸ID
     * @return array �����
     */
    public function unbindDevice(User $user, int $deviceId): array
    {
        try {
            // ��ѯ�豸
            $device = UserDevice::where('user_id', $user->id)
                ->where('id', $deviceId)
                ->first();
            
            if (!$device) {
                return [
                    'success' => false,
                    'message' => '�豸�����ڻ����ڵ�ǰ�û�'
                ];
            }
            
            // ɾ���豸
            $device->delete();
            
            // ��¼�豸�����־
            Log::channel('security')->info("�豸���ɹ�", [
                'user_id' => $user->id,
                'device_id' => $device->device_id,
            ]);
            
            return [
                'success' => true,
                'message' => '�豸���ɹ�'
            ];
        } catch (\Exception $e) {
            Log::error("�豸���ʧ��", [
                'user_id' => $user->id,
                'device_id' => $deviceId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => '�豸���ʧ�ܣ�' . $e->getMessage()
            ];
        }
    }
    
    /**
     * ��ȡ�û��豸�б�
     * 
     * @param User $user �û�
     * @return array �豸�б�
     */
    public function getUserDevices(User $user): array
    {
        $devices = UserDevice::where('user_id', $user->id)
            ->orderBy('last_active_at', 'desc')
            ->get();
        
        return [
            'success' => true,
            'devices' => $devices
        ];
    }
}

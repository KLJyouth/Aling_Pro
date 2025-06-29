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
     * 生成设备绑定二维码
     * 
     * @param User $user 用户
     * @return array 二维码数据
     */
    public function generateBindingQrCode(User $user): array
    {
        // 生成唯一的绑定码
        $bindingCode = Str::random(32);
        
        // 生成绑定数据
        $bindingData = [
            'user_id' => $user->id,
            'binding_code' => $bindingCode,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'created_at' => now()->timestamp,
        ];
        
        // 将绑定数据存入缓存
        Cache::put("device_binding:{$bindingCode}", $bindingData, now()->addMinutes(10));
        
        // 创建绑定URL
        $bindingUrl = url("/auth/device/bind/{$bindingCode}");
        
        // 生成二维码
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
     * 绑定设备
     * 
     * @param string $bindingCode 绑定码
     * @param array $deviceInfo 设备信息
     * @return array 绑定结果
     */
    public function bindDevice(string $bindingCode, array $deviceInfo): array
    {
        // 从缓存中获取绑定数据
        $bindingData = Cache::get("device_binding:{$bindingCode}");
        
        if (!$bindingData) {
            return [
                'success' => false,
                'message' => '绑定码无效或已过期'
            ];
        }
        
        // 检查绑定码是否过期
        if ($bindingData['expires_at'] < now()->timestamp) {
            return [
                'success' => false,
                'message' => '绑定码已过期'
            ];
        }
        
        try {
            // 获取用户
            $user = User::find($bindingData['user_id']);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => '用户不存在'
                ];
            }
            
            // 检查设备是否已绑定
            $existingDevice = UserDevice::where('user_id', $user->id)
                ->where('device_id', $deviceInfo['device_id'])
                ->first();
            
            if ($existingDevice) {
                // 更新设备信息
                $existingDevice->update([
                    'device_name' => $deviceInfo['device_name'] ?? $existingDevice->device_name,
                    'device_model' => $deviceInfo['device_model'] ?? $existingDevice->device_model,
                    'os_version' => $deviceInfo['os_version'] ?? $existingDevice->os_version,
                    'last_active_at' => now(),
                    'is_verified' => true,
                ]);
                
                return [
                    'success' => true,
                    'message' => '设备已更新',
                    'device' => $existingDevice
                ];
            }
            
            // 创建新设备记录
            $device = new UserDevice();
            $device->user_id = $user->id;
            $device->device_id = $deviceInfo['device_id'];
            $device->device_name = $deviceInfo['device_name'] ?? '未命名设备';
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
            
            // 清除绑定缓存
            Cache::forget("device_binding:{$bindingCode}");
            
            // 记录设备绑定日志
            Log::channel('security')->info("设备绑定成功", [
                'user_id' => $user->id,
                'device_id' => $device->device_id,
                'device_type' => $device->device_type,
            ]);
            
            return [
                'success' => true,
                'message' => '设备绑定成功',
                'device' => $device
            ];
        } catch (\Exception $e) {
            Log::error("设备绑定失败", [
                'binding_code' => $bindingCode,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => '设备绑定失败：' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 验证设备
     * 
     * @param User $user 用户
     * @param Request $request 请求对象
     * @return array 验证结果
     */
    public function verifyDevice(User $user, Request $request): array
    {
        // 获取设备ID
        $deviceId = $request->header('X-Device-ID');
        $deviceFingerprint = $request->header('X-Device-Fingerprint');
        
        if (!$deviceId && !$deviceFingerprint) {
            return [
                'success' => false,
                'verified' => false,
                'message' => '未提供设备标识',
                'requires_binding' => true
            ];
        }
        
        // 查询设备
        $query = UserDevice::where('user_id', $user->id);
        
        if ($deviceId) {
            $query->where('device_id', $deviceId);
        } elseif ($deviceFingerprint) {
            $query->where('device_fingerprint', $deviceFingerprint);
        }
        
        $device = $query->first();
        
        // 如果设备不存在
        if (!$device) {
            return [
                'success' => false,
                'verified' => false,
                'message' => '设备未绑定',
                'requires_binding' => true
            ];
        }
        
        // 更新设备活动时间
        $device->last_active_at = now();
        $device->save();
        
        // 如果设备未验证
        if (!$device->is_verified) {
            return [
                'success' => false,
                'verified' => false,
                'message' => '设备未验证',
                'requires_verification' => true,
                'device' => $device
            ];
        }
        
        return [
            'success' => true,
            'verified' => true,
            'message' => '设备已验证',
            'device' => $device
        ];
    }
    
    /**
     * 解绑设备
     * 
     * @param User $user 用户
     * @param int $deviceId 设备ID
     * @return array 解绑结果
     */
    public function unbindDevice(User $user, int $deviceId): array
    {
        try {
            // 查询设备
            $device = UserDevice::where('user_id', $user->id)
                ->where('id', $deviceId)
                ->first();
            
            if (!$device) {
                return [
                    'success' => false,
                    'message' => '设备不存在或不属于当前用户'
                ];
            }
            
            // 删除设备
            $device->delete();
            
            // 记录设备解绑日志
            Log::channel('security')->info("设备解绑成功", [
                'user_id' => $user->id,
                'device_id' => $device->device_id,
            ]);
            
            return [
                'success' => true,
                'message' => '设备解绑成功'
            ];
        } catch (\Exception $e) {
            Log::error("设备解绑失败", [
                'user_id' => $user->id,
                'device_id' => $deviceId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => '设备解绑失败：' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取用户设备列表
     * 
     * @param User $user 用户
     * @return array 设备列表
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

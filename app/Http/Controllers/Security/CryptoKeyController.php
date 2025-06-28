<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\CryptoKey;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CryptoKeyController extends Controller
{
    /**
     * 获取所有密钥
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            
            $keys = CryptoKey::paginate($perPage, ['*'], 'page', $page);
            
            return response()->json([
                'success' => true,
                'data' => $keys
            ]);
        } catch (\Exception $e) {
            Log::error('获取密钥列表失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取密钥列表失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 创建新密钥
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:symmetric,asymmetric,quantum',
                'description' => 'nullable|string'
            ]);
            
            $key = new CryptoKey();
            $key->name = $request->input('name');
            $key->type = $request->input('type');
            $key->description = $request->input('description');
            $key->key_value = $this->generateKeyValue($request->input('type'));
            $key->status = 'active';
            $key->save();
            
            return response()->json([
                'success' => true,
                'data' => $key
            ], 201);
        } catch (\Exception $e) {
            Log::error('创建密钥失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '创建密钥失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取特定密钥
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $key = CryptoKey::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $key
            ]);
        } catch (\Exception $e) {
            Log::error('获取密钥详情失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取密钥详情失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 更新密钥
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'string|max:255',
                'description' => 'nullable|string',
                'status' => 'string|in:active,inactive,revoked'
            ]);
            
            $key = CryptoKey::findOrFail($id);
            
            if ($request->has('name')) {
                $key->name = $request->input('name');
            }
            
            if ($request->has('description')) {
                $key->description = $request->input('description');
            }
            
            if ($request->has('status')) {
                $key->status = $request->input('status');
            }
            
            $key->save();
            
            return response()->json([
                'success' => true,
                'data' => $key
            ]);
        } catch (\Exception $e) {
            Log::error('更新密钥失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '更新密钥失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 删除密钥
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $key = CryptoKey::findOrFail($id);
            $key->delete();
            
            return response()->json([
                'success' => true,
                'message' => '密钥已成功删除'
            ]);
        } catch (\Exception $e) {
            Log::error('删除密钥失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '删除密钥失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 生成密钥值
     * 
     * @param string $type 密钥类型
     * @return string
     */
    private function generateKeyValue(string $type): string
    {
        // 这只是一个简单的模拟，实际项目中应该使用更安全的密钥生成方法
        switch ($type) {
            case 'symmetric':
                return base64_encode(random_bytes(32)); // 256位对称密钥
            case 'asymmetric':
                // 模拟非对称密钥对
                $private = base64_encode(random_bytes(64));
                $public = base64_encode(random_bytes(32));
                return json_encode(['private' => $private, 'public' => $public]);
            case 'quantum':
                // 模拟量子密钥
                return base64_encode(random_bytes(64)) . '_quantum';
            default:
                return Str::random(64);
        }
    }
} 
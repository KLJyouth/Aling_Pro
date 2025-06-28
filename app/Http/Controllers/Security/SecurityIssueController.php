<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\SecurityIssue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SecurityIssueController extends Controller
{
    /**
     * 获取所有安全问题
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $status = $request->input('status');
            $severity = $request->input('severity');
            
            $query = SecurityIssue::query();
            
            if ($status) {
                $query->where('status', $status);
            }
            
            if ($severity) {
                $query->where('severity', $severity);
            }
            
            $issues = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
            
            return response()->json([
                'success' => true,
                'data' => $issues
            ]);
        } catch (\Exception $e) {
            Log::error('获取安全问题列表失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取安全问题列表失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 创建新安全问题
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'severity' => 'required|string|in:low,medium,high,critical',
                'type' => 'required|string',
                'source' => 'required|string'
            ]);
            
            $issue = new SecurityIssue();
            $issue->title = $request->input('title');
            $issue->description = $request->input('description');
            $issue->severity = $request->input('severity');
            $issue->type = $request->input('type');
            $issue->source = $request->input('source');
            $issue->status = 'open';
            $issue->details = $request->input('details', []);
            $issue->save();
            
            return response()->json([
                'success' => true,
                'data' => $issue
            ], 201);
        } catch (\Exception $e) {
            Log::error('创建安全问题失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '创建安全问题失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取特定安全问题详情
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $issue = SecurityIssue::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $issue
            ]);
        } catch (\Exception $e) {
            Log::error('获取安全问题详情失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取安全问题详情失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 更新安全问题
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'string|max:255',
                'description' => 'string',
                'severity' => 'string|in:low,medium,high,critical',
                'status' => 'string|in:open,in_progress,resolved,closed',
                'type' => 'string'
            ]);
            
            $issue = SecurityIssue::findOrFail($id);
            
            if ($request->has('title')) {
                $issue->title = $request->input('title');
            }
            
            if ($request->has('description')) {
                $issue->description = $request->input('description');
            }
            
            if ($request->has('severity')) {
                $issue->severity = $request->input('severity');
            }
            
            if ($request->has('status')) {
                $issue->status = $request->input('status');
            }
            
            if ($request->has('type')) {
                $issue->type = $request->input('type');
            }
            
            if ($request->has('details')) {
                $issue->details = $request->input('details');
            }
            
            $issue->save();
            
            return response()->json([
                'success' => true,
                'data' => $issue
            ]);
        } catch (\Exception $e) {
            Log::error('更新安全问题失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '更新安全问题失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 解决安全问题
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'resolution' => 'required|string',
                'resolution_details' => 'nullable|string'
            ]);
            
            $issue = SecurityIssue::findOrFail($id);
            
            if ($issue->status === 'resolved' || $issue->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => '该安全问题已经被解决或关闭'
                ], 400);
            }
            
            $issue->status = 'resolved';
            $issue->resolution = $request->input('resolution');
            $issue->resolution_details = $request->input('resolution_details');
            $issue->resolved_at = now();
            $issue->save();
            
            return response()->json([
                'success' => true,
                'data' => $issue,
                'message' => '安全问题已成功解决'
            ]);
        } catch (\Exception $e) {
            Log::error('解决安全问题失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '解决安全问题失败: ' . $e->getMessage()
            ], 500);
        }
    }
} 
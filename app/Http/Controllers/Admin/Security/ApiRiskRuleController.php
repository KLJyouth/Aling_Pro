<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Security\ApiControl\ApiRiskRule;
use App\Models\Security\ApiControl\ApiRiskEvent;
use App\Models\Admin\Management\AdminOperationLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ApiRiskRuleController extends Controller
{
    /**
     * 显示风控规则列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ApiRiskRule::with('creator');
        
        // 搜索条件
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('rule_type')) {
            $query->where('rule_type', $request->input('rule_type'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        
        $rules = $query->orderBy('priority')
            ->orderBy('id', 'desc')
            ->paginate(15);
        
        return view('admin.security.api.risk_rules.index', compact('rules'));
    }

    /**
     * 显示创建风控规则表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $ruleTypes = [
            'frequency' => '频率控制',
            'amount' => '金额控制',
            'behavior' => '行为控制',
            'ip' => 'IP控制',
            'device' => '设备控制',
            'geo' => '地理位置控制'
        ];
        
        $actions = [
            'warn' => '警告',
            'block' => '阻止',
            'suspend' => '暂停',
            'review' => '人工审核'
        ];
        
        return view('admin.security.api.risk_rules.create', compact('ruleTypes', 'actions'));
    }

    /**
     * 保存新风控规则
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'rule_type' => 'required|string|in:frequency,amount,behavior,ip,device,geo',
            'conditions' => 'required|array',
            'threshold_value' => 'nullable|numeric',
            'time_window' => 'nullable|integer',
            'action' => 'required|string|in:warn,block,suspend,review',
            'priority' => 'required|integer|min:1|max:10',
            'status' => 'required|string|in:active,inactive',
            'description' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $rule = new ApiRiskRule();
            $rule->name = $validatedData['name'];
            $rule->rule_type = $validatedData['rule_type'];
            $rule->conditions = $validatedData['conditions'];
            $rule->threshold_value = $validatedData['threshold_value'] ?? null;
            $rule->time_window = $validatedData['time_window'] ?? null;
            $rule->action = $validatedData['action'];
            $rule->priority = $validatedData['priority'];
            $rule->status = $validatedData['status'];
            $rule->description = $validatedData['description'] ?? null;
            $rule->created_by = Auth::guard('admin')->id();
            $rule->save();
            
            DB::commit();
            
            return redirect()->route('admin.api.risk.rules.index')
                ->with('success', '风控规则创建成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('风控规则创建失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '风控规则创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示风控规则详情
     *
     * @param ApiRiskRule $rule
     * @return \Illuminate\View\View
     */
    public function show(ApiRiskRule $rule)
    {
        $rule->load('creator');
        
        // 获取规则触发的事件数量
        $eventsCount = $rule->riskEvents()->count();
        $recentEvents = $rule->riskEvents()->orderBy('created_at', 'desc')->limit(5)->get();
        
        return view('admin.security.api.risk_rules.show', compact('rule', 'eventsCount', 'recentEvents'));
    }

    /**
     * 显示编辑风控规则表单
     *
     * @param ApiRiskRule $rule
     * @return \Illuminate\View\View
     */
    public function edit(ApiRiskRule $rule)
    {
        $ruleTypes = [
            'frequency' => '频率控制',
            'amount' => '金额控制',
            'behavior' => '行为控制',
            'ip' => 'IP控制',
            'device' => '设备控制',
            'geo' => '地理位置控制'
        ];
        
        $actions = [
            'warn' => '警告',
            'block' => '阻止',
            'suspend' => '暂停',
            'review' => '人工审核'
        ];
        
        return view('admin.security.api.risk_rules.edit', compact('rule', 'ruleTypes', 'actions'));
    }

    /**
     * 更新风控规则
     *
     * @param Request $request
     * @param ApiRiskRule $rule
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ApiRiskRule $rule)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'rule_type' => 'required|string|in:frequency,amount,behavior,ip,device,geo',
            'conditions' => 'required|array',
            'threshold_value' => 'nullable|numeric',
            'time_window' => 'nullable|integer',
            'action' => 'required|string|in:warn,block,suspend,review',
            'priority' => 'required|integer|min:1|max:10',
            'status' => 'required|string|in:active,inactive',
            'description' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $rule->name = $validatedData['name'];
            $rule->rule_type = $validatedData['rule_type'];
            $rule->conditions = $validatedData['conditions'];
            $rule->threshold_value = $validatedData['threshold_value'] ?? null;
            $rule->time_window = $validatedData['time_window'] ?? null;
            $rule->action = $validatedData['action'];
            $rule->priority = $validatedData['priority'];
            $rule->status = $validatedData['status'];
            $rule->description = $validatedData['description'] ?? null;
            $rule->save();
            
            DB::commit();
            
            return redirect()->route('admin.api.risk.rules.index')
                ->with('success', '风控规则更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('风控规则更新失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', '风控规则更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除风控规则
     *
     * @param ApiRiskRule $rule
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ApiRiskRule $rule)
    {
        try {
            // 检查规则是否已经触发过风险事件
            $eventsCount = $rule->riskEvents()->count();
            if ($eventsCount > 0) {
                return redirect()->back()
                    ->with('error', "该规则已触发 {$eventsCount} 个风险事件，不能删除。请考虑将其设置为非活动状态。");
            }
            
            $rule->delete();
            
            return redirect()->route('admin.api.risk.rules.index')
                ->with('success', '风控规则删除成功');
        } catch (\Exception $e) {
            Log::error('风控规则删除失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '风控规则删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 切换规则状态
     *
     * @param ApiRiskRule $rule
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(ApiRiskRule $rule)
    {
        try {
            $rule->status = $rule->status === 'active' ? 'inactive' : 'active';
            $rule->save();
            
            $statusText = $rule->status === 'active' ? '启用' : '禁用';
            
            return redirect()->back()
                ->with('success', "风控规则已{$statusText}");
        } catch (\Exception $e) {
            Log::error('切换风控规则状态失败: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '切换风控规则状态失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示测试规则表单
     *
     * @param ApiRiskRule $rule
     * @return \Illuminate\View\View
     */
    public function showTestForm(ApiRiskRule $rule)
    {
        return view('admin.security.api.risk_rules.test', compact('rule'));
    }

    /**
     * 测试风控规则
     *
     * @param Request $request
     * @param ApiRiskRule $rule
     * @return \Illuminate\Http\JsonResponse
     */
    public function testRule(Request $request, ApiRiskRule $rule)
    {
        $testData = $request->input('test_data');
        
        try {
            // 解析测试数据
            $testData = json_decode($testData, true);
            if (!is_array($testData)) {
                return response()->json([
                    'success' => false,
                    'message' => '测试数据格式无效，请提供有效的JSON对象'
                ]);
            }
            
            // 模拟规则评估
            $result = $this->evaluateRule($rule, $testData);
            
            return response()->json([
                'success' => true,
                'triggered' => $result['triggered'],
                'details' => $result['details']
            ]);
        } catch (\Exception $e) {
            Log::error('测试风控规则失败: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '测试风控规则失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 评估规则（模拟）
     *
     * @param ApiRiskRule $rule
     * @param array $data
     * @return array
     */
    protected function evaluateRule(ApiRiskRule $rule, array $data)
    {
        $triggered = false;
        $details = [];
        
        // 根据规则类型进行不同的评估
        switch ($rule->rule_type) {
            case 'frequency':
                // 检查请求频率
                if (isset($data['request_count'], $data['time_period'])) {
                    $rate = $data['request_count'] / $data['time_period'];
                    $threshold = $rule->threshold_value / $rule->time_window;
                    $triggered = $rate > $threshold;
                    $details = [
                        'actual_rate' => $rate . ' 请求/秒',
                        'threshold' => $threshold . ' 请求/秒',
                        'comparison' => $triggered ? '超过阈值' : '未超过阈值'
                    ];
                }
                break;
                
            case 'amount':
                // 检查交易金额
                if (isset($data['amount'])) {
                    $triggered = $data['amount'] > $rule->threshold_value;
                    $details = [
                        'actual_amount' => $data['amount'],
                        'threshold' => $rule->threshold_value,
                        'comparison' => $triggered ? '超过阈值' : '未超过阈值'
                    ];
                }
                break;
                
            case 'behavior':
                // 检查行为模式
                if (isset($data['pattern'])) {
                    $conditions = $rule->conditions;
                    if (isset($conditions['patterns']) && in_array($data['pattern'], $conditions['patterns'])) {
                        $triggered = true;
                        $details = [
                            'detected_pattern' => $data['pattern'],
                            'matched_pattern' => $data['pattern'],
                            'comparison' => '匹配可疑行为模式'
                        ];
                    }
                }
                break;
                
            case 'ip':
                // 检查IP地址
                if (isset($data['ip'])) {
                    $conditions = $rule->conditions;
                    if (isset($conditions['blocked_ips']) && in_array($data['ip'], $conditions['blocked_ips'])) {
                        $triggered = true;
                        $details = [
                            'ip' => $data['ip'],
                            'matched_rule' => '黑名单IP',
                            'comparison' => 'IP在黑名单中'
                        ];
                    }
                }
                break;
                
            case 'device':
                // 检查设备信息
                if (isset($data['device_id'])) {
                    $conditions = $rule->conditions;
                    if (isset($conditions['suspicious_devices']) && in_array($data['device_id'], $conditions['suspicious_devices'])) {
                        $triggered = true;
                        $details = [
                            'device_id' => $data['device_id'],
                            'matched_rule' => '可疑设备',
                            'comparison' => '设备在可疑列表中'
                        ];
                    }
                }
                break;
                
            case 'geo':
                // 检查地理位置
                if (isset($data['country'])) {
                    $conditions = $rule->conditions;
                    if (isset($conditions['blocked_countries']) && in_array($data['country'], $conditions['blocked_countries'])) {
                        $triggered = true;
                        $details = [
                            'country' => $data['country'],
                            'matched_rule' => '受限国家/地区',
                            'comparison' => '国家/地区在受限列表中'
                        ];
                    }
                }
                break;
        }
        
        return [
            'triggered' => $triggered,
            'details' => $details
        ];
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\Membership\QuotaService;
use Closure;
use Illuminate\Http\Request;

class CheckQuota
{
    /**
     * 额度服务
     *
     * @var QuotaService
     */
    protected \;

    /**
     * 创建中间件实例
     *
     * @param QuotaService \
     * @return void
     */
    public function __construct(QuotaService \)
    {
        \->quotaService = \;
    }

    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  \
     * @param  \Closure  \
     * @param  string  \
     * @param  int  \
     * @return mixed
     */
    public function handle(Request \, Closure \, string \, int \ = 1)
    {
        \ = \->user();
        
        // 如果用户未登录，跳过检查
        if (!\) {
            return \(\);
        }
        
        // 检查用户是否有足够的额度
        if (!\->quotaService->hasEnoughQuota(\, \, \)) {
            // 根据请求类型返回不同的响应
            if (\->expectsJson()) {
                return response()->json([
                    'code' => 403,
                    'message' => '您的' . \->getQuotaTypeName(\) . '额度不足，请升级会员',
                    'data' => [
                        'upgrade_url' => route('user.membership.plans'),
                    ],
                ], 403);
            }
            
            return redirect()->route('user.membership.plans')
                ->with('error', '您的' . \->getQuotaTypeName(\) . '额度不足，请升级会员');
        }
        
        // 记录额度使用
        \->quotaService->recordUsage(\, \, \);
        
        return \(\);
    }
    
    /**
     * 获取额度类型名称
     *
     * @param string \
     * @return string
     */
    protected function getQuotaTypeName(string \): string
    {
        \ = [
            'api' => 'API调用',
            'ai' => 'AI处理',
            'storage' => '存储空间',
            'bandwidth' => '带宽',
        ];
        
        return \[\] ?? \;
    }
}

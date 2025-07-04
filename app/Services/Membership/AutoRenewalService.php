<?php

namespace App\Services\Membership;

use App\Models\Membership\MembershipSubscription;
use App\Models\Order;
use App\Services\Payment\PaymentFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoRenewalService
{
    /**
     * 处理自动续费
     *
     * @return void
     */
    public function processAutoRenewals()
    {
        // 获取即将到期（3天内）且开启了自动续费的订阅
        \ = MembershipSubscription::where('status', 'active')
            ->where('auto_renew', true)
            ->where('end_date', '<=', now()->addDays(3))
            ->where('end_date', '>', now())
            ->get();
            
        foreach (\ as \) {
            \->processSubscriptionRenewal(\);
        }
    }
    
    /**
     * 处理单个订阅的自动续费
     *
     * @param MembershipSubscription \
     * @return void
     */
    protected function processSubscriptionRenewal(MembershipSubscription \)
    {
        \ = \->user;
        \ = \->level;
        
        // 检查会员等级是否仍然有效
        if (\->status !== 'active') {
            Log::warning('会员等级已停用，无法自动续费', [
                'subscription_id' => \->id,
                'level_id' => \->id,
                'user_id' => \->id,
            ]);
            return;
        }
        
        // 确定价格和时长
        \ = \->subscription_type === 'monthly' ? \->price_monthly : \->price_yearly;
        \ = \->subscription_type === 'monthly' ? 30 : 365;
        
        // 创建订单
        DB::beginTransaction();
        try {
            // 创建订单
            \ = new Order();
            \->user_id = \->id;
            \->order_number = 'MEM' . date('YmdHis') . rand(1000, 9999);
            \->order_type = 'membership_renewal';
            \->subtotal_amount = \;
            \->discount_amount = 0;
            \->total_amount = \;
            \->status = 'pending';
            \->payment_method = 'auto';  // 自动续费
            \->metadata = [
                'level_id' => \->id,
                'level_name' => \->name,
                'subscription_id' => \->id,
                'subscription_type' => \->subscription_type,
                'duration_days' => \,
            ];
            \->save();
            
            DB::commit();
        } catch (\Exception \) {
            DB::rollBack();
            Log::error('创建自动续费订单失败', [
                'error' => \->getMessage(),
                'subscription_id' => \->id,
                'user_id' => \->id,
            ]);
            return;
        }
        
        // 处理支付
        try {
            // 这里应该集成实际的支付处理逻辑，例如扣款等
            // 此处仅为示例，实际应该根据用户的支付方式进行处理
            
            // 模拟支付成功
            \->processSuccessfulRenewal(\);
            
            Log::info('会员自动续费成功', [
                'subscription_id' => \->id,
                'order_id' => \->id,
                'user_id' => \->id,
            ]);
        } catch (\Exception \) {
            Log::error('会员自动续费支付失败', [
                'error' => \->getMessage(),
                'order_id' => \->id,
                'subscription_id' => \->id,
                'user_id' => \->id,
            ]);
        }
    }
    
    /**
     * 处理续费成功
     *
     * @param Order \
     * @return void
     */
    protected function processSuccessfulRenewal(Order \)
    {
        \ = \->metadata;
        \ = \['subscription_id'];
        \ = \['duration_days'];
        
        \ = MembershipSubscription::find(\);
        if (!\) {
            throw new \Exception('订阅不存在');
        }
        
        DB::beginTransaction();
        try {
            // 更新订阅结束时间
            \->end_date = Carbon::parse(\->end_date)->addDays(\);
            \->save();
            
            // 更新订单状态
            \->status = 'paid';
            \->paid_at = now();
            \->save();
            
            // 创建新的交易记录
            \ = new \App\Models\Transaction();
            \->user_id = \->user_id;
            \->order_id = \->id;
            \->transaction_no = 'TRX' . date('YmdHis') . rand(1000, 9999);
            \->amount = \->total_amount;
            \->type = 'membership_renewal';
            \->status = 'success';
            \->payment_method = 'auto';
            \->metadata = [
                'subscription_id' => \->id,
                'level_id' => \->membership_level_id,
            ];
            \->save();
            
            DB::commit();
        } catch (\Exception \) {
            DB::rollBack();
            Log::error('处理自动续费成功失败', [
                'error' => \->getMessage(),
                'order_id' => \->id,
                'subscription_id' => \,
            ]);
            throw \;
        }
    }
}

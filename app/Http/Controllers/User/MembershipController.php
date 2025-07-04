<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Membership\MembershipLevel;
use App\Models\Membership\MembershipSubscription;
use App\Models\Order;
use App\Services\Payment\PaymentFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MembershipController extends Controller
{
    /**
     * 显示会员中心页面
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
         = auth()->user();
         = ->activeSubscription();
         = ->subscriptions()->orderBy('created_at', 'desc')->get();
        
        return view('user.membership.index', [
            'currentSubscription' => ,
            'subscriptionHistory' => ,
        ]);
    }
}
    
    /**
     * 显示会员套餐选择页面
     *
     * @return \Illuminate\View\View
     */
    public function plans()
    {
        \ = MembershipLevel::where('status', 'active')
            ->orderBy('sort_order')
            ->get();
            
        return view('user.membership.plans', [
            'levels' => \,
        ]);
    }
    
    /**
     * 处理会员订阅请求
     *
     * @param Request \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function subscribe(Request \)
    {
        \->validate([
            'level_id' => 'required|exists:membership_levels,id',
            'subscription_type' => 'required|in:monthly,yearly',
        ]);
        
        \ = MembershipLevel::findOrFail(\->level_id);
        \ = auth()->user();
        
        // 检查会员等级状态
        if (\->status !== 'active') {
            return back()->with('error', '该会员等级暂不可订阅');
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
            \->order_number = \->generateOrderNumber();
            \->order_type = 'membership';
            \->subtotal_amount = \;
            \->discount_amount = 0;
            \->total_amount = \;
            \->status = 'pending';
            \->payment_method = \->input('payment_method', 'alipay');
            \->metadata = [
                'level_id' => \->id,
                'level_name' => \->name,
                'subscription_type' => \->subscription_type,
                'duration_days' => \,
            ];
            \->save();
            
            DB::commit();
        } catch (\Exception \) {
            DB::rollBack();
            Log::error('创建会员订阅订单失败', [
                'error' => \->getMessage(),
                'level_id' => \->id,
                'user_id' => \->id,
            ]);
            return back()->with('error', '创建订单失败，请稍后再试');
        }
        
        // 创建支付
        try {
            \ = PaymentFactory::create(\->payment_method);
            \ = \->createPayment(\);
            
            if (!\['success']) {
                return back()->with('error', \['message']);
            }
            
            // 跳转到支付页面
            return view('user.membership.pay', [
                'order' => \,
                'level' => \,
                'payment_data' => \['data'],
            ]);
        } catch (\Exception \) {
            Log::error('创建会员订阅支付失败', [
                'error' => \->getMessage(),
                'order_id' => \->id,
                'payment_method' => \->payment_method,
            ]);
            return back()->with('error', '创建支付失败，请稍后再试');
        }
    }
    
    /**
     * 处理支付成功后的会员订阅激活
     *
     * @param Order \
     * @return void
     */
    public function activateSubscription(Order \)
    {
        // 验证订单状态
        if (\->status !== 'paid') {
            throw new \Exception('订单未支付，无法激活会员');
        }
        
        // 验证订单类型
        if (\->order_type !== 'membership') {
            throw new \Exception('订单类型错误，无法激活会员');
        }
        
        \ = \->metadata;
        \ = \['level_id'];
        \ = \['subscription_type'];
        \ = \['duration_days'];
        
        \ = MembershipLevel::find(\);
        if (!\) {
            throw new \Exception('会员等级不存在');
        }
        
        \ = \->user;
        
        // 检查用户是否已有活跃订阅
        \ = \->activeSubscription();
        
        DB::beginTransaction();
        try {
            // 如果有活跃订阅，则延长其时间
            if (\ && \->level->id === \->id) {
                \->end_date = Carbon::parse(\->end_date)->addDays(\);
                \->save();
                
                \ = \;
            } else {
                // 如果没有活跃订阅或订阅的是不同等级，则创建新订阅
                // 如果有活跃订阅但等级不同，则取消旧订阅
                if (\) {
                    \->status = 'cancelled';
                    \->cancelled_at = now();
                    \->cancellation_reason = '用户升级/降级会员等级';
                    \->save();
                }
                
                // 创建新订阅
                \ = new MembershipSubscription();
                \->user_id = \->id;
                \->membership_level_id = \->id;
                \->order_id = \->id;
                \->subscription_no = 'SUB' . date('YmdHis') . rand(1000, 9999);
                \->start_date = now();
                \->end_date = now()->addDays(\);
                \->price_paid = \->total_amount;
                \->subscription_type = \;
                \->auto_renew = false; // 默认不自动续费
                \->status = 'active';
                \->save();
            }
            
            // 更新订单状态为完成
            \->status = 'completed';
            \->save();
            
            DB::commit();
            
            return \;
        } catch (\Exception \) {
            DB::rollBack();
            Log::error('激活会员订阅失败', [
                'error' => \->getMessage(),
                'order_id' => \->id,
                'user_id' => \->id,
            ]);
            throw \;
        }
    }
    
    /**
     * 会员续费
     *
     * @return \Illuminate\View\View
     */
    public function renew()
    {
        \ = auth()->user();
        \ = \->activeSubscription();
        
        if (!\) {
            return redirect()->route('user.membership.plans')
                ->with('error', '您当前没有活跃的会员订阅，请选择一个会员套餐');
        }
        
        return view('user.membership.renew', [
            'subscription' => \,
        ]);
    }
    
    /**
     * 开启自动续费
     *
     * @param Request \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enableAutoRenew(Request \)
    {
        \ = auth()->user();
        \ = \->activeSubscription();
        
        if (!\) {
            return back()->with('error', '您当前没有活跃的会员订阅');
        }
        
        \->auto_renew = true;
        \->save();
        
        return back()->with('status', '已成功开启自动续费');
    }
    
    /**
     * 取消自动续费
     *
     * @param Request \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelAutoRenew(Request \)
    {
        \ = auth()->user();
        \ = \->activeSubscription();
        
        if (!\) {
            return back()->with('error', '您当前没有活跃的会员订阅');
        }
        
        \->auto_renew = false;
        \->save();
        
        return back()->with('status', '已成功取消自动续费');
    }
    
    /**
     * 生成订单号
     *
     * @return string
     */
    protected function generateOrderNumber(): string
    {
        return 'MEM' . date('YmdHis') . rand(1000, 9999);
    }
}

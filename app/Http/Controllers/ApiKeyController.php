<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * �����µĿ�����ʵ��
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * ��ʾAPI��Կ����ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // ��ȡ�û���API��Կ
        $apiKeys = $user->apiKeys()->orderBy("created_at", "desc")->get();
        
        // ��ȡAPIʹ��ͳ��
        $apiUsageStats = $this->getApiUsageStats($user);
        
        return view("api.index", compact("user", "apiKeys", "apiUsageStats"));
    }

    /**
     * �����µ�API��Կ
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:100",
            "expires_at" => "nullable|date|after:today",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        
        try {
            // ����API��Կ
            $apiKey = new ApiKey();
            $apiKey->user_id = $user->id;
            $apiKey->name = $request->name;
            $apiKey->api_key = $this->generateApiKey();
            $apiKey->status = "active";
            $apiKey->expires_at = $request->filled("expires_at") ? $request->expires_at : null;
            $apiKey->save();
            
            // ��¼��־
            Log::info("API��Կ�����ɹ�", [
                "user_id" => $user->id,
                "api_key_id" => $apiKey->id
            ]);
            
            return redirect()->route("api-keys")->with([
                "success" => "API��Կ�����ɹ�",
                "new_api_key" => $apiKey->api_key
            ]);
        } catch (\Exception $e) {
            Log::error("API��Կ����ʧ��", [
                "error" => $e->getMessage(),
                "user_id" => $user->id
            ]);
            
            return redirect()->back()->with("error", "API��Կ����ʧ�ܣ����Ժ����ԡ�");
        }
    }

    /**
     * ����API��Կ
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:100",
            "status" => "required|in:active,inactive",
            "expires_at" => "nullable|date|after:today",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        
        // ��ȡAPI��Կ
        $apiKey = ApiKey::where("id", $id)
            ->where("user_id", $user->id)
            ->first();
        
        if (!$apiKey) {
            return redirect()->back()->with("error", "δ�ҵ�API��Կ��");
        }
        
        try {
            // ����API��Կ
            $apiKey->name = $request->name;
            $apiKey->status = $request->status;
            $apiKey->expires_at = $request->filled("expires_at") ? $request->expires_at : null;
            $apiKey->save();
            
            // ��¼��־
            Log::info("API��Կ���³ɹ�", [
                "user_id" => $user->id,
                "api_key_id" => $apiKey->id
            ]);
            
            return redirect()->route("api-keys")->with("success", "API��Կ���³ɹ���");
        } catch (\Exception $e) {
            Log::error("API��Կ����ʧ��", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "api_key_id" => $id
            ]);
            
            return redirect()->back()->with("error", "API��Կ����ʧ�ܣ����Ժ����ԡ�");
        }
    }

    /**
     * ɾ��API��Կ
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        // ��ȡAPI��Կ
        $apiKey = ApiKey::where("id", $id)
            ->where("user_id", $user->id)
            ->first();
        
        if (!$apiKey) {
            return redirect()->back()->with("error", "δ�ҵ�API��Կ��");
        }
        
        try {
            // ɾ��API��Կ
            $apiKey->delete();
            
            // ��¼��־
            Log::info("API��Կɾ���ɹ�", [
                "user_id" => $user->id,
                "api_key_id" => $id
            ]);
            
            return redirect()->route("api-keys")->with("success", "API��Կɾ���ɹ���");
        } catch (\Exception $e) {
            Log::error("API��Կɾ��ʧ��", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "api_key_id" => $id
            ]);
            
            return redirect()->back()->with("error", "API��Կɾ��ʧ�ܣ����Ժ����ԡ�");
        }
    }

    /**
     * ����ΨһAPI��Կ
     *
     * @return string
     */
    protected function generateApiKey()
    {
        $prefix = "ak_";
        $key = $prefix . Str::random(32);
        
        // ȷ����ԿΨһ
        while (ApiKey::where("api_key", $key)->exists()) {
            $key = $prefix . Str::random(32);
        }
        
        return $key;
    }

    /**
     * ��ȡAPIʹ��ͳ��
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    protected function getApiUsageStats($user)
    {
        $apiKeys = $user->apiKeys;
        
        // ����API���ô���
        $todayCalls = 0;
        
        // ����API���ô���
        $monthCalls = 0;
        
        // ��API���ô���
        $totalCalls = 0;
        
        // ��״̬��ͳ��
        $statusStats = [
            "success" => 0,
            "error" => 0
        ];
        
        // ���˵�ͳ��
        $endpointStats = [];
        
        foreach ($apiKeys as $apiKey) {
            // ��ȡAPI��־
            $logs = $apiKey->logs;
            
            foreach ($logs as $log) {
                $totalCalls++;
                
                // ���յ���
                if ($log->created_at->isToday()) {
                    $todayCalls++;
                }
                
                // ���µ���
                if ($log->created_at->isCurrentMonth()) {
                    $monthCalls++;
                }
                
                // ״̬ͳ��
                if ($log->status_code >= 200 && $log->status_code < 300) {
                    $statusStats["success"]++;
                } else {
                    $statusStats["error"]++;
                }
                
                // �˵�ͳ��
                $endpoint = $log->endpoint;
                if (!isset($endpointStats[$endpoint])) {
                    $endpointStats[$endpoint] = 0;
                }
                $endpointStats[$endpoint]++;
            }
        }
        
        // �����ô�������˵�
        arsort($endpointStats);
        
        // ֻ����ǰ5���˵�
        $endpointStats = array_slice($endpointStats, 0, 5, true);
        
        return [
            "today" => $todayCalls,
            "month" => $monthCalls,
            "total" => $totalCalls,
            "status" => $statusStats,
            "endpoints" => $endpointStats
        ];
    }
}

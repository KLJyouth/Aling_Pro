<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SettingService;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;

/**
 * ��վ����API������
 * 
 * ������վ������ص�API����
 */
class SettingApiController extends Controller
{
    /**
     * ���÷���
     *
     * @var SettingService
     */
    protected $settingService;
    
    /**
     * ���캯��
     *
     * @param SettingService $settingService
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
        $this->middleware("auth:api")->except(["getPublicSettings"]);
        $this->middleware("role:admin")->except(["getPublicSettings"]);
    }
    
    /**
     * ��ȡ��������
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $settings = Setting::orderBy("group")->orderBy("key")->get();
        
        return response()->json([
            "status" => "success",
            "data" => $settings
        ]);
    }
    
    /**
     * ��ȡָ������
     *
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($key)
    {
        $setting = Setting::where("key", $key)->first();
        
        if (!$setting) {
            return response()->json([
                "status" => "error",
                "message" => "���ò�����"
            ], 404);
        }
        
        return response()->json([
            "status" => "success",
            "data" => $setting
        ]);
    }
}
    /**
     * ��������
     *
     * @param Request $request
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $key)
    {
        $setting = Setting::where("key", $key)->first();
        
        if (!$setting) {
            return response()->json([
                "status" => "error",
                "message" => "���ò�����"
            ], 404);
        }
        
        // ��֤����
        $validator = Validator::make($request->all(), [
            "value" => "nullable",
            "group" => "sometimes|required|string|max:255",
            "type" => "sometimes|required|in:string,integer,float,boolean,array,json",
            "description" => "sometimes|nullable|string|max:255",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ����ֵ
        $value = $request->input("value");
        
        switch ($setting->type) {
            case "integer":
                $value = (int) $value;
                break;
            case "float":
                $value = (float) $value;
                break;
            case "boolean":
                $value = (bool) $value;
                break;
            case "array":
            case "json":
                if (is_string($value)) {
                    $value = json_decode($value, true) ?: [];
                }
                break;
        }
        
        // ��������
        $this->settingService->set(
            $key,
            $value,
            $request->input("group", $setting->group),
            $request->input("type", $setting->type),
            $request->input("description", $setting->description),
            $setting->is_system
        );
        
        return response()->json([
            "status" => "success",
            "message" => "���ø��³ɹ�",
            "data" => Setting::where("key", $key)->first()
        ]);
    }
    
    /**
     * ����������
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // ��֤����
        $validator = Validator::make($request->all(), [
            "key" => "required|string|max:255|unique:settings,key",
            "value" => "nullable",
            "group" => "required|string|max:255",
            "type" => "required|in:string,integer,float,boolean,array,json",
            "description" => "nullable|string|max:255",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ����ֵ
        $value = $request->input("value");
        
        switch ($request->input("type")) {
            case "integer":
                $value = (int) $value;
                break;
            case "float":
                $value = (float) $value;
                break;
            case "boolean":
                $value = (bool) $value;
                break;
            case "array":
            case "json":
                if (is_string($value)) {
                    $value = json_decode($value, true) ?: [];
                }
                break;
        }
        
        // ��������
        $setting = $this->settingService->set(
            $request->input("key"),
            $value,
            $request->input("group"),
            $request->input("type"),
            $request->input("description"),
            false
        );
        
        return response()->json([
            "status" => "success",
            "message" => "���ô����ɹ�",
            "data" => $setting
        ], 201);
    }
    
    /**
     * ɾ������
     *
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($key)
    {
        $setting = Setting::where("key", $key)->first();
        
        if (!$setting) {
            return response()->json([
                "status" => "error",
                "message" => "���ò�����"
            ], 404);
        }
        
        // ϵͳ���ò�����ɾ��
        if ($setting->is_system) {
            return response()->json([
                "status" => "error",
                "message" => "ϵͳ���ò�����ɾ��"
            ], 403);
        }
        
        $this->settingService->delete($key);
        
        return response()->json([
            "status" => "success",
            "message" => "����ɾ���ɹ�"
        ]);
    }
    
    /**
     * ��ȡ��������
     *
     * @param string $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroup($group)
    {
        $settings = Setting::where("group", $group)->orderBy("key")->get();
        
        if ($settings->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "���÷��鲻����"
            ], 404);
        }
        
        return response()->json([
            "status" => "success",
            "data" => $settings
        ]);
    }
    
    /**
     * ���·�������
     *
     * @param Request $request
     * @param string $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGroup(Request $request, $group)
    {
        $settings = Setting::where("group", $group)->get();
        
        if ($settings->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "���÷��鲻����"
            ], 404);
        }
        
        $rules = [];
        
        // ������֤����
        foreach ($settings as $setting) {
            switch ($setting->type) {
                case "integer":
                    $rules[$setting->key] = "nullable|integer";
                    break;
                case "float":
                    $rules[$setting->key] = "nullable|numeric";
                    break;
                case "boolean":
                    $rules[$setting->key] = "nullable|boolean";
                    break;
                case "array":
                case "json":
                    $rules[$setting->key] = "nullable|array";
                    break;
                default:
                    $rules[$setting->key] = "nullable|string";
            }
        }
        
        // ��֤����
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ��������
        $data = $request->only(array_keys($rules));
        $this->settingService->saveGroup($group, $data);
        
        return response()->json([
            "status" => "success",
            "message" => "���ñ���ɹ�",
            "data" => Setting::where("group", $group)->get()
        ]);
    }
    
    /**
     * ������û���
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        $this->settingService->clearCache();
        
        return response()->json([
            "status" => "success",
            "message" => "���û��������"
        ]);
    }
    
    /**
     * ��ʼ��ϵͳ����
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function initSystemSettings()
    {
        $this->settingService->initSystemSettings();
        
        return response()->json([
            "status" => "success",
            "message" => "ϵͳ�����ѳ�ʼ��"
        ]);
    }
    
    /**
     * ��ȡ��������
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicSettings()
    {
        $publicGroups = ["general", "contact", "social"];
        $settings = Setting::whereIn("group", $publicGroups)->get();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->typed_value;
        }
        
        return response()->json([
            "status" => "success",
            "data" => $result
        ]);
    }
}

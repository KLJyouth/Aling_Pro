<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Membership\PointService;
use Illuminate\Http\Request;

class PointController extends Controller
{
    /**
     * ���ַ���
     *
     * @var PointService
     */
    protected $pointService;

    /**
     * ����������ʵ��
     *
     * @param PointService $pointService
     * @return void
     */
    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
        $this->middleware("auth");
    }

    /**
     * ��ʾ�û�����ҳ��
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $pointStats = $this->pointService->getPointStats($user);
        $pointHistory = $this->pointService->getPointHistory($user, 10);
        
        return view("user.points.index", [
            "pointStats" => $pointStats,
            "pointHistory" => $pointHistory,
        ]);
    }
    
    /**
     * ��ȡ���������ʷ
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $request->validate([
            "offset" => "required|integer|min:0",
            "limit" => "integer|min:1|max:50",
        ]);
        
        $user = $request->user();
        $limit = $request->input("limit", 10);
        $offset = $request->input("offset", 0);
        
        $history = $this->pointService->getPointHistory($user, $limit, $offset);
        
        return response()->json([
            "code" => 0,
            "message" => "��ȡ�ɹ�",
            "data" => $history,
        ]);
    }
    
    /**
     * ��ʾ�û����ֶһ�ҳ��
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function exchange(Request $request)
    {
        $user = $request->user();
        $currentPoints = $this->pointService->getCurrentPoints($user);
        
        // ��ȡ�ɶһ�����Ŀ�б����磬�Ż�ȯ����Ա�����ȣ�
        // ������Ҫ����ʵ��ҵ���߼�ʵ��
        $exchangeItems = [
            // ʾ������
            [
                "id" => 1,
                "name" => "��Ա1��",
                "points" => 100,
                "description" => "�һ�1���Աʱ��",
            ],
            [
                "id" => 2,
                "name" => "8���Ż�ȯ",
                "points" => 200,
                "description" => "�һ�һ��8���Ż�ȯ���������κ��ײ�",
            ],
        ];
        
        return view("user.points.exchange", [
            "currentPoints" => $currentPoints,
            "exchangeItems" => $exchangeItems,
        ]);
    }
    
    /**
     * ������ֶһ�����
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doExchange(Request $request)
    {
        $request->validate([
            "item_id" => "required|integer|min:1",
        ]);
        
        $user = $request->user();
        $itemId = $request->input("item_id");
        
        // ���Ҷһ���Ŀ
        // ������Ҫ����ʵ��ҵ���߼�ʵ��
        $item = null;
        $exchangeItems = [
            // ʾ�����ݣ������汣��һ��
            [
                "id" => 1,
                "name" => "��Ա1��",
                "points" => 100,
                "description" => "�һ�1���Աʱ��",
            ],
            [
                "id" => 2,
                "name" => "8���Ż�ȯ",
                "points" => 200,
                "description" => "�һ�һ��8���Ż�ȯ���������κ��ײ�",
            ],
        ];
        
        foreach ($exchangeItems as $exchangeItem) {
            if ($exchangeItem["id"] == $itemId) {
                $item = $exchangeItem;
                break;
            }
        }
        
        if (!$item) {
            return back()->with("error", "�һ���Ŀ������");
        }
        
        // �������Ƿ��㹻
        if (!$this->pointService->hasEnoughPoints($user, $item["points"])) {
            return back()->with("error", "���ֲ��㣬�޷��һ�");
        }
        
        // �ۼ�����
        $result = $this->pointService->deductPoints(
            $user,
            $item["points"],
            "exchange",
            "�һ�{$item["name"]}",
            $item["id"],
            "exchange_item"
        );
        
        if (!$result) {
            return back()->with("error", "�һ�ʧ�ܣ����Ժ�����");
        }
        
        // ����һ��߼�
        // ������Ҫ����ʵ��ҵ���߼�ʵ��
        
        return redirect()->route("user.points.index")->with("status", "�һ��ɹ�");
    }
}

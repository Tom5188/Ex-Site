<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Cache\RedisLock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\PayUserInfo;

class PayController extends Controller
{
    public function index()
    {
        return view("admin.user.pay_info_log");
    }
    
    public function payLog(Request $request){
        $limit = $request->get('limit', 10);
        $userId = $request->get('user_id', '');
        $status = $request->get('status', '');
        $start_time = $request->get('start_time', '');
        $end_time = $request->get('end_time', '');
        $list = PayUserInfo::where(function ($query) use ($userId) {
            if (!empty($userId)) {
                $query->where('user_id', $userId);
            }
        }) -> where(function ($query) use ($status) {
            if ($status != '') {
                $query->where('status', $status);
            }
        })  -> where(function ($query) use ($start_time) {
            if (!empty($start_time)) {
                $query->where('create_time', '>=', strtotime($start_time));
            }
        })  -> where(function ($query) use ($end_time) {
            if (!empty($end_time)) {
                $query->where('create_time', '<=', strtotime($end_time));
            }
        }) ->orderBy('id', 'asc') -> paginate($limit);
        return $this->layuiData($list);
    }
    
    public function closeNofity(Request $request){
        $id = $request->get('id');
        $payUserInfo = PayUserInfo::where("id",$id) ->first();
        if (empty($payUserInfo)){
            return $this->error('数据信息错误');
        }
        
        DB::beginTransaction();
		try {
			$payUserInfo -> notify_status = 1;
            $payUserInfo -> save();
			DB::commit();
			return $this->success("操作成功"); 
		} catch (\Exception $_var_6) {
			DB::rollBack();
			return $this->error("关闭通知失败".$_var_6);
		}
    }
}

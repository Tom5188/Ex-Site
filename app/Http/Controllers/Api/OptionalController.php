<?php

namespace App\Http\Controllers\Api;



use App\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OptionalController extends Controller
{
    public function add(Request $request)
    {
        $user_id = Users::getUserId();
        $currency_id = $request->get('currency_id');

        $cu = DB::table('optional')->where('user_id',$user_id)
            ->where('currency_id',$currency_id)
            ->first();
        if ($cu){
            return $this->error('您已经添加过该自选了~');
        }
        $id =  DB::table('optional')->insertGetId([
            'user_id' => $user_id,
            'currency_id' => $currency_id,
            'create_time' => time(),
        ]);
        if (empty($id)){
            return $this->error('添加自选失败');
        }
        return $this->success(['id'=>$id]);
    }
    public function del(Request $request)
    {
        $user_id = Users::getUserId();
        $id = $request->get('id');

        $cu = DB::table('optional')->where('user_id',$user_id)
            ->where('id',$id)
            ->first();
        if (empty($cu)){
            return $this->error('您未添加该自选~');
        }
        $res =  DB::table('optional')->where('id',$id)->delete();
        if (empty($res)){
            return $this->error('删除自选失败');
        }
        return $this->success('删除成功');
    }

    public function list()
    {
        $user_id = Users::getUserId();

        $list =  DB::table('optional')->where('user_id',$user_id)
            ->select('id','currency_id')
            ->get();
        return $this->success($list);
    }
    
}
?>
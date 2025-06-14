<?php


namespace App\Http\Controllers\Admin;


use App\CoinTrade;
use Illuminate\Http\Request;
use App\Logic\CoinTradeLogic;


class CoinTradeController extends Controller
{
    public function tradeList(Request $request){
        $where = [];
        $account = $request->get('search','');
        $limit = $request->get('limit', 20);
        $status = $request->get('status');
        $type  = $request->get('type');
        if($status){
            $where['coin_trade.status'] = $status;
        }
        if($type){
            $where['coin_trade.type'] = $type;
        }
        $res = CoinTrade::join('users as u','u_id','=','u.id')
            ->join('currency as c','currency_id' ,'=','c.id')
            ->join('currency as l','legal_id','=','l.id')
            ->where($where)
            ->where(function($q)use($account){
                if($account){
                    $q->where('u.account_number','=',$account)->orWhere('u.email','=',$account);
                }})
            ->select(['u.id','u.account_number','coin_trade.*','l.name as legal_name','c.name as currency_name'])
            ->orderBy('coin_trade.id','desc')
            ->paginate($limit);
        return $this->layuiData($res);
    }
    public function finishTrade(Request $request){
        $id = $request->get('id');
        $coinTrade = CoinTrade::find($id);
        if($coinTrade->status != 1){
            return $this->error('不在交易中状态');
        }
        try{
            CoinTradeLogic::forceMatchTrade($coinTrade->id);
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
        return $this->success('操作成功');
    }
    public function index(){
        return view('admin.cointrade.index');
    }
}

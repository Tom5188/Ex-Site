<?php


namespace App\Http\Controllers\Api;


use App\AccountLog;
use App\CurrencyDepositOrder;
use App\Users;
use App\UsersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class CurrencyDepositController extends Controller
{

    public function dispatch(Request $request){
        $res = CurrencyDepositOrder::where([
            'status' => 1,
        ])->where('start_at','<',date("Y-m-d"))
            ->where('last_settle_time','<',date("Y-m-d"))
            ->orWhere('last_settle_time',null)
            ->take(20) ->get();
        foreach($res as $order){
            CurrencyDepositOrder::dispatchInterest($order->id);
            if($order->end_at < date('Y-m-d')){
                //todo 时间到了 释放存款
                CurrencyDepositOrder::where('id',$order->id)->update(['status' => 2]);
            }
        }
    }

    public function configList(Request $request){
        $currencyId = $request->input('currency_id');
        if(!$currencyId){
            return $this->error('require param currency_id');
        }
        $list = DB::table('currency_deposit')->where('currency_id',$currencyId)->orderBy('day','asc')->get();
        return $this->success($list);
    }

    public function orderList(Request $request){
        $currencyId = $request->input('currency_id');
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
        $where = [];
        if($currencyId){
            $where['currency_id'] = $currencyId;
        }
        $uid = Users::getUserId();
        $list = CurrencyDepositOrder::join('currency','currency.id','=','currency_id')->where('u_id',$uid)->where($where)->orderBy('end_at','asc')
            ->skip($limit*($page-1))->take($limit)->select(['currency_deposit_order.*','currency.name'])->get();
        return $this->success([
            'list' => $list
        ]);
    }



    public function deposit(Request $request){
        $uid = Users::getUserId();
        $currencyId = $request->input('currency_id');
        if(!$currencyId){
            return $this->error('require param currency_id');
        }
        $configId = $request->input('config_id');
        $config = DB::table('currency_deposit')->find($configId);
        if(!$config || $config->currency_id != $currencyId){
            return $this->error('config error');
        }
        $amount = Input::get('amount','');
        if($amount < 0 || ($amount < $config->save_min)){
            return $this->error('amount error');
        }
        $legal = UsersWallet::where("user_id", $uid)
            ->where("currency", $currencyId) //usdt
            ->lockForUpdate()
            ->first();
        if (!$legal) {
            return $this->error("钱包未找到,请先添加钱包");
        }
        if($legal->change_balance < $amount){
            return $this->error('资金钱包余额不足');
        }
        DB::beginTransaction();
        try{
            //先扣费
            $result = change_wallet_balance(
                $legal,
                2,
                -$amount,
                AccountLog::LH_LOAN,
                '质押挖矿',
                false,
                0,
                0,
                serialize([])
            );
            $model = new CurrencyDepositOrder();
            $model->u_id = $uid;
            $model->currency_id = $currencyId;
            $model->amount = $amount;
            $model->total_rate = $config->total_interest_rate;
            $model->day_rate = bc_div($config->total_interest_rate,$config->day,4);
            $model->start_at = date("Y-m-d",strtotime("+1 day"));
            $day = $config->day+1;
            $model->end_at = date("Y-m-d",strtotime("+$day day"));
            $model->save();
            Db::commit();
        }catch (\Exception $e){
            Db::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('success');

    }
}

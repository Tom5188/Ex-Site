<?php

namespace App\Http\Controllers\Api;

use App\Setting;
use App\AccountLog;
use App\CurrencyDepositOrder;
use App\CurrencyProjectOrder;
use App\CurrencyProject;
use App\Users;
use App\UsersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrencyProjectController extends Controller
{

    public function processOrder(){
        $list = CurrencyProjectOrder::where('status',2)
            ->where('type',1)
            ->where('end_at','<',date("Y-m-d H:i:s"))
            ->take(10)->get();
        //认购单派钱
        foreach($list as $v){
            DB::beginTransaction();
            try{
                $op_wallet = UsersWallet::where('user_id',$v->u_id)
                    ->where('currency',$v->currency_id)
                    ->first();
                if(!$op_wallet){
                    throw new \Execption('wallet not found');
                }
                $result = change_wallet_balance($op_wallet,
                    2,
                    $v->coin_amount,
                    AccountLog::IEO_OPERATION,
                    'ieo order');
                CurrencyProjectOrder::where('id',$v->id)->update([
                    'status' => 3
                ]);
                DB::commit();
            }catch(\Execption $e){
                DB::rollBack();
                continue ;
            }
        }

        $list2 = CurrencyProjectOrder::join('users_wallet','users_wallet.id','=','currency_project_order.pay_wallet_id')
            ->where('currency_project_order.status',1)->where('type',2)->where('users_wallet.change_balance','>','currency_project_order.total_price')
            ->where('end_at','<',date("Y-m-d H:i:s"))
            ->take(10)->select(['currency_project_order.*'])->get();
        // var_dump($list2);exit;
        foreach($list2 as $item){
            DB::beginTransaction();
            try{
                $wallet = UsersWallet::where("user_id", $item->u_id)
                    ->where("currency", $item->pay_currency_id)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet){
                    throw new \Execption('wallet not found');
                }
                $op_wallet = UsersWallet::where('user_id',$item->u_id)
                    ->where('currency',$item->currency_id)
                    ->first();
                if(!$op_wallet){
                    throw new \Execption('wallet not found');
                }
                //扣款
                $result = change_wallet_balance($wallet,
                    2,
                    -$item->total_price,
                    AccountLog::IEO_OPERATION,
                    'ieo order');
                if ($result !== true) {
                    throw new \Exception($result);
                }
                //发币
                $result = change_wallet_balance($op_wallet,
                    2,
                    $item->coin_amount,
                    AccountLog::IEO_OPERATION,
                    'ieo order');

                CurrencyProjectOrder::where('id',$item->id)->update([
                    'status' => 3
                ]);
                DB::commit();
            }catch(\Execption $e){
                DB::rollBack();
                continue ;
            }
        }
    }


    public function projectList(Request $request){
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
//        $model = new CurrencyProject();

        $list = CurrencyProject::where('status',1)
            ->skip($limit*($page-1))->take($limit)
            ->select(['title','summary','amount','total_sell','start_at','end_at','logo','id','currency_id','pay_currency_id'])
            ->orderBy('start_at','desc')
            ->get();
//        bc_sub($project->amount,$project->total_sell)
        $imageServerUrl = Setting::getValueByKey('image_server_url', '');
        foreach ($list as &$item) {
            $total_sell = $item->total_sell;
            if (empty($total_sell)){
                $total_sell = 0;
            }
            if ($total_sell<=0){
                $percentage = 100;
            }else{
                $percentage = (bc_sub($item->amount,$total_sell) / $item->amount) * 100;
                if (number_format($percentage,2) == 100){ // 卖出去就是99.99
                    $percentage = '99.99';
                }
            }
            $item->percentage = number_format($percentage,2);

            $day = strtotime($item->end_at) - strtotime($item->start_at);
            $day = intval($day / 24 / 3600);
            $item->day = $day > 0 ? $day : 1;
            $item->logo = $imageServerUrl . $item->logo;
        }
        unset($item);

//        foreach($list as &$v){
//            $v['time_status']
//        }
        return $this->success(['list' => $list]);
    }



    public function projectDetail(Request $request){
        $userId = Users::getUserId();
        $id = $request->get('project_id');
        $project = CurrencyProject::find($id);
        if(!$project){
            return $this->error('project not found');
        }
        if($project->status != 1){
            return $this->error('project status error');
        }
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $project->pay_currency_id)
            ->first();
        $hasMoney = $wallet->change_balance > 0 ? 1 : 0;
        if($project->min){
            $hasMoney = $wallet->change_balance > $project->min ? 1 : 0;
        }
        $project->user_has_money = 1;//$hasMoney;
        $order = CurrencyProjectOrder::where('project_id',$id)->where('type','<',3)->where('u_id',$userId)->first();
        if($order)
            $order->order_no = 1000+$order->id;

        $sell = CurrencyProjectOrder::where('project_id',$id)->where('type','=',3)->where('u_id',$userId)->first();
        return $this->success([
            'info' => $project,
            'order_info' => $order,
            'sell_order' => $sell
        ]);
    }
    public function joinLottery(Request $request){
        return $this->error('error');
        $id = $request->get('project_id');
        $amount = $request->get('amount');
        $userId = Users::getUserId();
        $project = CurrencyProject::find($id);
        if(!$project){
            return $this->error('找不到项目');
        }
        if($project->status != 1){
            return $this->error('项目状态异常');
        }
        if($project->time_status != 2){
            return $this->error('项目已结束');
        }
        if(bc_sub($project->amount,bc_add($amount,$project->total_sell,8))< 0){
            return $this->error('已售罄');
        }
        // $check = CurrencyProjectOrder::where('u_id',$userId)->where('project_id',$project->id)->first();
        // if($check){
        //     return $this->error('already apply');
        // }
        //new wallet
        $payWallet = UsersWallet::where('currency',$project->pay_currency_id)->where('user_id',$userId)->first();
        if(!$payWallet){
            $payWalletId =  UsersWallet::insertGetId([
                'currency' => $project->pay_currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $payWalletId = $payWallet->id;
        }
        $wallet = UsersWallet::where('currency',$project->currency_id)->where('user_id',$userId)->first();
        if(!$wallet){
            $walletId =  UsersWallet::insertGetId([
                'currency' => $project->currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $walletId = $wallet->id;
        }
        $price = bc_mul($project->price,$amount,8);
        $model = new CurrencyProjectOrder();
        $model->u_id = $userId;
        $model->project_id = $project->id;
        $model->currency_id = $project->currency_id;
        $model->pay_currency_id = $project->pay_currency_id;
        $model->coin_amount = $amount;
        $model->price = $project->price;
        $model->total_price = $price;
        $model->created_at = date('Y-m-d H:i:s');
        $model->status = 1;
        $model->type = 2;//抽奖
        $model->end_at = $project->end_at;
        $model->wallet_id = $walletId;
        $model->pay_wallet_id = $payWalletId;
        $model->save();
        return $this->success('success');
    }

    public function buyOrder(Request $request){
        $id = $request->get('project_id');
        $amount = $request->get('total_price');
        $userId = Users::getUserId();
        $project = CurrencyProject::find($id);
        if(!$project){
            return $this->error('找不到项目');
        }
        if(!$amount){
            return $this->error('参数错误');
        }
        if($project->status != 1){
            return $this->error('project status error');
        }
        if($project->time_status != 3){
            return $this->error('project not end');
        }
        if(!$project->sell_begin || strtotime($project->sell_begin) > time()){
            return $this->error('配售未开始');
        }
        $check = CurrencyProjectOrder::where('u_id',$userId)->where('project_id',$project->id)->first();
        if($check){
            return $this->error('IEO项目只可参与一次');
        }
        //new wallet
        $payWallet = UsersWallet::where('currency',$project->pay_currency_id)->where('user_id',$userId)->first();
        if(!$payWallet){
            $payWalletId =  UsersWallet::insertGetId([
                'currency' => $project->pay_currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $payWalletId = $payWallet->id;
        }
        $wallet = UsersWallet::where('currency',$project->currency_id)->where('user_id',$userId)->first();
        if(!$wallet){
            $walletId =  UsersWallet::insertGetId([
                'currency' => $project->currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $walletId = $wallet->id;
        }
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $project->pay_currency_id)
            ->lockForUpdate()
            ->first();
        if(!$wallet){
            return $this->error('wallet not found');
        }
        if($amount>$wallet->change_balance){
            // var_dump([$project->pay_currency_id,$userId]);
            return $this->error('钱包余额不足.');
        }
        DB::beginTransaction();
        try{
            $result = change_wallet_balance($wallet,
                2,
                -$amount,
                AccountLog::IEO_OPERATION,
                'ieo order');
            if ($result !== true) {
                throw new \Exception($result);
            }

            // $result = change_wallet_balance($op_wallet,
            //     2,
            //     $amount,
            //     AccountLog::IEO_OPERATION,
            //     'ieo order');

            $model = new CurrencyProjectOrder();
            $model->project_id = $project->id;
            $model->u_id = $userId;
            $model->currency_id = $project->currency_id;
            $model->pay_currency_id = $project->pay_currency_id;
            $model->coin_amount = null;
            $model->price = null;
            $model->total_price = $amount;
            $model->created_at = date('Y-m-d H:i:s');
            $model->status = 2;
            $model->type = 3;
            $model->end_at = $project->end_at;
            $model->wallet_id = $walletId;
            $model->pay_wallet_id = $payWalletId;
            $model->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('success');

    }

    public function postOrder(Request $request){
        $id = $request->get('project_id');
        $amount = $request->get('amount');
        $userId = Users::getUserId();
        $project = CurrencyProject::find($id);
        if(!$project){
            return $this->error('找不到项目');
        }
        if($project->status != 1){
            return $this->error('状态异常');
        }
        
        if($amount<=0){
            return $this->error('参数错误');
        }
        if($project->time_status != 2){
            return $this->error('项目已结束');
        }
        if(bc_sub($project->amount,bc_add($amount,$project->total_sell,8))< 0){
            return $this->error('已售罄');
        }
        //new wallet
        $payWallet = UsersWallet::where('currency',$project->pay_currency_id)->where('user_id',$userId)->first();
        if(!$payWallet){
            $payWalletId =  UsersWallet::insertGetId([
                'currency' => $project->pay_currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $payWalletId = $payWallet->id;
        }
        $wallet = UsersWallet::where('currency',$project->currency_id)->where('user_id',$userId)->first();
        if(!$wallet){
            $walletId =  UsersWallet::insertGetId([
                'currency' => $project->currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $walletId = $wallet->id;
        }

        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $project->pay_currency_id)
            ->lockForUpdate()
            ->first();
        if(!$wallet){
            return $this->error('找不到钱包');
        }
        // $check = CurrencyProjectOrder::where('u_id',$userId)->where('project_id',$project->id)->first();
        // if($check){
        //     return $this->error('already apply');
        // }
        $op_wallet = UsersWallet::where('user_id',$userId)
            ->where('currency',$project->currency_id)
            ->first();
        if(!$op_wallet){
            return $this->error('找不到钱包');
        }
        $price = bc_mul($project->price,$amount,8);
        if($price>$wallet->change_balance){
            // var_dump([$project->pay_currency_id,$userId]);
            return $this->error('钱包余额不足');
        }

        DB::beginTransaction();
        try{
            $result = change_wallet_balance($wallet,
                2,
                -$price,
                AccountLog::IEO_OPERATION,
                'ieo 项目');
            if ($result !== true) {
                throw new \Exception($result);
            }

            // $result = change_wallet_balance($op_wallet,
            //     2,
            //     $amount,
            //     AccountLog::IEO_OPERATION,
            //     'ieo order');

            $model = new CurrencyProjectOrder();
            $model->project_id = $project->id;
            $model->u_id = $userId;
            $model->currency_id = $project->currency_id;
            $model->pay_currency_id = $project->pay_currency_id;
            $model->coin_amount = $amount;
            $model->price = $project->price;
            $model->total_price = $price;
            $model->created_at = date('Y-m-d H:i:s');
            $model->status = 2;
            $model->type = 1;
            $model->end_at = $project->end_at;
            $model->wallet_id = $walletId;
            $model->pay_wallet_id = $payWalletId;
            $model->save();
            // 更新一出手
            $project->total_sell += $amount;
            $project->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('成功');


    }

    public function userOrder(Request $request)
    {
        $user_id = Users::getUserId();
        $page =  $request->get('page',1);
        $limit = $request->get('limit',10);
        $lists = DB::table('currency_project_order')
            ->join('currency_project', 'currency_project.id', '=', 'currency_project_order.project_id')
            ->join('currency', 'currency.id', '=', 'currency_project.currency_id')
            ->where('currency_project_order.u_id',$user_id)
            ->orderBy('currency_project_order.id', 'desc')
            ->select('currency_project_order.created_at',
                'currency.name',
                'currency_project_order.coin_amount',
                'currency_project.sell_begin',
                'currency_project_order.id',
                'currency_project_order.status'
            )
            ->paginate($limit);

        foreach ($lists->items() as &$item) {
            $item->give_amount = 0;
            if ($item->status == 3){
                $item->give_amount = $item->coin_amount;
            }
        }
        unset($item);

        return $this->success($lists);
    }

}

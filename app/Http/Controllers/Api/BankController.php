<?php


namespace App\Http\Controllers\Api;


use App\AccountLog;
use App\LegalDealSend;
use App\LhBankAccount;
use App\LhBankAccountLog;
use App\LhBankTeamMember;
use App\LhDepositOrder;
use App\LhDepositOrderLog;
use App\LhLoanOrder;
use App\Logic\LhBankProfitLogic;
use App\Setting;
use App\Users;
use App\UsersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Cache\RedisLock;
use Illuminate\Support\Facades\Redis;


class BankController extends Controller
{
    
    public function test(){

        $accountId = Input::get('id',171);
        LhBankProfitLogic::updateVipLevel($accountId);
//
//
//        LhBankProfitLogic::addInProcessingQueue(1);
//        LhBankProfitLogic::addInProcessingQueue(2);
//        LhBankProfitLogic::addInProcessingQueue(3);
//
//        $res = LhBankProfitLogic::getProcessingQueue();
////        var_dump($res);exit;
//        DB::beginTransaction();
//        try{
//            LhBankProfitLogic::saveDeposit($accountId,1000);
//            //存钱后更新M等级
//            LhBankProfitLogic::teamIncrement($accountId,1000);
//
//            DB::commit();
//        }catch (\Exception $e){
//            DB::rollBack();
//            throw $e;
//        }

    }
    
   
    public function dispatchInterest(){
        //查询开始时间在今天以前，状态是1的 结息时间小于今天的订单
        
      $res = LhDepositOrder::where([
            'status' => 1,
        ])->where('start_at','<',date("Y-m-d"))
          ->where('last_settle_time','<',date("Y-m-d"))
            ->orWhere('last_settle_time',null)
          ->take(500) ->get();
        foreach($res as $order){
            LhDepositOrder::dispatchInterest($order->id);
            // if($order->end_at < date('Y-m-d')){
            //     //todo 时间到了 释放存款
            //     LhDepositOrder::where('id',$order->id)->update(['status' => 2]);
            // }
        }

        return $this->success('success');
    }

    public function weekProfit(){
        $user_id = Users::getUserId();
        //判断是否有账号
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error('还未创建账户');
        }
        $beginDay = date('Y-m-d',strtotime('-8 day'));
        $res = LhDepositOrderLog::where('bank_account_id',$res->id)
            ->where('interest_day','>',$beginDay)
            ->groupBy('interest_day')
            ->selectRaw('sum(interest_amount) as amount, interest_day')
            ->get();
        return $this->success([
            'list'=>$res
        ]);
    }

    public function bankLog(Request $request){
        $user_id = Users::getUserId();
        //判断是否有账号
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error('还未创建账户');
        }
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
        $type = $request->get('type',null);
        $search = $request->get('search','');
        $where = [];
        if($type){
            $where['type'] = $type;
        }
        if($search){
            $where['description'] = $search;
        }
        $res = LhBankAccountLog::where('account_id',$res->id)
            ->where($where)
            ->orderBy('id','desc')
            ->skip($limit*($page-1))->take($limit)
            ->get();
        return $this->success([
            'list' => $res
        ]);
    }

    // public function newAccount(){
    //     $user_id = Users::getUserId();
    //     $user = Users::find($user_id);
    //     if (!$user) {
    //         return $this->error('用户不存在');
    //     }
    //     if($user->is_realname != 2){
    //         return $this->error('请实名制');
    //     };

    //     //判断是否有账号
    //     $res = LhBankAccount::where('uid',$user_id)->first();
    //     if($res){
    //         return $this->error('已创建过账户');
    //     }
    //     //获取爸爸加过的团队  全部再加一次
    //     if($user->parent_id){
    //         $parentTeamList = LhBankTeamMember::getUserTeams($user->parent_id);
    //     }
    //     DB::beginTransaction();
    //     try{
    //         $model = new LhBankAccount();
    //         $model->uid = $user_id;
    //         $model->usdt_balance = LhBankAccount::INIT_BALANCE;
    //         $model->p_uid = $user->parent_id;
    //         $model->save();
    //         if(isset($parentTeamList) && $parentTeamList){
    //             foreach($parentTeamList as $team){
    //                 LhBankTeamMember::addTeamMember($team->leader_uid,$user_id,$team['generation']+1);
    //             }
    //         }
    //         if($user->parent_id){
    //             LhBankTeamMember::addTeamMember($user->parent_id,$user_id,1);
    //         }

    //         DB::commit();
    //     }catch (\Exception $e){
    //         DB::rollBack();
    //         return $this->error($e->getMessage());
    //     }

    //     return $this->success('创建成功');
    // }

    public function myAccount(){
        $user_id = Users::getUserId();

        //判断是否有账号
        /** @var LhBankAccount $res */
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error(null);
        }
        else{
            $res->total_profit_df =0;
            $res->total_profit_usdt = 0;
            $res->last_day_profit_df = 0;
            $res->last_day_profit_usdt = 0;
            $res->total_value = 0;
            $res->total_invited = 0;
            $res->total_lock_usdt = LhDepositOrder::where('bank_account_id',$res->id)->where('status',1)->sum('usdt_amount');
            $res->total_lock_df = LhDepositOrder::where('bank_account_id',$res->id)->where('status',1)->sum('amount');
            return $this->success([
                'account_info' => $res,
                'usdt' =>  Setting::getValueByKey('AUTU_USDT_NUM',300),
                'df' =>  Setting::getValueByKey('AUTU_UNLOCK_NUM',1000)
            ]);
        }
    }

    public function saveMoney(){
        $user_id = Users::getUserId();
        $amount = Input::get('amount','');
        //判断是否有账号
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error('您还未创建账户');
        }
        $amount = (float)$amount;

        $legal = UsersWallet::where("user_id", $user_id)
            ->where("currency", 3) //usdt
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
                AccountLog::TRANSFER_TO_LH_ACCOUNT,
                '转账入余币宝',
                false,
                0,
                0,
                serialize([])
            );
            $res->usdt_balance += $amount;
            $res->save();
            LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_USDT,$amount,'转账入金');
            Db::commit();
        }catch (\Exception $e){
            Db::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('入金成功');
    }

    public function config(Request $request){
        $imageServerUrl = Setting::getValueByKey('image_server_url', '');

        $list = DB::table('lh_deposit_config')
            ->join('currency','currency.id','=','currency_id')
            ->select([
                'currency.name as currency_name',
                'currency.logo as currency_logo',
                'lh_deposit_config.*'
            ])
            ->get()
            ->map(function($item) use ($imageServerUrl) {
                $item->currency_logo = $imageServerUrl . $item->currency_logo;
                return $item;
            });


        return $this->success($list);
    }
    
    public function updateRate(Request $request){
        $res = LhDepositOrder::get();
        foreach($res as $key=>$order){
            $config = DB::table('lh_deposit_config')->where('save_min', '<=', $order->amount)->where('save_max', '>=', $order->amount)->first();
            $day_rate = $config->interest_rate / 100;
            if($day_rate != floatval($order->day_rate)){
                DB::table('lh_deposit_order')->where('id', $order->id)->update(['day_rate'=>$day_rate]);
            }
        }
        return $this->success('设置成功');
    }
    
    public function newDeposit(Request $request){
        $amount = $request->get('amount');
        if($amount<=0){
            return $this->error('金额错误');
        }
        $config = DB::table('lh_deposit_config')->where('save_min', '<=', $amount)->where('save_max', '>=', $amount)->first();
        if(!$config){
            return $this->error('您输入的金额范围不存在');
        }
        $user_id = Users::getUserId();
        
        // $order = LhDepositOrder::where('user_id', $user_id)->where('status', 1)->first();
        // if($order){
        //     return $this->error('您有未到期的质押订单');
        // }
        // if($amount < $config->save_min){
        //     return $this->error('最少存入数量为：'.$config->save_min);
        // }
        $legal = UsersWallet::where("user_id", $user_id)
            ->where("currency", $config->currency_id) //usdt
            ->lockForUpdate()
            ->first();
        DB::beginTransaction();
        try{
            // $result = change_wallet_balance(
            //     $legal,
            //     2,
            //     $amount,
            //     AccountLog::TRANSFER_TO_LH_ACCOUNT,
            //     '质押冻结|'.date('Y-m-d'),
            //     true,
            //     0,
            //     0,
            //     serialize([])
            // );
            $result = change_wallet_balance(
                $legal,
                2,
                -$amount,
                AccountLog::TRANSFER_TO_LH_ACCOUNT,
                '质押扣除|'.date('Y-m-d'),
                false,
                0,
                0,
                serialize([])
            );
            LhDepositOrder::newOrder($user_id,$config->currency_id,$amount,$config->day,$config->interest_rate);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());

        }
        return $this->success('操作成功');
    }
    
    /**
     * @return \Illuminate\Http\JsonResponse
     * @author 
     */
    public function timeDeposit(){
        $user_id = Users::getUserId();
        $num = Input::get('num',0);
        $num = (int)$num;
        if($num <= 0){
            return $this->error('数量不可低于0');
        }
        //判断是否有账号
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error('您还未创建账户');
        }

        $amount =  Setting::getValueByKey('AUTU_USDT_NUM',300);
        $amount *= $num;
        $df_amount =  Setting::getValueByKey('AUTU_UNLOCK_NUM',1000);
        $df_amount *= $num;
        if($res->usdt_balance < $amount){
            return $this->error('你的账户余额不足');
        }
        if($res->df_balance <= 0){
             return $this->error('你的锁定余额为0');
        }
        if($res->df_balance < $df_amount){
            $df_amount = $res->df_balance;
            // return $this->error('你的锁定余额不足'.$df_amount);
        }
        
        $totalDeposit = LhBankAccount::getTotalDeposit($res->id);
        DB::beginTransaction();
        try{
            $res->usdt_balance -= $amount;
            $res->df_balance -= $df_amount;
            $res->save();
            LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_USDT,-$amount,'解冻申请');
            LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_DF,-$df_amount,'解冻申请');
            $order = LhDepositOrder::newOrder($res->id,$df_amount,$amount);
            
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());

        }
        return $this->success('存币成功');
    }

    public function myDepositOrder(Request $request){
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
        $isCancel = $request->get('is_cancel',0);
        $status = $request->get('status',null);
        $where = [];
        if($isCancel){
            $where = [
                'is_cancel' => 1
            ];
        }
        if($status){
            $where['status'] = $status;
        }
        $res = LhDepositOrder::where('user_id',$user_id)
            ->join('currency','currency.id','=','currency_id')
            ->where($where)
            ->orderBy('lh_deposit_order.id','desc')
            ->skip($limit*($page-1))->take($limit)
            ->get(['currency.name as currency_name','lh_deposit_order.id','amount','day_rate','total_interest','start_at','end_at','status','lock_amount','withdraw_amount']);
            
        $total_interest = LhDepositOrder::where('user_id',$user_id)->sum('total_interest');
        return $this->success([
            'order_list' => $res,
            'total_interest' => $total_interest
        ]);
    }
    
    public function cancelOrderNew(){
        $orderId = Input::get('id');
        $order = LhDepositOrder::find($orderId);
        if(!$order){
            return $this->error('找不到存单');
        }
        $user_id = Users::getUserId();
        if($order->user_id != $user_id){
            return $this->error('非法操作');
        }
        if($order->status!=1){
            return $this->error('存单状态异常');
        }
        $legal = UsersWallet::where("user_id", $user_id)
            ->where("currency", $order->currency_id) //usdt
            ->lockForUpdate()
            ->first();
        DB::beginTransaction();
        try{
            //扣钱
            $returnAmount = $order->amount - $order->cancel_fee;
            
            $order->status = 2;
            $order->is_cancel = 1;
            $order->save();
            change_wallet_balance(
                $legal,
                2,
                -$order->lock_amount,
                AccountLog::BANK_WITHDRAW,
                '质押赎回解冻|'.date('Y-m-d'),
                true,
                0,
                0,
                serialize([])
            );
            change_wallet_balance(
                $legal,
                2,
                $returnAmount,
                AccountLog::BANK_WITHDRAW,
                '质押赎回返还|'.date('Y-m-d'),
                false,
                0,
                0,
                serialize([])
            );
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('毁约成功');
    }

    public function setwithdraw(){
        $orderId = Input::get('id');
        $amount = Input::get('amount');
        $user_id = Users::getUserId();
        
        $order = LhDepositOrder::find($orderId);
        if(!$order){
            return $this->error('找不到存单');
        }
        if($amount > ($order->amount - $order->lock_amount) || $amount < 0){
            return $this->error('提现金额异常');
        }
        if($order->user_id != $user_id){
            return $this->error('非法操作');
        }
        if($order->status!=1){
            return $this->error('存单状态异常');
        }
        $legal = UsersWallet::where("user_id", $user_id)
            ->where("currency", $order->currency_id) //usdt
            ->lockForUpdate()
            ->first();
        DB::beginTransaction();
        try{
            // 提现返回账户
            change_wallet_balance(
                $legal,
                2,
                $amount,
                AccountLog::BANK_WITHDRAW,
                '质押利息提现|'.date('Y-m-d'),
                false,
                0,
                0,
                serialize([])
            );
            $order->withdraw_amount = $order->withdraw_amount + $amount;
            $order->amount = $order->amount - $amount;
            $order->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('设置成功');
    }

    public function cancelOrder(){
        return $this->error('功能暂不可用');
        $user_id = Users::getUserId();
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error('您还未创建账户');
        }
        $orderId = Input::get('id');
        $order = LhDepositOrder::find($orderId);
        if(!$order){
            return $this->error('找不到存单');
        }
        if($order->bank_account_id != $res->id){
            return $this->error('非法操作');
        }
        if($order->status != 1){
            return $this->error('存单状态异常');
        }
        $loan = LhLoanOrder::where(['bank_account_id'=>$res->id,'status' => 1])->first();
        if($loan) {
            return $this->error('你还有质押单，不可毁约');
        }
        //判断毁约期限
        if(strtotime('+30 day',strtotime($order->start_at)) < date("Y-m-d")){
            return $this->error('毁约时间已过');
        }
        DB::beginTransaction();
        try{
            //扣钱
            $costRate = Setting::getValueByKey('CANCEL_DEPOSIT_ORDER_COST',20);
            $returnAmount = $order->amount * (1-$costRate/100);
            $balance = bc_add($res->usdt_balance,$returnAmount);
            $res->usdt_balance = $balance;
            $res->save();
            LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_USDT,$returnAmount,'毁约退款');
            $order->status = 2;
            $order->is_cancel = 1;
            $order->save();
            //更新自己账号等级
            LhBankProfitLogic::cancelOrderUpdate($res->id);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('毁约成功');
    }


    /**
     * 提现至钱包
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     * @author 
     */
    public function withdraw(){
        $user_id = Users::getUserId();
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error('您还未创建账户');
        }
        $depositAmount = LhBankAccount::getTotalDeposit($res->id);
        $amount = Input::get('amount',0);
        if($amount<10){
            return $this->error('最少提币金额为10');
        }
        $type = Input::get('type',''); //1 usdt 2 dfone
        $withdrawFee = Setting::getValueByKey('LH_WITHDRAW_FEE',0);
        if($withdrawFee>100){
            return $this->error('提现失败，请联系管理员');
        }
        switch ($type){
            case 1:
                 $legal = UsersWallet::where("user_id", $user_id)
                ->where("currency", 3) //usdt
                ->lockForUpdate()
                ->first();

                DB::beginTransaction();
                try{

                    //扣手续费
                    change_wallet_balance(
                        $legal,
                        2,
                        $amount,
                        AccountLog::BANK_WITHDRAW,
                        '理财账户提现',
                        false,
                        0,
                        0,
                        serialize([])
                    );
                    LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_USDT,-$amount,'理财账户提现');
                    DB::commit();
                }catch (\Exceptionq $e){
                    DB::rollBack();
                    return $this->error($e->getMessage());
                }
                break;
            case 2:
                return $this->error('维护中');
                $DfWallet = UsersWallet::getDF1Wallet($user_id);
                if(!$DfWallet){
                    return $this->error('找不到钱包');
                }
                if($res->df_balance - $amount < 0){
                    return $this->error('余额不足');
                }

                DB::beginTransaction();
                try{

                    $balance = bc_sub($res->df_balance,$amount);
                    $res->df_balance = $balance;
                    $res->save();
                    $amount = bc_mul($amount,(1-($withdrawFee/100)));
                    change_wallet_balance(
                        $DfWallet,
                        1,
                        $amount,
                        AccountLog::BANK_WITHDRAW,
                        '余币宝提现',
                        false,
                        0,
                        0,
                        serialize([])
                    );
                    LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_DF,-$amount,'提现');
                    DB::commit();
                }catch (\Exceptionq $e){
                    DB::rollBack();
                    return $this->error($e->getMessage());
                }
                break;
            default:
                return $this->error('错误类型');
        }
        return $this->success('提现成功');
    }


    public function loan(){
        $user_id = Users::getUserId();
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error('您还未创建账户');
        }
        $amount = Input::get('amount',0);
        if($amount<100){
            return $this->error('最少质押金额为100');
        }
        $type = Input::get('wallet_type',0);
        //验证当前质押单总额
        $totalLoan = LhLoanOrder::where([
            'bank_account_id' => $res->id,
            'status' => 1
        ])->sum('amount');
        //查询当前存单金额
        $totalDeposit = LhBankAccount::getTotalDeposit($res->id);
        $maxLoan = bc_mul($totalDeposit,0.8,0);
        if($totalLoan+$amount > $maxLoan){
            return $this->error('总质押金额超出，你还可以质押：'.($maxLoan-$totalLoan));
        }
        $legal = UsersWallet::where("user_id", $user_id)
            ->where("currency", 3) //usdt
            ->lockForUpdate()
            ->first();

        $redis = Redis::connection();
        $lock = new RedisLock($redis,'user_bank_loan_'.$user_id,10);
        if($lock->acquire()) {
            DB::beginTransaction();
            try{
                //创建订单
                LhLoanOrder::newOrder($res->id,$amount);
                //贷款出账
                switch ($type){
                    case 1:
                        //资金钱包
                        change_wallet_balance(
                            $legal,
                            1,
                            +$amount,
                            AccountLog::LH_LOAN,
                            '质押入账',
                            false,
                            0,
                            0,
                            serialize([])
                        );
                        break;
                    case 2:
                        change_wallet_balance(
                            $legal,
                            4,
                            +$amount,
                            AccountLog::LH_LOAN,
                            '质押入账',
                            false,
                            0,
                            0,
                            serialize([])
                        );
                        break;
                    case 3:
                        change_wallet_balance(
                            $legal,
                            3,
                            +$amount,
                            AccountLog::LH_LOAN,
                            '质押入账',
                            false,
                            0,
                            0,
                            serialize([])
                        );
                        break;
                        break;
                    default:
                        $res->usdt_balance += $amount;
                        $res->save();
                        LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_USDT,$amount,'质押入账');

                }

                DB::commit();
                $lock->release();
            }catch (\Exception $e){
                DB::rollBack();
                return $this->error('质押失败');
            }
        }
        return $this->success('质押成功');
    }

    public function loanList(Request $request){
        $user_id = Users::getUserId();
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error('您还未创建账户');
        }
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
        $status = $request->get('status',null);
        $where = [];
        if($status){
            $where['status'] = $status;
        }
        $res = LhLoanOrder::where('bank_account_id',$res->id)
            ->where($where)
            ->orderBy('id','desc')
            ->skip($limit*($page-1))->take($limit)
            ->get();
        return $this->success($res);
    }

    public function repayment(Request $request){
        $user_id = Users::getUserId();
        $res = LhBankAccount::where('uid',$user_id)->first();
        if(!$res){
            return $this->error('您还未创建账户');
        }
        $is_zj_wallet = Input::get('is_zj_wallet',0);
        $amount = Input::get('amount',0);
        if($amount <0){
            return $this->error('还款金额有误');
        }
        //usdtWallet

        if($is_zj_wallet){
            $legal = UsersWallet::where("user_id", $user_id)
                ->where("currency", 3) //usdt
                ->lockForUpdate()
                ->first();
            if (!$legal) {
                return $this->error("钱包未找到,请先添加钱包");
            }
            if($legal->legal_balance < $amount){
                return $this->error('资金钱包余额不足');
            }
        }else{
            if($amount> $res->usdt_balance){
                return $this->error('余币宝账号余额不足');
            }
        }

        $orderId = Input::get('order_id');
        if(!$orderId){
            return $this->error('参数异常');
        }
        $order = LhLoanOrder::where(['id' => $orderId,'bank_account_id' => $res->id])->first();
        if(!$order){
            return $this->error('找不到质押单');
        }
        $maxReturn = ($order->amount+$order->total_interest)-$order->total_return;
        if($maxReturn<=0){
            return $this->error('已还清');
        }
        DB::beginTransaction();
        try{
            //先判断还款金额是否超过所需还款金额
            if($amount - $maxReturn >= 0){
                $amount = $maxReturn;
                $status = 2;
            }else{
                $status = 1;
            }
            //扣款
            if(isset($legal)){
                change_wallet_balance(
                    $legal,
                    1,
                    -$amount,
                    AccountLog::TRANSFER_TO_LH_ACCOUNT,
                    '转账入余币宝',
                    false,
                    0,
                    0,
                    serialize([])
                );
                LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_USDT,+$amount,'资金账户转入余币宝');
                LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_USDT,-$amount,'质押还款');
            }else{
                $balance = bc_sub($res->usdt_balance,$amount,8);
                $res->usdt_balance = $balance;
                $res->save();
                LhBankAccountLog::newLog($res->id,LhBankAccountLog::LOG_TYPE_USDT,-$amount,'质押还款');
            }

            //质押单更新
            $order->total_return = bc_add($order->total_return,$amount,8);
            $order->status = $status;
            $order->end_at = date("Y-m-d");
            $order->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('还款成功');
    }
}

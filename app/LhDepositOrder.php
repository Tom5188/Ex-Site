<?php


namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LhDepositOrder extends Model
{
    public $table = 'lh_deposit_order';
    protected $appends = [
            'cancel_fee'
        ];
    public static function newOrder($user_id,$currency_id,$amount,$day,$rate){
        // $LhDepositOrder = new self();
        // $order = $LhDepositOrder::where('user_id', $user_id)->where('currency_id', $currency_id)->where('status', 1)->first();
        // if($order){
        //     $real_amount = $order->amount + $amount;
        //     $config = DB::table('lh_deposit_config')->where('save_min', '<=', $real_amount)->where('save_max', '>=', $real_amount)->first();
        //     $order->day_rate = $config->interest_rate / 100;
        //     $order->amount += $amount;
        //     $order->lock_amount += $amount;
        //     $order->save();
        //     return $order;
        // }else{
            $model = new self();
            $model->user_id = $user_id;
            $model->currency_id = $currency_id;
            $model->amount = $amount;
            $model->lock_amount = $amount;
    //        $model->day_rate = self::getDayRate($amount);
            $model->start_at = date("Y-m-d",strtotime('+1 day'));
            // $model->day_rate = ($rate/$day)/100;
            $model->day_rate = $rate/100;
            $day += 1;
            // $day = Setting::getValueByKey('AUTU_UNLOCK_DAY',30);
            $model->end_at = date("Y-m-d",strtotime("+$day day"));
            $model->save();
            return $model;
        // }
    }
    
    public static function unlockMoney($orderId){
        $model = self::find($orderId);
        if(!$model){
            return false;
        }
         $account = LhBankAccount::find($model->bank_account_id);
        if($model->status != 1){
            return false;
        }
        if(strtotime($model->end_at) > time()){
            return false;
        }
        echo 3333;
        $dfWallet = UsersWallet::getDF1Wallet($account->uid);
        $usdtWallet = UsersWallet::getUsdtWallet($account->uid);
        DB::beginTransaction();
        try{
            //获取用户的df钱包
            change_wallet_balance(
                $dfWallet,
                2,
                $model->amount,
                AccountLog::TRANSFER_TO_LH_ACCOUNT,
                '理财账户解冻',
                true,
                0,
                0,
                serialize([])
            );
            change_wallet_balance(
                $usdtWallet,
                2,
                $model->usdt_amount,
                AccountLog::TRANSFER_TO_LH_ACCOUNT,
                '理财账户解冻',
                false,
                0,
                0,
                serialize([])
            );
            $model->status = 2;
            // echo '解冻';
            // DB::table('lh_bank_account')->where('id',$model->bank_account_id)->increment('coin_lock_balance',$model->amount);
                
            // DB::table('wallet_unlock_order')->insert([
            //     'user_id' => $dfWallet->user_id,
            //     'wallet_id' => $dfWallet->id,
            //     'currency_id' => $dfWallet->currency,
            //     'amount' => $model->amount,
            //     'remain_amount' => $model->amount,
            //     'created_at' => date("Y-m-d H:i:s")
            // ]);
           
            
            $model->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
        return true;
    }

    public static function dispatchInterest($orderId){
        $model = self::find($orderId);
        if(!$model){
            return false;
        }
        $user = Users::find($model->user_id);
        if(!$user){
            $model->delete();
            return false;
        }
        // if($user->parent_id){
        //     $dad = Users::find($user->parent_id);
            
        //     if($dad && $dad->parent_id){
        //         $g_dad = Users::find($dad->parent_id);
        //     }
        // }
        
        //先判断上次派息时间是否存在  不存在从开始时间派息
        $startDay = $model->last_settle_time?date("Y-m-d",strtotime($model->last_settle_time)):$model->start_at;
        $endDay = date("Y-m-d",strtotime('-1 day'));
        if($model->last_settle_time >= date("Y-m-d")){
            return false;
        }
        //结息时间异常时
        if($startDay > $endDay){
            //不需要结息
            return false;
        }
        //当有结息时间时 且结息时间和开始时间相同 直接返回
//        if($startDay == $endDay && $model->last_settle_time){
//            return false;
//        }

        //计算需要派息的天数
        $d1=strtotime($startDay);
        $d2=strtotime($endDay);
        $dayCount=round(($d2-$d1)/3600/24);
        $totalInterest = 0;
        $legal = UsersWallet::where("user_id", $user->id)
            ->where("currency", $model->currency_id) //usdt
            ->lockForUpdate()
            ->first();
        DB::beginTransaction();
        try{
            for($i=0;$i<=$dayCount;$i++){
                //执行结息操作,先加钱
                $day = date("Y-m-d",strtotime("+$i day",strtotime($startDay)));
                //利率需要计算
                $config = DB::table('lh_deposit_config')->where('save_min', '<=', $model->amount)->where('save_max', '>=', $model->amount)->first();
                $model->day_rate = $config->interest_rate / 100;
                $interest = bc_mul($model->amount ,$model->day_rate);
                $totalInterest = bc_add($totalInterest,$interest);
                // if(strpos($model->withdraw_day, $day) !== false){
                //     // 提现返回账户
                //     change_wallet_balance(
                //         $legal,
                //         2,
                //         $interest,
                //         AccountLog::BANK_WITHDRAW,
                //         '质押利息提现|'.$day,
                //         false,
                //         0,
                //         0,
                //         serialize([])
                //     );
                //     $model->withdraw_amount += $interest;
                // }else{
                //     // 不提现加到本金
                //     $model->amount += $interest;
                // }
                $model->amount += $interest;
                try {
                    //写入结息的log
                    LhDepositOrderLog::newLog($user->id,$model->id,$interest,$day);
                } catch (\Exception $e) {
                    // 已经存在，会抛出错误，继续下一步执行
                    continue;
                }
            }
            //更新订单总利息
            $model->total_interest += $totalInterest;

            $model->last_settle_time = date("Y-m-d");
            //最后一天 退钱
            if($model->end_at <= date('Y-m-d')){
                //结单 退钱操作
                // change_wallet_balance(
                //     $legal,
                //     2,
                //     -$model->lock_amount,
                //     AccountLog::MINING_BUY,
                //     '质押解冻|'.date('Y-m-d'),
                //     true,
                //     0,
                //     0,
                //     serialize([])
                // );
                change_wallet_balance(
                    $legal,
                    2,
                    $model->amount,
                    AccountLog::MINING_BUY,
                    '质押返还|'.date('Y-m-d'),
                    false,
                    0,
                    0,
                    serialize([])
                );
                $model->status = 2;
            }
            
            // //上级返利
            // if(isset($dad)){
            //     self::parent_candy($dad->id,$model->currency_id,$totalInterest*Setting::getValueByKey('first_generation_reward')/100);
            // }
            // if(isset($g_dad)){
            //     self::parent_candy($g_dad->id,$model->currency_id,$totalInterest*Setting::getValueByKey('second_generation_reward')/100);
            // }
            $model->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
    
    public static function parent_candy($user_id,$currency_id,$amount){
         $legal = UsersWallet::where("user_id", $user_id)
            ->where("currency", $currency_id) //usdt
            ->lockForUpdate()
            ->first();
         change_wallet_balance(
                $legal,
                2,
                $amount,
                AccountLog::LOWER_REBATE,
                '质押下级返利',
                false,
                0,
                0,
                serialize([])
            );
    }

    public static function getDayRate($level){
        switch ($level){
            case 1:
                return 0.003;
                break;
            case 2:
                return 0.004;
                break;
            case 3:
                return 0.005;
                break;
            case 4:
                return 0.006;
                break;
            default:
                return 0;
        }

    }
    
    public function getCancelFeeAttribute(){
        // $amount = $this->attributes['amount'];
        $lock_amount = $this->attributes['lock_amount'];
        // $end_at = $this->attributes['end_at'];
        
        // $since = new DateTime($end_at);
        // $until = new DateTime('now'); // 当前日期
         
        // // 计算两个日期之间的差异
        // $interval = $since->diff($until);
        
        $rate = Setting::getValueByKey('cancel_deposit_fee');
        $rate /= 100;
        // $cancel_amount = $lock_amount * $rate * $interval->days;
        // if($cancel_amount > $amount)
        //     $cancel_amount = $amount;
        // return $cancel_amount;
        return $lock_amount * $rate;
    }
}

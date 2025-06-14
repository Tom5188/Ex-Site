<?php


namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CurrencyDepositOrder extends Model
{
    public $table = 'currency_deposit_order';
     public $appends = [
        'currency_name' ,
        'currency_logo',
        'output_currency_name',
        'output_currency_logo',
        'total_output'
    ];

    public function getCurrencyNameAttribute(){
        // $id = $this->attributes['currency_id'];
        return $this->currencyCoin()->value('name');
    }
     public function getCurrencyLogoAttribute(){
        // $id = $this->attributes['currency_id'];
        return $this->currencyCoin()->value('logo');
    }
     public function getOutputCurrencyLogoAttribute(){
        // $id = $this->attributes['currency_id'];
        return $this->outputCurrencyCoin()->value('logo');
    }
    public function getOutputCurrencyNameAttribute(){
         return $this->outputCurrencyCoin()->value('name');
    }
    public function currencyCoin()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
    public function outputCurrencyCoin()
    {
        return $this->belongsTo(Currency::class, 'output_currency_id', 'id');
    }
    public static function dispatchInterest($orderId){
        $model = self::find($orderId);
        if(!$model){
            return false;
        }
        $legal = UsersWallet::where("user_id", $model->u_id)
            ->where("currency", $model->currency_id)
            ->lockForUpdate()
            ->first();
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

        DB::beginTransaction();
        try{
            for($i=0;$i<=$dayCount;$i++){
                //执行结息操作
                //1先加钱
                $day = date("Y-m-d",strtotime("+$i day",strtotime($startDay)));

                $interest = bc_mul($model->amount ,$model->day_rate/100,8);
                $totalInterest = bc_add($totalInterest,$interest);
                //写入结息的log
//                LhDepositOrderLog::newLog($model->bank_account_id,$model->id,$interest,$day);
            }
            //更新订单利息
            $model->total_interest += $totalInterest;
            $model->last_settle_time = date("Y-m-d");
            //写入结息的log
            DB::table('currency_deposit_order_log')->insert([
                    'amount' => $totalInterest,
                    'currency_id' => $model->currency_id,
                    'order_id' => $model->id,
                    'user_id' => $model->user_id
                ]);
            // change_wallet_balance(
            //     $legal,
            //     2,
            //     $totalInterest,
            //     AccountLog::LH_LOAN,
            //     '质押利息',
            //     false,
            //     0,
            //     0,
            //     serialize([])
            // );
            //最后一天 退钱
            if($model->end_at == date('Y-m-d')){
                //结单 退钱操作
                change_wallet_balance(
                    $legal,
                    2,
                    $model->amount,
                    AccountLog::LH_LOAN,
                    '质押返还',
                    false,
                    0,
                    0,
                    serialize([])
                );
                $model->status = 2;
            }
            $model->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
        return true;
    }
    
    public function getTotalOutputAttribute(){
        
        $amount = $this->amount*(100+$this->total_rate)/100;
                //获取交易币汇率
                if($this->output_currency_id == $this->currency_id){
                    $rate = 1;
                }else{
                    if($this->currency_id == 3){
                         $payPrice = 1;
                    }else{
                        $payPrice = CurrencyQuotation::where('legal_id',3)->where('currency_id',$this->currency_id)->value('now_price');
                    }
                    
                    if($this->output_currency_id == 3){
                        $outPrice = 1;
                    }else{
                        $outPrice = CurrencyQuotation::where('legal_id',3)->where('currency_id',$this->output_currency_id)->value('now_price');
                    }
                    $rate = $payPrice/$outPrice;
                }
        return $amount *= $rate;
    }
}

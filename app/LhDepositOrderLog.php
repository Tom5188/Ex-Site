<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LhDepositOrderLog extends Model
{
    public $table = 'lh_deposit_order_log';
    const UPDATED_AT = null;
    public static function newLog($accountId,$orderId,$amount,$logDay){
        $model = new self();
        $model->bank_account_id = $accountId;
        $model->lh_order_id = $orderId;
        $model->interest_amount = $amount;
        $model->interest_day = $logDay;

        $model->save();
    }
}

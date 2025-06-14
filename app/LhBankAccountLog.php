<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
class LhBankAccountLog extends Model
{
	public $table = "lh_bank_account_log";
	const LOG_TYPE_USDT = 1;
	const LOG_TYPE_DF = 2;
	public static function newLog($account_id, $type, $amount, $description = '')
	{
		$RZPvFtQ = new self();
		$RZPvFtQ->account_id = $account_id;
		$RZPvFtQ->type = $type;
		$RZPvFtQ->amount = $amount;
		$RZPvFtQ->description = $description;
		$RZPvFtQ->save();
	}
}
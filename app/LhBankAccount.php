<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class LhBankAccount extends Model
{
	public $table = "lh_bank_account";
	const INIT_BALANCE = 0;
	public static function getLastDayProfit($accountId)
	{
		$CFGgyFv = LhDepositOrderLog::where("bank_account_id", $accountId)->where("interest_day", date("Y-m-d", strtotime("-1 day")))->sum("interest_amount");
		return (float) $CFGgyFv;
	}
	public static function getTotalDeposit($accountId)
	{
		$_var_0 = LhDepositOrder::where("bank_account_id", $accountId)->where("status", 1)->sum("amount");
		return $_var_0;
		$_var_1 = LhDepositOrder::where("bank_account_id", $accountId)->where("status", 1)->sum("total_interest");
		return bc_add($_var_0, $_var_1, 4);
	}
	public static function totalLoan($accountId)
	{
		$WCNtynQ = LhLoanOrder::where("bank_account_id", $accountId)->where("status", 1)->sum("amount");
		$StHHMPv = LhLoanOrder::where("bank_account_id", $accountId)->where("status", 1)->sum("total_interest");
		$LBHCnqJ = LhLoanOrder::where("bank_account_id", $accountId)->where("status", 1)->sum("total_return");
		return bc_sub(bc_add($WCNtynQ, $StHHMPv, 4), $LBHCnqJ, 4);
	}
	public static function canLoan($accountId)
	{
		$_var_2 = LhLoanOrder::where(["bank_account_id" => $accountId, "status" => 1])->sum("amount");
		$_var_3 = LhBankAccount::getTotalDeposit($accountId);
		$_var_4 = bc_mul($_var_3, 0.8, 0);
		return $_var_4 - $_var_2;
	}
	public static function newAccount($user_id)
	{
		DB::beginTransaction();
		try {
			$_var_5 = new LhBankAccount();
			$_var_5->uid = $user_id;
			$_var_5->usdt_balance = LhBankAccount::INIT_BALANCE;
			$_var_5->p_uid = null;
			$_var_5->save();
			DB::commit();
		} catch (\Exception $_var_6) {
			DB::rollBack();
			throw $_var_6;
		}
	}
	public static function updateDepositLevel($accountId)
	{
		$_var_7 = self::getTotalDeposit($accountId);
		if ($_var_7 > Setting::getValueByKey("LH_DEPOSIT_LEVEL_M4")) {
			$_var_8 = 4;
		} elseif ($_var_7 > Setting::getValueByKey("LH_DEPOSIT_LEVEL_M3")) {
			$_var_8 = 3;
		} elseif ($_var_7 > Setting::getValueByKey("LH_DEPOSIT_LEVEL_M2")) {
			$_var_8 = 2;
		} elseif ($_var_7 >= Setting::getValueByKey("LH_DEPOSIT_LEVEL_M1")) {
			$_var_8 = 1;
		} else {
			$_var_8 = 0;
		}
		self::where("id", $accountId)->update(["m_level" => $_var_8]);
	}
}
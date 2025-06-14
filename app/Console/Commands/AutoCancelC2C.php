<?php

namespace App\Console\Commands;

use App\AccountLog;
use App\C2cDeal;
use App\C2cDealSend;
use App\UsersWallet;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class AutoCancelC2C extends Command
{
	protected $signature = "auto_cancel_c2c";
	protected $description = "自动取消24小时C2C发布";
	public function __construct()
	{
		parent::__construct();
	}
	public function handle()
	{
		$now = Carbon::now();
		$this->info('开始执行自动取消C2C发布脚本-' . $now->toDateTimeString());
		$twenty_four = $now->subHours(24)->timestamp;
		$results = C2cDealSend::where('create_time', '<=', $twenty_four)->where('is_done', 0)->get();
		$count = count($results);
		$this->info('共有 ' . $count . ' 条可取消C2C发布');
		try {
			DB::beginTransaction();
			if (!empty($results)) {
				$i = 1;
				foreach ($results as $result) {
					$this->info('执行第 ' . $i . ' 条记录');
					$legal_deal_send = C2cDealSend::lockForUpdate()->find($result->id);
					$wallet = UsersWallet::where('user_id', $legal_deal_send->seller_id)->where('currency', $legal_deal_send->currency_id)->lockForUpdate()->first();
					if ($legal_deal_send->type == 'sell') {
						$data_wallet1 = ['balance_type' => 2, 'wallet_id' => $wallet->id, 'lock_type' => 0, 'create_time' => time(), 'before' => $wallet->change_balance, 'change' => $legal_deal_send->total_number, 'after' => bc_add($wallet->change_balance, $legal_deal_send->total_number, 5)];
						$data_wallet2 = ['balance_type' => 2, 'wallet_id' => $wallet->id, 'lock_type' => 1, 'create_time' => time(), 'before' => $wallet->lock_change_balance, 'change' => -1 * $legal_deal_send->total_number, 'after' => bc_sub($wallet->lock_change_balance, $legal_deal_send->total_number, 5)];
						$wallet->change_balance = bc_add($wallet->change_balance, $legal_deal_send->total_number, 5);
						$wallet->lock_change_balance = bc_sub($wallet->lock_change_balance, $legal_deal_send->total_number, 5);
						$wallet->save();
						AccountLog::insertLog(['user_id' => $legal_deal_send->seller_id, 'value' => $legal_deal_send->total_number, 'info' => '24小时未交易，发布取消，增加余额', 'type' => AccountLog::C2C_POST_AUTO_CANCEL, 'currency' => $legal_deal_send->currency_id], $data_wallet1);
						AccountLog::insertLog(['user_id' => $legal_deal_send->seller_id, 'value' => $legal_deal_send->total_number * -1, 'info' => '24小时未交易，发布取消，锁定余额减少', 'type' => AccountLog::C2C_POST_AUTO_CANCEL, 'currency' => $legal_deal_send->currency_id], $data_wallet2);
					}
					$legal_deal_send->is_done = 2;
					$legal_deal_send->save();
					$i++;
				}
			}
			DB::commit();
			$this->info('执行成功');
		} catch (\Exception $exception) {
			DB::rollback();
			$this->error($exception->getMessage());
		}
	}
}
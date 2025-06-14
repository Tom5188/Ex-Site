<?php

namespace App\Console\Commands;

use App\AccountLog;
use App\C2cDeal;
use App\C2cDealSend;
use App\UsersWallet;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class AutoCancelC2CDeal extends Command
{
	protected $signature = "auto_cancel_c2c_deal";
	protected $description = "15分钟自动取消C2C交易";
	public function __construct()
	{
		parent::__construct();
	}
	public function handle()
	{
		$now = Carbon::now();
		$this->info('开始执行15分钟自动取消C2C交易脚本-' . $now->toDateTimeString());
		$fiveteen = $now->subMinutes(15)->timestamp;
		$results = C2cDeal::where('create_time', '<=', $fiveteen)->where('is_sure', 0)->get();
		$count = count($results);
		$this->info('共有 ' . $count . ' 条可取消的记录');
		try {
			DB::beginTransaction();
			if (!empty($results)) {
				$i = 1;
				foreach ($results as $result) {
					$this->info('执行第 ' . $i . ' 条记录');
					C2cDeal::cancelLegalDealById($result->id, AccountLog::C2C_DEAL_AUTO_CANCEL);
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
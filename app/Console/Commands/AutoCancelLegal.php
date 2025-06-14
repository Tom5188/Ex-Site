<?php

namespace App\Console\Commands;

use App\AccountLog;
use App\LegalDeal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class AutoCancelLegal extends Command
{
	protected $signature = "auto_cancel_legal";
	protected $description = "自动取消 24 小时法币交易";
	public function __construct()
	{
		parent::__construct();
	}
	public function handle()
	{
		$now = Carbon::now();
		$this->info('开始执行自动取消法币交易脚本-' . $now->toDateTimeString());
		$twenty_four = $now->subHours(24)->timestamp;
		$results = LegalDeal::where('create_time', '<=', $twenty_four)->where('is_sure', 0)->get();
		$count = count($results);
		$this->info('共有 ' . $count . ' 条超时记录');
		DB::beginTransaction();
		try {
			if (!empty($results)) {
				$i = 1;
				foreach ($results as $result) {
					$this->info('执行第 ' . $i . ' 条记录');
					LegalDeal::cancelLegalDealById($result->id, AccountLog::LEGAL_DEAL_AUTO_CANCEL);
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
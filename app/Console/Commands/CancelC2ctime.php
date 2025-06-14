<?php

namespace App\Console\Commands;

use App\C2cDeal;
use App\Users;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\C2cDealSend;
use App\Setting;
use App\LegalDeal;
class CancelC2ctime extends Command
{
	protected $signature = "cancel:c2cdeal";
	protected $description = "c2c取消订单倒计时";
	public function __construct()
	{
		parent::__construct();
	}
	public function handle()
	{
		$now = Carbon::now();
		$this->comment('开始执行自动取消C2C交易脚本-' . $now->toDateTimeString());
		$userLegalDealCancel_time = Setting::getValueByKey("userLegalDealCancel_time") * 60;
		$result = LegalDeal::where("is_sure", 0)->get();
		foreach ($result as $key => $value) {
			$time = time();
			$create_time = strtotime($value->create_time);
			if ($create_time + $userLegalDealCancel_time <= $time) {
				$id = $value->id;
				if ($value->is_sure == 0) {
					LegalDeal::cancelLegalDealById($id);
					$aaaa = Users::find($value->user_id);
					$aaaa->today_LegalDealCancel_num = $aaaa->today_LegalDealCancel_num + 1;
					$aaaa->LegalDealCancel_num__update_time = time();
					$aaaa->save();
				} else {
					return $this->error('该订单状态不能取消');
				}
			}
		}
		$this->comment('执行成功');
	}
}
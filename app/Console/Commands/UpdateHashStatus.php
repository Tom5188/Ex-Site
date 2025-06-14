<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Utils\RPC;
use App\{Currency, LbxHash, UsersWallet};
use App\DAO\BlockChain;
class UpdateHashStatus extends Command
{
	protected $signature = "update_hash_status";
	protected $description = "更新链上哈希状态";
	public function handle()
	{
		$datas = LbxHash::whereIn('type', [0, 2])->where("status", 0)->get();
		$this->comment("开始执行");
		foreach ($datas as $d) {
			$this->updateHashStatus($d);
		}
		$this->comment("结束任务");
	}
	public function updateHashStatus($data)
	{
		if (empty($data->txid)) {
			return false;
		}
		echo 'id:' . $data->id . ',正在检测Hash:' . $data->txid . PHP_EOL;
		try {
			DB::beginTransaction();
			$user_wallet = UsersWallet::lockForUpdate()->find($data->wallet_id);
			$currency = Currency::find($user_wallet->currency);
			if (empty($currency)) {
				throw new \Exception('币种不存在');
			}
			$currency_type = $currency->type;
			if (!in_array($currency_type, ['usdt', 'btc', 'eth', 'erc20'])) {
				throw new \Exception('不支持的币种');
			}
			$currency_type == 'erc20' && ($currency_type = 'eth');
			if ($data->type == 0) {
				try {
					BlockChain::updateWalletBalance($user_wallet);
				} catch (\Exception $ex) {
					echo $ex->getMessage() . PHP_EOL;
				}
			} elseif ($data->type == 2) {
				if ($currency->type == 'usdt') {
					$currency_type = 'btc';
				} elseif ($currency->type == 'erc20') {
					$currency_type = 'eth';
				}
			}
			$chain_client = app('LbxChainServer');
			$uri = "/wallet/" . $currency_type . '/tx';
			$response = $chain_client->request('get', $uri, ['query' => ['hash' => $data->txid]]);
			$result = $response->getBody()->getContents();
			$result = json_decode($result, true);
			file_exists(base_path('storage/logs/blockchain/')) || @mkdir(base_path('storage/logs/blockchain/'));
			Log::useDailyFiles(base_path('storage/logs/blockchain/blockchain'), 7);
			Log::critical($uri, $result);
			if (isset($result["code"]) && $result["code"] == 0) {
				if ($data->type == 0) {
					$new_balance = bc_sub($user_wallet->old_balance, $data->amount);
					$user_wallet->old_balance = bc_comp($new_balance, 0) > 0 ? $new_balance : 0;
				} elseif ($data->type == 2) {
				}
				$data->status = 1;
				$user_wallet->save();
			} else {
				if (isset($result["code"]) && $result["code"] > 1) {
					$data->status = 2;
				} else {
					throw new \Exception('等待链上确认中');
				}
			}
			$data->save();
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollBack();
			$this->comment($ex->getFile());
			$this->comment($ex->getLine());
			$this->comment($ex->getMessage());
		}
	}
}
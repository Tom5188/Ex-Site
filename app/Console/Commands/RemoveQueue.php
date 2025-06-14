<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
class RemoveQueue extends Command
{
	protected $signature = "remove_queue";
	protected $description = "定期移除积压任务";
	public function handle()
	{
	    echo '移除积压任务开始运行' . PHP_EOL;
		$this->comment("start1");
		$redis = \Illuminate\Support\Facades\Redis::connection();
		$res = $redis->keys('queues:*');
		foreach ($res as $v) {
			if ($redis->type($v) == 'list' && $redis->llen($v) > 3000) {
				$redis->del($v);
			}
		}
		$this->comment("end");
		echo '移除积压任务运行结束' . PHP_EOL;
	}
}
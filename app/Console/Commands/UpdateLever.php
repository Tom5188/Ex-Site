<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\{LeverTransaction};
use App\Jobs\LeverClose;
class UpdateLever extends Command
{
    protected $signature = "remove_task";
    protected $description = "移除积压任务";
    public function handle()
    {
        $this->comment("开始任务");
        \Illuminate\Support\Facades\Redis::del('queues:lever:update');
        $this->comment("结束任务");
    }
}
?>
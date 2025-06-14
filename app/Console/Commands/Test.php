<?php 

namespace App\Console\Commands;

use App\AccountLog;
use App\Currency;
use App\Level;
use App\Users;
use App\UsersWallet;
use App\Setting;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class Test extends Command
{
    protected $signature = "test";
    protected $description = "测试";
    public function handle()
    {
       echo '-----------------------------------------';
       echo '开始我的工作'.date('Y-m-d H:i:s');
       echo '结束我的工作'.date('Y-m-d H:i:s');
    }
}
?>
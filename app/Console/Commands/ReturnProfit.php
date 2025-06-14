<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DAO\FactprofitsDAO;
use Illuminate\Support\Facades\DB;
class ReturnProfit extends Command
{
    protected $signature = "return:profit";
    protected $description = "返还杠杆交易亏损";
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
       
        $aaa = new FactprofitsDAO();
     
        $all = DB::table('lever_transaction')->select("user_id")->groupBy('user_id')->get();
        foreach ($all as $key => $value) {
            var_dump($value->user_id);
            var_dump($aaa::Profit_loss_release($value->user_id));
        }
    }
}
?>
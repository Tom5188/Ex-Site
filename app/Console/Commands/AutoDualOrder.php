<?php

namespace App\Console\Commands;

use App\AutoList;
use App\CurrencyQuotation;
use App\MarketHour;
use App\Setting;
use App\TransactionComplete;
use App\UsersWallet;
use Carbon\Carbon;
use Faker\Factory;
use App\Users;
use App\DualCurrency;
use App\DualOrder;
use App\AccountLog;
use App\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class AutoDualOrder extends Command
{
	protected $signature = "auto_dual_order";
	protected $description = "结算双币订单";
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	public function handle()
	{
	    $dual_orders = DualOrder::where('end_time','<=',date('Y-m-d',time()))->where('status',0)->get();
	    
	    foreach ($dual_orders as $dual_order){
	         $DualCurrency = DualCurrency::where('id',$dual_order->dual_id)->first();
	         $currency_id = $DualCurrency->currency_id;
	         $exercise_price = $DualCurrency->exercise_price;
	         $type = $DualCurrency->type;
	         $now_price = Currency::where('id',$currency_id)->pluck('price')->first();//查询最新价
	         
	         $total = $dual_order->total;
	         $day = $dual_order->day;
	         $rate = $dual_order->order_rate;
	         
	         if($type=='call'){//看涨
	             if($now_price < $exercise_price){
	                 $res = bc_add(1, bc_mul($day, bc_div($rate, 365)));
	                 $money = bc_mul($total, $res, 4);
	                 $settlement_currency = $currency_id;
	             }else{
	                 $res = bc_add(1, bc_mul($day, bc_div($rate, 365)));
	                 $money = bc_mul($exercise_price ,$res, 2);
	                 $settlement_currency = 3;//usdt
	             }
	         }else{//看跌
	             if($now_price <= $exercise_price){
	                 $res = bc_add(1, bc_mul($day, bc_div($rate, 365)));
	                 $money = bc_div($exercise_price, bc_mul($exercise_price, $res), 4);
	                 $settlement_currency = $currency_id;
	             }else{
	                 $res = bc_add(1, bc_mul($day, bc_div($rate, 365)));
	                 $money = bc_mul($exercise_price, $res, 2);
	                 $settlement_currency = 3;//usdt
	             }
	         }
	         $user_walllet=UsersWallet::where("user_id",$dual_order->user_id)->where("currency",$settlement_currency)->first();
	         change_wallet_balance($user_walllet , 2 , $money , AccountLog::USER_DUAL_ORDER_RETURN,'双币理财结算' . $user_walllet->name);
	         $dual_order->status = 1; //修改状态为已结算
	         $dual_order->save();
	         
	    }
	    
	    echo '已结算 '.PHP_EOL;
	}
}
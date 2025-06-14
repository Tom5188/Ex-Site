<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Users;
use App\DualCurrency;
use App\DualOrder;
use App\UsersWallet;
use App\AccountLog;
use App\Currency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


//双币理财
class DualController  extends Controller
{
    //显示理财页面
    public function index(Request $request){
        $currency_id = $request->get('currency_id');
        $type = $request->get('type');
        if(empty($currency_id)){
             $dual_currencys = DualCurrency::where('type',$type) ->orderBy('id','desc')->get();
        }else{
             $dual_currencys = DualCurrency::where('type',$type) ->where('currency_id',$currency_id)->orderBy('id','desc')->get();
        }
        
       
        return $this->success(array('dual_currencys' => $dual_currencys)); 
        
    }
    
    //查看我的理财订单
    public function dual_list(Request $request){
        $limit = $request->get('limit', 10);
        $status = $request->get('status');
        $currency = $request->get('currency');
        $type = $request->get('type');
        $user_id = Users::getUserId();
        $list = DB::table('dual_order')->join('dual_currency', 'dual_currency.id', '=', 'dual_order.dual_id');
            
        if (!empty($currency)) {
            $list = $list->where('dual_currency.currency_id', $currency);
        }
        if (!empty($type)) {
            $list = $list->where('dual_currency.type', $type);
        }
        if (!empty($status)) {
            $list = $list->where('dual_currency.status', $status);
        }
        if (!empty($user_id)) {
            $list = $list->where('dual_order.user_id', $user_id);
        }
        
        $list = $list->select('dual_order.*', 'dual_currency.currency_id','dual_currency.type','dual_currency.exercise_price','dual_currency.remaining_number','dual_currency.currency_name','dual_currency.name')->orderBy('dual_order.id', 'desc')->paginate($limit);

        return $this->success($list);
    }
    
    //购买理财
    public function buyDual(Request $request){
        $id = $request->post('id');
        $num = $request->post('num');
        $user_id = Users::getUserId();
        $num = intval($num);
        
        if(empty($num) || !is_numeric($num)){
            return $this->error('Parameter error：num ');
        }     
        
        if(empty($id) || !is_numeric($id)){
            return $this->error('Parameter error：ID Is NULL');
        }        
        
        if(Cache::has("by_dual_order_$user_id")){
            return $this->error('Do not repeat the operation!'); 
        }
        Cache::put("by_dual_order_$user_id", 1, Carbon::now()->addSeconds(5));//禁止重复提交
        
        $count = DualOrder::where('user_id',$user_id)->where('dual_id',$id)->count();
        $dual_currencys = DualCurrency::where('id',$id)->first();
        if($dual_currencys->end_time <= date('Y-m-d') || $dual_currencys->status == 0){//结束时间等于今天不可购买
            return $this->error('Project has ended!');
        }
        if($num > $dual_currencys->remaining_number){//已经卖完
            return $this->error('Sold out!');
        }
        
        if($count + $num > $dual_currencys->user_limit){
            return $this->error('Purchase limit exceeded!');
        }
        
        $currency = Currency::where('id',$dual_currencys->currency_id)->first();
        if(!$currency){
            return $this->error('Currency does not exist!');
        }
        $now_price = $currency->price;
        
        if($dual_currencys->type == 'call'){
            if($dual_currencys->currency_id == 1){//
                $buy_currency = 1;//消耗BTC
            }else{//ETH
                $buy_currency = 2;//消耗ETH
            }
            $total = bc_mul($num,0.1);//需要消耗的钱ETH 或者BTC最小单位0.1份
            
        }else{
            $buy_currency = 3; //消耗USDT
            $fen = bc_div($now_price, 10);//每一份的价格
            $total = bc_mul($fen, $num);//需要消耗的钱USDT
        }
        $user_walllet=UsersWallet::where("user_id",$user_id)->where("currency",$buy_currency)->first();
        if(!$user_walllet){
            return $this->error('User wallet does not exist!');
        }
        if($user_walllet->change_balance < $total){
            return $this->error('Insufficient balance!');
        }
        
            
        DB::beginTransaction();
        try{
            $d1=strtotime($dual_currencys->end_time);
            $d2 = strtotime(date('Y-m-d'));
            $dayCount=round(($d1- $d2)/3600/24);
            $dual_order = new DualOrder();
            $dual_order->user_id = Users::getUserId();
            $dual_order->currency_id = $buy_currency;//用户购买时消耗的币种
            $dual_order->dual_id = $dual_currencys->id;
            $dual_order->order_rate = $dual_currencys->rate;
            $dual_order->day = $dayCount;
            $dual_order->number = $num;
            $dual_order->end_time = $dual_currencys->end_time;
            $dual_order->price = $now_price;
            $dual_order->total = $total;
            $dual_order->created = date('Y-m-d H:i:s');
            $dual_order->save();
            
            $dual_currencys->refresh();
            if($num > $dual_currencys->remaining_number || $count + $num > $dual_currencys->user_limit){//已经卖完
                DB::rollBack();
                return $this->error('Sold out!');
            }
        
            $dual_currencys->remaining_number = $dual_currencys->remaining_number - $num;
            $dual_currencys->purchased_number = $dual_currencys->purchased_number + $num;
            $dual_currencys->save();
            
            change_wallet_balance($user_walllet , 2 , -$total , AccountLog::USER_DUAL_ORDER_BUY,'双币理财减少' . $user_walllet->name);
            DB::commit();
            return $this->success('Successful');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    public function getDetail(Request $request){
       $dual_id = $request->get('id');
       $dual = DualCurrency::where('id',$dual_id)->first();

        return $this->success($dual); 
    }
}
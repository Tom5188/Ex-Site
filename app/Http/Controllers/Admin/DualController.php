<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use App\Admin;
use App\Users;
use App\Currency;
use App\DualCurrency;
use App\DualOrder;
use Illuminate\Support\Facades\DB;

class DualController extends Controller
{
    private $Month_E = array('01' => "Jan",
        '02' => "Feb",
        '03' => "Mar",
        '04' => "Apr",
        '05' => "May",
        '06' => "Jun",
        '07' => "Jul",
        '08' => "Aug",
        '09' => "Sep",
        '10' => "Oct",
        '11' => "Nov",
        '12' => "Dec");
        
    public function index(){
        
        return view('admin.dual.index');
    }
    
    public function order(){
        
        
        return view('admin.dual.order');
    }
    
    //订单列表
    public function order_lists(Request $request){
        
        $limit = $request->get('limit', 20);
        $currency_id = $request->get('currency_id');
        $user_id = $request->get('user_id');
        
        $list = DB::table('dual_order')->join('dual_currency', 'dual_currency.id', '=', 'dual_order.dual_id');
            
        if (!empty($currency_id)) {
            $list = $list->where('dual_order.currency_id', $currency_id);
        }
        
        if($user_id){
            $list->where('dual_order.user_id','LIKE','%'.$user_id.'%');
        }
        
        $list = $list->select('dual_order.*','dual_currency.name')->orderBy('dual_order.id', 'desc')->paginate($limit);
        return $this->layuiData($list);
    }
    
    public function add(){
        return view('admin.dual.add');
    }
    
    
    public function editDualView(Request $request){
        $id = $request->get('id');
        if(empty($id)){
            return $this->error('参数错误');
        }
        $dual_currency =  DualCurrency::where(['id'=>$id])->first();
        // dump($dual_currency);die;
        return view('admin.dual.edit_dual',['results' => $dual_currency]);
    }
    
    //编辑项目
    public function editDual(Request $request){
        $id = $request->post('id');
        $rate = $request->post('rate');
        $status = $request->post('status');
        $total_number = $request->post('total_number');
        $user_limit = $request->post('user_limit');
        if(empty($id)){
            return $this->error('参数错误');
        }
        $dual_currency =  DualCurrency::where(['id'=>$id])->first();
        
        if($total_number < $dual_currency->remaining_number){
            return $this->error('总份数不能少于已卖出的份额');
        }
        $dual_currency->rate = $rate;
        $dual_currency->total_number = $total_number;
        $dual_currency->user_limit = $user_limit;
        $dual_currency->status = $status;
        $dual_currency->save();
        
        
        return $this->success(['data'=>$dual_currency]);
    }
    
    //新增项目
    public function saveDual(Request $request){
        $data = $request->all();
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        if($data['currency'] && $data['type'] && $data['rate'] && $data['exercise_price'] && $start_time && $end_time && $data['total_number']){
            if(strtotime($start_time) >= strtotime($end_time)){
                return $this->error('开始时间不能大于结束时间'); 
            }
            if(strtotime($end_time)<time()){
                return $this->error('结束时间不能小于当前时间'); 
            }
            
            $cu_name = Currency::where('id',$data['currency'])->first();
            $ends = explode('-',$end_time);
            if(count($ends)!==3){
                return $this->error('日期格式错误'); 
            }
            $_tp = $data['type'] =='call'? 'C':'P';
            
            $name = $cu_name->name .'-'.$ends[2] . strtoupper($this->Month_E[$ends[1]]) . substr($ends[0],-2) . '-' . $data['exercise_price'] . '-' . $_tp;
            
            $dual_currency = new DualCurrency();
            $dual_currency->name = $name;
            $dual_currency->rate = $data['rate'];
            $dual_currency->exercise_price = $data['exercise_price'];
            $dual_currency->currency_name = Currency::where('id',$data['currency'])->pluck('name')->first();
            $dual_currency->currency_id = $data['currency'];
            $dual_currency->type = $data['type'];
            $dual_currency->start_time = $start_time;
            $dual_currency->end_time = $end_time;
            $dual_currency->total_number = $data['total_number'];
            $dual_currency->user_limit = $data['user_limit'];
            $dual_currency->remaining_number = $data['total_number'];
            $dual_currency->created = date('Y-m-d',time());
            
            
            $dual_currency->save();
            
            return $this->success('操作成功');
        }else{
            
          return $this->error('必填项不能为空');  
        }
    }
    
    //获取实时价格
    public function getNewPrice(Request $request){
        $currency = $request->get('currency');
        $currencys = Currency::where('id',$currency)->get()->toArray();
        
        return $this->success(['data'=>$currencys]);
    }
    
    //理财设置
    public function lists(Request $request)
    {
        $limit = $request->get('limit', 20);
        $status = $request->get('status');
        $currency_id = $request->get('currency_id');
        $results = DualCurrency::where('id','>',1);
        
        if($currency_id){
            $results->where('currency_id',$currency_id);
        }
        
        if(isset($status)){
            $results->where('status',$status);
        }
        
        // dump($results);die;
        $results = $results->orderBy('id', 'desc')->orderBy('status','desc')->paginate($limit);
        
        return $this->layuiData($results);
    }
    
    
    
}

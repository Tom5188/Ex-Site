<?php


namespace App\Http\Controllers\Admin;

use App\CurrencyDepositOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Currency;
use App\CurrencyDeposit;
use App\LhDepositOrder;
class CurrencyDepositController extends Controller
{
    public function currencyConfig(Request $request){
        $currencyId = $request->route('currency_id');
        $limit = $request->get('limit', 20);
        // var_dump($currencyId);exit;
        $list = CurrencyDeposit::where('currency_id',$currencyId)->orderBy('id', 'asc')->paginate($limit);
       
        return $this->layuiData($list);
    }
    
    public function newConfig(Request $request){
        $currencyId = $request->get('currency_id');
        $day = $request->get('day');
        $rate = $request->get('rate');
        $save_min = $request->get('save_min');
        $output = $request->get('out_currency_id');
        $res = DB::table('currency_deposit')->where('currency_id',$currencyId)->where('day',$day)->where('output_currency_id',$output)->first();
        if($res){
            return $this->error('存在重复的期限');
        }
        DB::table('currency_deposit')->insert([
            'day' => $day,
            'output_currency_id' => $output,
            'total_interest_rate' => $rate,
            'currency_id' => $currencyId,
            'save_min' => $save_min,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->success('创建成功');
    }

    public function deleteConfig(Request $request){
        $configId = $request->get('id');
        DB::table('currency_deposit')->where('id',$configId)->delete();
        return $this->success('删除成功');
    }
    
    public function editConfig(Request $request){
        $configId = $request->get('id');
        $day = $request->get('day');
        $rate = $request->get('rate');
        $save_min = $request->get('save_min');
        $model = DB::table('currency_deposit')->where('id',$configId)->first();
        if(!$model){
            return $this->error('找不到次期限');
        }
        $res = DB::table('currency_deposit')->where('id','<>',$configId)->where('currency_id',$model->currency_id)->where('day',$day)->first();
        if($res){
            return $this->error('存在重复的期限');
        }
        DB::table('currency_deposit')->where('id',$configId)->update([
            'day' => $day,
            // 'total_rate' => $rate,
            'total_interest_rate' => $rate,
            'save_min' => $save_min,
            // 'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->success('创建成功');
    }
    
     public function orderList(Request $request){
        $account = $request->get('account_number');
        $currencyId = $request->get('currency_id');
        $status = $request->get('status');
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
        $where = [];
        if($currencyId){
            $where['currency_id'] = $currencyId;
        }
        if($status){
            $where['lh_deposit_order.status'] = $status;
        }
        
        // $model = new CurrencyDepositOrder();
        // $list = $model->join('users','users.id','=','u_id')->join('currency','currency.id','=','currency_id')
        //     ->where(function($q)use($account){
        //         if($account){
        //             $q->where('users.account_number','=',$account)->orWhere('users.email','=',$account);
        //         }
        //     })->where($where)->orderBy('currency_deposit_order.created_at','desc')
        //     ->select(['currency.name as currency_name','users.account_number','currency_deposit_order.*'])
        //     ->paginate($limit);
        // $list = LhDepositOrder::join('currency','currency.id','=','currency_id')
        //     ->where($where)
        //     ->orderBy('lh_deposit_order.id','desc')
        //     ->skip($limit*($page-1))->take($limit)
        //     ->get(['currency.name as currency_name','lh_deposit_order.id','amount','day_rate','total_interest','start_at','end_at','status'])
        //     ->paginate($limit);
        $model = new LhDepositOrder();
        $list = $model->join('users','users.id','=','user_id')->join('currency','currency.id','=','currency_id')
            ->where(function($q)use($account){
                if($account){
                    $q->where("users.account_number", 'like', '%' . $account . '%')->orwhere('users.id', $account);
                }
            })->where($where)->orderBy('lh_deposit_order.created_at','desc')
            ->select(['currency.name as currency_name','users.account_number','lh_deposit_order.*'])
            ->paginate($limit);
        return $this->layuiData($list);
    }
     public function orderView(){
        return view('admin.currency.order_list');
    }
    
     public function addConfigView(Request $request){
         $currencies =  Currency::where('id', '>', 0)->get();
        return view('admin.currency.add_config')->with('currencies', $currencies);
     
    }

    public function editConfigView(Request $request){
        return view('admin.currency.edit_config');
    }

    public function configView(){
        return view('admin.currency.config');
    }
}

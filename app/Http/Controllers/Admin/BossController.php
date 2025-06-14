<?php


namespace App\Http\Controllers\Admin;


use App\BossAccount;
use App\LhDepositOrder;
use App\LhBankAccount;
use App\LhLoanOrder;
use App\AccountLog;
use App\Currency;
use App\UsersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BossController extends Controller
{
    //管理员取消订单 存量超过一天结算收益扣除违约金
    public function cancelOrder(Request $request){
        $orderId = $request->get('id');
        $order = LhDepositOrder::find($orderId);
        if(!$order){
            return $this->error('找不到存单');
        }
        if($order->status!=1){
            return $this->error('存单状态不可操作');
        }
        $legal = UsersWallet::where("user_id", $order->user_id)
            ->where("currency", $order->currency_id) //usdt
            ->lockForUpdate()
            ->first();
        DB::beginTransaction();
        try{
            //扣钱
            $returnAmount = $order->amount - $order->cancel_fee;
            $order->status = 2;
            $order->is_cancel = 1;
            // $result = change_wallet_balance(
            //     $legal,
            //     2,
            //     $returnAmount,
            //     AccountLog::MINING_BUY,
            //     '质押返还',
            //     false,
            //     0,
            //     0,
            //     serialize([])
            // );
            change_wallet_balance(
                $legal,
                2,
                -$order->lock_amount,
                AccountLog::MINING_BUY,
                '质押赎回解冻|'.date('Y-m-d'),
                true,
                0,
                0,
                serialize([])
            );
            change_wallet_balance(
                $legal,
                2,
                $returnAmount,
                AccountLog::MINING_BUY,
                '质押赎回返还|'.date('Y-m-d'),
                false,
                0,
                0,
                serialize([])
            );
            $order->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('毁约成功');
        
    }
    
    public function bossList(Request $request){
        $limit = $request->get('limit', 20);
        $account = $request->get('phone','');
        $pid = $request->get('p_id','');
        $where = [];
        if($pid){
            $where['p_uid'] = $pid;
        }

        $res = BossAccount::join('users as u','uid','=','u.id')
            ->leftJoin('users as dad','dad.id','=','p_uid')
            ->select(['u.id','boss_account.p_uid','u.account_number','u.email','dad.phone as parent_phone','dad.account_number as parent_account','u.phone','total_invited','total_active','total_profit','balance'])
            ->where(function($q)use($account){
                    if($account){
                        $q->where('u.account_number','=',$account)->orWhere('u.email','=',$account);
                    }
            })->where($where)
            ->orderBy('p_uid','asc')
            ->paginate($limit);
        return $this->layuiData($res);
        return $this->success(['list' => $res]);

    }
    public function bossListView(){
        return view('admin.boss.boss_list');
    }

    public function depositOrderList(Request $request){
        $limit = $request->get('limit', 20);
        $where = [];
        $account = $request->get('search');
        $status = $request->get('status');
        $cancel = $request->get('is_cancel',null);
        if($status){
            $where['lh_deposit_order.status'] = $status;
        }
        if($cancel !== null){
            $where['is_cancel'] = $cancel;
        }
        $res = LhDepositOrder::join('currency as c','c.id','=','currency_id')
            ->join('users as u','user_id','=','u.id')
            ->where(function($q)use($account){
                if($account){
                    $q->where('u.account_number','=',$account)->orWhere('u.email','=',$account);
                }
            })->where($where)
            ->select(['u.id as uid','u.account_number','u.email','u.phone','lh_deposit_order.*','c.name as currency_name'])
            ->orderBy('lh_deposit_order.id','desc')
            ->paginate($limit);
        return $this->layuiData($res);

    }
    public function depositOrderView(){
        return view('admin.boss.lh_order_list');
    }
    
    
     public function depositConfig(Request $request){
        $currencyId = $request->get('currency_id');
        $limit = $request->get('limit', 20);
        // var_dump($currencyId);exit;
        $where = [];
        if($where){
            $where['currency_id'] = $currencyId;
        }
        $list = DB::table('lh_deposit_config')->join('currency','currency.id','=','currency_id')->where($where)->select(['currency.name as currency_name','lh_deposit_config.*'])->orderBy('lh_deposit_config.id', 'asc')->paginate($limit);
       
        return $this->layuiData($list);
    }
    public function addConfig(Request $request){
        $currencyId = $request->get('currency_id');
        $day = $request->get('day');
        $rate = $request->get('rate');
        $intro = $request->get('intro');
        $save_min = $request->get('save_min');
        // $output = $request->get('out_currency_id');
        $res = DB::table('lh_deposit_config')->where('currency_id',$currencyId)->where('day',$day)->first();
        if($res){
            return $this->error('存在重复的期限');
        }
        DB::table('lh_deposit_config')->insert([
            'day' => $day,
            'interest_rate' => $rate,
            'intro' => $intro,
            'currency_id' => $currencyId,
            'save_min' => $save_min,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->success('创建成功');
    }

     public function depositConfigView(){
        return view('admin.boss.config');
    }
    public function depositConfigAddView(){
        $currency = Currency::all();
         return view('admin.boss.config_add',['currencies'=>$currency]);
    }
    public function depositConfigEditView(Request $request){
        $configId = $request->get('id');
        $model = DB::table('lh_deposit_config')->where('id',$configId)->first();
        $currency = Currency::where('id',$model->currency_id)->first();
        $model->currency_name = $currency->name;
         return view('admin.boss.config_edit',['model' => $model]);
    }
    public function depositConfigDelete(Request $request){
        $configId = $request->get('id');
        DB::table('lh_deposit_config')->where('id',$configId)->delete();
        return $this->success('删除成功');
    }
    
    
    public function editConfig(Request $request){
        $configId = $request->get('id');
        $day = $request->get('day');
        $rate = $request->get('rate');
        $intro = $request->get('intro');
        $save_min = $request->get('save_min');
        $save_max = $request->get('save_max');
        $model = DB::table('lh_deposit_config')->where('id',$configId)->first();
        if(!$model){
            return $this->error('找不到此配置');
        }
        // $res = DB::table('lh_deposit_config')->where('id','<>',$configId)->where('currency_id',$model->currency_id)->where('day',$day)->first();
        // if($res){
        //     return $this->error('存在重复的期限');
        // }
        DB::table('lh_deposit_config')->where('id',$configId)->update([
            'day' => $day,
            // 'total_rate' => $rate,
            'interest_rate' => $rate,
            'save_min' => $save_min,
            'save_max' => $save_max,
            'intro' => $intro
            // 'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->success('编辑成功');
    }
    
    
    
    
    public function loanOrderView(){
        return view('admin.boss.loan_list');
    }
    public function loanOrderList(Request $request){
        $limit = $request->get('limit', 20);
        $where = [];
        $account = $request->get('search');
        $status = $request->get('status');
        if($status){
            $where['lh_loan_order.status'] = $status;
        }
        $res = LhLoanOrder::join('lh_bank_account as b','bank_account_id','=','b.id')
            ->join('users as u','uid','=','u.id')
            ->where(function($q)use($account){
                if($account){
                    $q->where('u.account_number','=',$account)->orWhere('u.email','=',$account);
                }
            })->where($where)
            ->select(['u.id as uid','u.account_number','u.email','u.phone','lh_loan_order.*'])
            ->orderBy('lh_loan_order.id','desc')
            ->paginate($limit);
        return $this->layuiData($res);
    }
    
    public function lhAccount(Request $request){
        $user_id = $request->get('user_id');
        $account = LhBankAccount::where('uid',$user_id)->first();
        if(!$account){
            return [];
        }
        return $this->success($account);
    }
    public function editLhAccountView(){
        return view('admin.boss.edit_lh_account');
    }
    public function editLhAccount(Request $request){
        $id = $request->get('id');
        $account = LhBankAccount::where('id',$id)->first();
        if(!$account){
            return $this->error('找不到账户');
        }
        
        $account->df_balance = $request->get('df_balance');
        $account->usdt_balance = $request->get('usdt_balance');
        $account->save();
        return $this->success('');
    
        
    }
    
     public function unlockOrder(Request $request){
         $account = $request->get('search');
         $limit = $request->get('limit', 20);
         $res = DB::table('wallet_unlock_order')->join('users as u','user_id','=','u.id')
         ->where(function($q)use($account){
                if($account){
                    $q->where('u.account_number','=',$account)->orWhere('u.email','=',$account);
                }
        })->orderBy('wallet_unlock_order.id','desc')
        ->select(['u.account_number','u.email','u.phone','wallet_unlock_order.*'])
        ->paginate($limit);
        return $this->layuiData($res);
    }
    public function unlockOrderView(){
        return view('admin.boss.unlock_order');
    }

}

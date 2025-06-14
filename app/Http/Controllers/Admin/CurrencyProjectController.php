<?php


namespace App\Http\Controllers\Admin;


use App\Currency;
use App\CurrencyProjectOrder;
use App\CurrencyProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\UsersWallet;
use App\AccountLog;
use Illuminate\Support\Facades\Input;

class CurrencyProjectController extends Controller
{

    
    public function projectDetail(Request $request){
        $id = $request->get('project_id');
        $project = CurrencyProject::find($id);
        return $this->success($project);
    }

    public function projectList(Request $request){
        $limit = $request->get('limit', 20);
        $model = new CurrencyProject();
        $currency_id = $request->get('currency_id');
        $where = [];
        if($currency_id){
            $where['currency_id'] = $currency_id;
        }
        $list = $model->where($where)->paginate($limit);
        return $this->layuiData($list);
    }

    public function newProject(Request $request){
        $currencyId = $request->get('currency_id');
        $payCurrencyId = $request->get('pay_currency_id');
        $title = $request->get('title');
        $summary = $request->get('summary');
        $content = $request->get('content');
        $startAt = $request->get('start_at');
        $endAt = $request->get('end_at');
        $whiteBook = $request->get('white_book');
        $price = $request->get('price');
        $link = $request->get('link');
        $logo = $request->get('logo');
        $min = $request->get('min',0);
        $amount = $request->get('amount');
        $sellBegin = $request->get('sell_begin',null);

        if(empty($amount) ||empty($currencyId) || empty($title) || empty($summary) || empty($content) || empty($startAt) || empty($endAt) || empty($whiteBook) || empty($link)){
            return $this->error('内容不能为空');
        }
        $currency = Currency::find($currencyId);
        if(!$currency){
            return $this->error('币种不存在');
        }
        $payCurrency = Currency::find($payCurrencyId);
        if(!$payCurrency){
            return $this->error('支付币种不存在');
        }
        // $res = CurrencyProject::where('currency_id',$currencyId)->first();
        // if($res){
        //     return $this->error('该币种已成立项目，不能重复');
        // }
        if(strtotime($startAt) < time() || strtotime($endAt) < time()){
            return $this->error('开始时间与结束时间不得早于当前时间');
        }
        if($sellBegin && strtotime($sellBegin) < strtotime($endAt)){
            return $this->error('配售时间不得早于结束时间');
        }
        if(empty($request->input('total_apply')) || empty($request->input('win_rate'))){
            return $this->error('中奖配置不能为空');
        }
        CurrencyProject::insert([
            'currency_id' => $currencyId,
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'white_book' => $whiteBook,
            'link' => $link,
            'price' => $price,
            'pay_currency_id' => $payCurrencyId,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'logo' => $logo,
            'sell_begin' => $sellBegin,
            'min' => $min,
            'amount' => $amount,
            'sell_amount' => $request->input('sell_amount',0),
            'total_apply' => $request->input('total_apply'),
            'win_rate' => $request->input('win_rate')

        ]);
        return $this->success('创建成功');
    }

    public function editProject(Request $request){
        $projectId = $request->get('project_id');
        $title = $request->get('title');
        $summary = $request->get('summary');
        $content = $request->get('content');
        $startAt = $request->get('start_at');
        $price = $request->get('price');
        $endAt = $request->get('end_at');
        $whiteBook = $request->get('white_book');
        $logo = $request->get('logo');
        $link = $request->get('link');
        $min = $request->get('min',0);
        $sellBegin = $request->get('sell_begin',null);
        $amount = $request->get('amount',0);
        if(empty($amount) || empty($projectId) || empty($title) || empty($summary) || empty($content) || empty($startAt) || empty($endAt) || empty($whiteBook) || empty($link)){
            return $this->error('内容不能为空');
        }

        $project = CurrencyProject::find($projectId);
        if(!$project){
            return $this->error('找不到此项目');
        }
        if(strtotime($project->start_at) < time()){
            // return $this->error('项目已开启，不可编辑');
        }
        if($project->status == 2){
            return $this->error('此项目已完结');
        }
        if($sellBegin && strtotime($sellBegin) < strtotime($endAt)){
            return $this->error('配售时间不得早于结束时间');
        }
        $data = [
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'white_book' => $whiteBook,
            'price' => $price,
            'link' => $link,
            'start_at' => $startAt,
            'logo' => $logo,
            'end_at' => $endAt,
            'sell_begin' => $sellBegin,
            'min' => $min,
            'amount' =>$amount,
            'sell_amount' => $request->input('sell_amount',0),
            'total_apply' => $request->input('total_apply'),
            'win_rate' => $request->input('win_rate')
        ];
        CurrencyProject::where('id',$projectId)->update($data);
        return $this->success('创建成功');
    }


    public function projectOrderList(Request $request){
        $id = $request->get('project_id');
        $project = CurrencyProject::find($id);
        $limit = $request->get('limit');
        $type = $request->get('type');
        $search = $request->get('search');
        $status = $request->get('status');
        if(!$project){
            return $this->error('找不到项目');
        }
        $where = [];
        if($type){
            $where['currency_project_order.type'] = $type;
        }
        if($status){
            $where['currency_project_order.status'] = $status;
        }
        $model =  CurrencyProjectOrder::join('users','users.id','=','u_id');
        if($search){
            $model = $model->where("phone", 'like', '%' . $account . '%')
                ->orwhere('email', 'like', '%' . $account . '%')
                ->orWhere('account_number', 'like', '%' . $account . '%');
        }
        $orders =$model->where('project_id',$id)->where($where)
            ->select(['currency_project_order.*','users.account_number'])->paginate($limit);
        return $this->layuiData($orders);
    }

    public function confirmSell(Request $request){
        $order_id = $request->get('order_id');
        $amount = $request->get('amount');
        if(empty($order_id) || empty($amount)){
            return $this->error('参数错误');
        }
        $order = CurrencyProjectOrder::find($order_id);
        if(!$order){
            return $this->error('找不到订单');
        }
        if($order->type != 3){
            return $this->error('订单类型异常');
        }
        if($order->status != 2){
            return $this->error('订单状态异常');
        }
        $price = bc_div($order->total_price,$amount,8);
        $op_wallet = UsersWallet::where('user_id',$order->u_id)
            ->where('currency',$order->currency_id)
            ->first();
        DB::beginTransaction();
        try{
            $result = change_wallet_balance($op_wallet,
                2,
                $amount,
                AccountLog::IEO_OPERATION,
                'ieo order');
            $order->price = $price;
            $order->coin_amount = $amount;
            $order->status = 3;
            $order->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('success');
    }

    public function confirmSellView(){
        return view('admin.currency.confirm_sell');
    }

    public function projectOrderView(){
        return view('admin.currency.project_orders');
    }

    public function projectView(){
        return view('admin.currency.project_list');
    }
    public function projectDetailView(){

        $id = Input::get('id',0);
        $info = null;
        if ($id){
            $info = CurrencyProject::find($id);
        }

        $currencies =  Currency::where('id', '>', 0)->get();
        return view('admin.currency.project_detail')->with('currencies', $currencies)
            ->with('info',$info);
    }
}

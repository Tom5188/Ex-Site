<?php

namespace App\Http\Controllers\Api;

use App\AccountLog;
use App\Currency;
use App\C2cDeal;
use App\C2cDealSend;
// use App\Seller;
use App\LegalDeal;
use App\Setting;
use App\Users;
use App\UsersWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\UserReal;
use App\UserCashInfo;

class C2cDealController extends Controller
{


    
    public function handle_one(Request $request)
    {
        $id = $request->get('id', null);
        $userLegalDealCancel_time=Setting::getValueByKey("userLegalDealCancel_time")*60;
        $result=C2cDeal::find($id);

        $time=time();
        $create_time=strtotime($result->create_time);
//            var_dump($create_time+$userLegalDealCancel_time); var_dump($time);die;
        if(($create_time+$userLegalDealCancel_time)<=$time)
        {
            $id =$result->id;
            C2cDeal::cancelLegalDealById($id);
            //取消订单数加一
            $aaaa=Users::find($result->user_id);
            $aaaa->today_LegalDealCancel_num=$aaaa->today_LegalDealCancel_num+1;
            $aaaa->LegalDealCancel_num__update_time=time();
            $aaaa->save();
        }

    }

    public function postSend(Request $request)
    {
        $type = $request->get('type', null);
        $way = $request->get('way', null);
        $price = $request->get('price', null);
        $total_number = $request->get('total_number', null);
      
        $currency_id = $request->get('currency_id', null);
        if (empty($type)) return $this->error('请选择需求类型');
        if (empty($way)) return $this->error('请选择交易方式');
        if (empty($price)) return $this->error('请填写单价');
        if (empty($total_number)) return $this->error('请填写数量');

        if (empty($currency_id)) return $this->error('请选择币种');
   
        if($price < 0 || $total_number <0){
            return $this->error('请输入正确的交易数量或价格');
        }

        DB::BeginTransaction();
        try {

            $user_id = Users::getUserId();
            //收款方式的判断
            $user_cash_info = UserCashInfo::where('user_id', $user_id)->first();
            if (!$user_cash_info) {
                DB::rollback();
                return response()->json(['type'=>'997','message'=>$this->returnStr('您还没有设置收款信息')]);
            }

            if ($type == 'buy')
            {
                $userLegalDealCancel=Setting::getValueByKey("userLegalDealCancel");
                $user_id = Users::getUserId();
                $user=Users::find($user_id);
                if($user->today_LegalDealCancel_num>=$userLegalDealCancel)
                {
                    return $this->error($this->returnStr('取消次数已超出').$userLegalDealCancel.$this->returnStr('次,明天再发布'));
                }
            }

                if ($type == 'sell')
            {   
                
                $wallet=UsersWallet::where('user_id',$user_id)->where('currency',$currency_id)->lockForUpdate()->first();
                if(empty($wallet)){
                    return $this->error('用户钱包不存在');
                }
                if($wallet->legal_balance < $total_number){
                    return $this->error('对不起，您的钱包余额不足');
                }
                //
                $data_wallet1 = [
                    'balance_type' =>  2,
                    'wallet_id' => $wallet->id,
                    'lock_type' => 0,
                    'create_time' => time(),
                    'before' => $wallet->legal_balance,
                    'change' => -$total_number,
                    'after' => bc_sub($wallet->legal_balance, $total_number, 5),
                ];
                $data_wallet2 = [
                    'balance_type' =>  2,
                    'wallet_id' => $wallet->id,
                    'lock_type' => 1,
                    'create_time' => time(),
                    'before' => $wallet->lock_legal_balance,
                    'change' => $total_number,
                    'after' => bc_add($wallet->lock_legal_balance, $total_number, 5),
                ];

                $wallet->legal_balance = bc_sub($wallet->legal_balance,$total_number,5);
                
                $wallet->lock_legal_balance = bc_add($wallet->lock_legal_balance,$total_number,5);
                $wallet->save();
                AccountLog::insertLog(
                    [
                        'user_id' => $user_id,
                        'value' => $total_number * -1,
                        'info' => '用户发布c2c交易法币出售，扣除法币余额',
                        'type' => AccountLog::C2C_DEAL_SEND_SELL,
                        'currency' => $currency_id
                    ],
                    $data_wallet1
                );
                AccountLog::insertLog(
                    [
                        'user_id' => $user_id,
                        'value' => $total_number,
                        'info' => '用户发布c2c交易法币出售,锁定余额增加',
                        'type' => AccountLog::C2C_DEAL_SEND_SELL,
                        'currency' => $currency_id
                    ],
                    $data_wallet2
                );
            }

            $legal_deal_send = new C2cDealSend();
            $legal_deal_send->seller_id = $user_id;
            $legal_deal_send->currency_id = $currency_id;
            $legal_deal_send->type = $type;
            $legal_deal_send->way = $way;
            $legal_deal_send->price = $price;
            $legal_deal_send->total_number = $total_number;
            $legal_deal_send->surplus_number = $total_number;
            $legal_deal_send->create_time = time();
            $legal_deal_send->save();
            DB::commit();
            return $this->success('发布成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }


    }

    public function sellerInfo(Request $request)
    {
        $id = $request->get('id', null);
        $type = $request->get('type',null);
        $was_done =  $request->get('was_done','false');
        $limit = $request->get('limit', 10);

        if (empty($id)) return $this->error('参数错误');
        $seller = Users::find($id);
        if (empty($seller)) return $this->error('无此用户');
        $beforeThirtyDays = Carbon::today()->subDay(30)->timestamp;  
        $results = Users::withCount(['legalDeal as total', 'legalDeal as done' => function ($query) {
            $query->where('is_sure', 1);
        }, 'legalDeal as thirtyDays' => function ($query) use ($beforeThirtyDays) {
            $query->where('is_sure', 1)->where('update_time', '>=', $beforeThirtyDays);
        }])->find($id);
        $lists = C2cDealSend::where('seller_id', $id);

        if($was_done == 'true'){
            $lists = $lists->where('is_done','=','1');
        }elseif($was_done == 'false'){
            $lists = $lists->where('is_done','=','0');
        }

        if($type == 'buy'){
            $type = 'buy';
            $lists = $lists->where('type',$type);
        }elseif($type == 'sell'){
            $type = 'sell';
            $lists = $lists->where('type',$type);
        }

        $lists = $lists->orderBy('id', 'desc')->paginate($limit);
        $results->lists = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($results);
    }

 
    public function tradeList(Request $request)
    {
        $currency_id = $request->get('currency_id',null);
        $type = $request->get('type',null);
   
        $limit = $request->get('limit', 10);
        $id = Users::getUserId();
        $user=Users::find($id);
        // $seller = Seller::where('user_id', $id)->first();

        if (empty($user)) {
            return $this->error('用户不存在');
        }
        $lists = C2cDealSend::where('seller_id', $id)->where('is_done','<',2);
        //是否完成
        // if ($was_done == 'true') {
        //     $lists = $lists->where('is_done','=','1');
        // } elseif ($was_done == 'false') {
        //     $lists = $lists->where('is_done','=','0');
        // }
        //出售还是购买
        if($type == 'buy') {
            $type = 'buy';
            $lists = $lists->where('type', $type);
        } elseif ($type == 'sell') {
            $type = 'sell';
            $lists = $lists->where('type', $type);
        }
        if($currency_id){
            $lists = $lists->where('currency_id', $currency_id);
        }
        $lists = $lists->whereDoesntHave('legalDeal',function($query){
            $query->where('is_sure','=',1);
        });

        $lists = $lists->orderBy('id', 'desc')->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }


    public function legalDealPlatform(Request $request)
    {
        $limit = $request->get('limit', 10);
        $currency_id = $request->get('currency_id', '');
        $type = $request->get('type', 'sell');
        if (empty($currency_id)) return $this->error('参数错误');
        if (empty($type)) return $this->error('参数错误2');
        $currency = Currency::find($currency_id);
        if (empty($currency)) return $this->error('无此币种');
        if (empty($currency->is_legal)) return $this->error('该币不是法币');

        $results = C2cDealSend::where('currency_id', $currency_id)->where('is_done', 0)->where('type', $type)->orderBy('id', 'desc')->paginate($limit);
        return $this->pageData($results);
    }
    public function legalDealSendInfo(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $legal_deal_send = C2cDealSend::find($id);
        if (empty($legal_deal_send)) return $this->error('无此记录');
        // $legal_deal_send['sell_cash_info'] = UserCashInfo::where('user_id',$legal_deal_send)->first();
        return $this->success($legal_deal_send);
    }

    public function doDeal(Request $request)
    {
        $deal_send_id = $request->get('id', null);
        // $value = $request->get('value', 0);
        // $means = $request->get('means', '');
        if (empty($deal_send_id)) {
            return $this->error('参数错误');
        }
        $user_id = Users::getUserId();
        
        $user_real = UserReal::where('user_id', $user_id)
            ->where('review_status', 2)
            ->first();
        if (!$user_real) {
            return response()->json(['type'=>'998','message'=>$this->returnStr('您还没有通过实名认证')]);
        }
        
        $user_cash_info = UserCashInfo::where('user_id', $user_id)->first();
        if (!$user_cash_info) {
            return response()->json(['type'=>'997','message'=>$this->returnStr('您还没有设置收款信息')]);
        }

        DB::beginTransaction();
        try {

            $legal_deal_send = C2cDealSend::lockForUpdate()->find($deal_send_id);
            if (empty($legal_deal_send)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            if (!empty($legal_deal_send->is_done)) {
                DB::rollback();
                return $this->error('此条交易已完成');
            }
            $money = bc_mul($legal_deal_send->total_number, $legal_deal_send->price, 6);
            $number=$legal_deal_send->total_number;
            $seller =Users::find($legal_deal_send->seller_id);
            if (empty($seller)) {
                DB::rollback();
                return $this->error('未找到该发布用户');
            }
            if ($user_id == $seller->id) {
                DB::rollback();
                return $this->error('不能操作自己的');
            }
            $users_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $legal_deal_send->currency_id)->first();
            if (empty($users_wallet)) {
                DB::rollback();
                return $this->error('您无此钱包账号');
            }
            if (!empty($users_wallet->status)) {
                DB::rollback();
                return $this->error('您的钱包已被锁定，请联系管理员');
            }
            $hasNonDone = C2cDeal::where([
                ['user_id' , '=' , $user_id],
            ])->whereIn('is_sure', [0,3])->first();
            if(!empty($hasNonDone)){
                DB::rollBack();
                return $this->error('检测有未完成交易，请完成后再来！');
            }
            if ($legal_deal_send->type == 'buy') { //求购
                // do something
                if ($users_wallet->legal_balance < $number) {
                    DB::rollback();
                    return $this->error('您的余额不足');
                }
                if ($users_wallet->lock_legal_balance < 0) {
                    DB::rollback();
                    return $this->error('您的法币锁定资金异常,请查看您是否有正在进行的挂单');
                }
                $legal_deal_send->is_done = 1;
                $data_wallet1 = [
                    'balance_type' =>  2,
                    'wallet_id' => $users_wallet->id,
                    'lock_type' => 0,
                    'create_time' => time(),
                    'before' => $users_wallet->legal_balance,
                    'change' => -$number,
                    'after' => bc_sub($users_wallet->legal_balance, $number, 5),
                ];
                $data_wallet2 = [
                    'balance_type' =>  2,
                    'wallet_id' => $users_wallet->id,
                    'lock_type' => 1,
                    'create_time' => time(),
                    'before' => $users_wallet->lock_legal_balance,
                    'change' => $number,
                    'after' => bc_add($users_wallet->lock_legal_balance, $number, 5),
                ];
//                $users_wallet->legal_balance -= $number;
                $users_wallet->legal_balance = bc_sub($users_wallet->legal_balance,$number,5);
//                $users_wallet->lock_legal_balance += $number;
                $users_wallet->lock_legal_balance = bc_add($users_wallet->lock_legal_balance,$number,5);
                $users_wallet->save();
                $legal_deal_send->save();
                AccountLog::insertLog(
                    [
                        'user_id' => $user_id,
                        'value' => $number * -1,
                        'info' => '出售给商家法币,余额减少',
                        'type' => AccountLog::C2C_DEAL_USER_SELL,
                        'currency' => $legal_deal_send->currency_id
                    ],
                    $data_wallet1
                );
                AccountLog::insertLog(
                    [
                        'user_id' => $user_id,
                        'value' => $number,
                        'info' => '出售给商家法币,锁定余额增加',
                        'type' => AccountLog::C2C_DEAL_USER_SELL,
                        'currency' => $legal_deal_send->currency_id
                    ],
                    $data_wallet2
                );

            } elseif ($legal_deal_send->type == 'sell') {
                $legal_deal_send->is_done = 1;
                $legal_deal_send->save();
            }

            $legal_deal = new C2cDeal();
            $legal_deal->legal_deal_send_id = $deal_send_id;
            $legal_deal->user_id = $user_id;
            $legal_deal->seller_id = $seller->id;
            $legal_deal->number = $number; //交易数量
            $legal_deal->create_time = time();
            $legal_deal->save();
//            var_dump(66666666);die;
            if ($legal_deal_send->type == 'buy'){
                Setting::sendSmsForSmsBao($seller->account_number,'您发布的求购信息有用户出售啦，请去 APP 查看吧～');
            }else{
                Setting::sendSmsForSmsBao($seller->account_number,'您发布的出售信息有用户购买啦，请去 APP 查看吧～');
            }
//            var_dump(11124);die;
            DB::commit();
            return $this->success([
                'msg' => '操作成功，请联系商家确认订单',
                'data' => $legal_deal,
            ]);

        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage() . $this->returnStr(',错误位于第') . $exception->getLine() . $this->returnStr(',行'));
        }
    }

    /**
     * 法币交易商家端列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerLegalDealList(Request $request)
    {

        //该天总取消次数是否归零
        $user_id = Users::getUserId();
        $user=Users::find($user_id);
        $lingchen=strtotime(date('Y-m-d'));
//        var_dump($lingchen);die;
        if($user->LegalDealCancel_num__update_time<$lingchen)
        {
            $user->LegalDealCancel_num__update_time=time();
            $user->today_LegalDealCancel_num=0;
            $user->save();
        }

        $limit = $request->get('limit', 10);
        $type = $request->get('type', '');
        $currency_id = $request->get('currency_id', '');

        if (empty($currency_id)) {
            return $this->error('参数错误');
        }
        
        $currency = Currency::find($currency_id);
        if (empty($currency)) {
            return $this->error('无此币种');
        }
        if (empty($currency->is_legal)) {
            return $this->error('该币不是法币');
        }
        $user_id = Users::getUserId();
        $seller=Users::find($user_id);
        if(empty($seller)){
            return $this->error('用户信息不正确');
        }
        
        $results = C2cDeal::where('seller_id', $seller->id);
        if (!empty($type)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            });
        }

        if (!empty($currency_id)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            });
        }
        $results =$results->where('is_sure',1)->orderBy('id', 'desc')->paginate($limit);
        return $this->pageData($results);

    }

    /**
     * 法币交易用户端列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userLegalDealList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $type = $request->get('type', null);
        $currency_id = $request->get('currency_id', '');
        $is_sure = $request->get('is_sure', null);

        if (!empty($currency_id)) {
            $currency = Currency::find($currency_id);
            if (empty($currency)) return $this->error('无此币种');
            if (empty($currency->is_legal)) return $this->error('该币不是法币');
        }

        $user_id = Users::getUserId();

        $results = C2cDeal::where('user_id', $user_id)->whereHas('legalDealSend');
        if (!empty($type)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            });
        }

        if (!empty($currency_id)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            });
        }

        if (!is_null($is_sure)) {
            $results = $results->where('is_sure', $is_sure);
        }
        $results = $results->orderBy('id', 'desc')->paginate($limit);
        return $this->pageData($results);
    }

    /**
     * 订单详情页
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function legalDealInfo(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $legal_deal = C2cDeal::find($id);
        if (empty($legal_deal)) {
            return $this->error('无此记录');
        }
        return $this->success($legal_deal);
    }

    /**
     * 用户确认支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userLegalDealPay(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        $legal_deal = C2cDeal::find($id);
        if (empty($legal_deal)) {
            return $this->error('无此记录');
        }
        DB::beginTransaction();
        try {
            if ($legal_deal->is_sure > 0) {
                DB::rollback();
                return $this->error('该订单已操作过，请勿重复操作');
            }
            $user_id = Users::getUserId();
            if ($legal_deal->type == 'sell') { //用户端-购买
                if ($user_id != $legal_deal->user_id) {
                    DB::rollback();
                    return $this->error('对不起，您无权操作');
                }
            } elseif ($legal_deal->type == 'buy') {
                 //??
                // $seller = Seller::find($legal_deal->seller_id);
                // if ($user_id != $seller->user_id) {
                //     DB::rollback();
                //     return $this->error('对不起，您无权操作');
                // }
                $seller = Users::find($legal_deal->seller_id);
                if ($user_id != $seller->id) {
                    DB::rollback();
                    return $this->error('对不起，您无权操作');
                }

            }
            $legal_deal->is_sure = 3;
            $legal_deal->save();
            DB::commit();
            return $this->success('操作成功，请联系卖家确认');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }


    public function handle()
    {
        $userLegalDealCancel_time=Setting::getValueByKey("userLegalDealCancel_time")*60;
        $result=LegalDeal::where("is_sure",0)->get();//0未确认 1已确认 2已取消 3已付款
        foreach($result as $key=>$value)
        {
            $time=time();
            $create_time=strtotime($value->create_time);
//            var_dump($create_time+$userLegalDealCancel_time); var_dump($time);die;
            if(($create_time+$userLegalDealCancel_time)<=$time)
            {
                $id =$value->id;
                DB::beginTransaction();
                try {
                    LegalDeal::cancelLegalDealById($id);
                    DB::commit();

                    //取消订单数加一
                    $aaaa=Users::find($value->user_id);
                    $aaaa->today_LegalDealCancel_num=$aaaa->today_LegalDealCancel_num+1;
                    $aaaa->save();

                    return $this->success('操作成功，订单已取消');
                } catch (\Exception $exception) {
                    DB::rollback();
                    return $this->error($exception->getMessage());
                }
            }
        }
    }


    /**
     * 用户取消订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userLegalDealCancel(Request $request)
    {
        $userLegalDealCancel=Setting::getValueByKey("userLegalDealCancel");
        //获取今天已经取消的次数
        $user_id = Users::getUserId();
        $user=Users::find($user_id);
        if($user->today_LegalDealCancel_num>=$userLegalDealCancel)
        {
            return $this->error($this->returnStr('您今天的取消次数已超出').$userLegalDealCancel.$this->returnStr('次'));
        }
        else
        {
            $user->today_LegalDealCancel_num=$user->today_LegalDealCancel_num+1;
            $user->save();
        }


        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $legal_deal = C2cDeal::find($id);
        if (empty($legal_deal)) {
            return $this->error('无此记录');
        }
        DB::beginTransaction();
        try {
            if ($legal_deal->is_sure > 0) {
                DB::rollback();
                return $this->error('该订单已操作，请勿取消');
            }
            $user_id = Users::getUserId();
            if ($legal_deal->type == 'sell') { //用户端-购买
                if ($user_id != $legal_deal->user_id) {
                    DB::rollback();
                    return $this->error('对不起，您无权操作');
                }
            } elseif ($legal_deal->type == 'buy') {//用户端出售
  
               
                if ($user_id != $legal_deal->seller_id) {
                    DB::rollback();
                    return $this->error('对不起，您无权操作');
                }


            }

            C2cDeal::cancelLegalDealById($id);
            DB::commit();
            return $this->success('操作成功，订单已取消');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }


    public function legalDealSellerList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        $legal_send = C2cDealSend::find($id);
        if (empty($legal_send)) {
            return $this->error('参数错误2');
        }
        $user_id=Users::getUserId();
        if($user_id !=$legal_send->seller_id){
            return $this->error('对不起，这不是您的发布信息');
        }

        $results = C2cDeal::where('legal_deal_send_id', $id)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->pageData($results);
    }

    public function doSure(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        DB::beginTransaction();
        try {
            $legal_deal = C2cDeal::find($id);
            if (empty($legal_deal)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            if ($legal_deal->is_sure != 3) {
                DB::rollback();
                return $this->error('该订单还未付款或已经操作过');
            }
            $user_id=Users::getUserId();
            $user=Users::find($user_id);
            if($user_id !=$legal_deal->seller_id){
                DB::rollback();
                return $this->error('对不起，您无权操作');
            }

            $legal_send = C2cDealSend::find($legal_deal->legal_deal_send_id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('订单异常');
            }
            if ($legal_send->type == 'buy') {
                DB::rollback();
                return $this->error('您不能确认该订单');
            }
            $user_wallet = UsersWallet::where('user_id', $legal_deal->user_id)->where('currency', $legal_send->currency_id)->first();
            if (empty($user_wallet)) {
                DB::rollback();
                return $this->error('该用户没有此币种钱包');
            }
            $from_wallet = UsersWallet::where('user_id', $legal_deal->seller_id)->where('currency', $legal_send->currency_id)->first();
            if (empty($from_wallet)) {
                DB::rollback();
                return $this->error('该用户没有此币种钱包');
            }

            $data_wallet1 = [
                'balance_type' =>2 ,
                'wallet_id' => $from_wallet->id,
                'lock_type' => 1,
                'create_time' => time(),
                'before' => $from_wallet->lock_legal_balance,
                'change' => -$legal_deal->number,
                'after' => bc_sub($from_wallet->lock_legal_balance,$legal_deal->number,5),
            ];
            $data_wallet2 = [
                'balance_type' =>2 ,
                'wallet_id' => $user_wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' => $user_wallet->legal_balance,
                'change' => $legal_deal->number,
                'after' => bc_add($user_wallet->legal_balance, $legal_deal->number, 5),
            ];
            $legal_deal->is_sure = 1;
            $legal_deal->update_time = time();
            $from_wallet->lock_legal_balance=bc_sub($from_wallet->lock_legal_balance,$legal_deal->number,5);
            $user_wallet->legal_balance = bc_add($user_wallet->legal_balance,$legal_deal->number,5);
            AccountLog::insertLog(
                [
                    'user_id' => $from_wallet->user_id,
                    'value' => $legal_deal->number * (-1),
                    'info' => '出售法币成功,扣除锁定余额',
                    'type' => AccountLog::C2C_USER_BUY,
                    'currency' => $legal_send->currency_id
                ],
                $data_wallet1
            );
            AccountLog::insertLog(
                [
                    'user_id' => $user_wallet->user_id,
                    'value' => $legal_deal->number,
                    'info' => $this->returnStr('在 ') . $user->account_number . $this->returnStr('购买法币成功，增加法币余额'),
                    'type' => AccountLog::C2C_USER_BUY,
                    'currency' => $legal_send->currency_id
                ],
                $data_wallet2
            );

            $legal_deal->save();
            //$seller->save();
            $from_wallet->save();
            $user_wallet->save();
            DB::commit();
            return $this->success('确认成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    public function userDoSure(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        DB::beginTransaction();
        try {
            $legal_deal = C2cDeal::find($id);
            if (empty($legal_deal)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            if ($legal_deal->is_sure != 3) {
                DB::rollback();
                return $this->error('该订单还未付款或已经操作过');
            }
            $user_id = Users::getUserId();
            $user = Users::find($user_id);
            if ($legal_deal->user_id != $user_id) {
                DB::rollback();
                return $this->error('对不起，您无权操作');
            }
            $legal_send = C2cDealSend::find($legal_deal->legal_deal_send_id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('订单异常');
            }
            if ($legal_send->type == 'sell') {
                DB::rollback();
                return $this->error('您不能确认该订单');
            }
            $user_wallet = UsersWallet::where('user_id', $legal_deal->user_id)
                ->where('currency', $legal_send->currency_id)
                ->first();
            if (empty($user_wallet)) {
                DB::rollback();
                return $this->error('该用户没有此币种钱包');
            }

            $seller=Users::find($legal_deal->seller_id);
            $seller_wallet = UsersWallet::where('user_id', $legal_deal->seller_id)
                ->where('currency', $legal_send->currency_id)
                ->first();
            if (empty($seller_wallet)) {
                DB::rollback();
                return $this->error('该买家没有此币种钱包');
            }

            $data_wallet1 = [
                'balance_type' => 2,
                'wallet_id' => $user_wallet->id,
                'lock_type' => 1,
                'create_time' => time(),
                'before' => $user_wallet->lock_legal_balance,
                'change' => -$legal_deal->number,
                'after' => bc_sub($user_wallet->lock_legal_balance, $legal_deal->number, 5),
            ];
            $data_wallet2 = [
                'balance_type' =>2,
                'wallet_id' => $seller_wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' => $seller_wallet->legal_balance,
                'change' => $legal_deal->number,
                'after' => bc_add($seller_wallet->legal_balance,$legal_deal->number,5),
            ];
            $legal_deal->is_sure = 1;
            $legal_deal->update_time = time();

            $user_wallet->lock_legal_balance = bc_sub($user_wallet->lock_legal_balance,$legal_deal->number,5);

              $seller_wallet->legal_balance = bc_add($seller_wallet->legal_balance,$legal_deal->number,5);
            AccountLog::insertLog(
                [
                    'user_id' => $user->id,
                    'value' =>-$legal_deal->number,
                    'info' => $user->account_number . '卖出法币成功',
                    'type' => AccountLog::LEGAL_SELLER_BUY,
                    'currency' => $legal_send->currency_id
                ],
                $data_wallet1
            );
            AccountLog::insertLog(
                [
                    'user_id' => $seller->id,
                    'value' => $legal_deal->number,
                    'info' => $seller->account_number . ' 购买法币成功',
                    'type' => AccountLog::LEGAL_SELLER_BUY,
                    'currency' => $legal_send->currency_id
                ],
                $data_wallet2
            );

            $legal_deal->save();
            $seller_wallet->save();
            $user_wallet->save();
            DB::commit();
            return $this->success('确认成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    public function backSend(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) return $this->error('参数错误');
        DB::beginTransaction();
        try {
            $legal_send = C2cDealSend::lockForUpdate()->find($id);
            if (empty($legal_send)) {
                DB::rollback();
                return $this->error('无此记录');
            }
            $is_deal = C2cDeal::where('legal_deal_send_id', $id)->where('is_sure','>',2)->first();
            return $is_deal;
            if (!empty($is_deal)) {
                DB::rollback();
                return $this->error('该发布信息下有交易产生无法删除');
            }
            $user_id = Users::getUserId();
            if($user_id !=$legal_send->seller_id){
                DB::rollback();
                return $this->error('对不起，您无权撤销此记录');
            }
            

            if ($legal_send->type == 'sell') {
                $wallet=UsersWallet::where('user_id',$user_id)->where('currency',$legal_send->currency_id)->first();
                if(empty($wallet)){
                    DB::rollback();
                    return $this->error('用户钱包不存在');
                   
                }
                if($wallet->lock_legal_balance < $legal_send->total_number){
                    DB::rollback();
                    return $this->error('对不起，您的账户锁定资金不足');
                }
                $data_wallet1 = [
                    'balance_type' =>  2,
                    'wallet_id' => $wallet->id,
                    'lock_type' => 0,
                    'create_time' => time(),
                    'before' => $wallet->legal_balance,
                    'change' => $legal_send->total_number,
                    'after' => bc_add($wallet->legal_balance, $legal_send->total_number, 5),
                ];
                $data_wallet2 = [
                    'balance_type' =>  2,
                    'wallet_id' => $wallet->id,
                    'lock_type' => 1,
                    'create_time' => time(),
                    'before' => $wallet->lock_legal_balance,
                    'change' => -$legal_send->total_number,
                    'after' => bc_sub($wallet->lock_legal_balance, $legal_send->total_number, 5),
                ];

                $wallet->legal_balance = bc_add($wallet->legal_balance, $legal_send->total_number, 5);
                
                $wallet->lock_legal_balance = bc_sub($wallet->lock_legal_balance, $legal_send->total_number, 5);
                $wallet->save();
                // AccountLog::insertLog(['user_id' => $user_id, 'value' => $total_number * -1, 'info' => '用户发布c2c交易法币出售，扣除法币余额', 'type' => AccountLog::C2C_DEAL_SEND_SELL, 'currency' => $currency_id]);
                AccountLog::insertLog(
                    [
                        'user_id' => $user_id,
                        'value' => $legal_send->total_number,
                        'info' => '商家撤回发布法币出售',
                        'type' => AccountLog::C2C_DEAL_BACK_SEND_SELL,
                        'currency' => $legal_send->currency_id
                    ],
                    $data_wallet1
                );
                AccountLog::insertLog(
                    [
                        'user_id' => $user_id,
                        'value' => -$legal_send->total_number,
                        'info' => '商家撤回发布法币出售',
                        'type' => AccountLog::C2C_DEAL_BACK_SEND_SELL,
                        'currency' => $legal_send->currency_id
                    ],
                    $data_wallet2
                );

            }

            $legal_send->delete();
            C2cDeal::where('legal_deal_send_id', $id)->delete();

            DB::commit();
            return $this->success('撤回成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }


}

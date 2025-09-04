<?php

namespace App\Http\Controllers\Api;

use App\UserLevelModel;
use Illuminate\Support\Carbon;
use App\Conversion;
use App\FlashAgainst;
use App\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\Http\Requests;
use App\Currency;
use App\Ltc;
use App\LtcBuy;
use App\TransactionComplete;
use App\NewsCategory;
use App\CoinTrade;
use App\Address;
use App\AccountLog;
use App\Setting;
use App\Users;
use App\MicroOrder;
use App\UsersWallet;
use App\UsersWalletOut;
use App\WalletLog;
use App\Levertolegal;
use App\LeverTransaction;
use App\Jobs\UpdateBalance;
use App\Service\TelegramService;

class WalletController extends Controller
{
    
     public function getRateCurrency(Request $request){
        $id=$request->get('id');
        $price=Currency::where('id',$id)->first()->price??1;
        $rate=Setting::getValueByKey('USDTRate', 6.5);
        $rmb=$price*$rate;
        return $this->success([
            'rmb'=>$rmb
        ]);
    }

    public function getRechargeSetting(){
        $bankaccount=Setting::getValueByKey('recharge_bank_account');
        $bankname=Setting::getValueByKey('recharge_bank_name');
        $openbank=Setting::getValueByKey('recharge_open_bank');
        return $this->success([
            'bank_account'=>$bankaccount,
            'bank_name'=>$bankname,
            'open_bank'=>$openbank,
        ]);
    }
    //我的资产
    public function walletList(Request $request)
    {
        $currency_name = $request->input('currency_name', '');
        $zeroFlag = $request->input('zero_flag', '');
        $user_id = Users::getUserId();
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $profits = [
            'today_profit' => '0.00',
            'all_profit' => '0.00',
            'profit_margin' => '0.00',
        ];
        $profits['today_profit'] = number_format(MicroOrder::where('user_id', $user_id)->whereDate('complete_at', today())->sum('fact_profits'), 2);
        $profits['all_profit'] = number_format(MicroOrder::where('user_id', $user_id)->sum('fact_profits'), 2);
        $profit = MicroOrder::where('user_id', $user_id)->whereDate('complete_at', today())->where('profit_result', '>', 0)->count();
        $allnum = MicroOrder::where('user_id', $user_id)->whereDate('complete_at', today())->count();
        if($profit > 0){
            $profits['profit_margin'] = number_format($profit / $allnum * 100, 2);
        }
        
        $legal_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->where('legal_balance', '>=', $zeroFlag)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                
                //$query->where("is_legal", 1)->where('show_legal', 1);
                $query->where("is_legal", 1);
            })
            // ->orderBy('legal_balance', 'DESC')
            ->get(['id', 'currency', 'legal_balance', 'lock_legal_balance'])
            ->toArray();
            
            

        $legal_wallet['totle'] = 0;
        $legal_wallet['usdt_totle'] = 0;
        foreach ($legal_wallet['balance'] as $k => $v) {
            if(in_array($v['currency'],[3])){
                $legal_wallet['balance'][$k]['is_charge'] = true;
            }else{
                $legal_wallet['balance'][$k]['is_charge'] = false;
            }
            $num = $v['legal_balance'] + $v['lock_legal_balance'];
            //$legal_wallet['totle'] += $num * $v['cny_price'];
            $legal_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }
        
        $legal_wallet['CNY'] = '';
        $change_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->where('change_balance', '>=', $zeroFlag)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
            })
            // ->orderBy('change_balance', 'DESC')
            ->get(['id', 'currency', 'change_balance', 'lock_change_balance'])
            ->toArray();
        $change_wallet['totle'] = 0;
        $change_wallet['usdt_totle'] = 0;
        foreach ($change_wallet['balance'] as $k => $v) {
            if(in_array($v['currency'],[1,2,3])){
                $change_wallet['balance'][$k]['is_charge'] = true;
            }else{
                $change_wallet['balance'][$k]['is_charge'] = false;
            }
            $num = $v['change_balance'] + $v['lock_change_balance'];
           // $change_wallet['totle'] += $num * $v['cny_price'];
            $change_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }
        
        $change_wallet['CNY'] = '';
        $lever_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->where('lever_balance', '>=', $zeroFlag)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_lever", 1);
            })
            // ->orderBy('lever_balance', 'DESC')
            ->get(['id', 'currency', 'lever_balance', 'lock_lever_balance'])->toArray();
        $lever_wallet['totle'] = 0;
        $lever_wallet['usdt_totle'] = 0;
        foreach ($lever_wallet['balance'] as $k => $v) {
            if(in_array($v['currency'],[])){
                $lever_wallet['balance'][$k]['is_charge'] = true;
            }else{
                $lever_wallet['balance'][$k]['is_charge'] = false;
            }
            $num = $v['lever_balance'] + $v['lock_lever_balance'];
            $lever_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }
        
        $lever_wallet['CNY'] = '';

        $micro_wallet['CNY'] = '';
        $micro_wallet['totle'] = 0;
        $micro_wallet['usdt_totle'] = 0;
        $micro_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->where('micro_balance', '>=', $zeroFlag)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_micro", 1);
            })
            // ->orderBy('micro_balance', 'DESC')
            ->get(['id', 'currency', 'micro_balance', 'lock_micro_balance'])
            ->toArray();
        foreach ($micro_wallet['balance'] as $k => $v) {
            if(in_array($v['currency'],[1,2,3,6,10,29])){
                $micro_wallet['balance'][$k]['is_charge'] = true;
            }else{
                $micro_wallet['balance'][$k]['is_charge'] = false;
            }
            $num = $v['micro_balance'] + $v['lock_micro_balance'];
           // $micro_wallet['totle'] += $num * $v['cny_price'];
            $micro_wallet['usdt_totle'] += $num * $v['usdt_price'];
        }
        $ExRate = Setting::getValueByKey('USDTRate', 6.5);

        //读取是否开启充提币
        $is_open_CTbi = Setting::where("key", "=", "is_open_CTbi")->first()->value;
        return $this->success([
            'legal_wallet' => $legal_wallet,
            'change_wallet' => $change_wallet,
            'micro_wallet' => $micro_wallet,
            'lever_wallet' => $lever_wallet,
            'ExRate' => $ExRate,
            "is_open_CTbi" => $is_open_CTbi,
            "profits" => $profits
        ]);
    }


    public function currencyList()
    {
        $user_id = Users::getUserId();
        $currency = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        if (empty($currency)) {
            return $this->error("暂时还没有添加币种");
        }
        foreach ($currency as $k => $c) {
            $w = Address::where("user_id", $user_id)->where("currency", $c['id'])->count();
            $currency[$k]['has_address_num'] = $w; 
        }
        return $this->success($currency);
    }

    public function addAddress()
    {
        $user_id = Users::getUserId();
        $id = Input::get("currency_id", '');
        $address = Input::get("address", "");
        $notes = Input::get("notes", "");
        if (empty($user_id) || empty($id) || empty($address)) {
            return $this->error("参数错误");
        }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error("用户未找到");
        }
        $currency = Currency::find($id);
        if (empty($currency)) {
            return $this->error("此币种不存在");
        }
        $has = Address::where("user_id", $user_id)->where("currency", $id)->where('address', $address)->first();
        if ($has) {
            return $this->error("已经有此提币地址");
        }
        try {
            $currency_address = new Address();
            $currency_address->address = $address;
            $currency_address->notes = $notes;
            $currency_address->user_id = $user_id;
            $currency_address->currency = $id;
            $currency_address->save();
            return $this->success("添加提币地址成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function addressDel()
    {
        $user_id = Users::getUserId();
        $address_id = Input::get("address_id", '');

        if (empty($user_id) || empty($address_id)) {
            return $this->error("参数错误");
        }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error("用户未找到");
        }
        $address = Address::find($address_id);

        if (empty($address)) {
            return $this->error("此提币地址不存在");
        }
        if ($address->user_id != $user_id) {
            return $this->error("您没有权限删除此地址");
        }

        try {
            $address->delete();
            return $this->success("删除提币地址成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
	public function chargeReq(){
		$user_id = Users::getUserId();

        $currency_id = xssCode(Input::get("currency", ''));
        $type = xssCode(Input::get("type", ''));
        $account = Input::get("account", '');
        $amount = xssCode(Input::get("amount",0));
        $sub_type = Input::get("sub_type", '');
        $address = Input::get("address", '');
        
        $receivingBankCard = null;
        if(empty($currency_id) || empty($amount)) {
        	return $this->error('参数错误1');
        }
        if($type == 0){
            $currency = Db::table('currency')->where('id',$currency_id)->first();
            if(!$currency) {
            		return $this->error('参数错误2');
            }
        }else{
            $receivingBankCard = Db::table('receiving_bank_card')->where('id',$currency_id)->first();
            if(!$receivingBankCard) {
            	return $this->error('参数错误2');
            }
        }
        
        $user = Users::find($user_id);
        
        if (empty($user)){
            return $this->error('用户不存在');
        }
        $nick_name = '';
        if (empty($user['email'])){
            $nick_name = $user['phone'];
        }else{
            $nick_name = $user['email'];
        }
        $userLevel = $user['user_level'] > 0 ? UserLevelModel::find($user['user_level']) : null;
      
        if($type == 1){
            $give = $receivingBankCard -> commissions ? round(($amount * $receivingBankCard -> commissions / 100),8) : 0;
            $give_rate = $receivingBankCard -> commissions ? $receivingBankCard -> commissions : 0;
        
            $data = [
                'type'=>$type,
            	'uid' => $user_id,
            	'currency_id' => $currency_id,
            	'amount' => $amount,
            	'give' => $give,
            	'account_name' => $nick_name,
            	'give_rate' => $give_rate,
            	'user_account' => $account,
            	'status' => 1,
            	'bank_user_name' => $receivingBankCard -> account_name,
            	'iban' => $receivingBankCard -> iban,
            	'beneficiary_country' => $receivingBankCard -> beneficiary_country,
            	'bank_code' => $receivingBankCard -> bank_code,
            	'bank_name' => $receivingBankCard -> bank_name,
            	'bank_address' => $receivingBankCard -> bank_address,
            	'currency_name' => $receivingBankCard -> currency_name,
            	'pay_way_name' => $receivingBankCard -> pay_way_name,
            	'created_at' => date('Y-m-d H:i:s')
        	];
            Db::table('charge_req')->insert($data);
            $message = "🎉🎉🎉<b>充值通知：</b>\n<b>会员账号：</b>{$user_id} [{$nick_name}]\n<b>充值金额：</b>{$amount} {$receivingBankCard->currency_name}\n<b>赠送金额：</b>{$give} {$receivingBankCard->currency_name}\n<b>银行信息：</b>\n{$receivingBankCard->account_name}\n{$receivingBankCard->bank_name}\n{$receivingBankCard->bank_address}";
        }else{
            $give = $userLevel ? round(($amount * $userLevel['give'] / 100),8) : 0;
            $give_rate = $userLevel ? $userLevel['give'] : 0;
            $data = [
                'type'=>$type,
            	'uid' => $user_id,
            	'currency_id' => $currency_id,
            	'amount' => $amount,
            	'give' => 0,
            	'account_name' => $nick_name,
            	'give_rate' => 0,
            	'user_account' => $account,
            	'currency_name' => $currency -> name,
            	'sub_type' => $sub_type,
            	'address' => $address,
            	'status' => 1,
            	'created_at' => date('Y-m-d H:i:s')
        	];
            Db::table('charge_req')->insert($data);
            $message = "🎉🎉🎉<b>充值通知：</b>\n<b>会员账号：</b>{$user_id} [{$nick_name}]\n<b>充值金额：</b>{$amount} {$currency->name}\n<b>赠送金额：</b>{$give} {$currency->name}\n<b>充值地址：</b>{$address}";
        }
        // TelegramService::sendMessage($message);
        return $this->success('申请成功');
	}

    public function hasLeverTrade($user_id)
    {
        $exist_close_trade = LeverTransaction::where('user_id', $user_id)
            ->whereNotIn('status', [LeverTransaction::CLOSED, LeverTransaction::CANCEL])
            ->count();
        return $exist_close_trade > 0 ? true : false;
    }


    private $fromArr = [
        'legal' => AccountLog::WALLET_LEGAL_OUT,
        'lever' => AccountLog::WALLET_LEVER_OUT,
        'micro' => AccountLog::WALLET_MCIRO_OUT,
        'change' => AccountLog::WALLET_CHANGE_OUT,
    ];
    private $toArr = [
        'legal' => AccountLog::WALLET_LEGAL_IN,
        'lever' => AccountLog::WALLET_LEVER_IN,
        'micro' => AccountLog::WALLET_MCIRO_IN,
        'change' => AccountLog::WALLET_CHANGE_IN,
    ];
    private $mome = [
        'legal' => 'c2c',
        'lever' => '合约',
        'micro' => '期权',
        'change' => '闪兑',
    ];

    public function changeWallet(Request $request)  //BY tian
    {
        $type = [
            'legal' => 1,
            'lever' => 3,
            'micro' => 4,
            'change' => 2,
        ];
        $user_id = Users::getUserId();
        $currency_id = Input::get("currency_id", '');
        $number = Input::get("number", '');

        $user = Users::find($user_id);
        if($user->frozen_funds==1){
            return $this->error('资金已冻结');
        }
        $from_field = $request->get('from_field', ""); 
        $to_field = $request->get('to_field', ""); 
        if (empty($from_field) || empty($number) || empty($to_field) || empty($currency_id)) {
            return $this->error('参数错误');
        }
        if ($number < 0) {
            return $this->error('输入的金额不能为负数');
        }
        $from_account_log_type = $this->fromArr[$from_field];
        $to_account_log_type =  $this->toArr[$to_field];
        $memo = $this->mome[$from_field] . '划转' . $this->mome[$to_field];
        if ($from_field == 'lever') {
            if ($this->hasLeverTrade($user_id)) {
                return $this->error('您有正在进行中的杆杠交易,不能进行此操作');
            }
        }
        try {
            DB::beginTransaction();
            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency_id)
                ->first();
            if (!$user_wallet) {
                throw new \Exception('钱包不存在');
            }
            $result = change_wallet_balance($user_wallet, $type[$from_field], -$number, $from_account_log_type, $memo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            $result = change_wallet_balance($user_wallet, $type[$to_field], $number, $to_account_log_type, $memo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('划转成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('操作失败:' . $e->getMessage());
        }
    }

    public function hzhistory(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 10);

        $arr = [
            AccountLog::WALLET_LEGAL_OUT,
            AccountLog::WALLET_LEVER_OUT,
            AccountLog::WALLET_MCIRO_OUT,
            AccountLog::WALLET_CHANGE_OUT,
            AccountLog::WALLET_LEGAL_IN,
            AccountLog::WALLET_LEVER_IN,
            AccountLog::WALLET_MCIRO_IN,
            AccountLog::WALLET_CHANGE_IN,
        ];
        $result = AccountLog::where('user_id',$user_id)->whereIn('type', $arr)->orderBy('id', 'desc')->paginate($limit);
        return $this->success($result);
        
    }
    public function getCurrencyInfo()
    {
        $user_id = Users::getUserId();
        $currency_id = Input::get("currency", '');
        if (empty($currency_id)) return $this->error('参数错误');
        $currencyInfo = Currency::find($currency_id);
        if (empty($currencyInfo)) return $this->error('币种不存在');
        $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first();
        $data = [
            'rate' => $currencyInfo->rate,
            'ratetype' => $currencyInfo->ratetype,
            'min_number' => $currencyInfo->min_number,
            'name' => $currencyInfo->name,
            'legal_balance' => $wallet->legal_balance,
            'change_balance' => $wallet->change_balance,
            'micro_balance' => $wallet->micro_balance
        ];
        return $this->success($data);
    }

    public function getAddressByCurrency()
    {
        $user_id = Users::getUserId();
        $currency_id = Input::get("currency", '');
        if (empty($user_id) || empty($currency_id)) {
            return $this->error('参数错误');
        }
        $address = Address::where('user_id', $user_id)->where('currency', $currency_id)->get()->toArray();
        if (empty($address)) {
            return $this->error('您还没有添加提币地址');
        }
        return $this->success($address);
    }

    public function postWalletOut()
    {
        $user_id = Users::getUserId();
        $type = Input::get("type", '');
        $currency_id = Input::get("currency", '');
        $number = Input::get("number", '');
        $rate = Input::get("rate", '');
        $address = Input::get("address", '');
        $password = Input::get('pay_password');
        $users_wallet_withdraw_id = Input::get('users_wallet_withdraw_id');

        if (empty($currency_id) || empty($number) ) {
            return $this->error('参数错误');
        }

        $userWalletWithDraw = DB::table('users_wallet_withdraw')
            ->where("user_id", $user_id)
            ->where("id", $users_wallet_withdraw_id)
            ->first();
        if (!$userWalletWithDraw) {
            return $this->error('地址不存在');
        }
        $address = $userWalletWithDraw->address;


        switch ($currency_id) {
                //BTC
                // case '1':
                //     if (!(preg_match('/^(1|3)[a-zA-Z\d]{24,33}$/', $address) && preg_match('/^[^0OlI]{25,34}$/', $address))) {
                //         return $this->error('参数错误');
                //     }
                //     break;
                // //ETH
                // case '2':

                //     if (!(preg_match('/^(0x)?[0-9a-fA-F]{40}$/', $address))) {
                //          return $this->error('参数错误');
                //     }
                //     break;
        }
        if ($number < 0) {
            return $this->error('输入的金额不能为负数');
        }
        $user = Users::getById(Users::getUserId());
        $payPassword = Users::MakePassword($password, $user->type);

        $zkRadio = Setting::getValueByKey('tk_radio', '');

        if ($zkRadio == 1) {
            if ($payPassword != $user->pay_password) return $this->error('支付密码错误');
        }
        if ($user->frozen_funds == 1) {
            return $this->error('资金已冻结');
        }
        $currencyInfo = Currency::find($currency_id);
        if ($number < $currencyInfo->min_number) {
            return $this->error('数量不能少于最小值');
        }
        if($currencyInfo->ratetype){
            $rate = $number * $currencyInfo->rate;
        }else{
            $rate = $currencyInfo->rate;
        }
        $user_name = $user['email'];
        if (empty($user_name)) {
            $user_name =  $user['phone'];
        }
        
        //扣款钱包
        $cashb_balance_type = Setting::getValueByKey('cashb_balance_type', 2);
        
        try {
            DB::beginTransaction();
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->lockForUpdate()->first();
            $fields = [
                '',
                'legal_balance',
                'change_balance',
                'lever_balance',
                'micro_balance',
            ];
            $field = $fields[$cashb_balance_type];
            if ($number > $wallet->$field) {
                DB::rollBack();
                return $this->error('余额不足');
            }
            
            $real_number = $number  - $rate;
            $walletOut = new UsersWalletOut();
            $walletOut->type = $type;
            $walletOut->user_id = $user_id;
            $walletOut->currency = $currency_id;
            $walletOut->number = $number;
            $walletOut->address = $address;
            $walletOut->user_name = $user_name;
            $walletOut->rate = $rate;
            $walletOut->real_number = $real_number;
            $walletOut->create_time = time();
            $walletOut->status = 1;
            $walletOut->save();

            $result = change_wallet_balance($wallet, $cashb_balance_type, -$number, AccountLog::WALLETOUT, '申请提币扣除余额');
            if ($result !== true) {
                throw new \Exception($result);
            }

            $result = change_wallet_balance($wallet, $cashb_balance_type, $number, AccountLog::WALLETOUT, '申请提币锁定余额', true);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            $message = "❗️❗️❗️<b>提款通知：</b>\n<b>会员账号：</b>{$user_id} [{$user_name}]\n<b>提款金额：</b>{$number} {$currencyInfo->name}\n<b>手续费：</b>{$rate} {$currencyInfo->name}\n<b>到账金额：</b>{$real_number} {$currencyInfo->name}\n<b>提款地址：</b>{$address}\n";
            // TelegramService::sendMessage($message);
            return $this->success('提币申请已成功，等待审核');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function getWalletAddressIn()
    {
        $user_id = Users::getUserId();

        $currency_id = Input::get("currency", '');
        if (empty($user_id) || empty($currency_id)) {
            return $this->error('参数错误');
        }
        $currencyInfo = Currency::find($currency_id);
        if(!$currencyInfo){
        	 return $this->error('参数错误');
        }
        $legal = UsersWallet::where("user_id", $user_id)
            ->where("currency", $currency_id) //usdt
            ->first();
        if($currency_id==3){//usdt充值地址
            $address=[
                'erc20'=>$currencyInfo->address_erc ?? '', //erc20
                "trc20"=>$currencyInfo->address_omni ?? '' //trc20
                ];
        }else{
            $address=$currencyInfo->address_erc ?? '';
        }
        return $this->success($address);
    }
 
    public function getWalletDetail()
    {
        $user_id = Users::getUserId();
        $currency_id = Input::get("currency", '');
        $type = Input::get("type", '');
        if (empty($user_id) || empty($currency_id)) {
            return $this->error('参数错误');
        }
        $CoinTrade_data = CoinTrade::where('u_id', $user_id)->where('currency_id', $currency_id)->get();
        $CoinTrade_amount = $trade_amount = 0;
        foreach($CoinTrade_data as $item){
            if($item['type'] == 1){
                $trade_amount += $item['trade_amount'];
                $CoinTrade_amount += ($item['target_price']*$item['trade_amount']);
            }else{
                $trade_amount -= $item['trade_amount'];
                $CoinTrade_amount -= ($item['target_price']*$item['trade_amount']);
            }
        }
        
        $ExRate = Setting::getValueByKey('USDTRate', 6.5);
        if ($type == 'legal') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'legal_balance', 'lock_legal_balance','address']);
        } else if ($type == 'change') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'change_balance', 'lock_change_balance','address']);
            
        } else if ($type == 'lever') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'lever_balance', 'lock_lever_balance','address']);
        } else if ($type == 'micro') {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency_id)->first(['id', 'currency', 'micro_balance', 'lock_micro_balance','address']);
        } else {
            return $this->error('类型错误');
        }
        if (empty($wallet)) return $this->error("钱包未找到");

        $wallet->ExRate = $ExRate;
  
        if(in_array($wallet->currency,[1,2,3])){
            $wallet->is_charge = true;
        }else{
            $wallet->is_charge = false;
        }

        $wallet->profit_or_loss = number_format(($trade_amount * $wallet['usdt_price']) - $CoinTrade_amount,4);
        $wallet->coin_trade_fee = Setting::getValueByKey('COIN_TRADE_FEE');
        return $this->success($wallet);
    }

    public function legalLog(Request $request)
    {
   
        $limit = $request->get('limit', 10);
        $account = $request->get('account', '');
        $currency = $request->get('currency', 0);
        $type= $request->get('type',0);
        $user_id = Users::getUserId();
        $list = new AccountLog();
        if (!empty($currency)) {
            $list = $list->where('currency', $currency);
        }
        if (!empty($user_id)) {
            $list = $list->where('user_id', $user_id);
        }
        if (!empty($type)) {
            $list = $list->whereHas('walletLog',function($query) use($type){
              $query->where('balance_type',$type);
            });
        }
        $list = $list->whereNotIn('type', [21, 24, 26])->orderBy('id', 'desc')->paginate($limit);
        $is_open_CTbi = Setting::where("key", "=", "is_open_CTbi")->first()->value;

        return $this->success(array(
            "list" => $list->items(), 'count' => $list->total(),
            "limit" => $limit,
            "is_open_CTbi" => $is_open_CTbi
        ));
    }

    public function walletOutLog()
    {
        $id = Input::get("id", '');
        $walletOut = UsersWalletOut::find($id);
        return $this->success($walletOut);
    }



    public function getLtcKMB()
    {
        $address = Input::get('address', '');
        $money = Input::get('money', '');
        $wallet = UsersWallet::whereHas('currencyCoin', function ($query) {
            $query->where('name', 'PB');
        })->where('address', $address)->first();
        if (empty($wallet)) {
            return $this->error('钱包不存在');
        }
        DB::beginTransaction();
        try {

            $data_wallet1 = array(
                'balance_type' => 1,
                'wallet_id' => $wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' => $wallet->change_balance,
                'change' => $money,
                'after' => $wallet->change_balance + $money,
            );
            $wallet->change_balance = $wallet->change_balance + $money;
            $wallet->save();
            AccountLog::insertLog([
                'user_id' => $wallet->user_id,
                'value' => $money,
                'currency' => $wallet->currency,
                'info' => '转账来自钱包的余额',
                'type' => AccountLog::LTC_IN,
            ], $data_wallet1);
            DB::commit();
            return $this->success('转账成功');
        } catch (\Exception $rex) {
            DB::rollBack();
            return $this->error($rex);
        }
    }
    public function sendLtcKMB()
    {
        $user_id = Users::getUserId();
        $account_number = Input::get('account_number', '');
        $money = Input::get('money', '');

        if (empty($account_number) || empty($money) || $money < 0) {
            return $this->error('参数错误');
        }
        $wallet = UsersWallet::whereHas('currencyCoin', function ($query) {
            $query->where('name', 'PB');
        })->where('user_id', $user_id)->first();
        if ($wallet->change_balance < $money) {
            return $this->error('余额不足');
        }

        DB::beginTransaction();
        try {

            $data_wallet1 = array(
                'balance_type' => 1,
                'wallet_id' => $wallet->id,
                'lock_type' => 0,
                'create_time' => time(),
                'before' => $wallet->change_balance,
                'change' => $money,
                'after' => $wallet->change_balance - $money,
            );
            $wallet->change_balance = $wallet->change_balance - $money;
            $wallet->save();
            AccountLog::insertLog([
                'user_id' => $wallet->user_id,
                'value' => $money,
                'currency' => $wallet->currency,
                'info' => '转账余额至钱包',
                'type' => AccountLog::LTC_SEND,
            ], $data_wallet1);

            $url = "http://walletapi.bcw.work/api/ltcGet?account_number=" . $account_number . "&money=" . $money;
            $data = RPC::apihttp($url);
            $data = @json_decode($data, true);
            //            var_dump($data);die;
            if ($data["type"] != 'ok') {
                DB::rollBack();
                return $this->error($data["message"]);
            }
            DB::commit();
            return $this->success('转账成功');
        } catch (\Exception $rex) {
            DB::rollBack();
            return $this->error($rex->getMessage());
        }
    }
    public function PB()
    {
        $user_id = Users::getUserId();
        $wallet = UsersWallet::whereHas('currencyCoin', function ($query) {
            $query->where('name', 'PB');
        })->where('user_id', $user_id)->first();
        return $this->success($wallet->change_balance);
    }
    public function flashAgainstList(Request $request)
    {
        $user_id = Users::getUserId();
        $left = Currency::where('is_match', 1)->get();
        foreach ($left as $k => $v) {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $v->id)->first();
            if (empty($wallet)) {
                $balance = 0;
            } else {
                $balance = $wallet->change_balance;
            }
            $v->balance = $balance;
            $left[$k] = $v;
        }
        $right = Currency::where('is_micro', 1)->get();
        foreach ($right as $k => $v) {
            $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $v->id)->first();
            if (empty($wallet)) {
                $balance = 0;
            } else {
                $balance = $wallet->change_balance;
            }
            $v->balance = $balance;
            $right[$k] = $v;
        }
        return $this->success(['left' => $left, 'right' => $right]);
    }

    public function flashAgainst(Request $request)
    {
        try {
            $l_currency_id = $request->get('l_currency_id', "");
            $r_currency_id = $request->get('r_currency_id', "");
            $num = $request->get('num', 0);

            $user_id = Users::getUserId();
            if ($num <= 0) return $this->error('数量不能小于等于0');
            $p = $request->get('price', 0);
            if ($p <= 0) return $this->error('价格不能小于等于0');

            if (empty($l_currency_id) || empty($r_currency_id))  return $this->error('参数错误哦');

            $left = Currency::where('id', $l_currency_id)->first();
            $right = Currency::where('id', $r_currency_id)->first();
            if (empty($left) || empty($right))  return $this->error('币种不存在');

            //$absolute_quantity = $p * $num / $right->price;
            $absolute_quantity = bc_div(bc_mul($p, $num), $right->price);
            DB::beginTransaction();

            $l_wallet = UsersWallet::where('currency', $l_currency_id)->where('user_id',$user_id)->lockForUpdate()->first();
            
            if (empty($l_wallet)){

                throw new \Exception('钱包不存在');
            }  

            if ($l_wallet->change_balance < $num){

                throw new \Exception('金额不足');
            } 

            $flash_against = new FlashAgainst();
            $flash_against->user_id = $user_id;
            $flash_against->price = $p;
            $flash_against->market_price = $left->price;
            $flash_against->num = $num;
            $flash_against->status = 0;
            $flash_against->left_currency_id = $l_currency_id;
            $flash_against->right_currency_id = $r_currency_id;
            $flash_against->create_time = time();
            $flash_against->absolute_quantity = $absolute_quantity; //实际数量
            $result = $flash_against->save();
            $result1=change_wallet_balance($l_wallet, 2, -$num, AccountLog::DEBIT_BALANCE_MINUS, '闪兑扣除余额');
            $result2=change_wallet_balance($l_wallet, 2, $num, AccountLog::DEBIT_BALANCE_ADD_LOCK, '闪兑增加锁定余额', true);
            if($result1 !== true){
                throw new \Exception($result1);
            }
            if ($result2 !== true) {
                throw new \Exception($result2);
            }

            DB::commit();
            return $this->success('兑换成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage() . '---' . $e->getLine());
        }
    }

    public function myFlashAgainstList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = Users::getUserId();
        $list = FlashAgainst::orderBy('id', 'desc')->where('user_id', $user_id)->paginate($limit);
        return $this->success($list);
    }

    public function conversion(Request $request)
    {
        $form_currency_id = $request->get('form_currency', '');
        $to_currency_id = $request->get('to_currency', '');
        $balance_filed = 'legal_balance';
        $num = $request->get('num', '');
        if (empty($form_currency_id) || empty($to_currency_id) || empty($num)) {
            return $this->error('参数错误');
        }
        if($num <= 0){
            return $this->error('兑换数量必须大于0');
        }
        $user_id = Users::getUserId();       
        try {
            DB::beginTransaction();
            $form_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $form_currency_id)->lockForUpdate()->first();
            $to_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $to_currency_id)->lockForUpdate()->first();
            if(empty($form_wallet) || empty($to_wallet)){
                DB::rollBack();
                return $this->error('钱包不存咋');
            }
            if ($form_wallet->$balance_filed < $num) {
                DB::rollBack();
                return $this->error('余额不足');
            }
            if (strtoupper($form_wallet->currency_name) == 'USDT') {
                $fee = Setting::getValueByKey('currency_to_usdt_bmb_fee');
                $proportion = Setting::getValueByKey('currency_to_usdt_bmb');
            } elseif (strtoupper($form_wallet->currency_name) == UsersWallet::CURRENCY_DEFAULT) {
                $fee = Setting::getValueByKey('currency_to_bmb_usdt_fee');
                $proportion = Setting::getValueByKey('currency_to_bmb_usdt');
            }
            $totle_num_fee =bc_mul($num,$fee / 100);
            $totle_num = bc_sub($num,$totle_num_fee);
            $totle_num_sj = $proportion * $totle_num;


            $res1=change_wallet_balance($form_wallet, 1, -$totle_num, AccountLog::WALLET_USDT_MINUS, $form_wallet->currency_name . '兑换，' . $to_wallet->currency_name . ',减少' . $form_wallet->currency_name . $totle_num);

            $res2=change_wallet_balance($form_wallet, 1, -$totle_num_fee, AccountLog::WALLET_USDT_BMB_FEE,  $form_wallet->currency_name . '兑换，' . $to_wallet->currency_name . ',减少' . $form_wallet->currency_name . '手续费' . $totle_num_fee);

            $res3=change_wallet_balance($to_wallet, 1, $totle_num_sj, AccountLog::WALLET_BMB_ADD,     $form_wallet->currency_name . '兑换，' . $to_wallet->currency_name . ',增加' . $to_wallet->currency_name . $totle_num_sj);
            if($res1 !== true ){
                DB::rollBack();
                return $this->error($res1);
            }
            if($res2 !== true ){
                DB::rollBack();
                return $this->error($res2);
            }
            if($res3 !== true){
                DB::rollBack();
                return $this->error($res3);
            }

            $conversion = new Conversion();
            $conversion->user_id = $user_id;
            $conversion->create_time = time();
            $conversion->form_currency_id = $form_currency_id;
            $conversion->to_currency_id = $to_currency_id;
            $conversion->num = $num;
            $conversion->fee = $totle_num_fee;
            $conversion->sj_num = $totle_num_sj;
            $conversion->save();
            DB::commit();
            return $this->success('兑换成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function myConversion(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->get('limit', 10);
        $list = Conversion::orderBy('id', 'desc')->where('user_id', $user_id)->paginate($limit);
        return $this->success($list);
    }

    public function conversionList()
    {
        $currency = Currency::where('name', 'USDT')->orWhere('name', UsersWallet::CURRENCY_DEFAULT)->get();
        return $this->success($currency);
    }
    public function conversionSet()
    {
        $fee = Setting::getValueByKey('currency_to_usdt_bmb_fee');
        $proportion = Setting::getValueByKey('currency_to_usdt_bmb');
        $data['usdt_bmb_fee'] = $fee;
        $data['usdt_bmb_proportion'] = $proportion;
        $fee1 = Setting::getValueByKey('currency_to_bmb_usdt_fee');
        $proportion1 = Setting::getValueByKey('currency_to_bmb_usdt');
        $data['bmb_usdt_fee'] = $fee1;
        $data['bmb_usdt_proportion'] = $proportion1;
        $usdt = Currency::where('name', 'USDT')->first();
        $bmb = Currency::where('name', UsersWallet::CURRENCY_DEFAULT)->first();
        $user_id = Users::getUserId();
        $balance_filed = 'legal_balance';
        $usdt_wallet = UsersWallet::where('currency', $usdt->id)->where('user_id', $user_id)->first();
        $data['user_balance'] = $usdt_wallet->$balance_filed;
        $bmb_wallet = UsersWallet::where('currency', $bmb->id)->where('user_id', $user_id)->first();
        $data['bmb_balance'] = $bmb_wallet->$balance_filed;
        return $this->success($data);
    }

    //持险生币
    public function Insurancemoney()
    {

        $user_id = Users::getUserId();
        $wallet = UsersWallet::where('lock_insurance_balance', '>', 0)->where('user_id', $user_id)->first();
        $data = [];

        $data['insurance_balance'] = $wallet->insurance_balance ?? 0;

        $data['lock_insurance_balance'] = $wallet->lock_insurance_balance ?? 0;
        //累计生币
        $data['sum_balance'] = AccountLog::where('user_id', $user_id)->where('type', AccountLog::INSURANCE_MONEY)->sum('value');
        //可用数量
        $data['usabled_balance'] = 0;

        return $this->success($data);
    }

    //持险生币日志
    public function Insurancemoneylogs()
    {

        $user_id = Users::getUserId();
        $limit = Input::get('limit', 10);

        $result = AccountLog::where('user_id', $user_id)->where('type', AccountLog::INSURANCE_MONEY)->orderBy('id', 'desc')->paginate($limit);

        return $this->success($result);
    }
}

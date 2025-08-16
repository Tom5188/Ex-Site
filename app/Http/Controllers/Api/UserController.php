<?php

namespace App\Http\Controllers\Api;
use App\UserAlgebra;
use App\UserLevelModel;
use App\UsersWalletWithdraw;
use Illuminate\Support\Facades\App;
use App\UserCashInfo;
use App\UserCashInfoInternational;
use Illuminate\Http\Request;
use Session;
use App\UserChat;
use App\Users;
use App\UserReal;
use App\Token;
use App\AccountLog;
use App\UsersWallet;
use App\UsersWalletcopy;
use App\Bank;
use App\IdCardIdentity;
use App\Currency;
use App\InviteBg;
use App\Setting;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\DAO\UserDAO;
use App\Seller;
use App\CurrencyQuotation;
use App\Service\RedisService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use function foo\func;

//use App\{Users, AccountLog};

class UserController extends Controller
{
    //根据当前价格计算汇率 手续费参考值
    public function getTransferFee(Request $request){
         

        $from_currency_id  = $request->get('from_currency_id',''); //被划转币种
        $to_currency_id  = $request->get('to_currency_id','');//划转目标币种
        if(!$from_currency_id || !$to_currency_id){
            return $this->error('error, Parameter is empty!');
        }
        $from_currency = Currency::where('id',$from_currency_id)->where('is_transfer',1)->first();
        $to_currency = Currency::where('id',$to_currency_id)->where('is_transfer',1)->first();
        $transfer_fee = Setting::getValueByKey('transfer_fee');
        
        if(!$to_currency){
            return $this->error('This currency ['.$to_currency.'] does not support transfers'); 
        }
        if(!$from_currency){
            return $this->error('This currency ['.$from_currency.'] does not support transfers'); 
        }
        
        $data =[
            'form_price'=>$from_currency->price,
            'to_price'=>$to_currency->price,
            'fee'=>$transfer_fee,
            ];
            
        return $this->success($data);
    }
    
    
    
    //获取可划转的币种
    public function getTransferList(){
        $user_id = Users::getUserId();
        if(!$user_id){
            return $this->error('error!'); 
        }
        
        $get_transfer_list = Currency::where('is_transfer',1)->get(['id','name'])->toArray();
        UsersWallet::leftjoin("currency", "currency.id", "users_wallet.currency")
            ->where("users_wallet.user_id", $user_id)
            ->where("currency.is_transfer", 1)
            ->select();
        $user_wallet = UsersWallet::where("user_id","=",$user_id)->get(['currency','change_balance'])->toArray();
        
        return $this->success(['transfer'=>$get_transfer_list??[],'wallet'=>$user_wallet]);
    }
    
    //币币划转
    public function Transfer(Request $request){
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if($user->frozen_funds==1){
            return $this->error('资金已冻结');
        }
        $from_currency_id  = $request->get('from_currency_id',''); //被划转币种
        $to_currency_id  = $request->get('to_currency_id','');//划转目标币种
        $number = $request->get('number',0);//划转数量
        if(Cache::has("on_the_way_$user_id")){
            return $this->error('Do not repeat the operation!'); 
        }
        Cache::put("on_the_way_$user_id", 1, Carbon::now()->addSeconds(3));
        if(!$from_currency_id || !$to_currency_id|| !$number || !$user_id){
            return $this->error('error, Parameter is empty!'); 
        }
        
        if($from_currency_id==$to_currency_id){
            return $this->error('The same currency cannot be transferred!');  //相同币种不可划转
        }
        $from_currency = Currency::where('id',$from_currency_id)->where('is_transfer',1)->first();
        $to_currency = Currency::where('id',$to_currency_id)->where('is_transfer',1)->first();
        $from_user_walllet_currency=UsersWallet::where("user_id","=",$user_id)->where("currency","=",$from_currency_id)->first();
        $to_user_walllet_currency=UsersWallet::where("user_id","=",$user_id)->where("currency","=",$to_currency_id)->first();
        if(!$from_user_walllet_currency || !$to_user_walllet_currency){
            return $this->error('User wallet does not exist'); 
        }
        if(!$to_currency){
            return $this->error('This currency ['.$to_currency.'] does not support transfers'); 
        }
        if(!$from_currency){
            return $this->error('This currency ['.$from_currency.'] does not support transfers'); 
        }
        
        
        $f_new_price = $from_currency['price'];//最新币种
        $t_new_price = $to_currency['price'];//最新币种
        $transfer_fee = Setting::getValueByKey('transfer_fee',0);//获取手续费比例
        $form_amount  = bc_mul($f_new_price ,$number);
        $to_amount = bc_div($form_amount, $t_new_price);
        if($transfer_fee<=0){
            $transfer_money = 0;
        }else{
            $transfer_money = bc_div(bc_div(bc_mul($transfer_fee,$form_amount),100),$f_new_price);
        }
        
        if($to_amount<=0 || $number<=0){
            return $this->error('Numerical error!'); 
        }
        if($transfer_money<0){
            $transfer_money = 0;
        }
        
        DB::beginTransaction();
        try{
            change_wallet_balance($from_user_walllet_currency , 2 , -$number , AccountLog::USER_EXCHANGE_DOWN,'划转减少' . $from_user_walllet_currency->name);
            change_wallet_balance($to_user_walllet_currency , 2 , $to_amount , AccountLog::USER_EXCHANGE_ADD,'划转增加' . $to_user_walllet_currency->name);
            change_wallet_balance($from_user_walllet_currency , 2 , -$transfer_money ,AccountLog::USER_EXCHANGE_FEE, '手续费' . $from_user_walllet_currency->name); //从被划转的币种扣除
            DB::commit();
            return $this->success('资产兑换成功!');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
        
        
    }
    
    public function getUserChargeAmount(Request $request){
         $key = $request->header('key');
         if($key != 'scdsfajcl123@$a'){
            return $this->error('非法请求');
         }
         $user_id = $request->get('user_id');
         
         $time = $request->get('starttime');
         if(!$time || !$user_id){
             return $this->error('缺少参数');
         }
         $sum = AccountLog::where('user_id',$user_id)->where('currency',31)->where('created_time','>',$time)->where('type',AccountLog::WALLET_CURRENCY_IN)->sum('value');
         return $this->success(['total_charge'=>$sum]);
    }
    
    public function addCollection(Request $request){
        $array = $request->get('value','');
        if(!$array){
            return $this->error('参数异常');
        }
        $array = (string)$array;
        $user_id = Users::getUserId();
        $key = 'User_Collect_'.$user_id;
        $redis = RedisService::getInstance(3);
        if(!$redis->sIsMember($key,$array)){
            $redis->sAdd($key,$array);
        }
        return $this->success('添加成功');
    }
    public function getCollection(Request $request){

        $user_id = Users::getUserId();
        $key = 'User_Collect_'.$user_id;
        $redis = RedisService::getInstance(3);
        $res = $redis->sMembers($key);
        return $this->success($res);
    }
    public function deleteCollection(Request $request){
        $array = $request->get('value','');
        if(!$array){
            return $this->error('参数异常');
        }
        $user_id = Users::getUserId();
        $key = 'User_Collect_'.$user_id;
        $redis = RedisService::getInstance(3);
        $redis->sRem($key,$array);
        return $this->success('');
    }

    public function rechargeLog(Request $request)
    {
        $user_id = Users::getUserId();
        $page =  $request->get('page',1);
        $limit = $request->get('limit',10);
        $lists = DB::table('charge_req')
            ->join('currency', 'currency.id', '=', 'charge_req.currency_id')
            ->where('charge_req.uid',$user_id)
            ->select('charge_req.*', 'currency.name')
            ->orderBy('charge_req.id', 'desc')
            ->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }

    public function realState(Request $request)
    {
        $user_id = Users::getUserId();
        $real_status = 0;
        $review_status = 0;
        $advanced_review_status = 0;
        
        $real_data = DB::table('user_real')->where('user_id',$user_id)
            ->first();

        if (!empty($real_data)){
            if ($real_data->review_status == 1){
                $real_status = 1;
                $review_status = 1;
            }
            if ($real_data->review_status == 2 && $real_data->advanced_user == null){
                $real_status = 1;
                $review_status = 2;
            }
            if ($real_data->review_status == 2 && $real_data->advanced_user == 1){
                $real_status = 2;
                $review_status = 2;
                $advanced_review_status = 1;
            }
            if ($real_data->advanced_user == 2){
                $real_status = 2;
                $review_status = 2;
                $advanced_review_status = 2;
            }
        }

        $result = compact('real_status','review_status','advanced_review_status',"real_data");
        return $this->success($result);
    }
    
    public function saveUserReal(Request $request)
    {
        $user_id = Users::getUserId();

        $id_type = $request->post('id_type', 0); // 0身份证 1护照 2驾驶证
        $country = $request->post('country', ''); // 0身份证 1护照 2驾驶证

        // 接受参数
        $real_type = $request->post('real_type'); // 1 初级认证  2 高级认证
        if (!in_array($real_type,[1,2])){
            return $this->error('认证类型错误');
        }
        $name = $request->post('name');
        $card_id = $request->post('card_id');
        $front_pic = $request->post('front_pic');
        $reverse_pic = $request->post('reverse_pic');

        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        if ($real_type == 1){
            if (empty($name) || empty($card_id)){
                return $this->error('请填写完整信息');
            }
            //校验  身份证号码合法性
            // $idcheck = new IdCardIdentity();
            // $res = $idcheck->check_id($card_id);
            // if (!$res) {
            //     return $this->error("请输入合法的身份证号码");
            // }
            
            $userreal_number = UserReal::where("card_id",$card_id)->count();
            if($userreal_number>0)
            {
                return $this->error("该身份证号已实名认证过!");
            }
            $real = UserReal::where('user_id',$user_id)->first();
            if ($real){
                return $this->error('已经审核过认证~');
            }
            $real = new UserReal;
            $real->id_type = $id_type;
            $real->name = $name;
            $real->country = $country;
            $real->user_id = $user_id;
            $real->card_id = $card_id;
            $real->front_pic = $front_pic;
            $real->reverse_pic = $reverse_pic;
            $real->create_time = time();
            $real->save();
        }else{
            if (empty($front_pic) || empty($reverse_pic)){
                return $this->error('请填写完整信息');
            }
            $real = UserReal::where('user_id',$user_id)->first();
            if (empty($real)){
                return $this->error('请先完成初级认证');
            }
            if ($real->review_status != 2){
                return $this->error('初级认证审核中，请耐心等待');
            }
            $real->front_pic = $front_pic;
            $real->reverse_pic = $reverse_pic;
            $real->advanced_user = 1;
            $real->save();
        }
        return $this->success('认证成功，请等待审核');
    }

    // public function saveUserReal(Request $request)
    // {
    //     $user_id = Users::getUserId();

    //     $id_type = $request->post('id_type', 0); // 0身份证 1护照 2驾驶证
    //     $country = $request->post('country', ''); // 0身份证 1护照 2驾驶证

    //     // 接受参数
    //     $real_type = $request->post('real_type'); // 1 初级认证  2 高级认证
    //     if (!in_array($real_type,[1,2])){
    //         return $this->error('认证类型错误');
    //     }
    //     $name = $request->post('name');
    //     $card_id = $request->post('card_id');
    //     $front_pic = $request->post('front_pic');
    //     $reverse_pic = $request->post('reverse_pic');

    //     $user = Users::find($user_id);
    //     if (empty($user)) {
    //         return $this->error("会员未找到");
    //     }
    //     if ($real_type == 1){
    //         if (empty($name) || empty($card_id)){
    //             return $this->error('请填写完整信息');
    //         }
    //         //校验  身份证号码合法性
    //         // $idcheck = new IdCardIdentity();
    //         // $res = $idcheck->check_id($card_id);
    //         // if (!$res) {
    //         //     return $this->error("请输入合法的身份证号码");
    //         // }
            
    //         $userreal_number = UserReal::where("card_id",$card_id)->count();
    //         if($userreal_number>0)
    //         {
    //             return $this->error("该身份证号已实名认证过!");
    //         }
    //         $real = UserReal::where('user_id',$user_id)->first();
    //         if ($real){
    //             return $this->error('已经审核过认证~');
    //         }
    //         $real = new UserReal;
    //         $real->id_type = $id_type;
    //         $real->name = $name;
    //         $real->country = $country;
    //         $real->user_id = $user_id;
    //         $real->card_id = $card_id;
    //         $real->create_time = time();
    //         $real->save();
    //     }else{
    //         if (empty($front_pic) || empty($reverse_pic)){
    //             return $this->error('请填写完整信息');
    //         }
    //         $real = UserReal::where('user_id',$user_id)->first();
    //         if (empty($real)){
    //             return $this->error('请先完成初级认证');
    //         }
    //         if ($real->review_status != 2){
    //             return $this->error('初级认证审核中，请耐心等待');
    //         }
    //         $real->front_pic = $front_pic;
    //         $real->reverse_pic = $reverse_pic;
    //         $real->advanced_user = 1;
    //         $real->save();
    //     }
    //     return $this->success('认证成功，请等待审核');
    // }

    public function userWalletSave(Request $request)
    {
        // 两种模式    给我传id  就是  修改     不给我传id  就是  新增
        $user_id = Users::getUserId();
        // 接受参数
        $wallet_id = $request->post('id');
        $currency = $request->post('currency');
        $address = $request->post('address');
        $qrcode = $request->post('qrcode');
        $type = $request->post('type');
        if (empty($currency) || empty($address) || empty($type)){
            return $this->error('请完善钱包信息');
        }
        if ($wallet_id){
            $wallet = UsersWalletWithdraw::where('id',$wallet_id)->first();
        }else{
            $wallet = new UsersWalletWithdraw();
        }
        $wallet->user_id = $user_id;
        $wallet->currency = $currency;
        $wallet->address = $address;
        $wallet->qrcode = $qrcode;
        $wallet->type = $type;
        $wallet->save();
        $msg = $wallet_id ? '修改成功' : '添加成功';
        return $this->success($msg);
    }

    public function userWalletList(Request $request)
    {
        $user_id = Users::getUserId();
        $page =  $request->get('page',1);
        $limit = $request->get('limit',10);
        $lists = DB::table('users_wallet_withdraw')
            ->join('currency', 'currency.id', '=', 'users_wallet_withdraw.currency')
            ->where('users_wallet_withdraw.user_id',$user_id)
            ->select('users_wallet_withdraw.*', 'currency.name','currency.logo')
            ->orderBy('users_wallet_withdraw.id', 'desc')
            ->paginate($limit);

        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }

    public function userCenterNew()
    {
        $user_id = Users::getUserId();
        $user = DB::table('users')->find($user_id);
        list($user_level_text,$user_level_avatar) = UserLevelModel::getLevelName($user->user_level);
        $res = [
            'id' => $user->id,
            'nickname' => empty($user->nickname) ? $user->id : $user->nickname,
            'user_level' => $user->user_level,
            'user_level_text' => $user_level_text,
            'user_level_avatar' => $user_level_avatar,
            'score' => floatval($user->score),
            'avatar' => $user->head_portrait
        ];

        return $this->success($res);
    }




    public function numberPromoters()
    {
        $user_id = Users::getUserId();
        // $data=Users::find(['id'=>$user_id])->first();
        $data=Users::where('parents_path','like','%'.$user_id.'%')
            ->get()->toArray();
        $one=0;
        $one_total_usdt=0;
        $two=0;
        $two_total_usdt=0;
        // $three=0;
        $three_total_usdt=0;
        $onearr=[];
        $twoarr=[];
        $nextarr=[];
        foreach($data as $k=>$v){
            $arr=substr($v['parents_path'],strpos($v['parents_path'],$user_id));
            $arr=explode(",", $arr);
            if(count($arr)==1){
                $one+=1;
                array_push($onearr,$v);
            }else if(count($arr)==2){
                $two+=1;
                array_push($twoarr,$v);
            }else{
                array_push($nextarr,$v);
            }
            
        }
        $data=empty($data)?[]:$data;
        $list=['one'=>$one,'two'=>$two,'onearr'=>$onearr,'twoarr'=>$twoarr,'nextarr'=>$nextarr,'data'=>$data];
         return $this->success(['data'=>$list]);
    }
    //添加/修改收款方式
    public function saveCashInfo(Request $request)
    {
        $bank_name = $request->get('bank_name', '');
        $bank_dizhi = $request->get('bank_dizhi', '');
        $bank_id = $request->get('bank_id', '');
        $bank_branch = $request->get('bank_branch', '');
        $bank_account = $request->get('bank_account', '');
        $real_name = $request->get('real_name', '');
        $iban = $request->get('iban', '');
        $bank_code = $request->get('bank_code', '');
        $bank_address = $request->get('bank_address', '');
        // $alipay_account = $request->get('alipay_account', '');
        // $wechat_nickname = $request->get('wechat_nickname', '');
        // $wechat_account = $request->get('wechat_account', '');
        // $alipay_qr_code = $request->get('alipay_qr_code', '');
        // $wechat_qr_code = $request->get('wechat_qr_code', '');

        $user_id = Users::getUserId();
        if (empty($real_name)) {
            return $this->error('真实姓名必须填写');
        }
       
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $cash_info = UserCashInfo::where('user_id', $user_id)->first();
        if (empty($cash_info)) {
            $cash_info = new UserCashInfo();
            $cash_info->user_id = $user_id;
            $cash_info->create_time = time();
        }
        if (!empty($bank_name)) {
            $cash_info->bank_name = $bank_name;
        }
        if (!empty($bank_id)) {
            $cash_info->bank_id = $bank_id;
            $bank = Bank::find($bank_id);
            $bank_name = $bank?$bank->name:'';
            $cash_info->bank_name = $bank_name;
        }
        if (!empty($bank_branch)) {
            $cash_info->bank_branch = $bank_branch;
        }
        if (!empty($bank_dizhi)) {
            $cash_info->bank_dizhi = $bank_dizhi;
        }
        if (!empty($bank_account)) {
            $cash_info->bank_account = $bank_account;
        }
        $cash_info->real_name = $real_name;
        if (!empty($alipay_account)) {
            $cash_info->alipay_account = $alipay_account;

        }
        if (!empty($wechat_account)) {
            $cash_info->wechat_account = $wechat_account;

        }
        if (!empty($wechat_nickname)) {
            $cash_info->wechat_nickname = $wechat_nickname;

        }
        if (!empty($wechat_account)) {
            $cash_info->wechat_account = $wechat_account;

        }
        if (!empty($alipay_qr_code)) {
            $cash_info->alipay_qr_code = $alipay_qr_code;

        }
        if (!empty($wechat_qr_code)) {
            $cash_info->wechat_qr_code = $wechat_qr_code;

        }
        if (!empty($wechat_qr_code)) {
            $cash_info->wechat_qr_code = $wechat_qr_code;

        }
        if (!empty($iban)) {
            $cash_info->iban = $iban;

        }
        if (!empty($bank_code)) {
            $cash_info->bank_code = $bank_code;

        }
        if (!empty($bank_address)) {
            $cash_info->bank_address = $bank_address;

        }
        try {
            $cash_info->save();
            //更新申请商家收付款方式
            $seller=Seller::where("user_id",$user_id)->first();
            if(!empty($seller))
            {
                $seller->alipay_qr_code=$alipay_qr_code;
                $seller->wechat_qr_code=$wechat_qr_code;

                $seller->wechat_nickname=$wechat_nickname;
                $seller->wechat_account=$wechat_account;
                $seller->ali_account=$alipay_account;
                $seller->bank_account=$bank_account;
                $seller->bank_address=$bank_branch;
                $seller->bank_id = $bank_id;
                $seller->save();
            }
            return $this->success('保存成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    //添加/修改收款方式 國際
    public function saveCashInfoInternational(Request $request)
    {

        // 获取表单设置
        $forms = Setting::getValueByKey("form_international");
        $list = explode("\n", $forms);
        $lang = session('lang', 'en');
        $langs = ['en', 'zh', 'hk', 'fra', 'jp', 'kor', 'spa', 'th'];
        $new_forms = [];

        $data = $request->get('data', []);

        foreach ($list as $i => $item) {
            $_item = explode('|', $item);  // 多语言用|分隔开，分别是en|zh|hk|fra|jp|kor|spa|th
            $_values = [];
            foreach ($langs as $_k => $_lang) {
                try {
                    $_values[$_lang] = $_item[$_k];
                } catch (\Exception $e) {
                    $_values[$_lang] = $_item[0];
                }
            }
            $new_forms[] = $_values[$lang];
            if ($data[$i] == '') {
                return $this->error('参数错误');
            }
        }

        $user_id = Users::getUserId();

        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $cash_info = UserCashInfoInternational::where('user_id', $user_id)->first();
        if (empty($cash_info)) {
            $cash_info = new UserCashInfoInternational();
            $cash_info->user_id = $user_id;
            $cash_info->create_time = time();
        }
        $cash_info->data = implode("\n", $data);
        try {
            $cash_info->save();
            return $this->success('保存成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    public function checkPayPassword()
    {
        $password = Input::get('password', '');
        $user = Users::getById(Users::getUserId());
        if ($user->pay_password != Users::MakePassword($password)) {
            return $this->error('密码错误');
        } else {
            return $this->success('操作成功');
        }
    }

    public function currency_tousdt_log()
    {
        $limit = Input::get('limit','10');
        $page = Input::get('page','1');
        $user_id = Users::getUserId();
        $type1=AccountLog::CURRENCY_TO_USDT_MUL;
        $type2=AccountLog::CURRENCY_TO_USDT_ADD;
        $prize_pool = AccountLog::where(function ($query) use ($type1, $type2,$user_id) {
            $query->orWhere(function ($query) use ($user_id,$type1) {
                $query->where("type","=",$type1);
            })->orWhere(function ($query) use ($user_id,$type2) {
                $query->where("type","=",$type2);
            });
        })->where("user_id","=",$user_id)->orderBy("created_time","desc")->paginate($limit);

        return $this->success(array(
            "data"=>$prize_pool->items(),
            "limit"=>$limit,
            "page"=>$page,
        ));
    }

    public function currency_show()
    {

        $currency_id = Input::get('currency_id');
        $number = Input::get('number');
        if(!empty($number))
        {
            $currency_to_usdt_fee = Setting::getValueByKey('currency_to_usdt_fee', 100);
            $currency_to_usdt_fee = bc_div($currency_to_usdt_fee, 100);
            $user_id = Users::getUserId();
            $usdt = Currency::where('name','USDT')->select(['id'])->first();
            $service_charge=bc_mul($number,$currency_to_usdt_fee,5);
            $now_price=CurrencyQuotation::where("legal_id","=",$usdt->id)->where("currency_id","=",$currency_id)->first()->now_price;
            $add_usdt_legal_balance=bc_mul($number,$now_price,5);
            $add_usdt_legal_balance=bc_sub($add_usdt_legal_balance,$service_charge,5);
        }
        else
        {
            $add_usdt_legal_balance=0;
        }


        $currency=Currency::where("is_legal","!=",1)->where("is_display",1)->get()->toArray();
        return $this->success(['currency' => $currency, 'add_usdt_legal_balance' => $add_usdt_legal_balance]);

    }
    public function currency_tousdt()
    {
        $currency_id = Input::get('currency_id');
        $currency_name=Currency::where("id",$currency_id)->first()->name;
        $number = Input::get('number');
        $currency_to_usdt_fee = Setting::getValueByKey('currency_to_usdt_fee', 100);
        $currency_to_usdt_fee = bc_div($currency_to_usdt_fee, 100);
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $user_walllet_currency=UsersWallet::where("user_id","=",$user_id)->where("currency","=",$currency_id)->first();
        $usdt = Currency::where('name','USDT')->select(['id'])->first();
        $user_walllet_usdt=UsersWallet::where("user_id",$user_id)->where("currency", $usdt->id)->first();

        $now_price=CurrencyQuotation::where("legal_id","=",$usdt->id)->where("currency_id","=",$currency_id)->first()->now_price;
        if($now_price<=0)
        {
            return $this->error('当前行情小于等于零!');
        }
        $add_usdt_legal_balance=bc_mul($number,$now_price,5);
        $service_charge=bc_mul($add_usdt_legal_balance,$currency_to_usdt_fee,5);
        $add_usdt_legal_balance=bc_sub($add_usdt_legal_balance,$service_charge,5);
        if (empty($number) || $number <= 0) {
            return $this->error('参数错误!');
        }
        if ($number > $user_walllet_currency->legal_balance) {
            return $this->error('兑换数量大于持有资产!');
        }
        DB::beginTransaction();
        try {
            $result1 = change_wallet_balance(
                $user_walllet_currency,
                1,
                -$number,
                AccountLog::CURRENCY_TO_USDT_MUL,
                '资产兑换,减少' .$currency_name.'法币数量:'. -$number,
                false,
                $user->id,
                0
            );
            if ($result1 !== true) {
                throw new \Exception('资产兑换,减少持有币法币:' . $result1);
            }

            $result2 = change_wallet_balance(
                $user_walllet_usdt,
                3,
                +$add_usdt_legal_balance,
                AccountLog::CURRENCY_TO_USDT_ADD,
                '资产兑换,增加USDT杠杆币' . +$add_usdt_legal_balance.'扣除手续费'.-$service_charge,
                false,
                $user->id,
                0
            );
            if ($result2 !== true) {
                throw new \Exception('资产兑换,增加USDT杠杆币:' . $result2);
            }

            $user_walllet_usdt->lever_balance_add_allnum=$user_walllet_usdt->lever_balance_add_allnum+$add_usdt_legal_balance;
            $user_walllet_usdt->save();

            DB::commit();
            return $this->success('资产兑换成功!');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    public function candy_tousdt()
    {
        $candy_tousdt = Setting::getValueByKey('candy_tousdt', 100);
        $candy_tousdt = bc_div($candy_tousdt, 100);
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $candy_number = Input::get('candy_number');
        if (empty($candy_number) || $candy_number <= 0) {
            return $this->error('参数错误!');
        }
        if ($candy_number > $user->candy_number) {
            return $this->error('兑换数量大于剩余数量!');
        }
        DB::beginTransaction();
        try {
            $change_result = change_user_candy($user, -$candy_number, AccountLog::CANDY_TOUSDT_CANDY, "通证兑换USDT");
            if ($change_result !== true) {
                throw new \Exception($change_result);
            }
            $aaaa = UsersWalletcopy::leftjoin("currency", "currency.id", "users_wallet.currency")
                ->where("currency.name", "USDT")
                ->where("users_wallet.user_id", $user_id)
                ->select("users_wallet.id", "users_wallet.lever_balance", "users_wallet.user_id", "currency.id as currency_id")
                ->first();
            $user_walllet = UsersWalletcopy::where("user_id", $aaaa->user_id)
                ->where("currency", $aaaa->currency_id)
                ->first();
            $change = bc_mul($candy_number, $candy_tousdt, 4);
            $result = change_wallet_balance(
                $user_walllet,
                3,
                $change,
                AccountLog::CANDY_LEVER_BALANCE,
                '通证兑换,杠杆币增加' . $change,
                false,
                $user->id,
                0
            );
            if ($result !== true) {
                throw new \Exception('通证兑换杠杆币增加失败:' . $result);
            }
            DB::commit();
            return $this->success('通证兑换成功!');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function cashInfo()
    {
        $user_id = Users::getUserId();
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $result = UserCashInfo::where('user_id', $user_id)->firstOrNew([]);

        $banks = Bank::all();
        $result->banks = $banks;
        return $this->success($result);
    }

    public function cashInfoInternational()
    {
        $user_id = Users::getUserId();
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        // 获取表单设置
        $forms = Setting::getValueByKey("form_international");
        $list = explode("\n", $forms);
        $lang = session('lang', 'en');
        $langs = ['en', 'zh', 'hk', 'fra', 'jp', 'kor', 'spa', 'th'];
        $new_forms = [];

        $data = UserCashInfoInternational::where('user_id', $user_id)->firstOrNew([]);
        $data = explode("\n", $data->data);
        
        $new_data = [];
        foreach ($list as $i => $item) {
            $_item = explode('|', $item);  // 多语言用|分隔开，分别是en|zh|hk|fra|jp|kor|spa|th
            $_values = [];
            foreach ($langs as $_k => $_lang) {
                try {
                    $_values[$_lang] = $_item[$_k];
                } catch (\Exception $e) {
                    $_values[$_lang] = $_item[0];
                }
            }
            $new_forms[] = $_values[$lang];
            try{
                $new_data[] = $data[$i];
            } catch (\Exception $e) {
                $new_data[] = '';
            }
        }

        $result = [
            'forms' => $new_forms,
            'data' => $new_data
        ];

        return $this->success($result);
    }
    public function setAccount()
    {
        $account = Input::get('account', '');
        $password = Input::get('password', '');
        $repassword = Input::get('repassword', '');
        if (empty($account) || empty($password) || empty($repassword)) {
            return $this->error('必填项信息不完整');
        }
        if ($password != $repassword) {
            return $this->error('两次输入密码不一致');
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error('此用户不存在');
        }
        if ($user->account_number) {
            return $this->error('此交易账号已经设置');
        }
        $res = Users::where('account_number', $account)->first();
        if ($res) {
            return $this->error('此账号已经存在');
        }
        try {
            $user->account_number = $account;
            $user->pay_password = Users::MakePassword($password, $user->type);
            $user->save();
            return $this->success('交易账号设置成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function safeCenter()
    {
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $safeInfo = array(
            'mobile' => $user->phone,
            'email' => $user->email,
            'gesture_password' => $user->gesture_password,
        );
        return $this->success($safeInfo);
    }
    public function setMobile()
    {
        $user_id = Users::getUserId();
        $mobile = Input::get('mobile', '');
        $code = Input::get('code', '');
        $area_code = Input::get('area_code', '');
        if (empty($user_id) || empty($mobile) || empty($code)) {
            return $this->error('参数错误');
        }
        if ($code != session('code')) {
            return $this->error('验证码错误');
        }
        $user = Users::getByString($mobile);
        if (!empty($user)) return $this->error('账号已存在');
        try {
            $user = Users::find($user_id);
            $user->phone = $mobile;
            $user->area_code = $area_code;
            $user->save();
            return $this->success('手机绑定成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function setEmail()
    {
        $user_id = Users::getUserId();
        $email = Input::get('email', '');
        $code = Input::get('code', '');
        if (empty($user_id) || empty($email) || empty($code)) {
            return $this->error('参数错误');
        }
        if ($code != session('code')) {
            return $this->error('验证码错误');
        }
        try {
            $user = Users::find($user_id);
            $user->email = $email;
            $user->save();
            return $this->success('邮箱绑定成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //安全中心-->手势密码-->添加手势密码
    public function gesturePassAdd()
    {
        $password = Input::get('password', '');//获取的是一个数组[1,2,3]
        $re_password = Input::get('re_password', '');
        if (mb_strlen($password) < 6) {
            return $this->error('手势密码至少连接6个点');
        }
        if ($password != $re_password) {
            return $this->error('两次手势密码不相同');
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $user->gesture_password = $password;
        try {
            $user->save();
            return $this->success('手势密码添加成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function gesturePassDel()
    {
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $user->gesture_password = "";
        try {
            $user->save();
            return $this->success('取消手势密码成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    public function updatePayPassword()
    {
        $oldpassword = Input::get('oldpassword', '');
        $password = Input::get('password', '');
        $re_password = Input::get('re_password', '');
        $code = Input::get('code', '');
        
        // if ($code != session('code')) {
        //     return $this->error('验证码错误');
        // }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('密码只能在6-16位之间');
        }
        if ($password != $re_password) {
            return $this->error('两次密码不一致');
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if(Users::MakePassword($oldpassword, $user->type) != $user->pay_password){
            return $this->error('旧密码错误');
        }
        $user->pay_password = Users::MakePassword($password, $user->type);
        try {
            $user->save();
            return $this->success('交易密码设置成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function inviteList()
    {
        $time = Input::get('time', '');
        if ($time) {
            $time = strtotime($time);
        } else {
            $time = 0;
        }
        $list = AccountLog::has('user')
            ->select(DB::raw('sum(value) as total, user_id'))
            ->where('type', AccountLog::INVITATION_TO_RETURN)
            ->where('created_time', '>=', $time)
            ->groupBy('user_id')
            ->orderBy('total', 'desc')

            ->limit(20)
            ->get()
            ->toArray();

        if (empty($list)) {
            return $this->error("暂时还没有邀请排行榜，快去邀请吧");
        }


        foreach ($list as $key => $val) {

            $user = Users::find($val['user_id']);


            $list[$key]['account'] = $user->account;

        }

        return $this->success($list);


    }
    

      //邀请 
    public function invite()
    {

        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first();

        if (empty($user)) {
            return $this->error("会员未找到");
        }

       
        //邀请排行榜 前3
        $list = AccountLog::has('user')
            ->select(DB::raw('sum(value) as total, user_id'))
            ->where('type', AccountLog::INVITATION_TO_RETURN)

            ->groupBy('user_id')
            ->orderBy('total', 'desc')

            ->limit(3)
            ->get()
            ->toArray();
        if (empty($list)) {
            $list = [];
        } else {

            foreach ($list as $key => $val) {

                $users = Users::find($val['user_id']);

                $list[$key]['account'] = $users->account;

            }


        }

        $ad = [];
        $ad['image'] = "/upload/invite.png";

        $data = [];
        $data['extension_code'] = $user['extension_code'];
        $data['ad'] = $ad;
        $data['inviteList'] = $list;

        $num = Users::where('parent_id', $user_id)->count();

        if ($num > 0) {
            $data['invite_num'] = $num;
            $total = AccountLog::where('user_id', $user_id)->where('type', AccountLog::INVITATION_TO_RETURN)->sum('value');
            $data['invite_return_total'] = $total;
        } else {
            $data['invite_num'] = 0;
            $data['invite_return_total'] = 0;
        }

        return $this->success($data);

    }


    //钱包地址
    public function walletaddress()
    {
//        $user_id = Users::getUserId();
        $user_id = Input::get('user_id');
        $wallet_address = Input::get('wallet_address');

        $usermyself = Users::where("id", $user_id)->first()->toArray();
        $user = Users::where("wallet_address", $wallet_address)->where("id", '!=', $user_id)->first();
        if ($usermyself['wallet_address']) {
            return $this->error("你已绑定，不可更改!");
        } elseif (!empty($user)) {
            return $this->error("该地址已被绑定,请重新输入");
        } else {
            $pdo = new Users();
            $pdo->where("id", "=", $user_id)->update(['wallet_address' => $wallet_address]);
            return $this->success('绑定成功!');
        }
    }



    //我的  
    public function info(Request $request)
    {
        $request_user_id = $request->get('user_id', 0);
        $user_id = Users::getUserId();
        if($request_user_id){
            $user_id = $request_user_id;
        }

        $currency_usdt_id = Currency::where('name','USDT')->select(['id','name'])->first();

        //$user = Users::where("id",$user_id)->first(['id','phone','email','head_portrait','status']);
        $user = Users::where("id", $user_id)->first();
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        $user['is_open_transfer_candy']=Setting::getValueByKey("is_open_transfer_candy");
        //用户认证状况
        $res = UserReal::where('user_id', $user_id)->first();
        if (empty($res)) {
            $user['review_status'] = 0;
            $user['name'] = '';
        } else {
            $user['review_status'] = $res['review_status'];
            $user['name'] = $res['name'];
        }
        $seller = Seller::where('user_id', $user_id)->get()->toArray();
        if(!empty($seller))
        {
            $user['seller']=$seller;
        }
        $user['tobe_seller_lockusdt']=Setting::getValueByKey("tobe_seller_lockusdt");
        $user['currency_usdt_id']=$currency_usdt_id->id;
        $user['currency_usdt_name']=$currency_usdt_id->name;


        $currency_name = $request->input('currency_name', '');
        $lever_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                $query->where("is_lever", 1);
            })->get(['id', 'currency', 'lever_balance', 'lock_lever_balance'])->toArray();

        $lever_wallet['totle'] = 0;
        foreach ($lever_wallet['balance'] as $k => $v) {
            $num = $v['lever_balance'] + $v['lock_lever_balance'];
            $lever_wallet['totle'] += $num * $v['cny_price'];
        }
        $user["lever_wallet"]=$lever_wallet;

        $legal_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {
                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');
                //$query->where("is_legal", 1)->where('show_legal', 1);
                $query->where("is_legal", 1);
            })->get(['id', 'currency', 'legal_balance', 'lock_legal_balance'])
            ->toArray();
        $legal_wallet['totle'] = 0;
        foreach ($legal_wallet['balance'] as $k => $v) {
            $num = $v['legal_balance'] + $v['lock_legal_balance'];
            $legal_wallet['totle'] += $num * $v['cny_price'];
        }
        $user["legal_wallet"]=$legal_wallet;
        $micro_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) {
                $query->where('is_micro',1);
            })->get(['id', 'currency', 'micro_balance', 'lock_micro_balance'])->toArray();
        $user["micro_wallet"] = $micro_wallet;
        //幣幣钱包
        $change_wallet['balance'] = UsersWallet::where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) {
                $query->where('is_match',1);
            })->get(['id', 'currency', 'change_balance', 'lock_change_balance'])->toArray();
        $user["change_wallet"] = $change_wallet;
        $user["pay_password_isnull"] = empty($user->pay_password) ? 'eqnull' : 'neqnull';
        return $this->success($user);


    }

    public function realAdvanced(){
        
        $user_id = Users::getUserId();
        $front_pic = Input::get("front_pic", "");
        $reverse_pic = Input::get("reverse_pic", "");
        $hand_pic = Input::get("hand_pic", "");

        $user = Users::find($user_id);

        if (empty($user)) {
            return $this->error("会员未找到");
        }

        $userreal = UserReal::where('user_id', $user_id)->first();
        
        try {
            $userreal->front_pic = $front_pic;
            $userreal->reverse_pic = $reverse_pic;
            $userreal->hand_pic = $hand_pic;
             $userreal->advanced_user = 1; //提交审核
          
            $userreal->save();

            return $this->success('提交成功，等待审核');
        } catch (\Exception $e) {

            return $this->error($e->getMessage());
        } 
    }
    
    public function realName()
    {

        $user_id = Users::getUserId();
        $name = Input::get("name", "");
        $card_id = Input::get("card_id", "");

        if (empty($name) || empty($card_id)) {
            return $this->error("请提交完整的信息");
        }  

        $user = Users::find($user_id);

        if (empty($user)) {
            return $this->error("会员未找到");
        }

        $userreal_number=UserReal::where("card_id",$card_id)->count();

        if($userreal_number>0)
        {
            return $this->error("该身份证号已实名认证过!");
        }

        $userreal = UserReal::where('user_id', $user_id)->first();
        if (!empty($userreal)) {
            return $this->error("您已经申请过了");
        }

        try {

            $userreal = new UserReal();

            $userreal->user_id = $user_id;
            $userreal->name = $name;
            $userreal->card_id = $card_id;
            $userreal->create_time = time();

            $userreal->save();

            return $this->success('提交成功，等待审核');
        } catch (\Exception $e) {

            return $this->error($e->getMessage());
        }


    }


    public function userCenter()
    {

        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first(['id', 'phone', 'email']);
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        $userreal = UserReal::where('user_id', $user_id)->first();

        if (empty($userreal)) {
            $user['review_status'] = 0;
            $user['name'] = '';
            $user['card_id'] = '';
            $user['advanced_user'] = 0;
        } else {
            $user['review_status'] = $userreal['review_status'];
            $user['name'] = $userreal['name'];
            $user['card_id'] = $userreal['card_id'];
            $user['advanced_user'] = $userreal['advanced_user'];

        }



        if (!empty($user['card_id'])) {
            $user['card_id'] = mb_substr($user['card_id'], 0, 2) . '******' . mb_substr($user['card_id'], -2, 2);
        }
        return $this->success($user);


    }


    public function posterBg()
    {
        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first(['id', 'extension_code']);
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        $pics = InviteBg::all(['id', 'pic'])->toArray();

        $data['extension_code'] = $user['extension_code'];
        $data['share_url'] = Setting::getValueByKey('share_url', '');
        $data['pics'] = $pics;

        return $this->success($data);

    }




    public function logout()
    {

        $user_id = Users::getUserId();
        $user = Users::find($user_id);

        if (empty($user)) {
            return $this->error("会员未找到");
        }
        //清除用户的token  session
        session(['user_id' => '']);
        $token = Token::getToken();

        Token::deleteToken($user_id, $token);

        return $this->success('退出登录成功');


    }
































    public function vip()
    {
        $user_id = Users::getUserId(Input::get("user_id"));
        $password = Input::get('password', '');


        if (empty($password)) return $this->error("请输入支付密码");

        $vip = Input::get("vip");
        if (empty($user_id) || empty($vip)) {
            return $this->error("参数错误");
        }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        if ($user->vip >= $vip) {
            return $this->error("无需升级");
        }
        if ($vip == "2") {
            if ($user->vip == 1) {
                $money = 9000;
            } else {
                $money = 9999;
            }
        } else {
            $money = 999;
        }

        $wallet = UsersWallet::where("user_id", $user_id)
            ->where("token", Users::TOKEN_DEFAULT)
            ->select("id", "user_id", "password", "address", "balance", "lock_balance", "remain_lock_balance", "create_time", "wallet_name", "password_prompt")
            ->first();
        if (empty($wallet)) {
            return $this->error("暂无钱包");
        }
        if ($password != $wallet->password) {
            return $this->error("支付密码错误");
        }
        if ($wallet->balance < $money) {
            return $this->error("余额不足");
        }

        $walletn = UsersWallet::find($wallet->id);
        $data_wallet = [
            'balance_type' => AccountLog::UPDATE_VIP,
            'wallet_id' => $walletn->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $walletn->balance,
            'change' => -$money,
            'after' => bc_sub($walletn->balance, $money, 5),
        ];
        $user->vip = $vip;
        $walletn->balance = $walletn->balance - $money;
        $user->save();
        $walletn->save();
        AccountLog::insertLog(
            array(
                "user_id" => $user_id,
                "value" => -$money,
                "type" => AccountLog::UPDATE_VIP,
                "info" => "升级会员"
            ),
            $data_wallet
        );
        return $this->success("升级成功");
    }
    //提交虚拟币收货地址
    public function updateCurrencyAddress()
    {

    }
    public function updateAddress()
    {
        $address = Users::getUserId();

        $eth_address = trim(Input::get('eth_address'));
        if (empty($address) || empty($eth_address)) {
            return $this->error('参数错误');
        }
        $user = Users::find($address);
        if (empty($user)) {
            return $this->error('没有此用户');
        }

        if ($other = Users::where('eth_address', $eth_address)->first()) {
            if ($other->id != $user->id) {
                return $this->error('该地址别人已经绑定过了');
            }
        }
        try {
            $user->eth_address = $eth_address;
            $user->save();
            return $this->success('更新成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getUserByAddress()
    {
        $user_id = Users::getUserId();
        if (empty($user_id))
            return $this->error("参数错误");
        $user = Users::where("id", $user_id)->first();
        if (empty($user)) {
            return $this->error("会员未找到");
        }
        if (empty($user->extension_code)) {
            $user->extension_code = Users::getExtensionCode();
            $user->save();
        }

        $wallet = UsersWallet::where("user_id", $user_id)
            ->where("token", Users::TOKEN_DEFAULT)
            ->select("id", "user_id", "address", "balance", "lock_balance", "remain_lock_balance", "create_time", "wallet_name", "password_prompt")
            ->first();
        $user->wallet = $wallet;
        return $this->success($user);
    }
    public function chatlist()
    {
        $user_id = Users::getUserId(Input::get('user_id', ''));
        if (empty($user_id)) return $this->error("参数错误");

        $user = Users::find($user_id);
        if (empty($user)) return $this->error("用户未找到");

        $chat_list = UserChat::orderBy('id', 'DESC')->paginate(20);

        $datas = $chat_list->items();

        krsort($datas);
        $return = array();
        foreach ($datas as $d) {
            array_push($return, $d);
        }
        return $this->success(array(
            "user" => $user,
            "chat_list" => [
                'total' => $chat_list->total(),
                'per_page' => $chat_list->perPage(),
                'current_page' => $chat_list->currentPage(),
                'last_page' => $chat_list->lastPage(),
                'next_page_url' => $chat_list->nextPageUrl(),
                'prev_page_url' => $chat_list->previousPageUrl(),
                'from' => $chat_list->firstItem(),
                'to' => $chat_list->lastItem(),
                'data' => $return,
            ]
        ));
    }
    public function sendchat()
    {
        $user_id = Users::getUserId(Input::get('user_id', ''));

        $content = Input::get('content', '');
        if (empty($user_id) || empty($content)) return $this->error("参数错误");

        $user = Users::find($user_id);
        if (empty($user)) return $this->error("会员未找到");

        $data["user_id"] = $user_id;
        $data["user_name"] = $user->account_number;
        $data["head_portrait"] = $user->head_portrait;
        $data["content"] = $content;
        $data["type"] = "1";


        try {
            $res = UserChat::sendChat($data);
            if ($res == "ok") {
                $user_chat = new UserChat();
                $user_chat->from_user_id = $user_id;
                $user_chat->to_user_id = 0;
                $user_chat->content = $content;
                $user_chat->type = 1;
                $user_chat->add_time = time();
                $user_chat->save();
                return $this->success("ok");
            } else {
                return $this->error("请重试");
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //用户信息导入
    public function into_users()
    {
        $password = Input::get('password', '');
        $account_number = Input::get('account_number', '');
        $pay_password = Input::get('pay_password', '');
        $parent_id = Input::get('parent_id', '');//邀请人账户
        if (empty($parent_id) || empty($pay_password) || empty($password) || empty($password)) {
            return $this->error('请把参数填写完整');
        }
        //判断用户是否存在
        $user = Users::getByAccountNumber($account_number);
        if (!empty($user)) {
            return $this->error('用户已存在');
        }
        //判断推荐人是否存在
        $invit = Users::getByAccountNumber($parent_id);
        if (empty($invit)) {
            return $this->error('推荐用户不存在');
        }

        $users = new Users();
        $users->password = Users::MakePassword($password, 1);
        $users->pay_password = Users::MakePassword($pay_password, 0);
        $users->parent_id = $invit->id;
        $users->account_number = $account_number;
        $users->type = 1;
        $users->head_portrait = URL("mobile/images/user_head.png");
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            $users->save();//保存到user表中
            $currency = Currency::all();
            $address_url = config('wallet_api') . $users->id;
            $address = RPC::apihttp($address_url);
            $address = @json_decode($address, true);

            foreach ($currency as $key => $value) {
                $userWallet = new UsersWallet();
                $userWallet->user_id = $users->id;
                if ($value->type == 'btc') {
                    $userWallet->address = $address["contentbtc"];
                } else {
                    $userWallet->address = $address["content"];
                }
                $userWallet->currency = $value->id;
                $userWallet->create_time = time();
                $userWallet->save();//默认生成所有币种的钱包
            }
            DB::commit();
            return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }

    }
     //美丽链转入(imc) 
    public function into_tra()
    {
        $account_number = Input::get('account_number', '');
       // var_dump($account_number);die;
        $password = Input::get('password', '');
        $number = Input::get('number', '');
        $type = Input::get('type', '1'); ///type:0 法币交易，type:1币币交易，type:2杠杆交易
        //dump( $type);die;
        if (empty($account_number)) {
            return $this->error('转入账户不能为空');
        }
        if (empty($password)) {
            return $this->error('密码不能为空');
        }
        if (empty($number)) {
            return $this->error('转入数量不能为空');
        }
        $tra_user = Users::getByAccountNumber($account_number);
        if (empty($tra_user)) {
            return $this->error('用户未找到');
        }
        if ($tra_user->password != Users::MakePassword($password, $tra_user->type)) {
            return $this->error('用户密码错误');
        }
        //当前用户钱包信息
        $currency = Currency::where('name', 'IMC')->first();
        $waller_info = UsersWallet::where('currency', $currency->id)->where('user_id', $tra_user->id)->first();
      //dump( $waller_info);die;
        DB::beginTransaction();
        $data_wallet = [
             //'balance_type' =>  0,
            'wallet_id' => $waller_info->id,
            'lock_type' => 0,
            'create_time' => time(),
             //'before' => 0,
            'change' => $number,
             //'after' => 0,
        ];
        try {
            if ($type == 0) {
                $data_wallet['balance_type'] = 1;
                $data_wallet['before'] = $waller_info->legal_balance;
                $data_wallet['after'] = bc_add($waller_info->legal_balance, $number, 5);
                $waller_info->legal_balance = $waller_info->legal_balance + $number;
                $info = '美丽链法币交易余额转入';
                $type_info = AccountLog::INTO_TRA_FB;
            } else if ($type == 1) {
                $data_wallet['balance_type'] = 2;
                $data_wallet['before'] = $waller_info->change_balance;
                $data_wallet['after'] = bc_add($waller_info->change_balance, $number, 5);
                $waller_info->change_balance = $waller_info->change_balance + $number;
                $info = '美丽链币币交易余额转入';
                $type_info = AccountLog::INTO_TRA_BB;
            } else {
                $data_wallet['balance_type'] = 3;
                $data_wallet['before'] = $waller_info->lever_balance;
                $data_wallet['after'] = bc_add($waller_info->lever_balance, $number, 5);
                $waller_info->lever_balance = $waller_info->lever_balance + $number;
                $info = '美丽链杠杆交易余额转入';
                $type_info = AccountLog::INTO_TRA_GG;
            }
            $waller_info->save();
            //锁定余额

            $waller_info->save();
            AccountLog::insertLog([
                'user_id' => $tra_user->id,
                'value' => $number,
                'currency' => $currency->id,
                'info' => $info,
                'type' => $type_info,
            ], $data_wallet);
            DB::commit();
            return $this->success('转入成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }


    }
     //转入记录
    public function into_tra_log()
    {
        $user_id = Users::getUserId();
        $list = AccountLog::whereIn("type", array(65, 66, 67))->where('user_id', $user_id)->orderBy('id', 'desc')->get()->toArray();
        return $this->success($list);
    }
     //修改密码
    public function e_pwd()
    {
        $user_id = Users::getUserId();
        // $account_number = Input::get('account_number', '');
        $password = Input::get('password', '');
        $type = Input::get('type', '1'); ///type:1登录密码，type:2支付密码

        if (empty($password)) {
            return $this->error('密码不能为空');
        }
        $tra_user = Users::find($user_id);
        // $tra_user = Users::getByAccountNumber($account_number);
        if (empty($tra_user)) {
            return $this->error('用户未找到');
        }
        DB::beginTransaction();
        try {
            if ($type == 1) {
                $tra_user->password = Users::MakePassword($password, $tra_user->type);
            } else {
                $tra_user->pay_password = Users::MakePassword($password, $tra_user->type);
            }
            $tra_user->save();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
        return $this->success('success');
    }

    public function updateBalance(){
        exit('close');
        $user_id = Users::getUserId();
        // $this->updateWalletAddress();
        try{
            DB::beginTransaction();
            $user_wallets = UsersWallet::lockForUpdate()->where('user_id',$user_id)->where('gl_time','<',time()-60*60)->get();
            foreach ($user_wallets as $user_wallet){
                
                // UsersWallet::updateBalance($user_wallet);
                $currency = Currency::find($user_wallet->currency);
                if(empty($currency)){
                    return false;
                }
                if(empty($user_wallet->address)){
                    return false;
                }
                $address = $user_wallet->address;
                if($currency->type=='eth'){
                    echo $user_wallet->currency_name;
                    $url = "https://api.etherscan.io/api?module=account&action=balance&address=".$address."&tag=latest&apikey=YourApiKeyToken";
                    // $content = RPC::apihttp($url);
                    $content = RPC::curl($url,false,0,1);
                    $content = @json_decode($content, true);
                    // echo($url); 
                    // dd($content);
                    // $content = json_decode($content,true);
                    $message = $content["message"];
                        
                    // dd($content);
                }else if($currency->type=='erc20'){
                    $url = "https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=".$currency->contract_address."&address=".$address."&tag=latest&apikey=579R8XPDUY1SHZNEZP9GA4FEF1URNC3X45".rand(1,10000);
                    $content = RPC::curl($url,false,0,1);
                    $content = @json_decode($content,true);
                    $message = $content["message"];
                }else if($currency->type=='btc'){
                    $url ='http://43.129.16.120:82/wallet/btc/balance?address='.$address;
                    $content = RPC::curl($url,false,0,0);
                    $content = @json_decode($content,true);
                    if(isset($content["code"]) && $content["code"] == 0){
                        $content["result"] = $content['data']['balance'];
                    }
                    
                    $code = $content["code"];
                }else if($currency->type=='usdt'){
                    // echo $address;
                    $url ='http://43.129.16.120:82/wallet/usdt/balance?address='.$address;
                    $content = RPC::curl($url,false,0,0);
                    $content = @json_decode($content,true);
                    if(isset($content["code"]) && $content["code"] == 0){
                        $content["result"] = $content['data']['balance'];
                    }
                }
                if (!$content){
                    return false;
                }
                if (isset($content["message"]) && $content["message"] == "OK"){
                    $decimal = $currency->decimal_scale;//小数位
                    empty($decimal) && $decimal=18;
                    echo $user_wallet->currency_name;
                    echo $content["result"];
                    $lessen = bc_pow(10, $decimal);
                    $content["result"] = bc_div($content["result"] , $lessen,8);
                    if ($content["result"] > $user_wallet->old_balance){
                        $result = bc_sub($content["result"] , $user_wallet->old_balance,8);
                        $user_wallet->old_balance = $content["result"];
                        $user_wallet->save();
                        change_wallet_balance($user_wallet,1,$result,AccountLog::ETH_EXCHANGE,'充币增加',false);
                    }
                }
                if (isset($content["code"]) && $content["code"] == 0){
                    $content["result"] = bc_div($content["result"] , 100000000,8);
                    echo $user_wallet->currency_name;
                    echo $content["result"];
                    if ($content["result"] > $user_wallet->old_balance){
                        $result = bc_sub($content["result"] , $user_wallet->old_balance,8);
                        $user_wallet->old_balance = $content["result"];
                        $user_wallet->save();
                        change_wallet_balance($user_wallet,1,$result,AccountLog::ETH_EXCHANGE,'充币增加',false);
                    }
                }

            }
            
            DB::commit();
            return $this->success('更新成功');
        }catch (\Exception $exception){
            DB::rollback();
            return $this->error($exception->getMessage().'-'.$exception->getFile().'-'.$exception->getLine());
        }

    }

    public function mining(){
        $user_id=Users::getUserId();
        $user=Users::where('id',$user_id)->first();
        $num=UserAlgebra::where('user_id',$user_id)->sum('value');
        $count=Users::where('parent_id',$user_id)->where('level','>=',1)->count('id');
        $level=$user->level;
        $sum=Users::whereRaw("FIND_IN_SET(".$user_id.",parents_path)")->count('id');
        $data['sum']=$sum;
        $data['count']=$count;
        $data['level']=$level;
        $data['num']=$num;
        return $this->success($data);
    }


    public function test(){
        $lang = session('lang', 'en');
        App::setLocale($lang);
        $this->success_ceshi([1]);
    }

    public function kf()
    {
        return $this->success(['kf'=>Setting::getValueByKey('kf')]);

    }

    public function withdrawList(Request $request)
    {
        $user_id = Users::getUserId();
        $page =  $request->get('page',1);
        $limit = $request->get('limit',10);
        $lists = DB::table('users_wallet_out')
            ->join('currency', 'currency.id', '=', 'users_wallet_out.currency')
            ->where('users_wallet_out.user_id',$user_id)
            ->select('users_wallet_out.id', 'users_wallet_out.number',
                'currency.name','users_wallet_out.create_time'
            ,'users_wallet_out.status', 'users_wallet_out.notes')
            ->orderBy('users_wallet_out.id', 'desc')
            ->paginate($limit);

        foreach($lists->items() as &$items){
            $items->create_time = date('Y-m-d H:i:s',$items->create_time);
            $arr = [1=>'审核中',2=>'审核完成',3=>'审核失败'];
            $items->status_text = str_replace('account_log_info.', '', __("account_log_info.".$arr[$items->status]));
        }
        unset($items);
        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }
    
    //修改用户头像
    public function uploadHeadPortrait()
    {
        $user_id = Users::getUserId();
        $head_portrait = Input::post('head_portrait');
        
        $res = Users::where("id", "=", $user_id)->update(['head_portrait' => $head_portrait]);

        return $this->success($res);
    }
    
    //修改昵称
    public function editNickname(){
        $user_id = Users::getUserId();
        $nickname = Input::post('nickname');
        
        $res = Users::where("id", "=", $user_id)->update(['nickname' => $nickname]);

        return $this->success($res);
    }
    
    //获取设置表里的东西
    public function getSetting(Request $request){
       
        $keyword = $request->get('keyword');
        
        if(!$keyword){
          return $this->error('error'); 
        }
        
        $res = DB::table('settings')->where('key','=',$keyword)->first();
    
        return $this->success($res);
    }
    

}
?>
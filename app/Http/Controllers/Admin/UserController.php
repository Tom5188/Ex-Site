<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Cache\RedisLock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\DAO\BlockChain;
use Maatwebsite\Excel\Facades\Excel;
use App\Utils\RPC;
use App\{Address,
    AccountLog,
    Currency,
    GdOrder,
    GdUser,
    IdCardIdentit,
    Setting,
    UserLevelModel,
    Users,
    UserCashInfo,
    MarketHour,
    MyQuotation,
    UserReal,
    UsersWallet,
    UserCashInfoInternational,
    LhDepositOrder
};
use App\UsersWalletWithdraw;
use Session;
use App\Agent;
use App\UserChat;
use App\Token;
use App\DAO\UserDAO;
use App\DAO\RewardDAO;
use App\UserProfile;

class UserController extends Controller
{
    public function index()
    {
        return view("admin.user.index");
    }
    
     public function add()
    {
        return view("admin.user.add");
    }
    
    public function postadd(Request $request){
        $phone = $request->get('phone', '');
        $email = $request->get('email', '');
        $password = $request->get('password', '');
        $parent = $request->get('parent', '');
        $area_code_id = $request->get('area_code_id', '');
        if (empty($password) || (empty($email) && (empty($phone) && empty($area_code_id)))) {
            return $this->error('参数错误');
        }
        if(empty($area_code_id)){
            $area_code_id = 0;
        }
        if(empty($email)){
            $email = 0;
        }
        $parent_id = 0;
        if(!empty($parent)){
            $p = Users::where("extension_code", $parent)->first();
            $parent_id = $p->id;
        }
        $type = "mobile";
        $users = new Users();
        $users->password = Users::MakePassword($password);
        $users->parent_id = $parent_id;
        if(empty($phone)){
            $users->account_number = $email;
        }else{
            $users->account_number = $phone;
        }
        
        $users->area_code_id = $area_code_id;
        if ($type == "mobile") {
            $users->phone = $phone;
            $users->email = $email;
        } else {
            $users->email = $email;
            $users->phone = $phone;
        }
        $user_default_avatar = DB::table('settings')->where('key','user_default_avatar')->first();

        $users->head_portrait = $user_default_avatar->value;
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();

        DB::beginTransaction();
        try {
            $users->parents_path = UserDAO::getRealParentsPath($users); // 生成parents_path tian add
            // 代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
            $users->agent_note_id = Agent::reg_get_agent_id_by_parentid($parent_id);
            // 代理商节点关系
            $users->agent_path = Agent::agentPath($parent_id);
            
            $users->save(); // 保存到user表中
            $test = UsersWallet::makeWallet($users->id);
            // DB::rollBack();
            // return $this->error('File:');
            UserProfile::unguarded(function () use ($users) {
                $users->userProfile()->create([]);
            });
            
            DB::commit();
            return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }

    
    //开通盲盒作者
    public function setBindBox(Request $request){
        $id = $request->get('id', '');
        if (empty($id)) return $this->error("参数错误");
        
        $user =  Users::where('id',$id)->first();
        if($user->is_realname != 2){
            return $this->error("用户还未实名!");
        }
        if($user->is_bind_box_author==1){
            $user->is_bind_box_author = 0;
        }else{
            $user->is_bind_box_author = 1;
        }
        $user->save();
       return $this->success("操作成功"); 
    }
    
    //导出用户列表至excel
    public function csv(Request $request)
    {
        // $limit = $request->get('limit', 10);
        $account = $request->get('account', '');

        $list = new Users();
        $list = $list->leftjoin("user_real", "users.id", "=", "user_real.user_id");
        //var_dump($n);die;
        if (!empty($account)) {
            $list = $list->where("phone", 'like', '%' . $account . '%')
                ->orwhere('email', 'like', '%' . $account . '%')
                ->orWhere('account_number', 'like', '%' . $account . '%');
        }
        $list = $list->select("users.*", "user_real.card_id")->orderBy('users.id', 'desc')->get();
        $data = $list;

        return Excel::create('用户数据', function ($excel) use ($data) {
            $excel->sheet('用户数据', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('账户名');
                });
                /*
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('团队充值业绩');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('直推实名人数');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('团队实名人数');
                });
                */
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('邀请码');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('用户状态');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('头像');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('注册时间');
                });
                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        $i = $key + 2;
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['account_number']);
                        $sheet->cell('C' . $i, $value['top_upnumber']);
                        $sheet->cell('D' . $i, $value['zhitui_real_number']);
                        $sheet->cell('E' . $i, $value['real_teamnumber']);
                        $sheet->cell('F' . $i, $value['extension_code']);
                        $sheet->cell('G' . $i, $value['status']);
                        $sheet->cell('H' . $i, $value['head_portrait']);
                        $sheet->cell('I' . $i, $value['time']);
                    }
                }
            });
        })->download('xlsx');
    }

    //用户列表
    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $account = $request->get('account', '');
        $name = $request->get('name', '');
        $risk = $request->get('risk', -2);

        $list = new Users();
        $list = $list->leftjoin("user_real", "users.id", "=", "user_real.user_id");

        if (!empty($account)) {
            $list = $list->where("phone", 'like', '%' . $account . '%')
                ->orwhere('email', 'like', '%' . $account . '%')
                ->orwhere('users.id', 'like', '%' . $account . '%')
                ->orWhere('account_number', 'like', '%' . $account . '%');
        }

        $list = $list->when($name != '', function ($query) use ($name) {
            $query->whereHas('userReal', function ($query) use ($name) {
                $query->where('name', $name);
            });
        });

        if ($risk != -2) {
            $list = $list->where('risk', $risk);
        }

        $list = $list->select("users.*", "user_real.card_id")
        ->orderBy('users.id', 'desc')
        ->paginate($limit);

        $items = $list->getCollection();
        
        $items->transform(function ($item, $key) {
            $level = UserLevelModel::pluck('name', 'id');
            $level = $level ? $level->toArray() : [];
            if ($item->user_level == 0){
                $item->level_text = '无';
            }else{
                $item->level_text = $level[$item->user_level];
            }
            $item->append('risk_name');

            $cashInfo = UserCashInfo::where('user_id', $item->id)->first();
            $hasCashInfo = false;
            if (!empty($cashInfo)) {
                $hasCashInfo = true;
            }
            $item->cash_info = $hasCashInfo;

            $cashInfoInternational = UserCashInfoInternational::where('user_id', $item->id)->first();
            $hasCashInfoInternational = false;
            if (!empty($cashInfoInternational)) {
                $hasCashInfoInternational = true;
            }
            $item->cash_info_international = $hasCashInfoInternational;

            return $item;
        });
        $list->setCollection($items);

        $USDT_id = Currency::where('name', 'USDT')->first()->id;


        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

    public function edit(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }

        $result = new Users();
        $result = $result->leftjoin("user_real", "users.id", "=", "user_real.user_id")->select("users.*", "user_real.card_id")->find($id);
        //var_dump($result->toArray());die;
        $res = UserCashInfo::where('user_id', $id)->first();
        $level = UserLevelModel::pluck('name', 'id');
        $level = $level ? $level->toArray() : [];
        $user_list = Users::all();

        return view('admin.user.edit', ['result' => $result, 'res' => $res,'level'=>$level,'user_list' => $user_list]);
    }
    
    //编辑用户信息  
    public function doedit()
    {
       // $phone = Input::get("phone");
        $parent_id = Input::get("parent_id");
        $common = Input::get("common");
        $card_id = Input::get("card_id");
        $password = Input::get("password");
        $account_number = Input::get("account_number");
        $pay_password = Input::get("pay_password");
        $bank_account = Input::get("bank_account");
        $bank_name = Input::get("bank_name");
        $alipay_account = Input::get("alipay_account");
        $wechat_nickname = Input::get("wechat_nickname");
        $wechat_account = Input::get("wechat_account");
        $is_service = Input::get("is_service",0)??0;
        $risk = Input::get('risk', 0);
        $user_level = Input::get('user_level', 0);
        $virtual_follow_num = Input::get('virtual_follow_num', 0);

        $id = Input::get("id");
        if (empty($id)) return $this->error("参数错误");

        $user = Users::find($id);
        if (empty($user)) {
            return $this->error("数据未找到");
        }
        
        if($id == $parent_id){
             return $this->error("上级用户不能是自己");
        }

        //$user->account_number = $account_number;

        if (!empty($password)) {
            $user->password = Users::MakePassword($password);
        }
        if (!empty($pay_password)) {
            $user->pay_password = Users::MakePassword($pay_password, $user->type);
        }
        if (!empty($is_service)) {
            $has_service = Users::where('is_service',1)->first();
            if($has_service){
                return $this->error("只允许设置一个客服,当前客服账号:{$has_service->account_number}");
            }
            $user->is_service = $is_service;
        }
        $flag = 0;
        if ($user->user_level != $user_level){
            $flag = 1;
        }
        $user->risk = $risk;
        $user->user_level = $user_level;
        $user->parent_id = $parent_id;
        $user->common = $common;
        if(!empty($virtual_follow_num)){
            $user->virtual_follow_num = $virtual_follow_num;
        }
        DB::beginTransaction();

        try {
            $user->save();
            $cashinfo = UserCashInfo::where('user_id', $id)->first();
            if (empty($cashinfo)) {
                $cashinfo = new UserCashInfo();
                $cashinfo->user_id = $id;
            }

            $cashinfo->bank_name = $bank_name ?? '';
            $cashinfo->bank_account = $bank_account ?? '';
            $cashinfo->alipay_account = $alipay_account ?? '';
            $cashinfo->wechat_account = $wechat_account ?? '';
            $cashinfo->wechat_nickname = $wechat_nickname ?? '';
            $cashinfo->save();
            //更改身份证号
            if (!empty($card_id)) {
                $real = UserReal::where("user_id", "=", $id)->first();
                $real->card_id = $card_id;
                $real->save();
            }
            if ($flag == 1){
                DB::table('user_level_log')->insertGetId([
                   'user_id' => $id,
                   'level_id' => $user_level,
                   'type' => 1,
                   'create_time' => time()
                ]);
            }
            DB::commit();
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function lockUser(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }
        $result = Users::find($id);
        //
        // $res=UserCashInfo::where('user_id',$id)->first();
        return view('admin.user.lock', ['result' => $result]);
    }

    public function doLock(Request $request)
    {
        $id = $request->get('id', 0);
        $date = $request->get('date', 0);
        $status = $request->get('status', 0);
        $frozen_funds = $request->get('frozen_funds', 0);

        if (empty($id)) {
            return $this->error('参数错误');
        }
        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        if (empty($date)) {
            return $this->error('缺少时间！');
        }
        $users = new Users();
        $result = $users->lockUser($user, $status, $date, $frozen_funds);
        if (!$result) {
            return $this->error('锁定失败');
        }
        return $this->success('操作成功');
    }

    public function del(Request $request)
    {
        
        $id = $request->get('id');
        $user = Users::getById($id);
        if (empty($user)) {
            $this->error("用户未找到");
        }
        try {
            $user->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function lock(Request $request)
    {
        $id = $request->get('id', 0);

        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        if ($user->status == 1) {
            $user->status = 0;
        } else {
            $user->status = 1;
        }
        try {
            $user->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function wallet(Request $request)
    {
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        return view("admin.user.user_wallet", ['user_id' => $id]);
    }

    public function walletList(Request $request)
    {
        $limit = $request->get('limit', 10);
        $user_id = $request->get('user_id', null);
        if (empty($user_id)) {
            return $this->error('参数错误');
        }
        $list = new UsersWallet();
        $list = $list->where('user_id', $user_id)->paginate($limit);
    
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

//钱包锁定状态
    public function walletLock(Request $request)
    {
        $id = $request->get('id', 0);

        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            return $this->error('参数错误');
        }
        if ($wallet->status == 1) {
            $wallet->status = 0;
        } else {
            $wallet->status = 1;
        }
        try {
            $wallet->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /*
     * 调节账户
     * */
    public function conf(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $result = UsersWallet::find($id);
        if (empty($result)) {
            return $this->error('无此结果');
        }
        $account = Users::where('id', $result->user_id)->value('phone');
        if (empty($account)) {
            $account = Users::where('id', $result->user_id)->value('email');
        }
        $result['account'] = $account;
        return view('admin.user.conf', ['results' => $result]);
    }

    //调节账号  type  1法币交易余额  2法币交易锁定余额 3币币交易余额 4币币交易锁定余额  5杠杆交易余额 6杠杆交易锁定余额
    public function postConf(Request $request)
    {
        $message = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($request->all(), [
            'way' => 'required',   //增加 increment；减少 decrement
            'type' => 'required',       //原生余额1；消费余额2；增值余额3；可增加其他账户调节字段
            'conf_value' => 'required',       //值
        ], $message);

        //以上验证通过后 继续验证
        $validator->after(function ($validator) use ($request) {

            $wallet = UsersWallet::find($request->get('id'));
            if (empty($wallet)) {
                return $validator->errors()->add('isUser', '没有此钱包');
            }
            $user = Users::getById($wallet->user_id);
            if (empty($user)) {
                return $validator->errors()->add('isUser', '没有此用户');
            }
            $way = $request->get('way', 'increment');
            $type = $request->get('type', 1);
            $conf_value = $request->get('conf_value', 0);
            if ($type == 1 && $way == 'decrement') {
                if ($wallet->legal_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包法币交易余额不足' . $conf_value . '元');
                }
            } elseif ($type == 2 && $way == 'decrement') {
                if ($wallet->lock_legal_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包法币交易锁定余额不足' . $conf_value . '元');
                }
            } elseif ($type == 3 && $way == 'decrement') {
                if ($wallet->change_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包币币交易余额不足' . $conf_value . '元');
                }
            } elseif ($type == 4 && $way == 'decrement') {
                if ($wallet->lock_change_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包币币交易锁定余额不足' . $conf_value . '元');
                }
            } elseif ($type == 5 && $way == 'decrement') {
                if ($wallet->lever_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包闪兑交易余额不足' . $conf_value . '元');
                }
            } elseif ($type == 6 && $way == 'decrement') {
                if ($wallet->lock_lever_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包闪兑交易锁定余额不足' . $conf_value . '元');
                }
            }elseif ($type == 7 && $way == 'decrement') {
                if ($wallet->micro_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包期权余额不足' . $conf_value . '元');
                }
            } elseif ($type == 8 && $way == 'decrement') {
                if ($wallet->lock_micro_balance < $conf_value) {
                    return $validator->errors()->add('isBalance', '此钱包期权锁定余额不足' . $conf_value . '元');
                }
            }




        });
        //如果验证不通过
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $id = $request->get('id', null);
        $way = $request->get('way', 'increment');
        $type = $request->get('type', 1);
        $conf_value = $request->get('conf_value', 0);
        $info = $request->get('info', ':');
        $wallet = UsersWallet::find($id);
        $user = Users::getById($wallet->user_id);


        $data_wallet['wallet_id'] = $id;
        $data_wallet['create_time'] = time();
        DB::beginTransaction();
        try {
            if ($type == 1) {
                $data_wallet['balance_type'] = 1;
                $data_wallet['lock_type'] = 0;
                $data_wallet['before'] = $wallet->legal_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->legal_balance, $conf_value, 5);
                    $wallet->increment('legal_balance', $conf_value);
                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LEGAL_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LEGAL_BALANCE, 'currency' => $wallet->currency], $data_wallet);
                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->legal_balance, $conf_value, 5);
                    $wallet->decrement('legal_balance', $conf_value);
                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LEGAL_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LEGAL_BALANCE, 'currency' => $wallet->currency], $data_wallet);
                }
            } elseif ($type == 2) {
                $data_wallet['balance_type'] = 1;
                $data_wallet['lock_type'] = 1;
                $data_wallet['before'] = $wallet->lock_legal_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lock_legal_balance, $conf_value, 5);
                    $wallet->increment('lock_legal_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_LEGAL_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_LEGAL_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lock_legal_balance, $conf_value, 5);
                    $wallet->decrement('lock_legal_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_LEGAL_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_LEGAL_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 3) {
                $data_wallet['balance_type'] = 2;
                $data_wallet['lock_type'] = 0;
                $data_wallet['before'] = $wallet->change_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->change_balance, $conf_value, 5);
                    $wallet->increment('change_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_CHANGE_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_CHANGE_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->change_balance, $conf_value, 5);
                    $wallet->decrement('change_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_CHANGE_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_CHANGE_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 4) {
                $data_wallet['balance_type'] = 2;
                $data_wallet['lock_type'] = 1;
                $data_wallet['before'] = $wallet->lock_change_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lock_change_balance, $conf_value, 5);
                    $wallet->increment('lock_change_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_CHANGE_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_CHANGE_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lock_change_balance, $conf_value, 5);
                    $wallet->decrement('lock_change_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_CHANGE_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_CHANGE_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 5) {
                $data_wallet['balance_type'] = 3;
                $data_wallet['lock_type'] = 0;
                $data_wallet['before'] = $wallet->lever_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lever_balance, $conf_value, 5);
                    $wallet->increment('lever_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LEVER_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LEVER_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lever_balance, $conf_value, 5);
                    $wallet->decrement('lever_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LEVER_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LEVER_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 6) {
                $data_wallet['balance_type'] = 3;
                $data_wallet['lock_type'] = 1;
                $data_wallet['before'] = $wallet->lock_lever_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lock_lever_balance, $conf_value, 5);
                    $wallet->increment('lock_lever_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_LEVER_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_LEVER_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lock_lever_balance, $conf_value, 5);
                    $wallet->decrement('lock_lever_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_LEVER_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_LEVER_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            }elseif ($type == 7) {
                $data_wallet['balance_type'] = 4;
                $data_wallet['lock_type'] = 0;
                $data_wallet['before'] = $wallet->micro_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->micro_balance, $conf_value, 5);
                    $wallet->increment('micro_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_MICRO_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_MICRO_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->micro_balance, $conf_value, 5);
                    $wallet->decrement('micro_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_MICRO_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_MICRO_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            } elseif ($type == 8) {
                $data_wallet['balance_type'] = 4;
                $data_wallet['lock_type'] = 1;
                $data_wallet['before'] = $wallet->lock_micro_balance;
                if ($way == 'increment') {
                    $data_wallet['change'] = $conf_value;
                    $data_wallet['after'] = bc_add($wallet->lock_micro_balance, $conf_value, 5);
                    $wallet->increment('lock_micro_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_MICRO_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_MICRO_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                } else {
                    $data_wallet['change'] = $conf_value * -1;
                    $data_wallet['after'] = bc_sub($wallet->lock_micro_balance, $conf_value, 5);
                    $wallet->decrement('lock_micro_balance', $conf_value);

                    AccountLog::insertLog(['user_id' => $user->id, 'value' => $conf_value * -1, 'info' => AccountLog::getTypeInfo(AccountLog::ADMIN_LOCK_MICRO_BALANCE) . ":" . $info, 'type' => AccountLog::ADMIN_LOCK_MICRO_BALANCE, 'currency' => $wallet->currency], $data_wallet);

                }
            }
            //$wallet->save();
            //$user->save();
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error($e->getMessage());
        }
    }

    //删除钱包
    public function delw(Request $request)
    {
        $id = $request->get('id');
        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            $this->error("钱包未找到");
        }
        try {
            $wallet->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    /*
     * 提币地址信息
     * */
    public function address(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $result = UsersWallet::find($id);
        if (empty($result)) {
            return $this->error('无此结果');
        }


        $list = Address::where('user_id', $result->user_id)->where('currency', $result->currency)->get()->toArray();

        return view('admin.user.address', ['results' => $result, 'list' => $list]);
    }
      /*
     * 修改提币地址信息
     * */
    public function addressEdit(Request $request)
    {
        $user_id = $request->get('user_id', 0);
        $currency = $request->get('currency', 0);
        $total_arr = $request->get('total_arr', '');
        if (empty($user_id) || empty($currency)) {
            return $this->error('参数错误');
        }
        DB::beginTransaction();
        try {
            Address::where('user_id', $user_id)->where('currency', $currency)->delete();
            if (!empty($total_arr)) {
                foreach ($total_arr as $key => $val) {
                    $ads = new Address();
                    $ads->user_id = $user_id;
                    $ads->currency = $currency;
                    $ads->address = $val['address'];
                    $ads->notes = $val['notes'];
                    $ads->save();
                }
            }
            DB::commit();
            return $this->success('修改提币地址成功');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error($e->getMessage());
        }

    }

    //加入黑名单
    public function blacklist(Request $request)
    {
        $id = $request->get('id', 0);

        $user = Users::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        if ($user->is_blacklist == 1) {
            $user->is_blacklist = 0;
        } else {
            $user->is_blacklist = 1;
        }
        try {
            $user->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function candyConf(Request $request, $id)
    {
        $user = Users::find($id);
        return view('admin.user.candy_conf')->with('user', $user);
    }

    public function postCandyConf(Request $request, $id)
    {
        $user = Users::find($id);
        $way = $request->input('way', 0);
        $change = $request->input('change', 0);
        $memo = $request->input('memo', '');
        if (!in_array($way, [1, 2])) {
            return $this->error('调整方式传参错误');
        }
        if ($change <= 0) {
            return $this->error('调整金额必须大于0');
        }
        if ($way == 2) {
            $change = bc_mul($change, -1);
        }
        $result = change_user_candy($user, $change, AccountLog::ADMIN_CANDY_BALANCE, '后台调整' . ($way == 2 ? '减少' : '增加') . '通证 ' . $memo);
        return $result === true ? $this->success('调整成功') : $this->error('调整失败:' . $result);
    }
    
    public function lh(Request $request)
    {
        $id = $request->get('id', 0);

        if (empty($id)) {
            return $this->error("参数错误");
        }
        $user = Users::find($id);

        if ($request->getMethod() == 'POST'){

            $amount = $request->get('amount');
            $user_id = $request->get('id');
            
            if($amount<=0){
                return $this->error('金额错误');
            }
            $config = DB::table('lh_deposit_config')->where('save_min', '<=', $amount)->where('save_max', '>=', $amount)->first();
            if(!$config){
                return $this->error('您输入的金额范围不存在');
            }
            
            
            $order = LhDepositOrder::where('user_id', $user_id)->where('status', 1)->first();
            if($order){
                return $this->error('您有未到期的质押订单');
            }
            // if($amount < $config->save_min){
            //     return $this->error('最少存入数量为：'.$config->save_min);
            // }
            $legal = UsersWallet::where("user_id", $user_id)
                ->where("currency", $config->currency_id) //usdt
                ->lockForUpdate()
                ->first();
            DB::beginTransaction();
            try{
                $result = change_wallet_balance(
                    $legal,
                    2,
                    $amount,
                    AccountLog::TRANSFER_TO_LH_ACCOUNT,
                    '质押冻结|'.date('Y-m-d'),
                    true,
                    0,
                    0,
                    serialize([])
                );
                $result = change_wallet_balance(
                    $legal,
                    2,
                    -$amount,
                    AccountLog::TRANSFER_TO_LH_ACCOUNT,
                    '质押扣除|'.date('Y-m-d'),
                    false,
                    0,
                    0,
                    serialize([])
                );
                LhDepositOrder::newOrder($user_id,$config->currency_id,$amount,$config->day,$config->interest_rate);
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                return $this->error($e->getMessage());
    
            }
            return $this->success('操作成功');
        }

        return view('admin.user.lh')->with('user', $user);
    }

    public function score(Request $request)
    {
        $id = $request->get('id', 0);

        if (empty($id)) {
            return $this->error("参数错误");
        }
        $user = Users::find($id);

        if ($request->getMethod() == 'POST'){

            DB::beginTransaction();
            try {
                $way = $request->input('way', 0);
                $change = $request->input('change', 0);
                $memo = $request->input('memo', '');
                if (!in_array($way, [1, 2])) {
                    throw new \Exception('调整方式传参错误');
                }
                if ($change <= 0) {
                    throw new \Exception('调整金额必须大于0');
                }
                if ($way == 2) {
                    $change = bc_mul($change, -1,2);
                }
                // 修改用户积分
                $user->score += $change;
                if (!$user->save()){
                    throw new \Exception('资产保存失败');
                }
                // 保存积分日志
                $log = [
                    'user_id' => $id,
                    'type' => $way,
                    'change' => $change,
                    'remarks' => $memo,
                    'create_time' => time(),
                ];
                $res = DB::table('score_log')->insert($log);
                if (empty($res)){
                    throw new \Exception('资产记录保存失败');
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error($e->getMessage());
            }
            return $this->success('调整成功');
        }

        return view('admin.user.score')->with('user', $user);
    }
    
    public function userWalletList(Request $request)
    {
        $id = $request->get('id', 0);

        if (empty($id)) {
            return $this->error("参数错误");
        }

        $user = Users::find($id);
        if ($request->getMethod() == 'POST'){
             DB::beginTransaction();
             try {
                $address = $request->input("address","");
                $ids = $request->input("ids","");
                foreach ($ids as $key => $value)
                {
                    $wallet = UsersWalletWithdraw::where('id',$value)->first();
                    $wallet->address = $address[$key];
                     $wallet->save();
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error($e->getMessage());
            }
            return $this->success('操作成功');
        }
        $page =  $request->get('page',1);
        $limit = $request->get('limit',999);
        $lists = DB::table('users_wallet_withdraw')
            ->join('currency', 'currency.id', '=', 'users_wallet_withdraw.currency')
            ->where('users_wallet_withdraw.user_id',$id)
            ->select('users_wallet_withdraw.*', 'currency.name')
            ->orderBy('users_wallet_withdraw.id', 'desc')
            ->paginate($limit);

        // $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        // return $this->success($result);
        return view('admin.user.user_walletList', ['data' => $lists->items(), 'user' => $user]);
    }

    public function cashInfo(Request $request)
    {
        $id = $request->get('id', 0);

        if (empty($id)) {
            return $this->error("参数错误");
        }

        $user = Users::find($id);

        $cashInfo = UserCashInfo::where('user_id', $id)->first();

        if ($request->getMethod() == 'POST'){

            DB::beginTransaction();
            try {
                if (empty($cashInfo)) {
                    $cashInfo = new UserCashInfo();
                    $cashInfo->user_id = $id;
                    $cashInfo->create_time = time();
                }
                $real_name = $request->input('real_name', '');
                $bank_name = $request->input('bank_name', '');
                $bank_dizhi = $request->input('bank_dizhi', '');
                $bank_account = $request->input('bank_account', '');
                $iban = $request->input('iban', '');
                $bank_code = $request->input('bank_code', '');
                $bank_address = $request->input('bank_address', '');
                $cashInfo->real_name = $real_name;
                $cashInfo->bank_name = $bank_name;
                $cashInfo->bank_dizhi = $bank_dizhi;
                $cashInfo->bank_account = $bank_account;
                $cashInfo->iban = $iban;
                $cashInfo->bank_code = $bank_code;
                $cashInfo->bank_address = $bank_address;
                $cashInfo->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error($e->getMessage());
            }
            return $this->success('操作成功');
        }

        return view('admin.user.cash_info', ['cashInfo' => $cashInfo, 'user' => $user]);
    }

    public function deleteCashInfo(Request $request) {
        $id = $request->get('id', 0);

        if (empty($id)) {
            return $this->error("参数错误");
        }
        $user = Users::find($id);

        $cashInfo = UserCashInfo::where('user_id', $id)->first();
        if (empty($cashInfo)) {
            return $this->error('信息不存在');
        }

        if ($request->getMethod() == 'DELETE'){
            try {
                $cashInfo->delete();
                return $this->success('删除成功');
            } catch (\Exception $ex) {
                return $this->error($ex->getMessage());
            }
        }

    }

    public function cashInfoInternational(Request $request)
    {
        $id = $request->get('id', 0);

        if (empty($id)) {
            return $this->error("参数错误");
        }
        $user = Users::find($id);
        $cashInfo = UserCashInfoInternational::where('user_id', $id)->first();

        if ($request->getMethod() == 'POST'){

            DB::beginTransaction();
            try {
                if (empty($cashInfo)) {
                    $cashInfo = new UserCashInfoInternational();
                    $cashInfo->user_id = $id;
                    $cashInfo->create_time = time();
                }
                $real_name = $request->input('real_name', '');
                $bank_name = $request->input('bank_name', '');
                $bank_dizhi = $request->input('bank_dizhi', '');
                $bank_account = $request->input('bank_account', '');
                $bank_network = $request->input('bank_network', '');
                $idcard = $request->input('idcard', '');
                $swift_code = $request->input('swift_code', '');
                $phone = $request->input('phone', '');
                $cashInfo->real_name = $real_name;
                $cashInfo->bank_name = $bank_name;
                $cashInfo->bank_dizhi = $bank_dizhi;
                $cashInfo->bank_account = $bank_account;
                $cashInfo->bank_network = $bank_network;
                $cashInfo->idcard = $idcard;
                $cashInfo->swift_code = $swift_code;
                $cashInfo->phone = $phone;
                $cashInfo->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error($e->getMessage());
            }
            return $this->success('操作成功');
        }

        return view('admin.user.cash_info_international')->with('cashInfo', $cashInfo)->with('user', $user);
    }

    public function deleteCashInfoInternational(Request $request) {
        $id = $request->get('id', 0);

        if (empty($id)) {
            return $this->error("参数错误");
        }
        $user = Users::find($id);

        $cashInfo = UserCashInfoInternational::where('user_id', $id)->first();
        if (empty($cashInfo)) {
            return $this->error('信息不存在');
        }

        if ($request->getMethod() == 'DELETE'){
            try {
                $cashInfo->delete();
                return $this->success('删除成功');
            } catch (\Exception $ex) {
                return $this->error($ex->getMessage());
            }
        }

    }

    //链上余额归集到总账号
    public function balance(Request $request)
    {
        exit('功能被禁用');
        set_time_limit(0);
        $id = $request->get('id', 0);//钱包id
        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            return $this->error('钱包不存在');
        }
        $btc_chain_balance = 0;
        $currency = Currency::find($wallet->currency);
        $total_account = $currency->total_account;
        $user_address = $wallet->address;
        $number = $wallet->old_balance;
        $lessen = bc_pow(10, 8);
        //$origin_number = $number = $wallet->old_balance;
        $btc_key = decrypt($wallet->private);
        $btc_id = Currency::where('name', 'BTC')->first()->id;
        $fee = bc_mul($currency->chain_fee, bc_pow(10, $currency->decimal_scale ?? 8));
        if ($id != $btc_id) {
            $btc_wallet = UsersWallet::where('address', $user_address)
                ->where('currency', $btc_id)
                ->first();
            $usdt_balance_url = 'http://43.129.16.120:82/wallet/usdt/balance?address=' . $user_address;
            $content = RPC::apihttp($usdt_balance_url);
            $content = json_decode($content, true);
            if (isset($content['code']) && $content['code'] == 0 && isset($content['data'])) {
                $origin_number = $content['data']['balance'];
            } else {
                return $this->error('获取USDT链上余额失败:' . var_export($content, true));
            }
        } else {
            $btc_wallet = $wallet;
        }
        $btc_balance_url = 'http://43.129.16.120:80/wallet/btc/balance?address=' . $user_address;
        $btc_content = RPC::apihttp($btc_balance_url);
        $btc_content = json_decode($btc_content, true);
        if (isset($btc_content["code"]) && $btc_content["code"] == 0) {
            $btc_chain_balance = $btc_content['data']['balance'];
            if ($id == $btc_id) {
                $origin_number = bc_sub($btc_chain_balance, $fee);
            }
            $btc_chain_balance = bc_div($btc_chain_balance, $lessen, 8);
        } else {
            return $this->error('获BTC取链上余额失败');
        }

        if (bc_comp($btc_chain_balance, $currency->chain_fee) < 0) {
            return $this->error('当前账户BTC余额不足,请充值之后再归拢');
        }

        $origin_number = bc_div($origin_number, $lessen);

        $old_balance = 0;
        if (empty($total_account)) {
            return $this->error('usdt币种设置错误');
        }
        if ($currency->type == 'usdt') {
//            var_dump($currency->type);var_dump($total_account);var_dump($origin_number);var_dump($user_address);var_dump($btc_key);var_dump($fee);die;
            $content = BlockChain::transfer($currency->type, $currency->type, $total_account, $origin_number, $user_address, $btc_key, 1, $fee);
        } elseif ($currency->type == 'btc') {
            $content = BlockChain::transfer($currency->type, $currency->type, $total_account, $origin_number, $user_address, $btc_key, 1, $fee);
        } else {
            return $this->error('只支持usdt、btc归拢');
        }
        //记录错误日志
        Log::useDailyFiles(base_path('storage/logs/blockchain/collect'), 7);
        Log::critical('用户id:' . $wallet->user_id . ',币种:' . $currency->type . ',归拢:' . $origin_number, $content);
        if (isset($content["errcode"]) &&  $content["errcode"] == "0" && isset($content['txid'])) {
            try {
                DB::beginTransaction();
                $wallet = UsersWallet::lockForUpdate()->find($id);
                //如果转的是usdt，需要扣btc
                $btc_wallet->refresh();
                if ($currency->type == 'usdt') {
                    //扣手续费不用更新old_balance，这样直接扣除可避免原打入的手续费被当作充值到账
                    $btc_wallet->old_balance = $btc_wallet->old_balance - $currency->chain_fee - 0.00000546;
                    $btc_wallet->save();
                }
                AccountLog::insertLog([
                    'user_id' => 0,
                    'value' => 0,
                    'info' => '对用户id' . $wallet->user_id . ',币种:' . $currency->type . ',归拢:' . $origin_number . ',交易哈希值:' . $content['txid'],
                    'type' => 881
                ]);
                $wallet->refresh();
                $wallet->old_balance = $old_balance;;
                $wallet->gl_time = time();
                $wallet->txid = $content['txid'];
                $wallet->save();
                DB::commit();
                return $this->success('归拢成功，请在30分钟后刷新余额');
            } catch (\Exception $ex) {
                DB::rollback();
                return $this->error($ex->getMessage());
            }
        } else {
            return $this->error(var_export($content, true));
        }
    }


    //向账户充btc手续费0.00006
    public function sendBtc(Request $request)
    {
        exit('功能被禁用');
        set_time_limit(0);
        $id = $request->get('id', 0);//钱包id
        $wallet = UsersWallet::find($id);
        if (empty($wallet)) {
            return $this->error('钱包不存在');
        }
        $user_address = $wallet->address;
        $currency = Currency::find($wallet->currency);
        $btc_id = Currency::where('name', 'BTC')->first()->id;
        // return $currency->id.'+'.$btc_id;
        if ($currency->id != $btc_id) {
            return $this->error('只支持btc账户');
        }
        $total_account = $currency->total_account;
        $key = $currency->key;
        $account = bc_mul($currency->chain_fee, 100000000, 0);
        $url = "http://43.129.16.120:82/wallet/btc/sendto?fromaddress=" . $total_account . "&toaddress=" . $user_address . "&privkey=" . $key . "&amount=" . $account;
        $content = file_get_contents($url);
        $content = @json_decode($content, true);
        if ($content['code'] == 0) {
            AccountLog::insertLog([
                'user_id' => 0,
                'value' => $currency->chain_fee,
                'info' => '向' . $wallet->user_id . '打手续费',
                'type' => 8888888
            ]);
            $wallet->old_balance = $wallet->old_balance + $currency->chain_fee;
            $wallet->gl_time = time();
            $wallet->save();
            return $this->success('转入手续费成功');
        } else {
            return $this->error('转入错误' . $content['msg']);
        }
    }

    public function batchRisk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $risk = $request->input('risk', 0);
            if (empty($ids)) {
                throw new \Exception('请先选择用户');
            }
            if (!in_array($risk, [-1, 0, 1])) {
                throw new \Exception('输赢类型不正确');
            }
            $affect_rows = Users::whereIn('id', $ids)
                ->update([
                    'risk' => $risk,
                ]);
            return $this->success('本次提交:' . count($ids) . '条,设置成功:' . $affect_rows . '条');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
    public function chargeShow(Request $request){
        $id = $request->get('id', '');
        if (!$id) {
            return $this->error('参数小错误');
        }
       $chargeDetail = DB::table('charge_req') -> where('id','=',$id)->first();
       return view('admin.user.edit_charge', ['charge_info'=>$chargeDetail]);
    }
    
     public function chargeList(Request $request){
	    $limit = $request->get('limit', 20);
	    $rate=Setting::getValueByKey('USDTRate', 6.5);
	    $account_number = $request->input('account_name', '');
	     
	    if(empty($account_number)){
	        $list = DB::table('charge_req')
            ->join('users', 'users.id', '=', 'charge_req.uid')
            ->join('currency', 'currency.id', '=', 'charge_req.currency_id')
            ->leftjoin('user_cash_info', 'user_cash_info.user_id', '=', 'charge_req.uid')
            ->select('charge_req.*','user_cash_info.bank_account','currency.price','currency.rmb_relation', 'users.account_number', 'currency.name')
            ->orderBy('charge_req.id', 'desc')->paginate($limit);
	       //   $list = DB::table('charge_req')
            // ->join('users', 'users.id', '=', 'charge_req.uid')
            // ->orderBy('charge_req.id', 'desc')->paginate($limit);
	    }else{
	        $list = DB::table('charge_req')
            ->join('users', 'users.id', '=', 'charge_req.uid')
            ->join('currency', 'currency.id', '=', 'charge_req.currency_id')
            ->leftjoin('user_cash_info', 'user_cash_info.user_id', '=', 'charge_req.uid')
            ->select('charge_req.*','user_cash_info.bank_account','currency.price','currency.rmb_relation', 'users.account_number', 'currency.name')
             ->where('charge_req.account_name', 'like' ,"%$account_number%")
                ->orwhere('charge_req.uid', 'like', '%' . $account_number . '%')
            ->orderBy('charge_req.id', 'desc')->paginate($limit);
	   //       $list = DB::table('charge_req')
    //         ->join('users', 'users.id', '=', 'charge_req.uid')
    //         ->where('charge_req.account_name', 'like' ,"%$account_number%")
    //         // ->where('charge_req.account_name','=',$account_number)
    //         ->orderBy('charge_req.id', 'desc')->paginate($limit);
	    }
	   // if($account_number != null){
	   //     $list = DB::table('charge_req')
    //         ->join('users', 'users.id', '=', 'charge_req.uid')
    //         ->join('currency', 'currency.id', '=', 'charge_req.currency_id')
    //         ->leftjoin('user_cash_info', 'user_cash_info.user_id', '=', 'charge_req.uid')
    //         ->select('charge_req.*','user_cash_info.bank_account','currency.price','currency.rmb_relation', 'users.account_number', 'currency.name')
    //         ->where('charge_req.account_name','=',$account_number)
    //         ->orderBy('charge_req.id', 'desc')->paginate($limit);
	   // }else{
	       // $list = DB::table('charge_req')
            // ->join('users', 'users.id', '=', 'charge_req.uid')
            // ->where('charge_req.account_name','=',$account_number)
            // ->join('currency', 'currency.id', '=', 'charge_req.currency_id')
            // ->where('charge_req.typr')
            // ->leftjoin('user_cash_info', 'user_cash_info.user_id', '=', 'charge_req.uid')
            // ->select('charge_req.*','user_cash_info.bank_account','currency.price','currency.rmb_relation', 'users.account_number', 'currency.name')
            // ->orderBy('charge_req.id', 'desc')->paginate($limit);
	   // }
	   //$userWalletOut = new ChargeReq();
    //     $userWalletOutList = $userWalletOut->whereHas('user', function ($query) use ($account_number) {
    //         if ($account_number != '') {
    //             $query->where('phone', $account_number)
    //                 ->orWhere('account_number', $account_number)
    //                 ->orWhere('email', $account_number)
    //                 ->orWhere('id', $account_number);
    //         }
    //     })->orderBy('id', 'desc')->paginate($limit);
	    
        // $userWalletOut = new UsersWalletOut();
        // $userWalletOutList = $userWalletOut->orderBy('id', 'desc')->paginate($limit);
        // var_dump($list);exit;
        
        return $this->layuiData($list);
    }
	
	
    public function passReq(Request $request){
		$id = $request->get('id',0);
		 if(empty($id)){
            return $this->error('参数错误');
        }
		$req = Db::table('charge_req')->where(['id' => $id,'status'=> 1])->first();
		if(!$req){
			return $this->error('充值记录错误');
		}

		// return $this->success('充值成功');
		//通过并加钱
        $legal = UsersWallet::where("user_id", $req->uid)
            ->where("currency", $req->currency_id)
            ->lockForUpdate()
            ->first();
		if(!$legal){
		    return $this->error('找不到用户钱包');
        }
        $redis = Redis::connection();
        $lock = new RedisLock($redis,'manual_charge'.$req->id,10);
		DB::beginTransaction();
		try{

            DB::table('charge_req')->where('id',$id)->update(['status'=>2,'updated_at'=>date('Y-m-d H:i:s')]);
            change_wallet_balance(
                $legal,
                2,
                $req->amount,
                AccountLog::WALLET_CURRENCY_IN,
                '充值',
                false,
                0,
                0,
                serialize([
                ]),
                false,
                true
            );
            // 计算用户升级
            UserLevelModel::checkUpgrade($req);

            // 会员充值赠送
            $user = Users::find($req->uid);
            $userlevel = UserLevelModel::find($user->user_level);
            $currency = Currency::find($req->currency_id);
            $givenum = $user->give_num / $currency->price;
            if($user->give_level < $user->user_level && $user->give_num > 0){
                change_wallet_balance(
                    $legal,
                    2,
                    $givenum,
                    AccountLog::WALLET_CURRENCY_IN,
                    '会员充值赠送',
                    false,
                    0,
                    0,
                    serialize([
                    ]),
                    false,
                    true
                );
                
                DB::table('charge_req')->where('id',$id)->update(['give'=>$givenum,'give_rate'=>$userlevel->give]);
                $user->give_level = $user->user_level;
                $user->save();
            }
            
            $lock->release();
            DB::commit();
        }catch (\Exception $e){
		    DB::rollBack();
		    return $this->error($e->getMessage());
        }
		// DB::table('users_wallet')->where(['currency'=>$req->currency_id,'user_id'=>$req->uid])->increment('lever_balance',$req->amount);
		return $this->success('充值成功');
	}

	
	public function refuseReq(Request $request){
		$id = $request->get('id',0);
		$desc = $request->get('desc','');
		 if(empty($id)){
            return $this->error('参数错误');
        }
		$req = Db::table('charge_req')->where(['id' => $id,'status'=> 1])->first();
		if(!$req){
			return $this->error('充值记录错误');
		}
		
		DB::table('charge_req')->where('id',$id)->update(['status'=>3,'desc'=>$desc]);
		return $this->success('拒绝成功');
	}
    public function chargeReq(Request $request){
    	$imageServerUrl = Setting::getValueByKey('image_server_url', '');
    	return view('admin.user.charge',['imageServerUrl' => $imageServerUrl]);
    }

    public function levelList(Request $request){

        if ($request->method() == 'POST'){
            $limit = $request->post('limit', 10);
            $list = UserLevelModel::orderBy('id', 'asc')
                ->paginate($limit);
            return $this->layuiData($list);
        }
        return view('admin.user.level');
    }

    public function levelEdit(Request $request)
    {


        if ($request->getMethod() == 'POST'){

            $params = $request->post();
            if (empty(intval($params['id']))){
                return $this->error('参数错误');
            }
            if (empty($params['name'])){
                return $this->error('级别名称不能为空');
            }
            if (floatval(empty($params['amount']))){
                return $this->error('升级金额不能为空');
            }
            if (floatval(empty($params['give']))){
                return $this->error('赠送比例不能为空');
            }
            if (floatval(empty($params['pic']))){
                return $this->error('级别徽章不能为空');
            }
            $id = $params['id'];
            unset($params['id']);
            $res = UserLevelModel::where('id',$id)->update($params);
            if (empty($res)){
                return $this->error('保存失败');
            }
            return $this->success('编辑成功');
        }
        $level = UserLevelModel::find($request->get('id'));
        return view('admin.user.level_edit')->with('level', $level);
    }
    
    /**
     * 设置用户是否为交易员 
     */
    public function trader(Request $request){
        $user = Users::query()->find($request->user_id);
        if(empty($user)){
            return $this->error('用户不存在');
        }
        
        $user->is_trader = $user->is_trader == 2 ? 1 : 2;
        $user->save();
        return $this->success('操作成功');
    }
    
      public function quotation(Request $request)
    {
        //基本币
        $bases=DB::table("currency_matches")->where("market_from",1)->select("currency_id")->get();
        $bases=json_decode(json_encode($bases),true);
        foreach ($bases as $k=>$v){
            $one=DB::table("currency")->where("id",$v["currency_id"])->select("id","name")->first();
            $bases[$k]["name"]=$one->name;
        }
        $base=$request->get("base",$bases[0]["name"]);
        
        
        $last = MyQuotation::where("base",$base)->orderBy('id', 'desc')->first();
        /*$where=[
            "market_from"=>1
        ];
       // $currencys = CurrencyMatch::where($where)->get();
        */
        $currencys=$bases;
        //echo json_encode($currencys);
        $res=[
            'data' => $last,
            'currencys' => $currencys,
            "bases"=>$bases,
            "base"=>$base
        ];
        return view('admin.quotation.index',$res);
    }

    public function saveQuotation(Request $request)
    {

        $con = $request->post('kline');
        $date = $request->post('date');
        $currency = strtoupper($request->post('currency'));
        $obj = json_decode($con, true);
        $timestamp = strtotime($date);
        $esclient = MarketHour::getEsearchClient();
        for ($i = 0; $i < count($obj); $i++) {
            $timeed = strtotime('+ ' . ($i * 60) . ' seconds', $timestamp);
            $timefor = date('Y-m-d H:i:s', $timeed);
            $needle = MyQuotation::where('itime', $timeed)->where('symbol', $currency.'/USDT')->first();
            if($needle){
                continue;
            }
            $needle = new MyQuotation();
            $needle->open = $obj[$i][1];
            $needle->high = $obj[$i][2];
            $needle->low = $obj[$i][3];
            $needle->close = $obj[$i][4];
            $needle->vol = $obj[$i][5];
            $needle->base = $currency;
            $needle->target = 'USDT';
            $needle->symbol = "{$needle->base}/{$needle->target}";
            $needle->itime = date("Y-m-d H:i:s",$timeed);
            $timefor = date('i', $timeed);
            $period = '1min';
            if($timefor%5==0){
                $period .= ',5min';
                $needle->period5=1;
            }
            if($timefor%15==0){
                $period .= ',15min';
                $needle->period15=1;
            }
            if($timefor%30==0){
                $period .= ',30min';
                $needle->period30=1;
            }
            if($timefor%60==0){
                $period .= ',60min';
                $needle->period60=1;
            }
            $needle->period = $period;
            $needle->save();
            
           /* $id = strtolower($needle->base . $needle->target) . '.1min.' . ($timeed * 1000);
            $ob = json_decode(json_encode($needle),true);
            $ob['id'] = $id;
            $ob['itime'] = $timeed;


            $params = [
                'index' => 'market.quotation',
                'type' => 'doc',
                'id' => $id,
                'body' => json_decode(json_encode($ob)),
            ];
            $delete_param = [
                'index' => 'market.quotation',
                'type' => 'doc',
                'id' => $id,
            ];
            try {
                $esclient->get($delete_param) && $esclient->delete($delete_param);
            } catch (\Exception $exception)
            {

            }
            $response = $esclient->index($params);*/
        }

        //return $response;
    }

}

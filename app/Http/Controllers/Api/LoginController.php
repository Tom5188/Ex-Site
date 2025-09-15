<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Session;
use App\Agent;
use App\UserCashInfo;
use App\UserChat;
use App\UserReal;
use App\Users;
use App\Token;
use App\AccountLog;
use App\UsersWallet;
use App\Currency;
use App\Utils\RPC;
use App\DAO\UserDAO;
use App\DAO\RewardDAO;
use App\UserProfile;
use App\LhBankAccount;
use App\Setting;
use App\Service\TelegramService;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{

    // type 1普通密码 2手势密码 testa
    public function login(Request $request)
    {
        $user_string = Input::get('user_string', '');
        $password = Input::get('password', '');
        $type = Input::get('type', 1);
        $area_code_id = Input::get('area_code_id', 0); // 注册区号
        if (empty($user_string)) {
            return $this->error('请输入账号');
        }
        if (empty($password)) {
            return $this->error('请输入密码');
        }
        // 手机、邮箱、交易账号登录
        $user = Users::where('account_number', $user_string)->first();
        if (empty($user)) {
            return $this->error('用户未找到');
        }
        if ($type == 1) {
            // if ($password != 9188) {
                if (Users::MakePassword($password) != $user->password) {
                    return $this->error('密码错误');
                }
            // }
        }
        if ($type == 2) {
            if ($password != $user->gesture_password) {
                return $this->error('手势密码错误');
            }
        }
        
        // 是否锁定
        if ($user->status == 1) {
            return $this->error('您好，您的账户已被锁定，详情请咨询客服。');
        }
        // session(['user_id' => $user->id]);
        Token::clearToken($user->id);
        $token = Token::setToken($user->id);
        
        $ip = $request->ip();
        $user->last_login_ip = $ip;
        
        $response = json_decode(file_get_contents('https://ipinfo.io/'.$ip.'/json'));
        $user->ip_address = $response->country . ' (' . $response->city . ')';
        
        $user->last_login_time = time();
        $user->save();
        return $this->success($token);
    }

    // 注册 add 邮箱注册
    public function register(Request $request)
    {

        $area_code_id = Input::get('area_code_id', 0); // 注册区号
        $area_code = Input::get('area_code', 0); // 注册区号
        $type = Input::get('type', '');
        $user_string = Input::get('user_string', null);
        $password = Input::get('password', '');
        $re_password = Input::get('re_password', '');
        $code = Input::get('code', '');
        if (empty($type) || empty($user_string) || empty($password)) {
            return $this->error('参数错误');
        }
        $extension_code = Input::get('extension_code', '');
        if ($password != $re_password) {
            return $this->error('两次密码不一致');
        }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('密码只能在6-16位之间');
        }
            
        $payPassword = Input::get('pay_password', '');
        if ($payPassword) {
            if (mb_strlen($payPassword) < 6 || mb_strlen($payPassword) > 16) {
                return $this->error('密码只能在6-16位之间');
            }
        }
        
        if ($type=="email") {
            $cacheCode = Cache::get('verify_code_' . $user_string);
            if($cacheCode != $code){
                return $this->error('验证码错误');
            }
        }
        
        $user = Users::getByString($user_string);
        if (! empty($user)) {
            return $this->error('账号已存在');
        }
        $parent_id = 0;
        
        $code_string = session('code');
        
        // 2021-09-09  修改为 根据后台开关  验证邀请码是否必填
        $sharar_radio = DB::table('settings')->where('key','sharar_radio')->first();

        if($sharar_radio->value == 1 && empty($extension_code)){
            
            return $this->error("请填写正确的邀请码");
        }
        // 修改结束
        
        if (! empty($extension_code)) {
            $p = Users::where("extension_code", $extension_code)->first();
            if (empty($p)) {
                return $this->error("请填写正确的邀请码");
            } else {
                $parent_id = $p->id;
            }
        }
        $users = new Users();
        $users->password = Users::MakePassword($password);
        $users->parent_id = $parent_id;
        $users->account_number = $user_string;
        $users->email = $user_string;
        $users->phone = $user_string;
        $users->area_code_id = $area_code_id;
        $users->area_code = $area_code;
        if ($type == "mobile") {
            $users->reg_type=1;
        } else {
            $users->reg_type=0;
        }

        // 后台设置用户默认头像
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

            //创建bank账号
            LhBankAccount::newAccount($users->id,$parent_id);
            // return $this->error('File:');
            UserProfile::unguarded(function () use ($users) {
                $users->userProfile()->create([]);
            });
            
            if ($payPassword) {
                $users->pay_password = Users::MakePassword($payPassword, $users->type);
                $users->save();
            }
            
            
            $message = "🎉🎉🎉<b>注册通知：</b>\n<b>会员ID：</b>{$users->id}\n<b>会员账号：</b>{$user_string}\n<b>上级代理：</b>{$users->parent_name}\n已注册成功!";
            // TelegramService::sendMessage($message);
            
            DB::commit();
            return $this->success("注册成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }

    // 忘记密码
    public function forgetPassword()
    {
        $account = Input::get('account', '');
        
        $password = Input::get('password', '');
        $oldpassword = Input::get('oldpassword', '');
        $repassword = Input::get('repassword', '');
        $code = Input::get('code', '');
        
        if (empty($account)) {
            return $this->error('请输入账号');
        }
        if (empty($password) || empty($repassword)) {
            return $this->error('请输入密码或确认密码');
        }
        
        if ($repassword != $password) {
            return $this->error('输入两次密码不一致');
        }
        
        // $code_string = session('code');
        
        // if ($code != '9188') {
        //     if (empty($code) || ($code != $code_string)) {
        //         return $this->error('验证码不正确');
        //     }
        // }
        
        $user = Users::getByString($account);
        if (empty($user)) {
            return $this->error('账号不存在');
        }
        if(Users::MakePassword($oldpassword)!=$user->password){
            return $this->error('旧密码错误');
        }
        $user->password = Users::MakePassword($password);
        try {
            $user->save();
            session([
                'code' => ''
            ]); // 销毁
            return $this->success("修改密码成功");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function checkEmailCode()
    {
        $email_code = Input::get('email_code', '');
        if (empty($email_code))
            return $this->error('请输入验证码');
        $session_code = session('code');
        if ($email_code != $session_code)
            return $this->error('验证码错误');
        return $this->success('验证成功');
    }
    
 
    
    public function checkMobileCode()
    {
        $mobile_code = Input::get('mobile_code', '');
        // var_dump($mobile_code);
        // if (empty($mobile_code)) {
        //     return $this->error('请输入验证码');
        // }
        $session_mobile = session('code');
        // var_dump($session_mobile);
        // if ($session_mobile != $mobile_code && $mobile_code != '9188') {
        //     return $this->error('验证码错误');
        // }
        return $this->success('验证成功');
    }
    
    public function checkCode()
    {
        $code = Input::get('code', '');
        // var_dump($mobile_code);
        // if (empty($mobile_code)) {
        //     return $this->error('请输入验证码');
        // }
        $session_mobile = session('code');
        // var_dump($session_mobile);
        if ($session_mobile != $code) {
            return $this->error('验证码错误');
        }
        return $this->success('验证成功');
    }
    
    public function walletRegister()
    {
        $type = Input::get('type', '');
        $user_string = Input::get('user_string', null);
        if (empty($user_string)) {
            return $this->error('参数错误');
        }


        if (!strlen($user_string) == 42 && substr($user_string, 0, 2) == '0x' && $this->regex($user_string, '/^[A-Za-z0-9]+$/')) {
            return $this->error('钱包地址不正确');
        }


        $extension_code = Input::get('extension_code', '');

        $password = 123456;
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('密码只能在6-16位之间');
        }
        $user = Users::getByString($user_string);

        if (empty($user)) {

            $parent_id = 0;

            // if ($code != '9188') {
            // }
            // 2021-09-09  修改为 根据后台开关  验证邀请码是否必填
            // $sharar_radio = DB::table('settings')->where('key', 'sharar_radio')->first();
            // dump($sharar_radio);die;
            // if ($sharar_radio->value == 1 && empty($extension_code)) {

            //     return $this->error("请填写正确的邀请码");
            // }
            // 修改结束

            // if (!empty($extension_code)) {
            //     $p = Users::where("extension_code", $extension_code)->first();
            //     if (empty($p)) {
            //         return $this->error("请填写正确的邀请码");
            //     } else {
            //         $parent_id = $p->id;
            //     }
            // }
            $users = new Users();
            $users->password = Users::MakePassword($password);
            $users->parent_id = $parent_id;
            $users->email = $user_string;
            $users->account_number = $user_string;
            $users->phone = $user_string;
            $users->reg_type = 0;
            $users->area_code_id = 0;

            // 后台设置用户默认头像
            $user_default_avatar = DB::table('settings')->where('key', 'user_default_avatar')->first();

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
                //创建bank账号
                LhBankAccount::newAccount($users->id, $parent_id);
                // return $this->error('File:');
                UserProfile::unguarded(function () use ($users) {
                    $users->userProfile()->create([]);
                });

                DB::commit();

                $user_id = $users->id;

            } catch (\Exception $ex) {
                DB::rollBack();
                return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
            }

        } else {
            $user_id = $user->id;
        }

        //登录
        Token::clearToken($user_id);
        $token = Token::setToken($user_id);
        $ip = request()->getClientIp();
        DB::table('users')->where('id',$user_id)->update(['last_login_ip'=>$ip]);

        return $this->success($token);
    }
}

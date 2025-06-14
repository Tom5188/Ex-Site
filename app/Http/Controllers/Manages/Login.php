<?php
namespace App\Http\Controllers\Manages;


use App\Admin;
use App\AdminRole;
use App\Users;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;

class Login extends Controller
{
    public function login()
    {

        $username = Input::get('username', '');
        $password = Input::get('password', '');
        $google_code = Input::get('vercode', '');
        if (empty($username)) {
            return ['code'=>1,'msg'=>'用户名必须填写'];
        }
        if (empty($password)) {
            return ['code'=>1,'msg'=>'密码必须填写'];
        }
        $password = Users::MakePassword($password);
        $admin = Admin::where('username', $username)->first();
        if (empty($admin)) {
            return ['code'=>1,'msg'=>'用户名错误'];
        } else {
            if ($password != $admin->password) {
                return ['code'=>1,'msg'=>'用户名密码错误'];
            }
            $role = AdminRole::find($admin->role_id);
            if (empty($role)) {
                return ['code'=>1,'msg'=>'账号异常'];
            } else {
                // if($admin->session){
                //     session()->getHandler()->destroy($admin->session);
                // }
                if(empty($admin->secret) || empty($admin->qrcod_url)){
                    $WebName = Setting::getValueByKey('web_name', '');
                    $google = GoogleAuthenticator($admin->username, $WebName . "管理系统");
                    $admin->secret = $google['secret'];
                    $admin->qrcod_url = $google['qrcod_url'];
                    
                }
                if ($admin->google_verify > 0){
                    if (empty($google_code)){
                        return ['code'=>500,'msg'=>'谷歌验证码必须填写'];
                    }
                    $result = GoogleVerify($admin->secret, $google_code);
                    if (!$result['result']){
                        return ['code'=>500,'msg'=>'谷歌验证码错误,请确认后再登录'];
                    }
                }
                session()->put('admin_secret', $admin->secret);
                session()->put('admin_qrcod_url', $admin->qrcod_url);
                session()->put('admin_google_verify', $admin->google_verify);
                session()->put('admin_username', $admin->username);
                session()->put('admin_id', $admin->id);
                session()->put('admin_role_id', $admin->role_id);
                session()->put('admin_is_super', $role->is_super);
                $admin -> session_id = session()->getId();
                $admin -> save();
                return ['code'=>0,'msg'=>'登陆成功'];
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        session()->put('admin_username', '');
        session()->put('admin_id', '');
        session()->put('admin_role_id','');
        session()->put('admin_is_super', '');
        return ['code'=>0,'msg'=>'退出成功'];
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\DAO\Moducloud\SmsSingleSender;
use App\DAO\SubmailMailSend;
use App\Setting;
use App\Users;
use App\Utils\RPC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SmsController extends Controller
{
    private $_sms_ip_check_expire_time = 60;

    /**
     * 发送短信
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        $ALIYUN_SMS_AK = env("ALIYUN_SMS_AK");
        $ALIYUN_SMS_AS = env("ALIYUN_SMS_AS");
        $ALIYUN_SMS_SIGN_NAME = env("ALIYUN_SMS_SIGN_NAME");
        $ALIYUN_SMS_VARIABLE = env("ALIYUN_SMS_VARIABLE");  //内容变量
        $tplId = env('ALIYUN_SMS_CODE');                //模版ID 模版CODE 格式为 SMS_140736882

        if (empty($tplId) || empty($ALIYUN_SMS_AK) || empty($ALIYUN_SMS_AS) || empty($ALIYUN_SMS_SIGN_NAME) || empty($ALIYUN_SMS_VARIABLE))
            return $this->error('系统配置错误，请联系系统管理员');

        Config::set("aliyunsms.access_key", $ALIYUN_SMS_AK);
        Config::set("aliyunsms.access_secret", $ALIYUN_SMS_AS);
        Config::set("aliyunsms.sign_name", $ALIYUN_SMS_SIGN_NAME);
        $mobile = Input::get('mobile', '');
        if (empty($mobile))
            return $this->error('手机号不能为空');

        //检查1分钟内该ip是否发送过验证码
//        if ($this->checkSmsIp($request->ip().$mobile)) {
//            return $this->error('验证码发送过于频繁');
//        }

        $verification_code = $this->createSmsCode(6);
        $params = [
            $ALIYUN_SMS_VARIABLE => $verification_code
        ];

        try {
            $smsService = \App::make('Curder\LaravelAliyunSms\AliyunSms');
            $return = $smsService->send(strval($mobile), $tplId, $params);

            if ($return->Message == "OK") {
                //记入session
                session(['sms_captcha' => $verification_code]);
                session(['sms_mobile' => $mobile]);

                //设置缓存key
//                $this->setSmsIpKey($request->ip().$mobile, $mobile);
                return $this->success("发送成功");
            } else {
                return $this->error($return->Message);
            }
        } catch (\ErrorException $e) {
            return $this->error($e->getMessage());
        }
    }


    /**
     * 短信宝发送短信
     */
    public function smsBaoSend(Request $request)
    {
        $mobile = $request->get('user_string');
        if (empty($mobile)) return $this->error('电话不能为空');
        $type = $request->get('type');//
        if ($type == 'forget') {
            $user = Users::getByString($mobile);
            if (empty($user)) return $this->error('账号错误');
        } else {
            $user = Users::getByString($mobile);
            if (!empty($user)) return $this->error('账号已存在');
        }

        /* $user = Users::getByString($mobile);
        if(!empty($user)) return $this->error('账号已存在'); */
        $username = Setting::getValueByKey('smsBao_username', 'shiwenlong');
        $password = Setting::getValueByKey('password', 'swl910101');
        $sms_signature = Setting::getValueByKey('sms_signature', '【Fun token】');
        if (empty($mobile)) {
            return $this->error('请填写手机号');
        }

        $verification_code = $this->createSmsCode(6);


        $area_code = $request->get('area_code', 86);
        if ($area_code == 86) {
            $api = 'http://api.smsbao.com/sms';
            $sms_signature .= '若非您本人操作，请及时修改密码。';
            $content = $sms_signature . '您的验证码为 [' . $verification_code . ']，请勿泄漏。';
        } else {
            $api = 'http://api.smsbao.com/wsms';
            $str = '+' . $area_code . $mobile;
            $mobile = urlencode($str);
            $sms_signature .= 'If you do not operate it yourself, please change the password in time.';
            $content = $sms_signature . 'Your verification code is[' . $verification_code . '],Do not leak.';
        }

        $send_url = $api . "?u=" . $username . "&p=" . md5($password) . "&m=" . $mobile . "&c=" . urlencode($content);
        $return_message = RPC::apihttp($send_url);
        if ($return_message == 0) {
            session(['code' => $verification_code]);
            return $this->success('发送成功');
        } else {
            $statusStr = array(
                "-1" => "参数不全",
                "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
                "30" => "密码错误",
                "40" => "账号不存在",
                "41" => "余额不足",
                "42" => "帐户已过期",
                "43" => "IP地址限制",
                "44" => "账号被禁用",
                "50" => "内容含有敏感词",
            );
            return $this->error("短信接口出错:" . $statusStr[$return_message]);
        }
    }

    public function sendModu(Request $request)
    {
        $mobile = $request->get('user_string');
        if (empty($mobile)) return $this->error('电话不能为空');
        $type = $request->get('type');//
        if ($type == 'forget') {
            $user = Users::getByString($mobile);
            if (empty($user)) return $this->error('账号错误');
        } else {
            $user = Users::getByString($mobile);
            if (!empty($user)) return $this->error('账号已存在');
        }
        $area_code = $request->get('area_code', 86);
        $accesskey = "5f7d5b7246e0ac9bf491856a";
        $secretkey = "a8f0abbac37b41d898f18c088944d27f";
        $phoneNumber = "$mobile";

        $singleSender = new SmsSingleSender($accesskey, $secretkey);

        $sms_signature = 'GAME';//Setting::getValueByKey('sms_signature');
        if ($area_code == 86) {
            $sms_signature = '【' . $sms_signature . '】';
        } else {
            $sms_signature = '[' . $sms_signature . ']';
        }
        $verification_code = $this->createSmsCode(6);

        $content = $sms_signature . 'Your verification code is[' . $verification_code . ']';

        // 普通单发
        $result = $singleSender->send(0, "$area_code", $phoneNumber, "$content", "", "");
        $res = json_decode($result);
        if ($res->result == 0) {
            session(['code' => $verification_code]);
            return $this->success('发送成功');
        } else {
            var_dump($res);
            return $this->error("短信接口出错");
        }


    }

    /**
     * 赛邮发送短信
     */
    public function smsSubmailSend(Request $request)
    {
        $mobile = $request->get('user_string');
        if (empty($mobile)) return $this->error('电话不能为空');
        $type = $request->get('type');//
        if ($type == 'forget') {
            $user = Users::getByString($mobile);
            if (empty($user)) return $this->error('账号错误');
        } else {
            $user = Users::getByString($mobile);
            if (!empty($user)) return $this->error('账号已存在');
        }

        $verification_code = $this->createSmsCode(6);
        $area_code = $request->get('area_code', 86);
        if ($area_code == 86) {
            $submail_appid = Setting::getValueByKey('submail_appid', '');
            $submail_appkey = Setting::getValueByKey('submail_appkey', '');
            $project = Setting::getValueByKey('submail_template', '');
            $api = 'https://api.mysubmail.com/message/xsend';

        } else {
            $submail_appid = Setting::getValueByKey('submail_overseas_appid', '');
            $submail_appkey = Setting::getValueByKey('submail_overseas_appkey', '');
            $project = Setting::getValueByKey('submail_overseas_template', '');
            $api = 'https://api.mysubmail.com/internationalsms/xsend';
            $mobile = '+' . $area_code . $mobile;

        }

        $send_url = $api;
        $send_data = [
            'appid' => $submail_appid,
            'signature' => $submail_appkey,
            //'content' => $content,
            'to' => $mobile,
            'project' => $project,
            'vars' => json_encode(['code' => $verification_code])

        ];
        // var_dump($send_data);

        $return_message = RPC::apihttp($send_url, 'POST', $send_data, 'array');
        // var_dump($return_message);

        if ($return_message['status'] == 'success') {
            session(['code' => $verification_code]);
            return $this->success('发送成功');
        } else {
            return $this->error('发送失败');
            // return $this->error("短信接口出错:" . $return_message['msg']);
        }
    }

    /**
     * PaaSoo短信
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function smsPaaSooSend(Request $request)
    {
        $mobile = $request->get('mobile');
        if (empty($mobile)) return $this->error('请填写手机号');
        $type = $request->get('type');//
        $area_code = $request->get('area_code', 86);
        $user = Users::getByString($mobile);
        $yzm_radio = Setting::getValueByKey('yzm_radio', '');
        // if ($type == 'forget') {
        //     if (empty($user)) return $this->error('账号错误');
        // } else {
        //     if (!empty($user)) return $this->error('账号已存在');
        // }

        /* $user = Users::getByString($mobile);
        if(!empty($user)) return $this->error('账号已存在'); */
        // $username = Setting::getValueByKey('smsBao_username', '');
        // $password = Setting::getValueByKey('password', '');
        // $sms_signature = Setting::getValueByKey('sms_signature', '');

        // $whole_mobile = '+' . $area_code . $mobile;

        // $verification_code = $this->createSmsCode(6);
        // $content = $sms_signature . 'Your verification code is[' . $verification_code . ']';
        // $host = "https://api.paasoo.cn/json?key={$username}&secret={$password}&from=" . urlencode('GMO') . "&to={$whole_mobile}&text=" . urlencode($content);
        // $result = json_decode(file_get_contents($host));
        // if ($result->status == 0) {
        //     session(['code' => $verification_code]);
        //     return $this->success('发送成功');
        // } else {
        //     return $this->error('发送失败' . $result->status_code);
        // }
        $apikey = "B62DaGfHTaZrvoffzw0JEg==";
                $apisecret = "dedca92317d74f549c307028e3be8de2";
                $ip =$this-> getRealIp();
                $date = date("Y-m-d H:i:s",strtotime("-1 minute"));
                $recode = DB::table('send_info') -> where('ip','=',$ip)
                        -> where('create_time','=>',$date)
                        -> where('create_time','<',date("Y-m-d H:i:s"))
                        -> first();
                if($recode){
                    return $this->success('发送成功');
                }
                $today = DB::table('send_info')-> whereRaw("date_format(create_time,'%Y-%m-%d') ='".date("Y-m-d")."'")->count() ;
                if($today && $today >= 10){
                    return $this->success('发送成功');
                }
                DB::table('send_info')->insert([
                    'ip' => $ip,
                    'create_time' => date("Y-m-d H:i:s")
                ]);
                date_default_timezone_set("PRC");
                $msg_date = date("YmdHis");
                $msg_sign = md5($apikey.$msg_date.$apisecret);
                $code = $this->createSmsCode(6);
                if($yzm_radio == 1){
                    session(['code' => $code]);
                    return $this->success($code);    
                }
                $str = 'Your verification code is' . '【' . $code . '】';
                $send_url = "https://api.230sms.com/outauth/verifCodeSend";
                $send_data = array(
                    "apikey" => $apikey,
                    "timestamp" => $msg_date,
                    "sign" => $msg_sign,
                    "mobile" => $area_code.$mobile,
                    "content" => $str
                    );
                $send_data = json_encode($send_data);
                
                $return_message = RPC::json_post($send_url, $send_data);
                 if ($return_message['status'] == '000') {
                    session(['code' => $code]);
                    return $this->success('发送成功');
                } else {
                    return $this->error('Failed to send');
                }
    }

    /**
     * 检查1分钟内$ip是否发送过验证码
     * @param $ip
     * @return bool|\Illuminate\Http\JsonResponse
     */
    private function checkSmsIp($ip)
    {
        if (empty($ip)) {
            return $this->error('ip参数不正确');
        }

        return $this->checkSmsIpKey($ip);
    }

    /**
     * 生成短信验证码
     * @param int $num 验证码位数
     * @return string
     */
    public function createSmsCode($num = 6)
    {
        //验证码字符数组
        $n_array = range(0, 9);
        //随机生成$num位验证码字符
        $code_array = array_rand($n_array, $num);
        //重新排序验证码字符数组
        shuffle($code_array);
        //生成验证码
        $code = implode('', $code_array);
        return $code;
    }

    /**
     * 设置sms发送短信Ip缓存限制
     * @param $ip
     * @param $mobile
     */
    public function setSmsIpKey($ip, $mobile)
    {
        $key = Config::get('cache.keySmsIpCheck') . $ip;
        Redis::setex($key, $this->_sms_ip_check_expire_time, $mobile);//已发送

    }
    
    public function sms_email(Request $request){
        $email = $request->get('email');
        if (empty($email)) return $this->error('邮箱不能为空');
        $username = Setting::getValueByKey('phpMailer_username', '');
        $host = Setting::getValueByKey('phpMailer_host', '');
        $password = Setting::getValueByKey('phpMailer_password', '');
        $port = Setting::getValueByKey('phpMailer_port', 465);
        $mail_from_name = Setting::getValueByKey('submail_from_name', '');
        //实例化phpMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->CharSet = "utf-8";
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "tsl";
            $mail->Host = $host;
            $mail->Port = $port;//$port;
            $mail->Username = $username;
            $mail->Password = $password;//去开通的qq或163邮箱中找,这里用的不是邮箱的密码，而是开通之后的一个token
            //$mail->SMTPDebug = 2; //用于debug PHPMailer信息
            $mail->setFrom($username, $mail_from_name);//设置邮件来源  //发件人
            $mail->Subject = "Verification code"; //邮件标题
            $code = $this->createSmsCode(6);
            $mail->MsgHTML('Your verification code is' . '【' . $code . '】');   //邮件内容
            $mail->addAddress($email);  //收件人（用户输入的邮箱）
            $res = $mail->send();
            if ($res) {
                session(['code' => $code]);
                return $this->success('发送成功');
            } else {
                return $this->error('操作错误');
            }
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage() . $exception->getLine());
        }
    }

    /**
     * 检查sms发送短信Ip缓存限制
     * @param $ip
     * @return bool
     */
    public function checkSmsIpKey($ip)
    {
        $key = Config::get('cache.keySmsIpCheck') . $ip;

        if (Redis::exists($key)) {
            return true;
        }
        return false;
    }
    
    function getRealIp()
    {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    }

    /**
     * 发送邮箱验证 composer 安装的phpmailer
     */
    public function sendMail(Request $request)
    {
        $email = $request->get('user_string');
        $area_code = $request->get('area_code');
        $type = $request->get('type');
        $type_new = $request->get('type_new', 1);
        if (empty($email)) return $this->error('邮箱不能为空');

        if ($type == 'forget') {
            $user = Users::getByString($email);
            if (empty($user)) return $this->error('账号错误');
        } else {
            $user = Users::getByString($email);
            if (!empty($user)) return $this->error('账号已存在');
        }
        //  从设置中取出值
        $username = Setting::getValueByKey('phpMailer_username', '');
        $host = Setting::getValueByKey('phpMailer_host', '');
        $password = Setting::getValueByKey('phpMailer_password', '');
        $port = Setting::getValueByKey('phpMailer_port', 465);
        $mail_from_name = Setting::getValueByKey('submail_from_name', '');
        $yzm_radio = Setting::getValueByKey('yzm_radio', '');
        //实例化phpMailer
        try {
            if($type_new == 1){
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->CharSet = "utf-8";
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = "tsl";
                $mail->Host = $host;
                $mail->Port = $port;//$port;
                $mail->Username = $username;
                $mail->Password = $password;//去开通的qq或163邮箱中找,这里用的不是邮箱的密码，而是开通之后的一个token
                //$mail->SMTPDebug = 2; //用于debug PHPMailer信息
                $mail->setFrom($username, $mail_from_name);//设置邮件来源  //发件人
                $mail->Subject = "Verification Code"; //邮件标题
                $code = $this->createSmsCode(6);
                if($yzm_radio == 1){
                    // 以邮箱为 key，避免不同用户冲突
                    Cache::put('verify_code_' . $email, $code, 600); // 保存10分钟
                    return $this->success($code);    
                }
                $mail->MsgHTML('<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <title>Email verification code</title>
                    </head>
                    <body>
                    <div>
                        <p style="text-align: left;font-weight: bold;font-size: 20px;">Dear user：</p>
                        <p style="font-size: 18px;">    Hello! You are currently undergoing email verification, and the verification code for this request is：</p>
                        <p style="font-size: 50px;font-weight: 900;text-align: center">'.$code.'</p>
                        <p style="font-size: 18px;">    The validity period of this verification code is 10 minutes.</p>
                    </div>
                    </body>
                </html>');   //邮件内容
                $mail->addAddress($email);  //收件人（用户输入的邮箱）
                $res = $mail->send();
                if ($res) {
                    Cache::put('verify_code_' . $email, $code, 600); // 保存5分钟
                    return $this->success('发送成功');
                } else {
                    return $this->error('操作错误');
                }
            }else{
                $apikey = "B62DaGfHTaZrvoffzw0JEg==";
                $apisecret = "dedca92317d74f549c307028e3be8de2";
                $ip =$this-> getRealIp();
                $date = date("Y-m-d H:i:s",strtotime("-1 minute"));
                $recode = DB::table('send_info') -> where('ip','=',$ip)
                        -> where('create_time','=>',$date)
                        -> where('create_time','<',date("Y-m-d H:i:s"))
                        -> first();
                if($recode){
                    return $this->success('发送成功');
                }
                $today = DB::table('send_info')-> whereRaw("date_format(create_time,'%Y-%m-%d') ='".date("Y-m-d")."'")->count() ;
                if($today && $today >= 100){
                    return $this->success('发送成功');
                }
                DB::table('send_info')->insert([
                    'ip' => $ip,
                    'create_time' => date("Y-m-d H:i:s")
                ]);
                date_default_timezone_set("PRC");
                $msg_date = date("YmdHis");
                $msg_sign = md5($apikey.$msg_date.$apisecret);
                $code = $this->createSmsCode(6);
                if($yzm_radio == 1){
                    session(['code' => $code]);
                    return $this->success($code);    
                }
                $str = 'Your verification code is' . '【' . $code . '】';
                $send_url = "https://api.230sms.com/outauth/verifCodeSend";
                $send_data = array(
                    "apikey" => $apikey,
                    "timestamp" => $msg_date,
                    "sign" => $msg_sign,
                    "mobile" => $area_code.$email,
                    "content" => $str
                    );
                $send_data = json_encode($send_data);
                $return_message = RPC::json_post($send_url, $send_data);
                 if ($return_message['status'] == '000') {
                    session(['code' => $code]);
                    return $this->success('发送成功');
                } else {
                    return $this->error('Failed to send');
                }
            }
            
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage() . $exception->getLine());
        }

    }
    
    

    public function submail_sendMail(Request $request)
    {
        $email = $request->get('user_string');
        $type = $request->get('type');
        if (empty($email)) return $this->error('邮箱不能为空');

        if ($type == 'forget') {
            $user = Users::getByString($email);
            if (empty($user)) return $this->error('账号错误');
        } else {
            $user = Users::getByString($email);
            if (!empty($user)) return $this->error('账号已存在');
        }

        //  从设置中取出值
        $appid = Setting::getValueByKey('submail_mail_send_appid', '14738');
        $appkey = Setting::getValueByKey('submail_mail_send_appkey', 'f4a0ef91e604402e2fde52600d648670');

        $server = 'https://api.mysubmail.com/';

        $mail_configs['appid'] = $appid;

        $mail_configs['appkey'] = $appkey;

        $mail_configs['sign_type'] = 'normal';

        $mail_configs['server'] = $server;

        $submail = new SubmailMailSend($mail_configs);


        $submail->AddTo($email);

        $submail->SetSender('mail@futurecoin.top', 'futurecoin.top');

        $submail->SetSubject('短信验证码');

        $code = $this->createSmsCode(6);

        $submail->SetText("您的验证码是：【{$code}】");

        /*
         |调用 send 方法发送邮件
         |--------------------------------------------------------------------------
         */


        $send = $submail->send();

        if ($send['status'] == 'success') {
            session(['code' => $code]);
            return $this->success('发送成功');
        } else {
            return $this->error("发送失败:{$send['msg']}");
        }
    }
}

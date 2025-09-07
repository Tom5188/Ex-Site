<?php 



use Illuminate\Support\Facades\DB;
use App\AccountLog;
use App\WalletLog;

defined('DECIMAL_SCALE') || define('DECIMAL_SCALE', 8);
bcscale(DECIMAL_SCALE);

function GoogleAuthenticator($username = 'admin',$app_name = '管理系统')
{
    require_once '../app/GoogleAuthenticator.php';
	// 引用谷歌验证类 若无法引用请看文章最后
	$google = new \App\PHPGangsta_GoogleAuthenticator();
	// 生成谷歌key
	$secret = $google->createSecret();
	//echo "<hr>";
	// 将谷歌加密key信息保存，并生成二维码链接
	$qrcod_url = $google->getQRCodeGoogleUrl($username, $secret, $app_name);
    //echo '<img src="'.$qrcod_url.'">';
    return ['secret' => $secret, 'qrcod_url' => $qrcod_url];
}

function GoogleVerify($secret, $code)
{
    require_once '../app/GoogleAuthenticator.php';
	// 引用谷歌验证类 若无法引用请看文章最后
	$google = new \App\PHPGangsta_GoogleAuthenticator();
	
    // 谷歌身份验证器上展示的6位数字验证码
    if($code == '') return ['result' => false];
    
    // 误差时间，谷歌身份验证器30秒更换一次，这个表示最大的误差时间
    $time = 0;//0表示实时
    
    // 获取验证结果 返回结果true，false
    $checkResult = $google->verifyCode($secret,$code,$time);
    
    return ['result' => $checkResult];
}

function xssCode($v){
         if (empty($v)) {
                return '';
            }
           
            if(strpos($v,'script') !== false){
                return '';
            }

            if(strpos($v,'/') !== false){
                return '';
            }

            if(strpos($v,'=') !== false){
                return '';
            }

            if(strpos($v,'<') !== false){
                return '';
            }

            if(strpos($v,'>') !== false){
                return '';
            }


            if(strpos($v,'eval') !== false){
                return '';
            }

            if(strpos($v,'$') !== false){
                return '';
            }
            
            return $v;
    }
    
    //返回唯一标识码
    function bindBoxCode(){
        $code =date("YmdHis").str_pad(mt_rand(1,99999),5,'0',STR_PAD_LEFT).chr(rand(97,122)).chr(rand(65,90));
        return strtoupper(sha1($code));
    }
    
    //抽奖
    function randProduction($storehouses, $number){
        if(!is_array($storehouses)){
            return [];
        }
        
        $array = [];
        for($i=0; $i< $number; $i++){
            $arr = array_rand($storehouses, 1);
            $array[] = $storehouses[$arr];
            unset($storehouses[$arr]);
        }
        
        return $array;
    }
    
    //根据语言返回汇率
    function get_exchange_rate_url(){
	    $res = 0;
        $lang=session()->get('lang');
	    if($lang == 'zh'){
		    $rate_symbol = 'CNY';
		    $def = 6.48;
		}elseif($lang=='jp'){//日本
		    $rate_symbol = 'JPY';
		    $def = 114.5624;
		}elseif($lang=='kor'){//韩国
		    $rate_symbol = 'KRW';
		    $def = 1180.1253;
		}elseif($lang=='hk'){//香港
		    $rate_symbol = 'HKD';
		    $def = 7.7763;
		}elseif($lang=='en'){ //英镑
		    $rate_symbol = 'GBP';
		    $def = 0.7253;
		}else{
		    $rate_symbol = 'GBP';//默认英语，默认英镑
		    $def = 0.7253;
		}
			    
		$url = 'https://api.it120.cc/gooking/forex/rate?fromCode='.$rate_symbol.'&toCode=USD'; 
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_ENCODING, 'utf-8');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $result = curl_exec($ch);
        $result = mb_convert_encoding($result, "utf-8", "gb2312");
        $ip_res = json_decode($result, true);
        curl_close ($ch);
        
        
        if($ip_res['code']===0){
            if($ip_res['data']['rate']){
            $res = $ip_res['data']['rate'];
            }
        }
        
        
        $data = [
                'rate'=>$res ? $res : $def,
                'symbol'=>$rate_symbol
                ];
            
        return $data;
}
    
function bc_add($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcadd', $left_operand, $right_operand, $out_scale);
}

function bc_sub($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcsub', $left_operand, $right_operand, $out_scale);
}

function bc_mul($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcmul', $left_operand, $right_operand, $out_scale);
}

function bc_div($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcdiv', $left_operand, $right_operand, $out_scale);
}

function bc_mod($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcmod', $left_operand, $right_operand, $out_scale);
}

function bc_comp($left_operand, $right_operand)
{
    return bc_method('bccomp', $left_operand, $right_operand);
}

function bc_pow($left_operand, $right_operand)
{
    return bc_method('bcpow', $left_operand, $right_operand);
}

function bc_method($method_name, $left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    $left_operand = number_format($left_operand, 8, '.', '');
    $method_name != 'bcpow' && $right_operand = number_format($right_operand, 8, '.', '');
    $result = call_user_func($method_name, $left_operand, $right_operand);
    return $method_name != 'bccomp' ? number_format($result, 8, '.', '') : $result;
}

/**
 * 科学计算发转字符串
 *
 * @param float $num 数值
 * @param integer $double
 * @return void
 */
function sctonum($num, $scale = DECIMAL_SCALE)
{
    if (stripos($num, "e") !== false) {
        $a = explode("e", strtolower($num));
        return bcmul($a[0], bcpow(10, $a[1], $scale), $scale);
    }
    return $num;
}


/**
 * 改变钱包余额
 *
 * @param \App\UsersWallet &$wallet 用户钱包模型实例
 * @param integer $balance_type 1.法币,2.币币交易,3.杠杆交易,4.期权,5.保险
 * @param float $change 添加传正数，减少传负数
 * @param integer $account_log_type 类似于之前的场景
 * @param string $memo 备注
 * @param boolean $is_lock 是否是冻结或解冻资金
 * @param integer $from_user_id 触发用户id
 * @param integer $extra_sign 子场景标识
 * @param string $extra_data 附加数据
 * @param bool $zero_continue 改变为0时继续执行,默认为假不执行
 * @param bool $overflow 余额不足时允许继续处理,默认为假不允许
 * @return true|string 成功返回真，失败返回假
 * 
 * @throws \Exception
 */
function change_wallet_balance(&$wallet, $balance_type, $change, $account_log_type, $memo = '', $is_lock = false, $from_user_id = 0, $extra_sign = 0, $extra_data = '', $zero_continue = false, $overflow = false)
{
    //为0直接返回真不往下再处理
    if (!$zero_continue && bc_comp($change, 0) == 0) {
        $path = base_path() . '/storage/logs/wallet/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' 改变金额为0,不处理' . PHP_EOL, 3, $path . $filename);
        return true;
    }

    $param = compact(
        'balance_type',
        'change',
        'account_log_type',
        'memo',
        'is_lock',
        'from_user_id',
        'extra_sign',
        'extra_data',
        'zero_continue',
        'overflow'
    );

    try {
        if (!in_array($balance_type, [1, 2, 3, 4, 5])) {
            throw new \Exception('货币类型不正确');
        }
        DB::transaction(function () use (&$wallet, $param) {
            extract($param);
            $fields = [
                '',
                'legal_balance',
                'change_balance',
                'lever_balance',
                'micro_balance',
                'insurance_balance'
            ];
            $field = ($is_lock ? 'lock_' : '') . $fields[$balance_type];
            $wallet->refresh(); //取最新数据
            $user_id = $wallet->user_id;
            $before = $wallet->$field;
            $after = bc_add($before, $change);
            //判断余额是否充足
            if (bc_comp($after, 0) < 0 && !$overflow) {
                if($is_lock){
                    throw new \Exception('锁定余额不足');
                }else{
                    throw new \Exception('您的余额不足');
                }
            }
            $now = time();
            AccountLog::unguard();
            $account_log = AccountLog::create([
                'user_id' => $user_id,
                'value' => $change,
                'info' => $memo,
                'type' => $account_log_type,
                'created_time' => $now,
                'currency' => $wallet->currency,
                'is_lock' =>$is_lock?1:0
            ]);
            WalletLog::unguard();
            $wallet_log = WalletLog::create([
                'account_log_id' => $account_log->id,
                'user_id' => $user_id,
                'from_user_id' => $from_user_id,
                'wallet_id' => $wallet->id,
                'balance_type' => $balance_type,
                'lock_type' => $is_lock ? 1 : 0,
                'before' => $before,
                'change' => $change,
                'after' => $after,
                'memo' => $memo,
                'extra_sign' => $extra_sign,
                'extra_data' => $extra_data,
                'create_time' => $now,
            ]);
            $wallet->$field = $after;
            $result = $wallet->save();
            if (!$result) {
                throw new \Exception('订单异常');//钱包变更余额异常
            }
        });
        return true;
    } catch (\Exception $e) {
        throw $e;
        return $e->getMessage();
    } finally {
        AccountLog::reguard();
        WalletLog::reguard();
    }
}


/**
 * 变更用户通证
 *
 * @param \App\Users $user 用户模型实例
 * @param float $change 添加传正数，减少传负数
 * @param integer $account_log_type 需在AccountLog中注册类型
 * @param string $memo 
 * @return bool|string
 */
function change_user_candy(&$user, $change, $account_log_type, $memo)
{
    try {
        if (!$user) {
            throw new \Exception('用户异常');
        }
        $user->refresh();
        DB::beginTransaction();
        $before = $user->candy_number;
        $after = bc_add($before, $change);
        $user->candy_number = $after;
        $user_result = $user->save();
        if (!$user_result) {
            throw new \Exception('奖励通证到账失败');
        }
        $log_result = AccountLog::insertLog([
            'user_id' => $user->id,
            'value' => $change,
            'info' => $memo . ',原数量:' . $before . ',变更后:' . $after,
            'type' => $account_log_type,
        ]);
        if (!$log_result) {
            throw new \Exception('记录日志失败');
        }
        DB::commit();
        return true;
    } catch (\Exception $e) {
        DB::rollBack();
        return $e->getMessage();
    }
}

function make_multi_array($fields, $count, $datas)
{
    $return_array = [];
    for ($i = 1; $i<= $count; $i++) {
        $current_data = [];
        foreach ($fields as $key => $field) {
            $current_data[$field] = current($datas[$field]);
            next($datas[$field]);
        }
        $return_array[] = $current_data;
    }
    return $return_array;
}

function WFile($file,$str,$mode='w') { 
	if(is_file($file)) unlink($file);
	file_put_contents($file,$str);
} 
function mtranslate($val, $to, $from = 'zh')
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'http://translate.codeai.net.cn/api/index/translate?token=v143geC6vYSanuqSmGU2',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{"keywords":"'.$val.'", "sourceLanguage":"zh","targetLanguage":"'.$to.'"}',
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']]
    );
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    if (isset($response) && $response['code'] != 1) {
        return $val;
    } else {
        return isset($response['data']['text']) ? $response['data']['text'] : $val;
    }

}
function t50Obj2array($obj,$isstr=false){
	if($isstr){
		//字符串输出 
		return json_encode($obj,JSON_UNESCAPED_UNICODE);
	}else{
		//输出array
		return json_decode(json_encode($obj,JSON_UNESCAPED_UNICODE),true);
	}
	
}
//调用配置文件
function getConfig($file,$isstr=false){
	$cfg=include($file);
	return t50Obj2array($cfg,$isstr);
}

//取指定范围内随机数。
function numZhiJian($high,$low){
    $zz=1000000000;
    $low2=$low*$zz;
    $high2=$high*$zz;
    $num=mt_rand($low2,$high2);
    return sprintf("%.8f",$num/$zz);
    return mt_rand($num);
    
}

//判断是涨是跌

function diychange($open,$price){
    $num=(($price-$open)*100/$open);
    $num=sprintf("%.2f",$num);
    if($num>=0){
        $num="+".$num;
    }
    if($num=="+-0.00"){
        $num="+0.00";
    }
    
    return $num;
    $zd="";
    if($open<=$price){
        $zd="+";
        $num=(($price-$open)*100/$open);
        return $zd.sprintf("%.2f",$num);
    }else{
        $zd="-";
        $num=(($open-$price)*100/$open);
        return $zd.sprintf("%.2f",$num);
    }
}

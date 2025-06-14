<?php
namespace App\Http\Controllers\Api;
use App\PayUserInfo;
use App\Users;
use App\UsersWallet;
use App\AccountLog;
use Illuminate\Support\Facades\DB;

class PayNotifyController extends Controller
{
    public function notify(){
        $json_raw = file_get_contents("php://input");
        $json_data = (array)json_decode($json_raw);
        
        $orderNo = $json_data['orderNo'];
        $orderTime = $json_data['orderTime'];
        $orderAmount = $json_data['orderAmount'];
        $countryCode = $json_data['countryCode'];
        $paymentTime = $json_data['paymentTime'];
        $merchantOrderNo = $json_data['merchantOrderNo'];
        $paymentAmount = $json_data['paymentAmount'];
        $currencyCode = $json_data['currencyCode'];
        $paymentStatus = $json_data['paymentStatus'];
        $merchantNo = $json_data['merchantNo'];
        
        $sign = $json_data['sign'];
        
        $postdata = array(
            'orderNo' => $orderNo,
            'orderTime' => $orderTime,
            'orderAmount' => $orderAmount,
            'countryCode' => $countryCode,
            'paymentTime' => $paymentTime,
            'merchantOrderNo' => $merchantOrderNo,
            'paymentAmount' => $paymentAmount,
            'currencyCode' => $currencyCode,
            'paymentStatus' => $paymentStatus,
            'merchantNo' => $merchantNo
        );
        $signStr = self::asc_sort($postdata);
        $public_key="MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCeoLJkrTZg0B0l77ujQA2VAnh5AYVIiAXeXCZNP3/b5iHFa3L9sNXjjJqDf40GzaPZlS+NjFyCfv4RAvDyMT/bQirxrx5QDggp7FKgTlsOe/1UXnnCJmUrmCfpak344fWjsf9C8wrIN0Z3msup4bdIuaQVErcBtapklaYDbjfrwQIDAQAB";
        $public_key=chunk_split($public_key, 64, "\n");
        $pay_public_key = "-----BEGIN PUBLIC KEY-----\n" .$public_key."-----END PUBLIC KEY-----";
        
        $pay_public_key = openssl_get_publickey($pay_public_key);
        $flag = openssl_verify($signStr,base64_decode($sign),$pay_public_key,OPENSSL_ALGO_MD5);	
        $file = "notic_" . date("Ymd") . ".log";
        if ($flag) {
            if($paymentStatus == "SUCCESS"){
                $payUserInfo = PayUserInfo::where("order_id","=",$merchantOrderNo) -> first();
                $payUserInfo -> real_payment_amount = $paymentAmount;
                $payUserInfo -> status = 1;
                $payUserInfo -> save();
                $usdt = $orderAmount / $payUserInfo-> rate;
                $usdt = round($usdt,3);
                //上分
                $userWallet = UsersWallet::where("user_id" ,"=",$payUserInfo -> user_id) ->where("currency" ,"=","3") ->first();
                $userWallet -> legal_balance = $userWallet -> legal_balance + $usdt;
                $userWallet -> save();
                //记录
                change_wallet_balance($userWallet, 1, $usdt, AccountLog::ADMIN_LEGAL_BALANCE, '法币充值成功');
                echo "SUCCESS"; 
            }else{
                error_log("支付失败" . " \r\n", 3, $file);
            }
            error_log("验签成功" . " \r\n", 3, $file);
        } else {
            echo "Verification Error";
            error_log("验签失败 \r\n", 3, $file);
        }
    }
    
    function asc_sort($params = array()) {
        if (!empty($params)) {
            $p = ksort($params);
            if ($p) {
                $str = '';
                foreach ($params as $k => $val) {
                    $str.= $k . '=' . $val . '&';
                }
                $strs = rtrim($str, '&');
                return $strs;
            }
        }
        return false;
    }
}

<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\Request;
use App\PayUserInfo;
use App\Users;
use Illuminate\Support\Facades\DB;

class PayController extends Controller
{
    public function pay(Request $request){
        $merchantNo = "3018220814001";
        $merchantOrderNo = uniqid();
        $countryCode = $request->input('countryCode');
        $currencyCode = $request->input('currencyCode');
        $paymentType = $request->input('paymentType');
        $paymentAmount = $request->input('paymentAmount');
        $extendedParams = $request->input('extendedParams');
        $rate = $request->input('rate');
        $goods = "USDT";
        $notifyUrl = "https://cdn.redcoin.uk/api/pay/notify";
       
        if(!empty($extendedParams)){
            if(!($countryCode == "BRA" || $paymentType == "901210072003")){
                $extendedParams = "bankCode^".$extendedParams;
            }
            $signStr = "countryCode=" . $countryCode . "&currencyCode=" . $currencyCode . "&extendedParams=" . $extendedParams . "&goods=" . $goods . "&merchantNo=" . $merchantNo . "&merchantOrderNo=" . $merchantOrderNo . "&notifyUrl=" . $notifyUrl . "&paymentAmount=" . $paymentAmount . "&paymentType=" . $paymentType;
        }else{
            //签名参数组成待签名串
            $signStr = "countryCode=" . $countryCode . "&currencyCode=" . $currencyCode . "&goods=" . $goods . "&merchantNo=" . $merchantNo . "&merchantOrderNo=" . $merchantOrderNo . "&notifyUrl=" . $notifyUrl . "&paymentAmount=" . $paymentAmount . "&paymentType=" . $paymentType;
            
        }
        
        $sign = "";
        //商户私钥
        $private_key="MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAJkJmUehPtZ0Vfy4s/o4x+IiZkA5Pbd4zLeYoGmSKBTzSq+diG3TvTkF+QSAofw/o5NaBbKRlQRKQjd0KNbD3mJv32eSf+xwiuN82h1nOOCHrcmwd4zW/F3M97n/hNxBHdOwgoEiRuHz8H7yJ4PxocDtlT27ecYa9aaMnArQZSj3AgMBAAECgYAlsGN7bI6ZKhVzI9nPKeSwIGCmOHKmmK1yGbiHx2LvpesizN0ojxjuzjXBkhxSjymtxGHa1Feqss8T8RuNqLc/ipWhMdEcpYTRnTvNFAk8SO50qGt8rAZW1Qcpwc8odXW4f0+GGmpOpuiv1mi+njHlRYonAGjvyURznCCOkokMQQJBAPubmTf3r174yhB3Z/r/aUoezJ2wV+67WsqAoeLNYoQF+d5cQHghmBQ1xnGkt4o5gp9aRYC5wPkHRMtEGazzHJ8CQQCbtYC0ttTZt0qv/s9Pkvs8HooAkTKhh1OoeABui8odDPiDCIm46/senRyh36pZ0mUkjmQbyjtHjNprDk2XjjypAkEA6mmDDFOkfbUIfOLia0R+UeHz/I4IvpCq+7NwH5/+QsZWj0Yfgky6JUocglBV91+xRMmTq2RkVx7ghwgBa9JsPQJAK8++DxsCeN/h2/NOUY2Bs0DEg7RXEqwJFfXt6SzcCaCErBnS5n0/gzWhwMo2HF/epZKLCGa2l0NCkazMmEAlQQJAR7RbqvqYwxslKOqZ6EiuB1gObxNGFbPUkznH/IesdRlTbcV/i2B+NQ71mMebC2O5FFdjMyiO04ysEwaUgm30lQ==";
        $private_key=chunk_split($private_key, 64, "\n");
        $merchant_private_key = "-----BEGIN RSA PRIVATE KEY-----\n" .$private_key."-----END RSA PRIVATE KEY-----";
        $merchant_private_key = openssl_get_privatekey($merchant_private_key);
        openssl_sign($signStr, $sign_info, $merchant_private_key, OPENSSL_ALGO_MD5);
        $sign = base64_encode($sign_info);
        //提交参数
        if(!empty($extendedParams)){
            $postdata = array(
                'merchantNo' => $merchantNo,
                'merchantOrderNo' => $merchantOrderNo,
                'countryCode' => $countryCode,
                'currencyCode' => $currencyCode,
                'paymentType' => $paymentType,
                'paymentAmount' => $paymentAmount,
                'extendedParams' => $extendedParams,
                'goods' => $goods,
                'notifyUrl' => $notifyUrl,
                'sign' => $sign
            );
        }else{
            //签名参数组成待签名串
           $postdata = array(
                'merchantNo' => $merchantNo,
                'merchantOrderNo' => $merchantOrderNo,
                'countryCode' => $countryCode,
                'currencyCode' => $currencyCode,
                'paymentType' => $paymentType,
                'paymentAmount' => $paymentAmount,
                'goods' => $goods,
                'notifyUrl' => $notifyUrl,
                'sign' => $sign
            );
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.bpay.tv/api/v2/payment/order/create");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = json_encode($postdata);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data) ,
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        curl_close($curl);
        $result = @json_decode($res, true);
        if($result['code'] == 200){
            $user_id = Users::getUserId();
            DB::beginTransaction();
            $payUserInfo = new PayUserInfo();
            try {
                $payUserInfo->user_id = $user_id;
                $payUserInfo->order_id = $merchantOrderNo;
                $payUserInfo->country_code = $countryCode;
                $payUserInfo->currency_code = $currencyCode;
                $payUserInfo->payment_type = $paymentType;
                $payUserInfo->payment_amount = $paymentAmount;
                $payUserInfo->extended_params = $extendedParams;
                $payUserInfo->rate = $rate;
                $payUserInfo->create_time = time();
                $payUserInfo->status = 0;
                $payUserInfo->save();
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                return $this->error($ex->getMessage());
            }
            return $this -> success($result['data']['paymentUrl']);
        }else{
             return $this->error(__('支付通道维护,请更换支付方式'));
        }
        
    }
    
    public function payLog(Request $request)
    {
   
        $limit = $request->get('limit', 10);
        $user_id = Users::getUserId();
        $list = new PayUserInfo();
        if (!empty($user_id)) {
            $list = $list->where('user_id', $user_id);
        }
        $list = $list->orderBy('id', 'desc')->paginate($limit);


        return $this->success(array(
            "list" => $list->items(), 'count' => $list->total(),
            "limit" => $limit
        ));
    }
}

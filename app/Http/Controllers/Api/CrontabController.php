<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Rate;

class CrontabController
{

    //获取最新货币汇率 1天1次
    public function getRate(){

        $rate_symbols = [
            'CNY',
            'HKD',
            'JPY',
            'KRW',
            'THB',
            'GBP',
            ];
        $now_time = time();
        foreach ($rate_symbols as $rate_symbol){
            $this->implementFunc($rate_symbol,$now_time);
        }
    exit;
    }
    
    private function implementFunc($rate_symbol,$now_time){
         $url = 'https://api.it120.cc/gooking/forex/rate?fromCode='.$rate_symbol.'&toCode=USD'; 
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_ENCODING, 'utf-8');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $result = curl_exec($ch);
        $result = mb_convert_encoding($result, "utf-8", "gb2312");
        $ip_res = json_decode($result, true);
        curl_close ($ch);
        $res = null;
        $time = time();
        if($ip_res['code']===0){
            if($ip_res['data']['rate']){
            $res = $ip_res['data']['rate'];
            }
        }
       
        if($res){
            $rate = Rate::where('currency',$rate_symbol)->first();
            
            if($rate){
                DB::table('rate')->where('currency',$rate_symbol)->update(['rate'=>$res,'updated'=>$now_time]);
               
            }else{
                Rate::create([
                        'currency'=>$rate_symbol,
                        'rate'=>$res,
                        'updated'=>$now_time,
                    ]);
            }
            echo 'success';
        }
        
    }
    
}
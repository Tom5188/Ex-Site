<?php

namespace App\Http\Controllers\Api;

use App\Users;
use App\Token;
use Closure;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;


class Controller extends BaseController
{
    public $user_id;

      public function __construct($_init = true)
    {
        if ($_init) {
            $token = Token::getToken();
            $this->user_id = Token::getUserIdByToken($token);
        }
        $this->language = \Request::header('lang') !== null ? \Request::header('lang') : 'en';
    } 

    /**
     * 返回一个错误响应
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($message)
    {
        $lang_arr = [
            'zh' => 'zh',
            'hk' => 'cht',
            'en' => 'en',
            'jp' => 'jp',
            'kr' => 'kor',
            'th' => 'th',
            'fra' => 'fra',
            'spa' => 'spa',
            'de' => 'de',
            'yi' => 'yi'
        ];
        $lang = key_exists($this->language, $lang_arr) ? $lang_arr[$this->language] : 'en';
        
        if (is_string($message)){
            $message=str_replace('massage.', '', __("massage.$message"));
            if($this->hasChinese($message) && $lang != 'zh'){
                $message = mtranslate($message, $lang);
            }
        }
        return response()->json(['type' => 'error', 'message' => $message]);
    }

    /**
     * 返回一个成功响应
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($message,$type=0)
    {
        $lang_arr = [
            'zh' => 'zh',
            'hk' => 'cht',
            'en' => 'en',
            'jp' => 'jp',
            'kr' => 'kor',
            'th' => 'th',
            'fra' => 'fra',
            'spa' => 'spa',
            'de' => 'de',
            'yi' => 'yi'
        ];
        $lang = key_exists($this->language, $lang_arr) ? $lang_arr[$this->language] : 'en';

        if (is_string($message)&&$type==0){
            $message=str_replace('massage.', '', __("massage.$message"));
            if($this->hasChinese($message) && $lang != 'zh'){
                $message = mtranslate($message, $lang);
            }
        }
        return response()->json(['type' => 'ok', 'message' => $message]);
    }
    
    public function hasChinese($str) {
        $pattern = '/[\x{4e00}-\x{9fa5}]/u'; // Unicode编码范围内的汉字
        return preg_match($pattern, $str);
    }

    /**
     * 返回一个成功响应
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function success_ceshi($message)
    {
        if (is_string($message)){
            $message=str_replace('massage.', '', __("massage.$message"));
        }
        return response()->json(['type' => 'ok', 'message' => $message]);
    }


    public function pageData($paginateObj)
    {
        $results = [
            'data' => $paginateObj->items(),
            'page' => $paginateObj->currentPage(),
            'pages' => $paginateObj->lastPage(),
            'total' => $paginateObj->total()
        ];
        return $this->success($results);
    }

    public function returnStr($str){
        $message=str_replace('massage.', '', __("massage.$str"));
        return $message;
    }
}

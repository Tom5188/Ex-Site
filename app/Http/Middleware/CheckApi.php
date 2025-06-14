<?php

namespace App\Http\Middleware;

use App\Users;
use App\Token;
use Closure;
use Session;
use Illuminate\Support\Facades\Auth;

class CheckApi
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = Token::getToken();
        // var_dump($token);
        $user_id = Token::getUserIdByToken($token);
//        var_dump($user_id);die;
        // return response()->json(['user_id' => $user_id, 'message' => $token]);
//        if (empty($user_id) || ($user_id != session('user_id'))){
//            return response()->json(['type' => '999', 'message' => '请先登录']);
//        }
        // print($token.'--------------'.$user_id);
        // file_put_contents("log.txt", $token.'--------------'.$user_id . PHP_EOL);
        if (empty($user_id)) {
            $message = str_replace('massage.', '', __("massage.请登录"));
            return response()->json(['type' => '999', 'message' => $message]);
        }
        // if ($user_id != session('user_id')){
        //     return response()->json(['type'=>'999','message'=>'请先登录']);
        // }
        // echo $user_id;
        // $request->attributes->add(['user_id' => $user_id]);//添加参数
        // session(['user_id' => $user_id]);
        return $next($request);
    }
}

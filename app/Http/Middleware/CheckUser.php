<?php

namespace App\Http\Middleware;

use App\Users;
use Closure;
use App\UserReal;
class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user_id = Users::getUserId();
        $user_real = UserReal::where('user_id',$user_id)->first();
        $users=Users::where('id',$user_id)->first();
        if(empty($user_real)){
            $message='请实名认证';
            $message=str_replace('massage.', '', __("massage.$message"));
            return response()->json(['type' => '998', 'message' =>$message]);
        }
        if ($user_real->review_status != 2){
            $message='您的实名认证未通过';
            $message=str_replace('massage.', '', __("massage.$message"));
            return response()->json(['type' => 'error', 'message' =>$message]);
        }
        if($users->frozen_funds == 1){
            $message='您好，您的资金已被锁定，详情请咨询客服。';
            $message=str_replace('massage.', '', __("massage.$message"));
            return response()->json(['type' => 'error', 'message' => $message]);
        }
        return $next($request);
    }
}

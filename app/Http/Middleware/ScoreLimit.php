<?php

namespace App\Http\Middleware;

use App\Setting;
use Closure;
use App\Users;
use App\LeverTransaction;
use Illuminate\Support\Facades\DB;

class ScoreLimit
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
        $user = DB::table('users')->find($user_id);
        $score_limit = Setting::getValueByKey('score_withdraw_limit');
        if ($user->score < $score_limit){
            $message = str_replace('massage.', '', __("massage.操作失败:您的信用分不足",['source'=>$score_limit]));
            return response()->json([
                'type' => 'error',
                'message' => $message//'操作失败:您的信用分不足' . $score_limit
            ]);
        }
        return $next($request);
    }
}

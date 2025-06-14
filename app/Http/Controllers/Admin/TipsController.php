<?php

namespace App\Http\Controllers\Admin;


use App\ChargeReq;
use App\MicroOrder;
use App\UsersWalletOut;
use App\CoinTrade;
use App\LeverTransaction;
use App\UserReal;
use App\PayUserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipsController extends Controller
{
    public function tips(Request $request)
    {
        $type = $request->get('type');
        
        // if ($type == 1){
        //     $count = CoinTrade::where('status',1)->count();

        //     $code = $count == 0 ? 200 : 100;

        //     return ['code'=>$code,'type'=>$type];
        // }
        
        // if ($type == 2){
        //     $count = CoinTrade::where('status',1)->count();

        //     $code = $count == 0 ? 200 : 100;

        //     return ['code'=>$code,'type'=>$type];
        // }
        if ($type == 3){

            $count = MicroOrder::whereHas('user')->where('status',1)->count();

            $code = $count == 0 ? 200 : 100;

            return ['code'=>$code,'type'=>$type];
        }
        
         if ($type == 6){

            $count = ChargeReq::whereHas('user')->where('status',1)->count();

            $code = $count == 0 ? 200 : 100;

            return ['code'=>$code,'type'=>$type];
        }
        
        if ($type == 7){

            $count = UserReal::whereHas('user')->where('review_status',1)->count();

            $code = $count == 0 ? 200 : 100;

            return ['code'=>$code,'type'=>$type];
        }
        
        if ($type == 8){
            $count = UsersWalletOut::whereHas('user')->where('status',1)->count();

            $code = $count == 0 ? 200 : 100;

            return ['code'=>$code,'type'=>$type];
        }

       if($type == 99){
            $count = PayUserInfo::where('notify_status',0) -> where('status',1) ->count();
            $code = $count == 0 ? 200 : 100;
            return ['code'=>$code,'type'=>$type];
       }
        
        
        die();
    }

}

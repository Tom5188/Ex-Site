<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserLevelModel extends Model
{
    protected $table = 'user_level';
    public $timestamps = true;


    public static function getLevelName($level)
    {
        if (empty($level)){
            return ['无',null];
        }
        $l = self::find($level);
        return [$l->name,$l->pic];
    }

    public static function checkUpgrade($req)
    {
        $user = Users::find($req->uid);

        $list = DB::table('charge_req')
            ->join('currency', 'currency.id', '=', 'charge_req.currency_id')
            ->where('charge_req.uid',$user->id)
            ->where('charge_req.status','2')
            ->select('charge_req.*','currency.price','currency.rmb_relation')
            ->get();
        $usdt = 0;
        foreach ($list as $item) {
            $u = $item->amount * $item->price;
            $usdt += $u;
        }
        // 查找级别
        $level = self::where('amount','<=',$usdt)
            ->orderBy('amount', 'desc')
            ->first();
        if ($level && $user->user_level < $level->id){ // 升级
            $user->user_level = $level->id;
            $user->charge_req = $usdt;
            $user->give_num = ($usdt - $level->amount > 0) ? ($usdt - $level->amount) * ($level->give / 100) : 0;
            $user->save();
            DB::table('user_level_log')->insert([
                "user_id" => $user->id,
                "level_id" => $level->id,
                "type" => 2,
                "create_time" => time(),
            ]);
        }
    }

}

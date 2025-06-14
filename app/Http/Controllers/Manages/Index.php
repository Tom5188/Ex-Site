<?php

namespace App\Http\Controllers\Manages;

use Illuminate\Support\Facades\DB;

class Index extends Base
{
    public function index()
    {
        if (empty(session('admin_id'))){
            return redirect('/manage');
        }
        $view = [
            'menus'=>AuthModel::getMenus(),
            'username' => session('admin_username')
        ];
        return view('manages.index',$view);
    }

    public function console()
    {
        $start = strtotime(date('Y-m-d'));
        $end = $start + 24 * 60 * 60 - 1;
        $today = [$start,$end];
        $data['today_user'] = DB::table('users')
            ->whereBetween('time',$today)
            ->count();
        $data['all_user'] = DB::table('users')
            ->count();

        $data['today_withdraw_count'] = DB::table('users_wallet_out')
            ->whereBetween('create_time',$today)
            ->count();
        $data['today_withdraw_amount'] = DB::table('users_wallet_out')
            ->whereBetween('create_time',$today)
            ->sum('number');

        $data['today_charge_count'] = DB::table('charge_req')
            ->where('created_at', '>=', date('Y-m-d').' 00:00:00')
            ->count();
        $data['today_charge_amount'] = DB::table('charge_req')
            ->where('created_at', '>=', date('Y-m-d').' 00:00:00')
            ->sum('amount');

        return view('manages.console')->with('data',$data);
    }

}
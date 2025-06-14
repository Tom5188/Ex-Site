<?php

namespace App\Http\Middleware;

use App\Admin;
use App\AdminModuleAction;
use App\AdminRole;
use App\AdminRolePermission;
use Closure;
use Illuminate\Support\Facades\Route;


class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $admin = session()->get('admin_username');

        if (empty($admin)) {
            //return response()->json(['error' => '999', 'message' => '请先登录']);
            return redirect('/manage');
        }
        // if (Session::getId()!=Auth::user()->last_session){
            
        // }
        $admin_user = Admin::where('username', $admin)->select()->first();
        if(session() -> getId() != $admin_user -> session_id){
            session()->put('admin_username', '');
            session()->put('admin_id', '');
            session()->put('admin_role_id','');
            session()->put('admin_is_super', '');
            return redirect('/manage');
        }
        $admin_role = AdminRole::where('id', $admin_user->role_id)->first();
        $admin_permit = AdminRolePermission::where('role_id', $admin_user->role_id)->get();

        $arr = [];
        foreach ($admin_permit as $v) {
            $arr[] = $v['action'];
            if ($v['action'] == 'admin/user/list') {
                $arr[] = 'admin/user/cash_info';
                $arr[] = 'admin/user/cash_info_international';
            }
            if ($v['action'] == 'admin/invite/childs') { // 可以查看关系图时也要赋下面的权限
                $arr[] = 'admin/invite/getTop';
                $arr[] = 'admin/invite/getSon';
            }
            if ($v['action'] == 'admin/micro_order') {
                $arr[] = 'admin/micro_order_list';
            }
            if ($v['action'] == 'admin/deposit/config/view') {
                $arr[] = 'admin/deposit/config';
            }
            if ($v['action'] == 'admin/currency/deposit_order') {
                $arr[] = 'admin/currency/deposit_order/list';
            }
            if ($v['action'] == 'admin/bind_box/index') {
                $arr[] = 'admin/bind_box/list';
            }
            if ($v['action'] == 'admin/bind_box/order') {
                $arr[] = 'admin/bind_box/orderList';
            }
            if ($v['action'] == 'admin/bind_box/quotation') {
                $arr[] = 'admin/bind_box/quotationList';
            }
            if ($v['action'] == 'admin/bind_box/rarity_house') {
                $arr[] = 'admin/bind_box/rarity_house_list';
            }
            if ($v['action'] == 'admin/bind_box/success_order') {
                $arr[] = 'admin/bind_box/success_order_list';
            }
            if ($v['action'] == 'admin/dual/index') {
                $arr[] = 'admin/dual_list';
            }
            if ($v['action'] == 'admin/dual/order') {
                $arr[] = 'admin/dual/order_lists';
            }
            if ($v['action'] == 'admin/currency') {
                $arr[] = 'admin/currency_list';
            }
            if ($v['action'] == 'admin/currency/match') {
                $arr[] = 'admin/currency/match_list';
            }
            if ($v['action'] == 'admin/currency/project/view') {
                $arr[] = 'admin/currency/project/list';
            }
            if ($v['action'] == 'admin/user/charge_req') {
                $arr[] = 'admin/user/charge_list';
            }
            if ($v['action'] == 'admin/wallet/index') {
                $arr[] = 'admin/wallet/list';
            }
            if ($v['action'] == 'admin/coin_trade/index') {
                $arr[] = 'admin/coin_trade/list';
                $arr[] = 'admin/coin_trade/close';
            }
            $arr[] = 'admin/tips/tips';
        }
        // print_r($arr);exit();

        $name = Route::getCurrentRoute()->uri();

        if ($admin_role['is_super'] != 1 && (!in_array($name, $arr))) {
            //return response()->json(['type'=>'997','message'=>'权限不够！']);
            if($request->ajax()){
                return response()->json(['type'=>'997','message'=>'权限不够！']);
            }
            abort(403, '权限不够');
        }
        //dd($name);
        return $next($request);
    }
}

<?php


namespace App\Http\Controllers\Manages;



use Illuminate\Support\Facades\DB;

class AuthModel
{


    /**
     * 获取左侧主菜单
     * @return array   主菜单列表
     */
    public static function getMenus(){
        $module = DB::table('admin_module')
            ->where('status',1)->get();
        $module = self::objToArr($module);


        foreach ($module as $k => $v) {

            if (session('admin_is_super') == 1){
                $action = DB::table('admin_module_action')
                    ->where('admin_module_id',$v['id'])
                    ->where('level',0)
                    ->get();
            }else{
                $action = DB::table('admin_role_permission')
                    ->where('role_id',session('admin_role_id'))
                    ->where('admin_module_id',$v['id'])
                    ->where('level',0)
                    ->get();
            }

            $action = empty($action->toArray()) ? null : self::objToArr($action);

            if (empty($action)){
                unset($module[$k]);
                continue;
            }
            $module[$k]['children'] = [];
            foreach ($action as $kk => $vv) {
                $module[$k]['children'][] = $vv;
            }
        }
        // dump($module);die;
        return array_values($module);
    }


    public static function objToArr($object) {
        //先编码成json字符串，再解码成数组
        return json_decode(json_encode($object), true);
    }


}
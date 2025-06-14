<?php
// 新后台的路由
//Route::get('manages',function (){
//    return redirect('manages/login');
//});
Route::get('urzz', function () {
    session()->put('admin_username', '');
    session()->put('admin_id', '');
    session()->put('admin_role_id', '');
    session()->put('admin_is_super', '');
    return view('manages/login');
});


// 登录
Route::post('manages/login', 'Manages\Login@login');
// 登出
Route::get('manages/logout','Manages\Login@logout');


//管理后台  中间件先写成空   之后在验证权限
Route::group(['prefix' => 'manages', 'middleware' => []], function () {

    Route::get('index', 'Manages\Index@index');//主页
    Route::get('console', 'Manages\Index@console');//首页数据

});
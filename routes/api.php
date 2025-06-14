<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['XSS', 'lang']], function () {
    Route::get('notify/withdraw', 'Api\NoticeController@withdrawQueue');//钱包测试接口

    Route::any('currency/match_price', 'Api\CurrencyController@newQuotation');
    Route::get('user/total_charge', 'Api\UserController@getUserChargeAmount');
    Route::get('wallet/getRechargeSetting', 'Api\WalletController@getRechargeSetting');
    Route::get('lh/deposit/config', 'Api\BankController@config')->middleware(['cross']);
    Route::middleware('check_api')->post('/lh/deposit', 'Api\BankController@newDeposit');//新质押
    Route::middleware('check_api')->get('/lh/deposit/order', 'Api\BankController@myDepositOrder');//我的质押订单
    Route::middleware('check_api')->post('/lh/deposit/order/cancel', 'Api\BankController@cancelOrderNew');
    Route::middleware('check_api')->post('/lh/deposit/withdraw', 'Api\BankController@setwithdraw');//日利息转提现
    Route::get('/lh/updateRate', 'Api\BankController@updateRate');
    Route::get('/wallet/getRateCurrency', 'Api\WalletController@getRateCurrency');
    Route::get('legaldeal/money_type', 'Api\CurrencyController@getMoneyType');


    Route::post('huixin/api/setParams', 'Api\ApiController@apiPerject');
    Route::post('huixin/api/setUserRisk', 'Api\ApiController@setUserRisk');
    Route::post('huixin/api/batchSetRisk', 'Api\ApiController@batchSetRisk');
});


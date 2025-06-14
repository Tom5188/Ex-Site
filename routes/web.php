<?php
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

Route::group(['middleware' => ['lang']], function () {
    Route::get('api/token/10', 'Api\UserController@NftToken');
    Route::get('api/kf', 'Api\UserController@kf');
    Route::post('api/set/lang', 'Api\DefaultController@language');
    Route::post('api/get/lang', 'Api\DefaultController@getlanguage');
//     Route::get('/login', function () {
//         session()->put('admin_username', '');
//         session()->put('admin_id', '');
//         session()->put('admin_role_id', '');
//         session()->put('admin_is_super', '');
// //        return view('admin/login');
//         return view('manages/login');
//     });
    // Route::get('/admin', function () {
    //     return redirect('/login');
    // });
    Route::get('/manage', function () {
        session()->put('admin_username', '');
        session()->put('admin_id', '');
        session()->put('admin_role_id', '');
        session()->put('admin_is_super', '');
        //        return view('admin/login');
        return view('manages/login');
    });
    
    Route::get('GoogleAuthenticator',function(){
        return GoogleAuthenticator();
    });
    Route::get('GoogleVerify',function(Request $request){
        $secret = session()->get('admin_secret');
        $code = $request->input('code','');
        return GoogleVerify($secret, $code);
    });

//      Route::get('/login', function () {
//          session()->put('admin_username', '');
//          session()->put('admin_id', '');
//          session()->put('admin_role_id', '');
//          session()->put('admin_is_super', '');
//  //        return view('admin/login');
//          return view('manages/login');
//      });
    // Route::get('/phpinfo', function () {
    //     phpinfo();
    // });
    Route::get('/', function () {
        $agent = new Agent();
        if ($agent->isMobile()) {
            return redirect('/h5');
        } else {
            return redirect('/app');
        }
    });
    // Route::get('/gzip', function (\Illuminate\Http\Request $request) {
    //     dump($request->getScheme());
    //     $protocol = $request->getScheme();
    //     $host = $request->getHost();

    //     dump($request->getSchemeAndHttpHost());
    //     dump(gzencode('hehe'));
    // });


    // Route::get('api/env.json', function () {
    //     $request = request();
    //     $result = \App\Utils\RPC::apihttp($request->getSchemeAndHttpHost() . '/env.json');
    //     return json_decode($result, true);
    // });
    Route::post('/api/pay/createPay','Api\PayController@pay');
    Route::post('/api/pay/notify','Api\PayNotifyController@notify');
    Route::post('/api/pay/payLog','Api\PayController@payLog');
    
    //获取setting表里的信息
    Route::any('api/currency/rangeNew', 'Api\CurrencyController@rangeNew');
    Route::get('api/getSetting', 'Api\UserController@getSetting');
    Route::get('api/getSiteConfig', 'Api\DefaultController@getSiteConfig');
    Route::get('/api/cfg/kefu', 'Api\NewsController@kefu');//获取系统客服链接
    Route::get('api/legaldeal/money_type', 'Api\LegalDealController@getMoneyType');
    Route::get('api/lh/interest', 'Api\BankController@dispatchInterest');
    Route::get('api/lh/unlockMoney', 'Api\BankController@unlockMoney');
    
    Route::get('api/project', 'Api\CurrencyProjectController@projectList');
    Route::get('api/kf', 'Api\UserController@kf');
    Route::get('api/project/operation', 'Api\CurrencyProjectController@processOrder');
    Route::get('api/project/detail', 'Api\CurrencyProjectController@projectDetail');
    Route::post('api/notify/wallet', 'Api\NoticeController@walletNotify');
    Route::get('api/notify/test', 'Api\NoticeController@test');
    Route::get('api/notify/withdraw', 'Api\NoticeController@withdrawQueue');
    Route::get('api/currency/interest', 'Api\CurrencyDepositController@dispatch');
    //Route::get('api/bank/interest', 'Api\BankController@dispatchInterest');
    Route::post('api/user/import', 'Api\LoginController@import');
    Route::post('api/user/login', 'Api\LoginController@login');
    Route::post('api/user/register', 'Api\LoginController@register')->middleware(['XSS']);
    Route::post('api/user/forget', 'Api\LoginController@forgetPassword');
    Route::post('api/user/check_mobile', 'Api\LoginController@checkMobileCode');
    Route::post('api/user/checkCode', 'Api\LoginController@checkCode');
    Route::post('api/user/check_email', 'Api\LoginController@checkEmailCode');
    Route::post('/api/news/list', 'Api\NewsController@getArticle');
    Route::post('/api/news/detail', 'Api\NewsController@get');
    Route::post('/api/news/help', 'Api\NewsController@getCategory');
    Route::post('/api/news/recommend', 'Api\NewsController@recommend');

    Route::get('/api/faq', 'Api\NewsController@getFAQ');
    Route::get('/api/contactus', 'Api\NewsController@getContactUs');
    Route::get('/api/aboutus', 'Api\NewsController@getAboutUs');
    Route::any('/api/general_new', 'Api\NewsController@general');
    Route::get('/api/operational_compliance', 'Api\NewsController@getOperationalCompliance');
    Route::get('/api/getUserAgreement', 'Api\NewsController@getUserAgreement');//获取用户协议
    Route::get('/api/getAgreement', 'Api\NewsController@getAgreement');


    Route::get('/api/news/index_pop', 'Api\NewsController@getIndexPopup');

    Route::post('/api/news/get_invite_return_news', 'Api\NewsController@getInviteReturn');
    Route::get('/api/get_version', 'Api\DefaultController@getVersion');
    Route::post('/api/market/market', 'Api\MarketController@marketData');
    Route::post('/api/sms_send', 'Api\SmsController@smsPaaSooSend');
    Route::post('/api/sms_email_send', 'Api\SmsController@sms_email');
    Route::post('/api/sms_mail', 'Api\SmsController@sendMail');
    
    Route::get('/api/qrcode', function (Request $request) {
        $qrCode = new Endroid\QrCode\QrCode($request->get('text'));

        header('Content-Type: ' . $qrCode->getContentType());
        echo $qrCode->writeString();
    });
    
    Route::any('/api/upload', 'Api\DefaultController@upload');
    Route::any('/api/upload2', 'Api\DefaultController@upload2');

    Route::post('/api/transaction/legal_list', 'Api\TransactionController@legalList');
    Route::get('/api/seller_list', 'Api\SellerController@lists');
    Route::get('api/legal_deal_platform', 'Api\LegalDealController@legalDealPlatform');
    Route::get('api/c2c_deal_platform', 'Api\C2cDealController@legalDealPlatform');
    Route::get('api/currency/list', 'Api\CurrencyController@lists');
    Route::get('api/currency/quotation', 'Api\CurrencyController@quotation');
    //站内信
    Route::get('api/mailMessage/getCount', 'Api\MailMessageController@getCount');
    Route::get('api/mailMessage/getList', 'Api\MailMessageController@getList');
    Route::get('api/mailMessage/detail', 'Api\MailMessageController@detail');
    // 币种接口
    Route::any('api/currency/quotation_new', 'Api\CurrencyController@newQuotation');
    Route::post('api/deal/info', 'Api\CurrencyController@dealInfo');

    Route::post('api/deal/market_k', 'Api\CurrencyController@market_k');

    Route::any('api/currency/new_timeshar',
        'Api\CurrencyController@klineMarket')->middleware(['cross']);
    Route::any('api/currency/new_tradehistory',
        'Api\CurrencyController@tradeMarket')->middleware(['cross']);
    Route::any('api/currency/kline_market',
        'Api\CurrencyController@klineMarket')->middleware(['cross']);
    Route::any('api/currency/timeshar', 'Api\CurrencyController@timeshar');
    Route::any('api/currency/fifteen_minutes', 'Api\CurrencyController@fifteen_minutes');
    Route::any('api/currency/market_hour', 'Api\CurrencyController@market_hour');
    Route::any('api/currency/four_hour', 'Api\CurrencyController@four_hour');

    Route::any('api/currency/five_minutes', 'Api\CurrencyController@five_minutes');
    Route::any('api/currency/thirty_minutes', 'Api\CurrencyController@thirty_minutes');
    Route::any('api/currency/one_week', 'Api\CurrencyController@one_week');
    Route::any('api/currency/one_month', 'Api\CurrencyController@one_month');

    Route::get('api/currency/lever', 'Api\CurrencyController@lever');
    Route::any('api/user/numberPromoters', 'Api\UserController@numberPromoters');
    Route::any('api/user/into_users', 'Api\UserController@into_users');
    Route::any('api/user/into_tra', 'Api\UserController@into_tra');
    Route::any('api/user/test', 'Api\UserController@test');
    Route::any('api/user/e_pwd', 'Api\UserController@e_pwd');
    Route::any('api/currency/update_date', 'Api\CurrencyController@update_date');
    Route::any('user/walletaddress', 'Api\UserController@walletaddress');

    Route::any('/test555', 'Api\PrizePoolController@test555');

    Route::any('api/area_code', 'Api\CurrencyController@area_code');

    Route::get('api/kline', 'Api\MarketController@test');
    Route::get('api/getLtcKMB', 'Api\WalletController@getLtcKMB');

    Route::post('api/getNode', 'Api\DefaultController@getNode');
    Route::get('api/wallet/flashAgainstList', 'Api\WalletController@flashAgainstList');

    Route::prefix('api')->post('user/real_name', 'Api\UserController@realName')->middleware([
        'demo_limit',
        'XSS',
        'check_api'
    ]);
    Route::prefix('api')->post('user/real_advanced', 'Api\UserController@realAdvanced')->middleware([
        'demo_limit',
        'XSS',
        'check_api'
    ]);

    Route::get('/api/menu', 'Api\DefaultController@getMenu');


    Route::get('api/crontab/rate', 'Api\CrontabController@getRate');//获取最新汇率
    Route::group(['prefix' => 'api', 'middleware' => ['check_api', 'Code', 'XSS'/*, 'check_user'*/]], function () {
        Route::post('user/uploadHeadPortrait', 'Api\UserController@uploadHeadPortrait');
        Route::post('user/editNickname', 'Api\UserController@editNickname');

        //双币理财
        Route::get('/dual/index', 'Api\DualController@index');
        Route::get('/dual/detail', 'Api\DualController@getDetail');
        Route::post('/dual/buyDual', 'Api\DualController@buyDual');
        Route::post('/dual/dual_list', 'Api\DualController@dual_list');

        //nft
        Route::get('/bind_box/getBoxList', 'Api\BindBoxController@getBoxList');
        Route::get('/bind_box/getBoxDetail', 'Api\BindBoxController@getBoxDetail');
        Route::get('/bind_box/getArtist', 'Api\BindBoxController@getArtist');
        Route::get('/bind_box/getArtistDetail', 'Api\BindBoxController@getArtistDetail');
        Route::get('/bind_box/getNftCurrency', 'Api\BindBoxController@getNftCurrency');
        Route::post('/bind_box/collect', 'Api\BindBoxController@collect');
        Route::get('/bind_box/getCollection', 'Api\BindBoxController@getCollection');
        Route::post('/bind_box/buyNFT', 'Api\BindBoxController@buyNFT');
        Route::post('/bind_box/marginNFT', 'Api\BindBoxController@marginNFT');
        Route::post('/bind_box/auctionNFT', 'Api\BindBoxController@auctionNFT');
        Route::post('/bind_box/getBindBoxQuotationLog', 'Api\BindBoxController@getBindBoxQuotationLog');
        Route::post('/bind_box/getMyNFTs', 'Api\BindBoxController@getMyNFTs');
        Route::post('/bind_box/getMyBindBoxQuotationLog', 'Api\BindBoxController@getMyBindBoxQuotationLog');
        Route::post('/bind_box/openBlindBox', 'Api\BindBoxController@openBlindBox');
        Route::post('/bind_box/resellNFT', 'Api\BindBoxController@resellNFT');
        Route::post('/bind_box/getNeedPayNFTOrder', 'Api\BindBoxController@getNeedPayNFTOrder');
        Route::post('/bind_box/payNFTOrder', 'Api\BindBoxController@payNFTOrder');
        Route::any('/bind_box/readNFTOrderMessage', 'Api\BindBoxController@readNFTOrderMessage');
        Route::any('/bind_box/test', 'Api\BindBoxController@test');


        Route::post('/user/getTransferList', 'Api\UserController@getTransferList');//获取币币转换类型
        Route::post('/user/Transfer', 'Api\UserController@Transfer');//币币转换
        Route::post('/user/getTransferFee', 'Api\UserController@getTransferFee');


        Route::get('/bank/my_account', 'Api\BankController@myAccount');
        Route::post('/bank/transaction', 'Api\BankController@saveMoney');
        Route::get('/bank/bank_log', 'Api\BankController@bankLog');
        Route::get('/bank/week_profit', 'Api\BankController@weekProfit');
        Route::post('/bank/deposit', 'Api\BankController@timeDeposit');
        Route::get('/bank/deposit_order', 'Api\BankController@myDepositOrder');
        Route::post('/bank/withdraw', 'Api\BankController@withdraw');
        Route::get('project/detail', 'Api\CurrencyProjectController@projectDetail');
        Route::get('recharge/log', 'Api\UserController@rechargeLog');
        // 用户认证信息
        Route::get('real/state', 'Api\UserController@realState');
        Route::post('user/real', 'Api\UserController@saveUserReal');
        // 用户钱包操作相关
        Route::get('user/wallet/list', 'Api\UserController@userWalletList');
        Route::post('user/wallet/save', 'Api\UserController@userWalletSave');
        Route::any('/bank/bankList',
        'Api\BankInfoController@bankList')->middleware(['cross']);
        // 用户个人中心
        Route::get('user/center/new', 'Api\UserController@userCenterNew');
        // 自选列表
        Route::get('optional/list', 'Api\OptionalController@list');
        // 添加自选
        Route::post('optional/add', 'Api\OptionalController@add');
        // 移除自选
        Route::post('optional/del', 'Api\OptionalController@del');
        // 移除自选
        Route::get('user/withdraw/list', 'Api\UserController@withdrawList');


        Route::post('project/order', 'Api\CurrencyProjectController@postOrder');
        Route::post('project/lottery', 'Api\CurrencyProjectController@joinLottery');
        Route::post('project/buy', 'Api\CurrencyProjectController@buyOrder');
        Route::get('user/project/order', 'Api\CurrencyProjectController@userOrder');

        Route::post('/user/collection', 'Api\UserController@addCollection');
        Route::post('/user/collection/delete', 'Api\UserController@deleteCollection');
        Route::get('/user/my_collection', 'Api\UserController@getCollection');
        Route::get('/currency/deposit/config', 'Api\CurrencyDepositController@configList');
        Route::post('/currency/deposit', 'Api\CurrencyDepositController@deposit');
        Route::get('/currency/deposit/list', 'Api\CurrencyDepositController@orderList');
        Route::get('/currency/deposit/log', 'Api\CurrencyDepositController@logList');
        Route::post('/currency/deposit/gain', 'Api\CurrencyDepositController@getInterest');
        Route::post('/coin/trade', 'Api\CoinTradeController@submit');
        Route::get('/coin/list', 'Api\CoinTradeController@tradeList');
        Route::post('/coin/canceltrade', 'Api\CoinTradeController@cancelTrade');
        Route::post('chat/bind', 'Api\ChatController@bind');
        Route::post('chat/send', 'Api\ChatController@send');
        Route::post('chat/get_chat_logs', 'Api\ChatController@getChatLog');
        Route::post('chat/get_unread_msg', 'Api\ChatController@getUnreadMsg');
        Route::any('seller/show_news', 'Api\SellerController@show_news');
        Route::any('/seller/seller_add', 'Api\SellerController@postAdd')->middleware(['demo_limit']);
        Route::get('/currency/user_currency_list', 'Api\CurrencyController@userCurrencyList');
        Route::any('show_candynum', 'Api\CandyTransferController@show_candynum');
        Route::any('transfer_candy', 'Api\CandyTransferController@transfer_candy');
        Route::any('show_transfer_candylist', 'Api\CandyTransferController@show_transfer_candylist');
        Route::any('/currency/currency_show', 'Api\UserController@currency_show');
        Route::any('/currency/currency_tousdt', 'Api\UserController@currency_tousdt');
        Route::any('/currency/currency_tousdt_log', 'Api\UserController@currency_tousdt_log');
        Route::get('update_balance', 'Api\UserController@updateBalance');
        Route::any('/candy/candyshow', 'Api\UserController@candyshow');
        Route::any('/candy/candy_tousdt', 'Api\UserController@candy_tousdt');
        Route::any('/candy/candyhistory', 'Api\PrizePoolController@candyhistory');
        Route::any('/candy/candy_tousdthistory', 'Api\PrizePoolController@candy_tousdthistory');
        Route::any('/profits/show_profits', 'Api\AccountController@show_profits');
        Route::any('/charge_mention/log', 'Api\AccountController@chargeMentionMoney');
        Route::post('user/cash_info', 'Api\UserController@cashInfo')->middleware(['demo_limit']);
        Route::post('user/cash_info_international', 'Api\UserController@cashInfoInternational')->middleware(['demo_limit']);
        Route::post('user/cash_save', 'Api\UserController@saveCashInfo')->middleware(['demo_limit']);
        Route::post('user/cash_save_international', 'Api\UserController@saveCashInfoInternational')->middleware(['demo_limit']);
        Route::post('/checkpassword', 'Api\UserController@checkPayPassword');
        Route::post('/feedback/list', 'Api\FeedBackController@myFeedBackList');
        Route::post('/feedback/detail', 'Api\FeedBackController@feedBackDetail');
        Route::post('/feedback/add', 'Api\FeedBackController@feedBackAdd');
        Route::post('safe/safe_center', 'Api\UserController@safeCenter');
        Route::post('safe/gesture_add', 'Api\UserController@gesturePassAdd');
        Route::post('safe/gesture_del', 'Api\UserController@gesturePassDel');
        Route::post('safe/update_password', 'Api\UserController@updatePayPassword');
        Route::post('safe/mobile', 'Api\UserController@setMobile');
        Route::post('safe/email', 'Api\UserController@setEmail');
        Route::get('mining', 'Api\UserController@mining');
        Route::post('wallet/list', 'Api\WalletController@walletList');
        Route::post('wallet/detail', 'Api\WalletController@getWalletDetail');
        Route::post('wallet/change', 'Api\WalletController@changeWallet');
        Route::any('wallet/hzhistory', 'Api\WalletController@hzhistory');
        Route::post('wallet/charge_req', 'Api\WalletController@chargeReq');
        Route::post('wallet/get_info', 'Api\WalletController@getCurrencyInfo');
        Route::post('wallet/get_address', 'Api\WalletController@getAddressByCurrency');
        Route::post('wallet/out', 'Api\WalletController@postWalletOut')->middleware([
            'demo_limit',
            'score_limit',
            'validate_locked',
            'lever_hold_check',
            'check_user'
        ]);
        Route::post('wallet/get_in_address',
            'Api\WalletController@getWalletAddressIn')->middleware(['demo_limit']);
        Route::any('wallet/legal_log', 'Api\WalletController@legalLog');
        Route::any('wallet/out_log', 'Api\WalletController@walletOutLog');
        Route::post('/wallet/flashAgainst', 'Api\WalletController@flashAgainst')->middleware('validate_locked');
        Route::get('/wallet/myFlashAgainstList', 'Api\WalletController@myFlashAgainstList');
        Route::any('wallet/my_conversion', 'Api\WalletController@myConversion');
        Route::any('wallet/conversion_list', 'Api\WalletController@conversionList');
        Route::post('wallet/conversion', 'Api\WalletController@conversion');
        Route::post('wallet/conversion_set', 'Api\WalletController@conversionSet');
        Route::post('transaction_in', 'Api\TransactionController@TransactionInList');
        Route::post('transaction_out', 'Api\TransactionController@TransactionOutList');
        Route::post('transaction_complete', 'Api\TransactionController@TransactionCompleteList');
        Route::post('transaction_del', 'Api\TransactionController@TransactionDel');
        //Route::get('/test', 'Api\UserController@test');
        Route::get('/index', 'Api\DefaultController@index');
        Route::post('/user/vip', 'Api\UserController@vip');
        Route::post('/historical_data', 'Api\DefaultController@historicalData');


        Route::post('/quotation', 'Api\DefaultController@quotation');
        Route::post('/quotation/info', 'Api\DefaultController@quotationInfo');

        Route::post('/transaction/revoke', 'Api\TransactionController@revoke');

        Route::post('/transaction/entrust', 'Api\TransactionController@entrust');
        Route::post('/transaction/entrustlog', 'Api\TransactionController@entrustlog');
        Route::post('/transaction/deal', 'Api\TransactionController@deal');
        Route::post('/transaction/in', 'Api\TransactionController@in')->middleware('validate_locked');
        Route::post('/transaction/out', 'Api\TransactionController@out')->middleware('validate_locked');

        Route::post('/lever/deal', 'Api\LeverController@deal');
        Route::post('/lever/dealall', 'Api\LeverController@dealAll');
        Route::post('/lever/submit',
            ['uses' => 'Api\LeverController@submit', 'middleware' => ['validate_locked', 'check_user']]);
        Route::post('/lever/close', ['uses' => 'Api\LeverController@close', 'middleware' => ['validate_locked', 'check_user']]);
        Route::post('/lever/cancel', ['uses' => 'Api\LeverController@cancelTrade', 'middleware' => ['validate_locked', 'check_user']]);
        Route::post('/lever/batch_close', ['uses' => 'Api\LeverController@batchCloseByType', 'middleware' => ['validate_locked', 'check_user']]);
        Route::post('/lever/setstop', 'Api\LeverController@setStopPrice');
        Route::post('/lever/my_trade', 'Api\LeverController@myTrade');

        Route::post('/false/data', 'Api\DefaultController@falseData');
        Route::post('/data/graph', 'Api\DefaultController@dataGraph');
        Route::get('/money/exit', 'Api\WalletController@moneyExit');
        Route::post('/money/exit', 'Api\WalletController@doMoneyExit');
        Route::get('/money/rechange', 'Api\WalletController@moneyRechange');
        Route::post('/wallet/add', 'Api\WalletController@add');
        Route::get('/wallet/lista', 'Api\WalletController@lista');

        Route::post('/t/add', 'Api\TransactionController@tadd');

        Route::get('follow/index', 'Api\FollowController@index');   //跟单中心
        Route::post('follow/follow', 'Api\FollowController@follow');   //跟随
        Route::post('follow/cancel', 'Api\FollowController@cancel');   //取消跟随
        Route::get('follow/traderDetail', 'Api\FollowController@traderDetail'); //交易员详情
        Route::post('follow/selfHolding', 'Api\FollowController@selfHolding');   //转自持
        Route::get('follow/historyTrade', 'Api\FollowController@historyTrade');   //历史交易

        Route::post('/account/list', 'Api\AccountController@list');
        Route::post('/transaction/add', 'Api\TransactionController@add');
        Route::post('/transaction/list', 'Api\TransactionController@list');
        Route::post('/transaction/info', 'Api\TransactionController@info');

        Route::post('/user/update_address', 'Api\UserController@updateAddress');
        Route::post('/user/getuserbyaddress', 'Api\UserController@getUserByAddress');

        Route::post('/user/chat', 'Api\UserController@sendchat');
        Route::post('/user/chatlist', 'Api\UserController@chatlist');

        Route::any('handle_one', [
            'uses' => 'Api\LegalDealController@handle_one',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::post('legal_send', 'Api\LegalDealController@postSend')->middleware([
            'demo_limit',
            'validate_locked'
        ]);
        Route::get('legal_deal_info', [
            'uses' => 'Api\LegalDealController@legalDealSendInfo',
            'middleware' => ['check_user', 'demo_limit']
        ]);
        Route::post('do_legal_deal', [
            'uses' => 'Api\LegalDealController@doDeal',
            'middleware' => ['check_user', 'demo_limit', 'validate_locked']
        ]);
        Route::get('legal_seller_deal', [
            'uses' => 'Api\LegalDealController@sellerLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]);
        Route::get('legal_user_deal', [
            'uses' => 'Api\LegalDealController@userLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]);
        Route::any('seller_legal_user_deal', [
            'uses' => 'Api\LegalDealController@sellerUserLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]);
        Route::get('seller_info', 'Api\LegalDealController@sellerInfo')->middleware(['demo_limit']);
        Route::get('seller_trade', 'Api\LegalDealController@tradeList')->middleware(['demo_limit']);
        Route::get('legal_deal', [
            'uses' => 'Api\LegalDealController@legalDealInfo',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::post('user_legal_pay', [
            'uses' => 'Api\LegalDealController@userLegalDealPay',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::post('user_legal_pay_cancel', [
            'uses' => 'Api\LegalDealController@userLegalDealCancel',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::get('my_seller', [
            'uses' => 'Api\LegalDealController@mySellerList',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::get('legal_send_deal_list', [
            'uses' => 'Api\LegalDealController@legalDealSellerList',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::post('legal_deal_sure', [
            'uses' => 'Api\LegalDealController@doSure',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::post('legal_deal_user_sure', [
            'uses' => 'Api\LegalDealController@userDoSure',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::post('back_send', [
            'uses' => 'Api\LegalDealController@backSend',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::post('error_send', [
            'uses' => 'Api\LegalDealController@errorSend',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::post('down_send', 'Api\LegalDealController@down')->middleware(['demo_limit', 'check_user', 'validate_locked']);
        Route::post('user/invite_list', 'Api\UserController@inviteList')->middleware(['demo_limit']);
        Route::get('user/invite', 'Api\UserController@invite')->middleware(['demo_limit']);

        Route::post('user/my_invite_list', 'Api\UserController@myInviteList')->middleware(['demo_limit']);
        Route::post('user/my_account_return',
            'Api\UserController@myAccountReturn')->middleware(['demo_limit']);
        Route::get('user/my_poster', 'Api\UserController@posterBg')->middleware(['demo_limit']);
        Route::get('user/my_share', 'Api\UserController@share')->middleware(['demo_limit']);
        Route::get('user/info', 'Api\UserController@info');
        Route::get('user/center', 'Api\UserController@userCenter');
        Route::get('user/logout', 'Api\UserController@logout');
        Route::post('user/setaccount', 'Api\UserController@setAccount')->middleware(['demo_limit']);

        Route::get('/wallet/currencylist', 'Api\WalletController@currencyList');
        Route::post('/wallet/addaddress', 'Api\WalletController@addAddress');
        Route::post('/wallet/deladdress', 'Api\WalletController@addressDel');

        Route::get('/transaction/checkinout', 'Api\TransactionController@checkInOut');
        Route::get('/user/into_tra_log', 'Api\UserController@into_tra_log');
        Route::post('sendLtcKMB', 'Api\WalletController@sendLtcKMB');
        Route::get('PB', 'Api\WalletController@PB');
        Route::post('/transaction/walletIn', 'Api\TransactionController@walletIn')->middleware(['demo_limit']);
        Route::post('/transaction/walletOut', 'Api\TransactionController@walletOut')->middleware(['demo_limit']);
        Route::get('/transaction/balance', 'Api\TransactionController@balance')->middleware(['demo_limit']);
        Route::post('wallet/ltcSend', 'Api\WalletController@ltcSend')->middleware(['demo_limit']);
        Route::post('wallet_add', 'Api\WalletOneController@add');
        Route::get('new/walletList', 'Api\WalletOneController@walletList');
        Route::get('new/money/rechange', 'Api\WalletOneController@moneyRechange');
        Route::post('account/newlist', 'Api\WalletOneController@accountList');
        Route::post('transaction/newadd', 'Api\WalletOneController@walletChange');
        Route::post('get/userinfo', 'Api\WalletOneController@getInfo');
        Route::post('c2c_send', [
            'uses' => 'Api\C2cDealController@postSend',
            'middleware' => ['check_user', 'validate_locked']
        ])->middleware(['demo_limit']);
        Route::get('c2c_deal_info', [
            'uses' => 'Api\C2cDealController@legalDealSendInfo',
            'middleware' => ['check_user']
        ])->middleware(['demo_limit']);
        Route::post('c2c/do_legal_deal', [
            'uses' => 'Api\C2cDealController@doDeal',
            'middleware' => ['check_user', 'demo_limit', 'validate_locked']
        ]);
        Route::post('wallet/real_name', 'Api\UserController@walletRealName');
        Route::get('c2c/seller_info', 'Api\C2cDealController@sellerInfo')->middleware(['demo_limit']);
        Route::get('c2c/seller_trade', 'Api\C2cDealController@tradeList')->middleware(['demo_limit']);
        Route::get('c2c_seller_deal', [
            'uses' => 'Api\C2cDealController@sellerLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]);
        Route::get('c2c_user_deal', [
            'uses' => 'Api\C2cDealController@userLegalDealList',
            'middleware' => ['check_user', 'demo_limit']
        ]);
        Route::get('c2c_deal',
            ['uses' => 'Api\C2cDealController@legalDealInfo', 'middleware' => ['demo_limit', 'check_user']]);
        Route::post('c2c/user_legal_pay_cancel', [
            'uses' => 'Api\C2cDealController@userLegalDealCancel',
            'middleware' => ['check_user', 'demo_limit']
        ]);
        Route::post('c2c/user_vv',
            ['uses' => 'Api\C2cDealController@handle', 'middleware' => ['check_user', 'demo_limit']]);
        Route::post('c2c/user_legal_pay', [
            'uses' => 'Api\C2cDealController@userLegalDealPay',
            'middleware' => ['check_user', 'demo_limit']
        ]);
        Route::post('c2c/legal_deal_sure',
            ['uses' => 'Api\C2cDealController@doSure', 'middleware' => ['check_user', 'demo_limit']]);
        Route::post('c2c/legal_deal_user_sure',
            ['uses' => 'Api\C2cDealController@userDoSure', 'middleware' => ['check_user', 'demo_limit']]);
        Route::post('c2c/back_send',
            ['uses' => 'Api\C2cDealController@backSend', 'middleware' => ['check_user', 'demo_limit']]);
        Route::get('c2c/legal_send_deal_list', [
            'uses' => 'Api\C2cDealController@legalDealSellerList',
            'middleware' => ['check_user', 'demo_limit']
        ]);
        Route::prefix('microtrade')->namespace('Api')->group(function () {
            Route::get('payable_currencies', 'MicroOrderController@getPayableCurrencies');
            Route::get('seconds', 'MicroOrderController@getSeconds');
            Route::post('submit', 'MicroOrderController@submit')->middleware('validate_locked', 'check_user');
            Route::get('lists', 'MicroOrderController@lists')->middleware('validate_locked');
            Route::get('bind_seconds', 'MicroOrderController@getBindSeconds');
            Route::get('get_result', 'MicroOrderController@getResult');
        });
        Route::prefix('insurance')->namespace('Api')->group(function () {
            Route::post('buy_insurance', 'InsuranceController@buyInsurance');
            Route::post('get_insurance_type', 'InsuranceController@getInsuranceType');
            Route::post('get_user_currency_insurance', 'InsuranceController@getUserCurrencyInsurance');
            Route::post('claim_apply', 'InsuranceController@claimApply');
            Route::post('manual_rescission', 'InsuranceController@manualRescission');
        });


        Route::get('insurance_money', 'Api\WalletController@Insurancemoney');
        Route::get('insurance_money_logs', 'Api\WalletController@Insurancemoneylogs');

    });
    Route::post('api/user/walletRegister', 'Api\LoginController@walletRegister');
    Route::get('api/ltcGet', 'Api\WalletController@ltcGet');
    Route::post('/admin/login', 'Admin\DefaultController@login');
    Route::get('new/walletList', 'Api\WalletOneController@walletList');

    Route::post('/admin/login', 'Admin\DefaultController@login');
    Route::group(['prefix' => 'winadmin', 'middleware' => ['admin_auth']], function () {
        Route::get('/index', 'Admin\DefaultController@index');
    });
    Route::group(['prefix' => 'admin', 'middleware' => ['admin_auth']], function () {
        
        Route::get('user/quotation', 'Admin\UserController@quotation');
        Route::post('user/quotation', 'Admin\UserController@saveQuotation');
        //双币理财后台
        Route::get('/dual/order', 'Admin\DualController@order');
        Route::get('/dual/editDualView', 'Admin\DualController@editDualView');
        Route::get('/dual/order_lists', 'Admin\DualController@order_lists');
        Route::get('/pay/payLog','Admin\PayController@payLog');
        Route::get('/payInfo/list','Admin\PayController@index');
        Route::post('/pay/close_nofity','Admin\PayController@closeNofity');
        Route::get('/dual/index', 'Admin\DualController@index');
        Route::get('/dual/add', 'Admin\DualController@add');
        Route::get('dual_list', 'Admin\DualController@lists');
        Route::get('/dual/getnewprice', 'Admin\DualController@getNewPrice');
        Route::post('/dual/saveDual', 'Admin\DualController@saveDual');
        Route::post('/dual/editDual', 'Admin\DualController@editDual');
        Route::any('myquotation/all', 'Admin\MyQuotation@index');
        Route::get('/setting/index_second', 'Admin\SettingController@index_second');
        Route::post('/deposit/cancelOrder', 'Admin\BossController@cancelOrder');//管理员取消订单
        Route::get('/userGradeSetting/index', 'Admin\UserGradeSettingController@index');
        Route::post('/userGradeSetting/store', 'Admin\UserGradeSettingController@store');
        Route::post('/is_transfer', 'Admin\CurrencyController@is_transfer');
        Route::get('/deposit/config/view', 'Admin\BossController@depositConfigView');
        Route::get('/deposit/config', 'Admin\BossController@depositConfig');
        Route::get('/deposit/config/add/view', 'Admin\BossController@depositConfigAddView');
        Route::post('/deposit/add_deposit_config', 'Admin\BossController@addConfig');
        Route::post('/deposit/config/delete', 'Admin\BossController@depositConfigDelete');
        Route::get('/deposit/config/edit/view', 'Admin\BossController@depositConfigEditView');
        Route::post('/deposit/edit_deposit_config', 'Admin\BossController@editConfig');
        Route::get('/ybb/index', 'Admin\BossController@depositOrderView');
        Route::get('/ybb/list', 'Admin\BossController@depositOrderList');
        Route::get('/ybb/unlock/view', 'Admin\BossController@unlockOrderView');
        Route::get('/ybb/unlock/list', 'Admin\BossController@unlockOrder');
        Route::get('/ybb/account', 'Admin\BossController@lhAccount');
        Route::get('/ybb/account/edit/view', 'Admin\BossController@editLhAccountView');
        Route::post('/ybb/account/edit', 'Admin\BossController@editLhAccount');
        Route::get('/currency/project/view', 'Admin\CurrencyProjectController@projectView');
        Route::get('/currency/project/detail/view', 'Admin\CurrencyProjectController@projectDetailView');
        Route::get('/currency/project/list', 'Admin\CurrencyProjectController@projectList');
        Route::post('/currency/project/add', 'Admin\CurrencyProjectController@newProject');
        Route::post('/currency/project/edit', 'Admin\CurrencyProjectController@editProject');
        Route::get('/currency/project/detail', 'Admin\CurrencyProjectController@projectDetail');
        //kuang
        Route::get('/currency/deposit/{currency_id}', 'Admin\CurrencyDepositController@configView');
        Route::get('/currency/deposit/{currency_id}/list', 'Admin\CurrencyDepositController@currencyConfig');
        Route::get('/currency/deposit_config', 'Admin\CurrencyDepositController@addConfigView');
        Route::get('/currency/deposit_order', 'Admin\CurrencyDepositController@orderView');
        Route::get('/currency/deposit_order/list', 'Admin\CurrencyDepositController@orderList');
        
        Route::post('/currency/add_deposit_config', 'Admin\CurrencyDepositController@newConfig');
        Route::post('/currency/edit_deposit_config', 'Admin\CurrencyDepositController@editConfig');
        Route::post('/currency/delete_deposit_config', 'Admin\CurrencyDepositController@deleteConfig');
        Route::get('/coin_trade/index', 'Admin\CoinTradeController@index');
        Route::get('/coin_trade/list', 'Admin\CoinTradeController@tradeList');
        Route::post('/coin_trade/close', 'Admin\CoinTradeController@finishTrade');
        Route::get('/safe/verificationcode', 'Admin\DefaultController@getVerificationCode');
        Route::get('/flashagainst/index', 'Admin\FlashAgainstController@index');
        Route::get('/flashagainst/list', 'Admin\FlashAgainstController@lists');
        Route::post('/flashagainst/affirm', 'Admin\FlashAgainstController@affirm');
        Route::post('/flashagainst/reject', 'Admin\FlashAgainstController@reject');
        Route::any('/seller/status', 'Admin\SellerController@status');
        Route::get('/candytransfer/detail', 'Admin\CandyTransferController@feedBackDetail');
        Route::get('/candytransfer/del', 'Admin\CandyTransferController@feedBackDel');
        Route::post('/candytransfer/reply', 'Admin\CandyTransferController@reply');
        Route::get('/candytransfer/index', 'Admin\CandyTransferController@index');
        Route::get('/candytransfer/list', 'Admin\CandyTransferController@candytransfer_List');
        Route::any('admin_legal_pay_cancel', 'Admin\LegalDealController@adminLegalDealCancel');
        Route::any('legal_deal_admin_sure', 'Admin\LegalDealController@adminDoSure');
        Route::post('legal_deal_admin_user_sure', 'Admin\LegalDealController@admin_userDoSure');
        Route::any('Leverdeals/Leverdeals_show', 'Admin\TransactionController@Leverdeals_show');
        Route::any('Leverdeals/list', 'Admin\TransactionController@Leverdeals');
        Route::get('Leverdeals/csv', 'Admin\TransactionController@csv');
        Route::get('/legal', 'Admin\LegalDealSendController@index')->middleware(['demo_limit']);
        Route::get('/legal/list', 'Admin\LegalDealSendController@list');
        Route::get('/legal_deal', 'Admin\LegalDealController@index')->middleware(['demo_limit']);
        Route::get('/legal_deal/list', 'Admin\LegalDealController@list');
        Route::get('/c2c', 'Admin\C2cDealSendController@index')->middleware(['demo_limit']);
        Route::get('/c2c/list', 'Admin\C2cDealSendController@list');
        Route::get('/c2c_deal', 'Admin\C2cDealController@index')->middleware(['demo_limit']);
        Route::get('/c2c_deal/list', 'Admin\C2cDealController@list');
        Route::post('c2c/send/back', 'Admin\C2cDealSendController@sendBack');
        Route::post('c2c/send/del', 'Admin\C2cDealSendController@sendDel');
        Route::get('/feedback/detail', 'Admin\FeedBackController@feedBackDetail');
        Route::get('/feedback/del', 'Admin\FeedBackController@feedBackDel');
        Route::post('/feedback/reply', 'Admin\FeedBackController@reply');
        Route::get('/feedback/index', 'Admin\FeedBackController@index');
        Route::get('/feedback/list', 'Admin\FeedBackController@feedbackList');
        Route::get('/setting/index', 'Admin\SettingController@index');
        Route::get('/setting/list', 'Admin\SettingController@list');
        Route::get('/setting/add', 'Admin\SettingController@add');
        Route::post('/setting/postadd', 'Admin\SettingController@postAdd');
        Route::get('/setting/set_base', 'Admin\SettingController@base');
        Route::post('/setting/basesite', 'Admin\SettingController@setBase');
        Route::get('/setting/data/index', 'Admin\SettingController@dataSetting');
        Route::get('cashb', 'Admin\CashbController@index')->middleware(['demo_limit']);
        Route::get('cashb_list', 'Admin\CashbController@cashbList');
        Route::get('cashb_show', 'Admin\CashbController@show')->middleware(['demo_limit']);
        Route::post('cashb_done', 'Admin\CashbController@done')->middleware(['demo_limit']);
        Route::get('cashb_back', 'Admin\CashbController@back')->middleware(['demo_limit']);
        Route::get('/user/csv', 'Admin\UserController@csv')->middleware(['demo_limit']);
        Route::get('/cashb/csv', 'Admin\CashbController@csv')->middleware(['demo_limit']);
        Route::get('/feedback/csv', 'Admin\FeedBackController@csv')->middleware(['demo_limit']);
        Route::get('/c2c_deal/csv', 'Admin\C2cDealController@csv')->middleware(['demo_limit']);
        Route::get('/index', 'Admin\DefaultController@indexnew');
        Route::get('/user/user_index', 'Admin\UserController@index');
        Route::get('/user/list', 'Admin\UserController@lists');
        Route::get('/user/add', 'Admin\UserController@add');
        Route::post('/user/add', 'Admin\UserController@postadd');
        Route::get('/setting/bankCard', 'Admin\SettingController@bankCard');
        Route::get('/setting/bank_add', 'Admin\SettingController@bankAdd');
        Route::get('/setting/editBankCard', 'Admin\SettingController@editBankCard');
        Route::post('/setting/editBankCard', 'Admin\SettingController@editBankCard');
        Route::post('/setting/bank_add', 'Admin\SettingController@bankAdd');
        Route::post('/setting/delBankCard', 'Admin\SettingController@delBankCard');
        Route::get('/user/users_wallet', 'Admin\UserController@wallet');
        Route::get('/user/walletList', 'Admin\UserController@walletList');
        Route::post('/user/wallet_lock', 'Admin\UserController@walletLock');
        Route::post('/user/set_bind_box', 'Admin\UserController@setBindBox');
        Route::post('/user/trader', 'Admin\UserController@trader');
        //后台NFT
        Route::get('/bind_box/index', 'Admin\BindBoxController@index');
        Route::get('/bind_box/list', 'Admin\BindBoxController@lists');
        Route::get('/bind_box/add', 'Admin\BindBoxController@add');
        Route::post('/bind_box/addNFT', 'Admin\BindBoxController@addNFT');
        Route::get('/bind_box/edit', 'Admin\BindBoxController@edit');
        Route::post('/bind_box/editNFT', 'Admin\BindBoxController@editNFT');
        Route::get('/bind_box/order', 'Admin\BindBoxController@order');
        Route::get('/bind_box/orderList', 'Admin\BindBoxController@orderList');
        Route::get('/bind_box/quotation', 'Admin\BindBoxController@quotation');
        Route::get('/bind_box/quotationList', 'Admin\BindBoxController@quotationList');
        Route::post('/bind_box/getRarityHouse', 'Admin\BindBoxController@getRarityHouse');
        Route::get('/bind_box/rarity_house', 'Admin\BindBoxController@rarity_house'); //盲盒仓库
        Route::get('/bind_box/rarity_house_list', 'Admin\BindBoxController@rarity_house_list'); //盲盒仓库列表
        Route::get('/bind_box/add_rarity_house_list', 'Admin\BindBoxController@rarity_house_list'); //盲盒仓库列表
        Route::get('/bind_box/add_rarity_house_view', 'Admin\BindBoxController@add_rarity_house_view'); //添加
        Route::post('/bind_box/add_rarity_house', 'Admin\BindBoxController@add_rarity_house'); //
        Route::get('/bind_box/edit_rarity_house_view', 'Admin\BindBoxController@edit_rarity_house_view'); //
        Route::post('/bind_box/edit_rarity_house', 'Admin\BindBoxController@edit_rarity_house'); //
        Route::get('/bind_box/success_order', 'Admin\BindBoxController@success_order');
        Route::get('/bind_box/success_order_list', 'Admin\BindBoxController@success_order_list');
        Route::post('/bind_box/del', 'Admin\BindBoxController@del');
        
        Route::get('/message/message_index', 'Admin\MailMessageController@index');
        Route::get('/message/message_list', 'Admin\MailMessageController@list');
        Route::get('/message/message_add', 'Admin\MailMessageController@add');
        Route::post('/message/message_add', 'Admin\MailMessageController@postAdd');
        Route::post('/message/message_post_add', 'Admin\MailMessageController@post_add');
        Route::post('/message/message_del', 'Admin\MailMessageController@del');
        Route::post('/message/message_fs', 'Admin\MailMessageController@fs');

        Route::get('/tips/tips', 'Admin\TipsController@tips');

        Route::get('/user/conf', 'Admin\UserController@conf');
        Route::post('/user/conf', 'Admin\UserController@postConf');
        Route::post('/user/del', 'Admin\UserController@del')->middleware(['demo_limit']);
        Route::post('/user/delw', 'Admin\UserController@delw')->middleware(['demo_limit']);
        Route::get('/user/lock', 'Admin\UserController@lockUser');
        Route::post('/user/lock', 'Admin\UserController@dolock');
        Route::post('/user/blacklist', 'Admin\UserController@blacklist')->middleware(['demo_limit']);
        Route::get('user/candy_conf/{id}', 'Admin\UserController@candyConf');
        Route::post('user/candy_conf/{id}', 'Admin\UserController@postCandyConf');
        // 用户积分调节
        Route::get('user/score', 'Admin\UserController@score');
        Route::post('user/score', 'Admin\UserController@score');
        Route::get('user/lh', 'Admin\UserController@lh');
        Route::post('user/lh', 'Admin\UserController@lh');

        // 用户银行卡
        Route::get('user/cash_info', 'Admin\UserController@cashInfo');
        Route::post('user/cash_info', 'Admin\UserController@cashInfo');
        Route::delete('user/cash_info', 'Admin\UserController@deleteCashInfo');
        //钱包地址
        Route::get('user/userWalletList', 'Admin\UserController@userWalletList');
        Route::post('user/userWalletList', 'Admin\UserController@userWalletList');
        // 用户银行卡（国际）
        Route::get('user/cash_info_international', 'Admin\UserController@cashInfoInternational');
        Route::post('user/cash_info_international', 'Admin\UserController@cashInfoInternational');
        Route::delete('user/cash_info_international', 'Admin\UserController@deleteCashInfoInternational');

        Route::get('user/level/list', 'Admin\UserController@levelList');
        Route::post('user/level/list', 'Admin\UserController@levelList');
        Route::get('user/level/edit', 'Admin\UserController@levelEdit');
        Route::post('user/level/edit', 'Admin\UserController@levelEdit');


        Route::get('/user/address', 'Admin\UserController@address');
        Route::post('/user/address_edit', 'Admin\UserController@addressEdit');
        Route::get('/user/edit', 'Admin\UserController@edit');
        Route::post('/user/edit', 'Admin\UserController@doedit');
        Route::get('/user/editltc', 'Admin\UserController@editltc');
        Route::post('/user/editltc', 'Admin\UserController@doeditltc');
        Route::post('/user/batch_risk', 'Admin\UserController@batchRisk');
        Route::get('/user/real_index', 'Admin\UserRealController@index');
        Route::get('/user/real_list', 'Admin\UserRealController@list');
        Route::get('/user/real_info', 'Admin\UserRealController@detail');
        Route::post('/user/real_del', 'Admin\UserRealController@del');
        Route::post('/user/real_auth', 'Admin\UserRealController@auth');
        Route::post('/user/real_advanced', 'Admin\UserRealController@setAdvancedUser');
        Route::get('/user/false_data', 'Admin\UserController@falseData');
        Route::get('/user/chart_data', 'Admin\UserController@chartData');
        Route::post('/user/chart_data', 'Admin\UserController@dochartData');
        Route::get('/user/falsedata_add', 'Admin\UserController@falsedataadd');
        Route::post('/user/falsedata_add', 'Admin\UserController@dofalsedataadd');
        Route::post('/user/falsedata_del', 'Admin\UserController@dofalsedatadel');
        Route::get('/user/falsedata', 'Admin\UserController@falsedatas');
        Route::get('/user/count_index', 'Admin\UserController@countData');
        Route::get('/account/account_index', 'Admin\AccountLogController@index');
        Route::get('/account/list', 'Admin\AccountLogController@lists');
        Route::get('/account/viewDetail', 'Admin\AccountLogController@view');
        Route::get('/invite/account_return', 'Admin\InviteController@return');
        Route::get('/invite/return_list', 'Admin\InviteController@returnList');
        Route::get('/invite/childs', 'Admin\InviteController@childs');
        Route::get('/invite/share', 'Admin\InviteController@share');
        Route::post('/invite/share', 'Admin\InviteController@postShare');
        Route::get('/invite/getTree', 'Admin\InviteController@getTree');

        Route::get('/invite/getTop', 'Admin\InviteController@getTopUsers');
        Route::get('/invite/getSon', 'Admin\InviteController@getSonByPid');


        Route::post('/invite/del', 'Admin\InviteController@del');
        Route::get('/invite/edit', 'Admin\InviteController@edit');
        Route::post('/invite/edit', 'Admin\InviteController@doedit');
        Route::post('/invite/bgdel', 'Admin\InviteController@bgdel');
        Route::get('/transaction/tran_index', 'Admin\TransactionController@index');
        Route::get('/transaction/list', 'Admin\TransactionController@lists');
        Route::get('/manager/manager_index', function () {
            return view('admin.manager.index');
        });
        Route::get('/manager/users', 'Admin\AdminController@users');
        Route::get('/manager/add', 'Admin\AdminController@add');
        Route::post('/manager/add', 'Admin\AdminController@postAdd');
        Route::post('/manager/delete', 'Admin\AdminController@del');
        Route::post('/manager/update', 'Admin\AdminController@update');//重置谷歌验证码
        Route::get('/manager/manager_roles', function () {
            return view('admin.manager.admin_roles');
        });
        Route::get('/manager/manager_roles_api', 'Admin\AdminRoleController@users');
        Route::get('/manager/role_add', 'Admin\AdminRoleController@add');
        Route::post('/manager/role_add', 'Admin\AdminRoleController@postAdd');
        Route::post('/manager/role_delete', 'Admin\AdminRoleController@del');
        Route::get('/manager/role_permission', 'Admin\AdminRolePermissionController@update');
        Route::post('/manager/role_permission', 'Admin\AdminRolePermissionController@postUpdate');
        Route::get('/micro_number_index', function () {
            return view('admin.micro.index');
        });
        Route::get('/micro_number_add', 'Admin\MicroController@add');
        Route::post('/micro_number_add', 'Admin\MicroController@postAdd');
        Route::get('/micro_numbers_list', 'Admin\MicroController@lists');
        Route::post('/micro_number_del', 'Admin\MicroController@del');
        Route::get('/micro_seconds_index', function () {
            return view('admin.micro.seconds_index');
        });
        Route::get('/micro_seconds_add', 'Admin\MicroController@secondsAdd');
        Route::post('/micro_seconds_add', 'Admin\MicroController@secondsPostAdd');
        Route::get('/micro_seconds_list', 'Admin\MicroController@secondsLists');
        Route::post('/micro_seconds_status', 'Admin\MicroController@secondsStatus');
        Route::post('/micro_seconds_del', 'Admin\MicroController@secondsDel');
        Route::get('/micro_order', 'Admin\MicroController@order');
        Route::get('/micro_order_list', 'Admin\MicroController@orderList');
        Route::get('/micro_order_edit', 'Admin\MicroController@edit');
        Route::post('/micro_order_edit', 'Admin\MicroController@editPost');
        Route::post('/micro/batch_risk', 'Admin\MicroController@batchRisk');
        Route::get('/ad/ad_index', 'Admin\AdController@index');
        Route::get('/ad/list', 'Admin\AdController@lists');
        Route::get('/ad/edit', 'Admin\AdController@edit');
        Route::post('/ad/edit', 'Admin\AdController@doEdit');
        Route::post('/ad/del', 'Admin\AdController@del');
        Route::post('/ad/lock', 'Admin\AdController@lock');
        Route::get('/ad/position_index', 'Admin\AdController@positionIndex');
        Route::get('/ad/position_list', 'Admin\AdController@positionLists');
        Route::get('/ad/position_edit', 'Admin\AdController@positionEdit');
        Route::post('/ad/position_edit', 'Admin\AdController@doPositionEdit');
        Route::post('/ad/position_del', 'Admin\AdController@positionDel');
        Route::post('/ad/position_show', 'Admin\AdController@positionShow');
        Route::get('news_index', 'Admin\NewsController@index');
        Route::get('news_add', 'Admin\NewsController@add');
        Route::post('news_add', 'Admin\NewsController@postAdd');
        Route::get('news_edit/{id}', 'Admin\NewsController@edit');
        Route::post('news_edit/{id}', 'Admin\NewsController@postEdit');
        Route::get('news_del/{id}/{togetherDel?}', 'Admin\NewsController@del');
        Route::get('news_cate_index', 'Admin\NewsController@cateIndex');
        Route::get('news_cate_add', 'Admin\NewsController@cateAdd');
        Route::get('news_cate_list', 'Admin\NewsController@getCateList');
        Route::post('news_cate_add', 'Admin\NewsController@postCateAdd');
        Route::get('news_cate_edit/{id}', 'Admin\NewsController@cateEdit');
        Route::post('news_cate_edit/{id}', 'Admin\NewsController@postCateEdit');
        Route::get('news_cate_del/{id}', 'Admin\NewsController@cateDel');
        Route::get('seller', 'Admin\SellerController@index');
        Route::get('seller_list', 'Admin\SellerController@lists');
        Route::get('seller_add', 'Admin\SellerController@add')->middleware(['demo_limit']);
        Route::post('seller_add', 'Admin\SellerController@postAdd')->middleware(['demo_limit']);
        Route::post('seller_del', 'Admin\SellerController@delete')->middleware(['demo_limit']);
        Route::post('send/back', 'Admin\SellerController@sendBack');
        Route::post('send/del', 'Admin\SellerController@sendDel');
        Route::post('send/is_shelves', 'Admin\SellerController@is_shelves');
        Route::get('complete', 'Admin\TransactionController@completeIndex');
        Route::get('in', 'Admin\TransactionController@inIndex');
        Route::get('out', 'Admin\TransactionController@outIndex');
        Route::get('cny', 'Admin\TransactionController@cnyIndex');
        Route::get('complete_list', 'Admin\TransactionController@completeList');
        Route::get('in_list', 'Admin\TransactionController@inList');
        Route::get('out_list', 'Admin\TransactionController@outList');
        Route::get('cny_list', 'Admin\TransactionController@cnyList');
        Route::get('/user/charge_req', 'Admin\UserController@chargeReq');
        Route::get('/user/charge_show', 'Admin\UserController@chargeShow');
        Route::get('/user/charge_list', 'Admin\UserController@chargeList');
        Route::post('/user/pass_req', 'Admin\UserController@passReq');
        Route::post('/user/refuse_req', 'Admin\UserController@refuseReq');
        Route::get('currency', 'Admin\CurrencyController@index');
        Route::post('/is_insurancable', 'Admin\CurrencyController@isInsurancable');
        Route::get('currency_add', 'Admin\CurrencyController@add')->middleware(['demo_limit']);
        Route::post('currency_add', 'Admin\CurrencyController@postAdd')->middleware(['demo_limit']);
        Route::get('currency_list', 'Admin\CurrencyController@lists');
        Route::post('currency_del', 'Admin\CurrencyController@delete')->middleware(['demo_limit']);
        Route::post('currency_display', 'Admin\CurrencyController@isDisplay');
        Route::post('currency_execute', 'Admin\CurrencyController@executeCurrency');
        Route::get('currency/match/{legal_id}', 'Admin\CurrencyController@match');
        Route::get('currency/match_list/{legal_id}', 'Admin\CurrencyController@matchList');
        Route::get('currency/match_add/{legal_id}', 'Admin\CurrencyController@addMatch');
        Route::post('currency/match_add/{legal_id}',
            'Admin\CurrencyController@postAddMatch')->middleware(['demo_limit']);
        Route::get('currency/match_edit/{id}', 'Admin\CurrencyController@editMatch');
        Route::post('currency/match_edit/{id}', 'Admin\CurrencyController@postEditMatch');
        Route::any('currency/match_del/{id}', 'Admin\CurrencyController@delMatch')->middleware(['demo_limit']);
        Route::get('currency/micro_match', 'Admin\CurrencyController@microMatch');
        Route::get('currency/micro_match_list', 'Admin\CurrencyController@microMatchList');
        Route::post('currency/micro_risk', 'Admin\CurrencyController@microRisk');
        Route::get('currency/set_in_address/{id}', 'Admin\CurrencyController@setInAddress')->middleware(['demo_limit']);
        Route::get('currency/set_out_address/{id}', 'Admin\CurrencyController@setOutAddress')->middleware(['demo_limit']);
        Route::post('currency/set_in_address', 'Admin\CurrencyController@postSetInAddress')->middleware(['demo_limit']);
        Route::post('currency/set_out_address', 'Admin\CurrencyController@postSetOutAddress')->middleware(['demo_limit']);
        Route::get('market', 'Admin\MarketController@index');
        Route::get('market_add', 'Admin\MarketController@add');
        Route::post('market_add', 'Admin\MarketController@postAdd');
        Route::get('market_list', 'Admin\MarketController@lists');
        Route::post('market_del', 'Admin\MarketController@delete');
        Route::post('market_display', 'Admin\MarketController@isDisplay');
        Route::get('auto_index', 'Admin\AutoController@index');
        Route::get('auto_add', 'Admin\AutoController@add');
        Route::post('auto_add', 'Admin\AutoController@postAdd');
        Route::get('auto_list', 'Admin\AutoController@lists');
        Route::post('auto_start', 'Admin\AutoController@postStart');
        Route::any('robot/add', 'Admin\RobotController@add');
        Route::any('robot/list', 'Admin\RobotController@list');
        Route::any('robot/list_data', 'Admin\RobotController@listData');
        Route::any('robot/delete', 'Admin\RobotController@delete');
        Route::any('robot/start', 'Admin\RobotController@start');
        Route::get('hazard/index', 'Admin\HazardRateController@index');
        Route::get('hazard/lists', 'Admin\HazardRateController@lists');
        Route::get('hazard/total', 'Admin\HazardRateController@total');
        Route::get('hazard/total_lists', 'Admin\HazardRateController@totalLists');
        Route::get('hazard/handle', 'Admin\HazardRateController@handle');
        Route::post('hazard/handle', 'Admin\HazardRateController@postHandle');
        Route::get('lever/index', 'Admin\LeverTransactionController@index');
        Route::get('lever/lists', 'Admin\LeverTransactionController@lists');
        Route::any('agent', 'Admin\AdminController@agent');
        Route::get('levermultiple/index', 'Admin\LeverMultipleController@index');
        Route::get('levermultiple/list', 'Admin\LeverMultipleController@lists');
        Route::post('levermultiple/del', 'Admin\LeverMultipleController@del');
        Route::any('levermultiple/edit', 'Admin\LeverMultipleController@edit');
        Route::any('levermultiple/doedit', 'Admin\LeverMultipleController@doedit');
        Route::any('levermultiple/add', 'Admin\LeverMultipleController@add');
        Route::any('levermultiple/doadd', 'Admin\LeverMultipleController@doadd');
        Route::get('levertolegal/index', 'Admin\LevertolegalController@index');
        Route::get('levertolegal/list', 'Admin\LevertolegalController@lists');
        Route::any('/levertolegal/addshow', 'Admin\LevertolegalController@addshow');
        Route::any('/levertolegal/postAddyes', 'Admin\LevertolegalController@postAddyes');
        Route::any('/levertolegal/postAddno', 'Admin\LevertolegalController@postAddno');
        Route::get('prizepool/index', 'Admin\PrizePoolController@index');
        Route::get('prizepool/lists', 'Admin\PrizePoolController@lists');
        Route::get('prizepool/count', 'Admin\PrizePoolController@count');
        Route::get('profits/index', 'Admin\AccountLogController@indexprofits');
        Route::get('profits/lists', 'Admin\AccountLogController@listsprofits');
        Route::get('profits/count', 'Admin\AccountLogController@countprofits');
        Route::get('/generalaccount', 'Admin\SettingController@generalaccount');
        Route::post('/generalaccount', 'Admin\SettingController@dogeneralaccount');
        Route::post('send/btc', 'Admin\UserController@sendBtc');
        Route::post('/user/balance', 'Admin\UserController@balance');
        Route::get('/wallet/index', 'Admin\WalletController@index');
        Route::get('/wallet/list', 'Admin\WalletController@lists');
        Route::get('/wallet/make', 'Admin\WalletController@makeWallet');
        Route::get('/wallet/update_balance', 'Admin\WalletController@updateBalance');
        Route::get('/wallet/transfer_poundage', 'Admin\WalletController@transferPoundage');
        Route::get('/wallet/collect', 'Admin\WalletController@collect');
        Route::get('/level_index', function () {
            return view('admin.level.index');
        });
        Route::get('/level_add', 'Admin\LevelController@add');
        Route::post('/level_add', 'Admin\LevelController@postAdd');
        Route::get('/level_list', 'Admin\LevelController@lists');
        Route::post('/level_del', 'Admin\LevelController@del');
        Route::get('/level_algebra_index', function () {
            return view('admin.level.algebra_index');
        });
        Route::get('/level_algebra_add', 'Admin\LevelController@algebraAdd');
        Route::post('/level_algebra_add', 'Admin\LevelController@algebraPostAdd');
        Route::get('/level_algebra_list', 'Admin\LevelController@algebraLists');
        Route::post('/level_algebra_del', 'Admin\LevelController@algebraDel');

        Route::get('/level_order_index', function () {
            return view('admin.level.orders');
        });

        Route::get('/level_order_list', 'Admin\LevelController@levelOrderList');
        Route::get('/insurance_rules_index', function () {
            return view('admin.insurancerule.index');
        });
        Route::get('/insurance_rules_add', 'Admin\InsuranceRuleController@add');
        Route::post('/insurance_rules_add', 'Admin\InsuranceRuleController@postAdd');
        Route::get('/insurance_rules_list', 'Admin\InsuranceRuleController@lists');
        Route::post('/insurance_rules_del', 'Admin\InsuranceRuleController@del');
        Route::get('/claim_index', 'Admin\ClaimController@index');
        Route::get('/claim_list', 'Admin\ClaimController@lists');
        Route::post('/claim_affirm', 'Admin\ClaimController@affirm');
        Route::post('/claim_reject', 'Admin\ClaimController@reject');
        Route::group(['prefix' => 'insurance'], function () {
            Route::get('index', 'Admin\InsuranceController@index');
            Route::get('lists', 'Admin\InsuranceController@lists');
            Route::get('add', 'Admin\InsuranceController@add');
            Route::post('add', 'Admin\InsuranceController@postAdd');
            Route::post('del', 'Admin\InsuranceController@del');
            Route::post('change_auto_claim', 'Admin\InsuranceController@changeAutoClaim');
            Route::post('change_status', 'Admin\InsuranceController@changeStatus');
            Route::post('change_t_add_1', 'Admin\InsuranceController@changeTAdd1');
            Route::get('order_index', 'Admin\InsuranceController@orderIndex');
            Route::get('order_lists', 'Admin\InsuranceController@orderLists');
        });
    });
});

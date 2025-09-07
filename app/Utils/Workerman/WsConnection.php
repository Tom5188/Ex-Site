<?php

namespace App\Utils\Workerman;

use App\Jobs\UpdateCurrencyPrice;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Lib\Timer;
use Illuminate\Support\Facades\DB;
use App\{Currency,CurrencyMatch,CurrencyQuotation,UsersWallet, MarketHour, Service\RedisService, UserChat, AccountLog, BindBox,BindBoxOrder,BindBoxQuotationLog,BindBoxSuccessOrder,BindBoxCollect,BindBoxMarginLog,BindBoxRaityHouse};
use App\Jobs\{CoinTradeHandel, EsearchMarket, LeverUpdate, LeverPushPrice, SendMarket, WriteMarket, HandleMicroTrade};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

class WsConnection
{
    protected $server_address = 'ws://api.huobi.pro:443/ws';
    // protected $server_address = 'ws://api.huobi.br.com:443/'; //ws国内开发调试
    protected $server_ping_freq = 5; //服务器ping检测周期,单位秒
    protected $server_time_out = 2; //服务器响应超时
    protected $send_freq = 5; //写入和发送数据的周期，单位秒
    protected $micro_trade_freq = 1; //期权处理时间周期

    protected $worker_id;

    protected $events = [
        'onConnect',
        'onClose',
        'onMessage',
        'onError',
        'onBufferFull',
        'onBufferDrain',
    ];

    protected static $marketKlineData = [];
    protected static $marketDepthData = [];
    protected static $matchTradeData = []; //撮合交易全站交易

    protected $connection;

    protected $timer;

    protected $pingTimer;

    protected $sendKlineTimer;
    
    protected $bidding_nft;

    protected $sendDepthTimer;

    protected $depthTimer;

    protected $handleTimer;

    protected $microTradeHandleTimer;

    protected $sendMatchTradeTimer;
    
    protected $tzm = 0;
    
    protected $tzmflag = 0;

    protected $subscribed = [];
    
    protected $bids = [];
    
    protected $asks = [];

    protected $topicTemplate = [
        'sub' => [
            'market_kline' => 'market.$symbol.kline.$period',
            'market_detail' => 'market.$symbol.detail',
            'market_depth' => 'market.$symbol.depth.$type',
            'market_trade' => 'market.$symbol.trade.detail', //成交的交易
        ],
    ];

    public function __construct($worker_id)
    {
        $this->worker_id = $worker_id;
        AsyncTcpConnection::$defaultMaxPackageSize = 1048576000;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 绑定所有事件到连接
     *
     * @return void
     */
    protected function bindEvent()
    {
        foreach ($this->events as $key => $event) {
            if (method_exists($this, $event)) {
                $this->connection && $this->connection->$event = [$this, $event];
                //echo '绑定' . $event . '事件成功' . PHP_EOL;
            }
        }
    }

    /**
     * 解除连接所有绑定事件
     *
     * @return void
     */
    protected function unBindEvent()
    {
        foreach ($this->events as $key => $event) {
            if (method_exists($this, $event)) {
                $this->connection && $this->connection->$event = null;
                //echo '解绑' . $event . '事件成功' . PHP_EOL;
            }
        }
    }

    public function getSubscribed($topic = null)
    {
        if (is_null($topic)) {
            return $this->subscribed;
        }
        return $this->subscribed[$topic] ?? null;
    }

    protected function setSubscribed($topic, $value)
    {
        $this->subscribed[$topic] = $value;
    }

    protected function delSubscribed($topic)
    {
        unset($this->subscribed[$topic]);
    }

    public function connect()
    {
        $this->connection = new AsyncTcpConnection($this->server_address);
        $this->bindEvent();
        $this->connection->transport = 'ssl';
        $this->connection->connect();
    }

    public function onConnect($con)
    {
        //连接成功后定期发送ping数据包检测服务器是否在线
        $this->timer = Timer::add($this->server_ping_freq, [$this, 'ping'], [$this->connection], true);
        if ($this->worker_id < 8) {
            $this->sendKlineTimer = Timer::add($this->send_freq, [$this, 'writeMarketKline'], [], true);
        } else {
            $this->depthTimer = Timer::add($this->send_freq, [$this, 'sendDepthData'], [], true);
            $this->sendMatchTradeTimer = Timer::add($this->send_freq, [$this, 'sendMatchTradeData'], [], true);
        }

        if ($this->worker_id == 5) {
            $this->handleTimer = Timer::add($this->send_freq, [self::class, 'sendLeverHandle'], [], true);
        }

        if ($this->worker_id == 0) {
            // echo  '处理期权订单' . PHP_EOL;
            $this->bidding_nft = Timer::add(10, [$this, 'AuctionSettlementNFT'], [], true);//nft拍卖 结算 保证金退回
            $this->microTradeHandleTimer = Timer::add($this->micro_trade_freq, [self::class, 'handleMicroTrade'], [], true);
        }
        //添加订阅事件代码
        $this->subscribe($con);
    }

    public function onClose($con)
    {
        echo $this->server_address . '连接关闭' . PHP_EOL;
        $path = base_path() . '/storage/logs/wss/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $this->server_address . '连接关闭' . PHP_EOL, 3, $path . $filename);
        //解除事件
        $this->timer && Timer::del($this->timer);
        $this->sendKlineTimer && Timer::del($this->sendKlineTimer);
        $this->pingTimer && Timer::del($this->pingTimer);
        $this->depthTimer && Timer::del($this->depthTimer);
        $this->handleTimer && Timer::del($this->handleTimer);
        $this->bidding_nft && Timer::del($this->bidding_nft);
        $this->microTradeHandleTimer && Timer::del($this->microTradeHandleTimer);
        $this->unBindEvent();
        unset($this->connection);
        $this->connection = null;
        $this->subscribed = null; //清空订阅
        echo '尝试重新连接' . PHP_EOL;
        $this->connect();
    }

    public function close($msg)
    {
        $path = base_path() . '/storage/logs/wss/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $msg, 3, $path . $filename);
        $this->connection->destroy();
    }
    
    //nft拍卖 结算 保证金退回
    public function AuctionSettlementNFT(){
        $now = Carbon::now();
                // 拍卖已经结束 没有人出价的拍卖品
                $bind_boxs = BindBox::where('end_time','<=',$now)->where('pay_type',2)->get();
                foreach ($bind_boxs as $bind_box){
                    $log = BindBoxQuotationLog::where('is_expired',0)->where('code',$bind_box->code)->first();
                    if(!$log){
                        $bind_box->status = 0;
                        $bind_box->resell_nft_status = 0;
                        $bind_box->save();
                    }
                }
                
            //拍卖结束 拍卖品有人出价
            $bind_box_quotation_logs = DB::table('bind_box_quotation_log')             
                ->leftjoin('bind_box_margin_log', 'bind_box_margin_log.id', '=', 'bind_box_quotation_log.margin_log_id')
                ->leftjoin('bind_box', 'bind_box.code', '=', 'bind_box_quotation_log.code')
                ->where('bind_box.end_time','<=',$now)
                ->where('bind_box.pay_type',2)
                ->where('bind_box.status',1)
                ->where('bind_box_quotation_log.is_expired',0)
                ->select('bind_box_quotation_log.*')->distinct()->get();
                
                foreach ($bind_box_quotation_logs as $bind_box_quotation_log){
                        $quotation_log_id = $bind_box_quotation_log->id;
                        $status = $bind_box_quotation_log->status; //成交状态
                        if($status == 1){ //拍到了
                            echo '生成待支付订单_'.PHP_EOL;
                            $order = new BindBoxSuccessOrder();
                            $order->code = $bind_box_quotation_log->code;
                            $order->quotrtion_log_id = $bind_box_quotation_log->id;
                            $order->user_id = $bind_box_quotation_log->buyer_id;
                            $order->currency_id = $bind_box_quotation_log->currency_id;
                            $order->is_read = 0;
                            $order->is_pay = 0;
                            $order->overtime = time() + 86400;//过期时间24小时
                            $order->created = time();
                            $order->save();
                            
                            Db::table('bind_box')->where('code',$bind_box_success_order->code)->update(['lock_order'=>1,'status'=>0]); //锁定未支付状态
                            Db::table('bind_box_quotation_log')->where('id',$quotation_log_id)->update(['is_expired'=>1]);
                        }else{//未拍到
                            $margin_log = BindBoxMarginLog::where('id',$bind_box_quotation_log->margin_log_id)->where('is_expired',0)->where('status',1)->first();//保证金交了未退
                            if($margin_log){//退保证金
                                $_currency = $margin_log->currency_id;
                                $number = $margin_log->number;
                                $users_wallet = UsersWallet::where('currency', $_currency)->where('user_id', $margin_log->user_id)->first();
                                change_wallet_balance($users_wallet, 2, $number, AccountLog::USER_RETURN_MARGIN_NFT, '退还竞拍保证金');
                                
                                $margin_log->is_expired = 1;
                                $margin_log->status = 0;
                                $margin_log->save();
                                
                                Db::table('bind_box_quotation_log')->where('id',$quotation_log_id)->update(['is_expired'=>1]);
                            }else{ //未拍到 但是推过保证金了
                                Db::table('bind_box_quotation_log')->where('id',$quotation_log_id)->update(['is_expired'=>1]);
                            }
                            
                            
                        }
                        
                }
                
        
        //超时处理
        $bind_box_success_orders = BindBoxSuccessOrder::where('overtime','<',time())->where('overtime','>',0)->where('is_expired',0)->where('is_pay',0)->get();
        foreach ($bind_box_success_orders as $bind_box_success_order){
            Db::table('bind_box')->where('code',$bind_box_success_order->code)->update(['status'=>0,'resell_nft_status'=>0,'lock_order'=>0]); //状态失效
            $BindBoxQuotationLog = BindBoxQuotationLog::where('id',$bind_box_success_order->quotrtion_log_id)->first();
            Db::table('bind_box_margin_log')->where('id',$BindBoxQuotationLog->margin_log_id)->update(['is_expired'=>1]);
            $bind_box_success_order->is_expired = 1; //订单已失效
            $bind_box_success_order->overtime = 0; 
            $bind_box_success_order->save();
        }
    }
    
    protected function makeSubscribeTopic($topic_template, $param)
    {
        $need_param = [];
        $match_count = preg_match_all('/\$([a-zA-Z_]\w*)/', $topic_template, $need_param);
        if ($match_count > 0 && count(reset($need_param)) > count($param)) {
            throw new \Exception('所需参数不匹配');
        }
        $diff = array_diff(next($need_param), array_keys($param));
        if (count($diff) > 0) {
            throw new \Exception('topic:' . $topic_template . '缺少参数：' . implode(',', $diff));
        }
        return preg_replace_callback('/\$([a-zA-Z_]\w*)/', function ($matches) use ($param) {
            extract($param);
            $value = $matches[1];
            return $$value ?? '';
        }, $topic_template);
    }

    public function onBufferFull()
    {
        echo 'buffer is full' . PHP_EOL;
    }

    protected function subscribe($con)
    {
        $periods = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week']; //['1day', '1min'];
        if ($this->worker_id < 8) {
            $value = $periods[$this->worker_id];
            echo '进程' . $this->worker_id . '开始订阅' . $value . '数据' . PHP_EOL;
            $this->subscribeKline($con, $value); //订阅k线行情
        } else {
            if ($this->worker_id == 8) {
                $this->subscribeMarketDepth($con); //订阅盘口数据
                $this->subscribeMarketTrade($con); //订阅全站交易数据
            }
        }
    }

    //订阅回调
    protected function onSubscribe($data)
    {
        if ($data->status == 'ok') {
            echo $data->subbed . '订阅成功' . PHP_EOL;
        } else {
            echo '订阅失败:' . $data->{'err-msg'} . PHP_EOL;
        }
    }

    /**
     * 订阅全站交易(已完成)
     * @param \Workerman\Connection\ConnectionInterface $con
     * @param \App\Models\CurrencyMatch $currency_match
     * @return void
     */
    public function subscribeMarketTrade($con)
    {
        $currency_list = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_list as $key => $currency_match) {
            $param = [
                'symbol' => $currency_match->match_name,
            ];
            $topic = $this->makeSubscribeTopic($this->topicTemplate['sub']['market_trade'], $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
                'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
            ]);
            $subscribed_data = $this->getSubscribed($topic);
            $match_data = $subscribed_data['match'] ?? [];
            $match_data[] = $currency_match;
            $this->setSubscribed($topic, [
                'callback' => 'onMatchTrade',
                'match' => $match_data,
            ]);
            // 未订阅过的才能订阅
            if (is_null($subscribed_data)) {
                $con->send($sub_data);
            }
        }
    }

    //订阅K线行情
    protected function subscribeKline($con, $period)
    {
        $currency_match = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_match as $key => $value) {
            $param = [
                'symbol' => $value->match_name,
                'period' => $period,
            ];
            $topic = $this->makeSubscribeTopic($this->topicTemplate['sub']['market_kline'], $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
                'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
            ]);
            //未订阅过的才能订阅
            if (is_null($this->getSubscribed($topic))) {
                $this->setSubscribed($topic, [
                    'callback' => 'onMarketKline',
                    'match' => $value
                ]);
                $con->send($sub_data);
            }
        }
    }

    protected function onMarketKline($con, $data, $match)
    {
        $topic = $data->ch;
        $msg = date('Y-m-d H:i:s') . ' 进程' . $this->worker_id . '接收' . $topic . '行情' . PHP_EOL;
        list($name, $symbol, $detail_name, $period) = explode('.', $topic);
        $subscribed_data = $this->getSubscribed($topic);
        $currency_match = $subscribed_data['match'];
        $tick = $data->tick;
        
        $open = $tick->open;
        $close = $tick->close;
        $high = $tick->high;
        $low = $tick->low;
        $redis = RedisService::getInstance(4);
        // 你可以使用 hash 类型存储结构化行情数据
        $redis->set("kline:{$currency_match->currency_name}", json_encode([
            'open'  => $open,
            'close' => $close,
            'high'  => $high,
            'low'   => $low,
        ]));
        
        $currency = Currency::where('name', $currency_match->currency_name)
            ->where('floating', '<>', 0)
            ->first();
        if ($currency) {
            $currentIndex = (time() - $currency->startTime);
            if ($currentIndex > $currency->duration) $currentIndex = $currency->duration;
            $step = $currency->floating / $currency->duration;
            
            $tickPrice = $step * $currentIndex;
            $price = $close + $tickPrice;
    
            // $tick->open = round($price, 4);
            $tick->close = round($price, 4); // 模拟小波动
            $tick->high = round(max($tick->open, $tick->close), 4);
            $tick->low = round(min($tick->open, $tick->close), 4);
        }
        if ($currency_match->market_from == 2) {
            $market_data = [
                'id' => $tick->id,
                'period' => $period,
                'base-currency' => $currency_match->currency_name,
                'quote-currency' => $currency_match->legal_name,
                'open' => sctonum($tick->open),
                'close' => sctonum($tick->close),
                'high' => sctonum($tick->high),
                'low' => sctonum($tick->low),
                'vol' => sctonum($tick->vol),
                'amount' => sctonum($tick->amount),
                'original_open' => sctonum($open),
                'original_close' => sctonum($close),
                'original_high' => sctonum($high),
                'original_low' => sctonum($low),
            ];

            $kline_data = [
                'type' => 'kline',
                'period' => $period,
                'match_id' => $currency_match->id,
                'currency_id' => $currency_match->currency_id,
                'currency_name' => $currency_match->currency_name,
                'legal_id' => $currency_match->legal_id,
                'legal_name' => $currency_match->legal_name,
                'open' => sctonum($tick->open),
                'close' => sctonum($tick->close),
                'high' => sctonum($tick->high),
                'low' => sctonum($tick->low),
                'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                'volume' => sctonum($tick->amount),
                'time' => $tick->id * 1000,
                'original_open' => sctonum($open),
                'original_close' => sctonum($close),
                'original_high' => sctonum($high),
                'original_low' => sctonum($low),
            ];
            //EsearchMarket::dispatch($market_data)->onQueue('esearch:market:' . $period);
            $key = $currency_match->currency_name . '.' . $currency_match->legal_name;
            self::$marketKlineData[$period][$key] = [
                'market_data' => $market_data,
                'kline_data' => $kline_data,
            ];
            if ($period == '1day') {
                //推送币种的日行情(带涨副)
                $change = $this->calcIncreasePair($kline_data);
                bc_comp($change, 0) > 0 && $change = '+' . $change;
                //追加涨副等信息
                $daymarket_data = [
                    'type' => 'daymarket',
                    'change' => round($change, 2),
                    'now_price' => $market_data['close'],
                    'api_form' => 'huobi_websocket',
                ];
                $kline_data = array_merge($kline_data, $daymarket_data);
                self::$marketKlineData[$period][$key]['kline_data'] = $kline_data;
                //SendMarket::dispatch($kline_data)->onQueue('kline.1day');
                /*
                //存入数据库
                CurrencyQuotation::getInstance($currency_match->legal_id, $currency_match->currency_id)
                    ->updateData([
                        'change' => $change,
                        'now_price' => $tick->close,
                        'volume' => $tick->amount,
                    ]);
                */
            }
        }
    }

    //订阅盘口数据
    protected function subscribeMarketDepth($con)
    {
        $currency_match = CurrencyMatch::getHuobiMatchs();
        foreach ($currency_match as $key => $value) {
            $param = [
                'symbol' => $value->match_name,
                'type' => 'step0',
            ];
            $topic = $this->makeSubscribeTopic($this->topicTemplate['sub']['market_depth'], $param);
            $sub_data = json_encode([
                'sub' => $topic,
                'id' => $topic,
                'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
            ]);
            //未订阅过的才能订阅
            if (is_null($this->getSubscribed($topic))) {
                $this->setSubscribed($topic, [
                    'callback' => 'onMarketDepth',
                    'match' => $value
                ]);
                $con->send($sub_data);
            }
        }
    }

    private function getMillisecond()
    {
        list($pSppfwv, $hmvXQMv) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($pSppfwv) + floatval($hmvXQMv)) * (0 - 1352 + 42 * 56));
    }

    public static function getSelfMatch()
    {
        $redis = RedisService::getInstance(4);
        $result = $redis->get('self_match');
        if (!$result) {
            $match_ids = CurrencyMatch::where('market_from', 0)->where('fluctuate_min', '>', 0)->where('fluctuate_max', '>', 0)->pluck('id')->toArray();
            $redis->set('self_match', implode(',', $match_ids));
            $redis->expire('self_match', 10);
            return $match_ids;
        } else {
            return explode(',', $result);
        }
    }

    /**
     * 撮合交易全站交易数据回调
     * @param \Workerman\Connection\ConnectionInterface $con
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Collection $currency_matches
     * @return void
     */
    protected function onMatchTrade($con, $data, $currency_matches)
    {
        $topic = $data->ch;
        $data = $data->tick->data;
        foreach ($currency_matches as $key => $currency_match) {
            $symbol_key = $currency_match->currency_name . '.' . $currency_match->legal_name;
            if ($symbol_key == 'BTC.USDT') {
                $self_match_ids = self::getSelfMatch();
                foreach ($self_match_ids as $id) {
                    $currencyMatch = CurrencyMatch::query()->find($id);
                    if (!$currencyMatch) {
                        continue 1;
                    }
                    $now_price = CurrencyQuotation::where('match_id', $id)->value('now_price');
                    $rate_arr = [-0.004, -0.003, -0.002, -0.001, 0.001, 0.002, 0.003, 0.004];
                    $price = bc_add($now_price, $rate_arr[array_rand($rate_arr)]);
                    $self_data = [];
                    $self_data[] = [
                        'id' => time() . rand(1, 100000),
                        'amount' => rand(100, 39 * 56 - 184) / (16 * 56 + 104),
                        'direction' => $data[0]->direction,
                        'price' => $price,
                        'tradeId' => time() . rand(1, 3333),
                        'ts' => self::getMillisecond()
                    ];
                    $self_trade_data = [
                        'type' => 'match_trade',
                        'symbol' => $currencyMatch->currency_name . '/' . $currencyMatch->legal_name,
                        'base-currency' => $currencyMatch->currency_name,
                        'quote-currency' => $currencyMatch->legal_name,
                        'currency_id' => $currencyMatch->currency_id,
                        'currency_name' => $currencyMatch->currency_name,
                        'legal_id' => $currencyMatch->legal_id,
                        'legal_name' => $currencyMatch->legal_name,
                        'data' => $self_data
                    ];
                    self::$matchTradeData[$currencyMatch->currency_name . '.' . $currencyMatch->legal_name] = $self_trade_data;
                }
            }
            if ($currency_match->market_from == 2) {
                $trade_data = [
                    'type' => 'match_trade',
                    'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                    'base-currency' => $currency_match->currency_name,
                    'quote-currency' => $currency_match->legal_name,
                    'currency_id' => $currency_match->currency_id,
                    'currency_name' => $currency_match->currency_name,
                    'legal_id' => $currency_match->legal_id,
                    'legal_name' => $currency_match->legal_name,
                    'data' => $data,
                ];
                self::$matchTradeData[$symbol_key] = $trade_data;
            }
        }
    }

    // protected function onMarketDepth($con, $data, $currency_matches)
    // {
    //     try {
    //         $topic = $data->ch;
    //         $limit = 10;
    //         $tick = $data->tick;
    //         $bids = array_slice($tick->bids, 0, $limit);
    //         $asks = array_slice($tick->asks, 0, $limit);
    //         krsort($asks);
    //         $asks = array_values($asks);
    //         // 将模拟克隆的交易对也添加上数据
    //         foreach ($currency_matches as $key => $currency_match) {
    //             $depth_data = [
    //                 'type' => 'market_depth',
    //                 'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
    //                 'base-currency' => $currency_match->currency_name,
    //                 'quote-currency' => $currency_match->legal_name,
    //                 'currency_id' => $currency_match->currency_id,
    //                 'currency_name' => $currency_match->currency_name,
    //                 'legal_id' => $currency_match->legal_id,
    //                 'legal_name' => $currency_match->legal_name,
    //                 'bids' => $bids, //买入盘口
    //                 'asks' => $asks, //卖出盘口
    //             ];
    //             $symbol_key = $currency_match->currency_name . '.' . $currency_match->legal_name;
    //             self::$marketDepthData[$symbol_key] = $depth_data;
    //         }
    //     } catch (\Throwable $th) {

    //     }
    // }

    //盘口数据回调
    protected function onMarketDepth($con, $data, $match)
    {
        $topic = $data->ch;
        $subscribed_data = $this->getSubscribed($topic);
        $currency_match = $subscribed_data['match'];
        krsort($data->tick->asks);
        $data->tick->asks = array_values($data->tick->asks);

        if ($currency_match->market_from == 2) {
            $depth_data = [
                'type' => 'market_depth',
                'symbol' => $currency_match->currency_name . '/' . $currency_match->legal_name,
                'base-currency' => $currency_match->currency_name,
                'quote-currency' => $currency_match->legal_name,
                'currency_id' => $currency_match->currency_id,
                'currency_name' => $currency_match->currency_name,
                'legal_id' => $currency_match->legal_id,
                'legal_name' => $currency_match->legal_name,
                'bids' => array_slice($data->tick->bids, 0, 10), //买入盘口
                'asks' => array_slice($data->tick->asks, 0, 10), //卖出盘口
            ];
            $symbol_key = $currency_match->currency_name . '.' . $currency_match->legal_name;
            self::$marketDepthData[$symbol_key] = $depth_data;
        }
    }

    /**
     * 发送盘口数据
     *
     * @return void
     */
    public function sendDepthData()
    {
        $market_depth = self::$marketDepthData;
        foreach ($market_depth as $depth_data) {
            SendMarket::dispatch($depth_data)->onQueue('market.depth');
        }
    }

    //取消订阅
    protected function unsubscribe()
    {
    }

    protected function onUnsubscribe()
    {
    }

    public function onMessage($con, $data)
    {
        $data = gzdecode($data);
        $data = json_decode($data, false, 512, JSON_BIGINT_AS_STRING);

        if (isset($data->ping)) {
            $this->onPong($con, $data);
        } elseif (isset($data->pong)) {
            $this->onPing($con, $data);
        } elseif (isset($data->id) && $this->getSubscribed($data->id) != null) {
            $this->onSubscribe($data);
        } elseif (isset($data->id)) {

        } else {
            $this->onData($con, $data);
        }
    }

    protected function onData($con, $data)
    {
        if (isset($data->ch)) {
            $subscribed = $this->getSubscribed($data->ch);
            if ($subscribed != null) {
                //调用回调处理
                $callback = $subscribed['callback'];
                $this->$callback($con, $data, $subscribed['match']);
            } else {
                //不在订阅中的数据
            }
        } else {
            echo '未知数据' . PHP_EOL;
            var_dump($data);
        }
    }

    /**
     * 发送全站交易数据
     *
     * @return void
     */
    public function sendMatchTradeData()
    {
        $market_trade = self::$matchTradeData;
        foreach ($market_trade as $trade_data) {
            SendMarket::dispatch($trade_data)->onQueue('send:match:trade');
        }
        self::$matchTradeData = []; //发送完清空,以避免重复发送相同的数据
    }


    public static function sendLeverHandle()
    {
//        echo date('Y-m-d H:i:s') . '定时器取价格' . PHP_EOL;
        $now = microtime(true);
//        $master_start = microtime(true);
//        echo str_repeat('=', 80) . PHP_EOL;
//        echo date('Y-m-d H:i:s') . '开始发送价格到杠杆交易系统' . PHP_EOL;
//        echo '{' . PHP_EOL;
        $market_kiline = self::$marketKlineData['1day'];
        foreach ($market_kiline as $key => $value) {
            $kline_data = $value['kline_data'];
            $start = microtime(true);
//            echo "\t" . date('Y-m-d H:i:s') . ' 发送' . $key . ',价格:' . $kline_data['close'] . PHP_EOL;
            $params = [
                'legal_id' => $kline_data['legal_id'],
                'legal_name' => $kline_data['legal_name'],
                'currency_id' => $kline_data['currency_id'],
                'currency_name' => $kline_data['currency_name'],
                'now_price' => $kline_data['close'],
                'now' => $now
            ];
            //价格大于0才进行任务推送
            if (bc_comp($kline_data['close'], 0) > 0) {
                LeverUpdate::dispatch($params)->onQueue('lever:update');
                CoinTradeHandel::dispatch($params)->onQueue('coin:trade');
//                LeverPushPrice::dispatch($params)->onQueue('lever:push:price');
            }
            $end = microtime(true);
//            echo "\t" . date('Y-m-d H:i:s') . $key . '处理完成,耗时' .($end - $start) . '秒' . PHP_EOL;
        }
//        $master_end = microtime(true);
//        echo '}' . PHP_EOL;
//        echo date('Y-m-d H:i:s') . '杠杆交易系统处理完成,耗时' . ($master_end - $master_start) . '秒' . PHP_EOL;
//        echo str_repeat('=', 80) . PHP_EOL;
    }

    public static function handleMicroTrade()
    {
        //self::$marketKlineData[$period][$key]['kline_data'] = $kline_data;
        $market_data = self::$marketKlineData;
        foreach ($market_data as $period => $data) {
            foreach ($data as $key => $symbol) {
                echo '期权时间:' . time() . ', Symbol:' . $key . '.' . $period . '数据' . PHP_EOL;
                if ($period == '1min') {
                    //处理期权
                    // $match_id=$symbol['kline_data']['match_id'];
                    // $c_m=CurrencyMatch::find($match_id);
                    // if($c_m->open_microtrade == 1){
                        HandleMicroTrade::dispatch($symbol['kline_data'])->onQueue('micro_trade:handle');
                    // }

                } else {
                    continue;
                }
            }
        }
    }

    public function writeMarketKline()
    {
        if ($this->worker_id < 8) {
            $market_data = self::$marketKlineData;
            foreach ($market_data as $period => $data) {
                foreach ($data as $key => $symbol) {
                    echo '处理' . $key . '.' . $period . '数据' . PHP_EOL;
                    $result = MarketHour::getEsearchMarketById(
                        $symbol['market_data']['base-currency'],
                        $symbol['market_data']['quote-currency'],
                        $period,
                        $symbol['market_data']['id']
                    );
                    if (isset($result['_source'])) {
                        $origin_data = $result['_source'];
                        bc_comp($symbol['kline_data']['high'], $origin_data['high']) < 0
                        && $symbol['kline_data']['high'] = $origin_data['high']; //新过来的价格如果不高于原最高价则不更新
                        bc_comp($symbol['kline_data']['low'], $origin_data['low']) > 0
                        && $symbol['kline_data']['low'] = $origin_data['low']; //新过来的价格如果不低于原最低价则不更新
                    }
                    
                    $currency = Currency::where('id', $symbol['kline_data']['currency_id'])
                        ->where('floating', '<>', 0)
                        ->first();
                    if ($currency) {
                        $currentIndex = (time() - $currency->startTime);
                        if ($currentIndex > $currency->duration) $currentIndex = $currency->duration;
                        $step = $currency->floating / $currency->duration;
                        
                        $tickPrice = $step * $currentIndex + mt_rand(-50, 50) / 1000;
                        $price = $symbol['kline_data']['original_close'] + $tickPrice;
                        
                        
                        $close = round($price, 4); // 模拟小波动
                        $high = round(max($symbol['kline_data']['original_open'], $close), 4);
                        $low = round(min($symbol['kline_data']['original_open'], $close), 4);
                        $symbol['kline_data']['close'] = $close;
                        $symbol['kline_data']['high'] = $high;
                        $symbol['kline_data']['low'] = $low;
                        $symbol['market_data']['close'] = $close;
                        $symbol['market_data']['high'] = $high;
                        $symbol['market_data']['low'] = $low;
                        $market_data[$period][$key] = $symbol;
                        echo '--｜推送' . $key . '.' . $period . '数据 '. $close . PHP_EOL;
                    } else {
                        $high = round(max($symbol['kline_data']['original_open'], $symbol['kline_data']['original_close']), 4);
                        $low = round(min($symbol['kline_data']['original_open'], $symbol['kline_data']['original_close']), 4);
                        $symbol['kline_data']['close'] = $symbol['kline_data']['original_close'];
                        $symbol['kline_data']['high'] = $high;
                        $symbol['kline_data']['low'] = $low;
                        $symbol['market_data']['close'] = $symbol['kline_data']['original_close'];
                        $symbol['market_data']['high'] = $high;
                        $symbol['market_data']['low'] = $low;
                        $market_data[$period][$key] = $symbol;
                        echo '--｜推送' . $key . '.' . $period . '数据 '. $symbol['kline_data']['original_close'] . PHP_EOL;
                    }
                    
                    SendMarket::dispatch($symbol['kline_data'])->onQueue('kline.all');
                    EsearchMarket::dispatch($symbol['market_data'])->onQueue('esearch:market');//统一用一个队列
                    if ($period == '1min') {
                        // var_dump($symbol['kline_data']);
                        //推送一分钟行情
                        //SendMarket::dispatch($symbol['kline_data'])->onQueue('kline.1min');
                        //更新币种价格
                        UpdateCurrencyPrice::dispatch($symbol['kline_data'])->onQueue('update_currency_price');
                    } else if ($period == '1day') {
                        //推送一天行情
                        $day_kline = $symbol['kline_data'];
                        $day_kline['type'] = 'kline';
                        //SendMarket::dispatch($day_kline)->onQueue('kline.all');
                        //SendMarket::dispatch($symbol['kline_data'])->onQueue('kline.1day');
                        //存入数据库
                        CurrencyQuotation::getInstance($symbol['kline_data']['legal_id'], $symbol['kline_data']['currency_id'])
                            ->updateData([
                                'change' => $symbol['kline_data']['change'],
                                'now_price' => $symbol['kline_data']['close'],
                                'volume' => $symbol['kline_data']['volume'],
                            ]);
                    } else {
                        continue;
                    }
                }
            }
        }
    }

    protected function calcIncreasePair($kline_data)
    {
        $open = $kline_data['open'];
        $close = $kline_data['close'];;
        $change_value = bc_sub($close, $open);
        $change = bc_mul(bc_div($change_value, $open), 100, 2);
        return $change;
    }

    //心跳响应
    protected function onPong($con, $data)
    {
        //echo '收到心跳包,PING:' . $data->ping . PHP_EOL;
        $send_data = [
            'pong' => $data->ping,
        ];
        $send_data = json_encode($send_data);
        $con->send($send_data);
        //echo '已进行心跳响应' . PHP_EOL;
    }

    public function ping($con)
    {
        $ping = time();
        //echo '进程' . $this->worker_id . '发送ping服务器数据包,ping值:' . $ping . PHP_EOL;
        $send_data = json_encode([
            'ping' => $ping,
        ]);
        $con->send($send_data);
        // $this->pingTimer = Timer::add($this->server_time_out, function () use ($con) {
        //     $msg = '进程' . $this->worker_id . '服务器响应超时,连接关闭' . PHP_EOL;
        //     echo $msg;
        //     $this->close($msg);
        // }, [], false);
    }

    protected function onPing($con, $data)
    {
        $this->pingTimer && Timer::del($this->pingTimer);
        $this->pingTimer = null;
        //echo '进程' . $this->worker_id . '服务器正常响应中,pong:' . $data->pong. PHP_EOL;
    }
}

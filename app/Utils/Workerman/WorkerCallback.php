<?php
namespace App\Utils\Workerman;

use Workerman\Lib\Timer;
use App\MarketHour;
class WorkerCallback
{
    protected $events = ["onWorkerStart", "onConnect", "onMessage", "onClose", "onError", "onBufferFull", "onBufferDrain", "onWorkerStop", "onWorkerReload"];
    protected $interval = 1;
    protected $wsConnection;
    protected $worker;
    public function __construct()
    {
        $this->registerEvent();
    }
    public function registerEvent()
    {
        foreach ($this->events as $yXKEuKJ => $CpWSnxQ) {
            method_exists($this, $CpWSnxQ) && ($this->{$CpWSnxQ} = [$this, $CpWSnxQ]);
        }
    }
    public function onWorkerStart($worker)
    {


        $this->worker = $worker;
        echo '进程' . $worker->id . '启动' . PHP_EOL;
        if ($worker->id < 8) {
            $KvYhtUJ = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week'];
            $WEDDKbJ = $KvYhtUJ[$worker->id];
            $worker->name = 'huobi.ws:' . 'market.kline.' . $WEDDKbJ;
        } else {
            $worker->name = 'huobi.ws:' . 'market.depth.step0';
        }
        $UiFYzeQ = new WsConnection($worker->id);
        $this->wsConnection = $UiFYzeQ;
        $UiFYzeQ->connect();
    }
    public function onWorkerReload($worker)
    {
    }
    public function onConnect($connection)
    {
    }
    public function onClose($connection)
    {
    }
    public function onError($connection, $code, $msg)
    {
    }
    public function onMessage($connection, $data)
    {

    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;
class WebSocketClient extends Command
{
    protected $signature = "websocket:client {worker_command} {--mode=}";
    protected $description = "websocket client";
    protected $worker;
    protected $events = ["onWorkerStart", "onConnect", "onMessage", "onClose", "onError", "onBufferFull", "onBufferDrain", "onWorkerStop", "onWorkerReload"];
    protected $callback_class;
    public function __construct()
    {
        parent::__construct();
        $class_name = config('websocket.client.callback_class');
        $process_num = config('websocket.client.process_num');
        $this->callback_class = new $class_name();
        $this->worker = new Worker();
        $this->worker->count = $process_num;
        $this->worker->name = 'Huobi Websocket';
    }
    public function handle()
    {
        $this->initWorker();
        $this->bindEvent();
        $this->worker->runAll();
    }
    protected function initWorker()
    {
        global $argv;
        $command = $this->argument('worker_command');
        $argv[1] = $command;
        $mode = $this->option('mode');
        if ((bool) isset($mode)) {
            $argv[2] = '-' . $mode;
        }
    }
    protected function bindEvent()
    {
        foreach ($this->events as $key => $event) {
            if ((bool) method_exists($this->callback_class, $event)) {
                $this->worker->{$event} = [$this->callback_class, $event];
            }
        }
    }
}
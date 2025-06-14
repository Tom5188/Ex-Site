<?php

namespace App\Jobs;

use App\LeverTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * 处理跟随订单
 */
class FollowOrderHandel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //被跟随的订单id
    protected $to_lever_transaction_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to_lever_transaction_id)
    {
        $this->to_lever_transaction_id = $to_lever_transaction_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $toLeverTransaction = LeverTransaction::query()->find($this->to_lever_transaction_id);
            if ($toLeverTransaction->status != LeverTransaction::CLOSED) {
                throw new \Exception('被跟随的订单状态错误');
            }
            LeverTransaction::query()
                ->where('status', LeverTransaction::TRANSACTION)
                ->where('follow_user_id', $toLeverTransaction->user_id)
                ->where('follow_order_id', $toLeverTransaction->id)
                ->where('order_type', 2)
                ->chunkById(50, function ($items) {
                    $items->each(function ($item) {
                        try {
                            $return = LeverTransaction::leverClose($item, true);
                            if (!$return) {
                                throw new \Exception("平仓失败");
                            }
                        } catch (\Throwable $ex) {
                            //出现出错跳过，执行下一个
                            $msg = "跟随者的订单id：{$item->id}，被跟随者订单id：{$this->to_lever_transaction_id}，" . $ex->getMessage();
                            $this->writeLog($msg);
                        }
                    });
                });
        } catch (\Throwable $ex) {
            $msg = "被跟随者订单id：{$this->to_lever_transaction_id}，" . $ex->getMessage();
            $this->writeLog($msg);
        }
    }

    /**
     * 写日志
     * @param $msg
     */
    protected function writeLog($msg)
    {
        $path = base_path() . '/storage/logs/follow/';
        $filename = 'FollowOrderHandel-' . date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL, 3, $path . $filename);
    }
}

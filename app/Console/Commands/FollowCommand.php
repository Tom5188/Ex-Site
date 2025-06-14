<?php

namespace App\Console\Commands;

use App\AccountLog;
use App\CurrencyMatch;
use App\UsersWallet;
use Illuminate\Console\Command;
use App\Follow;
use App\Users;
use App\LeverTransaction;
use Illuminate\Support\Facades\DB;

class FollowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'follow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理跟单';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo '跟单任务开始运行' . PHP_EOL;
        Follow::query()
            ->where('status', 1)
            ->chunkById(50, function ($items) {
                $items->each(function ($follow) {
                    $this->handleFollow($follow);
                });
            });
        echo '跟单任务运行结束' . PHP_EOL;
    }

    protected function handleFollow($follow)
    {
        //查看跟随的用户是否为交易员
        $is_trader = Users::where('id', $follow->follow_user_id)->value('is_trader');
        if ($is_trader != 1) {
            $this->cancelFollow($follow->id);
            return false;
        }

        $transaction = LeverTransaction::query()
            ->where([
                'user_id' => $follow->follow_user_id,
                'status' => 1
            ])
            ->where('create_time', '>=', strtotime($follow->created_at))
            ->get();
        foreach ($transaction as $val) {
            //查询是否已存在该订单的跟单
            $exists_follow_order = LeverTransaction::query()
                ->where('user_id', $follow->user_id)
                ->where('follow_order_id', $val->id)
                ->where('follow_user_id', $follow->follow_user_id)
                ->exists();
            //不存在则添加
            if (!$exists_follow_order) {
                $this->generateFollowOrder($val, $follow);
            }
        }
    }

    /**
     * 生成跟随订单
     *
     * @param $order
     * @param $follow
     * @return false|void
     */
    protected function generateFollowOrder($order, $follow)
    {
        try {
            $order = $order->getOriginal();
            $currency_match = CurrencyMatch::where('legal_id', $order['legal'])
                ->where('currency_id', $order['currency'])
                ->first();
            if (!$currency_match) { //指定交易对不存在
                throw new \Exception("指定交易对不存在");
            }
            if ($currency_match->open_lever != 1) { //未开通本交易对的交易功能
                throw new \Exception("未开通本交易对的交易功能");
            }

            $follow_order = $order;
            $follow_order['order_type'] = 2;
            $follow_order['follow_user_id'] = $order['user_id'];
            $follow_order['follow_order_id'] = $order['id'];
            $follow_order['user_id'] = $follow->user_id;
            $follow_order['status'] = LeverTransaction::TRANSACTION;

            if ($follow->type == 1) {//跟随类型：1固定比例跟随 2固定手数跟随
                $number = bc_mul($order['number'], $follow->number, 2);

                $number = $number <= 1 ? 1 : $number;
            } else {
                $number = $follow->number;
            }
            //算法参考：App\Http\Controllers\Api\LeverController->submit()
            $all_money = bc_mul($order['price'], $number);
            $caution_money = bc_div($all_money, $order['multiple']);   //保证金

            //计算手续费
            $lever_trade_fee_rate = bc_div($currency_match->lever_trade_fee ?? 0, 100);
            $trade_fee = bc_mul($all_money, $lever_trade_fee_rate); //手续费

            $follow_order['share'] = $number;
            $follow_order['number'] = $number;
            $follow_order['origin_caution_money'] = $caution_money;
            $follow_order['caution_money'] = $caution_money;
            $follow_order['trade_fee'] = $trade_fee;
            //追加用户的代理商关系
            $user = Users::query()->where('id', $follow->user_id)->first(['agent_path']);
            $follow_order['agent_path'] = $user->agent_path;

            DB::beginTransaction();

            $legal = UsersWallet::where("user_id", $follow->user_id)
                ->where("currency", $order['legal'])
                ->lockForUpdate()
                ->first();
            if (!$legal) {
                throw new \Exception("该用户对应钱包未找到");
            }
            $user_lever = $legal->lever_balance;

            $shoud_deduct = bc_add($caution_money, $trade_fee); //保证金+手续费
            if (bc_comp($user_lever, $shoud_deduct) < 0) {
                throw new \Exception($currency_match->legal_name . '余额不足,不能小于:' . $shoud_deduct . '(手续费:' . $trade_fee . ')');
            }
            unset($follow_order['id']);

            $lever_transaction = LeverTransaction::query()->create($follow_order);

            //扣除保证金
            $result = change_wallet_balance(
                $legal,
                3,
                -$caution_money,
                AccountLog::LEVER_TRANSACTION_DEDUCT_CAUTION,
                '跟随购买 ' . $currency_match->symbol . ' 杠杆交易,价格' . $order['price'] . ',扣除保证金',
                false,
                0,
                0,
                serialize([
                    'trade_id' => $lever_transaction->id,
                    'all_money' => $all_money,
                    'multiple' => $order['multiple'],
                ])
            );
            if ($result !== true) {
                throw new \Exception('扣除保证金失败:' . $result);
            }
            //扣除手续费
            $result = change_wallet_balance(
                $legal,
                3,
                -$trade_fee,
                AccountLog::LEVER_TRANSACTION_TRADE_FEE,
                '跟随购买 ' . $currency_match->symbol . ' 杠杆交易,扣除手续费',
                false,
                0,
                0,
                serialize([
                    'trade_id' => $lever_transaction->id,
                    'all_money' => $all_money,
                    'lever_trade_fee_rate' => $lever_trade_fee_rate,
                ])
            );
            if ($result !== true) {
                throw new \Exception('扣除手续费失败:' . $result);
            }
            DB::commit();
            
            echo "跟随订单已生成，ID：{$lever_transaction->id}";
        } catch (\Throwable $ex) {
            DB::rollBack();
            echo $msg = "跟随者用户id：{$follow->user_id}，跟随订单id：{$order['id']}，" . $ex->getMessage();
            $this->writeLog($msg);
            return false;
        }
    }

    //取消跟随
    protected function cancelFollow($follow_id)
    {
        Follow::where('id', $follow_id)->update([
            'status' => 2
        ]);
    }

    /**
     * 写日志
     * @param $msg
     */
    protected function writeLog($msg)
    {
        $path = base_path() . '/storage/logs/follow/';
        $filename = 'FollowCommand-' . date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL, 3, $path . $filename);
    }
}

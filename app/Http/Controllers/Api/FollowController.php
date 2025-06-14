<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Users;
use App\LeverTransaction;
use App\Follow;
use Illuminate\Support\Carbon;

class FollowController extends Controller
{
    //跟单中-交易员列表
    public function index()
    {
        $user_id = Users::getUserId();
        $list = Users::query()
            ->selectRaw('count(t.id) as closed_count')
            ->selectRaw('IFNULL(sum(t.fact_profits),0) as total_profits')   //总盈亏
            ->selectRaw('users.id,users.nickname,users.head_portrait,users.virtual_follow_num')
            ->leftJoin('lever_transaction as t', function ($join) {
                $join->on('users.id', '=', 't.user_id')
                    ->where('t.status', LeverTransaction::CLOSED);
            })
            ->where('users.is_trader', 1)
            ->groupBy('users.id')
            ->orderBy('total_profits', 'desc')
            ->paginate();

        $list = $list->setCollection($list->getCollection()->map(function ($item) use ($user_id) {
            //查询已平仓且盈利的总数
            $flat_count = LeverTransaction::query()
                ->where([
                    'status' => LeverTransaction::CLOSED,
                    'fact_profits' => ['>', 0],
                    'user_id' => $item->id
                ])
                ->count();
            //总准确率
            $item->correct_rate = $item->closed_count ? round($flat_count / $item->closed_count * 100, 2) : 0.00;
            if (!empty($item->virtual_follow_num)) {
                //跟随人数，有虚拟数则使用虚拟数
                $item->follow_num = $item->virtual_follow_num;
            } else {
                $item->follow_num = Follow::query()->where(['follow_user_id' => $item->id, 'status' => 1])->count();
            }
            unset($item->virtual_follow_num);

            //查询当前交易员是否已跟随
            $item->is_followed = Follow::query()
                ->where(['user_id' => $user_id, 'follow_user_id' => $item->id, 'status' => 1])
                ->exists();

            return $item;
        }));

        return $this->success($list);
    }

    //跟随
    public function follow(Request $request)
    {
        try {
            $user_id = Users::getUserId();
            $trader_user_id = $request->trader_user_id;
            $type = $request->type; //跟随类型：1固定比例跟随 2固定手数跟随
            $number = $request->number; //跟随数量

            if (!$trader_user_id) {
                return $this->error('请选择交易员');
            }
            if (!in_array($type, [1, 2])) {
                return $this->error('跟随类型错误');
            }
            if ($type == 1 && $number > 5) {
                return $this->error('跟随倍数不能超过5倍');
            }
            if ($type == 2 && $number > 5) {
                return $this->error('跟随手数不能超过5手');
            }
            $user = Users::query()->find($user_id);
            if ($user->is_trader == 1) {
                return $this->error('禁止交易员跟随');
            }
            if (Users::where('id', $trader_user_id)->value('is_trader') != 1) {
                return $this->error('交易员身份错误');
            }

            //同时最多跟随两个
            if (Follow::where(['user_id' => $user_id, 'status' => 1])->count() >= 2) {
                return $this->error('最多跟随2名交易员');
            }

            Follow::updateOrCreate([
                'user_id' => $user_id,
                'follow_user_id' => $trader_user_id,
            ], [
                'number' => $number,
                'type' => $type,
                'status' => 1
            ]);

            return $this->success('操作成功');
        } catch (\Throwable $ex) {
            return $this->error('操作失败');
        }
    }

    //取消跟随
    public function cancel(Request $request)
    {
        try {
            $user_id = Users::getUserId();
            $follow = Follow::where(['user_id' => $user_id, 'follow_user_id' => $request->follow_user_id])->first();
            $follow->status = 2;
            $follow->save();

            return $this->success('操作成功');
        } catch (\Throwable $ex) {
            return $this->error('操作失败');
        }
    }

    //交易员详情
    public function traderDetail(Request $request)
    {
        $user_id = Users::getUserId();
        $trader_user_id = $request->trader_user_id;
        $user = Users::query()
            ->where([
                'id' => $trader_user_id,
                'is_trader' => 1
            ])->first(['id', 'is_trader', 'nickname', 'head_portrait', 'virtual_follow_num']);
        if (empty($user)) {
            return $this->error('交易员不存在');
        }
        $transaction = LeverTransaction::query()
            ->selectRaw('count(id) as closed_count')
            ->selectRaw('IFNULL(sum(fact_profits),0) as total_profits')   //总盈亏
            ->selectRaw('sum(fact_profits) / sum(origin_caution_money) as total_income_rate')   //总收益率
            ->selectRaw('count(
                            CASE
                            WHEN fact_profits > 0 THEN
                                fact_profits
                            END
                        ) AS profitCount')  //盈利订单
            ->selectRaw('count(
                            CASE
                            WHEN type = 2 THEN
                                type
                            END
                        ) AS fallCount')    //成功做空交易
            ->selectRaw('count(
                            CASE
                            WHEN type = 1 THEN
                                type
                            END
                        ) AS riseCount')    //成功做多交易
            ->where('status', LeverTransaction::CLOSED)
            ->where('user_id', $trader_user_id)
            ->first();
        $data['total_profits'] = $transaction['total_profits'];
        $data['profitCount'] = $transaction['profitCount'];
        $data['fallCount'] = $transaction['fallCount'];
        $data['riseCount'] = $transaction['riseCount'];
        $data['total_income_rate'] = bc_mul($transaction['total_income_rate'], 100, 2); //总收益率
        //准确率
        $data['correct_rate'] = bc_mul(bc_div($transaction['profitCount'], $transaction['closed_count']), 100, 2);
        if (!empty($user->virtual_follow_num)) {
            //跟随人数，有虚拟数则使用虚拟数
            $data['follow_num'] = $user->virtual_follow_num;
        } else {
            $data['follow_num'] = Follow::query()->where(['follow_user_id' => $user->id, 'status' => 1])->count();
        }
        $yesterday = Carbon::yesterday();
        $yesterday_data = LeverTransaction::query()
            ->selectRaw('sum(fact_profits) / sum(origin_caution_money) as profit')
            ->where('status', LeverTransaction::CLOSED)
            ->where('user_id', $trader_user_id)
            ->where('handle_time', '>=', $yesterday->startOfDay()->timestamp . '.' . $yesterday->startOfDay()->micro)
            ->where('handle_time', '<=', $yesterday->endOfDay()->timestamp . '.' . $yesterday->endOfDay()->micro)
            ->first();
        $data['yesterday_profits'] = bc_mul($yesterday_data->profit, 100, 2);   //昨日交易状况
        $data['position_count'] = LeverTransaction::query()
            ->where('status', LeverTransaction::TRANSACTION)
            ->where('user_id', $trader_user_id)
            ->count();  //当前持仓
        $data['total_count'] = LeverTransaction::query()
            ->where('user_id', $trader_user_id)
            ->count();  //总订单

        //查询当前交易员是否已跟随
        $is_followed = Follow::query()
            ->where(['user_id' => $user_id, 'follow_user_id' => $trader_user_id, 'status' => 1])
            ->exists();

        return $this->success([
            'trader_info' => $user,
            'data' => $data,
            'is_followed' => $is_followed
        ]);
    }
    
    //转自持
    public function selfHolding(Request $request)
    {
        try {
            $user_id = Users::getUserId();
            $transaction_id = $request->transaction_id;
            $transaction = LeverTransaction::query()->where([
                'id' => $transaction_id,
                'user_id' => $user_id,
                'status' => LeverTransaction::TRANSACTION,
                'order_type' => 2
            ])->first();
            if (empty($transaction)) {
                return $this->error('数据未找到');
            }
            $transaction->order_type = 1;
            $transaction->save();

            return $this->success('操作成功');
        } catch (\Throwable $ex) {
            return $this->error('操作失败');
        }
    }
    
    //历史交易
    public function historyTrade(Request $request)
    {
        $trader_user_id = $request->trader_user_id;
        $list = LeverTransaction::query()
            ->where([
                'user_id' => $trader_user_id,
                'status' => LeverTransaction::CLOSED,
                'order_type' => 1
            ])
            ->orderByDesc('id')
            ->paginate();

        return $this->success($list);
    }
}
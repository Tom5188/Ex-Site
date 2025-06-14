<?php

namespace App\Http\Controllers\Admin;

use App\Setting;
use Illuminate\Http\Request;

/**
 * 会员等级配置
 */
class UserGradeSettingController extends Controller
{
    public function index()
    {
        $data = Setting::query()->where('key', 'user_grade_setting')->first();
        $values = json_decode($data->value);

        $ordinary_setting = collect($values)->where('grade', 1)->first();
        $vip_setting = collect($values)->where('grade', 2)->first();
        $svip_setting = collect($values)->where('grade', 3)->first();
        $setting['ordinary'] = collect($ordinary_setting)->toArray();
        $setting['vip'] = collect($vip_setting)->toArray();
        $setting['svip'] = collect($svip_setting)->toArray();

        return view("admin.userGradeSetting.index", [
            'setting' => $setting
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = [
                [
                    'grade' => 1,
                    'ore_pool_income' => $request->ordinary_ore_pool_income,
                    'delivery_contract_30s_switch' => $request->ordinary_delivery_contract_30s_switch,
                    'transaction_service_change' => $request->ordinary_transaction_service_change,
                    'withdraw_coin_service_change' => $request->ordinary_withdraw_coin_service_change
                ],
                [
                    'grade' => 2,
                    'ore_pool_income' => $request->vip_ore_pool_income,
                    'delivery_contract_30s_switch' => $request->vip_delivery_contract_30s_switch,
                    'transaction_service_change' => $request->vip_transaction_service_change,
                    'withdraw_coin_service_change' => $request->vip_withdraw_coin_service_change
                ],
                [
                    'grade' => 3,
                    'ore_pool_income' => $request->svip_ore_pool_income,
                    'delivery_contract_30s_switch' => $request->svip_delivery_contract_30s_switch,
                    'transaction_service_change' => $request->svip_transaction_service_change,
                    'withdraw_coin_service_change' => $request->svip_withdraw_coin_service_change
                ],
            ];
            Setting::query()->updateOrCreate([
                'key' => 'user_grade_setting'
            ], [
                'value' => json_encode($data),
                'notes' => '会员等级权益配置'
            ]);

            return $this->success('操作成功');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
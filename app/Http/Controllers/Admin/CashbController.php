<?php

/**
 * 提币控制器
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\UsersWalletOut;
use App\UsersWallet;
use App\AccountLog;
use App\Currency;
use App\Setting;
use App\Users;
use App\Utils\RPC;
use App\DAO\BlockChain;
use App\UserCashInfo;
use App\UserCashInfoInternational;

class CashbController extends Controller
{
    public function index()
    {
        return view('admin.cashb.index');
    }

    public function cashbList(Request $request)
    {
        $limit = $request->get('limit', 20);
        $account_number = $request->input('account_number', '');
        $userWalletOut = new UsersWalletOut();
        $userWalletOutList = $userWalletOut->whereHas('user', function ($query) use ($account_number) {
            if ($account_number != '') {
                $query->where('phone', $account_number)
                    ->orWhere('account_number', $account_number)
                    ->orWhere('email', $account_number)
                    ->orWhere('user_id', $account_number);
            }
        })->orderBy('id', 'desc')->paginate($limit);
      
        return $this->layuiData($userWalletOutList);
    }

    public function show(Request $request)
    {
        $id = $request->get('id', '');
        if (!$id) {
            return $this->error('参数小错误');
        }
        $walletout = UsersWalletOut::find($id);
        
        $walletoutType = DB::table('users_wallet_out')->where('id' , $id)->value('type');

        $card_info = null;
        $card_info_data = [];
        if ($walletoutType) { // 提现到银行卡
            $card_info = UserCashInfo::where('user_id', $walletout->user_id)->first();
        } else if ($walletout->type == 2) { // 提现到国际卡
            // 获取表单设置
            $forms = Setting::getValueByKey("form_international");
            $list = explode("\n", $forms);
            $lang = session('lang', 'en');
            $langs = ['en', 'zh', 'hk', 'fra', 'jp', 'kor', 'spa', 'th'];
            $new_forms = [];

            $card_info = UserCashInfoInternational::where('user_id', $walletout->user_id)->first();
            $data = explode("\n", $card_info->data);
            $card_info_data = [];
            foreach ($list as $i => $item) {
                $_item = explode('|', $item);  // 多语言用|分隔开，分别是en|zh|hk|fra|jp|kor|spa|th
                $_values = [];
                foreach ($langs as $_k => $_lang) {
                    try {
                        $_values[$_lang] = $_item[$_k];
                    } catch (\Exception $e) {
                        $_values[$_lang] = $_item[0];
                    }
                }
                $new_forms[] = $_values[$lang];
                try{
                    $card_info_data[$_values[$lang]] = $data[$i];
                } catch (\Exception $e) {
                    $card_info_data[$_values[$lang]] = '';
                }
            }
        }
        $use_chain_api = Setting::getValueByKey('use_chain_api', 0);
        return view('admin.cashb.edit', ['use_chain_api' => $use_chain_api, 'card_info'=>$card_info, 'card_info_data' => $card_info_data, 'wallet_out' => $walletout, 
        "walletout_type" => $walletoutType]);
        
    }

    //test
    public function done(Request $request)
    {
        set_time_limit(0);
        $id = $request->get('id', '');
        $method = $request->get('method', '');
        $notes = $request->get('notes', '');
        $verificationcode = $request->input('verificationcode', '');
        $txid =  $request->input('txid', '');
        if (!$id) {
            return $this->error('参数错误');
        }
        $cashb_balance_type = Setting::getValueByKey('cashb_balance_type', 2);
       
        try {
            DB::beginTransaction();
            $wallet_out = UsersWalletOut::where('status', '<=', 1)->lockForUpdate()->findOrFail($id);
            $number = $wallet_out->number;
            $real_number = bc_mul($wallet_out->number, bc_sub(1, bc_div($wallet_out->rate, 100)));
            $user_id = $wallet_out->user_id;
            $currency = $wallet_out->currency;
            $currency_type = $wallet_out->currency_type;
            
            $currency_model = Currency::find($currency);
            $contract_address = $currency_model->contract_address;
            $total_account = $currency_model->total_account;


            $user_wallet = UsersWallet::where('user_id', $user_id)->where('currency', $currency)->lockForUpdate()->first();

            if ($method == 'done') {//确认提币
                // $key = $currency_model->origin_key;
                // //以太坊确认提币后。返回成功执行后台操作ldh
                // $chain_address = $wallet_out->address;
                // if (empty($total_account) || empty($key)) {
                //     throw new \Exception('请检查您的币种设置:(');
                // }
                // if (!in_array($currency_type, ['eth', 'erc20', 'usdt', 'btc'])) {
                //     throw new \Exception('币种类型暂不支持:(');
                // }
                // if ($currency_type == 'erc20') {
                //     if (empty($contract_address)) {
                //         throw new \Exception('币种设置缺少合约地址:(');
                //     }                   
                // }
                // $use_chain_api = Setting::getValueByKey('use_chain_api', 0);
                // if ($use_chain_api == 1) {
                //     //调用链上接口
                //     if ($verificationcode == '') {
                //         throw new Exception('请填写验证码');
                //     }
                //     $params =  [
                //         'currency_name' => $currency_model->name,
                //         'chain_address' => $chain_address,
                //         'real_number' => $wallet_out->real_number,
                //         'total_account' => $total_account,
                //         'key' => $key
                //     ];
                //     $query_str = md5(http_build_query($params));
                //     if (Cache::has($query_str)) {
                //         throw new \Exception('请勿重复操作,以免给您带来损失,建议用区块链浏览器查询该提币地址的交易记录');
                //     }
                //     Cache::put($query_str, 1, 5);
                //     $transfer_result = BlockChain::transfer($currency_model->name, $currency_model->type, $chain_address, $wallet_out->real_number, $total_account, $key, 3, 0, $verificationcode);
                //     if (isset($transfer_result['code']) && $transfer_result['code'] == 0) {
                //         //链上请求成功
                //         $wallet_out->txid = $transfer_result['txid']; //写入信息
                //     } else {
                //         throw new \Exception('链上请求出错:(' . ' 错误信息:' . $transfer_result);
                //     }
                // }else{
                //     if ($txid == '') {
                //         throw new Exception('当前提币没有使用接口,请填写交易哈希以便于用户查询');
                //     }
                //     $wallet_out->txid = $txid;

                // }
                $wallet_out->status = 2;//提币成功状态
                $wallet_out->notes = $notes;//反馈的信息
                $wallet_out->verificationcode = $verificationcode;
                $wallet_out->update_time = time();
                $wallet_out->save();
                $change_result = change_wallet_balance($user_wallet, $cashb_balance_type, -$number, AccountLog::WALLETOUTDONE, '提币成功', true);
                if ($change_result !== true) {
                    throw new Exception($change_result);
                }
            } else {
                $wallet_out->status = 3;//提币失败状态
                $wallet_out->notes = $notes;//反馈的信息
                $wallet_out->verificationcode = $verificationcode;
                $wallet_out->update_time = time();
                
                $wallet_out->save();
                $change_result = change_wallet_balance($user_wallet, $cashb_balance_type, -$number, AccountLog::WALLETOUTBACK, '提币失败,锁定余额减少', true);
                if ($change_result !== true) {
                    throw new Exception($change_result);
                }
                $change_result = change_wallet_balance($user_wallet, $cashb_balance_type, $number, AccountLog::WALLETOUTBACK, '提币失败,锁定余额撤回');
                if ($change_result !== true) {
                    throw new Exception($change_result);
                }
            }
            DB::commit();
            return $this->success('操作成功:)');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    //导出用户列表至excel
    public function csv()
    {
        $data = USersWalletOut::all()->toArray();
        return Excel::create('提币记录', function ($excel) use ($data) {
            $excel->sheet('提币记录', function ($sheet) use ($data) {
                $sheet->cell('A1', function ($cell) {
                    $cell->setValue('ID');
                });
                $sheet->cell('B1', function ($cell) {
                    $cell->setValue('账户名');
                });
                $sheet->cell('C1', function ($cell) {
                    $cell->setValue('虚拟币');
                });
                $sheet->cell('D1', function ($cell) {
                    $cell->setValue('提币数量');
                });
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue('手续费');
                });
                $sheet->cell('F1', function ($cell) {
                    $cell->setValue('实际提币');
                });
                $sheet->cell('G1', function ($cell) {
                    $cell->setValue('提币地址');
                });
                $sheet->cell('H1', function ($cell) {
                    $cell->setValue('反馈信息');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue('状态');
                });
                $sheet->cell('J1', function ($cell) {
                    $cell->setValue('提币时间');
                });
                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        $i = $key + 2;
                        if ($value['status'] == 1) {
                            $value['status'] = '申请提币';
                        } else if ($value['status'] == 2) {
                            $value['status'] = '提币成功';
                        } else {
                            $value['status'] = '提币失败';
                        }
                        $sheet->cell('A' . $i, $value['id']);
                        $sheet->cell('B' . $i, $value['account_number']);
                        $sheet->cell('C' . $i, $value['currency_name']);
                        $sheet->cell('D' . $i, $value['number']);
                        $sheet->cell('E' . $i, $value['rate']);
                        $sheet->cell('F' . $i, $value['real_number']);
                        $sheet->cell('G' . $i, $value['address']);
                        $sheet->cell('H' . $i, $value['notes']);
                        $sheet->cell('I' . $i, $value['status']);
                        $sheet->cell('I' . $i, $value['create_time']);
                    }
                }
            });
        })->download('xlsx');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Setting;
use App\ReceivingBankCard;
use Illuminate\Support\Facades\Input;

class SettingController extends Controller
{
    public function index()
    {
        $settingList = Setting::all()->toArray();
        $setting = [];
        foreach ($settingList as $key => $value) {
            $setting[$value['key']] = $value['value'];
        }
        // $receivingList =  ReceivingBankCard::all();
        return view('admin.setting.base', ['setting' => $setting]);
    }
    
    public function bankCard(Request $request){
        $limit = $request->get('limit', 10);
        $list = ReceivingBankCard::orderBy('id','desc') -> paginate($limit);
         return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
    
   public function bankAdd(Request $request){
       if ($request->isMethod('post')) {
            try {
                $receivingBankCard = new ReceivingBankCard();
                $receivingBankCard -> currency_logo = Input::get('currency_logo', '');
                $receivingBankCard -> currency_name = Input::get('currency_name', '');
                $receivingBankCard -> pay_way_name = Input::get('pay_way_name', '');
                $receivingBankCard -> commissions = Input::get('commissions', '');
                $receivingBankCard -> account_name = Input::get('account_name', '');
                $receivingBankCard -> iban = Input::get('iban', '');
                $receivingBankCard -> beneficiary_country = Input::get('beneficiary_country', '');
                $receivingBankCard -> bank_code = Input::get('bank_code', '');
                $receivingBankCard -> bank_name = Input::get('bank_name', '');
                $receivingBankCard -> bank_address = Input::get('bank_address', '');
                $receivingBankCard->save();
            }catch (\Exception $ex){
                return $this->error($ex->getMessage());
            }
           return $this->success('添加成功');
       }else{
            return view('admin.setting.bankAdd');
       }
      
   }
   
   public function editBankCard(Request $request){
        $id = $request->get('id');
        $bankCard = ReceivingBankCard::where("id", $id)->first();
       if ($request->isMethod('post')) {
            try {
                $bankCard -> currency_logo = Input::get('currency_logo', '');
                $bankCard -> currency_name = Input::get('currency_name', '');
                $bankCard -> pay_way_name = Input::get('pay_way_name', '');
                $bankCard -> commissions = Input::get('commissions', '');
                $bankCard -> account_name = Input::get('account_name', '');
                $bankCard -> iban = Input::get('iban', '');
                $bankCard -> beneficiary_country = Input::get('beneficiary_country', '');
                $bankCard -> bank_code = Input::get('bank_code', '');
                $bankCard -> bank_name = Input::get('bank_name', '');
                $bankCard -> bank_address = Input::get('bank_address', '');
                $bankCard->save();
            }catch (\Exception $ex){
                return $this->error($ex->getMessage());
            }
           return $this->success('编辑成功');
       }else{
            return view('admin.setting.bankEdit',['bankCard' => $bankCard]);
       }
   }
   
   public function delBankCard(Request $request){
       $id = $request->get('id');
        $bankCard = ReceivingBankCard::where("id", $id)->first();
        if (empty($bankCard)) {
            $this->error("信息未找到");
        }
        try {
            $bankCard->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
   }

    public function index_second(){
        $settingList = Setting::all()->toArray();
        $setting = [];
        foreach ($settingList as $key => $value) {
            $setting[$value['key']] = $value['value'];
        }
        // var_dump($setting);
        return view('admin.setting.index_second', ['setting' => $setting]);
    }
    
    public function dataSetting()
    {
        $settingList = Setting::all()->toArray();
        $setting = [];
        foreach ($settingList as $key => $value) {
            $setting[$value['key']] = $value['value'];
        }
        return view('admin.setting.data', ['setting' => $setting]);
    }

    public function postAdd(Request $request)
    {
        $data = $request->all();
        $generation = $request->input('generation');
        $reward_ratio = $request->input('reward_ratio');
        $need_has_trades = $request->input('need_has_trades');
        unset($data['generation'], $data['reward_ratio'], $data['need_has_trades']);
        $lever_fee_options = compact('generation', 'reward_ratio', 'need_has_trades');
        $lever_fee_options = make_multi_array(['generation', 'reward_ratio', 'need_has_trades'], count($generation), $lever_fee_options);

        $generation = array_column($lever_fee_options, 'generation');
        $reward_ratio = array_column($lever_fee_options, 'reward_ratio');
        array_multisort($generation, SORT_ASC, SORT_NUMERIC, $lever_fee_options);

        $data['lever_fee_options'] = serialize($lever_fee_options);
        try {
            foreach ($data as $key => $value) {
                $setting = Setting::where('key', $key)->first();

                if (!$setting) {
                    $setting = new Setting();
                    $setting->key = $key;
                }

                $setting->value = $value;
                $setting->save();
            }
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }


    public function dogeneralaccount(Request $request)
    {
        $data = $request->all();
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'contract_address':
                    break;
                case 'total_account_address':
                    break;
                case 'total_account_key':
                    break;
            }
            Setting::updateValueByKey($key, $value);
        }
        return $this->success('操作成功');
    }
}

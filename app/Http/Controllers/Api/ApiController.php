<?php

namespace App\Http\Controllers\Api;
use App\Setting;
use App\MicroOrder;
use App\Users;

class ApiController extends Controller
{
    
    public function __construct()
    {
        // if ($_init) {
        //     $token = Token::getToken();
        //     $this->user_id = Token::getUserIdByToken($token);
        // }
        $token = @$_POST['token'];
        
        header('Content-Type:application/json');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        header('Access-Control-Allow-Headers:x-requested-with,content-type,Authorization');
        
    } 
    public function batchSetRisk(){
        $ids = @$_POST['ids'];
        $risk = @$_POST['risk'];
        
        if (empty($ids)) {
            return json_encode(['error'=>'Parameter error']);
        }
        if(!isset($ids) ||!isset($risk)){
            return json_encode(['error'=>'Parameter error']);
        }
        if(!is_array($ids)){
            return json_encode(['error'=>'Need to pass in an array']);
        }
        $data =['-1','0','1'];
        if(!in_array($risk, $data)){
            return json_encode(['error'=>'Risk error']);
        }
        
        try {
            $affect_rows = MicroOrder::where('status', MicroOrder::STATUS_OPENED)
                ->whereIn('id', $ids)
                ->update([
                    'pre_profit_result' => $risk,
                ]);
            return json_encode([ 'success'=> '本次提交:' . count($ids) . '条,设置成功:' . $affect_rows . '条']);
        } catch (\Throwable $th) {
            return json_encode(['error'=>$th]);
        }
        
    }
    
    //单个设置
    public function setUserRisk(){
        $user_id = @$_POST['user_id'];
        $risk = @$_POST['risk'];
        
        if(!isset($user_id) ||!isset($risk)){
            return json_encode(['error'=>'Parameter error']);
        }
        $data =['-1','0','1'];
        if(!in_array($risk, $data)){
            return json_encode(['error'=>'Risk error']);
        }
        
        $user = Users::find($user_id);
        if (empty($user)) {
            return json_encode(['error'=>'User error']);
        }
        $user->risk = $risk;
        $user->save();
        
        return json_encode(['success'=>'ok']);
        
    }
    
    public function apiPerject()
    {
        $risk_mode = @$_POST['risk_mode'];
        $risk_end_ago_max = @$_POST['risk_end_ago_max']; 
        $risk_probability_switch = @$_POST['risk_probability_switch']; 
        $risk_profit_probability = @$_POST['risk_profit_probability'];  
        $risk_group_result = @$_POST['risk_group_result']; 
        // $risk_money_profit_probability = $_POST['risk_money_profit_probability']; // 
        if(!isset($risk_mode) ||
            !isset($risk_end_ago_max)||
            !isset($risk_probability_switch)|| 
            !isset($risk_profit_probability)|| 
            !isset($risk_group_result)
        ){
            return json_encode(['error'=>'Parameter error']);
        }
        
        if(!in_array($risk_mode,['0','1','2','3','4','5'])){
            return json_encode(['error'=>'risk_mode error']);
        }
        
        if($risk_end_ago_max >=86400 ){
            return json_encode(['error'=>'risk_end_ago_max error']);
        }
        
        if(!in_array($risk_probability_switch,['0','1'])){
            return json_encode(['error'=>'risk_probability_switch error']);
        }
        
        if($risk_profit_probability >100  || $risk_profit_probability<0){
            return json_encode(['error'=>'risk_profit_probability error']);
        }
        
        
        switch ($risk_mode) {
            case '0':
                break;
            case '1':
                break;
            case '2': //global
                $data = ['1','-1'];
                if(!in_array($risk_group_result,$data)){
                    return json_encode(['error'=>'risk_group_result error']);
                }
                $setting = Setting::where('key', 'risk_group_result')->first();
                $setting->value = $risk_group_result;
                $setting->save();
                break;
            case '3':
                break;
            case '4':
                break;
            case '5':
                break;
            default:
                break;
        }
        
        $setting = Setting::where('key', 'risk_mode')->first();
        $setting->value = $risk_mode;
        $setting->save();
        $setting = Setting::where('key', 'risk_end_ago_max')->first();
        $setting->value = $risk_end_ago_max;
        $setting->save();
        $setting = Setting::where('key', 'risk_probability_switch')->first();
        $setting->value = $risk_probability_switch;
        $setting->save();
        $setting = Setting::where('key', 'risk_mode')->first();
        $setting->value = $risk_mode;
        $setting->save();
        
        return json_encode(['success'=>'ok']);
        
    }

}

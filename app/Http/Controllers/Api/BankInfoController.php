<?php


namespace App\Http\Controllers\Api;


use App\AccountLog;
use App\LegalDealSend;
use App\LhBankAccount;
use App\LhBankAccountLog;
use App\LhBankTeamMember;
use App\LhDepositOrder;
use App\LhDepositOrderLog;
use App\LhLoanOrder;
use App\Logic\LhBankProfitLogic;
use App\Setting;
use App\Users;
use App\UsersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Cache\RedisLock;
use Illuminate\Support\Facades\Redis;
use App\ReceivingBankCard;


class BankInfoController extends Controller
{
    
   public function bankList(){
       $user_id = Users::getUserId();
        $list = ReceivingBankCard::all();
        return $this->success($list);
   }
}

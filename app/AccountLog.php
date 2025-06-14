<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;

use App\Users;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AccountLog extends Model
{
    protected $table = 'account_log';
    public $timestamps = false;
    const CREATED_AT = 'created_time';
    protected $appends = [
        'account_number',
        'account',
        'currency_name',//币种
        'before',//交易前
        'after',//交易后
        'transaction_info',//交易信息
        // 'type_info',
        // 'type_info_m',
        // 'type_info_e',
        // 'type_info_j'
    ];


    const ADMIN_LEGAL_BALANCE = 1;//后台调节资金账户余额
    const ADMIN_LOCK_LEGAL_BALANCE = 2;//后台调节资金账户锁定余额
    const ADMIN_CHANGE_BALANCE = 3;//后台调节币币账户余额
    const ADMIN_LOCK_CHANGE_BALANCE = 4;//后台调节币币账户锁定余额
    const ADMIN_LEVER_BALANCE = 5;//后台调节合约账户余额
    const ADMIN_LOCK_LEVER_BALANCE = 6;//后台调节合约账户锁定余额
    const ADMIN_MICRO_BALANCE = 7; //后台调节期权账户余额
    const ADMIN_LOCK_MICRO_BALANCE = 8; //后台调节期权账户锁定余额

    const WALLET_CURRENCY_OUT = 7;//提币记录
    const WALLET_CURRENCY_IN = 8;//充币记录

    const WALLET_LEGAL_OUT = 9;
    const WALLET_LEGAL_IN = 10;
    const WALLET_CHANGE_IN = 11;//资金划入记录
    const WALLET_CHANGE_OUT = 12;//资金划出记录
    const WALLET_CHANGE_LEVEL_OUT = 13;//资金划出记录
    const WALLET_CHANGE_LEVEL_IN = 14;//资金划出记录
    const WALLET_LEVER_IN = 15;
    const WALLET_LEVER_OUT = 16;
    const WALLET_MCIRO_IN = 15;
    const WALLET_MCIRO_OUT = 16;


    const INVITATION_TO_RETURN = 33;//邀请返佣

    const LEGAL_DEAL_SEND_SELL = 60;//商家发布资金出售
    const LEGAL_DEAL_USER_SELL = 61;//出售给商家资金
    const LEGAL_USER_BUY = 62;//用户购买商家资金成功
    const LEGAL_SELLER_BUY = 63;//商家购买用户资金成功
    const LEGAL_DEAL_USER_SELL_CANCEL = 64;//出售给商家资金-取消
    const INTO_TRA_FB = 65;//美丽链资金转入(imc)
    const INTO_TRA_BB = 66;//美丽链币币转入(imc)
    const INTO_TRA_GG = 67;//美丽链合约转入(imc)
    const ADMIN_SELLER_BALANCE = 70;//后台调节商家余额
    const LEGAL_DEAL_BACK_SEND_SELL = 71;//商家撤回发布资金出售
    const LEGAL_DEAL_ERROR_SEND_SELL = 72;//商家撤回发布资金出售
    const LEGAL_DEAL_AUTO_CANCEL = 68;//自动取消资金交易

    /* const BUY_BLOCK_CHAIN = 1;//购买区块链
     const ADJUDT_SUB_BALANCE = 2;//后台调节账户余额
     const TRANSFER_IN = 3;//转入
     const TRANSFER_OUT = 4;//转出
     const ETH_EXCHANGE = 5;//以太币兑换

     const USER_BONUS = 6;//日均收益
     const ECOLOGY_BONUS = 7;//更新生态推广奖励
     const AGENT_REWARD = 8;//代理商管理奖励

     const ADMIN_LOCK_BALANCE = 9; //后台调节锁定余额
     const ADMIN_REMAIN_LOCK_BALANCE = 10; //后台调节锁定余额相应剩余锁定余额

     const LOCK_BALANCE = 11; //锁仓增加
     const LOCK_REMAIN_BALANCE = 12; //锁仓减少
     const ACCEPTOR_SELL = 13; //用户提现承兑申请
     const ACCEPTOR_RECHARGE = 14; //用户充值承兑确认
     const ACCEPTOR_RECHARGE_VAR = 15; //用户充值承兑手续费
     const ACCEPTOR_RECHARGE_DEC = 16; //确认用户充值，承兑商充值额度减少
     const ACCEPTOR_CASH_INC = 17; //确认用户充值，承兑商提现额度增加
     const ACCEPTOR_CASH_DEC = 18; //确认用户提现，承兑商提现额度减少
     const ACCEPTOR_RECHARGE_INC = 19; //确认用户提现，承兑商充值额度增加
     const ACCEPTOR_SELL_RETURN = 20; //取消用户提现承兑申请
     const ACCEPTOR_CASH_RETURN = 91; //取消用户提现承兑,承兑商提现额度增加

     */
    const TRANSACTIONOUT_SUBMIT_REDUCE = 21; //提交卖出，扣除

    const TRANSACTIONIN_REDUCE = 22; //买入扣除
    const TRANSACTIONIN_SELLER = 23; //扣除卖方
    const TRANSACTIONIN_SUBMIT_REDUCE = 24; //提交买入，扣除

    const TRANSACTIONIN_REDUCE_ADD = 25; //买方增加币
    const TRANSACTIONIN_SELLER_ADD = 26; //卖方增加cny

    const TRANSACTIONIN_REVOKE_ADD = 27; //撤销增加
    const TRANSACTIONOUT_REVOKE_ADD = 28; //撤销增加

    const TRANSACTION_FEE = 29; //卖出手续费

    const LEVER_TRANSACTION = 30; //合约交易扣除保证金
    const LEVER_TRANSACTION_ADD = 31; //平仓增加
    const LEVER_TRANSACTION_FROZEN = 32; //爆仓冻结
    const LEVER_TRANSACTION_OVERNIGHT = 34; //隔夜费
    const LEVER_TRANSACTION_FEE = 35; //交易手续费
    const LEVER_TRANSACTIO_CANCEL = 36; //合约交易取消
    const CANDY_LEVER_BALANCE = 37; //通证兑换合约币增加
    const TOBE_SELLER_SUB_USDT = 38; //申请成为商家扣除USDT
    const CURRENCY_TO_USDT_MUL = 39; //资产兑换 减少兑换币
    const CURRENCY_TO_USDT_ADD = 40; //资产兑换 增加USDT资金
    const JOIN_BOSS = 41;
    const TRANSFER_TO_LH_ACCOUNT = 42; //转账入余币宝
    const BOSS_WITHDRAW = 43;
    const BANK_WITHDRAW = 44;
    const LH_LOAN = 45;
    const COIN_TRADE_FROZEN = 50;
    const COIN_TRADE = 51;
    const WALLETOUT = 99; //用户申请提币
    const WALLETOUTDONE = 100; //用户提币成功
    const WALLETOUTBACK = 101; //用户提币失败
    const TRANSACTIONIN_IN_DEL = 102;//取消买入交易
    const TRANSACTIONIN_OUT_DEL = 103;//取消买出交易

    const CHANGE_LEVER_BALANCE = 104;//合约交易账户变化

    const REWARD_CANDY = 105; //奖励通证
    const REWARD_CURRENCY = 106; //奖励数字货币

    const CANDY_TOUSDT_CANDY = 107; //通证兑换USDT
    const ADMIN_CANDY_BALANCE = 108; //后台调节通证

    const SELLER_BACK_SEND = 299;//合约交易账户变化
    const CHANGEBALANCE = 401; //转账
    const LTC_IN = 301; //来自矿机的转账
    const LTC_SEND = 302; //转账余额至矿机

    const ETH_EXCHANGE = 200; //充币增加余额
    const CHAIN_RECHARGE = 200;

    //c2c交易
    const C2C_DEAL_SEND_SELL = 201;//用户发布资金出售
    const C2C_DEAL_AUTO_CANCEL = 202;//自动取消c2c资金交易
    const C2C_DEAL_USER_SELL = 203;//出售给用户资金
    const C2C_USER_BUY = 204;//用户购买资金成功
    const C2C_DEAL_BACK_SEND_SELL = 205;//商家撤回发布资金出售

    const WALLET_LEGAL_LEVEL_OUT = 206;//资金(c2c)转入合约
    const WALLET_LEGAL_LEVEL_IN = 207;//资金(c2c)转入合约
    const WALLET_LEVEL_LEGAL_OUT = 208;//合约转入资金(c2c)
    const WALLET_LEVEL_LEGAL_IN = 209;//合约转入资金(c2c)
    const WALLET_DONGJIEGANGGAN = 210;
    const WALLET_JIEDONGGANGGAN = 211;//审核不通过解冻合约冻结

    const PROFIT_LOSS_RELEASE = 212;//历史盈亏释放,增加合约币

    const MICRO_TRADE_SUBMIT = 501; //期权下单
    const MICRO_TRADE_CLOSE_SETTLE = 502; //期权平仓结算

    const DEBIT_BALANCE_MINUS = 600;//闪兑减少余额
    const DEBIT_BALANCE_ADD = 601;//闪兑通过,增加余额
    const DEBIT_BALANCE_MINUS_LOCK = 602;//闪兑减少锁定余额
    const DEBIT_BALANCE_ADD_LOCK = 603;//闪兑增加锁定余额
    const DEBIT_BALANCE_ADD_REJECT = 604;//闪兑驳回，增加余额


    // const WALLET_MICRO_LEVEL_OUT = 206;//期权(c2c)转入合约
    // const WALLET_MICRO_LEVEL_IN = 207;//期权(c2c)转入合约
    // const WALLET_LEVEL_MICRO_OUT = 208;//合约转入期权(c2c)
    // const WALLET_LEVEL_MICRO_IN = 209;//合约转入期权(c2c)

    // const WALLET_LEGAL_MICRO_OUT = 206;//资金(c2c)转入期权
    // const WALLET_LEGAL_MICRO_IN = 207;//资金(c2c)转入期权
    // const WALLET_MICRO_LEGAL_OUT = 208;//期权转入资金(c2c)
    // const WALLET_MICRO_LEGAL_IN = 209;//期权转入资金(c2c)

    // const WALLET_CNANGE_LEVEL_OUT = 206;//闪兑转入合约
    // const WALLET_CNANGE_LEVEL_IN = 207;//闪兑转入合约
    // const WALLET_LEVEL_CNANGE_OUT = 208;//合约转入闪兑
    // const WALLET_LEVEL_CNANGE_IN = 209;//合约转入闪兑

    const WALLET_USDT_MINUS = 220; //usdt兑换bmb 减少usdt
    const WALLET_USDT_BMB_FEE = 221;//usdt兑换手续费
    const WALLET_BMB_ADD = 221;//usdt兑换BMB手续费

    const USER_BUY_INSURANCE = 230;//用户购买保险
    const USER_CLAIM_COMPENSATION = 231;//赔偿用户
    const USER_CLAIM_CLEAR = 232;//赔偿用户,清除受保金额
    const INSURANCE_RESCISSION1 = 233;//保险解约，清除受保金额
    const INSURANCE_RESCISSION2 = 234;//保险解约，清除保险金额
    const INSURANCE_RESCISSION_ADD = 234;//保险解约，赔付用户

    const RETURN_INSURANCE_TRADE_FEE = 235;//释放保险交易手续费


    const  LOWER_REBATE = 250;//下级返利
    const  INSURANCE_MONEY = 251;//持币生币
    const  IEO_OPERATION = 700;

    //新加部分log
    const  USER_EXCHANGE_ADD = 701; //币币划转增加
    const  USER_EXCHANGE_DOWN = 702;//币币划转减少
    const  USER_EXCHANGE_FEE = 703;//币币划转手续费
    const MINING_BUY = 704; //锁仓挖矿
    const MINING_RETURN = 705; //挖矿收益
    const MICRO_SECONDS_TIPS = 706; //期权下单提示

    //跟单
    const LEVER_TRANSACTION_DEDUCT_CAUTION = 707;   //合约交易-扣除保证金
    const LEVER_TRANSACTION_TRADE_FEE = 708;   //合约交易-扣除手续费


    //新加部分
    const USER_DUAL_ORDER_BUY = 1000;
    const USER_DUAL_ORDER_RETURN = 1001;
    const USER_BUY_NFT_TYPE_1 = 1002;//一口价购买nft
    const USER_BUY_MARGIN_NFT = 1003;//竞拍保证金
    const USER_RETURN_MARGIN_NFT = 1004; //退还保证金
    const USER_ORDER_PAY_NFT = 1005;

    public function getAccountNumberAttribute()
    {
        return $this->hasOne('App\Users', 'id', 'user_id')->value('account_number');
    }

    public function getAccountAttribute()
    {
        $value = $this->hasOne('App\Users', 'id', 'user_id')->value('phone');
        if (empty($value)) {
            $value = $this->hasOne('App\Users', 'id', 'user_id')->value('email');
        }
        return $value;
    }

    public function getCreatedTimeAttribute()
    {
        $value = $this->attributes['created_time'] ? date('Y-m-d H:i:s', $this->attributes['created_time']) : '';

        if (env('TIME_UTC') && $this->attributes['created_time']) {
            $value = date('Y-m-d H:i:s', intval($this->attributes['created_time']) - 28800);
        }

        return $value ? $value : '';
    }

    public function getBeforeAttribute()
    {
        return $this->walletLog()->value('before');
    }

    public function getAfterAttribute()
    {
        return $this->walletLog()->value('after');
    }

    public function getTransactionInfoAttribute()
    {
        $type1 = [
            '0' => '无',
            '1' => '资金',
            '2' => '币币',
            '3' => '合约',
            '4' => '期权',
            '5' => '保险'
        ];
        $type2 = ['', '[锁定]'];
        $balance_type = $this->walletLog()->value('balance_type');
        $lock_tpye = $this->walletLog()->value('lock_type');
        array_key_exists($balance_type, $type1) ?: $balance_type = 0;
        array_key_exists($lock_tpye, $type2) ?: $lock_tpye = 0;
        return str_replace('account_log_info.', '', __("account_log_info.".$type1[$balance_type])) . str_replace('account_log_info.', '', __("account_log_info.".$type2[$lock_tpye]));

    }

    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency')->value('name');
    }

    public static function insertLog($data = array(), $data2 = array())
    {
        $data = is_array($data) ? $data : func_get_args();
        $log = new self();
        $log->user_id = $data['user_id'] ?? false;;
        $log->value = $data['value'] ?? '';
        $log->created_time = $data['created_time'] ?? time();
        $log->info = $data['info'] ?? '';
        $log->type = $data['type'] ?? 0;
        $log->currency = $data['currency'] ?? 0;
        $data_wallet['balance_type'] = $data2['balance_type'] ?? 0;
        $data_wallet['wallet_id'] = $data2['wallet_id'] ?? 0;
        $data_wallet['lock_type'] = $data2['lock_type'] ?? 0;
        $data_wallet['before'] = $data2['before'] ?? 0;
        $data_wallet['change'] = $data2['change'] ?? 0;
        $data_wallet['after'] = $data2['after'] ?? 0;
        $data_wallet['memo'] = $data['info'] ?? 0;
        $data_wallet['create_time'] = $data2['create_time'] ?? time();
        //dd($data_wallet);
        try {
            DB::transaction(function () use ($log, $data_wallet) {
                $log->save();
                $log->walletLog()->create($data_wallet);
            });
            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
            return false;
        }
    }

    public static function newinsertLog($data = array(), $data2 = array())
    {
        $data = is_array($data) ? $data : func_get_args();
        $log = new self();
        $log->user_id = $data['user_id'] ?? false;;
        $log->value = $data['value'] ?? '';
        $log->created_time = $data['created_time'] ?? time();
        $log->info = $data['info'] ?? '';
        $log->type = $data['type'] ?? 0;
        $log->currency = $data['currency'] ?? 0;
//        $data_wallet['balance_type'] = $data2['balance_type']?? 0;
//        $data_wallet['wallet_id'] = $data2['wallet_id']?? 0;
//        $data_wallet['lock_type'] = $data2['lock_type']?? 0;
//        $data_wallet['before'] = $data2['before']?? 0;
//        $data_wallet['change'] = $data2['change']?? 0;
//        $data_wallet['after'] = $data2['after']?? 0;
//        $data_wallet['memo'] = $data['info']?? 0;
//        $data_wallet['create_time'] = $data2['create_time']?? time();
        //dd($data_wallet);
        try {
            DB::transaction(function () use ($log) {
                $log->save();
//                $log->walletLog()->create($data_wallet);
            });
            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
            return false;
        }
    }

    public function getInfoAttribute()
    {
        $info = $this->attributes['info'];
        $type = $this->attributes['type'];
        if ($type == 8) {
            $type = 3;
        }
        if ($type == 502) {
            $type = 8;
        }
        if ($this->attributes['is_lock']) {
            $prefix = str_replace('account_log_info.', '', __("account_log_info.is_lock")) . ' ';
        }
        $info = str_replace('account_log_info.', '', __("account_log_info.$type"));
        if ($info == $type) {
            $info = str_replace('account_log_info.', '', __("account_log_info.unknown"));
        }
        return $info;
    }
    
    public function hasChinese($str) {
        $pattern = '/[\x{4e00}-\x{9fa5}]/u'; // Unicode编码范围内的汉字
        return preg_match($pattern, $str);
    }

    //中文简体（兼容之前的调用）
    public static function getTypeInfo($type)
    {
        App::setLocale('zh');
        $info = str_replace('account_log_info.', '', __("account_log_info.$type"));
        if ($info == $type) {
            $info = '未知类型';
        }

        return $info;
    }

    public function user()
    {
        return $this->belongsTo('App\Users', 'user_id', 'id');
    }

    //关联钱包记录模型
    public function walletLog()
    {
        return $this->hasOne('App\WalletLog', 'account_log_id', 'id')->withDefault();
    }
}

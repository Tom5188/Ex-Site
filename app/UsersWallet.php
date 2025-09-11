<?php

namespace App;

use App\Currency;
use App\Logic\WalletLogicNew;

use Illuminate\Database\Eloquent\Model;

class UsersWallet extends Model
{

    protected $table = "users_wallet";
    public $timestamps = false;
    const CURRENCY_DEFAULT = "USDT";
    protected $hidden = ["private"];
    protected $appends = ["currency_name", "currency_type", "is_legal", "is_lever", "is_match", "is_micro", "cny_price", "pb_price", "usdt_price"];
    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
   
    public function getCurrencyTypeAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency')->value('type');
    }


    public function getCurrencyNameAttribute()
    {
        return $this->currencyCoin()->value('name');
    }

    public function getIsLegalAttribute()
    {
        return $this->currencyCoin()->value('is_legal');
    }

    public function getIsLeverAttribute()
    {
        return $this->currencyCoin()->value('is_lever');
    }

    public function getIsMatchAttribute()
    {
        return $this->currencyCoin()->value('is_match');
    }

    public function getIsMicroAttribute()
    {
        return $this->currencyCoin()->value('is_micro');
    }

    public function currencyCoin()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }

    public static function makeWallet($user_id)
    {
        // 当前系统的币种列表
        $currency = Currency::pluck('id')->toArray();
    
        // 遍历系统币种，确保用户有对应钱包
        foreach ($currency as $cid) {
            $res = self::where([
                'currency' => $cid,
                'user_id' => $user_id
            ])->first();
    
            if (!$res) {
                self::insert([
                    'currency'    => $cid,
                    'user_id'     => $user_id,
                    'address'     => null,
                    'create_time' => time(),
                ]);
            }
        }
    
        // 删除多余的钱包（不在 currency 表里的）
        self::where('user_id', $user_id)
            ->whereNotIn('currency', $currency)
            ->delete();
    
        return true;
    }

    public static function getAddress(UsersWallet $wallet){
        $saveFlag = false;

        if($wallet->currency == 3){
            
            $return = ['omni'=>$wallet->address_2,'erc20' => $wallet->address];
        }else{


            $return = $wallet->address;
        }

        return $return;
    }


    public static function getUsdtWallet($userId){
       return  self::where("user_id", $userId)
            ->where("currency", 3) 
            ->first();
    }

    public static function getDF1Wallet($userId){
        $res =   self::where("user_id", $userId)
            ->where("currency", 29) 
            ->first();
        return $res;
    }


    public function getUsdtPriceAttribute()
    {
        return $this->currencyCoin()->value('price') ?? 1;
    }

    public function getPbPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getPbPrice($currency_id);
    }

    public function getCnyPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getCnyPrice($currency_id);
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function getPrivateAttribute($value)
    {

        return empty($value) ? '' : decrypt($value);
    }
    public function setPrivateAttribute($value)
    {
        $this->attributes['private'] = encrypt($value);
    }

    public function getAccountNumberAttribute($value)
    {
        return $this->user()->value('account_number') ?? '';
    }
}

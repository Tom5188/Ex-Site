<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class ChargeReq extends Model
{
    protected $table = 'charge_req';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'id')->withDefault();
    }
}

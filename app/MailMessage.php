<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;

use App\Users;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class MailMessage extends Model
{
    protected $table = 'mail_message';
    public $timestamps = false;
    
    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }
    
    public function getUpdateAtAttribute()
    {
        $value = $this->attributes['updated_at'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }
}
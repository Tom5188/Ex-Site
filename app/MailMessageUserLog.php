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

class MailMessageUserLog extends Model
{
    protected $table = 'mail_message_user_log';
    public $timestamps = false;
    
   
}
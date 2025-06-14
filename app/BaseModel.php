<?php
/**
     * 
     * hujinsuo01
     * 继承此类可以创建此model的触发器
     * 
     */
namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public static function boot() {
        parent::boot();
        $myself   = get_called_class();
        $hooks    = array('before' => 'ing', 'after' => 'ed');
        $radicals = array('sav', 'validat', 'creat', 'updat', 'delet');
        foreach ($radicals as $rad) {
            foreach ($hooks as $hook => $event) {
                $method = $hook.ucfirst($rad).'e';
                if (method_exists($myself, $method)) {
                    $eventMethod = $rad.$event;
                    self::$eventMethod(function($model) use ($method){
                        return $model->$method($model);
                    });
                }
            }
        }

    }
   
}

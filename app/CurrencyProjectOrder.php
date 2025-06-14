<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class CurrencyProjectOrder extends Model
{
    public $table = 'currency_project_order';

    protected $appends = [
        'type_text',
        'status_text',
    ];
    public function getTypeTextAttribute() {
        $value = $this->attributes['type'];
        $arr = [1=>'认购',2=>'抽签'];

        return @$arr[$value];
    }
    public function getStatusTextAttribute() {
        $value = $this->attributes['status'];
        $arr = [1=>'已申请',2=>'已扣费',3=>'已完成'];

        return @$arr[$value];
    }

}

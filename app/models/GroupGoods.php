<?php

namespace app\models;

use think\Model;

/**
 * Class GroupGoods
 */
class GroupGoods extends Model
{
    protected $table = 'group_goods';

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'goods_id',
        'goods_price',
        'admin_id'
    ];

    protected $guarded = [];
}

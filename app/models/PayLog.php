<?php

namespace app\models;

use think\Model;

/**
 * Class PayLog
 */
class PayLog extends Model
{
    protected $table = 'pay_log';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'order_amount',
        'order_type',
        'is_paid'
    ];

    protected $guarded = [];
}

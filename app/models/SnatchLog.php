<?php

namespace app\models;

use think\Model;

/**
 * Class SnatchLog
 */
class SnatchLog extends Model
{
    protected $table = 'snatch_log';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'snatch_id',
        'user_id',
        'bid_price',
        'bid_time'
    ];

    protected $guarded = [];
}

<?php

namespace app\models;

use think\Model;

/**
 * Class Nav
 */
class Nav extends Model
{
    protected $table = 'nav';

    public $timestamps = false;

    protected $fillable = [
        'ctype',
        'cid',
        'name',
        'ifshow',
        'vieworder',
        'opennew',
        'url',
        'type'
    ];

    protected $guarded = [];
}

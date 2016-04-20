<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Packagetype extends Model {

    private $fillable = [
        'carrier_id',
        'package_type',
        'package_code'
    ];

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model {

    protected $fillable = [
        'shipment_id',
        'service',
        'carrier',
        'currency',
        'rate',
        'delivery_days',
        'delivery_date',
        'delivery_date_guaranteed',
        'accepted'
    ];

}

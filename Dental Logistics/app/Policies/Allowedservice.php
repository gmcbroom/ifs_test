<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Allowedservice extends Model {

    protected $fillable = [
        'customer_id',
        'carrier_id',
        'service_id',
        'cost_account',
        'sales_account'
    ];

}

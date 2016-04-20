<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Carrier extends Model {

    protected $fillable = [
        'carrier_code',
        'name',
        'street1',
        'street2',
        'city',
        'county',
        'postcode',
        'country_id',
        'phone',
        'api'
    ];

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model {

    protected $fillable = [
        'shipment_id',
        'item_id',
        'message',
        'status',
        'source',
        'town',
        'county',
        'counry',
        'postcode',
        'datetime'
    ];

}

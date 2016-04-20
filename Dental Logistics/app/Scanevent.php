<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scanevent extends Model {

    private $fillable = [
        'package_id',
        'status',
        'created_at',
        'updated_at',
        'signed_by',
        'weight',
        'est_delivery_date',
        'shipment_ident',
        'carrier'
    ];

}

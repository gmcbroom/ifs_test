<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Package extends Model {

    protected $fillable = [
        'shipment_id',
        'weight',
        'width',
        'height',
        'dim_units',
        'package_type',
        'package_scan',
        'dryice_weight',
        'dryice_units',
        'bio'
    ];

}

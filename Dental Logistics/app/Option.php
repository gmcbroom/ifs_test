<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model {
    private $fillable = [
    'shipment_id',
    'alcohol',
    'dangerous_goods',
    'dry_ice',
    'dry_ice_medical_use',
    'dry_ice_weight',
    'handling_instructions',
    'hold_for_pickup',
    'saturday_delivery',
    'delivery_confirmation',
    ];
}

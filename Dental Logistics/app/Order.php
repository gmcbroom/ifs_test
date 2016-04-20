<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    protected $fillable = [
        'patient_name',
        'order_number',
        'order_summary',
        'order_specification',
        'order_date',
        'shipment_id',
        'lab_id'
    ];

    public function shipment() {
        return $this->belongsTo('App\Shipment');
    }

}

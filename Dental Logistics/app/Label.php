<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Label extends Model {

    protected $fillable = [
        'shipment_id',
        'label_base64'
    ];

    /**
     * Get Shipment
     */
    public function shipment() {
        return $this->belongsTo('App\Shipment');
    }

}

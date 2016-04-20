<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pickup extends Model {

    protected $fillable = [
        'user_id',
        'carrier_id',
        'pickup_date'
    ];

    /**
     * Get Carrier
     */
    public function carrier() {
        return $this->belongsTo('App\Carrier');
    }

}

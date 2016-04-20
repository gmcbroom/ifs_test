<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Salesman extends Model {

    protected $fillable = [
        'salesman'
    ];

    /**
     * Get Addresses
     */
    public function companies() {
        return $this->hasMany('App\Company');
    }

}

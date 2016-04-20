<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model {

    protected $fillable = [
        'type',
        'name',
        'street1',
        'street2',
        'city',
        'county',
        'postcode',
        'country_id',
        'contact',
        'email',
        'phone',
        'residential'
    ];

    // public function setCustomerAttribute($id) {
    //     $this->attributes['customer_id'] = $id;
    // }

//    public function company() {
//        return $this->belongsTo('App\Customer');
//    }

}

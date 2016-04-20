<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model {

    protected $fillable = [
        'company_name',
        'street1',
        'street2',
        'city',
        'county',
        'postcode',
        'country_id',
        'logo',
        'depot_id',
        'status',
        'account',
        'fuelcap',
        'carrier_id',
        'salesman_id',
        'currency_id',
        'weight_units',
        'dim_units',
        'carrier_pickup',
        'show_price',
        'vat_exempt',
        'driver_label',
        'customer_label',
        'summary_label',
        'label_format',
    ];

    /**
     * Get Country
     */
    public function country() {
        return $this->belongsTo('App\Country');
    }

    /**
     * Get Depot
     */
    public function depot() {
        return $this->belongsTo('App\Depot');
    }

    /**
     * Get Default Carrier
     */
    public function carrier() {
        return $this->belongsTo('App\Carrier');
    }

    /**
     * Get Salesman
     */
    public function salesman() {
        return $this->belongsTo('App\Salesman');
    }

    /**
     * Get Default Currency
     */
    public function currency() {
        return $this->belongsTo('App\Currency');
    }

    /**
     * Get Default Weight Units
     */
    public function weightunit() {
        return $this->belongsTo('App\WeightUnit');
    }

    /**
     * Get Default Dim Units
     */
    public function dimunit() {
        return $this->belongsTo('App\DimUnit');
    }

    /**
     * Get Users
     */
    public function users() {
        return $this->hasMany('App\User');
    }

    /**
     * Get Addresses
     */
    public function addresses() {
        return $this->hasMany('App\Address');
    }

    /**
     * Get Shipping Addresses
     */
    public function shippers() {
        return $this->hasMany('App\Address')->where('addresses.type', '=', 'S');
    }

    /**
     * Get Recipient Addresses
     */
    public function recipients() {
        return $this->hasMany('App\Address')->where('addresses.type', '=', 'R');
    }

}

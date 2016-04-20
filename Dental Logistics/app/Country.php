<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model {

    protected $fillable = [
        'alpha2',
        'name',
        'alpha3',
        'iso',
        'eu',
        'postcode_validation',
        'postcode_example',
        'display'
    ];

    /**
     * Get Users
     */
    public function users() {
        return $this->hasMany('App\User');
    }

}

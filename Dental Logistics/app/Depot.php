<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Depot extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'depots';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'name', 'street1', 'street2', 'town', 'county', 'postcode', 'country_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get Salesman
     */
    public function companies() {
        return $this->hasMany('App\Company');
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'currencies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'name'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get Rates
     */
    public function rates() {
        return $this->hasMany('App\Rates');
    }

}

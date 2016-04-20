<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'exchange_rates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rate', 'from_date', 'to_date'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get Salesman
     */
    public function currency() {
        return $this->belongsTo('App\Currency');
    }

}

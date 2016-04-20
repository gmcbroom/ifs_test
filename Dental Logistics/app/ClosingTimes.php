<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClosingTime extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'closing_times';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'day', 'closing_time'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

}

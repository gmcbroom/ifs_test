<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeightUnit extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'weight_units';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['unit', 'rate'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

}

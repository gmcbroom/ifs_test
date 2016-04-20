<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceLevel extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'service_levels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['service', 'name'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    
}

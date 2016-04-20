<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scandetail extends Model {

    private $fillable = [
        'package_id',
        'message',
        'status',
        'datetime',
        'town',
        'county',
        'country',
        'postcode'
    ];

}

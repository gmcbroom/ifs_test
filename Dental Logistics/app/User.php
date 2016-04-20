<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {

    use Authenticatable,
        Authorizable,
        CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'first_name', 'last_name', 'password', 'password_confirmation', 'phone', 'practice', 'address1', 'town', 'county', 'postcode', 'email', 'mobile', 'verify_token', 'device_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'password_confirmation', 'remember_token'];

    /**
     * Get Salesman
     */
    public function company() {
        return $this->belongsToMany('App\Company');
    }

    /**
     * Get country
     */
    public function country() {
        return $this->belongsTo('App\Country');
    }

    /**
     * Get pickups
     */
    public function pickup() {
        return $this->hasMany('App\Pickup');
    }

}

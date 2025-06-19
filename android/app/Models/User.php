<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\HasRolesAndPermissions;
use Laratrust\Contracts\LaratrustUser;

use Auth;

class User extends \App\Models\Base\User implements LaratrustUser, AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    // Always remember to put necessary traits inside class after defining them below namespace
    // These traits are used by default for user login authentication
    use HasRolesAndPermissions,
        Authenticatable,
        Authorizable,
        CanResetPassword,
        Notifiable;

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'image',
        'role',
    ];

    protected $casts = [
        'created_at'  => 'date:Y-m-d',
        'updated_at'  => 'date:Y-m-d',
    ];

    // Function get user image from database
    public function adminlte_image()
    {

        $getImage = User::find(Auth::user()->id);
        $image = asset('uploads/' . $getImage->image);

        return $image;
    }

    public function adminlte_desc()
    {
        return 'Hi, Welcome!';
    }
}

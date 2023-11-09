<?php

namespace App\Models\Users;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Articles\Article;
use App\Models\Groups\Group;
use App\Models\Quiz\Quiz;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Silber\Bouncer\Database\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndAbilities;

    const ALL_ACCESS_ROLES = [
        'ar_mgmt',
        'ar_admin',
        'ar_staff1',
    ];

    const INSTRUCTOR_ROLES = [
        'ar_instructor',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_instructor',
        'role',
        'ability',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function article()
    {
        return $this->hasMany(Article::class);
    }

    public function group()
    {
        return $this->belongsToMany(Group::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function quiz()
    {
        return $this->hasMany(Quiz::class);
    }

    public function all_access_roles()
    {
        return $this->roles()->whereIn('name', self::ALL_ACCESS_ROLES);
    }

    public function instructor_roles()
    {
        return $this->roles()->whereIn('name', self::INSTRUCTOR_ROLES);
    }
}

<?php

namespace App\Models\GroupUser;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupUser extends Model
{
    use HasFactory;
    protected $table = 'group_user';
    protected $fillable = [
        'user_id',
        'group_id',
    ];
}

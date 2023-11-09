<?php

namespace App\Models\Groups;

use App\Models\ArticleGroup\ArticleGroup;
use App\Models\Articles\Article;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $table = 'el_groups';
    protected $fillable = [
        'name',
    ];

    //protected $hidden = ['pivot'];

    public function user()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    public function article()
    {
        return $this->belongsToMany(Article::class);
    }
}

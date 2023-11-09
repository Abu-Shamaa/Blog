<?php

namespace App\Models\Articles;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\ArticleGroup\ArticleGroup;
use App\Models\Groups\Group;

class Article extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'el_articles';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'date',
        'status',
        'ingroup',

    ];
    protected $casts = [
        'date' => 'datetime:Y-m-d\TH:i:s.u\Z',

    ];

    //protected $hidden = ['pivot'];
    
    protected $with = ['user'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function group()
    {
        return $this->belongsToMany(Group::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function category()
    {
        return $this->belongsToMany(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}

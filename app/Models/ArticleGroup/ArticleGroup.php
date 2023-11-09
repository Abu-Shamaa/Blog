<?php

namespace App\Models\ArticleGroup;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleGroup extends Model
{
    use HasFactory;
    protected $table = 'article_group';
    protected $fillable = [
        'article_id',
        'group_id',
    ];
}

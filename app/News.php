<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
	protected $fillable = [
        'category_id', 'country_id', 'source_id', 'title', 'author', 'description', 'url', 'urltoimage', 'content', 'published_at', 'load_at'
    ];
}

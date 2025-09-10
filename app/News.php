<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
	protected $fillable = [
        'category_id', 'country_id', 'source_id', 'title', 'author', 'description', 'url', 'urltoimage', 'content', 'published_at', 'load_at'
    ];

	public function country() {
        return $this->hasOne('App\Country', 'id', 'country_id');
    }
	public function newscategory() {
        return $this->hasOne('App\NewsCategory', 'id', 'category_id');
    }
	public function newssource() {
        return $this->hasOne('App\NewsSource', 'id', 'source_id');
    }            
}

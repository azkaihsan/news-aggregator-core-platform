<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    protected $fillable = [
        'name', // Add 'name' to the fillable array
        // Add other fillable attributes as needed
    ];
	public function news() {
        return $this->hasMany('App\News', 'category_id');
    }
}

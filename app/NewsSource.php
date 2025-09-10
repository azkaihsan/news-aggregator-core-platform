<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsSource extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // Add 'name' to the fillable array
        // Add other fillable attributes as needed
    ];
    
	public function news() {
        return $this->hasMany('App\News', 'source_id');
    }
}

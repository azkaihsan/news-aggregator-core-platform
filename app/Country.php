<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'code','name', 'full_name', 'iso3', 'number', 'continent_code' // Add 'name' to the fillable array
        // Add other fillable attributes as needed
    ];    
	public function news() {
        return $this->hasMany('App\News', 'country_id');
    }
}

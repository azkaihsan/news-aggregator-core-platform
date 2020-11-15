<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
	public function news() {
        return $this->hasMany('App\News', 'country_id');
    }
}

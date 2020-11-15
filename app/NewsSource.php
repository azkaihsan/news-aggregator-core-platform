<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsSource extends Model
{
	public function news() {
        return $this->hasMany('App\News', 'source_id');
    }
}

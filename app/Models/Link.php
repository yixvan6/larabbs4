<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [
        'title', 'link',
    ];

    public $cache_key = 'larabbs4_links';
    protected $expire_in_minutes = 1440;

    public function getFromCached()
    {
        return \Cache::remember($this->cache_key, $this->expire_in_minutes, function () {
            return $this->all();
        });
    }
}

<?php

namespace App\Models;

use App\Models\User;
use App\Models\Category;

class Topic extends Model
{
    protected $fillable = ['title', 'body', 'category_id', 'excerpt', 'slug'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function scopeOrderWith($query, $order)
    {
        switch ($order) {
            case 'recent':
                $query->latest();
                break;

            default:
                $query->latest('updated_at');
                break;
        }

        // 预加载防止 N+1
        return $query->with('user', 'category');
    }

    public function link($params = [])
    {
        return route('topics.show', array_merge([$this->id, $this->slug], $params));
    }
}

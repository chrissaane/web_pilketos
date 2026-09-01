<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuideItem extends Model
{
    protected $fillable = ['target', 'title', 'content', 'order'];

    public function scopeTargeted($query, string $target)
    {
        return $query->where('target', $target)->orderBy('order')->orderBy('id');
    }
}

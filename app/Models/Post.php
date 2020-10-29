<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['date', 'title', 'link', 'author', 'tags'];
    public $timestamps = false;

    public function getDateFormatedAttribute()
    {
        return date('H:i d.m.Y', $this->date);
    }
}

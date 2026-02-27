<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'start_time',
        'is_active'
    ];

    public function categories()
    {
        return $this->hasMany(TicketCategory::class);
    }
}

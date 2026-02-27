<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'total_quota',
        'remaining_quota',
        'price'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'ticket_category_id',
        'booking_code',
        'status',
        'expires_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class);
    }
}

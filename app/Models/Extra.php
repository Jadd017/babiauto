<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price_per_day', 'is_active'];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_extra')
            ->withPivot(['quantity', 'price_per_day', 'total_price'])
            ->withTimestamps();
    }
}
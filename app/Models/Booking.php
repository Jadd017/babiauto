<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id',
        'pickup_branch_id',
        'dropoff_branch_id',
        'start_date',
        'end_date',
        'pickup_time',
        'dropoff_time',
        'total_days',
        'daily_rate',
        'subtotal',
        'extras_total',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'payment_status',
        'notes',
        'driver_name',
        'driver_phone',
        'driver_license_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function pickupBranch()
    {
        return $this->belongsTo(Branch::class, 'pickup_branch_id');
    }

    public function dropoffBranch()
    {
        return $this->belongsTo(Branch::class, 'dropoff_branch_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function extras()
    {
        return $this->belongsToMany(Extra::class, 'booking_extra')
            ->withPivot(['quantity', 'price_per_day', 'total_price'])
            ->withTimestamps();
    }
}
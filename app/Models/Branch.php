<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'address',
        'phone',
        'latitude',
        'longitude'
    ];

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function pickupBookings()
    {
        return $this->hasMany(Booking::class, 'pickup_branch_id');
    }

    public function dropoffBookings()
    {
        return $this->hasMany(Booking::class, 'dropoff_branch_id');
    }
}
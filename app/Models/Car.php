<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'category_id',
        'branch_id',
        'name',
        'slug',
        'model',
        'year',
        'plate_number',
        'color',
        'transmission',
        'fuel_type',
        'seats',
        'doors',
        'bags_capacity',
        'daily_rate',
        'weekly_rate',
        'monthly_rate',
        'mileage_limit_per_day',
        'description',
        'main_image',
        'is_available',
        'is_featured',
        'status',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function images()
    {
        return $this->hasMany(CarImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
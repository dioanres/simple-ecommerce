<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'desc',
        'stock',
        'price'
    ];

    public function rating()
    {
        return $this->hasMany(ProductRating::class, 'product_id', 'id');
    }

    public function getRateAttribute()
    {
        return round(($this->rating->sum('rating') / 5) / 5, 1) * 5;
    }
}

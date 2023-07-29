<?php

namespace App\Models;

use App\Models\Cart;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'price',
        'uuid'
    ];

    public static function boot()
    {
        parent::boot();
        
        self::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    /**
     * The carts where the product belongs to.
     */
    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class);
    }
}

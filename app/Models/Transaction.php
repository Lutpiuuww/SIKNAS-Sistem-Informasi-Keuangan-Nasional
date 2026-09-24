<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'category',
        'description',
        'amount',
        'type',
        'status',
        'region_id',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'value',
        'desc',
    ];

    public function ratingSender()
    {
        return $this->belongsTo(User::class);
    }

    public function ratingReceiver()
    {
        return $this->belongsTo(User::class);
    }
}
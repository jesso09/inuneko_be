<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'profile_pict',
        'nama',
        'alamat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function pet()
    {
        return $this->hasMany(Pet::class, 'id');
    }
    public function houseCall()
    {
        return $this->hasMany(HouseCall::class, 'customer_id', 'id');
    }
    public function order()
    {
        return $this->hasMany(Pesanan::class, 'customer_id', 'id');
    }
    public function penitipan()
    {
        return $this->hasMany(Penitipan::class, 'customer_id', 'id');
    }
}
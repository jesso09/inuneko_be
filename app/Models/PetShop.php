<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetShop extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'profile_pict',
        'nama',
        'alamat',
        'kapasitas_penitipan',
        'rating',
        'harga_titip',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function product()
    {
        return $this->hasMany(Produk::class, 'shop_id', 'id');
    }
    public function penitipan()
    {
        return $this->hasMany(Penitipan::class, 'pet_shop_id', 'id');
    }
    
    // public function order()
    // {
    //     return $this->hasMany(Pesanan::class, 'shop_id', 'id');
    // }
}
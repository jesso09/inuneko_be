<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'produk_pict',
        'nama',
        'kategori',
        'harga',
        'desc',
        'stok',
    ];
    public function shop()
    {
        return $this->belongsTo(PetShop::class, 'shop_id');
    }
    public function order()
    {
        return $this->hasMany(Pesanan::class, 'produk_id', 'id');
    }
}
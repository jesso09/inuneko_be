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
        'status',
    ];
    public function shop()
    {
        return $this->belongsTo(PetShop::class, 'shop_id');
    }
    public function detailOrder()
    {
        return $this->hasMany(DetailPesanan::class, 'produk_id', 'id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'produk_id',
        'jumlah_pesan',
        'total_harga',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Pesanan::class, 'order_id');
    }
    public function product()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}

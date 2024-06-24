<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        // 'shop_id',
        'no_pesanan',
        'tanggal_pesan',
        'alamat_pengiriman',
    ];

    public function cust()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function detailOrder()
    {
        return $this->hasMany(DetailPesanan::class, 'order_id');
    }
}
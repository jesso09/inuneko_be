<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'produk_id',
        'no_pesanan',
        'jumlah_pesan',
        'tanggal_pesan',
        'status',
    ];
    public function cust()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
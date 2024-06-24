<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penitipan extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'pet_id',
        'pet_shop_id',
        'penitipan_order_id',
        'durasi',
        'harga',
        'mulai',
        'selesai',
        'status',
    ];
    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
    public function petShop()
    {
        return $this->belongsTo(PetShop::class);
    }
    public function cust()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function aktivitas()
    {
        return $this->hasMany(AktivitasPenitipan::class, 'pet_id', 'id');
    }
}
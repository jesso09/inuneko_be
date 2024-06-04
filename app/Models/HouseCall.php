<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseCall extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'vet_id',
        'service_id',
        'status',
        'mulai',
        'selesai',
    ];
    public function vet()
    {
        return $this->belongsTo(Vet::class, 'vet_id');
    }
    public function cust()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function layanan()
    {
        return $this->belongsTo(LayananVet::class, 'service_id');
    }
}

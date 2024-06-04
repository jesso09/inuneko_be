<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'pet_pict',
        'nama',
        'gender',
        'jenis',
        'ras',
        'umur',
        'status',
    ];
    public function cust()
    {
        return $this->belongsTo(Customer::class, 'id');
    }
    public function penitipan()
    {
        return $this->hasOne(Pet::class, 'pet_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vet extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'profile_pict',
        'nama',
        'alamat',
        'pengalaman',
        'rating',
        'info_lain',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function layanan()
    {
        return $this->hasMany(LayananVet::class, 'vet_id', 'id');
    }
    public function houseCall()
    {
        return $this->hasMany(HouseCall::class, 'vet_id', 'id');
    }
}

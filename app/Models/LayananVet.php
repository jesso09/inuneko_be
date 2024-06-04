<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LayananVet extends Model
{
    use HasFactory;
    protected $fillable = [
        'vet_id',
        'nama_layanan',
        'harga',
        'harga_per_jarak',
        'keterangan',
    ];
    public function vet()
    {
        return $this->belongsTo(Vet::class, 'vet_id');
    }
    public function layanan()
    {
        return $this->hasMany(HouseCall::class, 'service_id', 'id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasPenitipan extends Model
{
    use HasFactory;
    protected $fillable = [
        'penitipan_id',
        'foto',
        'video',
        'judul_aktivitas',
        'waktu_aktivitas',
        'keterangan',
    ];

    public function penitipan()
    {
        return $this->belongsTo(Penitipan::class, 'id');
    }
}

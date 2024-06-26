<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipsPet extends Model
{
    use HasFactory;
    protected $fillable = [
        'judul',
        'jenis_pet',
        'ras_pet',
        'tips_pict',
        'tips_text',
    ];
}
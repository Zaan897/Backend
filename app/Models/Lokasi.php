<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use illuminate\database\Eloquent\Factories\HasFactory;

class Lokasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'nama_tempat',
        'lat',
        'lang',
        'absen_datang',
        'absen_pulang',
        'radius',
    ];
}

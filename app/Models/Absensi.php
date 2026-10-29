<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use illuminate\database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'tgl',
        'jam_datang',
        'keterangan_datang',
        'jam_pulang',
        'keterangan_pulang',
        'presensi',
        'keterangan_presensi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}

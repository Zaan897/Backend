<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penugasan extends Model
{
    protected $table = 'penugasan';

    protected $fillable = [
        'id_user',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'status_penugasan',
        'keterangan',
        'created_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
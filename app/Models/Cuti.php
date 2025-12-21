<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    protected $table = 'cutis';

    protected $fillable = [
        'id_karyawan',
        'id_cuti',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan_cuti',
        'status_cuti',
    ];
    
}

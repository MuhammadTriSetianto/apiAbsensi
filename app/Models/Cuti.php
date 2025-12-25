<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    protected $table = 'cutis';

    protected $fillable = [
        'id_proyek',
        'id_karyawan',
        'id_cuti',
        'subjek_cuti',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan_cuti',
        'surat_cuti',
        'status_cuti',
    ];
    
}

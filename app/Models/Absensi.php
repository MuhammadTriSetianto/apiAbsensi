<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';
    protected $primaryKey = 'id_absensi';

    protected $fillable = [
        'id_pegawai',
        'id_proyek',
        'tanggal_absensi',
        'jam_masuk',
        'jam_pulang',
        'keterangan_absensi',
    ];

    // 1 absensi = 1 foto
    public function fotoAbsensi(): HasOne
    {
        return $this->hasOne(FotoAbsensi::class, 'id_absensi', 'id_absensi');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'id_proyek', 'id_proyek');
    }
}

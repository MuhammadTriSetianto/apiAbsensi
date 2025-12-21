<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Absensi extends Model
{
    
    use HasFactory;
    
    protected $table = "absensis";
    protected $fillable = [
    "id_pegawai",
    "id_proyek",
    "tanggal_absensi",
    "jam_masuk",
    "jam_pulang",
    "keterangan_absensi"
    ];
    

public function fotoabsensi(): HasMany{
    return $this->hasMany(FotoAbsensi::class, 'id_absensi', 'id_absensi');
}
//FK from table users
    public function pegawai(): BelongsTo{
        return $this->belongsTo(pegawai::class, 'id_pagawai', 'id_pegawai');
    } 
//FK from table proyeks
    public function proyek(): BelongsTo{
        return $this->belongsTo(Proyek::class, 'id_proyek', 'id_proyek');
    }
    //
}

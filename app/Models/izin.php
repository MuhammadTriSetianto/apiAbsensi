<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Izin extends Model
{
    use HasFactory;

    protected $table = 'izins'; // ← SESUAIKAN DENGAN DB
    protected $primaryKey = 'id_izin'; // jika bukan id
    protected $guarded = ['id_izin'];   

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'id_proyek', 'id_proyek');
    }
    
    
}

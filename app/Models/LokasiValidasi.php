<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LokasiValidasi extends Model
{
    //
    use HasFactory;

    protected $table = 'lokasi_validasi';
    
    protected $filleable = ['id_lokasi_validasi', 'id_proyek', 'radius_meter', 'keterangan'];

    function proyek():BelongsTo{
        return $this->belongsTo(Proyek::class, 'id_proyek');
    }
}

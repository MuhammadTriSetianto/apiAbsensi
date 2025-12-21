<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoAbsensi extends Model
{
    //
    use HasFactory;

    protected $tabele = 'foto_absensis';
    protected $fillable = [
        'id_absensi',
        'foto_absensi',
        'latitude',
        'longitude',
    ];

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class, 'id_absensi', 'id_absensi');
    }
}

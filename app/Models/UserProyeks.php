<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class UserProyeks extends Model
{
    //
    use HasFactory;

    protected $table = 'user_proyeks';
    protected $fillable = [
        'id_pegawai',
        'id_proyek',
        'jabatan',
    ];


    public function pegawai():BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    public function proyek():BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'id_proyek', 'id_proyek');
    }
    
}

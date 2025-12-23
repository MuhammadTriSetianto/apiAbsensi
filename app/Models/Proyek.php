<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Factories\HasFactory;
class Proyek extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'nama_proyek',
        'deskripsi',
        'lokasi_proyek',
        'long_proyek',
        'lat_proyek',
    ];
    function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    function user_proyeks()
    {
        return $this->hasMany(UserProyeks::class);
    }
}

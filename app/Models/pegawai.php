<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pegawai extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'pegawais';
    public $timestamps = true;
    public $incrementing = false;  

    protected $fillable = ['name', 'email', "no_hp", 'password','image',"id_pegawai", "jabatan"];

    public function user_proyeks(): HasMany
    {
        return $this->hasMany(UserProyeks::class, 'id_pegawai', 'id_pegawai');
    }

    public function izin(): HasMany
    {
        return $this->hasMany(Izin::class, 'id_pegawai', 'id_pegawai');
    }

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class, 'id_pegawai', 'id_pegawai');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function cuti(): HasMany
    {
        return $this->hasMany(Cuti::class, 'id_karyawan', 'id_pegawai');
    }
}

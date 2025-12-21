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
        'user_id',
        'proyek_id',
        'jabatan',
        'created_at'
    ];


    public function user():BelongsTo
    {
        return $this->belongsTo(pegawai::class, 'user_id', 'id_users');
    }
    
}

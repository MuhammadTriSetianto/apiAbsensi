<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class izin extends Model
{
    use HasFactory;
    protected $table = 'izin';

    protected $guarded = ['id_izin'];
    
    public function user():BelongsTo{
        return $this->belongsTo(pegawai::class,'id_pegawai','id_pegawai');
    }
    //
}
